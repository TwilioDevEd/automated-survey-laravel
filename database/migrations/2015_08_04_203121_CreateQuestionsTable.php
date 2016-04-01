<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'questions', function (Blueprint $table) {
                $table->increments('id');
                $table->string('body');
                $table->enum('kind', ['free-answer', 'yes-no', 'numeric']);
                $table->integer('survey_id');
                $table->timestamps();

                $table->foreign('survey_id')->references('id')->on('surveys')->onDelete('cascade');;
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
            'questions', function (Blueprint $table) {
                $table->dropForeign('questions_survey_id_foreign');
            }
        );
        Schema::drop('questions');
    }
}
