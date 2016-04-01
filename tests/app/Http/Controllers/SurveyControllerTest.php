<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use \App\QuestionResponse;
use \App\Question;

class SurveyControllerTest extends TestCase
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

        $appBasePath = base_path();
        Artisan::call(
            'surveys:load', ['fileName' => "$appBasePath/bear_survey.json"]
        );

        $this->firstSurvey = \App\Survey::all()->first();
    }

    /**
     * GET redirects to first voice survey
     *
     * @return void
     */
    public function testRedirectToFirstVoiceSurvey()
    {
        $response = $this->call('POST', '/voice/connect');
        $this->assertEquals(200, $response->getStatusCode());

        $redirectDocument = new SimpleXMLElement($response->getContent());
        $this->assertContains(route('survey.show.voice', ['id' => $this->firstSurvey->id]), strval($redirectDocument->Redirect));
        $this->assertEquals('GET', strval($redirectDocument->Redirect->attributes()['method']));
    }

    /**
     * GET redirects to first sms survey
     *
     * @return void
     */
    public function testRedirectToFirstSmsSurvey()
    {
        $response = $this->call('POST', '/sms/connect', ['Body' => 'Start', 'MessageSid' => 'unique_SID']);
        $this->assertEquals(200, $response->getStatusCode());

        $redirectDocument = new SimpleXMLElement($response->getContent());
        $cookies = $response->headers->getCookies();

        $this->assertCount(1, $cookies);
        $this->assertEquals('survey_session', $cookies[0]->getName());
        $this->assertEquals('unique_SID', $cookies[0]->getValue());
        $this->assertContains(route('survey.show.sms', ['id' => $this->firstSurvey->id]), strval($redirectDocument->Redirect));
        $this->assertEquals('GET', strval($redirectDocument->Redirect->attributes()['method']));
    }

    public function testSuggestCommandWhenNoSurveyIsStarted() {
        $response = $this->call('POST', '/sms/connect', ['Body' => 'not start command']);
        $this->assertEquals(200, $response->getStatusCode());
        $replyDocument = new SimpleXMLElement($response->getContent());

        $this->assertEquals('You have no active surveys. Reply with "Start" to begin.', strval($replyDocument->Message));
    }

    public function testRedirectToStoreSmsAnswer()
    {
        $response = $this->call(
            'POST',
            '/sms/connect',
            ['Body' => 'Some answer'],
            ['survey_session' => 'message_sid', 'current_question' => '1']);

        $this->assertEquals(200, $response->getStatusCode());
        $redirectDocument = new SimpleXMLElement($response->getContent());

        $this->assertContains(
            route('response.store.sms', ['survey' => $this->firstSurvey->id, 'question' => 1]),
            strval($redirectDocument->Redirect)
        );
        $this->assertEquals('POST', strval($redirectDocument->Redirect->attributes()['method']));
    }

    /**
     * GET test voice welcome response
     *
     * @return void
     */
    public function testVoiceSurveyWelcomeResponse()
    {
        $response = $this->call(
            'GET',
            route('survey.show.voice', ['id' => $this->firstSurvey->id])
        );

        $welcomeDocument = new SimpleXMLElement($response->getContent());
        $surveyTitle = $this->firstSurvey->title;

        $this->assertEquals("Hello and thank you for taking the $surveyTitle survey!", strval($welcomeDocument->Say));
        $this->assertContains(
            route(
                'question.show.voice',
                ['survey' => $this->firstSurvey->id, 'question' => $this->firstSurvey->questions()->first()->id]
            ),
            strval($welcomeDocument->Redirect)
        );
    }

    /**
     * GET test SMS welcome response
     *
     * @return void
     */
    public function testSmsSurveyWelcomeResponse()
    {
        $response = $this->call(
            'GET',
            route('survey.show.sms', ['id' => $this->firstSurvey->id])
        );

        $welcomeDocument = new SimpleXMLElement($response->getContent());
        $surveyTitle = $this->firstSurvey->title;

        $this->assertEquals("Hello and thank you for taking the $surveyTitle survey!", strval($welcomeDocument->Message));
        $this->assertContains(
            route(
                'question.show.sms',
                ['survey' => $this->firstSurvey->id, 'question' => $this->firstSurvey->questions()->first()->id]
            ),
            strval($welcomeDocument->Redirect)
        );
    }

    public function testSmsSurveyWelcomeResponseNoSurvey()
    {
        // Only one survey exists for testing. Trying to get survey id + 1 has no match
        $response = $this->call(
            'GET',
            route('survey.show.sms', ['id' => $this->firstSurvey->id + 1])
        );
        $welcomeDocument = new SimpleXMLElement($response->getContent());

        $this->assertEquals('Sorry, we could not find the survey to take. Good-bye', strval($welcomeDocument->Message));
    }

    /**
     * GET test question response index
     *
     * @return void
     */
    public function testQuestionSurveyResults()
    {
        $responseDataOne= ['type' => 'voice', 'response' => '//faketyfake.mp3', 'session_sid' => '4l505up3run1qu3'];
        $responseDataTwo = ['type' => 'voice', 'response' => '//somefakesound.mp3', 'session_sid' => '5up3run1qu3'];

        $question = new Question(['body' => 'What is this?', 'kind' => 'free-answer']);
        $question->survey()->associate($this->firstSurvey);
        $question->save();

        $question->responses()->createMany([$responseDataOne, $responseDataTwo]);

        $question->push();

        $response = $this->call(
            'GET',
            route('survey.results', ['id' => $this->firstSurvey->id])
        );

        $this->assertEquals($response->original['responses']->count(), 2);

        $actualResponseOne = $response->original['responses']->get(0)->toArray()[0];
        $actualResponseTwo = $response->original['responses']->get(1)->toArray()[0];

        $this->assertArraySubset($responseDataOne, $actualResponseOne);
        $this->assertArraySubset($responseDataTwo, $actualResponseTwo);
    }
}
