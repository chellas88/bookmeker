<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BotLogs;
use App\Models\BotSteps;
use App\Models\BotUsers;

class ViberController extends Controller
{

    public function index()
    {
        //
    }

    public function setupWebhook()
    {
        $params = [
            "url" => "https://schulz.dev.adminka.pro/viber/webhook",
            "event_types" => ["delivered", "seen", "subscribed", "unsubscribed"],
            "send_name" => true,
            "send_photo" => true
        ];
        $setup = $this->sendCURL("https://chatapi.viber.com/pa/set_webhook", $params);
        dd($setup);
    }

    public function uninstallWebhook()
    {
        $params = [
            "url" => ""
        ];
        $setup = $this->sendCURL("https://chatapi.viber.com/pa/set_webhook", $params);
        dd($setup);
    }

    public function webhook()
    {
        $request = file_get_contents("php://input");
        $data = json_decode($request, true);
        BotLogs::insert([
            "platform" => "viber",
            "source" => $data["event"],
            "data" => $request,
            "created_at" => now(),
            "updated_at" => now()
        ]);
        if ($data["event"] == "message") {
            $this->sendMessage("Не умею отвечать", $data["sender"]["id"]);
        } else if ($data["event"] == "subscribed") {
            $this->sendMessage("Добро пожаловать", $data["user"]["id"]);
        }
    }

    public function sendMessage($msg, $user, $keyboard = null)
    {
        $params = [
            "receiver" => $user,
            "type" => "text",
            "text" => $msg,
            "keyboard" => $keyboard
        ];
        $this->sendCURL("https://chatapi.viber.com/pa/send_message", $params);
    }

    public function sendCURL($url, $data)
    {

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                "Cache-control: no-cache",
                "Content-Type: application/JSON",
                "X-Viber-Auth-Token: " . config('viber.token'),

            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        if ($err) {
            return $err;
        } else {
            return $response;
        }
    }


    public function checkCommand($user_id, $command)
    {
        $step = BotSteps::where("user_id", $user_id)->get()[0];
        $current_step = $step["step"];
        switch ($command) {
            //Кнопка СТАРТ
            case "start":
                BotSteps::updateOrInsert(["user_id" => $user_id], [
                    "updated_at" => now(),
                    "created_at" => now(),
                    "step" => "start"
                ]);
                $this->sendMessage("Отправь нам свой номер телефона, указанный при регистрации", $user_id);
                break;

                //Кнопка ДА
            case "yes":
                if ($current_step == "start"){
                    //Тут будем создавать лид
                }
                break;

                //Кнопка НЕТ
            case "no":
                if ($current_step == "start"){
                    $this->sendMessage("Отправь нам свой номер телефона, указанный при регистрации", $user_id);
                }
                break;

            default:
                if ($current_step == "start") {
                    $phone = str_replace("+", "", $command);
                    if (is_numeric($phone)) {
                        $amo = new AmoController();
                        $amo_contact = $amo->findContact($phone);
                        if ($amo_contact) {
                            $check_phone = null;
//                            foreach ($amo_contact["custom_fields_values"] as $field){
//                                switch ($field["field_id"]){
//                                    case "302541":
//                                        $check_phone = $field["values"][0]["value"];
//                                }
//                            }
//                            $msg = $check_phone ." ". $amo_contact["name"] ."\nПодтвердить личность?";
//                            $this->sendMessage($msg, $user_id, config("viber.ask_keyboard"));

                            BotSteps::where("user_id", $user_id)->update([
                                "step" => "registered"
                            ]);
                            BotUsers::where("user_id", $user_id)->update([
                                "is_amo" => 1,
                                "amo_id" => $amo_contact["id"],
                                "phone" => $phone
                            ]);
                            $this->sendMessage("Номер телефона успешно подтвержден", $user_id);
                        } else {
                            $this->sendMessage("Номер не найден в базе клиентов\nПодать заявку на регистрацию?", $user_id, config('viber.ask_keyboard'));
                        }
                    } else {
                        $this->sendMessage("Введите номер телефона", $user_id);
                    }

                }

                break;
        }
    }


    public function broadcast()
    {

    }


    public function dev()
    {
        $amo = new AmoController();
        $amo_contact = $amo->findContact(380732665174);
        foreach ($amo_contact["custom_fields_values"] as $field){
            switch ($field["field_id"]){
                case "302541":
                    $check_phone = $field["values"][0]["value"];
            }
        }
        dd($check_phone);
    }
}
