<?php

namespace App\Services;

use App\Container;
use App\Countries;
use App\EmailListContent;
use App\Flight;
use App\Http\Controllers\NotificationController;
use App\Jobs\CollectorInWarehouseJob;
use App\Location;
use App\Package;
use App\PackageStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class AserFlight
{

    public function show($request)
    {
        try {
            $query = Flight::whereNull('deleted_by')
                ->when($request->get('name'), fn ($query) => $query->where('flight.name', $request->get('name')))
            ;


            if ($request->collector->role() == 3) {
                //collector
                $query->whereRaw('(public = 1 or location_id = ?)', $request->collector->location());
            }

            $flights = $query
                ->orderBy('id', 'DESC')
                ->select(
                    'id',
                    'name',
                    'carrier',
                    'flight_number',
                    'awb',
                    'departure',
                    'destination',
                    'closed_at'
                )->paginate(50);


            foreach ($flights as $flight) {
                $flight_id = $flight->id;

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


            return $flights;
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function addFlight($request){
        try {
            unset($request['id']);

            $public = 0;
            //dd();
            $date = strtotime($request->flight_number);
            $day = date('d', $date);
            $month = date('m', $date);
            $year = date('Y', $date);

            $name = $request->carrier . $day . $month . $year;

            $request->merge(['created_by' => $request->collector->id, 'name' => $name, 'location_id'=>$request->collector->location(), 'public'=>$public]);

            $flight = Flight::create($request->all());


            unset($request['carrier']);
            unset($request['flight_number']);
            unset($request['awb']);
            unset($request['departure']);
            unset($request['destination']);
            unset($request['name']);


            $request->merge([
                'departure_id'=>$request->collector->location(),
                'flight_id' => $flight->id,
                'count' => $request->count
            ]);

            $container = $this->create_container($request);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'flight_id' => $flight->id, 'container' => $container]);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function update(Request $request)
    {
        try {
            $id = $request->id;
            unset($request['id'], $request['_token']);


            $date = strtotime($request->flight_number);
            $day = date('d', $date);
            $month = date('m', $date);
            $year = date('Y', $date);

            $name = $request->carrier . $day . $month . $year;

            $request->merge(['name' => $name]);

            Flight::where(['id' => $id])->whereNull('closed_at')->update($request->all());


            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function close($request)
    {
        try {
            $date = Carbon::now()->toDateTimeString();

            $flight = Flight::where('id', $request->id)->select('id', 'closed_at', 'name')->first();


            if (!$flight) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Flight not found!'], Response::HTTP_NOT_FOUND);
            }


            if($flight->closed_at != null){
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Flight already closed!'], Response::HTTP_BAD_REQUEST);
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
            if (count($packages) > 0) {
                $departure_id = $request->collector->location();
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

                $notification = new NotificationController();
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

                            $country_check = Countries::where('id', $country_id)->select('countries.name_' . $language . ' as name')->first();
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


                foreach ($groupedPackages as $clientId => $clientPackages) {
                    $client_id = $clientPackages->pluck('client_id')->first();
                    $packageNumbers = $clientPackages->pluck('number')->toArray();
                    $packageList = implode(', ', $packageNumbers);

                    $notification_title = $email->{'title_' . $language}; //from
                    $notification_subject = $email->{'subject_' . $language};
                    $notification_content = $email->{'push_content_' . $language};
                    $notification_content = str_replace('{name_surname}', $client, $notification_content);
                    $notification_content = str_replace('{country_name}', $country_name, $notification_content);

                    $notification_content = str_replace('{list_inside_content}', $packageList, $notification_content);

                    $notification->sendNotification($notification_title, $notification_subject, $notification_content, $client_id);

                }

                Package::whereIn('id', $packages_arr)->update(['is_warehouse'=>2, 'on_the_way_date' => Carbon::now()]);
            }

            Flight::where(['id' => $request->id])->whereNull('deleted_by')->update(['closed_by' => $request->collector->id, 'closed_at' => $date]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'closed_at' => $date]);
        } catch (\Exception $e) {
            //dd($e);
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function flights($request)
    {
        return Flight::whereNull('deleted_at')
            ->whereNull('closed_at')
            ->where('location_id', $request->collector->location())
            ->select('id', 'name')
            ->orderBy('id', 'Desc')
            ->get();
    }

    public function showContainer($request) {
        try {
            $query = Container::leftJoin('flight as flt', 'container.flight_id', '=', 'flt.id')
                ->leftJoin('locations as dep', 'container.departure_id', '=', 'dep.id')
                ->leftJoin('locations as des', 'container.destination_id', '=', 'des.id')
                ->where('container.departure_id', $request->collector->location())
                ->whereNull('container.deleted_by');

            if ($request->collector->role() == 3) {
                //collector
                $query->whereRaw('(container.public = 1 or container.created_by = ?)', $request->collector->id);
                $query->when($request->get('flight_id'), fn ($query) => $query->where('container.flight_id', $request->get('flight_id')));
            }

            $containers = $query
                ->orderBy('.container.id', 'Desc')
                ->select(
                    'container.id',
                    'flt.carrier as airline',
                    'flt.flight_number',
                    'flt.awb',
                    'dep.name as departure',
                    'des.name as destination',
                    'container.created_at',
                    'flt.departure as dep',
                    'flt.destination as des'
                )->paginate(50);


            foreach ($containers as $container) {
                $container_id = $container->id;

                $packages = Package::where('package.container_id', $container_id)
                    ->whereNull('package.deleted_by')
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

                $container->packages_count = $packages_count;
                $container->total_weight = $total_weight;
            }



            return \response(['case' => 'success', 'containers' => $containers]);
        } catch (\Exception $exception) {
            dd($exception);
            return \response(['case' => 'error', 'message' => 'Error found'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create_container($request)
    {
        try {

            $request->merge([
                'public'=>$request->public,
                'created_by' => $request->collector->id,
                'departure_id' => $request->collector->location(),
                'destination_id' => 1
            ]);

            $count = $request->count;
            unset($request['count']);

            $arr = [];
            for ($i = 0; $i < $count; $i++) {
                $container = Container::create($request->all());
                array_push($arr, $container->id);
            }

            return  $arr;
        } catch (\Exception $exception) {
            //dd($exception);
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong in container!']);
        }
    }

}