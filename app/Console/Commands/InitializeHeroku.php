<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InitializeHeroku extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'heroku:initialize {fileName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Forces a database migration and loads an initial survey';

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
        $fileName = $this->argument('fileName');

        Artisan::call('migrate', ['--force' => 1]);
        $this->info('Database migrated');

        Artisan::call(
            'surveys:load', ['fileName' => $fileName]
        );
        $this->info('Survey loaded into database');
    }
}
