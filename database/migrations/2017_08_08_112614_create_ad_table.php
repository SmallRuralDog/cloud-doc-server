<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ad', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('广告名称');
            $table->string('title')->comment('显示标题');
            $table->string('cover')->comment('广告图片');
            $table->string('page')->comment('广告页面');
            $table->integer('order')->comment('排序');
            $table->boolean('state')->comment('状态');
            $table->integer('loca_id')->comment('广告位');
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
        Schema::dropIfExists('ad');
    }
}
