<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SurveyControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * GET show TwiML for first survey
     *
     * @return void
     */
    public function testShowTwimlQuestion()
    {
        $appBasePath = base_path();
        Artisan::call(
            'surveys:load', ['fileName' => "$appBasePath/bear_survey.json"]
        );

        $response = $this->call('GET', '/first_survey');
        $this->assertEquals(303, $response->getStatusCode());
        $this->assertTrue($response->isRedirect());
    }
}
