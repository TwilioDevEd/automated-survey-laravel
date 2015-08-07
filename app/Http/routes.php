<?php

use Illuminate\Http\RedirectResponse;
use App\Survey;

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
    '/', function () {
        return view('welcome');
    }
);

Route::get(
    '/first_survey', function () {
        $firstSurveyId = Survey::all()->first()->id;
        return redirect(route('survey.show', ['id' => $firstSurveyId]))
                       ->setStatusCode(303);
    }
);
Route::get(
    'survey/{survey}/results',
    ['as' => 'survey.results', 'uses' => 'SurveyController@showResults']
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