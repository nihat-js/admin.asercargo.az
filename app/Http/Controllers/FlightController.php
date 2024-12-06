<?php

namespace App\Http\Controllers;

use App\Countries;
use App\EmailListContent;
use App\Flight;
use App\Item;
use App\Jobs\CollectorInWarehouseJob;
use App\Jobs\NotificationJob;
use App\Jobs\PackageStatusJob;
use App\Location;
use App\Package;
use App\PackageStatus;
use App\Services\Carrier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FlightController extends HomeController
{
    /**
     * @var Carrier
     */
    private Carrier $carrier;

    public function __construct(Carrier $carrier, NotificationController $notification)
    {
        parent::__construct();
        $this->carrier = $carrier;
        $this->notification = $notification;
    }

    public function show()
    {
        try {
            $query = Flight::whereNull('deleted_by');

            if (Auth::user()->role() == 3 || Auth::user()->role() == 11) {
                //collector
                $query->whereRaw('(public = 1 or location_id = ?)', Auth::user()->location());

                if(Auth::user()->id == 137297){
                    $query->whereRaw('(public = 1 or location_id = ?)', Auth::user()->location());
                    $query->whereDate('created_at', '>', date('2021-08-20'));
                }
            }

            $search_arr = array(
                'name' => '',
                'created' => '',
            );

            if (!empty(Input::get('name')) && Input::get('name') != '' && Input::get('name') != null) {
                $where_name = Input::get('name');
                $query->where('name', 'LIKE', '%' . $where_name . '%');
                $search_arr['name'] = $where_name;
            }

            if (Auth::user()->role() == 3 || Auth::user()->role() == 11) {
                if (!empty(Input::get('created')) && Input::get('created') != '' && Input::get('created') != null) {
                    $where_created = Input::get('created');
                    $search_arr['created'] = $where_created;
                    if ($where_created == 'byme') {
                        $query->where('created_by', Auth::id());
                    }
                }
            }

            //short by start
            $short_by = 'id';
            $shortType = 'DESC';
            if (!empty(Input::get('shortBy')) && Input::get('shortBy') != '' && Input::get('shortBy') != null) {
                $short_by = Input::get('shortBy');
            }
            if (!empty(Input::get('shortType')) && Input::get('shortType') != '' && Input::get('shortType') != null) {
                $short_type = Input::get('shortType');
                if ($short_type == 2) {
                    $shortType = 'DESC';
                } else {
                    $shortType = 'ASC';
                }
            }
            //short by finish

            $flights = $query
                ->orderBy($short_by, $shortType)
                ->select(
                    'id',
                    'name',
                    'carrier',
                    'flight_number',
                    'awb',
                    'departure',
                    'destination',
//                    'fact_take_off',
//                    'fact_arrival',
                    'plan_take_off',
//                    'plan_arrival',
                    'closed_at'
                )->paginate(20);

            // $before = microtime(true);
            foreach ($flights as $flight) {
                $flight_id = $flight->id;
                // $packages = Item::leftJoin('package', 'item.package_id', '=', 'package.id')
                //     ->leftJoin('container', 'package.container_id', '=', 'container.id')
                //     ->where('container.flight_id', $flight_id)
                //     ->whereNull('package.deleted_by')
                //     ->whereNull('container.deleted_by')
                //     ->select('package.gross_weight')
                //     ->get();

                $packages = Package::leftJoin('container', 'package.container_id', '=', 'container.id')
                    ->where('container.flight_id', $flight_id)
                    ->whereNull('package.deleted_by')
                    ->whereNull('container.deleted_by')
                    ->select('package.gross_weight')
                    ->get();

                $total_weight = 0;
                $packages_count = 0;

                foreach ($packages as $package) {
                    $packages_count++;
                    if ($package->gross_weight != null) {
                        $total_weight += $package->gross_weight;
                    }
                }

                $flight->packages_count = $packages_count;
                $flight->total_weight = $total_weight;
            }
            // $after = microtime(true);
            // dd($after-$before);
            if (Auth::user()->role() == 3 || Auth::user()->role() == 11) {
                return view("backend.flights_for_collector", compact('flights', 'search_arr'));
            }

            return view("backend.flights", compact('flights', 'search_arr'));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'carrier' => ['required', 'string', 'max:3'],
            'flight_number' => ['required', 'date'],
            'awb' => ['nullable', 'string', 'max:15'],
            'departure' => ['required', 'string', 'max:50'],
            'destination' => ['required', 'string', 'max:50'],
            //'plan_take_off' => ['required', 'date'],
            //'plan_arrival' => ['required', 'date'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            unset($request['id']);

            //            $last_flight = Flight::orderBy('id', 'desc')->first();
            //            if ($last_flight) {
            //                $id = $last_flight->id + 1;
            //            } else {
            //                $id = 1;
            //            }
            //            $date = substr($request->plan_take_off, 0, 10);
            //            $date = str_replace('-', '', $date);
            //            $name = $request->departure . $id . $date;

            if (Auth::user()->role() == 3 || Auth::user()->role() == 11) {
                //collector
                $public = 0;
            } else {
                $public = 1;
            }

            $date = strtotime($request->flight_number);
            $day = date('d', $date);
            $month = date('m', $date);
            $year = date('Y', $date);

            $name = $request->carrier . $day . $month . $year;

            $request->merge(['created_by' => Auth::id(), 'name' => $name, 'location_id'=>Auth::user()->location(), 'public'=>$public]);

            Flight::create($request->all());

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'integer'],
            'carrier' => ['required', 'string', 'max:3'],
            'flight_number' => ['required', 'date'],
            'awb' => ['nullable', 'string', 'max:15'],
            'departure' => ['required', 'string', 'max:50'],
            'destination' => ['required', 'string', 'max:50'],
            //'plan_take_off' => ['required', 'date'],
            //'plan_arrival' => ['required', 'date'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $id = $request->id;
            unset($request['id'], $request['_token']);

            if (Auth::user()->role() == 3 || Auth::user()->role() == 11) {
                if (Flight::where(['id'=>$id, 'created_by'=>Auth::id()])->count() == 0) {
//                    return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Access denied!']);
                }
            }

            $date = strtotime($request->flight_number);
            $day = date('d', $date);
            $month = date('m', $date);
            $year = date('Y', $date);

            $name = $request->carrier . $day . $month . $year;

            $request->merge(['name' => $name]);

            if(Auth::user()->getAttribute('role_id') == 1){
                Flight::where(['id' => $id])->update($request->all());           
            }else{
                $test = Flight::where(['id' => $id])->whereNull('closed_at')->update($request->all());
                if(!$test){
                    return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'You cannot update closed flight!']);
                }
            }


            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Id not found!']);
        }
        try {
            if (Auth::user()->role() == 3 || Auth::user()->role() == 11) {
                if (Flight::where(['id'=>$request->id, 'created_by'=>Auth::id()])->count() == 0) {
                    return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Access denied!']);
                }
            }

            Flight::where(['id' => $request->id])->whereNull('deleted_by')->update(['deleted_by' => Auth::id(), 'deleted_at' => Carbon::now()]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'id' => $request->id]);
        } catch (\Exception $e) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }

    public function close(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Flight not found!']);
        }
        try {
            $date = Carbon::now()->toDateTimeString();

            $flight_id = Flight::whereNull('closed_by')->whereNull('closed_at')->where('id', $request->id)->select('id', 'location_id')->first();
            //dd($flight_id);
            if ($flight_id == null) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Flight already closed!']);
            }


            /*if (Flight::whereNotNull('closed_by')->where('id', $request->id)->select('id')->first()) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Flight already closed!']);
            }*/

            //$containers = Container::where('flight_id', $request->id)->where('deleted_by')->select('id')->get();
            $packages = Package::leftJoin('container as con', 'package.container_id', '=', 'con.id')
                ->leftJoin('users as client', 'package.client_id', '=', 'client.id')
                ->where('con.flight_id', $request->id)
                ->where('package.client_id', '<>', 0)
                ->whereNull('package.on_the_way_date')
                ->whereNull('package.deleted_by')
                ->whereNull('con.deleted_by')
                ->select('package.id', 'package.client_id', 'package.number', 'package.carrier_registration_number', 'client.name', 'client.surname', 'client.email', 'client.language', 'client.id as cl_id')
                ->orderBy('package.client_id')
                ->get();

            $packages_arr = array();
            $packagesIds = array();
            if (count($packages) > 0) {
               // $departure_id = Auth::user()->location();
                $departure_id = $flight_id->location_id;
                $country = Location::where('id', $departure_id)->select('country_id')->first();
                if ($country) {
                    $country_id = $country->country_id;
                } else {
                    $country_id = 0;
                }

                // variables for email
                $email_to = '';
                $email_title = '';
                $email_subject = '';
                $email_bottom = '';
                $email_content = '';
                $email_list_inside = '';
                $list_insides = '';

                $email = EmailListContent::where(['type' => 'on_the_way_list'])->first();

                if (!$email) {
                    return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Email template not found!']);
                }

                $client_id_for_email = 0;
                foreach ($packages as $package) {
                    array_push($packages_arr, $package->id);
                    array_push($packagesIds, [
                        'regNumber' => $package->carrier_registration_number,
                        'trackNumber' => $package->number
                    ]);

                    PackageStatus::create([
                        'package_id' => $package->id,
                        'status_id' => 14, // on the way
                        'created_by' => Auth::id()
                    ]);

                    if ($package->client_id != 0 && $package->client_id != null) {
                        if ($package->client_id != $client_id_for_email) {
                            // new client
                            if ($client_id_for_email != 0) {
                                $email_content = str_replace('{list_inside}', $list_insides, $email_content);

                                $job = (new CollectorInWarehouseJob($email_to, $email_title, $email_subject, $email_content, $email_bottom))
                                    ->delay(Carbon::now()->addSeconds(10));
                                dispatch($job);
                            }

                            $list_insides = '';

                            $language = $package->language;
                            $language = strtolower($language);

                            $country_check = Countries::where('id', $country_id)->select('name_' . $language . ' as name')->first();
                            if ($country_check) {
                                $country_name = $country_check->name;
                            } else {
                                $country_name = '---';
                            }

                            $email_title = $email->{'title_' . $language}; //from
                            $email_subject = $email->{'subject_' . $language};
                            $email_bottom = $email->{'content_bottom_' . $language};
                            $email_content = $email->{'content_' . $language};
                            $email_list_inside = $email->{'list_inside_' . $language};

                            $email_push_content = $email->{'push_content_' . $language};
                            $email_push_content = str_replace('{tracking_number}', $package->number, $email_push_content);

                            $list_inside = $email_list_inside;

                            $list_inside = str_replace('{tracking_number}', $package->number, $list_inside);

                            $list_insides .= $list_inside;

                            $email_to = $package->email;
                            $client = $package->name . ' ' . $package->surname;
                            $email_content = str_replace('{name_surname}', $client, $email_content);
                            $email_content = str_replace('{country_name}', $country_name, $email_content);

                            $client_id_for_email = $package->client_id;
                        } else {
                            // same client
                            $list_inside = $email_list_inside;

                            $list_inside = str_replace('{tracking_number}', $package->number, $list_inside);

                            $list_insides .= $list_inside;
                        }
                    }


                }

                // send email
                if ($client_id_for_email != 0) {
                    $email_content = str_replace('{list_inside}', $list_insides, $email_content);

                    $job = (new CollectorInWarehouseJob($email_to, $email_title, $email_subject, $email_content, $email_bottom))
                        ->delay(Carbon::now()->addSeconds(10));
                    dispatch($job);
                }

                $groupedPackages = $packages->groupBy('cl_id');


                /*foreach ($groupedPackages as $clientId => $clientPackages) {
                    $client_id = $clientPackages->pluck('client_id')->first();
                    $language = $clientPackages->pluck('language')->first();
                    $packageNumbers = $clientPackages->pluck('number')->toArray();
                    $packageList = implode(', ', $packageNumbers);

                    $language = strtolower($language);
                    $notification_title = $email->{'title_' . $language}; //from
                    $notification_subject = $email->{'subject_' . $language};
                    $notification_content = $email->{'push_content_' . $language};
                    $notification_content = str_replace('{name_surname}', $client, $notification_content);
                    $notification_content = str_replace('{country_name}', $country_name, $notification_content);

                    $notification_content = str_replace('{list_inside_content}', $packageList, $notification_content);

                    $this->notification->sendNotification($notification_title, $notification_subject, $notification_content, $client_id);

                }*/

               Package::whereIn('id', $packages_arr)->update(['is_warehouse'=>2, 'on_the_way_date' => Carbon::now()]);
            }

            Flight::where(['id' => $request->id])->whereNull('deleted_by')->update(['closed_by' => Auth::id(), 'closed_at' => $date]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'closed_at' => $date]);
        } catch (\Exception $e) {
            //dd($e);
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }

    private function set_fact_take_off(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'fact_take_off' => 'required|date',
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => $validator->errors()->toArray()]);
        }
        try {
            Flight::where(['id' => $request->id])->whereNull('deleted_by')->update(['fact_take_off' => $request->fact_take_off]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $e) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    private function set_fact_arrival(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'fact_arrival' => 'required|date',
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => $validator->errors()->toArray()]);
        }
        try {
            Flight::where(['id' => $request->id])->whereNull('deleted_by')->update(['fact_arrival' => $request->fact_arrival]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $e) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function closeNew(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Flight not found!']);
        }
        try {
            //set_time_limit(9000);
            //$start = time();
            $date = Carbon::now()->toDateTimeString();

            $flight_id = Flight::whereNull('closed_by')->whereNull('closed_at')->where('id', $request->id)->select('id', 'location_id')->first();
            //$flight_id = Flight::where('id', $request->id)->select('id', 'location_id')->first();

            if ($flight_id == null) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Flight already closed!']);
            }


            $packages = Package::leftJoin('container as con', 'package.container_id', '=', 'con.id')
                ->leftJoin('users as client', 'package.client_id', '=', 'client.id')
                ->where('con.flight_id', $request->id)
                ->where('package.client_id', '<>', 0)
                ->whereNull('package.on_the_way_date')
                ->whereNull('package.deleted_by')
                ->whereNull('con.deleted_by')
                ->select('package.id', 'package.client_id', 'package.number', 'package.carrier_registration_number', 'client.name', 'client.surname', 'client.email', 'client.language', 'client.id as cl_id')
                ->orderBy('package.client_id')
                ->get();

            $packages_arr = array();
            $packagesIds = array();
            $statusesToCreate = array();
            if (count($packages) > 0) {

                $departure_id = $flight_id->location_id;
                $country = Location::where('id', $departure_id)->select('country_id')->first();
                if ($country) {
                    $country_id = $country->country_id;
                } else {
                    $country_id = 0;
                }

                // variables for email
                $email_to = '';
                $email_title = '';
                $email_subject = '';
                $email_bottom = '';
                $email_content = '';
                $email_list_inside = '';
                $list_insides = '';
                $client_id = '';

                $email = EmailListContent::where(['type' => 'on_the_way_list'])->first();
                $country_check = Countries::where('id', $country_id)->first();

                if (!$email) {
                    return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Email template not found!']);
                }



                $client_id_for_email = 0;
                foreach ($packages as $package) {
                    array_push($packages_arr, $package->id);
                    array_push($packagesIds, [
                        'regNumber' => $package->carrier_registration_number,
                        'trackNumber' => $package->number
                    ]);

                    if ($package->client_id != 0 && $package->client_id != null) {
                        if ($package->client_id != $client_id_for_email) {
                            // new client
                            if ($client_id_for_email != 0) {
                                $email_content = str_replace('{list_inside}', $list_insides, $email_content);

                                $job = (new CollectorInWarehouseJob($email_to, $email_title, $email_subject, $email_content, $email_bottom))
                                    ->delay(Carbon::now()->addSeconds(10));
                                dispatch($job);

                               $notf = (new NotificationJob($email_title, $email_subject, $email_content, $client_id))
                                   ->delay(Carbon::now()->addSeconds(10));
                                dispatch($notf);
                            }

                            $list_insides = '';

                            $language = $package->language;
                            $language = strtolower($language);

                            if ($country_check) {
                                $attribute_name = 'name_' . $language;
                                $country_name = $country_check->$attribute_name;
                            } else {
                                $country_name = '---';
                            }

                            $email_title = $email->{'title_' . $language};
                            $email_subject = $email->{'subject_' . $language};
                            $email_bottom = $email->{'content_bottom_' . $language};
                            $email_content = $email->{'content_' . $language};
                            $email_list_inside = $email->{'list_inside_' . $language};

                            $email_push_content = $email->{'push_content_' . $language};
                            $email_push_content = str_replace('{tracking_number}', $package->number, $email_push_content);

                            $list_inside = $email_list_inside;

                            $list_inside = str_replace('{tracking_number}', $package->number, $list_inside);

                            $list_insides .= $list_inside;

                            $email_to = $package->email;
                            $client_id = $package->client_id;
                            $client = $package->name . ' ' . $package->surname;
                            $email_content = str_replace('{name_surname}', $client, $email_content);
                            $email_content = str_replace('{country_name}', $country_name, $email_content);

                            $client_id_for_email = $package->client_id;
                        } else {
                            // same client
                            $list_inside = $email_list_inside;

                            $list_inside = str_replace('{tracking_number}', $package->number, $list_inside);

                            $list_insides .= $list_inside;
                        }
                    }


                }

                // send email
                if ($client_id_for_email != 0) {
                    $email_content = str_replace('{list_inside}', $list_insides, $email_content);

                    $job = (new CollectorInWarehouseJob($email_to, $email_title, $email_subject, $email_content, $email_bottom))
                        ->delay(Carbon::now()->addSeconds(10));
                    dispatch($job);

                    $notf = (new NotificationJob($email_title, $email_subject, $email_content, $client_id))
                        ->delay(Carbon::now()->addSeconds(10));
                    dispatch($notf);
                }


                $closed_user = Auth::id();
                $job = (new PackageStatusJob($packages, $closed_user))
                    ->delay(Carbon::now()->addSeconds(10));
                dispatch($job);

               /* $end = time();

                dd($start, $end, $end - $start);*/

            }

            Flight::where(['id' => $request->id])->whereNull('deleted_by')->update(['closed_by' => Auth::id(), 'closed_at' => $date]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'closed_at' => $date]);
        } catch (\Exception $e) {
            //dd($e);
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }

    public function closeNewVersionTwo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Flight not found!']);
        }
        try {
            set_time_limit(6000);

            $date = Carbon::now()->toDateTimeString();

            // $flight_id = Flight::whereNull('closed_by')->whereNull('closed_at')->where('id', $request->id)->select('id', 'location_id')->first();
            $flight_id = Flight::where('id', $request->id)->select('id', 'location_id')->first();

            if ($flight_id == null) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Flight already closed!']);
            }


            $packages = Package::leftJoin('container as con', 'package.container_id', '=', 'con.id')
                ->leftJoin('users as client', 'package.client_id', '=', 'client.id')
                ->where('con.flight_id', $request->id)
                ->where('package.client_id', '<>', 0)
                //->whereNull('package.on_the_way_date')
                ->whereNull('package.deleted_by')
                ->whereNull('con.deleted_by')
                ->select('package.id', 'package.client_id', 'package.number', 'package.carrier_registration_number', 'client.name', 'client.surname', 'client.email', 'client.language', 'client.id as cl_id')
                ->orderBy('package.client_id')
                ->get();

            if (count($packages) > 0) {
                $departure_id = $flight_id->location_id;
                $country = Location::where('id', $departure_id)->select('country_id')->first();
                if ($country) {
                    $country_id = $country->country_id;
                } else {
                    $country_id = 0;
                }

                $country_check = Countries::where('id', $country_id)->first();

                $email = EmailListContent::where(['type' => 'on_the_way_list'])->first();

                if (!$email) {
                    return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Email template not found!']);
                }

                $start = time();

                $groupedPackages = $packages->groupBy('cl_id');

                foreach ($groupedPackages as $clientId => $clientPackages) {

                    $email_to = $clientPackages->pluck('email')->first();

                    $packageIdes = $clientPackages->pluck('id')->toArray();
                    $client_id = $clientPackages->pluck('client_id')->first();
                    $language = $clientPackages->pluck('language')->first();
                    $packageNumbers = $clientPackages->pluck('number')->toArray();
                    $clientName = $clientPackages->pluck('name')->first();
                    $clientSurname = $clientPackages->pluck('surname')->first();

                    $client = $clientName . ' ' . $clientSurname;
                    $packageList = implode(', ', $packageNumbers);

                    $language = strtolower($language);

                    if ($country_check) {
                        $attribute_name = 'name_' . $language;
                        $country_name = $country_check->$attribute_name;
                    } else {
                        $country_name = '---';
                    }

                    $notification_title = $email->{'title_' . $language}; //from
                    $notification_subject = $email->{'subject_' . $language};
                    $notification_content = $email->{'push_content_' . $language};
                    $notification_content = str_replace('{name_surname}', $client, $notification_content);
                    $notification_content = str_replace('{country_name}', $country_name, $notification_content);

                    $notification_content = str_replace('{list_inside_content}', $packageList, $notification_content);

                    //$this->notification->sendNotification($notification_title, $notification_subject, $notification_content, $client_id);

                    $notf = (new NotificationJob($notification_title, $notification_subject, $notification_content, $client_id))
                        ->delay(Carbon::now()->addSeconds(10));
                    dispatch($notf);

                    $email_content = $email->{'content_' . $language};
                    $email_content = str_replace('{name_surname}', $client, $email_content);
                    $email_content = str_replace('{country_name}', $country_name, $email_content);
                    $email_content = str_replace('{list_inside}', $packageList, $email_content);

                    $email_bottom = $email->{'content_bottom_' . $language};

                    $job = (new CollectorInWarehouseJob($email_to, $notification_title, $notification_subject, $email_content, $email_bottom))
                        ->delay(Carbon::now()->addSeconds(40));
                    dispatch($job);


                }

                $closed_user = Auth::id();
                $job = (new PackageStatusJob($packages, $closed_user))
                    ->delay(Carbon::now()->addSeconds(10));
                dispatch($job);
                $end = time();
                dd($end - $start);

            }

            //Flight::where(['id' => $request->id])->whereNull('deleted_by')->update(['closed_by' => Auth::id(), 'closed_at' => $date]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'closed_at' => $date]);
        } catch (\Exception $e) {
            dd($e);
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }

}
