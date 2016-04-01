<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuestionsResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'question_responses', function ($table) {
                $table->increments('id');
                $table->text('response');
                $table->enum('type', ['voice', 'sms']);
                $table->string('session_sid');
                $table->integer('question_id');
                $table->timestamps();

                $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
            }
        );

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(
            'question_responses', function (Blueprint $table) {
                $table->dropForeign('question_responses_question_id_foreign');
            }
        );
        Schema::drop('question_responses');
    }
}
