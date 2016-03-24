<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Survey;

use Services_Twilio_Twiml;

class SurveyController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function showVoice($id)
    {
        $surveyToTake = Survey::find($id);
        $voiceResponse = new Services_Twilio_Twiml();

        if (is_null($surveyToTake)) {
            return $this->_noSuchVoiceSurvey($voiceResponse);
        }

        $surveyTitle = $surveyToTake->title;
        $voiceResponse->say("Hello and thank you for taking the $surveyTitle survey!");
        $voiceResponse->redirect($this->_urlForFirstQuestion($surveyToTake), ['method' => 'GET']);

        return $voiceResponse;
    }

    public function showSms($id)
    {
        return 'TwiML';
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

    public function connectVoice()
    {
        $redirectResponse = $this->_redirectWithFirstSurvey('survey.show.voice');
        return response($redirectResponse)->header('Content-Type', 'application/xml');
    }

    public function connectSms()
    {
        $redirectResponse = $this->_redirectWithFirstSurvey('survey.show.sms');
        return response($redirectResponse)->header('Content-Type', 'application/xml');
    }

    private function _urlForFirstQuestion($survey)
    {
        return route(
            'question.show',
            ['id' => $survey->questions()->first()],
            false
        );
    }

    private function _noSuchVoiceSurvey($voiceResponse)
    {
        $voiceResponse->say('Sorry, we could not find the survey to take');
        $voiceResponse->say('Good-bye');
        $voiceResponse->hangup();

        $response = new Response($voiceResponse);

        return $response;
    }

    private function _redirectWithFirstSurvey($routeName)
    {
        $response = new Services_Twilio_Twiml();
        $firstSurvey = Survey::first();

        if (is_null($firstSurvey)) {
            if ($routeName === 'survey.show.voice') {
                return $this->_noSuchVoiceSurvey($response);
            }
            return $this->_noSuchSmsSurvey($response);
        }

        $response->redirect(
            route($routeName, ['id' => $firstSurvey->id]),
            ['method' => 'GET']
        );
        return $response;
    }
}
