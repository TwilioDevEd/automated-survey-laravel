<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use \App\Question;
use \App\Survey;

class QuestionControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Load survey test data
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->beginDatabaseTransaction();
    }

    public function testShowQuestion()
    {
        $survey = new Survey(['title' => 'Testing survey']);
        $question = new Question(['body' => 'What is this?', 'kind' => 'voice']);

        $survey->save();
        $question->survey()->associate($survey)->save();

        $response = $this->call(
            'GET',
            route('question.show', ['id' => $question->id])
        );

        $savingUrl = route(
            'question.question_response.store',
            ['question_id' => $question->id], false
        );

        $absoluteSavingUrl = route(
            'question.question_response.store',
            ['question_id' => $question->id]
        );

        $this->assertContains($question->body, $response->getContent());
        $this->assertContains($savingUrl . '?Kind=voice', $response->getContent());
        $this->assertNotContains($absoluteSavingUrl, $response->getContent());
    }
}