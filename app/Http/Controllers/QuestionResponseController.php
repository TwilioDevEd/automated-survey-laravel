<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Question;
use App\QuestionResponse;

class QuestionResponseController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store($questionId, Request $request)
    {
        $question = Question::find($questionId);
        $surveyId = $question->survey->id;
        $newResponse = new QuestionResponse();
        $newResponse->session_sid = $request->input('CallSid');
        $newResponse->type = $request->input('Kind');
        $newResponse->question_id = $questionId;
        $newResponse->response = $this->_responseFromRequest($request);

        $newResponse->save();

        $nextQuestion = $this->_questionAfter($questionId);

        if (is_null($nextQuestion)) {
            return $this->_messageAfterLastQuestion();
        } else {
            $nextQuestionUrl = route('question.show.voice', ['question' => $this->_questionAfter($questionId), 'survey' => $surveyId], false);
            return redirect($nextQuestionUrl)->setStatusCode(303);
        }
    }

    private function _responseFromRequest(Request $request)
    {
        if ($request->input('Kind') === 'voice') {
            return $request->input('RecordingUrl');
        } else {
            return $request->input('Digits');
        }
    }

    private function _messageAfterLastQuestion()
    {
        $voiceResponse = new \Services_Twilio_Twiml();
        $voiceResponse->say('That was the last question');
        $voiceResponse->say('Thank you for participating in this survey');
        $voiceResponse->say('Good-bye');
        $voiceResponse->hangup();

        return $voiceResponse;
    }

    private function _questionAfter($questionId)
    {
        $question = \App\Question::find($questionId);
        $survey = \App\Survey::find($question->survey_id);
        $allQuestions = $survey->questions()->orderBy('id', 'asc')->get();
        $position = $allQuestions->search($question);

        $nextQuestion = $allQuestions->get($position + 1);

        return $this->_idIfNotNull($nextQuestion);
    }

    private function _idIfNotNull($question)
    {
        if (is_null($question)) {
            return null;
        } else {
            return $question->id;
        }
    }
}
