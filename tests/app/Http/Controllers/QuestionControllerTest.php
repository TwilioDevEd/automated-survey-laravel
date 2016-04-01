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
        $this->survey = new Survey(['title' => 'Testing survey']);
        $this->question = new Question(['body' => 'What is this?', 'kind' => 'free-answer']);

        $this->survey->save();
        $this->question->survey()->associate($this->survey)->save();
    }

    public function testShowVoiceQuestion()
    {
        $response = $this->call(
            'GET',
            route('question.show.voice', ['question' => $this->question->id, 'survey' => $this->survey->id])
        );

        $savingUrl = route(
            'response.store.voice',
            ['question' => $this->question->id,
             'survey' => $this->survey->id], false
        );

        $absoluteSavingUrl = route(
            'response.store.voice',
            ['question' => $this->question->id,
             'survey' => $this->survey->id]
        );

        $transcriptionUrl = route(
            'response.transcription.store',
            ['question' => $this->question->id,
             'survey' => $this->survey->id]
        );

        $responseDocument = new SimpleXMLElement($response->getContent());

        $this->assertContains($this->question->body, $response->getContent());
        $this->assertContains($savingUrl, $response->getContent());
        $this->assertNotContains($absoluteSavingUrl, $response->getContent());
        $this->assertEquals($transcriptionUrl, strval($responseDocument->Record->attributes()['transcribeCallback']));
        $this->assertTrue(boolval($responseDocument->Record->attributes()['transcribe']));
    }

    public function testShowSmsQuestion() {
        $response = $this->call(
            'GET',
            route('question.show.sms', ['question' => $this->question->id, 'survey' => $this->survey->id])
        );
        $cookies = $response->headers->getCookies();

        $this->assertCount(1, $cookies);
        $this->assertEquals('current_question', $cookies[0]->getName());
        $this->assertEquals($this->question->id, $cookies[0]->getValue());

        $messageDocument = new SimpleXMLElement($response->getContent());

        $this->assertEquals(
            $this->question->body . "\n\nReply to this message with your answer",
            strval($messageDocument->Message)
        );
    }
}
