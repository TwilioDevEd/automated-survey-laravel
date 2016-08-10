<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Survey;
use App\QuestionResponse;

use Twilio\Twiml;

class SurveyController extends Controller
{
    const START_SMS_SURVEY_COMMAND = 'start';

    public function showResults($surveyId)
    {
        $survey = Survey::find($surveyId);
        $responsesByCall = QuestionResponse::responsesForSurveyByCall($surveyId)
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
        $firstSurvey = $this->_getFirstSurvey();
        return redirect(route('survey.results', ['survey' => $firstSurvey->id]))
                ->setStatusCode(303);
    }

    public function connectVoice()
    {
        $response = new Twiml();
        $redirectResponse = $this->_redirectWithFirstSurvey('survey.show.voice', $response);
        return $this->_responseWithXmlType($redirectResponse);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function showVoice($id)
    {
        $surveyToTake = Survey::find($id);
        $voiceResponse = new Twiml();

        if (is_null($surveyToTake)) {
            return $this->_responseWithXmlType($this->_noSuchVoiceSurvey($voiceResponse));
        }
        $surveyTitle = $surveyToTake->title;
        $voiceResponse->say("Hello and thank you for taking the $surveyTitle survey!");
        $voiceResponse->redirect($this->_urlForFirstQuestion($surveyToTake, 'voice'), ['method' => 'GET']);

        return $this->_responseWithXmlType(response($voiceResponse));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function showSms($id)
    {
        $surveyToTake = Survey::find($id);
        $voiceResponse = new Twiml();

        if (is_null($surveyToTake)) {
            return $this->_responseWithXmlType($this->_noSuchSmsSurvey($voiceResponse));
        }

        $surveyTitle = $surveyToTake->title;
        $voiceResponse->message("Hello and thank you for taking the $surveyTitle survey!");
        $voiceResponse->redirect($this->_urlForFirstQuestion($surveyToTake, 'sms'), ['method' => 'GET']);

        return $this->_responseWithXmlType(response($voiceResponse));
    }

    public function connectSms(Request $request)
    {
        $response = $this->_getNextSmsStepFromCookies($request);
        return $this->_responseWithXmlType($response);
    }

    private function _getNextSmsStepFromCookies($request) {
        $response = new Twiml();
        if (strtolower(trim($request->input('Body'))) === self::START_SMS_SURVEY_COMMAND) {
            $messageSid = $request->input('MessageSid');

            return $this->_redirectWithFirstSurvey('survey.show.sms', $response)
                        ->withCookie('survey_session', $messageSid);
        }

        $currentQuestion = $request->cookie('current_question');
        $surveySession = $request->cookie('survey_session');

        if ($this->_noActiveSurvey($currentQuestion, $surveySession)) {
            return $this->_smsSuggestCommand($response);
        }

        return $this->_redirectToStoreSmsResponse($response, $currentQuestion);
    }

    private function _redirectWithFirstSurvey($routeName, $response)
    {
        $firstSurvey = $this->_getFirstSurvey();

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
        return response($response);
    }

    private function _noActiveSurvey($currentQuestion, $surveySession) {
        $noCurrentQuestion = is_null($currentQuestion) || $currentQuestion == 'deleted';
        $noSurveySession = is_null($surveySession) || $surveySession == 'deleted';

        return $noCurrentQuestion || $noSurveySession;
    }

    private function _redirectToStoreSmsResponse($response, $currentQuestion) {
        $firstSurvey = $this->_getFirstSurvey();
        $storeRoute = route('response.store.sms', ['survey' => $firstSurvey->id, 'question' => $currentQuestion]);
        $response->redirect($storeRoute, ['method' => 'POST']);

        return response($response);
    }

    private function _smsSuggestCommand($response) {
        $response->message('You have no active surveys. Reply with "Start" to begin.');
        return response($response);
    }

    private function _noSuchSmsSurvey($messageResponse)
    {
        $messageResponse->message('Sorry, we could not find the survey to take. Good-bye');
        return response($messageResponse);
    }

    private function _urlForFirstQuestion($survey, $routeType)
    {
        return route(
            'question.show.' . $routeType,
            ['survey' => $survey->id,
             'question' => $survey->questions()->orderBy('id')->first()->id]
        );
    }

    private function _noSuchVoiceSurvey($voiceResponse)
    {
        $voiceResponse->say('Sorry, we could not find the survey to take');
        $voiceResponse->say('Good-bye');
        $voiceResponse->hangup();

        return response($voiceResponse);
    }

    private function _getFirstSurvey() {
        return Survey::orderBy('id', 'DESC')->get()->first();
    }

    private function _responseWithXmlType($response) {
        return $response->header('Content-Type', 'application/xml');
    }
}
