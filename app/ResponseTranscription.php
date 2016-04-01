<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResponseTranscription extends Model
{
    protected $fillable = ['transcription'];

    public function questionResponse()
    {
        return $this->belongsTo('App\QuestionResponse');
    }
}
