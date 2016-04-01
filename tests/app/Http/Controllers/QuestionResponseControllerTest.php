<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Question;
use App\Survey;
use App\QuestionResponse;

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
        $this->questionOne = new Question(['body' => 'What is this? Question 1', 'kind' => 'free-answer']);
        $this->questionTwo = new Question(['body' => 'What is that? Question 2', 'kind' => 'numeric']);
        $this->survey->save();
        $this->questionOne->survey()->associate($this->survey)->save();
        $this->questionTwo->survey()->associate($this->survey)->save();
    }

    public function testStoreVoiceResponse()
    {
        $responseForQuestion = [
            'RecordingUrl' => '//somefake.mp3',
            'CallSid' => '7h1515un1qu3',
            'Digits' => '10'
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
        $this->assertContains('That was the last question', $secondResponse->getContent());
    }

    public function testStoreSmsResponse()
    {
        $this->assertCount(0, QuestionResponse::all());

        $firstResponse = $this->call(
            'POST',
            route(
                'response.store.sms',
                ['question' => $this->questionOne->id,
                 'survey' => $this->survey->id]
            ),
            ['Body' => 'Some answer'],
            ['survey_session' => 'session_SID']
        );

        $messageDocument = new SimpleXMLElement($firstResponse->getContent());
        $this->assertCount(1, QuestionResponse::all());
        $questionResponse = QuestionResponse::first();

        $this->assertEquals('Some answer', $questionResponse->response);
        $this->assertEquals('session_SID', $questionResponse->session_sid);
        $this->assertEquals('sms', $questionResponse->type);
        $this->assertEquals(
            route('question.show.sms', ['survey' => $this->survey->id, 'question' => $this->questionTwo->id]),
            strval($messageDocument->Redirect)
        );
    }

    public function testStoreLastQuestionSmsAnswer() {
        $this->assertCount(0, QuestionResponse::all());

        $firstResponse = $this->call(
            'POST',
            route(
                'response.store.sms',
                ['question' => $this->questionTwo->id,
                 'survey' => $this->survey->id]
            ),
            ['Body' => 'Some answer two'],
            ['survey_session' => 'session_SID']
        );

        $cookies = $firstResponse->headers->getCookies();
        $messageDocument = new SimpleXMLElement($firstResponse->getContent());
        $this->assertCount(1, QuestionResponse::all());
        $questionResponse = QuestionResponse::first();

        $this->assertCount(2, $cookies);
        $this->assertEquals('Some answer two', $questionResponse->response);
        $this->assertEquals('session_SID', $questionResponse->session_sid);
        $this->assertEquals('sms', $questionResponse->type);
        $this->assertEquals($this->questionTwo->id, $questionResponse->question_id);
        $this->assertEquals(
            "That was the last question.\n" .
            "Thank you for participating in this survey.\n" .
            'Good bye.',
            strval($messageDocument->Message)
        );
    }

    public function testUpdateResponseWithTranscription() {
        $questionResponse = $this->questionOne->responses()->create(
            ['response' => 'Some answer',
             'type' => 'voice',
             'session_sid' => 'call_sid']
        );
        $this->assertNull($questionResponse->responseTranscription);

        $response = $this->call(
            'POST',
            route(
                'response.transcription.store',
                ['survey' => $this->survey->id, 'question' => $this->questionOne->id, 'response' => $questionResponse->id]
            ),
            ['TranscriptionText' => 'transcribed answer!',
             'TranscriptionStatus' => 'completed',
             'CallSid' => 'call_sid']
        );
        $questionResponse = $questionResponse->fresh();
        $transcription = $questionResponse->responseTranscription;

        $this->assertNotNull($transcription);
        $this->assertEquals('transcribed answer!', $transcription->transcription);
    }

    public function testUpdateResponseWithTranscriptionError() {
        $questionResponse = $this->questionOne->responses()->create(
            ['response' => 'Some answer',
             'type' => 'voice',
             'session_sid' => 'call_sid']
        );
        $this->assertNull($questionResponse->responseTranscription);

        $response = $this->call(
            'POST',
            route(
                'response.transcription.store',
                ['survey' => $this->survey->id, 'question' => $this->questionOne->id]
            ),
            ['TranscriptionText' => 'Some error occurred',
             'TranscriptionStatus' => 'failed',
             'CallSid' => 'call_sid']
        );
        $questionResponse = $questionResponse->fresh();
        $transcription = $questionResponse->responseTranscription;

        $this->assertNotNull($transcription);
        $this->assertEquals('An error occurred while transcribing the answer', $transcription->transcription);
    }
}
