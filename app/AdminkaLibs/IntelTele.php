<?php


namespace App\AdminkaLibs;


use Illuminate\Support\Facades\Http;

class IntelTele
{
    private $api_data;

    public function __construct()
    {
        $this->api_data = config("conf.intel_telecom");
    }

    private function postRequest($method, $params = null)
    {
        if ($params) {
            $params["username"] = $this->api_data["login"];
            $params["api_key"] = $this->api_data["token"];
        } else {
            $params = [
                "username" => $this->api_data["login"],
                "api_key" => $this->api_data["token"]
            ];
        }
        $url = $this->api_data["domain"] . "/$method/";

        $headers = [
            "Accept" => "application/json",
            "Content-Typ" => "application/json"
        ];
        $res = Http::withHeaders($headers)->post($url, $params);
        if ($res->status() == 200) {
            return $res->json();
        } else {
            return $res;
        }
    }

    private function getRequest($method, $params = null)
    {
        if ($params) {
            $params["username"] = $this->api_data["login"];
            $params["api_key"] = $this->api_data["token"];
        } else {
            $params = [
                "username" => $this->api_data["login"],
                "api_key" => $this->api_data["token"]
            ];
        }
        $url = $this->api_data["domain"] . "/$method/?" . http_build_query($params);

        $headers = [
            "Accept" => "application/json",
            "Content-Typ" => "application/json"
        ];
        $res = Http::withHeaders($headers)->get($url);
        if ($res->status() == 200) {
            return $res->json();
        } else {
            return $res;
        }
    }

    public function getBalance()
    {
        $method = "balance";
        return $this->getRequest($method);
    }

    public function getSender($channel = null)
    {
        $sender_list = null;
        if ($channel) {
            $method = "sender/$channel/list";
        } else {
            $method = "sender/list";
        }
        $res = $this->getRequest($method);
        if ((!empty($res)) && (isset($res["sender_list"])) && (isset($res["sender_list"]["items"])) && (!empty($res["sender_list"]["items"]))) {
            foreach ($res["sender_list"]["items"] as $sender) {
                $sender_list[] = $sender;
            }
        }
        return $sender_list;
    }

    public function sendSMS($message_params)
    {
        $method = "message/send";
        return $this->postRequest($method, $message_params);
    }
}
