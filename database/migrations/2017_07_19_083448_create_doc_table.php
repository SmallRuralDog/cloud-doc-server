<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('doc', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->comment('文档标题');
            $table->string('desc')->comment('文档描述');
            $table->string('doc_class_id')->comment('分类ID');
            $table->string('cover')->comment('文档封面');
            $table->integer('user_id')->comment('用户ID');
            $table->string('source')->comment('文档来源');
            $table->boolean('is_end')->default('1')->comment('是否完结');
            $table->integer('order')->default('1')->comment('排序');
            $table->boolean('is_hot')->comment('是否推荐');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('doc');
    }
}
