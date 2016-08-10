<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Question;
use Twilio\Twiml;

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
        $questionToAsk = Question::find($questionId);
        return $this->_responseWithXmlType($this->_commandForVoice($questionToAsk));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function showSms($surveyId, $questionId)
    {
        $questionToAsk = Question::find($questionId);
        return $this->_responseWithXmlType($this->_commandForSms($questionToAsk));
    }

    private function _messageForSmsQuestion($question) {
        $questionPhrases = collect(
            [
                'free-answer' => "\n\nReply to this message with your answer",
                'yes-no'      => "\n\nReply with \"YES\" or \"NO\" to this message",
                'numeric'     => "\n\nReply with a number from 1 to 10 to this message"
            ]
        );

        return $questionPhrases->get($question->kind, "\n\nReply to this message with your answer");
    }

    private function _messageForVoiceQuestion($question)
    {
        $questionPhrases = collect(
            [
                'free-answer' => "Please record your answer after the beep and then hit the pound sign",
                'yes-no'      => "Please press the one key for yes and the zero key for no and then hit the pound sign",
                'numeric'     => "Please press a number between 1 and 10 and then hit the pound sign"
            ]
        );

        return $questionPhrases->get($question->kind, "Please press a number and then the pound sign");
    }

    private function _commandForSms($question)
    {
        $smsResponse = new Twiml();

        $messageBody = $question->body . $this->_messageForSmsQuestion($question);
        $smsResponse->message($messageBody);

        return response($smsResponse)->withCookie('current_question', $question->id);
    }

    private function _commandForVoice($question)
    {
        $voiceResponse = new Twiml();

        $voiceResponse->say($question->body);
        $voiceResponse->say($this->_messageForVoiceQuestion($question));
        $voiceResponse = $this->_registerResponseCommand($voiceResponse, $question);

        return response($voiceResponse);
    }

    private function _registerResponseCommand($voiceResponse, $question)
    {
        $storeResponseURL = route(
            'response.store.voice',
            ['question' => $question->id,
             'survey' => $question->survey->id],
            false
        );

        if ($question->kind === 'free-answer') {
            $transcribeUrl = route(
                'response.transcription.store',
                ['question' => $question->id,
                 'survey' => $question->survey->id]
            );
            $voiceResponse->record(
                ['method' => 'POST',
                 'action' => $storeResponseURL,
                 'transcribe' => true,
                 'transcribeCallback' => $transcribeUrl]
            );
        } elseif ($question->kind === 'yes-no') {
            $voiceResponse->gather(['method' => 'POST', 'action' => $storeResponseURL]);
        } elseif ($question->kind === 'numeric') {
            $voiceResponse->gather(['method' => 'POST', 'action' => $storeResponseURL]);
        }
        return $voiceResponse;
    }

    private function _responseWithXmlType($response) {
        return $response->header('Content-Type', 'application/xml');
    }
}
