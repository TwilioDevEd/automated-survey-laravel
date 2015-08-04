<?php

namespace App\Twilio;

use Illuminate\Support\Collection;

class SurveyParser
{
    public function __construct($survey)
    {
        $parsedSurvey = json_decode($survey, true);
        if ($parsedSurvey === null) {
            throw new \Exception("Could not parse survey document");
        }
        $this->survey = collect($parsedSurvey);
    }

    public function title()
    {
        return $this->survey->get('title', 'Untitled survey');
    }

    public function questions()
    {
        return Collection::make($this->survey->get('questions', collect()));
    }
}