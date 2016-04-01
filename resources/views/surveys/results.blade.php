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
                        </div>
                        <div class="panel-body">
                            @foreach ($response as $questionResponse)
                            <ol class="list-group">
                               <li class="list-group-item">Question: {{ $questionResponse->question->body }}</li>
                               <li class="list-group-item">Answer type: {{ $questionResponse->question->kind }}</li>
                               <li class="list-group-item">
                                   @if($questionResponse->question->kind === 'free-answer')
                                       <div class="voice-response">
                                           <span class="voice-response-text">Response:</span>
                                           <i class="fa fa-play-circle fa-2x play-icon"></i>
                                           <audio class="voice-response" src="{{ $questionResponse->response }}"></audio>
                                       </div>
                                   @else
                                       {{ $questionResponse->response }}
                                   @endif
                               </li>
                            </ol>
                            @endforeach
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@stop
