<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    protected $fillable = ['title'];

    public function questions()
    {
        return $this->hasMany('App\Question');
    }
}
