<?php


namespace App\AdminkaLibs;


use Illuminate\Support\Facades\Http;

class Zvonobot
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    private function postRequest($method, $params = null)
    {
        $headers = [
            "Accept" => "application/json",
            "Content-Typ" => "application/json"
        ];
        $url = "https://" . $this->config["domain"] . "/apiCalls/" . $method;
        if ($params) {
            $params["apiKey"] = $this->config["api_key"];
        } else {
            $params = [
                "apiKey" => $this->config["api_key"]
            ];
        }
        $res = Http::withHeaders($headers)->post($url, $params);
        if ($res->status() == 200) {
            $result = $res->json();
            if ($result["status"] == "success") {
                return [
                    "status" => "success",
                    "data" => $result["data"]
                ];
            } else {
                return [
                    "status" => "error",
                    "data" => $result
                ];
            }
        } else {
            return [
                "status" => "error",
                "data" => $res
            ];
        }
    }

    public function createCalls($call_data)
    {
        $method = "create";
        $params = $call_data;
        $params["webhookUrl"] = config("app.url") . "/zvonobot/status";
        return $this->postRequest($method, $params);
    }

    public function getCallInfo($call_list)
    {
        $method = "get";
        $params = [
            "apiCallIdList" => $call_list
        ];
        return $this->postRequest($method, $params);
    }

    public function getPhones()
    {
        $method = "getPhones";
        $params = [
            "all" => true
        ];
        return $this->postRequest($method, $params);
    }

    public function getUserInfo()
    {
        $method = "userInfo";
        return $this->postRequest($method);
    }

}
