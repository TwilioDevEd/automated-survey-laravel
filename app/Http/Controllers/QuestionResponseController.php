<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class QuestionResponseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store($questionId, Request $request)
    {
        $newResponse = new \App\QuestionResponse();
        $newResponse->call_sid = $request->input('CallSid');
        $newResponse->kind = $request->input('Kind');
        $newResponse->question_id = $questionId;

        if ($request->input('Kind') === 'voice') {
            $newResponse->response = $request->input('RecordingUrl');
        } else {
            $newResponse->response = $request->input('Digits');
        }
        $newResponse->save();

        $nextQuestion = $this->_questionAfter($questionId);

        if (is_null($nextQuestion)) {
            $voiceResponse = new \Services_Twilio_Twiml();
            $voiceResponse->say('That was the last question');
            $voiceResponse->say('Thank you for participating in this survey');
            $voiceResponse->say('Good-bye');
            $voiceResponse->hangup();

            return $voiceResponse;
        }

        return redirect(route('question.show', ['id' => $this->_questionAfter($questionId)]))
                       ->setStatusCode(303);
    }

    private function _questionAfter($questionId)
    {
        $question = \App\Question::find($questionId);
        $survey = \App\Survey::find($question->survey_id);
        $allQuestions = $survey->questions()->orderBy('id', 'asc')->get();
        $position = $allQuestions->search($question);

        $nextQuestion = $allQuestions->get($position + 1);

        if (is_null($nextQuestion)) {
            return null;
        } else {
            return $nextQuestion->id;
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
