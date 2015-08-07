<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['body', 'kind'];

    public function survey()
    {
        return $this->belongsTo('App\Survey', 'survey_id');
    }

    public function responses()
    {
        return $this->hasMany('App\QuestionResponse');
    }
}
