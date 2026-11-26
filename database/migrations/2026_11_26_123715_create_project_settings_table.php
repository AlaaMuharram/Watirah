<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('project_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('project_name')->default('WATIRA');
            $table->json('work_days')->default(json_encode(['sun','mon','tue','wed','thu']));
            $table->time('work_start')->default('09:00:00');
            $table->time('work_end')->default('17:00:00');
            $table->decimal('daily_production_hours',5,2)->default(6);
            $table->decimal('break_hours',5,2)->default(1);
            $table->string('locale')->default('ar');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('project_settings'); }
};