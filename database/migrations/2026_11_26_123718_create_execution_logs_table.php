<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('execution_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->timestamp('changed_at')->useCurrent();
            $table->timestamps();
        });
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->string('title');
            $table->text('message')->nullable();
            $table->boolean('read')->default(false);
            $table->timestamps();
        });
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('available_hours',5,2)->default(6);
            $table->decimal('used_hours',5,2)->default(0);
            $table->timestamps();
            $table->unique(['user_id','date']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('work_schedules');
        Schema::dropIfExists('alerts');
        Schema::dropIfExists('execution_logs');
    }
};