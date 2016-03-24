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
     * GET redirects to first voice survey
     *
     * @return void
     */
    public function testRedirectToFirstSmsSurvey()
    {
        $response = $this->call('POST', '/sms/connect');
        $this->assertEquals(200, $response->getStatusCode());

        $redirectDocument = new SimpleXMLElement($response->getContent());

        $this->assertContains(route('survey.show.sms', ['id' => $this->firstSurvey->id]), strval($redirectDocument->Redirect));
        $this->assertEquals('GET', strval($redirectDocument->Redirect->attributes()['method']));
    }

}
