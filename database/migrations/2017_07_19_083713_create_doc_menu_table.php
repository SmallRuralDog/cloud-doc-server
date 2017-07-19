<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocMenuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('doc_menu', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->comment('目录名称');
            $table->string('parent_id')->comment('上级目录');
            $table->smallInteger('order')->default('1')->comment('排序');
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
        Schema::dropIfExists('doc_menu');
    }
}
