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
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $allSurveys = Survey::all();
        return response()->view('surveys.index', ['surveys' => $allSurveys]);
    }

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
                         ->groupBy('call_sid')
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
        $voiceResponse->say('Could not find the survey to take');
        $voiceResponse->say('Good-bye');
        $voiceResponse->hangup();

        $response = new Response($voiceResponse, 404);

        return $response;
    }

    private function _messageForQuestion($kind)
    {
        $questionPhrases = collect(
            [
                "voice"   => "Please record your answer after the beep and then the pound sign",
                "yes-no"  => "Please press the one key for yes and the zero key for no and then the pound sign",
                "numeric" => "Please press a number between 1 and 10 and then the pound sign"
            ]
        );

        return $questionPhrases->key($kind, "Please press a number and then the pound sign");
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
