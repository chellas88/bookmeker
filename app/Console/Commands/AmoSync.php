<?php

namespace App\Console\Commands;

use App\Http\Controllers\AmoController;
use Illuminate\Console\Command;

class AmoSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amo:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
        $a_c = new AmoController();
        dd($a_c->dev());
        return 0;
    }
}
