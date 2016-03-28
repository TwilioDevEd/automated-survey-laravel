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
    }

    public function testStoreResponse()
    {
        $survey = new Survey(['title' => 'Testing survey']);
        $questionOne = new Question(['body' => 'What is this?', 'kind' => 'voice']);
        $questionTwo = new Question(['body' => 'What is that?', 'kind' => 'voice']);

        $survey->save();
        $questionOne->survey()->associate($survey)->save();
        $questionTwo->survey()->associate($survey)->save();


        $responseForQuestion = [
            'RecordingUrl' => '//somefake.mp3',
            'CallSid' => '7h1515un1qu3',
            'Kind' => 'voice'
        ];

        $firstResponse = $this->call(
            'POST',
            route(
                'response.store.voice',
                ['question' => $questionOne->id,
                 'survey' => $survey->id]
            ),
            $responseForQuestion
        );

        $routeToNextQuestion = route('question.show.voice', ['question' => $questionTwo->id, 'survey' => $survey->id], false);
        $routeToNextQuestionAbsolute = route('question.show.voice', ['question' => $questionTwo->id, 'survey' => $survey->id], true);
        $this->assertContains($routeToNextQuestion, $firstResponse->getContent());

        $secondResponse = $this->call(
            'POST',
            route(
                'response.store.voice',
                ['question' => $questionTwo->id,
                 'survey' => $survey->id]
            ),
            $responseForQuestion
        );

        $this->assertNotContains('Redirect', $secondResponse->getContent());
    }
}
