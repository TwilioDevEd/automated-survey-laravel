<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use \App\Question;
use \App\Survey;

class QuestionResponseControllerTest extends TestCase
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
        $this->survey = new Survey(['title' => 'Testing survey']);
        $this->questionOne = new Question(['body' => 'What is this? Question 1', 'kind' => 'voice']);
        $this->questionTwo = new Question(['body' => 'What is that? Question 2', 'kind' => 'numeric']);
        $this->survey->save();
        $this->questionOne->survey()->associate($this->survey)->save();
        $this->questionTwo->survey()->associate($this->survey)->save();
    }

    public function testStoreVoiceResponse()
    {
        $responseForQuestion = [
            'RecordingUrl' => '//somefake.mp3',
            'CallSid' => '7h1515un1qu3'
        ];

        $firstResponse = $this->call(
            'POST',
            route(
                'response.store.voice',
                ['question' => $this->questionOne->id,
                 'survey' => $this->survey->id]
            ),
            $responseForQuestion
        );

        $routeToNextQuestion = route('question.show.voice', ['question' => $this->questionTwo->id, 'survey' => $this->survey->id], false);
        $routeToNextQuestionAbsolute = route('question.show.voice', ['question' => $this->questionTwo->id, 'survey' => $this->survey->id], true);
        $this->assertContains($routeToNextQuestion, $firstResponse->getContent());

        $secondResponse = $this->call(
            'POST',
            route(
                'response.store.voice',
                ['question' => $this->questionTwo->id,
                 'survey' => $this->survey->id]
            ),
            $responseForQuestion
        );

        $this->assertNotContains('Redirect', $secondResponse->getContent());
    }
}
