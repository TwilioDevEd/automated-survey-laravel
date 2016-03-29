<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class QuestionController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function showVoice($surveyId, $questionId)
    {
        $questionToAsk = \App\Question::find($questionId);
        return $this->_commandFor($questionToAsk);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function showSms($surveyId, $questionId)
    {
        return response('<Response><Message>You reached the first question</Message></Response>');
    }

    private function _messageForQuestion($question)
    {
        $questionPhrases = collect(
            [
                "voice"   => "Please record your answer after the beep and then hit the pound sign",
                "yes-no"  => "Please press the one key for yes and the zero key for no and then hit the pound sign",
                "numeric" => "Please press a number between 1 and 10 and then hit the pound sign"
            ]
        );

        return $questionPhrases->get($question->kind, "Please press a number and then the pound sign");
    }

    private function _commandFor($question)
    {
        $voiceResponse = new \Services_Twilio_Twiml();

        $voiceResponse->say($question->body);
        $voiceResponse->say($this->_messageForQuestion($question));
        $voiceResponse = $this->_registerResponseCommand($voiceResponse, $question);

        return $voiceResponse;
    }

    private function _registerResponseCommand($voiceResponse, $question)
    {
        $storeResponseURL = route(
            'response.store.voice',
            ['question' => $question->id,
             'survey' => $question->survey->id],
            false
        );

        if ($question->kind === 'voice') {
            $voiceResponse->record(['method' => 'POST', 'action' => $storeResponseURL . '?Kind=voice']);
        } elseif ($question->kind === "yes-no") {
            $voiceResponse->gather(['method' => 'POST', 'action' => $storeResponseURL . '?Kind=yes-no']);
        } elseif ($question->kind === "numeric") {
            $voiceResponse->gather(['method' => 'POST', 'action' => $storeResponseURL . '?Kind=numeric']);
        }
        return $voiceResponse;
    }

}
