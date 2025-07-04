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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // кто отправил
            $table->foreignId('support_id')->nullable()->constrained('users'); //кто получил сообщение
            $table->text('content'); 
            $table->string('attachment')->nullable();
            $table->boolean('is_from_user')->default(true); //от пользователя или от поддержки
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};

