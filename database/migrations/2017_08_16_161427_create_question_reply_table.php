<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuestionReplyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('question_reply', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->text('content');
            $table->integer('question_id');
            $table->boolean('state');
            $table->integer('view_count');
            $table->boolean('is_accept');
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
        Schema::dropIfExists('question_reply');
    }
}
