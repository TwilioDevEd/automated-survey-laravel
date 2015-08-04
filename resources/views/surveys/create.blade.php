@extends('layouts.master')

@section('content')
    <div class="col-md-4">
        <h1>Create a new survey</h1>
        {!! Form::open(['url' => '/survey', 'files' => true]) !!}
        <div class="form-group">
            {!! Form::label('surveyName', 'Survey name') !!}
            {!! Form::text('surveyName', '', ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('surveyFile', 'Survey file') !!}
            {!! Form::file('survey') !!}
        </div>
        {!! Form::close() !!}
    </div>
@stop
