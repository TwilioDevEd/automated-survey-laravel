@extends('layouts.master')

@section('content')
<h1>Results for survey: {{ $survey->title }}</h1>
<div class="col-md-12">
    <ul class="list-unstyled">
        @foreach ($responses as $response)
        <li>
            <div class="panel panel-default">
                <div class="panel-heading">
                    Response from: {{ $response->first()->session_sid }}
                    </br>
                    Survey type:
                    @if($response->first()->type == 'voice')
                    <span class="label label-primary">
                    @else
                    <span class="label label-success">
                    @endif
                        {{ $response->first()->type }}
                    </span>
                </div>
                <div class="panel-body">
                    @foreach ($response as $questionResponse)
                    <ol class="list-group">
                        <li class="list-group-item">Question: {{ $questionResponse->question->body }}</li>
                        <li class="list-group-item">Answer type: {{ $questionResponse->question->kind }}</li>
                        <li class="list-group-item">
                            @if($questionResponse->question->kind === 'free-answer' && $questionResponse->type === 'voice')
                            <div class="voice-response">
                                <span class="voice-response-text">Response:</span>
                                <i class="fa fa-play-circle fa-2x play-icon"></i>
                                <audio class="voice-response" src="{{ $questionResponse->response }}"></audio>
                            </div>
                            @elseif($questionResponse->question->kind === 'yes-no')
                                @if($questionResponse->response == 1)
                                YES
                                @else
                                NO
                                @endif
                            @else
                            {{ $questionResponse->response }}
                            @endif
                        </li>
                        @if(!is_null($questionResponse->transcription))
                        <li class="list-group-item">Transcribed Answer: {{ $questionResponse->transcription }}</li>
                        @endif
                    </ol>
                    @endforeach
                </div>
            </div>
        </li>
        @endforeach
    </ul>
</div>
@stop
