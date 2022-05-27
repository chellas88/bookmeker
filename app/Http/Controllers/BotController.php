<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BotLogs;
use App\Models\BotUsers;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use App\Http\Controllers\ViberController;
use App\Http\Controllers\AmoController;

class BotController extends Controller
{
    public function handle(Request $request, $platform)
    {

        $this->addBotLog($platform, 'handle', $request->input());


        $request_data = $request->input();


        if ($platform === 'telegram') {
            $bot = new BotApi('1802443738:AAFLFDceZlrldq6kS4euYp7qs71wYLSb1Hc');
            $user_id = $request_data['message']['from']['id'];
            if (isset($request_data['message'])) {
                $text = $request_data['message']['text'];
                if (preg_match("/^\/(?P<command>\w+)/", $text, $result) == 1) {
                    switch ($result['command']) {
                        case 'start':
                            $amo_id = null;
                            $is_amo = 0;
                            if (preg_match("/^\/(?P<command>\w+) (?P<params>\d+)/", $text, $result) == 1) {
                                $amo_id = $result['params'];
                                $is_amo = 1;
                            }
                            $this->addOrUpdateBotUser([
                                'user_id' => $request_data['message']['from']['id'],
                                'username' => $request_data['message']['from']['username'],
                                'platform' => $platform,
                                'first_name' => $request_data['message']['from']['first_name'],
                                'last_name' => $request_data['message']['from']['last_name'],
                                'amo_id' => $amo_id,
                                'is_amo' => $is_amo,
                                'status' => 'subscribed',
                            ]);
                            break;
                    }
                } else {
                    switch ($text) {
                        case 'test message':
                            $bot->sendMessage($user_id, 'hello');
                            break;
                        case 'test keyboard':
                            $keyboard = new ReplyKeyboardMarkup(array(
                                array("one", "two", "three")
                            ), true, true);

                            $bot->sendMessage($user_id, 'test message', null, false, null, $keyboard);
                            break;
                        case 'test inline keyboard':
                            $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup(
                                [
                                    [
                                        ['text' => 'link', 'url' => 'https://core.telegram.org']
                                    ]
                                ]
                            );

                            $bot->sendMessage($user_id, 'test message inline', null, false, null, $keyboard);
                            break;
                        case 'test media':
                            $media = new \TelegramBot\Api\Types\InputMedia\ArrayOfInputMedia();
                            // $media->addItem(new \TelegramBot\Api\Types\InputMedia\InputMediaPhoto('https://avatars3.githubusercontent.com/u/9335727'));
                            // $media->addItem(new \TelegramBot\Api\Types\InputMedia\InputMediaPhoto('https://avatars3.githubusercontent.com/u/9335727'));
                            // Same for video
                            $media->addItem(new \TelegramBot\Api\Types\InputMedia\InputMediaVideo('http://clips.vorwaerts-gmbh.de/VfE_html5.mp4'));
                            $bot->sendMediaGroup($user_id, $media);
                            break;
                    }
                }
            } else if (isset($request_data['my_chat_member'])) {
                if ($request_data['my_chat_member']['new_chat_member']['status'] === 'kicked') {
                    $status = 'unsubscribed';
                } else {
                    $status = 'subscribed';
                }
                $this->updateBotUser([
                    'user_id' => $request_data['my_chat_member']['chat']['id'],
                    'platform' => $platform,
                    'status' => $status,
                ]);
            }

        } else if ($platform === 'viber') {

            $viber = new ViberController();
            switch ($request_data['event']) {
                case 'conversation_started':
                    $this->addOrUpdateBotUser([
                        'user_id' => $request_data['user']['id'],
                        'platform' => $platform,
                        'first_name' => $request_data['user']['name'],
                        'amo_id' => (isset($request_data['context'])) ? $request_data['context'] : null,
                        'is_amo' => (isset($request_data['context'])) ? 1 : 0,
                        'status' => $request_data['subscribed'] ? 'subscribed' : 'unsubscribed',
                    ]);
                    $viber->sendMessage("Добро пожаловать!\nЗдесь ты сможешь получать наилучшие бонусы и предложения(money)\nНажми Старт для начала", $request_data["user"]["id"], config('viber.start_keyboard'));
                    break;
                case 'message':
                    $viber->checkCommand($request_data["sender"]["id"], $request_data["message"]["text"]);
                    break;
                case 'unsubscribed':
                    $this->updateBotUser([
                        'user_id' => $request_data['user_id'],
                        'platform' => $platform,
                        'status' => 'unsubscribed',
                    ]);
                    break;
                case 'subscribed':
                    $this->addOrUpdateBotUser([
                        'user_id' => $request_data['user']['id'],
                        'platform' => $platform,
                        'first_name' => $request_data['user']['name'],
                        'status' => 'subscribed',
                    ]);
                    $viber->sendMessage("Добро пожаловать!\nЗдесь ты сможешь получать наилучшие бонусы и предложения(money)\nНажми Старт для начала", $request_data["user"]["id"], config('viber.start_keyboard'));
                    break;
            }

        }
    }

    public function addOrUpdateBotUser($params)
    {
        $bot_user = BotUsers::where('user_id', $params['user_id'])->first();

        if (empty($bot_user)) {
            $bot_user = new BotUsers();
            $bot_user->created_at = now();
        }

        $bot_user->user_id = $params['user_id'];
        if (!empty($params['username'])) $bot_user->username = $params['username'];
        if (!empty($params['platform'])) $bot_user->platform = $params['platform'];
        if (!empty($params['first_name'])) $bot_user->first_name = $params['first_name'];
        if (!empty($params['last_name'])) $bot_user->last_name = $params['last_name'];
        if (!empty($params['amo_id'])) $bot_user->amo_id = $params['amo_id'];
        if (!empty($params['is_amo'])) $bot_user->is_amo = $params['is_amo'];
        if (!empty($params['status'])) $bot_user->status = $params['status'];

        $bot_user->updated_at = now();

        $this->addBotLog($params['platform'], 'user_data add/update', $bot_user);

        $bot_user->save();
    }

    public function updateBotUser($params)
    {
        $bot_user = BotUsers::where('user_id', $params['user_id'])->first();

        if (!empty($bot_user)) {
            $bot_user->user_id = $params['user_id'];
            if (!empty($params['username'])) $bot_user->username = $params['username'];
            if (!empty($params['platform'])) $bot_user->platform = $params['platform'];
            if (!empty($params['first_name'])) $bot_user->first_name = $params['first_name'];
            if (!empty($params['last_name'])) $bot_user->last_name = $params['last_name'];
            if (!empty($params['amo_id'])) $bot_user->amo_id = $params['amo_id'];
            if (!empty($params['is_amo'])) $bot_user->is_amo = $params['is_amo'];
            if (!empty($params['status'])) $bot_user->status = $params['status'];

            $bot_user->updated_at = now();

            $this->addBotLog($params['platform'], 'user_data update', $bot_user);

            $bot_user->save();
        }
    }

    public function addBotLog($platform, $source, $data)
    {
        $db_log = new BotLogs();
        $log['platform'] = $platform;
        $log['source'] = $source;
        $log['data'] = json_encode($data);
        $log['created_at'] = now();
        $log['updated_at'] = now();
        return $db_log->insertGetId($log);
    }
}
