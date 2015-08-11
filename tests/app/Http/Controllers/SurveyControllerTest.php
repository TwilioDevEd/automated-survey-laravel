<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

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
     * GET redirects to first survey
     *
     * @return void
     */
    public function testRedirectToFirstSurvey()
    {
        $response = $this->call('GET', '/first_survey');
        $this->assertEquals(303, $response->getStatusCode());
        $this->assertTrue($response->isRedirect());

        $this->assertEquals(
            route('survey.show', ['id' => $this->firstSurvey->id]),
            $response->headers->get('Location')
        );
    }

    /**
     * GET test welcome response
     *
     * @return void
     */
    public function testSurveyWelcomeResponse()
    {
        $response = $this->call(
            'GET',
            route('survey.show', ['id' => $this->firstSurvey->id])
        );

        $firstQuestion = $this->firstSurvey->questions()->first();
        $absoluteUrl = route('question.show', ['id' => $firstQuestion]);
        $relativeUrl = route('question.show', ['id' => $firstQuestion], false);

        $this->assertNotContains($absoluteUrl, $response->content());
        $this->assertContains($relativeUrl, $response->content());

        $this->assertContains($this->firstSurvey->title, $response->content());
    }
}
