<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'surveys:load';

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
        $appointmentReminder = new \App\AppointmentReminders\AppointmentReminder();
        $appointmentReminder->sendReminders();
    }
}
