<?php

namespace App\Http\Controllers;

use App\AdminkaLibs\IntelTele;
use App\Jobs\AmoSyncLeads;
use App\Jobs\AmoSyncs;
use App\Jobs\Sender;
use App\Models\AmoContacts;
use App\Models\AmoLeadTriggers;
use App\Models\AmoTriggers;
use App\Models\SmsTriger;
use Illuminate\Http\Request;
use TheSeer\Tokenizer\Exception;

class AmoController extends Controller
{
    public function amoHook(Request $request)
    {
        $req = $request->post();
        $this->addAmoLog("amo_hook", $req);
        if (isset($req["leads"])) {
            $db_leads_triggers = new AmoLeadTriggers();
            $lead_key = array_key_first($req["leads"]);
            //dd($leads_key);
            if ($lead_key != "delete") {
                foreach ($req["leads"][$lead_key] as $lead_data) {
                    //dd($lead_data);
                    $tmp_lead = [
                        "name" => $lead_data["name"],
                        "status_id" => empty($lead_data["status_id"]) ? null : $lead_data["status_id"],
                        "old_status_id" => empty($lead_data["old_status_id"]) ? null : $lead_data["old_status_id"],
                        "price" => $lead_data["price"],
                        "responsible_user_id" => $lead_data["responsible_user_id"],
                        "last_modified" => date("Y-m-d H:i:s", $lead_data["last_modified"]),
                        "modified_user_id" => $lead_data["modified_user_id"] ?? null,
                        "created_user_id" => $lead_data["created_user_id"],
                        "date_create" => date("Y-m-d H:i:s", $lead_data["date_create"]),
                        "pipeline_id" => $lead_data["pipeline_id"],
                        "account_id" => $lead_data["account_id"] ?? null,
                        "created_at" => date("Y-m-d H:i:s", $lead_data["created_at"]),
                        "updated_at" => date("Y-m-d H:i:s", $lead_data["updated_at"]),
                        "is_deleted" => false,
                        "last_event" => $lead_key
                    ];
                    if (isset($tmp_lead["custom_fields_values"])) {
                        $tmp_lead["custom_fields_values"] = json_encode($tmp_lead["custom_fields_values"]);
                    }
                    $db_leads_triggers->updateOrInsert(["id" => $lead_data["id"]], $tmp_lead);
                    $this->setQueueTasks($lead_key, $lead_data);
                }
            } else {
                foreach ($req["leads"][$lead_key] as $lead_data) {
                    $tmp_lead = [
                        "status_id" => $lead_data["status_id"],
                        "pipeline_id" => $lead_data["pipeline_id"],
                        "is_deleted" => true,
                        "last_event" => $lead_key
                    ];
                    $db_leads_triggers->updateOrInsert(["id" => $lead_data["id"]], $tmp_lead);
                }
            }
        } elseif (isset($req["contacts"])) {
            $contact_key = array_key_first($req["contacts"]);
            //dd($leads_key);
            if ($contact_key != "delete") {
                foreach ($req["contacts"][$contact_key] as $contact) {
                    if ($contact_key == "add") {
                        $item = [
                            "entity_id" => $contact["id"],
                            "entity" => "contacts"
                        ];
                        AmoSyncs::dispatch($item);
                    }
                    //dd($lead_data);
                    $new_contact = [
                        "name" => $contact["name"],
                        "responsible_user_id" => $contact["responsible_user_id"],
                        "updated_by" => $contact["modified_user_id"] ?? null,
                        "created_by" => $contact["created_user_id"] ?? null,
                        "created_at" => date("Y-m-d H:i:s", $contact["created_at"]),
                        "updated_at" => date("Y-m-d H:i:s", $contact["updated_at"]),
                        "is_deleted" => false
                    ];
                    if ((isset($contact["custom_fields"])) && (!empty($contact["custom_fields"]))) {
                        foreach ($contact["custom_fields"] as $cf) {
                            if ($cf["id"] == 302541) {
                                foreach ($cf["values"] as $val) {
                                    if ((!isset($new_contact["phone"])) || (empty($new_contact["phone"]))) {
                                        $new_contact["phone"] = preg_replace('/[^0-9]/', '', $val["value"]);
                                    } else {
                                        $new_contact["phone"] .= "," . preg_replace('/[^0-9]/', '', $val["value"]);
                                    }
                                }
                            }
                            if ($cf["id"] == 302543) {
                                foreach ($cf["values"] as $val) {
                                    if ((!isset($new_contact["email"])) && (empty($new_contact["email"]))) {
                                        $new_contact["email"] = $val["value"];
                                    } else {
                                        $new_contact["email"] .= "," . $val["value"];
                                    }
                                }
                            }
                        }
                        $new_contact["custom_fields"] = json_encode($contact["custom_fields"]);
                    }
                    AmoContacts::updateOrInsert(["id" => $contact["id"]], $new_contact);
                }
            } else {
                foreach ($req["contacts"][$contact_key] as $contact) {
                    $tmp_contact = [
                        "is_deleted" => true,
                    ];
                    AmoContacts::updateOrInsert(["id" => $contact["id"]], $tmp_contact);
                }
            }
        }
    }

    private function setQueueTasks($event, $lead)
    {
        $where_params = [
            ["event", $event],
            ["is_active", true]
        ];
        $sms_trigger_list = SmsTriger::where($where_params)->get()->toArray();
        //dd($sms_trigger_list);
        foreach ($sms_trigger_list as $trigger) {
            if (($lead["pipeline_id"] == $trigger["pipeline_id"]) && ($lead["status_id"] == $trigger["status_id"])) {
                $trigger_params = [
                    "system" => "sms",
                    "trigger_id" => $trigger["id"],
                    "lead_id" => $lead["id"]
                ];
                //dd($lead);
                switch ($trigger["delay_type"]) {
                    case "now":
                        print_r("set sms trigger now<br/>");
                        Sender::dispatch($trigger_params);
                        break;
                    case "minutes":
                        print_r("set sms triggqer after " . $trigger["delay_time"] . " minutes - " . date("Y-m-d H:i:s", time() + ($trigger["delay_time"] * 60)) . " <br/>");
                        Sender::dispatch($trigger_params)->delay(now()->addMinutes($trigger["delay_time"]));
                        break;
                    case "hours":
                        print_r("set sms trigger after " . $trigger["delay_time"] . " hours<br/>");
                        Sender::dispatch($trigger_params)->delay(now()->addHours($trigger["delay_time"]));
                        break;
                }
            }
        }
        $zvonobot_trigger_list = AmoTriggers::where($where_params)->get()->toArray();
        //dd($trigger_list);
        foreach ($zvonobot_trigger_list as $trigger) {
            if (($lead["pipeline_id"] == $trigger["pipeline_id"]) && ($lead["status_id"] == $trigger["status_id"])) {
                $trigger_params = [
                    "system" => "zvonobot",
                    "trigger_id" => $trigger["id"],
                    "lead_id" => $lead["id"]
                ];
                switch ($trigger["delay_type"]) {
                    case "now":
                        print_r("set zvonobot trigger now<br/>");
                        Sender::dispatch($trigger_params);
                        break;
                    case "minutes":
                        print_r("set zvonobot trigger after " . $trigger["delay_time"] . " minutes<br/>");
                        Sender::dispatch($trigger_params)->delay(now()->addMinutes($trigger["delay_time"]));
                        break;
                    case "hours":
                        print_r("set zvonobot trigger after " . $trigger["delay_time"] . " hours<br/>");
                        Sender::dispatch($trigger_params)->delay(now()->addHours($trigger["delay_time"]));
                        break;
                    case "days":
                        print_r("set zvonobot trigger after " . $trigger["delay_time"] . " days<br/>");
                        Sender::dispatch($trigger_params)->delay(now()->addDays($trigger["delay_time"]));
                        break;
                }
            }
        }
        dd($lead);
    }

    public function loadContacts($contact_id)
    {
        $amo_contact = $this->amo->getContactFromID($contact_id);
        if (!empty($amo_contact)) {
            $new_contact = [
                "name" => $amo_contact["name"],
                "first_name" => (empty($amo_contact["first_name"])) ? null : $amo_contact["first_name"],
                "last_name" => (empty($amo_contact["last_name"])) ? null : $amo_contact["last_name"],
                "responsible_user_id" => $amo_contact["responsible_user_id"],
                "group_id" => $amo_contact["group_id"],
                "updated_by" => $amo_contact["updated_by"],
                "created_by" => $amo_contact["created_by"],
                "created_at" => date("Y-m-d H:i:s", $amo_contact["created_at"]),
                "updated_at" => date("Y-m-d H:i:s", $amo_contact["updated_at"]),
                "closest_task_at" => $amo_contact["closest_task_at"],
                "is_deleted" => $amo_contact["is_deleted"]
            ];
            if ((isset($amo_contact["custom_fields_values"])) && (!empty($amo_contact["custom_fields_values"]))) {
                foreach ($amo_contact["custom_fields_values"] as $cf) {
                    if ($cf["field_id"] == 302541) {
                        foreach ($cf["values"] as $val) {
                            if ((!isset($new_contact["phone"])) || (empty($new_contact["phone"]))) {
                                $new_contact["phone"] = preg_replace('/[^0-9]/', '', $val["value"]);
                            } else {
                                $new_contact["phone"] .= "," . preg_replace('/[^0-9]/', '', $val["value"]);
                            }
                        }
                    }
                    if ($cf["field_id"] == 302543) {
                        foreach ($cf["values"] as $val) {
                            if ((!isset($new_contact["email"])) && (empty($new_contact["email"]))) {
                                $new_contact["email"] = $val["value"];
                            } else {
                                $new_contact["email"] .= "," . $val["value"];
                            }
                        }
                    }
                }
                $new_contact["custom_fields"] = json_encode($amo_contact["custom_fields_values"]);
            }
            AmoContacts::updateOrInsert(["id" => $amo_contact["id"]], $new_contact);
        }
    }

    public function filterLeads(Request $request)
    {
        $filtr = $request['filtr'];
        $params = null;
        $statuses = [];
        $created = null;
        $updated = null;
        $status_list = $this->loadPipelinesWithStatuses();
        if (isset($filtr['created_from'])) {
            $created["from"] = strtotime($filtr['created_from']);
        }
        if (isset($filtr['created_to'])) {
            $created["to"] = strtotime($filtr['created_to']);
        }
        if (!empty($created)) {
            $params["filter"]["created_at"] = $created;
        }

        if (isset($filtr['update_from'])) {
            $updated["from"] = strtotime($filtr['update_from']);
        }
        if (isset($filtr['update_to'])) {
            $updated["to"] = strtotime($filtr['update_to']);
        }
        if (!empty($updated)) {
            $params["filter"]["updated_at"] = $updated;
        }

        if (!empty($filtr['statuses'])) {
            foreach ($filtr['statuses'] as $status) {
                $statuses[] = [
                    "pipeline_id" => $filtr['pipeline_id'],
                    "status_id" => $status
                ];
            }
            $params["filter"]["statuses"] = $statuses;
        }

        $leads_list = null;
        if ($request['show'] != 'all') {
            $count = $request['show'];
            $page = $request['page'];
            $params["page"] = $page;
            $params["limit"] = $request['show'];
            $leads = $this->amo->getLeads(null, $params);
            if (isset($leads['_embedded']['leads'])) {
                foreach ($leads['_embedded']['leads'] as $lead) {
                    $pipeline_id = $lead['pipeline_id'];
                    $status_id = $lead['status_id'];
                    $created_at = date('d-m-Y H:i:s', $lead['created_at']);
                    $updated_at = date('d-m-Y H:i:s', $lead['updated_at']);


                    $leads_list[] = [
                        "lead_id" => $lead['id'],
                        "lead_name" => $lead['name'],
                        "pipeline_name" => $status_list[$pipeline_id]['name'],
                        "status_name" => $status_list[$pipeline_id]['statuses'][$status_id],
                        "created_at" => $created_at,
                        "updated_at" => $updated_at,
                    ];
                    // $leads_list[] = $lead;
                }
            }
        } else {

            $page = 1;
            while (1) {
                $params["page"] = $page;
                $params["limit"] = 250;
                $leads = $this->amo->getLeads(null, $params);

                if (isset($leads['_embedded']['leads'])) {
                    foreach ($leads['_embedded']['leads'] as $lead) {
                        $pipeline_id = $lead['pipeline_id'];
                        $status_id = $lead['status_id'];
                        $created_at = date('d-m-Y H:i:s', $lead['created_at']);
                        $updated_at = date('d-m-Y H:i:s', $lead['updated_at']);


                        $leads_list[] = [
                            "lead_id" => $lead['id'],
                            "lead_name" => $lead['name'],
                            "pipeline_name" => $status_list[$pipeline_id]['name'],
                            "status_name" => $status_list[$pipeline_id]['statuses'][$status_id],
                            "created_at" => $created_at,
                            "updated_at" => $updated_at,
                        ];
                        // $leads_list[] = $lead;
                    }
                    $page++;
                } else {
                    break;
                }

            }
        }
        return $leads_list;


    }

    public function loadPipelinesWithStatuses()
    {
        $pipelines = $this->amo->getPipelines();
        $status_list = [];
        foreach ($pipelines as $pipeline) {

            $statuses = $pipeline['_embedded']['statuses'];
            $tmp = [];

            foreach ($statuses as $status) {
                $tmp[$status['id']] = $status['name'];
            }

            $status_list[$pipeline['id']] = [
                'name' => $pipeline['name'],
                "statuses" => $tmp
            ];
        };
        return $status_list;
    }

    public function getLeadsByList($list)
    {
        $list = json_decode($list);
        $leads = null;
        foreach ($list as $lead) {
            $amo = $this->amo->getLeadFromID($lead);
            $leads[] = [
                "lead_id" => $amo["id"],
                "lead_name" => $amo["name"],
                "contacts" => $amo["_embedded"]["contacts"]
            ];
        }
        return $leads;
    }

    public function getLeadsByFilter($filtr)
    {
        $params = null;
        if ($filtr['created_from']) {
            $created["from"] = strtotime($filtr['created_from']);
        }
        if ($filtr['created_to']) {
            $created["to"] = strtotime($filtr['created_to']);
        }
        if (!empty($created)) {
            $params["filter"]["created_at"] = $created;
        }

        if (isset($filtr['update_from'])) {
            $updated["from"] = strtotime($filtr['update_from']);
        }
        if (isset($filtr['update_to'])) {
            $updated["to"] = strtotime($filtr['update_to']);
        }
        if (!empty($updated)) {
            $params["filter"]["updated_at"] = $updated;
        }

        if (!empty($filtr['statuses'])) {
            foreach ($filtr['statuses'] as $status) {
                $statuses[] = [
                    "pipeline_id" => $filtr['pipeline_id'],
                    "status_id" => $status
                ];
            }
            $params["filter"]["statuses"] = $statuses;
        }
        $leads_list = null;
        $page = 1;
        while (1) {
            $params["page"] = $page;
            $params["limit"] = 250;
            $leads = $this->amo->getLeads(null, $params);

            if (isset($leads['_embedded']['leads'])) {
                foreach ($leads['_embedded']['leads'] as $lead) {
                    $leads_list[] = [
                        "lead_id" => $lead['id'],
                        "lead_name" => $lead['name'],
                        "contacts" => $lead['_embedded']['contacts']
                    ];
                }
//                return $leads_list;
                $page++;
            } else {
                break;
            }


        }
        return $leads_list;


    }

    public function dev()
    {
        $params = [
            "limit" => 250,
        ];
        $get = true;
        $page = 0;
        $total_contacts = null;
        while ($get) {
            $params["page"] = $page;
            $amo_contacts = $this->amo->getContacts(null, $params);
            if (!empty($amo_contacts["_embedded"]["contacts"])) {
                $total_contacts += sizeof($amo_contacts["_embedded"]["contacts"]);
                foreach ($amo_contacts["_embedded"]["contacts"] as $contact) {
                    $new_contact = [
                        "name" => $contact["name"],
                        "first_name" => (empty($contact["first_name"])) ? null : $contact["first_name"],
                        "last_name" => (empty($contact["last_name"])) ? null : $contact["last_name"],
                        "responsible_user_id" => $contact["responsible_user_id"],
                        "group_id" => $contact["group_id"],
                        "updated_by" => $contact["updated_by"],
                        "created_by" => $contact["created_by"],
                        "created_at" => date("Y-m-d H:i:s", $contact["created_at"]),
                        "updated_at" => date("Y-m-d H:i:s", $contact["updated_at"]),
                        "closest_task_at" => $contact["closest_task_at"],
                        "is_deleted" => $contact["is_deleted"]
                    ];
                    if ((isset($contact["custom_fields_values"])) && (!empty($contact["custom_fields_values"]))) {
                        foreach ($contact["custom_fields_values"] as $cf) {
                            if ($cf["field_id"] == 302541) {
                                foreach ($cf["values"] as $val) {
                                    if ((!isset($new_contact["phone"])) || (empty($new_contact["phone"]))) {
                                        $new_contact["phone"] = preg_replace('/[^0-9]/', '', $val["value"]);
                                    } else {
                                        $new_contact["phone"] .= "," . preg_replace('/[^0-9]/', '', $val["value"]);
                                    }
                                }
                            }
                            if ($cf["field_id"] == 302543) {
                                foreach ($cf["values"] as $val) {
                                    if ((!isset($new_contact["email"])) && (empty($new_contact["email"]))) {
                                        $new_contact["email"] = $val["value"];
                                    } else {
                                        $new_contact["email"] .= "," . $val["value"];
                                    }
                                }
                            }
                        }
                        $new_contact["custom_fields"] = json_encode($contact["custom_fields_values"]);
                    }
                    AmoContacts::updateOrInsert(["id" => $contact["id"]], $new_contact);
                }
            }
            print_r("Page - $page, contacts number - $total_contacts\n");
            if (isset($amo_contacts["_links"]["next"])) {
                $page++;
            } else {
                print_r($amo_contacts);
                $get = false;
            }
        }
        //dd($this->amo->getLeadFromID(11193527));
    }


    public function findContact($phone){
        $contact = $this->amo->getContacts($phone);
        if (isset($contact["_embedded"]["contacts"][0])){
            return $contact["_embedded"]["contacts"][0];
        }
        else{
            return false;
        }
    }
}
