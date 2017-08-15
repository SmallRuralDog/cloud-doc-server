<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuestionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('question', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->comment('用户id');
            $table->string('title')->comment('问题');
            $table->string('desc')->comment('问题描述');
            $table->char('source')->comment('问题来源');
            $table->integer('source_id')->comment('来源ID');
            $table->integer('view_count')->comment('浏览器次');
            $table->boolean('state')->comment('状态');
            $table->string('pics')->comment('问题图片数组');
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
        Schema::dropIfExists('question');
    }
}
