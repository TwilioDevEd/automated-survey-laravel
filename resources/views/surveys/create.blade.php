@extends('layouts.master')

@section('content')
    <h1>Create a new survey</h1>
    {!! Form::open(array('url' => '/survey', 'files' => true)) !!}
    {!! Form::file('survey') !!}
    {!! Form::close() !!}
@stop
