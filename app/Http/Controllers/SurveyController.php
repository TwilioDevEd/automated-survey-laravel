<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Survey;

class SurveyController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $surveyToTake = \App\Survey::find($id);
        $voiceResponse = new \Services_Twilio_Twiml();

        if (is_null($surveyToTake)) {
            return $this->_noSuchSurvey($voiceResponse);
        }

        $surveyTitle = $surveyToTake->title;
        $voiceResponse->say("Hello and thank you for taking the $surveyTitle survey!");
        $voiceResponse->redirect($this->_urlForFirstQuestion($surveyToTake), ['method' => 'GET']);

        return $voiceResponse;
    }

    public function showResults($surveyId)
    {
        $survey = \App\Survey::find($surveyId);
        $responsesByCall = \App\QuestionResponse::responsesForSurveyByCall($surveyId)
                         ->get()
                         ->groupBy('session_sid')
                         ->values();

        return response()->view(
            'surveys.results',
            ['survey' => $survey, 'responses' => $responsesByCall]
        );
    }

    public function showFirstSurveyResults()
    {
        return $this->_redirectWithFirstSurvey('survey.results');
    }

    public function showFirstSurvey()
    {
        return $this->_redirectWithFirstSurvey('survey.show');
    }

    private function _urlForFirstQuestion($survey)
    {
        return route(
            'question.show',
            ['id' => $survey->questions()->first()],
            false
        );
    }

    private function _noSuchSurvey($voiceResponse)
    {
        $voiceResponse->say('Sorry, we could not find the survey to take');
        $voiceResponse->say('Good-bye');
        $voiceResponse->hangup();

        $response = new Response($voiceResponse);

        return $response;
    }

    private function _redirectWithFirstSurvey($routeName)
    {
        $firstSurvey = Survey::first();

        if (is_null($firstSurvey)) {
            $voiceResponse = new \Services_Twilio_Twiml();
            return $this->_noSuchSurvey($voiceResponse);
        }

        return redirect(route($routeName, ['id' => $firstSurvey->id]))
                                                ->setStatusCode(303);
    }
}
