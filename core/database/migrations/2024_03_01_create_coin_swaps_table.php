<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoinSwapsTable extends Migration
{
    public function up()
    {
        Schema::create('coin_swaps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('from_currency_id');
            $table->unsignedBigInteger('to_currency_id');
            $table->decimal('from_amount', 18, 8);
            $table->decimal('to_amount', 18, 8);
            $table->decimal('rate', 18, 8);
            $table->decimal('charge', 18, 8);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('from_currency_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->foreign('to_currency_id')->references('id')->on('currencies')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('coin_swaps');
    }
}