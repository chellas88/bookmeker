<?php

namespace App\Jobs;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\ZvonobotController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class Sender implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $params;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        switch ($this->params["system"]) {
            case "sms":
                $sms = new SmsController();
                $sms->sendSms($this->params);
                break;
            case "zvonobot":
                $zvonobot = new ZvonobotController();
                $zvonobot->sendCall($this->params);
                break;
            case "zvonobot_sms":
                $zvonobot = new ZvonobotController();
                $zvonobot->sendSms($this->params);
                break;
        }
    }
}
