<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class QuestionController extends Controller
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $questionToAsk = \App\Question::find($id);
        return $this->_commandFor($questionToAsk);
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

        $storeResponseURL = route(
            'question.question_response.store',
            ['question_id' => $question->id],
            false
        );

        $voiceResponse->say($this->_messageForQuestion($question));

        if ($question->kind === "voice") {
            $voiceResponse->record(['method' => 'POST', 'action' => $storeResponseURL]);
        } elseif ($question->kind === "yes-no" || $question->kind === "numeric") {
            $voiceResponse->gather(['method' => 'POST', 'action' => $storeResponseURL]);
        }

        return $voiceResponse;
    }
}
