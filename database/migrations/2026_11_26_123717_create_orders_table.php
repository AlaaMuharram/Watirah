<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_type_id')->nullable()->constrained('product_types')->nullOnDelete();
            $table->string('order_number')->unique();
            $table->string('customer_name');
            $table->string('product_name');
            $table->text('description')->nullable();
            $table->integer('quantity')->default(1);
            $table->date('received_date')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('unit_prep_minutes',8,2)->default(0);
            $table->decimal('total_prep_minutes',10,2)->default(0);
            $table->enum('status',['new','pending','in_progress','completed','late'])->default('new');
            $table->enum('risk_level',['low','medium','high'])->default('low');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('orders'); }
};