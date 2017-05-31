<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('owner_id')->unsigned();
            $table->integer('payment_id')->unsigned();
            $table->text('message');
            $table->boolean('type');
            $table->decimal('amount', 8, 4);
            $table->decimal('balance', 8, 4);

            $table->foreign('owner_id')->references('id')->on('users');
            $table->foreign('payment_id')->references('id')->on('payments');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('history');
    }
}