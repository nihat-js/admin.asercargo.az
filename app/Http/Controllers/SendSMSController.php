<?php

namespace App\Http\Controllers;

use App\EmailListContent;
use App\Http\Controllers\Classes\SMS;
use App\Item;
use App\Location;
use App\Package;
use App\SmsTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class SendSMSController extends HomeController
{
    //no invoice
    public function get_send_sms_for_no_invoice_package_page()
    {
        try {
            $locations = Location::whereNull('deleted_by')
                ->where('id', '<>', 1)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();

            return view('backend.send_sms.send_sms_no_invoice_package', compact(
                'locations'
            ));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function send_sms_for_no_invoice_package(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'location' => 'required|integer'
        ]);
        if ($validator->fails()) {
            Session::flash('message', 'Flight not found!');
            Session::flash('class', 'warning');
            Session::flash('display', 'block');
            return redirect()->route("get_send_sms_for_no_invoice_package_page");
        }
        try {
            $packages = Item::leftJoin('package', 'item.package_id', '=', 'package.id')
                ->leftJoin('position', 'package.position_id', '=', 'position.id')
                ->leftJoin('users as client', 'package.client_id', '=', 'client.id')
                ->where(['position.location_id' => $request->location, 'package.is_warehouse' => 1])
                //->where('package.sms_no_invoice', 2)
                ->where('package.client_id', '<>', 0)
                ->where('item.invoice_confirmed', '<>', 1)
                ->whereRaw('(item.price is null or item.price = 0)')
                ->whereNull('package.deleted_by')
                ->whereNull('package.delivered_by')
                ->whereNotNull('package.position_id')
                ->select('package.id', 'package.number', 'client.phone1 as client', 'package.id', 'package.client_id', 'client.client_sent_sms', 'client.language')
                ->get();

            if (count($packages) == 0) {
                Session::flash('message', 'Packages not found!');
                Session::flash('class', 'warning');
                Session::flash('display', 'block');
                return redirect()->route("get_send_sms_for_no_invoice_package_page");
            }

            $sms = new SMS();
            $count = 0;
            $phone_arr_az = array();
            $phone_arr_en = array();
            $phone_arr_ru = array();
            $message = 'SMS was not sent!';

            foreach ($packages as $package) {
                if ($package->client_sent_sms == 0) {
                    continue;
                }

                $language_for_sms = strtoupper($package->language);
                switch ($language_for_sms) {
                    case 'AZ': {
                        array_push($phone_arr_az, $package->client);
                    } break;
                    case 'EN': {
                        array_push($phone_arr_en, $package->client);
                    } break;
                    case 'RU': {
                        array_push($phone_arr_ru, $package->client);
                    } break;
                }
            }

            //sms template
            $email = EmailListContent::where(['type' => 'invoice_notification_list'])->first();

            if (!$email) {
                Session::flash('message', 'SMS template not found!');
                Session::flash('class', 'error');
                Session::flash('display', 'block');
                return redirect()->route("get_send_sms_for_no_invoice_package_page");
            }

            if (count($phone_arr_az) > 0) {
                $text = $email->sms_az;

                $control_id = time() . 'az';
                $phone_arr_az = array_unique($phone_arr_az);
                $send_bulk_sms = $sms->sendBulkSms($text, $phone_arr_az, $control_id);

                if ($send_bulk_sms[0] == true) {
                    $response = simplexml_load_string($send_bulk_sms[1], 'SimpleXMLElement', LIBXML_NOCDATA);
                    $json = json_decode(json_encode((array)$response), TRUE);

                    if (isset($json['head']['responsecode'])) {
                        $response_code = $json['head']['responsecode'];
                    } else {
                        $response_code = 'error';
                    }

                    if (isset($json['body']['taskid'])) {
                        $task_id = $json['body']['taskid'];
                    } else {
                        $task_id = 'error';
                    }

                    if ($response_code == '000') {
                        //success
                        $sms_status = 1;
                    } else {
                        //failed
                        $sms_status = 0;
                    }

                    $package_arr_for_sms_az = array();
                    $i = 0;
                    foreach ($packages as $package) {
                        if ($package->client_sent_sms == 0 || !in_array($package->client, $phone_arr_az)) {
                            continue;
                        }

                        $i++;

                        array_push($package_arr_for_sms_az, $package->id);

                        SmsTask::create([
                            'type' => 'no_invoice',
                            'code' => $response_code,
                            'task_id' => $task_id,
                            'control_id' => $control_id,
                            'package_id' => $package->id,
                            'client_id' => $package->client_id,
                            'number' => $package->client,
                            'message' => $text,
                            'created_by' => Auth::id()
                        ]);
                    }
                    $count += $i;

                    if ($i > 0) {
                        Package::whereIn('id', $package_arr_for_sms_az)->update(['sms_no_invoice'=>$sms_status]);
                    }
                }
            }

            if (count($phone_arr_en) > 0) {
                $text = $email->sms_en;

                $control_id = time() . 'en';
                $phone_arr_en = array_unique($phone_arr_en);
                $send_bulk_sms = $sms->sendBulkSms($text, $phone_arr_en, $control_id);

                if ($send_bulk_sms[0] == true) {
                    $response = simplexml_load_string($send_bulk_sms[1], 'SimpleXMLElement', LIBXML_NOCDATA);
                    $json = json_decode(json_encode((array)$response), TRUE);

                    if (isset($json['head']['responsecode'])) {
                        $response_code = $json['head']['responsecode'];
                    } else {
                        $response_code = 'error';
                    }

                    if (isset($json['body']['taskid'])) {
                        $task_id = $json['body']['taskid'];
                    } else {
                        $task_id = 'error';
                    }

                    if ($response_code == '000') {
                        //success
                        $sms_status = 1;
                    } else {
                        //failed
                        $sms_status = 0;
                    }

                    $package_arr_for_sms_en = array();
                    $i = 0;
                    foreach ($packages as $package) {
                        if ($package->client_sent_sms == 0 || !in_array($package->client, $phone_arr_en)) {
                            continue;
                        }

                        $i++;

                        array_push($package_arr_for_sms_en, $package->id);

                        SmsTask::create([
                            'type' => 'no_invoice',
                            'code' => $response_code,
                            'task_id' => $task_id,
                            'control_id' => $control_id,
                            'package_id' => $package->id,
                            'client_id' => $package->client_id,
                            'number' => $package->client,
                            'message' => $text,
                            'created_by' => Auth::id()
                        ]);
                    }
                    $count += $i;

                    if ($i > 0) {
                        Package::whereIn('id', $package_arr_for_sms_en)->update(['sms_no_invoice'=>$sms_status]);
                    }
                }
            }

            if (count($phone_arr_ru) > 0) {
                $text = $email->sms_ru;

                $control_id = time() . 'ru';
                $phone_arr_ru = array_unique($phone_arr_ru);
                $send_bulk_sms = $sms->sendBulkSms($text, $phone_arr_ru, $control_id);

                if ($send_bulk_sms[0] == true) {
                    $response = simplexml_load_string($send_bulk_sms[1], 'SimpleXMLElement', LIBXML_NOCDATA);
                    $json = json_decode(json_encode((array)$response), TRUE);

                    if (isset($json['head']['responsecode'])) {
                        $response_code = $json['head']['responsecode'];
                    } else {
                        $response_code = 'error';
                    }

                    if (isset($json['body']['taskid'])) {
                        $task_id = $json['body']['taskid'];
                    } else {
                        $task_id = 'error';
                    }

                    if ($response_code == '000') {
                        //success
                        $sms_status = 1;
                    } else {
                        //failed
                        $sms_status = 0;
                    }

                    $package_arr_for_sms_ru = array();
                    $i = 0;
                    foreach ($packages as $package) {
                        if ($package->client_sent_sms == 0 || !in_array($package->client, $phone_arr_ru)) {
                            continue;
                        }

                        $i++;

                        array_push($package_arr_for_sms_ru, $package->id);

                        SmsTask::create([
                            'type' => 'no_invoice',
                            'code' => $response_code,
                            'task_id' => $task_id,
                            'control_id' => $control_id,
                            'package_id' => $package->id,
                            'client_id' => $package->client_id,
                            'number' => $package->client,
                            'message' => $text,
                            'created_by' => Auth::id()
                        ]);
                    }
                    $count += $i;

                    if ($i > 0) {
                        Package::whereIn('id', $package_arr_for_sms_ru)->update(['sms_no_invoice'=>$sms_status]);
                    }
                }
            }

            if ($count > 0 ){
                $message = 'SMS was sent for ' . $count . ' package(s)!';
            }

            Session::flash('message', $message);
            Session::flash('class', 'success');
            Session::flash('display', 'block');
            return redirect()->route("get_send_sms_for_no_invoice_package_page");
        } catch (\Exception $exception) {
            Session::flash('message', 'Sorry something went wrong!');
            Session::flash('class', 'danger');
            Session::flash('display', 'block');
            return redirect()->route("get_send_sms_for_no_invoice_package_page");
        }
    }
}
