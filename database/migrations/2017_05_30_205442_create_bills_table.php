<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sender_id')->unsigned();
            $table->integer('receiver_id')->unsigned();

            $table->integer('src_payment_id')->unsigned();
            $table->integer('dst_payment_id')->unsigned();

            $table->decimal('amount', 8, 4);
            $table->text('notification')->nullable();
            $table->boolean('status')->default(0);

            $table->foreign('sender_id')->references('id')->on('users');
            $table->foreign('receiver_id')->references('id')->on('users');

            $table->foreign('src_payment_id')->references('id')->on('payments');
            $table->foreign('dst_payment_id')->references('id')->on('payments');

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
        Schema::dropIfExists('bills');
    }
}
