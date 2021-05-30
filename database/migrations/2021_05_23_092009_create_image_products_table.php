<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImageProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('imageProducts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('creator_id');
            $table->unsignedBigInteger('category_id')->default(1);
            $table->foreign('creator_id')->references('id')->on('users');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->double('price',8,2);
            $table->string('filename');
            $table->string('format');
            $table->integer('width');
            $table->integer('height');
            $table->integer('type');

            $table->timestamps();
        });;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('imageProducts');
    }
}
