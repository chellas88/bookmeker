<?php

namespace App\Http\Controllers;

use Adminka\AmoCRM\AmoOAuth;
use App\AdminkaLibs\IntelTele;
use App\AdminkaLibs\Zvonobot;
use App\Models\AmoApps;
use App\Models\ZvonobotRecords;
use Illuminate\Http\Request;
use App\Models\AmoTriggers;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $trigers = null;
        $delay = null;
        $data = AmoTriggers::select()->get();
        foreach ($data as $item) {
            if ($item['delay_type'] === 'now') {
                $delay = 'Сразу';
            } else if ($item['delay_type'] === 'minutes') {
                $delay = 'Через ' . $item['delay_time'] . ' минут';
            } else if ($item['delay_type'] === 'hours') {
                $delay = 'Через ' . $item['delay_time'] . ' часов';
            } else if ($item['delay_type'] === 'days') {
                $delay = 'Через ' . $item['delay_time'] . ' дней';
            }
            if ($item['send_sms'] == 1) {
                $send_sms = "Вкл.";
            } else {
                $send_sms = "Выкл.";
            }
            if ($item['event'] == 'add'){
                $event = 'Создание лида';
            }
            else if ($item['event'] == 'status'){
                $event = 'Изменение статуса';
            }
            $trigers[] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'event' => $event,
                'delay_type' => $delay,
                'pipeline_name' => $item['pipeline_name'],
                'status_name' => $item['status_name'],
                'record_name' => $item['record_name'],
                'send_sms' => $send_sms,
                'is_active' => $item['is_active']
            ];
        }
        return view('home', ['data' => $trigers]);
    }

    public function addAudioRecord(Request $request)
    {
        $check = ZvonobotRecords::where('name', $request['name'])->get();
        if (isset($check[0])){
            return 'error';
        }
        else {
            $res = ZvonobotRecords::insert([
                'name' => $request['name'],
                'id' => $request['id'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
            return 'success';
        }
    }

    public function saveZvonobotTriger(Request $request)
    {


        $triger_id = AmoTriggers::insertGetId([
            'name' => $request['name'],
            'sender_id' => $request['sender_id'],
            'sender_name' => $request['sender_name'],
            'event' => $request['event'],
            'delay_type' => $request['delay_type'],
            'delay_time' => $request['delay_time'],
            'pipeline_id' => $request['pipeline_id'],
            'pipeline_name' => $request['pipeline_name'],
            'status_id' => $request['status_id'],
            'status_name' => $request['status_name'],
            'record_id' => $request['record_id'],
            'record_name' => $request['record_name'],
            'send_sms' => $request['send_sms'],
            'sms_text' => $request['sms_text'],
            'sms_url' => $request['sms_url'],
            'is_active' => $request['is_active'],
            'created_at' => now(),
            'updated_at' => now(),
            'is_ivr' => $request['is_ivr'],
            'is_sec_record' => $request['is_sec_record'],
            'sec_record_id' => $request['sec_record_id'],
            'sec_record_name' => $request['sec_record_name'],
            'sec_record_digit' => $request['sec_record_digit']
        ]);

        return $request;
    }

    public function openZvonobotTriger(Request $request)
    {
        $triger_data = AmoTriggers::find($request['triger_id']);
        return $triger_data;
    }

    public function updateZvonobotTriger(Request $request)
    {
        if ($request['is_callback'] == 1){
            $callback_url = 'https://'.$_SERVER['SERVER_NAME'].'/zvonobot/hook?tid='.$request['id'].'&lid={lead_id}';
            AmoTriggers::where('id', $request['id'])->update(['callback_url' => $callback_url]);

        }
        else {
            $callback_url = null;
        }


        AmoTriggers::where('id', $request['id'])->update([
            'name' => $request['name'],
            'sender_id' => $request['sender_id'],
            'event' => $request['event'],
            'delay_type' => $request['delay_type'],
            'delay_time' => $request['delay_time'],
            'pipeline_id' => $request['pipeline_id'],
            'pipeline_name' => $request['pipeline_name'],
            'status_id' => $request['status_id'],
            'status_name' => $request['status_name'],
            'record_id' => $request['record_id'],
            'record_name' => $request['record_name'],
            'send_sms' => $request['send_sms'],
            'sms_text' => $request['sms_text'],
            'sms_url' => $request['sms_url'],
            'is_active' => $request['is_active'],
            'updated_at' => now(),
            'is_sec_record' => $request['is_audio'],
            'sec_record_id' => $request['sec_record_id'],
            'sec_record_name' => $request['sec_record_name'],
            'sec_record_digit' => $request['digit'],
            'sender_name' => $request['sender_name'],
            'is_ivr' => $request['is_ivr']

        ]);
        return 'success';
    }

    public
    function amoAuth()
    {
        $amo_apps = new AmoApps();
        $auth_data = $amo_apps->getAuthData(1);
        $amo_auth = new AmoOAuth($auth_data);
        $url = $amo_auth->getRedirectUrl(1);
//        dd($url);
        echo '<a href="' . $url . '">Войти в Амо</a>';

    }

    public
    function getAmoToken(Request $request)
    {
        if (isset($request['code'])) {
            $amo_apps = new AmoApps();
            $auth_data = $amo_apps->getAuthData($request['state']);
            $amo_auth = new AmoOAuth($auth_data);

            $amo_auth->setExternalSaveToken(function ($token_data) {
                $this->saveToken($token_data);

            });
            $amo_auth->getAccessToken($request["code"]);
        }

    }


}
