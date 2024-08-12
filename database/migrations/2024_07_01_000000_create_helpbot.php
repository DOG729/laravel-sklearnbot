<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHelpbot extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sklearnbot_helpbot', function (Blueprint $table) { 
            $table->increments('id');
            $table->string('type');
            $table->string('hash')->nullable();
            $table->string('title')->nullable();
            $table->longText('text')->nullable();
            $table->json('synonym')->nullable();
            $table->json('action')->nullable();
            $table->json('data')->nullable();
            $table->integer('belongs_to')->nullable();
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
        Schema::dropIfExists('sklearnbot_helpbot'); 
    }
}
