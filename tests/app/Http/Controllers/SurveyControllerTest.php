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

        DB::table('questions')->delete();
        DB::table('surveys')->delete();

        $response = $this->call('GET', '/first_survey');
        $this->assertEquals(404, $response->getStatusCode());
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

    /**
     * GET test question response index
     *
     * @return void
     */
    public function testQuestionSurveyResults()
    {
        $responseDataOne= ['kind' => 'voice', 'response' => '//faketyfake.mp3', 'call_sid' => '4l505up3run1qu3'];
        $responseDataTwo = ['kind' => 'voice', 'response' => '//somefakesound.mp3', 'call_sid' => '5up3run1qu3'];

        $question = new Question(['body' => 'What is this?', 'kind' => 'voice']);
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