<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class PrepareHeroku extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'heroku:prepare {fileName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate the database and load an initial survey';

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

        Artisan::call('migrate');
        $this->info('Database migrated');

        Artisan::call(
            'surveys:load', ['fileName' => $fileName]
        );
        $this->info('Survey loaded into database');
    }
}
