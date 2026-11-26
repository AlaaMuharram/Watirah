<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_settings', function (Blueprint $table) {
            $table->id();
            $table->string('project_name', 200);
            $table->json('working_days');
            $table->char('start_time', 5);
            $table->char('end_time', 5);
            $table->decimal('daily_productive_hours', 5, 2);
            $table->integer('break_duration')->default(0);
            $table->integer('buffer_days')->default(0);
            $table->timestamps();
        });

        Schema::create('product_types', function (Blueprint $table) {
            $table->string('id', 40)->primary();
            $table->string('product_name', 200);
            $table->integer('estimated_unit_duration');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->string('id', 40)->primary();
            $table->string('order_number', 30)->unique();
            $table->string('customer_name', 200);
            $table->string('customer_phone', 50)->nullable();
            $table->string('product_name', 200);
            $table->text('description')->nullable();
            $table->integer('quantity')->default(1);
            $table->date('received_date');
            $table->date('due_date');
            $table->enum('duration_mode', ['auto', 'manual'])->default('auto');
            $table->integer('unit_duration')->nullable();
            $table->integer('estimated_duration')->default(0);
            $table->integer('remaining_duration')->default(0);
            $table->enum('status', ['new', 'waiting', 'in_progress', 'completed', 'late'])->default('new');
            $table->enum('risk_level', ['low', 'medium', 'high'])->default('low');
            $table->text('notes')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->index('due_date', 'idx_due');
            $table->index('status', 'idx_status');
            $table->index('risk_level', 'idx_risk');
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->string('id', 40)->primary();
            $table->string('order_id', 40);
            $table->string('title', 200);
            $table->integer('duration_minutes')->default(30);
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->integer('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->index('order_id', 'idx_task_order');
            $table->index('status', 'idx_task_status');
        });

        Schema::create('execution_logs', function (Blueprint $table) {
            $table->string('id', 40)->primary();
            $table->string('order_id', 40);
            $table->string('old_status', 20)->nullable();
            $table->string('new_status', 20);
            $table->dateTime('changed_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
        });

        Schema::create('alerts', function (Blueprint $table) {
            $table->string('id', 40)->primary();
            $table->string('order_id', 40)->nullable();
            $table->string('alert_type', 30);
            $table->string('alert_key', 80);
            $table->text('message');
            $table->text('action')->nullable();
            $table->enum('risk_level', ['low', 'medium', 'high'])->default('low');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->index('risk_level', 'idx_alert_risk');
            $table->index('is_read', 'idx_alert_read');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
        Schema::dropIfExists('execution_logs');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('product_types');
        Schema::dropIfExists('project_settings');
    }
};
