<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Question;
use App\Survey;
use App\QuestionResponse;
use Services_Twilio_Twiml;
use Cookie;

class QuestionResponseController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function storeVoice($surveyId, $questionId, Request $request)
    {
        $question = Question::find($questionId);
        $newResponse = $question->responses()->create(
            ['response' => $this->_responseFromRequest($question, $request),
             'type' => 'voice',
             'session_sid' => $request->input('CallSid')]
        );

        $nextQuestion = $this->_questionAfter($question);

        if (is_null($nextQuestion)) {
            return $this->_voiceMessageAfterLastQuestion();
        } else {
            return $this->_responseWithXmlType(
                $this->_redirectToQuestion($nextQuestion, 'question.show.voice')
            );
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function storeSms($surveyId, $questionId, Request $request)
    {
        $question = Question::find($questionId);
        $newResponse = $question->responses()->create(
            ['response' => $request->input('Body'),
             'type' => 'text',
             'session_sid' => $request->cookie('survey_session')]
        );

        $nextQuestion = $this->_questionAfter($question);

        if (is_null($nextQuestion)) {
            return $this->_responseWithXmlType($this->_smsMessageAfterLastQuestion());
        } else {
            return $this->_responseWithXmlType(
                $this->_redirectToQuestion($nextQuestion, 'question.show.sms')
            );
        }
    }

    private function _smsMessageAfterLastQuestion() {
        $messageResponse = new Services_Twilio_Twiml();
        $messageResponse->message(
            "That was the last question.\n" .
            "Thank you for participating in this survey.\n" .
            'Good bye.'
        );
        return response($messageResponse)
                   ->withCookie(Cookie::forget('survey_session'))
                   ->withCookie(Cookie::forget('current_question'));
    }

    private function _redirectToQuestion($question, $route) {
        $questionUrl = route(
            $route,
            ['question' => $question->id, 'survey' => $question->survey->id]
        );
        $redirectResponse = new Services_Twilio_Twiml();
        $redirectResponse->redirect($questionUrl, ['method' => 'GET']);

        return response($redirectResponse);
    }

    private function _responseFromRequest($question, $request)
    {
        if ($question->kind === 'voice') {
            return $request->input('RecordingUrl');
        } else {
            return $request->input('Digits');
        }
    }

    private function _voiceMessageAfterLastQuestion()
    {
        $voiceResponse = new Services_Twilio_Twiml();
        $voiceResponse->say('That was the last question');
        $voiceResponse->say('Thank you for participating in this survey');
        $voiceResponse->say('Good-bye');
        $voiceResponse->hangup();

        return $voiceResponse;
    }

    private function _questionAfter($question)
    {
        $survey = Survey::find($question->survey_id);
        $allQuestions = $survey->questions()->orderBy('id', 'asc')->get();
        $position = $allQuestions->search($question);
        $nextQuestion = $allQuestions->get($position + 1);
        return $nextQuestion;
    }

    private function _responseWithXmlType($response) {
        return $response->header('Content-Type', 'application/xml');
    }
}
