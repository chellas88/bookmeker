<?php

namespace App\Http\Controllers;

use App\AdminkaLibs\IntelTele;
use App\AdminkaLibs\Zvonobot;
use App\Jobs\Sender;
use App\Models\AmoContacts;
use App\Models\AmoTriggers;
use App\Models\ApiCalls;
use App\Models\Calls;
use App\Models\LeadsTriggers;
use Illuminate\Http\Request;

class ZvonobotController extends Controller
{
    public function hook(Request $request)
    {
        $this->addAmoLog("zvonobot_hook", $request->post());
        $req = $request->post();
        if ((!empty($req)) && (isset($req["apiCalls"]))) {
            foreach ($req["apiCalls"] as $api_call) {
                $api_call_params = [
                    "webhookTry" => $api_call["webhookTry"],
                    "plannedAt" => $api_call["plannedAt"],
                    "needRecording" => $api_call["needRecording"],
                    "webhookSent" => $api_call["webhookSent"],
                    "hook_package" => json_encode($api_call)
                ];
                ApiCalls::updateOrInsert(["id" => $api_call["id"]], $api_call_params);
                if ((isset($api_call["calls"])) && (!empty($api_call["calls"]))) {
                    foreach ($api_call["calls"] as $call) {
                        $call_params = [
                            "phone" => $call["phone"],
                            "comment" => $call["comment"],
                            "hangup_cause" => $call["hangupCause"],
                            "cost" => $call["cost"],
                            "ivr_digit" => $call["ivrDigit"],
                            "ivr_answers" => $call["ivrAnswers"],
                            "status" => $call["status"],
                            "started_at" => date("Y-m-d H:i:s", $call["startedAt"]),
                            "answered_at" => date("Y-m-d H:i:s", $call["answeredAt"]),
                            "finished_at" => date("Y-m-d H:i:s", $call["finishedAt"]),
                            "created_at" => date("Y-m-d H:i:s", $call["createdAt"]),
                            "updated_at" => date("Y-m-d H:i:s", $call["updatedAt"])
                        ];
                        Calls::updateOrInsert(["id" => $call["id"], "api_call_id" => $api_call["id"]], $call_params);
                    }
                }
            }
        }
    }

    public function ivr(Request $request)
    {
        $this->addAmoLog("zvonobot_ivr", $request->post());
        $req = $request->post();
        if ((!empty($req)) && (isset($req["call"]))) {
            ApiCalls::updateOrInsert(["id" => $req["call"]["apicallId"]], ["answer" => $req["call"]["answer"]]);
            $api_call = ApiCalls::where("id", $req["call"]["apicallId"])->first();
            if ($api_call) {
                $sms_params = [
                    "system" => "zvonobot_sms",
                    "tid" => $api_call["trigger_id"],
                    "phone" => $api_call["phone"],
                    "lead_id" => $api_call["lead_id"],
                    "digit" => $req["call"]["answer"]
                ];
                $this->addAmoLog("zvonobot_sms_dispatch", $sms_params);
                Sender::dispatch($sms_params);
            }
        }
    }

    public function dev()
    {
        dd();
        $trigger_params = [
            "system" => "zvonobot",
            "trigger_id" => 6,
            "lead_id" => 9264225
        ];
        if ($trigger_params) {
            $trigger = AmoTriggers::find($trigger_params["trigger_id"]);
            if (!empty($trigger)) {
                $trigger = $trigger->toArray();
                if ($trigger["is_active"]) {
                    //dd($trigger);
                    $lead = $this->amo->getLeadFromID($trigger_params["lead_id"]);
                    if (!empty($lead)) {
                        //dd($lead);
                        if (!empty($lead["_embedded"]["contacts"])) {
                            $db_contact = AmoContacts::find($lead["_embedded"]["contacts"][0]["id"]);
                            if ((!empty($db_contact)) && (!empty($db_contact["phone"]))) {
                                $call_data = [
                                    "phone" => $db_contact["phone"],
                                    "dutyPhone" => 1,
                                    //"outgoingPhone" => "380482399415",
                                    "record" => [
                                        "id" => $trigger["record_id"]
                                    ],
                                    "ivrs" => [
                                        [
                                            "digit" => 1, // db digit
                                            "record" => [
                                                "id" => 832554 // db second record_id
                                            ],
                                            "webhookUrl" => config("app.url") . "/zvonobot/ivr",
                                            "webhookParameters" => json_encode(["tid" => $trigger["id"]])
                                        ]
                                    ]
                                ];
                                //dd($call_data);
                                $zb = new Zvonobot(config("conf.zvonobot"));
                                $res = $zb->createCalls($call_data);
                                if ($res["status"] == "success") {
                                    foreach ($res["data"] as $api_call) {
                                        $api_call_params = [
                                            "lead_id" => $lead["id"],
                                            "phone" => $api_call["phone"],
                                            "trigger_id" => $trigger["id"],
                                            "created_at" => date("Y-m-d H:i:s", $api_call["createdAt"]),
                                            "needRecording" => $api_call["needRecording"],
                                            "send_package" => json_encode($call_data),
                                            "response_package" => json_encode($api_call)
                                        ];
                                        ApiCalls::updateOrInsert(["id" => $api_call["id"]], $api_call_params);
                                    }
                                }
                                dd($res);
                            }
                        }
                    }

                } else {
                    dd("Trigger not active");
                }
            }
        } else {
            dd("Empty trigger");
        }

        dd("End");
        dd($zb->getPhones());
    }

    public function sendCall($trigger_params = null)
    {
        if ($trigger_params) {
            $trigger = AmoTriggers::find($trigger_params["trigger_id"]);
            if (!empty($trigger)) {
                $trigger = $trigger->toArray();
                if ($trigger["is_active"]) {
                    $lead = $this->amo->getLeadFromID($trigger_params["lead_id"]);
                    if (!empty($lead)) {
                        if (!empty($lead["_embedded"]["contacts"])) {
                            $db_contact = AmoContacts::find($lead["_embedded"]["contacts"][0]["id"]);
                            if ((!empty($db_contact)) && (!empty($db_contact["phone"]))) {
                                $call_data = [
                                    "phone" => $db_contact["phone"],
                                    "dutyPhone" => 1,
                                    //"outgoingPhone" => "380482399415",
                                    "record" => [
                                        "id" => $trigger["record_id"]
                                    ]
                                ];
                                if ($trigger["is_ivr"]) {
                                    $ivr = [
                                        "digit" => $trigger["sec_record_digit"], // db digit
                                        "webhookUrl" => config("app.url") . "/zvonobot/ivr",
                                        "webhookParameters" => json_encode(["tid" => $trigger["id"]])
                                    ];
                                    if (!empty($trigger["sec_record_id"])) {
                                        $ivr["record"] = [
                                            "id" => $trigger["sec_record_id"] // db second record_id
                                        ];
                                    }
                                    $call_data["ivrs"][] = $ivr;
                                }

                                $zb = new Zvonobot(config("conf.zvonobot"));
                                $res = $zb->createCalls($call_data);
                                if ($res["status"] == "success") {
                                    foreach ($res["data"] as $api_call) {
                                        $api_call_params = [
                                            "lead_id" => $lead["id"],
                                            "phone" => $api_call["phone"],
                                            "trigger_id" => $trigger["id"],
                                            "created_at" => date("Y-m-d H:i:s", $api_call["createdAt"]),
                                            "needRecording" => $api_call["needRecording"],
                                            "send_package" => json_encode($call_data),
                                            "response_package" => json_encode($api_call)
                                        ];
                                        ApiCalls::updateOrInsert(["id" => $api_call["id"]], $api_call_params);
                                    }
                                }
                                print_r($res);
                            }
                        }
                    }

                } else {
                    print_r("Trigger not active");
                }
            }
        }
        return true;
    }

    public function sendSms($params)
    {
        $trigger = AmoTriggers::find($params["tid"]);
        if ($trigger) {
            if (($trigger["sec_record_digit"] == $params["digit"]) && ($trigger["send_sms"])) {
                $sms = new IntelTele();
                $url_params = [
                    "domain" => "bit.ly",
                    "long_url" => $trigger["sms_url"] . $params["lead_id"]
                ];
                $short_url = $this->getShortUrl($url_params);
                $message = str_replace("{url}", $short_url, $trigger["sms_text"]);
                $find_lt = [ // lead trigger find params
                    "lead_id" => $params["lead_id"],
                    "trigger_type" => "zvonobot_sms",
                    "trigger_id" => $trigger["id"]
                ];
                $update_lt = [ // lead trigger update params
                    "sms_text" => $message,
                    "sms_url" => $trigger["sms_url"] . $params["lead_id"]
                ];
                LeadsTriggers::updateOrInsert($find_lt, $update_lt);
                $new_msg = [
                    "from" => $trigger["sender_name"],
                    "to" => $params["phone"],
                    "message" => $message
                ];
                //print_r($new_msg);
                print_r($sms->sendSMS($new_msg));
                $update_lead = [
                    "updated_by" => 0,
                    "custom_fields_values" => [
                        $this->amo->setCustomField(904279, "https://" . $short_url)
                    ]
                ];
                $update_res = $this->amo->updateLead($update_lead, $params["lead_id"]);
                print_r($update_res);
            }
        }
        return true;
    }
}
