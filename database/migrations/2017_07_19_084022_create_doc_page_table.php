<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocPageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('doc_page', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->comment('文档标题');
            $table->string('menu_title')->comment('目录标题');
            $table->text('content')->comment('文档内容');
            $table->smallInteger('order')->default('1')->comment('排序');
            $table->boolean('state')->default('1')->comment('文档状态');
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
        Schema::dropIfExists('doc_page');
    }
}
