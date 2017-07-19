<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocClassTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('doc_class', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->comment('分类名称');
            $table->string('desc')->comment('分类描述');
            $table->integer('parent_id')->comment('上级ID');
            $table->smallInteger('order')->comment('排序');
            $table->string('icon')->comment('分类图标');
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
        Schema::dropIfExists('doc_class');
    }
}
