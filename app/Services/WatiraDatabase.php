<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * نفس عقد خادم Express السابق: قراءة الجداول دفعة واحدة،
 * وحفظ كل جدول بـ upsert ثم حذف الصفوف التي لم تعد موجودة.
 */
class WatiraDatabase
{
    /** @var array<string, array{cols: list<string>, dt?: list<string>, bool?: list<string>, int?: list<string>}> */
    public const TABLES = [
        'product_types' => [
            'cols' => ['id', 'product_name', 'estimated_unit_duration', 'notes'],
            'int' => ['estimated_unit_duration'],
        ],
        'orders' => [
            'cols' => ['id', 'order_number', 'customer_name', 'product_name', 'description', 'quantity', 'received_date', 'due_date', 'duration_mode', 'unit_duration', 'estimated_duration', 'remaining_duration', 'status', 'risk_level', 'notes', 'completed_at', 'created_at', 'updated_at'],
            'dt' => ['completed_at', 'created_at', 'updated_at'],
            'int' => ['quantity', 'unit_duration', 'estimated_duration', 'remaining_duration'],
        ],
        'execution_logs' => [
            'cols' => ['id', 'order_id', 'old_status', 'new_status', 'changed_at', 'notes'],
            'dt' => ['changed_at'],
        ],
        'alerts' => [
            'cols' => ['id', 'order_id', 'alert_type', 'alert_key', 'message', 'action', 'risk_level', 'created_at', 'is_read'],
            'dt' => ['created_at'],
            'bool' => ['is_read'],
        ],
    ];

    public function known(string $name): bool
    {
        return isset(self::TABLES[$name]);
    }

    /** @return array{settings: ?array<string, mixed>, product_types: ?list<array<string, mixed>>, orders: ?list<array<string, mixed>>, execution_logs: list<array<string, mixed>>, alerts: list<array<string, mixed>>} */
    public function all(): array
    {
        $settings = $this->readSettings();
        $productTypes = $this->readTable('product_types');
        $orders = $this->readTable('orders');

        return [
            'settings' => $settings,
            'product_types' => $settings ? $productTypes : null,
            'orders' => $settings ? $orders : null,
            'execution_logs' => $this->readTable('execution_logs'),
            'alerts' => $this->readTable('alerts'),
        ];
    }

    /** @param array<string, mixed> $settings */
    public function saveSettings(array $settings): void
    {
        $days = $settings['working_days'] ?? [];
        if (! is_string($days)) {
            $days = json_encode($days, JSON_UNESCAPED_UNICODE);
        }

        $row = [
            'project_name' => $settings['project_name'] ?? '',
            'working_days' => $days,
            'start_time' => $settings['start_time'] ?? '',
            'end_time' => $settings['end_time'] ?? '',
            'daily_productive_hours' => $settings['daily_productive_hours'] ?? 0,
            'break_duration' => $settings['break_duration'] ?? 0 ?: 0,
            'buffer_days' => $settings['buffer_days'] ?? 0 ?: 0,
        ];

        DB::table('project_settings')->upsert(
            [array_merge(['id' => 1], $row)],
            ['id'],
            array_keys($row)
        );
    }

    /** @param list<array<string, mixed>> $data */
    public function saveTable(string $name, array $data): void
    {
        $t = self::TABLES[$name];
        $update = array_values(array_filter($t['cols'], fn (string $c) => $c !== 'id'));

        DB::transaction(function () use ($name, $data, $t, $update) {
            foreach ($data as $record) {
                $row = [];
                foreach ($t['cols'] as $column) {
                    $value = $record[$column] ?? null;
                    if (in_array($column, $t['dt'] ?? [], true)) {
                        $value = self::toDb($value);
                    }
                    if (in_array($column, $t['bool'] ?? [], true)) {
                        $value = $value ? 1 : 0;
                    }
                    $row[$column] = $value;
                }
                DB::table($name)->upsert([$row], ['id'], $update);
            }

            $ids = [];
            foreach ($data as $record) {
                if (isset($record['id'])) {
                    $ids[] = $record['id'];
                }
            }

            if ($ids !== []) {
                DB::table($name)->whereNotIn('id', $ids)->delete();
            } else {
                DB::table($name)->delete();
            }
        });
    }

    public function clear(): void
    {
        DB::transaction(function () {
            foreach (['alerts', 'execution_logs', 'orders', 'product_types', 'project_settings'] as $table) {
                DB::table($table)->delete();
            }
        });
    }

    public static function toDb(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $date = new \DateTimeImmutable((string) $value);

        return $date->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s.v');
    }

    public static function fromDb(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            $value = \DateTimeImmutable::createFromInterface($value)
                ->setTimezone(new \DateTimeZone('UTC'))
                ->format('Y-m-d H:i:s.v');
        }

        if ($value === null || $value === '') {
            return null;
        }

        $value = str_replace(' ', 'T', (string) $value);
        if (strlen($value) === 19) {
            $value .= '.000';
        }

        return $value.'Z';
    }

    /** @return ?array<string, mixed> */
    private function readSettings(): ?array
    {
        $row = DB::table('project_settings')->where('id', 1)->first([
            'project_name', 'working_days', 'start_time', 'end_time',
            'daily_productive_hours', 'break_duration', 'buffer_days',
        ]);

        if (! $row) {
            return null;
        }

        $settings = (array) $row;
        $days = $settings['working_days'];
        if (is_string($days)) {
            $decoded = json_decode($days, true);
            $settings['working_days'] = is_array($decoded) ? $decoded : $days;
        }
        $settings['daily_productive_hours'] = (float) $settings['daily_productive_hours'];
        $settings['break_duration'] = (int) $settings['break_duration'];
        $settings['buffer_days'] = (int) $settings['buffer_days'];

        return $settings;
    }

    /** @return list<array<string, mixed>> */
    private function readTable(string $name): array
    {
        $definition = self::TABLES[$name];
        $rows = DB::table($name)->get($definition['cols']);
        $out = [];

        foreach ($rows as $row) {
            $record = (array) $row;
            foreach ($definition['dt'] ?? [] as $column) {
                $record[$column] = self::fromDb($record[$column] ?? null);
            }
            foreach ($definition['bool'] ?? [] as $column) {
                $record[$column] = (bool) (int) ($record[$column] ?? 0);
            }
            foreach ($definition['int'] ?? [] as $column) {
                $value = $record[$column] ?? null;
                $record[$column] = ($value === null || $value === '') ? null : (int) $value;
            }
            $out[] = $record;
        }

        return $out;
    }
}
