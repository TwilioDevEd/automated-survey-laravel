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
            route('question.show.voice', ['question' => $question->id, 'survey' => $survey->id])
        );

        $savingUrl = route(
            'response.store.voice',
            ['question' => $question->id,
             'survey' => $survey->id], false
        );

        $absoluteSavingUrl = route(
            'response.store.voice',
            ['question' => $question->id,
             'survey' => $survey->id]
        );

        $this->assertContains($question->body, $response->getContent());
        $this->assertContains($savingUrl . '?Kind=voice', $response->getContent());
        $this->assertNotContains($absoluteSavingUrl, $response->getContent());
    }
}
