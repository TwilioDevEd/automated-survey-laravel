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
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return response()->view('surveys.create', []);
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
        $surveyToTake = \App\Survey::find($id);
        $voiceResponse = new \Services_Twilio_Twiml();

        if (is_null($surveyToTake)) {
            $voiceResponse->say('Could not find the survey to take');
            $voiceResponse->say('Good-bye');
            $voiceResponse->hangup();

            $response = new Response($voiceResponse, 404);

            return $response;
        }

        $surveyTitle = $surveyToTake->title;

        $voiceResponse->say("Hello and thank you for taking the $surveyTitle survey!");
        $voiceResponse->say($surveyToTake->title);

        return $voiceResponse;
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
