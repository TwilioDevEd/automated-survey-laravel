<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResponseTranscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('response_transcriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->text('transcription');
            $table->integer('question_response_id');
            $table->foreign('question_response_id')->references('id')->on('question_responses')->onDelete('cascade');
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
        Schema::table(
            'response_transcriptions', function (Blueprint $table) {
                $table->dropForeign('response_transcriptions_question_response_id_foreign');
            }
        );
        Schema::drop('response_transcriptions');
    }
}
