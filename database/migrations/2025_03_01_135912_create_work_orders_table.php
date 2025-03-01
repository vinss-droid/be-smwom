<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->integer('id')->primary()->autoIncrement();
            $table->foreignId('operator_id')
                ->references('id')
                ->on('users')
                ->onDelete('NO ACTION')
                ->onUpdate('NO ACTION');
            $table->string('no_wo');
            $table->string('product_name');
            $table->date('date_work_order');
            $table->date('deadline');
            $table->integer('qty_order');
            $table->integer('qty_pending')->nullable();
            $table->integer('qty_inProgress')->nullable();
            $table->integer('qty_completed')->nullable();
            $table->integer('qty_canceled')->nullable();
            $table->enum('status', ['pending', 'inProgress', 'completed', 'canceled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('word_orders');
    }
};
