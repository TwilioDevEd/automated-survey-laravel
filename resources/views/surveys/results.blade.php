@extends('layouts.master')

@section('content')
    <h1>Results for survey</h1>
    <div class="col-md-8">
        <ul class="list-unstyled">
            @foreach ($responses as $response)
                <li>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">Response for: {{ $survey->title }}</h3>
                        </div>
                        <div class="panel-body">
                            @foreach ($response as $question)
                            <ol class="list-unstyled">
                               <li>Question: {{ $question->kind }}</li>
                               <li>Answer type: {{ $question->body }}</li>
                               <li>Response: {{ $question->response }}</li>
                            </ol>
                            @endforeach
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@stop
