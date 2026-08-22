<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTelegramUsersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('telegram_users')) {
            Schema::create('telegram_users', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->bigInteger('telegram_id')->unique();
                $table->string('first_name')->nullable();
                $table->string('username')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('telegram_users');
    }
}