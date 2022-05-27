<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/sms', [App\Http\Controllers\SmsController::class, 'index']);

Route::post('/home/zvonobot_popup_data', [App\Http\Controllers\WidgetController::class, 'loadAudioDataPopup']);
Route::post('/home/create_audio', [App\Http\Controllers\HomeController::class, 'addAudioRecord']);
Route::post('/home/load_statuses', [App\Http\Controllers\WidgetController::class, 'loadStatuses']);
Route::get('/home/get_tasks', [App\Http\Controllers\WidgetController::class, 'getTaskTypes']);

Route::post('/home/save_zvonobot_triger', [App\Http\Controllers\HomeController::class, 'saveZvonobotTriger']);
Route::post('/home/edit_zvonobot_triger', [App\Http\Controllers\HomeController::class, 'editZvonobotTriger']);

Route::post('/home/open_triger', [App\Http\Controllers\HomeController::class, 'openZvonobotTriger']);
Route::post('/home/update_triger', [App\Http\Controllers\HomeController::class, 'updateZvonobotTriger']);

Route::post('/sms/save_triger', [App\Http\Controllers\SmsController::class, 'saveTriger']);
Route::post('/sms/open_triger', [App\Http\Controllers\SmsController::class, 'openTriger']);
Route::post('/sms/update_triger', [App\Http\Controllers\SmsController::class, 'updateTriger']);
Route::get('/sms/open_triger', [App\Http\Controllers\SmsController::class, 'openTriger']);
Route::get('/sms/send', [App\Http\Controllers\SmsController::class, 'sendSms']);

Route::get('/amo/btn', [App\Http\Controllers\HomeController::class, 'amoAuth']);
Route::get('/amo/auth', [App\Http\Controllers\HomeController::class, 'getAmoToken']);

Route::get('/dev', [App\Http\Controllers\WidgetController::class, 'dev']);
Route::post('/amo/widget', [App\Http\Controllers\WidgetController::class, 'widget']);
Route::post('/amo/test', [App\Http\Controllers\WidgetController::class, 'amoHook']);
Route::post('/amo/hook', [App\Http\Controllers\AmoController::class, 'amoHook']);
Route::get('/amo/test', [App\Http\Controllers\AmoController::class, 'amoHook']);
Route::post('/amo/filter', [App\Http\Controllers\AmoController::class, 'filterLeads']);
Route::get('/amo/dev', [App\Http\Controllers\AmoController::class, 'dev']);

Route::get('/global', [App\Http\Controllers\GlobalController::class, 'index']);
Route::post('/global/save_autosender', [App\Http\Controllers\GlobalController::class, 'save_autosender']);
Route::post('/global/save_autocaller', [App\Http\Controllers\GlobalController::class, 'save_autocaller']);
Route::post('/global/save_autobotsender', [App\Http\Controllers\GlobalController::class, 'save_autobotsender']);
Route::get('/global/dev', [App\Http\Controllers\GlobalController::class, 'dev']);


Route::post('/zvonobot/status', [App\Http\Controllers\ZvonobotController::class, 'hook']);
Route::post('/zvonobot/ivr', [App\Http\Controllers\ZvonobotController::class, 'ivr']);
Route::get('/zvonobot/dev', [App\Http\Controllers\ZvonobotController::class, 'dev']);

Route::get('/callbacks', [App\Http\Controllers\CallbackController::class, 'index']);
Route::post('/callbacks/get_trigers', [App\Http\Controllers\CallbackController::class, 'getTrigers']);
Route::post('/callbacks/add_callback', [App\Http\Controllers\CallbackController::class, 'addCallback']);
Route::post('/callbacks/open_callback', [App\Http\Controllers\CallbackController::class, 'openCallback']);
Route::post('/callbacks/update_callback', [App\Http\Controllers\CallbackController::class, 'updateCallback']);
Route::post('/callbacks/remove_callback', [App\Http\Controllers\CallbackController::class, 'removeCallback']);

Route::get('/callbacks/hook', [App\Http\Controllers\CallbacksController::class, 'callbackHook']);

Route::get('/viber/dev', [App\Http\Controllers\ViberController::class, 'dev']);
Route::get('/viber', [App\Http\Controllers\ViberController::class, 'index']);
Route::post('/viber/setup', [App\Http\Controllers\ViberController::class, 'setupWebhook']);
Route::get('/viber/setup', [App\Http\Controllers\ViberController::class, 'setupWebhook']);
Route::get('/viber/uninstall', [App\Http\Controllers\ViberController::class, 'uninstallWebhook']);
Route::post('/viber/webhook', [App\Http\Controllers\ViberController::class, 'webhook']);

Route::get('/templates', [App\Http\Controllers\TemplateController::class, 'index']);
Route::post('/templates/create', [App\Http\Controllers\TemplateController::class, 'createTemplate']);
Route::post('/templates/update', [App\Http\Controllers\TemplateController::class, 'updateTemplate']);
Route::post('/templates/delete', [App\Http\Controllers\TemplateController::class, 'deleteTemplate']);
Route::post('/templates/get_template_by_id', [App\Http\Controllers\TemplateController::class, 'getTemplateById']);
Route::post('/templates/all', [App\Http\Controllers\TemplateController::class, 'getAllTemplates']);

// Telegram/Viber bot
Route::match(['get', 'post'], '/bot/webhook/{platform}', [App\Http\Controllers\BotController::class, 'handle']);


// Artisan commands
Route::get('artisan/migrate', function () {
    $exitCode = Artisan::call('migrate');
    var_dump($exitCode);
});

Route::get('artisan/clear-cache', function () {
    $exitCode = Artisan::call('cache:clear');
    var_dump($exitCode);
});

Route::get('artisan/clear-config', function () {
    $exitCode = Artisan::call('config:clear');
    var_dump($exitCode);
});

// this page for test invite_links
Route::get('/invite_links', function () {
    echo '<a href="viber://pa?chatURI=adminkadevelopmentbot&context=123456&text=/start">viber</a>';
    echo '<br />';
    echo '<a href="https://t.me/test_botman_localhost_bot?start=123456">telegram</a>';
});
