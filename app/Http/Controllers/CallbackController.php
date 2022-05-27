<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Callbacks;
use App\Models\SmsTriger;
use App\Models\AmoTriggers;

class CallbackController extends Controller
{
    public function index(){
        $callbacks = Callbacks::get();

        return view('callbacks', ['data' => $callbacks]);
    }


    public function getTrigers(Request $request){
        $trigers = null;
        if ($request['triger_type'] == 'zvonobot'){
            $zvonobot = AmoTriggers::get();
            foreach ($zvonobot as $triger){
                $trigers[] = [
                    'id' => $triger['id'],
                    'name' => $triger['name']
                ];
            }
        }
        else if ($request['triger_type'] == 'sms'){
            $sms = SmsTriger::get();
            foreach ($sms as $triger){
                $trigers[] = [
                    'id' => $triger['id'],
                    'name' => $triger['name']
                ];
            }
        }
        return $trigers;
    }

    public function addCallback(Request $request){
        $callback_id = Callbacks::insertGetId([
            'name' => $request['name'],
            'triger_type' => $request['type'],
            'triger_id' => $request['triger_id'],
            'triger_name' => $request['triger_name'],
            'callback_event' => $request['event'],
            'callback_pipeline' => $request['pipeline_id'],
            'callback_pipeline_name' => $request['pipeline_name'],
            'callback_status' => $request['status_id'],
            'callback_status_name' => $request['status_name'],
            'callback_note' => $request['note'],
            'callback_task' => $request['task'],
            'callback_task_text' => $request['task_text'],
            'is_active' => $request['is_active'],
            'created_at' => now(),
            'updated_at' => now()

        ]);
        $callback_url = 'https://' . $_SERVER['SERVER_NAME'] . '/callbacks/hook?cid=' . $callback_id . '&lid={lead_id}';
        Callbacks::where('id', $callback_id)->update(['callback_url' => $callback_url]);
        return $callback_id;
    }

    public function openCallback(Request $request){
        $data = null;
        $callback_info = Callbacks::find($request['callback_id']);
        $data['callback_info'] = $callback_info;
        if ($callback_info['triger_type'] == 'zvonobot'){
            $trigers = AmoTriggers::get();
            foreach ($trigers as $triger){
                $data['trigers'][] = [
                    'id' => $triger['id'],
                    'name' => $triger['name']
                ];
            }
        }
        else if ($callback_info['triger_type'] == 'sms'){
            $trigers = SmsTriger::get();
            foreach ($trigers as $triger){
                $data['trigers'][] = [
                    'id' => $triger['id'],
                    'name' => $triger['name']
                ];
            }
        }
        return $data;
    }

    public function updateCallback(Request $request){

        Callbacks::where('id', $request['id'])->update([
            'name' => $request['name'],
            'triger_type' => $request['type'],
            'triger_id' => $request['triger_id'],
            'triger_name' => $request['triger_name'],
            'callback_event' => $request['event'],
            'callback_pipeline' => $request['pipeline_id'],
            'callback_pipeline_name' => $request['pipeline_name'],
            'callback_status' => $request['status_id'],
            'callback_status_name' => $request['status_name'],
            'callback_note' => $request['note'],
            'callback_task' => $request['task'],
            'callback_task_text' => $request['task_text'],
            'is_active' => $request['is_active'],
            'created_at' => now(),
            'updated_at' => now()

        ]);
    }

    public function removeCallback(Request $request){
        $result = Callbacks::where('id', $request['id'])->delete();
        return $result;
    }
}
