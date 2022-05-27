<?php

namespace App\Http\Controllers;

use App\AdminkaLibs\IntelTele;
use App\Models\AmoContacts;
use Illuminate\Http\Request;
use App\Models\GlobalAction;
use App\Jobs\GlobalActions;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\SmsController;
use App\Models\GlobalSenderLogs;


class GlobalController extends Controller
{

  public function __construct()
  {
      $this->middleware('auth');
  }

    public function index()
    {
        $leads[] = [
            'id' => 32,
            'name' => 'Test'
        ];
        return view('global', ['data' => $leads]);
    }


    public function save_autosender(Request $request)
    {
        $id = GlobalAction::insertGetId([
            'name' => $request['name'],
            'type' => 'sms',
            'sender_id' => $request['sender_id'],
            'sender_name' => $request['sender_name'],
            'is_sms' => 1,
            'sms_text' => $request['sms_text'],
            'sms_url' => $request['sms_url'],
            'destination' => $request['destination'],
            'leads' => json_encode($request['leads']),
            'filters' => $request['filters'],
            'created_at' => now(),
            'updated_at' => now(),
            'is_audio' => 0,
            'is_ivr' => 0,
        ]);
        $params = [
            "id" => $id,
            "system" => "sms"
        ];
        GlobalActions::dispatch($params);
        return 'success';
    }

    public function save_autocaller(Request $request)
    {
        $id = GlobalAction::insertGetId([
            'name' => $request['name'],
            'type' => 'zvonobot',
            'audio' => $request['audio'],
            'sender_id' => $request['sender_id'],
            'sender_name' => $request['sender_name'],
            'is_sms' => $request['is_sms'],
            'sms_text' => $request['sms_text'],
            'sms_url' => $request['sms_url'],
            'destination' => $request['destination'],
            'leads' => json_encode($request['leads']),
            'is_audio' => $request['is_audio'],
            'sec_audio' => $request['sec_audio'],
            'filters' => $request['filters'],
            'is_ivr' => $request['is_ivr'],
            'digit' => $request['digit'],
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $params = [
            "id" => $id,
            "system" => "zvonobot"
        ];
        GlobalActions::dispatch($params);
        return 'success';
    }

    public function save_autobotsender(Request $request)
    {
        $id = GlobalAction::insertGetId([
            'name' => $request['name'],
            'type' => 'botman',
            'driver' => $request['driver'],
            'destination' => $request['destination'],
            'is_url' => $request['is_link'],
            'sms_text' => $request['text_message'],
            'sms_url' => $request['link'],
            'filters' => $request['filters'],
            'type_message' => $request['type_message'],
            'template_id' => $request['template'],
            'leads' => json_encode($request['leads']),
            'created_at' => now(),
            'updated_at' => now(),
            'is_audio' => 0,
            'is_ivr' => 0,
            'is_sms' => 0,
        ]);
        $params = [
            "id" => $id,
            "system" => "botman"
        ];
        GlobalActions::dispatch($params);
        return 'success';
    }

    public function sender($global_id){
        $amo = new AmoController();
        $global_info = GlobalAction::find($global_id);
        if(!empty($global_info)) {
            if ($global_info['destination'] == 'all') {
                $list = $amo->getLeadsByFilter(json_decode($global_info['filters'], true));
                if ($global_info['type'] == 'sms') {
                    $this->sendSms($global_id, $list);
                } else if ($global_info['type'] == 'zvonobot') {
                    $this->sendCall($global_id, $list);
                }
            } else if ($global_info['destination'] == 'list') {
                $list = $amo->getLeadsByList($global_info['leads']);
                if ($global_info['type'] == 'sms') {
                    $this->sendSms($global_id, $list);
                } else if ($global_info['type'] == 'zvonobot') {
                    $this->sendCall($global_id, $list);
                }
            }
        }

    }


    public function sendSms($global_id, $list){
        $global_info = GlobalAction::find($global_id);
        $log = null;
        if($list){
            foreach($list as $lead){
                if ($lead["contacts"]){
                    $contact = AmoContacts::find($lead["contacts"][0]["id"]);


                    if (($contact) && (!empty($contact["phone"]))){
                        $log["find_contact_info"][] = [
                            "contact_id" => $contact["id"],
                            "phone" => $contact["phone"],
                            "name" => $contact["name"],
                            "first_name" => $contact["first_name"],
                            "last_name" => $contact["last_name"],
                            "email" => $contact["email"],

                        ];
                        $phone = explode(",", $contact["phone"]);
                        $url_params = [
                            "domain" => "bit.ly",
                            "long_url" => $global_info["sms_url"] . $lead["lead_id"]
                        ];
                        $smsController = new SmsController();
                        $short_url = $smsController->getShortUrl($url_params);
                        $message = str_replace("{url}", $short_url, $global_info["sms_text"]);
                        $new_msg = [
                            "from" => $global_info["sender_name"],
                            "to" => $phone[0],
                            "message" => $message
                        ];
                        GlobalSenderLogs::insert([
                           "type" => "sms",
                           "global_id" => $global_id,
                            "lead_id" => $lead["lead_id"],
                            "contact_id" => $lead["contacts"][0]["id"],
                            "phone" => $contact["phone"],
                            "name" => $contact["name"],
                            "first_name" => $contact["first_name"],
                            "last_name" => $contact["last_name"],
                            "email" => $contact["email"],
                            "msg_data" => json_encode($new_msg),
                            "updated_at" => now(),
                            "created_at" => now()
                        ]);
//                        $sms = new IntelTele();
//                        $sms->sendSMS($new_msg);

                    }
                }

            }
        }
    }

    public function sendCall($global_id, $list){
        $global_info = GlobalAction::find($global_id);
        $log = null;
        if($list){
            foreach($list as $lead){
                if ($lead["contacts"]){
                    $contact = AmoContacts::find($lead["contacts"][0]["id"]);

                    if (($contact) && (!empty($contact["phone"]))){
                        $log["find_contact_info"][] = [
                            "contact_id" => $contact["id"],
                            "phone" => $contact["phone"],
                            "name" => $contact["name"],
                            "first_name" => $contact["first_name"],
                            "last_name" => $contact["last_name"],
                            "email" => $contact["email"],

                        ];
                        $phone = explode(",", $contact["phone"]);
                        $url_params = [
                            "domain" => "bit.ly",
                            "long_url" => $global_info["sms_url"] . $lead["lead_id"]
                        ];
                        $smsController = new SmsController();
                        $short_url = $smsController->getShortUrl($url_params);
                        $message = str_replace("{url}", $short_url, $global_info["sms_text"]);
                        $new_msg = [
                            "from" => $global_info["sender_name"],
                            "to" => $phone[0],
                            "message" => $message
                        ];
                        GlobalSenderLogs::insert([
                            "type" => "sms",
                            "global_id" => $global_id,
                            "lead_id" => $lead["lead_id"],
                            "contact_id" => $lead["contacts"][0]["id"],
                            "phone" => $contact["phone"],
                            "name" => $contact["name"],
                            "first_name" => $contact["first_name"],
                            "last_name" => $contact["last_name"],
                            "email" => $contact["email"],
                            "msg_data" => json_encode($new_msg),
                            "updated_at" => now(),
                            "created_at" => now()
                        ]);
//                        $sms = new IntelTele();
//                        $sms->sendSMS($new_msg);

                    }
                }

            }
        }

    }

    public function dev(){

        $this->sender(66);
    }

}
