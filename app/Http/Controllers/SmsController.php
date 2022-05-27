<?php

namespace App\Http\Controllers;

use Adminka\AmoCRM\AmoOAuth;
use App\AdminkaLibs\IntelTele;
use App\Models\AmoContacts;
use App\Models\AmoTriggers;
use App\Models\LeadsTriggers;
use App\Models\SmsTriger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\AmoApps;
use Illuminate\Support\Facades\Http;
use function Psy\sh;


class SmsController extends Controller
{
    public function index()
    {
        $trigers = null;
        $delay = null;
        $data = SmsTriger::select()->get();
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

            $trigers[] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'event' => $item['event'],
                'delay_type' => $delay,
                'pipeline_name' => $item['pipeline_name'],
                'status_name' => $item['status_name'],
                'record_name' => $item['record_name'],
                'text' => $item['sms_text'],
                'url' => $item['sms_url'],
                'is_active' => $item['is_active'],
                'sender_id' => $item['sender_id']
            ];
        }
        return view('sms', ['data' => $trigers]);
    }


    public function saveTriger(Request $request)
    {
        $triger_id = SmsTriger::insertGetId([
            'sender_id' => $request['sender_id'],
            'sender_name' => $request['sender_name'],
            'name' => $request['name'],
            'event' => $request['event'],
            'delay_type' => $request['delay_type'],
            'delay_time' => $request['delay_time'],
            'pipeline_id' => $request['pipeline_id'],
            'pipeline_name' => $request['pipeline_name'],
            'status_id' => $request['status_id'],
            'status_name' => $request['status_name'],
            'sms_text' => $request['sms_text'],
            'sms_url' => $request['sms_url'],
            'is_active' => $request['is_active'],
            'created_at' => now(),
            'updated_at' => now(),
            'send_sms' => 1
        ]);
        return 'success';
    }

    public function openTriger(Request $request)
    {
        $triger_data = SmsTriger::find($request['triger_id']);
        return $triger_data;
    }

    public function updateTriger(Request $request)
    {
        SmsTriger::where('id', $request['id'])->update([
            'name' => $request['name'],
            'event' => $request['event'],
            'delay_type' => $request['delay_type'],
            'delay_time' => $request['delay_time'],
            'pipeline_id' => $request['pipeline_id'],
            'pipeline_name' => $request['pipeline_name'],
            'status_id' => $request['status_id'],
            'status_name' => $request['status_name'],
            'sms_text' => $request['sms_text'],
            'sms_url' => $request['sms_url'],
            'is_active' => $request['is_active'],
            'updated_at' => now(),
            'sender_id' => $request['sender_id'],
            'sender_name' => $request['sender_name']

        ]);
        return 'success';
    }

    public function sendSms($trigger_params = null)
    {
        if ($trigger_params) {
            print_r($trigger_params);
            $trigger = SmsTriger::find($trigger_params["trigger_id"]);
            if ($trigger) {
                $lead = $this->amo->getLeadFromID($trigger_params["lead_id"]);
                $sms = new IntelTele();
                if (!empty($lead)) {
                    if (($lead["pipeline_id"] == $trigger["pipeline_id"]) && ($lead["status_id"] == $trigger["status_id"])) {
                        //dd($lead);
                        if (!empty($lead["_embedded"]["contacts"])) {
                            $contact = AmoContacts::find($lead["_embedded"]["contacts"][0]["id"]);
                            //dd($contact);
                            if (!empty($contact["phone"])) {
                                $phone = explode(",", $contact["phone"]);
                                $url_params = [
                                    "domain" => "bit.ly",
                                    "long_url" => $trigger["sms_url"] . $lead["id"]
                                ];
                                $short_url = $this->getShortUrl($url_params);
                                //dd($short_url);
                                $message = str_replace("{url}", $short_url, $trigger["sms_text"]);
                                //dd($message);
                                $find_lt = [ // lead trigger find params
                                    "lead_id" => $lead["id"],
                                    "trigger_type" => "sms",
                                    "trigger_id" => $trigger_params["trigger_id"]
                                ];
                                $update_lt = [ // lead trigger update params
                                    "sms_text" => $message,
                                    "sms_url" => $trigger["sms_url"] . $lead["id"]
                                ];
                                LeadsTriggers::updateOrInsert($find_lt, $update_lt);
                                $new_msg = [
                                    "from" => $trigger["sender_name"],
                                    "to" => $phone[0],
                                    "message" => $message
                                ];
                                print_r($new_msg);
                                print_r($sms->sendSMS($new_msg));
                                $update_lead = [
                                    "updated_by" => 0,
                                    "custom_fields_values" => [
                                        $this->amo->setCustomField(904279, "https://" . $short_url)
                                    ]
                                ];
                                $update_res = $this->amo->updateLead($update_lead, $lead["id"]);
                                print_r($update_res);
                            }
                        }
                    } else {
                        print_r("Lead changed\n");
                    }
                }
            }
        }
        return true;
    }

    public function getAmoToken(Request $request)
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
