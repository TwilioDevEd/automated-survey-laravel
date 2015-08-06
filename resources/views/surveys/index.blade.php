@extends('layouts.master')

@section('content')
    <h1>Existing surveys</h1>
    <div class="col-md-6">
        <ul class="list-unstyled">
            @foreach ($surveys as $survey)
                <li>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{ $survey->title }}</h3>
                        </div>
                        <div class="panel-body">
                            <ol class="list-unstyled">
                                @foreach ($survey->questions()->get() as $question)
                                    <li class="list-group-item">
                                        <ul>
                                            <li>Question: {{ $question->body }}</li>
                                            <li>Answer type: {{ $question->kind }}</li>
                                        </ul>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                        <div class="panel-footer">
                            <a class="btn btn-xs btn-default" href="{{ route('survey.show', ['id' => $survey->id]) }}">See answers for survey!</a>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@stop
