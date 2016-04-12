<?php

use App\Twilio\SurveyParser;


class SurveyParserTest extends TestCase
{
    const SAMPLE_SURVEY = <<<EOD
    {"title": "About bears", "questions": [{"body": "What type of bear is best?", "kind": "free-answer"}, {"body": "In a scale of 1 to 10 how cute do you find koalas?", "kind": "numeric"}, {"body": "Do you think bears beat beets?", "kind": "yes-no"}]}
EOD;

    public function testParserTitle()
    {
        $parser = new SurveyParser(self::SAMPLE_SURVEY);
        $this->assertEquals($parser->title(), 'About bears');
    }
    public function testQuestions()
    {
        $parser = new SurveyParser(self::SAMPLE_SURVEY);
        $firstQuestion = ['body' => 'What type of bear is best?',
                          'kind' => 'free-answer'];

        $secondQuestion = ['body' => 'In a scale of 1 to 10 how cute do you find koalas?',
                           'kind' => 'numeric'];

        $thirdQuestion = ['body' => 'Do you think bears beat beets?',
                          'kind' => 'yes-no'];

        $this->assertEquals(
            $parser->questions()->toArray(),
            [$firstQuestion, $secondQuestion, $thirdQuestion]
        );
    }
}
