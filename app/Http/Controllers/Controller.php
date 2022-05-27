<?php

namespace App\Http\Controllers;

use Adminka\AmoCRM\AmoCRM;
use App\Models\AmoApps;
use App\Models\AmoLogs;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $amo;

    public function __construct()
    {
        $amo_app = new AmoApps();
        $api_data = $amo_app->getApiData(1);
        $this->amo = new AmoCRM($api_data);
        $this->amo->setExternalUpdateToken(function ($token_data) {
            $this->saveToken($token_data);
        });
    }

    public function addAmoLog($source, $data)
    {
        $db_log = new AmoLogs();
        $log['source'] = $source;
        $log['data'] = json_encode($data);
        return $db_log->insertGetId($log);
    }

    public
    function saveToken($token_data)
    {
        $amo_app = new AmoApps();
        $update_param = [
            "access_token" => $token_data["access_token"],
            "refresh_token" => $token_data["refresh_token"]
        ];
        if (isset($token_data["expires_in"])) {
            $update_param["expires"] = date("Y-m-d H:i:s", time() + $token_data["expires_in"]);
        } elseif (isset($token_data["expired_in"])) {
            $update_param["expires"] = date("Y-m-d H:i:s", $token_data["expired_in"]);
        }
        $amo_app->where("id", $token_data["auth_id"])->update($update_param);
    }

    public function getShortUrl($url_params)
    {
        $bit_api = config("conf.bitly");
        $url_params["group_id"] = $bit_api["group_id"];

        /*$headers = [
            "Content-Typ" => "application/json",
            "Authorization: Bearer " . $bit_api["token"]
        ];
        $res = Http::withHeaders($headers)->post($bit_api["bit_url"], $url_params);
        if ($res->status() == 200) {
            return $res->json();
        } else {
            return $res;
        }*/
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $bit_api["bit_url"],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($url_params),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $bit_api["token"]
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $short_data = json_decode($response, true);
        if (isset($short_data["link"])) {
            return $short_data["id"];
        } else {
            return null;
        }
    }
}
