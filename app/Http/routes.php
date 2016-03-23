<?php

use Illuminate\Http\RedirectResponse;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get(
    '/survey/{survey}/results',
    ['as' => 'survey.results', 'uses' => 'SurveyController@showResults']
);
Route::get(
    '/',
    ['as' => 'root', 'uses' => 'SurveyController@showFirstSurveyResults']
);
Route::post(
    '/voice/connect',
    ['as' => 'voice.connect', 'uses' => 'SurveyController@connectVoice']
);
Route::post(
    '/sms/connect',
    ['as' => 'sms.connect', 'uses' => 'SurveyController@connectSms']
);
Route::get(
    '/survey/{survey}/voice',
    ['as' => 'survey.show.voice', 'uses' => 'SurveyController@showVoice']
);
Route::get(
    '/survey/{survey}/sms',
    ['as' => 'survey.show.sms', 'uses' => 'SurveyController@showSms']
);
Route::resource(
    'question', 'QuestionController',
    ['only' => ['show']]
);
Route::resource(
    'question.question_response', 'QuestionResponseController',
    ['only' => ['store']]
);
