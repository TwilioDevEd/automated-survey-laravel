<?php

namespace App\Twilio;

use Illuminate\Support\Collection;

class SurveyParser
{
    function __construct($survey)
    {
        $parsedSurvey = json_decode($survey, true);
        if ($parsedSurvey === null) {
            throw new \Exception("Could not parse survey document");
        }
        $this->survey = collect($parsedSurvey);
    }

    function title()
    {
        return $this->survey->get('title', 'Untitled survey');
    }

    function questions()
    {
        return $this->survey->get('questions', []);
    }
}