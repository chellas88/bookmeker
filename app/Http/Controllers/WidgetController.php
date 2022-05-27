<?php

namespace App\Http\Controllers;

use App\AdminkaLibs\IntelTele;
use App\AdminkaLibs\Zvonobot;
use Illuminate\Http\Request;
use App\Models\ZvonobotRecords;

class WidgetController extends Controller
{

    public function amoHook(Request $request)
    {
        $req = $_REQUEST;
        $this->addAmoLog("amo_req", $req);
        $this->addAmoLog("amo_request", $request->post());
    }

    public function getTaskTypes(){
        $amo_tasks = $this->amo->accountInfo('task_types');
//        dd($amo_tasks);
        $tasks = null;
        foreach ($amo_tasks['_embedded']['task_types'] as $task){
            $tasks[] = [
                'id' => $task['id'],
                'name' => $task['name']
            ];
        }
        return $tasks;
    }

    public function dev()
    {
        dd($this->amo->getPipelines());
        $intel = new IntelTele(config("conf.intel_telecom"));
        //dd($intel->getSender("sms"));
        //dd($intel->getBalance());

        $zb = new Zvonobot(config("conf.zvonobot"));
        dd($zb->getCallInfo([
            719602
        ]));

        dd($zb->getPhones());
        dd($zb->getUserInfo());
    }

    public function widget()
    {
        $post = $_POST;
        if (!empty($post)) {
            $log_id = $this->addAmoLog("widget", $post);
            return [
                "success" => true,
                "log_id" => $log_id
            ];
        } else {
            $post = file_get_contents("php://input");
            $json = json_decode($post, true);
            if (!empty($json)) {
                $log_id = $this->addAmoLog("widget", $json);
                return [
                    "success" => true,
                    "log_id" => $log_id
                ];
            } else {
                return [
                    "success" => false,
                    "message" => "Empty POST data"
                ];
            }
        }
    }

    public function getSender(){
        $sms = new IntelTele();
        return $sms->getSender();
    }

    public function loadAudioDataPopup(){

        $pipelines = $this->amo->getPipelines();
        $data = null;
        foreach ($pipelines as $item){
            $data['pipelines'][] = [
                'pipeline_id' => $item['id'],
                'pipeline_name' => $item['name']
            ];
        };
        $records = ZvonobotRecords::select('id', 'name')->orderBy('name')->get();
        $data['records'] = $records;
        $data['senders'] = $this->getSender();
        return $data;
    }

    public function loadStatuses(Request $request){
        $pipeline_id = $request['pipeline_id'];
        $statuses = $this->amo->getPipelines($pipeline_id);
        $tmp = null;
        foreach($statuses as $status) {
            $tmp[] = [
                'id' => $status['id'],
                'name' => $status['name']
            ];
        }
        return $tmp;
    }



}
