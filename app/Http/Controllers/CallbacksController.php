<?php

namespace App\Http\Controllers;

use App\Models\Callbacks;
use App\Models\LeadsTriggers;
use Illuminate\Http\Request;

class CallbacksController extends Controller
{
    public function callbackHook(Request $request)
    {
        if ((isset($request["cid"])) && (isset($request["lid"]))) {
            $callback = Callbacks::find($request["cid"]);
            if (($callback) && ($callback["is_active"])) {
                //dd($callback);
                $lead = $this->amo->getLeadFromID($request["lid"]);
                $db_lead = LeadsTriggers::where("lead_id", $request["lid"])->first();
                if ((!empty($lead)) && (!empty($db_lead))) {
                    //dd($lead);
                    switch ($callback["callback_event"]) {
                        case "status":
                            $lead_data = [
                                "updated_by" => 0,
                                "pipeline_id" => $callback["callback_pipeline"],
                                "status_id" => $callback["callback_status"]
                            ];
                            $amo_res = $this->amo->updateLead($lead_data, $request["lid"]);
                            $this->addAmoLog("callback_status", $amo_res);
                            break;
                        case "task":
                            $task_data = [
                                "entity_id" => $lead["id"],
                                "responsible_user_id" => $lead["responsible_user_id"],
                                "complete_till" => strtotime(date("Y-m-d", strtotime("+2day"))) - 1,
                                "entity_type" => "leads",
                                "task_type_id" => $callback["callback_task"],
                                "created_by" => 0,
                                "text" => $callback["callback_task_text"]
                            ];
                            $add_res = $this->amo->addTask([$task_data]);
                            $this->addAmoLog("callback_task", $add_res);
                            break;
                        case "note":
                            $note_list = null;
                            $note_list[] = [
                                "entity_id" => $lead["id"],
                                "note_type" => "common",
                                "created_by" => 0,
                                "params" => [
                                    "text" => $callback["callback_note"]
                                ]
                            ];
                            $add_res = $this->amo->addNote("leads", $note_list);
                            $this->addAmoLog("callback_note", $add_res);
                            break;
                    }
                }
            }
        }
    }
}
