<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LoadSurveys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'surveys:load {fileName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load surveys into the database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $filename = $this->argument('fileName');
        $surveyJSON = file_get_contents($filename);

        $parser = new \App\Twilio\SurveyParser($surveyJSON);

        $survey = new \App\Survey();
        $survey->title = $parser->title();
        $survey->save();

        $parser->questions()->each(
            function ($question) use ($survey) {
                $questionToSave = new \App\Question($question);
                $questionToSave->survey()->associate($survey);
                $questionToSave->save();
            }
        );
    }
}
