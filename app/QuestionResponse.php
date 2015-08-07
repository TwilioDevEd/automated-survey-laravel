<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuestionResponse extends Model
{
    public function question()
    {
        return $this->belongsTo('App\Question');
    }

    public function scopeResponsesForSurveyByCall($query, $surveyId)
    {
        return $query
            ->join('questions', 'questions.id', '=', 'question_responses.question_id')
            ->join('surveys', 'surveys.id', '=', 'questions.survey_id')
            ->where('surveys.id', '=', $surveyId)
            ->orderBy('question_responses.call_sid')
            ->orderBy('question_responses.id');
    }
}
