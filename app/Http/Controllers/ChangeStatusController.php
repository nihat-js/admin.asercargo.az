<?php

namespace App\Http\Controllers;

use App\BalanceLog;
use App\ChangeStatusLog;
use App\Countries;
use App\EmailListContent;
use App\ExchangeRate;
use App\Flight;
use App\Http\Controllers\Classes\SMS;
use App\Jobs\CollectorInWarehouseJob;
use App\Package;
use App\PackageStatus;
use App\PaymentLog;
use App\SmsTask;
use App\Status;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ChangeStatusController extends HomeController
{

    public function __construct(NotificationController $notification)
    {
        parent::__construct();
        $this->notification = $notification;
    }

    public function get_packages_in_baku_page()
    {
        try {
            $flights = Flight::whereNull('flight.deleted_by')
                ->whereNull('flight.status_in_baku_date')
                ->orderBy('flight.id', 'desc')
                ->take(100)
                ->select('flight.id', 'flight.name')
                ->get();

            return view('backend.change_status.in_baku', compact(
                'flights'
            ));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function post_packages_in_baku(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'flight' => 'required|integer',
            "branch" => 'sometimes|integer'
        ]);
        if ($validator->fails()) {
            if (Auth::user()->role() == 1) {
                //admin
                Session::flash('message', 'Flight not found!');
                Session::flash('class', 'warning');
                Session::flash('display', 'block');
                return redirect()->route("admin_get_packages_in_baku_page");
            } else {
                return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Flight not found!']);
            }
        }
        // return response()->json($request->all());
        try {
            $packages = Package::leftJoin('container as con', 'package.last_container_id', '=', 'con.id')
                ->leftJoin('flight as flt', 'con.flight_id', '=', 'flt.id')
                ->leftJoin('locations as l', 'flt.location_id', '=', 'l.id')
                ->leftJoin('countries as c', 'l.country_id', '=', 'c.id')
                ->leftJoin('users as client', 'package.client_id', '=', 'client.id')
                ->leftJoin('seller', 'package.seller_id', '=', 'seller.id')
                ->where(['con.flight_id' => $request->flight, 'package.in_baku' => 1])
                ->where('package.sms_sent', '<>', 1)
                ->where('package.is_warehouse', '<>', 3) //
                ->whereNull('package.customs_date')
                ->whereNull('package.delivered_by')
                ->select('package.number', 'client.phone1 as phone', 'package.id', 'package.client_id', 'client.client_sent_sms', 'package.is_warehouse', 'client.name', 'client.surname', 'client.email', 'seller.name as store','package.seller_id', 'package.hash', 'package.sms_sent', 'client.language', 'c.name_az as country','flt.id as flight_id','flt.name as flight_name','package.branch_id as branch_id')
                ->orderBy('package.client_id');
                // ->get();

            if ($request->branch){
                $packages->where('package.branch_id', $request->branch);
            }
            $packages = $packages->get();

            // return response()->json($packages);



            if (count($packages) == 0) {
                if (Auth::user()->role() == 1) {
                    Session::flash('message', 'Packages not found!');
                    Session::flash('class', 'warning');
                    Session::flash('display', 'block');
                    return redirect()->route("admin_get_packages_in_baku_page");
                } else {
                    return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Packages not found!']);
                }
            }

            $email = EmailListContent::where(['type' => 'in_baku_list'])->first();

            if (!$email) {
                if (Auth::user()->role() == 1) {
                    Session::flash('message', 'Email template not found!');
                    Session::flash('class', 'warning');
                    Session::flash('display', 'block');
                    return redirect()->route("admin_get_packages_in_baku_page");
                } else {
                    return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Email template not found!']);
                }
            }

            $send_notification = $this->in_baku_notification($packages, $email);
            // $send_notification = true;

            if (!$send_notification) {
                if (Auth::user()->role() == 1) {
                    Session::flash('message', 'Sorry something went wrong (1)!');
                    Session::flash('class', 'danger');
                    Session::flash('display', 'block');
                    return redirect()->route("admin_get_packages_in_baku_page");
                } else {
                    return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry something went wrong (1)!']);
                }
            }

            // return response()->json($email);

           Flight::where('id', $request->flight)->update(['status_in_baku_date' => Carbon::now()]);

            $userGroup = $packages->pluck('client_id')->toArray();
            $country = $packages->pluck('country')->toArray();
            $track = $packages->pluck('number')->toArray();
            //$this->sendNotificationMethod($userGroup, $country, 15, $track);

            if ($request->branch){
                return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Status changed for the packages in selected flight and branch!']);
            }
            if (Auth::user()->role() == 1) {
                Session::flash('message', 'Status changed for the packages in selected flight!');
                Session::flash('class', 'success');
                Session::flash('display', 'block');
                return redirect()->route("admin_get_packages_in_baku_page");
            } else {
                return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Status changed for the packages in selected flight!']);
            }
        } catch (\Exception $exception) {
            if (Auth::user()->role() == 1) {
                Session::flash('message', 'Sorry something went wrong (2)!');
                Session::flash('class', 'danger');
                Session::flash('display', 'block');
                return redirect()->route("admin_get_packages_in_baku_page");
            } else {
                return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry something went wrong (2)!']);
            }
        }
    }

    private function in_baku_notification($packages, $email)
    {
        try {
            // variables for sms
            $sms = new SMS();
            $i = 0;
            $date = Carbon::now();
            $phone_arr_az = array();
            $phone_arr_en = array();
            $phone_arr_ru = array();
            $text = '';

            // variables for email
            $email_to = '';
            $email_title = '';
            $email_subject = '';
            $email_button = '';
            $email_bottom = '';
            $email_content = '';
            $email_list_inside = '';
            $list_insides = '';

            $client_id_for_email = 0;
            $client_id_for_sms = 0;
            $package_arr_for_warehouse_status = array();
            // $package_arr_for_in_baku_status = array();
            $no = 0;
            foreach ($packages as $package) {
                //                $warehouse = $package->is_warehouse;
                //                if ($warehouse == 3) {
                //                    //already in baku (already sent notification and change status)
                //                    continue;
                //                }
                // dd($package->hash);

                // $hash = Package::whereNotNull('hash')->get();

                // dd($hash['hash']);

                // change status
                array_push($package_arr_for_warehouse_status, $package->id);
                // array_push($package_arr_for_in_baku_status, $package->id);

                PackageStatus::create([
                    'package_id' => $package->id,
                    'status_id' => 15, //In Baku
                    'created_by' => Auth::id()
                ]);

                // dd($package->hash);

                if($package->seller_id != 1338 && $package->hash == null){
                    // dd('tst');
                    // send sms
                    if ($package->client_sent_sms != 0 && $package->client_id != 0 && $package->client_id != null && $package->phone != null && $package->sms_sent == 2) {
                        if ($package->client_id != $client_id_for_sms) {
                            // new client
                            $language_for_sms = strtoupper($package->language);
                            switch ($language_for_sms) {
                                case 'AZ':
                                    {
                                        array_push($phone_arr_az, $package->phone);
                                    }
                                    break;
                                case 'EN':
                                    {
                                        array_push($phone_arr_en, $package->phone);
                                    }
                                    break;
                                case 'RU':
                                    {
                                        array_push($phone_arr_ru, $package->phone);
                                    }
                                    break;
                            }
    
                            $client_id_for_sms = $package->client_id;
                        }
                    }
    
                    //send email
                    if ($package->client_id != 0 && $package->client_id != null && $package->email != null) {
                        if ($package->client_id != $client_id_for_email) {
                            // new client
                            if ($client_id_for_email != 0) {
                                $email_content = str_replace('{list_inside}', $list_insides, $email_content);
    
                                $job = (new CollectorInWarehouseJob($email_to, $email_title, $email_subject, $email_content, $email_bottom, $email_button))
                                    ->delay(Carbon::now()->addSeconds(10));
                                dispatch($job);
                            }
    
                            $list_insides = '';
    
                            $language = $package->language;
                            $language = strtolower($language);
    
                            $email_title = $email->{'title_' . $language}; //from
                            $email_subject = $email->{'subject_' . $language};
                            $email_bottom = $email->{'content_bottom_' . $language};
                            $email_button = $email->{'button_name_' . $language};
                            $email_content = $email->{'content_' . $language};
                            $email_list_inside = $email->{'list_inside_' . $language};
    
                            $email_push_content = $email->{'push_content_' . $language};
                            $email_push_content = str_replace('{tracking_number}', $package->number, $email_push_content);

                            $list_inside = $email_list_inside;
    
                            $no++;
                            $list_inside = str_replace('{tracking_number}', $package->number, $list_inside);
                            $list_inside = str_replace('{no}', $no, $list_inside);
    
                            $list_insides .= $list_inside;
    
                            $email_to = $package->email;
                            $client = $package->name . ' ' . $package->surname;
                            $email_content = str_replace('{name_surname}', $client, $email_content);
    
                            $client_id_for_email = $package->client_id;
                        } else {
                            // same client
                            $list_inside = $email_list_inside;
    
                            $no++;
                            $list_inside = str_replace('{tracking_number}', $package->number, $list_inside);
                            $list_inside = str_replace('{no}', $no, $list_inside);
    
                            $list_insides .= $list_inside;
                        }
                    }

                    //$content_in_baku = empty($email_push_content) ? $email_content : $email_push_content;

                    //$this->notification->sendNotification($email_title, $email_subject, $content_in_baku, $package->client_id);

                }

            }

            // send email
            if ($client_id_for_email != 0) {
                $email_content = str_replace('{list_inside}', $list_insides, $email_content);

                // $job = (new CollectorInWarehouseJob($email_to, $email_title, $email_subject, $email_content, $email_bottom, $email_button))
                    // ->delay(Carbon::now()->addSeconds(10));
                // dispatch($job);
            }

            // change status warehouse
            if (count($package_arr_for_warehouse_status) > 0) {
                Package::whereIn('id', $package_arr_for_warehouse_status)->update(['is_warehouse' => 3]);
            }

            // // change status in_baku
            // if (count($package_arr_for_in_baku_status) > 0) {
            //     Package::whereIn('id', $package_arr_for_in_baku_status)->update([
            //         'in_baku' => 1,
            //         'in_baku_date' => Carbon::now(),
            //     ]);
            // }

            // dd($phone_arr_az);
            // send sms az
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

                    $package_arr_for_sms = array();
                    foreach ($packages as $package) {
                        if ($package->client_sent_sms == 0 || $package->client_id == 0 || $package->client_id == null || $package->phone == null || $package->sms_sent != 2 || !in_array($package->phone, $phone_arr_az)) {
                            continue;
                        }

                        $i++;

                        array_push($package_arr_for_sms, $package->id);

                        // SmsTask::create([
                        //     'type' => 'in_baku',
                        //     'code' => $response_code,
                        //     'task_id' => $task_id,
                        //     'control_id' => $control_id,
                        //     'package_id' => $package->id,
                        //     'client_id' => $package->client_id,
                        //     'number' => $package->phone,
                        //     'message' => $text,
                        //     'created_by' => Auth::id()
                        // ]);
                    }

                    if ($i > 0) {
                        Package::whereIn('id', $package_arr_for_sms)->update(['sms_sent' => $sms_status, 'sms_sent_date' => $date]);
                    }
                }
            }

            // send sms en
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

                    $package_arr_for_sms = array();
                    foreach ($packages as $package) {
                        if ($package->client_sent_sms == 0 || $package->client_id == 0 || $package->client_id == null || $package->phone == null || $package->sms_sent != 2 || !in_array($package->phone, $phone_arr_en)) {
                            continue;
                        }

                        $i++;

                        array_push($package_arr_for_sms, $package->id);

                        SmsTask::create([
                            'type' => 'in_baku',
                            'code' => $response_code,
                            'task_id' => $task_id,
                            'control_id' => $control_id,
                            'package_id' => $package->id,
                            'client_id' => $package->client_id,
                            'number' => $package->phone,
                            'message' => $text,
                            'created_by' => Auth::id()
                        ]);
                    }

                    if ($i > 0) {
                        Package::whereIn('id', $package_arr_for_sms)->update(['sms_sent' => $sms_status, 'sms_sent_date' => $date]);
                    }
                }
            }

            // send sms ru
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

                    $package_arr_for_sms = array();
                    foreach ($packages as $package) {
                        if ($package->client_sent_sms == 0 || $package->client_id == 0 || $package->client_id == null || $package->phone == null || $package->sms_sent != 2 || !in_array($package->phone, $phone_arr_ru)) {
                            continue;
                        }

                        $i++;

                        array_push($package_arr_for_sms, $package->id);

                        SmsTask::create([
                            'type' => 'in_baku',
                            'code' => $response_code,
                            'task_id' => $task_id,
                            'control_id' => $control_id,
                            'package_id' => $package->id,
                            'client_id' => $package->client_id,
                            'number' => $package->phone,
                            'message' => $text,
                            'created_by' => Auth::id()
                        ]);
                    }

                    if ($i > 0) {
                        Package::whereIn('id', $package_arr_for_sms)->update(['sms_sent' => $sms_status, 'sms_sent_date' => $date]);
                    }
                }
            }

            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    public function change_status_for_single_package(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'package_id' => ['required', 'integer'],
            'status_id' => ['required', 'integer'],
            'back_paid' => ['nullable', 'integer'],
            'from_balance' => ['nullable', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            switch ($request->status_id) {
                case 2:
                    {
                        // paid
                        return $this->paid_status($request->package_id, $request->from_balance);
                    }
                    break;
                case 10:
                    {
                        // unpaid
                        return $this->unpaid_status($request->package_id, $request->back_paid);
                    }
                    break;
                case 15:
                    {
                        // in baku
                        return $this->in_baku_status($request->package_id);
                    }
                    break;
                case 3:
                    {
                        // delivered
                        return $this->delivered_status($request->package_id);
                    }
                    break;
                case 14:
                    {
                        // on the way
                        return $this->on_the_way_status($request->package_id);
                    }
                    break;
                case 29:
                    {
                        // detained smart custom
                        return $this->detained_smart_custom($request->package_id);
                    }
                    break;
                case 37:
                    {
                    // not declared
                    return $this->not_declared_status($request->package_id);
                    }
                    break;
                case 42:
                    {
                        // out of delivery
                        return $this->out_of_delivery($request->package_id, $request->status_id);
                    }
                    break;
                case 43:
                    {
                        // hold by customer
                        return $this->hold_by_customer($request->package_id, $request->status_id);
                    }
                    break;
                default:
                {
                    return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Wrong status!']);
                }
            }
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    private function detained_smart_custom($package_id)
    {
        try {
            $package = Package::whereNull('deleted_by')
                ->with('client')
                ->where('id', $package_id)
                ->select('id', 'last_status_id', 'client_id', 'number', 'gross_weight as weight')
                ->first();
       
            if (!$package) {
                return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Package not found!']);
            }


             // variables for email
             $email_to = '';
             $email_title = '';
             $email_subject = '';
             $email_bottom = '';
             $email_button = '';
             $email_content = '';
             $email_list_inside = '';
             $list_insides = '';
 
             $email = EmailListContent::where(['type' => 'detained_at_customs'])->first();
 
             if (!$email) {
                 return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Email template not found!']);
             }
 
             $client_id = 0;
             $no = 0;
            
                 PackageStatus::create(['package_id'=>$package->id, 'status_id'=>29, 'created_by' => Auth::id()]); // custom status
             
        
                 //send email
                 if ($package->client_id != 0 && $package->client_id != null && $package->client->email != null) {
                
                    if ($package->client_id != $client_id) {
      
                         // new client
                        if ($client_id != 0) {
                            $email_content = str_replace('{list_inside}', $list_insides, $email_content);

                            $job = (new CollectorInWarehouseJob($email_to, $email_title, $email_subject, $email_content, $email_bottom, $email_button))
                                ->delay(Carbon::now()->addSeconds(10));
                            dispatch($job);
                        }
                  
                         $list_insides = '';
 
                         $language = $package->client->language;
                         $language = strtolower($language);
 
                         $email_title = $email->{'title_' . $language}; //from
                         $email_subject = $email->{'subject_' . $language};
                         $email_bottom = $email->{'content_bottom_' . $language};
                         $email_button = $email->{'button_name_' . $language};
                         $email_content = $email->{'content_' . $language};
                         $email_list_inside = $email->{'list_inside_' . $language};
                         $list_inside = $email_list_inside;
                         $no++;
                         $list_inside = str_replace('{tracking_number}', $package->number, $list_inside);
                         $list_inside = str_replace('{weight}', $package->weight . ' kg', $list_inside);
                         $list_inside = str_replace('{no}', $no, $list_inside);
         
                         
                         $list_insides .= $list_inside;
                        
                         $email_to = $package->client->email;
                         $client = $package->client->name . ' ' . $package->client->surname;
                         $email_content = str_replace('{name_surname}', $client, $email_content);
                        
                         $client_id = $package->client_id;
                     } else {
                         // same client
                         $list_inside = $email_list_inside;
                         $no++;
                         $list_inside = str_replace('{tracking_number}', $package->number, $list_inside);
                         $list_inside = str_replace('{weight}', $package->weight, $list_inside);
                         $list_inside = str_replace('{no}', $no, $list_inside);
 
                         $list_insides .= $list_inside;
                     }

                     $content_detained_customs = empty($email_push_content) ? $email_content : $email_push_content;
                     $this->notification->sendNotification($email_title, $email_subject, $content_detained_customs, $package->client_id);
                 }
        
            //  dd('test');
 
             // send email
             if ($client_id != 0) {
                 $email_content = str_replace('{list_inside}', $list_insides, $email_content);
                 $job = (new CollectorInWarehouseJob($email_to, $email_title, $email_subject, $email_content, $email_bottom, $email_button))
                     ->delay(Carbon::now()->addSeconds(10));
                 dispatch($job);
             }

            Package::where('id', $package_id)->update(['customs_notification'=>1]);

            ChangeStatusLog::create([
                'package_id' => $package_id,
                'old_status_id' => $package->last_status_id,
                'new_status_id' => 29,
                'created_by' => Auth::id()
            ]);
            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Status changed: Detained at Customs!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    private function not_declared_status($packageId)
    {
        try {
            $package = Package::whereNull('deleted_by')
                ->where('id', $packageId)
                ->select('id', 'last_status_id')
                ->first();

            if (!$package) {
                return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Package not found!']);
            }

            Package::where('id', $packageId)->update([
            //               'carrier_status_id' => 0,
            //               'carrier_registration_number' => null,
               'delivered_by' => null,
               'customs_date' => null,
               'on_the_way_date' => null,
            ]);
            PackageStatus::create([
                'package_id' => $packageId,
                'status_id' => 37,
                'created_by' => Auth::id()
            ]);
            ChangeStatusLog::create([
                'package_id' => $packageId,
                'old_status_id' => $package->last_status_id,
                'new_status_id' => 37,
                'created_by' => Auth::id()
            ]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Status changed: Not declared!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    private function on_the_way_status($package_id)
    {
        try {
            $package = Package::whereNull('deleted_by')
                ->where('id', $package_id)
                ->select('id', 'last_status_id')
                ->first();

            if (!$package) {
                return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Package not found!']);
            }

            Package::where('id', $package_id)->update([
                'in_baku' => 0,
                'delivered_by' => null,
                'customs_date' => null,
                'is_warehouse' => 2,
                'on_the_way_date' => Carbon::now()
            ]);;

            PackageStatus::create([
                'package_id' => $package_id,
                'status_id' => 14, // on the way
                'created_by' => Auth::id()
            ]);

            ChangeStatusLog::create([
                'package_id' => $package_id,
                'old_status_id' => $package->last_status_id,
                'new_status_id' => 14,
                'created_by' => Auth::id()
            ]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Status changed: Delivered!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    private function in_baku_status($package_id)
    {
        try {
            $packages = Package::leftJoin('container as con', 'package.last_container_id', '=', 'con.id')
                ->leftJoin('flight as flt', 'con.flight_id', '=', 'flt.id')
                ->leftJoin('users as client', 'package.client_id', '=', 'client.id')
                ->leftJoin('seller', 'package.seller_id', '=', 'seller.id')
                ->where('package.id', $package_id)
                ->whereNull('package.deleted_by')
                ->select('package.number', 'client.phone1 as phone', 'package.id', 'package.seller_id', 'package.hash', 'package.client_id', 'client.client_sent_sms', 'package.is_warehouse', 'client.name', 'client.surname', 'client.email', 'seller.name as store', 'package.sms_sent', 'client.language', 'package.last_status_id')
                ->get();

            if (count($packages) == 0) {
                return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Package not found!']);
            }

            $email = EmailListContent::where(['type' => 'in_baku_list'])->first();

            if (!$email) {
                return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Email template not found!']);
            }

            $send_notification = $this->in_baku_notification($packages, $email);

            if (!$send_notification) {
                return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry something went wrong (1)!']);
            }

            Package::where('id', $package_id)->update([
                'in_baku' => 1,
                'in_baku_date' => Carbon::now(),
                'is_warehouse' => 3,
                'customs_date' => null,
                'delivered_by' => null
            ]);

            ChangeStatusLog::create([
                'package_id' => $package_id,
                'old_status_id' => $packages[0]->last_status_id,
                'new_status_id' => 15,
                'created_by' => Auth::id()
            ]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Status changed: In baku!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong (2)!']);
        }
    }

    private function delivered_status($package_id)
    {
        try {
            $package = Package::whereNull('delivered_by')
                ->whereNull('deleted_by')
                ->where('id', $package_id)
                ->select('id', 'last_status_id')
                ->first();

            if (!$package) {
                return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Package not found or already delivered!']);
            }

            Package::where('id', $package_id)->update(['delivered_by' => Auth::id(), 'delivered_at' => Carbon::now()]);

            PackageStatus::create([
                'package_id' => $package_id,
                'status_id' => 3, //delivered
                'created_by' => Auth::id()
            ]);

            ChangeStatusLog::create([
                'package_id' => $package_id,
                'old_status_id' => $package->last_status_id,
                'new_status_id' => 3,
                'created_by' => Auth::id()
            ]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Status changed: Delivered!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    private function paid_status($package_id, $from_balance)
    {
        try {
            $package = Package::whereNull('deleted_by')
                ->where('id', $package_id)
                ->where('paid_status', 0)
                ->select('id', 'last_status_id', 'client_id', 'total_charge_value as amount', 'paid', 'currency_id')
                ->first();

            if (!$package) {
                return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Package not found or already paid!']);
            }

            $client_id = $package->client_id;
            $amount = $package->amount;
            $paid = $package->paid;

            $unpaid = $amount - $paid;
            $unpaid = sprintf('%0.2f', $unpaid);

            $payment_type = 4; // by_admin

            if ($from_balance == 1) {
                $payment_type = 1;

                $date = Carbon::now();
                $rates = ExchangeRate::whereDate('from_date', '<=', $date)->whereDate('to_date', '>=', $date)
                    ->select('rate', 'from_currency_id', 'to_currency_id')
                    ->get();

                if (count($rates) == 0) {
                    return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Rates not found!']);
                }

                $client = User::where(['id' => $client_id, 'role_id' => 2])
                    ->whereNull('deleted_by')
                    ->select('balance')
                    ->first();

                if (!$client) {
                    return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Client not found!']);
                }

                $rate_to_usd = $this->calculate_exchange_rate($rates, $package->currency_id, 1);

                $unpaid_usd = $unpaid * $rate_to_usd;
                $unpaid_usd = sprintf('%0.2f', $unpaid_usd);

                $old_balance = $client->balance;

                if ($unpaid_usd > $old_balance) {
                    return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'There is not enough money in the balance.']);
                }

                $rate_to_azn = $this->calculate_exchange_rate($rates, $package->currency_id, 3);

                $unpaid_azn = $unpaid * $rate_to_azn;
                $unpaid_azn = sprintf('%0.2f', $unpaid_azn);

                $new_balance = $old_balance - $unpaid_usd;

                $payment_code = Str::random(20);

                BalanceLog::create([
                    'payment_code' => $payment_code,
                    'amount' => $unpaid_usd,
                    'amount_azn' => $unpaid_azn,
                    'client_id' => $client_id,
                    'status' => 'out',
                    'type' => 'balance',
                    'created_by' => Auth::id()
                ]);

                User::where('id', $client_id)->update(['balance' => $new_balance]);

                $remark = 'amount_was_deducted_from_balance';
            } else {
                $remark = 'amount_was_not_deducted_from_balance';
            }

            PaymentLog::create([
                'payment' => $unpaid,
                'currency_id' => $package->currency_id,
                'client_id' => $client_id,
                'package_id' => $package_id,
                'type' => $payment_type,
                'created_by' => Auth::id()
            ]);

            Package::where('id', $package_id)->update(['paid' => $amount, 'paid_status' => 1]);

            ChangeStatusLog::create([
                'package_id' => $package_id,
                'old_status_id' => $package->last_status_id,
                'new_status_id' => 2,
                'remark' => $remark,
                'created_by' => Auth::id()
            ]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Status changed: Paid!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    private function unpaid_status($package_id, $back_paid)
    {
        try {
            $package = Package::whereNull('deleted_by')
                ->where('id', $package_id)
                ->where('paid_status', 1)
                ->select('id', 'last_status_id', 'client_id', 'paid', 'currency_id', 'total_charge_value as amount', 'discounted_amount')
                ->first();

            if (!$package) {
                return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Package not found or not paid!']);
            }

            Package::where('id', $package_id)->update(['paid' => 0, 'paid_status' => 0]);

            if ($back_paid == 1) {
                $date = Carbon::now();
                $rates = ExchangeRate::whereDate('from_date', '<=', $date)->whereDate('to_date', '>=', $date)
                    ->select('rate', 'from_currency_id', 'to_currency_id')
                    ->get();

                if (count($rates) == 0) {
                    return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Rates not found!']);
                }

                $client_id = $package->client_id;
                $amount = $package->amount;
                $discounted_amount = $package->discounted_amount;
                $paid = $amount - $discounted_amount;

                $client = User::where(['id' => $client_id, 'role_id' => 2])
                    ->whereNull('deleted_by')
                    ->select('balance')
                    ->first();

                if (!$client) {
                    return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Client not found!']);
                }

                $rate_to_usd = $this->calculate_exchange_rate($rates, $package->currency_id, 1);

                $paid_usd = $paid * $rate_to_usd;
                $paid_usd = sprintf('%0.2f', $paid_usd);

                $rate_to_azn = $this->calculate_exchange_rate($rates, $package->currency_id, 3);

                $paid_azn = $paid * $rate_to_azn;
                $paid_azn = sprintf('%0.2f', $paid_azn);

                $old_balance = $client->balance;
                $new_balance = $old_balance + $paid_usd;

                $payment_code = Str::random(20);

                BalanceLog::create([
                    'payment_code' => $payment_code,
                    'amount' => $paid_usd,
                    'amount_azn' => $paid_azn,
                    'client_id' => $client_id,
                    'status' => 'in',
                    'type' => 'back',
                    'created_by' => Auth::id()
                ]);

                User::where('id', $client_id)->update(['balance' => $new_balance]);

                $remark = 'sent_payment_back_to_the_balance';
            } else {
                $remark = 'not_sent_payment_back_to_the_balance';
            }

            ChangeStatusLog::create([
                'package_id' => $package_id,
                'old_status_id' => $package->last_status_id,
                'new_status_id' => 10,
                'remark' => $remark,
                'created_by' => Auth::id()
            ]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Status changed: Unpaid!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    private function calculate_exchange_rate($rates, $from, $to)
    {
        try {
            if ($from == $to) {
                return 1;
            }

            foreach ($rates as $rate) {
                if ($rate->from_currency_id == $from && $rate->to_currency_id == $to) {
                    return $rate->rate;
                }
            }

            return 0;
        } catch (\Exception $exception) {
            return 0;
        }
    }

    private function out_of_delivery($packageId, $statusId)
    {
        try {
            $package = Package::whereNull('deleted_by')
                ->where('id', $packageId)
                ->select('id', 'last_status_id')
                ->first();

            if (!$package) {
                return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Package not found!']);
            }

            Package::where('id', $packageId)->update([
                'last_status_id' => $statusId,
            ]);
            PackageStatus::create([
                'package_id' => $packageId,
                'status_id' => $statusId,
                'created_by' => Auth::id()
            ]);
            ChangeStatusLog::create([
                'package_id' => $packageId,
                'old_status_id' => $package->last_status_id,
                'new_status_id' => $statusId,
                'created_by' => Auth::id()
            ]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Status changed']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    private function hold_by_customer($packageId, $statusId)
    {
        try {
            $package = Package::whereNull('deleted_by')
                ->where('id', $packageId)
                ->select('id', 'last_status_id')
                ->first();

            if (!$package) {
                return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Package not found!']);
            }

            Package::where('id', $packageId)->update([
                'last_status_id' => $statusId,
            ]);
            PackageStatus::create([
                'package_id' => $packageId,
                'status_id' => $statusId,
                'created_by' => Auth::id()
            ]);
            ChangeStatusLog::create([
                'package_id' => $packageId,
                'old_status_id' => $package->last_status_id,
                'new_status_id' => $statusId,
                'created_by' => Auth::id()
            ]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Status changed']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    /* Partner flight custom status changes ind this area*/

    public function get_packages_custom_status_page()
    {
        try {

            $statuses = Status::whereIn('id', [Status::customs_clearance_started, Status::customs_control_started, Status::customs_clearance_in_progress, Status::released_from_customs])->get();

            $flights = Flight::whereNull('flight.deleted_by')
                //->whereNull('flight.status_in_baku_date')
               // ->where('flight.public', 3)
                ->whereNotNull('flight.closed_at')
                ->orderBy('flight.id', 'desc')
                ->take(100)
                ->select('flight.id', 'flight.name')
                ->get();

            return view('backend.change_status.custom_status', compact(
                'flights',
                'statuses'
            ));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    /*public function post_packages_custom_status(Request $request)
    {
        //dd($request->all());
        $validator = Validator::make($request->all(), [
            'flight' => 'required|integer',
            'status' => 'required|integer'
        ]);
        //dd($validator->fails());
        if ($validator->fails()) {
            if (Auth::user()->role() == 1) {
                //admin
                Session::flash('message', 'Flight not found!');
                Session::flash('class', 'warning');
                Session::flash('display', 'block');
                return redirect()->route("admin_get_custom_status");
            } else {
                return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Flight not found!']);
            }
        }
        try {

            $reg_status = $request->status;

            $packages = Package::leftJoin('container as con', 'package.last_container_id', '=', 'con.id')
                ->leftJoin('flight as flt', 'con.flight_id', '=', 'flt.id')
                ->leftJoin('locations as l', 'flt.location_id', '=', 'l.id')
                ->leftJoin('countries as c', 'l.country_id', '=', 'c.id')
                ->where(['con.flight_id' => $request->flight, 'package.in_baku' => 0])
                //->whereNotNull('package.partner_id')
                ->whereNull('package.deleted_at')
                ->whereNotNull('flt.closed_at')
                ->select('package.id', 'package.client_id', 'c.name_az as country')
                ->orderBy('package.client_id')
                ->get();


            if (count($packages) == 0) {
                if (Auth::user()->role() == 1) {
                    Session::flash('message', 'Packages not found!');
                    Session::flash('class', 'warning');
                    Session::flash('display', 'block');
                    return redirect()->route("admin_get_custom_status");
                } else {
                    return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Packages not found!']);
                }
            }

            foreach ($packages as $package){
                $package->update([
                    'last_status_id' => $request->status,
                    'last_status_date' => Carbon::now()
                ]);

                PackageStatus::create([
                    'package_id' => $package->id,
                    'status_id' => $request->status,
                    'created_by' => Auth::id()
                ]);
            }


            Session::flash('message', 'Status changed for the packages in selected flight!');
            Session::flash('class', 'success');
            Session::flash('display', 'block');
            return redirect()->route("admin_get_custom_status");
           // return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Status changed for the packages in selected flight!']);

        } catch (\Exception $exception) {
            //dd($exception);
            if (Auth::user()->role() == 1) {
                Session::flash('message', 'Sorry something went wrong (2)!');
                Session::flash('class', 'danger');
                Session::flash('display', 'block');
                return redirect()->route("admin_get_custom_status");
            } else {
                return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry something went wrong (2)!']);
            }
        }
    }*/

    public function post_packages_custom_status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'flight' => 'required|integer',
            'status' => 'required|integer'
        ]);

        if ($validator->fails()) {
            $response = (Auth::user()->role() == 1) ? redirect()->route("admin_get_custom_status") : response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Flight not found!']);
            Session::flash('message', 'Flight not found!');
            Session::flash('class', 'warning');
            Session::flash('display', 'block');
            return $response;
        }

        try {
            $reg_status = $request->status;

            $packages = Package::leftJoin('container as con', 'package.last_container_id', '=', 'con.id')
                ->leftJoin('flight as flt', 'con.flight_id', '=', 'flt.id')
                ->leftJoin('locations as l', 'flt.location_id', '=', 'l.id')
                ->leftJoin('countries as c', 'l.country_id', '=', 'c.id')
                ->where(['con.flight_id' => $request->flight, 'package.in_baku' => 0])
                ->whereNull('package.deleted_at')
                ->whereNotNull('flt.closed_at')
                ->select('package.id', 'package.client_id', 'c.name_az as country', 'package.number')
                ->orderBy('package.client_id')
                ->get();

            if ($packages->isEmpty()) {
                $response = (Auth::user()->role() == 1) ? redirect()->route("admin_get_custom_status") : response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Packages not found!']);
                Session::flash('message', 'Packages not found!');
                Session::flash('class', 'warning');
                Session::flash('display', 'block');
                return $response;
            }

            $packageIds = $packages->pluck('id')->toArray();

            Package::whereIn('id', $packageIds)->update([
                'last_status_id' => $request->status,
                'last_status_date' => now()
            ]);

            $packageStatuses = array_map(function ($packageId) use ($request) {
                return [
                    'package_id' => $packageId,
                    'status_id' => $request->status,
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $packageIds);

            PackageStatus::insert($packageStatuses);

            if($reg_status == 48 || $reg_status == 50){
                $userGroup = $packages->pluck('client_id')->toArray();
                $country = $packages->pluck('country')->toArray();
                $tracking = $packages->pluck('number')->toArray();
                //$this->sendNotificationMethod($userGroup, $country, $reg_status, $tracking);
            }

            Session::flash('message', 'Status changed for the packages in the selected flight!');
            Session::flash('class', 'success');
            Session::flash('display', 'block');
            return redirect()->route("admin_get_custom_status");

        } catch (\Exception $exception) {
            //dd($exception);
            $response = (Auth::user()->role() == 1) ? redirect()->route("admin_get_custom_status") : response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong (2)!']);
            Session::flash('message', 'Sorry, something went wrong (2)!');
            Session::flash('class', 'danger');
            Session::flash('display', 'block');
            return $response;
        }
    }

    public function sendNotificationMethod($userGroup, $country, $status, $tracking = null){
        $groupedArray = array_count_values($userGroup);
        $uniqueValues = array_keys($groupedArray);
        $firstValueOfCountry = reset($country);
        $packageList = implode(', ', $tracking);
        $email = '';

        if($status == 48){
            $email = EmailListContent::where(['type' => 'custom_control_started'])->first();
        }else if($status == 50){
            $email = EmailListContent::where(['type' => 'released_from_custom'])->first();
        }else if($status == 15){
            $email = EmailListContent::where(['type' => 'in_baku'])->first();
        }
        //dd($tracking);
        foreach ($uniqueValues as $clientId => $id) {

            $notification_title = $email->{'title_az'};
            $notification_subject = $email->{'subject_az'};
            $content = $email->{'content_az'};
            $notification_content = $email->{'push_content_az'};
            $notification_content = str_replace('{country_name}', $firstValueOfCountry, $notification_content);
            $notification_content = str_replace('{list_inside}', $packageList, $notification_content);

            $content_check = empty($notification_content) ? $content : $notification_content;

            $this->notification->sendNotification($notification_title, $notification_subject, $content_check, $id);

        }
    }

}
