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
    'survey/{survey}/results',
    ['as' => 'survey.results', 'uses' => 'SurveyController@showResults']
);
Route::get(
    '/',
    ['as' => 'approot', 'uses' => 'SurveyController@showFirstSurveyResults']
);
Route::post(
    '/first_survey',
    ['as' => 'survey.first_survey', 'uses' => 'SurveyController@showFirstSurvey']
);
Route::resource(
    'survey', 'SurveyController',
    ['only' => ['index', 'show']]
);
Route::resource(
    'question', 'QuestionController',
    ['only' => ['show']]
);
Route::resource(
    'question.question_response', 'QuestionResponseController',
    ['only' => ['store']]
);
