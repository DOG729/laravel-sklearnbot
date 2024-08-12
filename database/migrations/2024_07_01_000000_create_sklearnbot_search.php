<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSklearnbotSearch extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sklearnbot_search', function (Blueprint $table) { 
            $table->increments('id');
            $table->string('type');
            $table->string('hash')->nullable();
            $table->string('title')->nullable();
            $table->longText('description')->nullable();
            $table->json('synonym')->nullable();
            $table->json('data')->nullable();
            $table->double('weight')->default(1); 
            $table->longText('class')->nullable();
            $table->integer('class_id')->nullable();
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
        Schema::dropIfExists('sklearnbot_search'); 
    }
}
