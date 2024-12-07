<?php

namespace App\Http\Controllers;

use App\Countries;
use App\EmailListContent;
use App\Flight;
use App\Item;
use App\Jobs\CollectorInWarehouseJob;
use App\Jobs\WarehouseChangeStatusJob;
use App\Location;
use App\Package;
use App\PackageStatus;
use App\Position;
use App\Scopes\DeletedScope;
use App\Status;
use App\TrackingLog;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DistributorController extends HomeController
{

    public function __construct(NotificationController $notification)
    {
        parent::__construct();
        $this->notification = $notification;
    }

    public function index()
    {
        try {
            $flights = Flight::whereNull('flight.deleted_by')
                ->whereNull('flight.status_in_baku_date')
                ->orderBy('flight.id', 'desc')
                ->take(100)
                ->select('flight.id', 'flight.name')
                ->get();

            // dd($flights->toJson());

            return view('backend.warehouse.distributor', compact('flights'));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function change_position(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'track' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:100'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $user_id = Auth::id();
            $user_location_id = Auth::user()->location();
            $branch = Auth::user()->branch();

            // dd($user_id);
            // dd(Auth::user()->branch());

            $position_no = $request->position;
            $package_number = $request->track;

            if (substr($package_number, 0, 8) == '42019801') {
                $package_number_search = substr($package_number, -22);
                $package = Package::whereRaw("(number = '" . $package_number_search . "' or internal_id = '" . $package_number_search . "')")
                    ->whereNull('deleted_by')
                    ->whereNull('delivered_by')
                    ->orderBy('id', 'desc')
                    ->select('id', 'number', 'in_baku', 'client_id', 'branch_id')
                    ->first();

                if (!$package) {
                    $package_number_search = substr($package_number, 10, strlen($package_number) - 1);
                    $package = Package::whereRaw("(number = '" . $package_number_search . "' or internal_id = '" . $package_number_search . "')")
                        ->whereNull('deleted_by')
                        ->whereNull('delivered_by')
                        ->orderBy('id', 'desc')
                        ->select('id', 'number', 'in_baku', 'client_id', 'branch_id')
                        ->first();
                }
            } else {
                $package_number_search = $package_number;
                $package = Package::whereRaw("(number = '" . $package_number_search . "' or internal_id = '" . $package_number_search . "')")
                    ->whereNull('deleted_by')
                    ->whereNull('delivered_by')
                    ->orderBy('id', 'desc')
                    ->select('id', 'number', 'in_baku', 'client_id', 'branch_id')
                    ->first();
            }

            if (!$package) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Package not found!']);
            }

            if ($branch !== $package->branch_id) {
                return response(['case' => 'branch', 'title' => 'Oops!', 'content' => 'This package does not belong to your office!']);
            }

            $package_id = $package->id;
            $in_baku = $package->in_baku;

            $position = Position::where('name', $position_no)->whereNull('deleted_by')
                ->where('location_id', $user_location_id)
                ->orderBy('id', 'desc')
                ->select('id')
                ->first();

            if (!$position) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Position is not found at your location!']);
            }

            $position_id = $position->id;

            $job = (new WarehouseChangeStatusJob($package_id, $user_id, $position_id, $in_baku));

            dispatch($job);

            return response(['case' => 'success', 'change' => true, 'content' => 'Position is changed! ' . $package->id, 'track' => $package->number]);
        } catch (\Exception $exception) {
            //dd($exception);
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Something went wrong!']);
        }
    }

    public function changePackageBranchView()
    {
        $branches = DB::table("filial")->where("is_active", 1)->get();
        return view("2025/warehouse_distributor_change_branch", compact("branches"));
    }

    public function changePackageBranch(Request $request){
        // dd($request->all());
        // return response()->json($request->all());   
        $validator = Validator::make($request->all(), [
            'branch_id' => ['required', 'integer'],
            'package' => ['required', 'string'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Not valid data']);
        }
        $package = Package::where('number', $request->package)
            ->orWhere("internal_id",$request->package)
            // ->where("deleted_by",null)
            ->first();

        if (!$package) {
            return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Package not found (Package paid or delivered or issued to courier)!']);
        }
        if ($package->branch_id == $request->branch_id) {
            return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'The old and new branches are the same!']);
        }
        if (in_array($package->last_status_id, [5, 14, 38, 39, 40])) {
            return response([
                'case' => 'error',
                'title' => 'Oops!',
                'content' => 'Can not change branch in status ' . $package->status_en
            ]);
        }

        $package->update([
                'branch_id' => $request->branch_id
            ]);
        DB::insert('insert into change_package_branch_log (package_id, old_branch_id, new_branch_id, user_id) values (?, ?, ?, ?)', [
            $request->package,
            $package->branch_id,
            $request->branch_id,
            Auth::user()->id,
        ]);
        return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Branch is changed!']);
    }


    public function changePackageInBakuView()
    {
        $branches = DB::table("filial")->where("is_active", 1)->get();
        return view("2025/warehouse_distributor_change_in_baku", compact("branches"));
    }

    public function changePackageInBaku(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'branch_id' => ['required', 'integer'],
            'package' => ['required',]
        ]);

        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Package Or Branch not found']);
        }
        try {
            $package = Package::withoutGlobalScope(DeletedScope::class)->where('package.number', $request->package)
                ->orWhere('package.internal_id', $request->package)
                ->leftJoin('lb_status as status', 'package.last_status_id', '=', 'status.id')
                // ->where('package.paid_status', 0)
                // ->whereNull('package.delivered_by')
                // ->whereNull('package.issued_to_courier_date')
                ->whereNull('package.deleted_by')
                ->select(['package.number', 'package.branch_id', 'package.last_status_id', 'status.status_en', 'package.carrier_status_id'])
                ->first();
                
                if (!$package) {
                    return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Package not found (Package paid or delivered or issued to courier)!']);
                }
            $default_in_baku_status_id = 15;

            $package = Package::withoutGlobalScope(DeletedScope::class)
                ->where('package.number', $request->package)
                ->orWhere('package.internal_id', $request->package)
                // ->leftJoin('lb_status as status', 'package.last_status_id', '=', 'status.id')
                ->where('package.paid_status', 0)
                ->whereNull('package.delivered_by')
                ->whereNull('package.issued_to_courier_date')
                ->whereNull('package.deleted_by')->update([
                    'last_status_id' => $default_in_baku_status_id,
                    // 'in_baku' => 1,
                    // 'hash' => "yoyoyo"
                ]);

         
            // $package->
            // $package->last_status_id = $default_in_baku_status_id;
            // $package->in_baku = -1;
            // $package->hash ="ASdfdsgdsgs";
            // $package->save();
            // $package->
           
            // if (in_array($package->last_status_id, [5, 14, 38, 39, 40])) {
            //     return response([
            //         'case' => 'error',
            //         'title' => 'Oops!',
            //         'content' => 'Can not change branch in status ' . $package->status_en
            //     ]);
            // }

            // Package::where('number', $request->package)
            //     ->orWhere('package.internal_id', $request->package)
            //     ->update([
            //         'branch_id' => $request->branch_id
            //     ]);

            // DB::insert('insert into change_package_branch_log (package_id, old_branch_id, new_branch_id, user_id) values (?, ?, ?, ?)', [
            //     $request->package,
            //     $package->branch_id,
            //     $request->branch_id,
            //     Auth::user()->id,
            // ]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Changed to In Baku!']);
        } catch (\Exception $exception) {
            dd($exception);
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    // reports
    public function get_report_page()
    {
        try {
            $flights = Flight::whereNull('deleted_by')
                ->whereDate('flight_number', '>', Carbon::now()->subDays(30))
                ->orderBy('id', 'desc')
                ->select('id', 'name')
                ->get();
            //dd($flights);
            return view('backend.warehouse.report', compact(
                'flights'
            ));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    //    public function get_report(Request $request)
//    {
//        $validator = Validator::make($request->all(), [
//            'flight' => ['required', 'integer'],
//            'status' => ['required', 'integer'],
//        ]);
//        if ($validator->fails()) {
//            return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
//        }
//        try {
//            $query = Package::leftJoin('container as con', 'package.last_container_id', '=', 'con.id')
//                ->leftJoin('users as client', 'package.client_id', '=', 'client.id')
//                ->where('con.flight_id', $request->flight)
//                ->whereNull('package.deleted_by');
//
//            $status = $request->status;
//            switch ($status) {
//                case 2: $query->where('package.is_warehouse', 3); break; // In Baku
//                case 3: $query->where('package.is_warehouse', '<>', 3); break; // Not In Baku
//            }
//
//            $packages = $query
//                ->select('package.number as track', 'package.internal_id', 'client.suite as client_suite',  'client.id as client_id', 'client.name as client_name', 'client.surname as client_surname', 'package.gross_weight')
//                ->get();
//
//            return response(['case' => 'success', 'title' => 'Success!', 'packages' => $packages]);
//        } catch (\Exception $exception) {
//            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
//        }
//    }

    // detained at customs
    public function detained_at_customs_page()
    {
        try {
            $flights = Flight::whereNull('flight.deleted_by')
                //->whereNull('flight.status_in_baku_date')
                ->orderBy('flight.id', 'desc')
                ->take(100)
                ->select('flight.id', 'flight.name')
                ->get();

            return view('backend.warehouse.customs', compact('flights'));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function detained_at_customs(Request $request)
    {
        try {
            $query = Item::leftJoin('package', 'item.package_id', '=', 'package.id')
                ->leftJoin('users as client', 'package.client_id', '=', 'client.id')
                ->leftJoin('container', 'package.last_container_id', '=', 'container.id')
                ->leftJoin('flight', 'container.flight_id', '=', 'flight.id')
                ->leftJoin('seller', 'package.seller_id', '=', 'seller.id')
                ->leftJoin('category', 'item.category_id', '=', 'category.id')
                ->leftJoin('lb_status as status', 'package.last_status_id', '=', 'status.id')
                //->where('package.position_id', 1)
                ->whereNotNull('package.customs_date')
                ->whereNull('item.deleted_by')
                ->whereNull('package.delivered_by')
                ->whereNull('package.deleted_by');

            $flight_id = $request->input("flight");
            if (isset($flight_id) && !empty($flight_id) && $flight_id != null && $flight_id != "null" && $flight_id != "undefined") {
                $query->where('container.flight_id', $flight_id);
            }

            $suite = $request->input("client");
            //$suite = 142712;
            if (isset($suite) && !empty($suite) && $suite != null && $suite != "null" && $suite != "undefined") {
                $query->where('package.client_id', $suite);
            }

            $track = $request->input("track");
            if (isset($track) && !empty($track) && $track != null && $track != "null" && $track != "undefined") {
                $query->whereRaw("(package.number like '%" . $track . "%' or package.internal_id like '%" . $track . "%')");
            }

            $packages = $query
                ->select(
                    'flight.name as flight',
                    'package.number as track',
                    'package.internal_id',
                    'package.client_id as suite',
                    'client.name as client_name',
                    'client.surname as client_surname',
                    'package.gross_weight',
                    'status.status_en as status',
                    'seller.name as seller',
                    'category.name_en as category',
                    'package.customs_date as date'
                )
                ->paginate(50);

            return response(['case' => 'success', 'title' => 'Success!', 'packages' => $packages]);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function detained_at_customs_notification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'flight' => 'nullable|integer',
            'client' => 'nullable|integer',
            'track' => 'nullable|string|max:255',
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Validation error!']);
        }
        try {
            $query = Item::leftJoin('package', 'item.package_id', '=', 'package.id')
                ->leftJoin('users as client', 'package.client_id', '=', 'client.id')
                ->leftJoin('container', 'package.last_container_id', '=', 'container.id')
                ->whereNotNull('package.customs_date')
                ->where('package.customs_notification', 0)
                ->where('package.client_id', '<>', 0)
                ->whereNull('item.deleted_by')
                ->whereNull('package.delivered_by')
                ->whereNull('package.deleted_by');

            $flight_id = $request->flight;
            if (isset($flight_id) && !empty($flight_id) && $flight_id != null && $flight_id != "null" && $flight_id != "undefined") {
                $query->where('container.flight_id', $flight_id);
            }

            $suite = $request->client;
            if (isset($suite) && !empty($suite) && $suite != null && $suite != "null" && $suite != "undefined") {
                $query->where('package.client_id', $suite);
            }

            $track = $request->track;
            if (isset($track) && !empty($track) && $track != null && $track != "null" && $track != "undefined") {
                $query->whereRaw("(package.number like '%" . $track . "%' or package.internal_id like '%" . $track . "%')");
            }

            $packages = $query
                ->select(
                    'package.id',
                    'package.number',
                    'package.gross_weight as weight',
                    'package.client_id',
                    'client.name as name',
                    'client.surname as surname',
                    'client.email',
                    'client.language'
                )
                ->get();

            if (count($packages) == 0) {
                return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Packages not found or already changed status!']);
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

            $packages_arr = array();

            $email = EmailListContent::where(['type' => 'detained_at_customs'])->first();

            if (!$email) {
                return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Email template not found!']);
            }

            $client_id = 0;
            $no = 0;
            foreach ($packages as $package) {
                PackageStatus::create(['package_id' => $package->id, 'status_id' => 29, 'created_by' => Auth::id()]); // custom status

                //send email
                if ($package->client_id != 0 && $package->client_id != null && $package->email != null) {
                    array_push($packages_arr, $package->id);

                    if ($package->client_id != $client_id) {
                        // new client
                        if ($client_id != 0) {
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
                        $list_inside = str_replace('{weight}', $package->weight . ' kg', $list_inside);
                        $list_inside = str_replace('{no}', $no, $list_inside);

                        $list_insides .= $list_inside;

                        $email_to = $package->email;
                        $client = $package->name . ' ' . $package->surname;
                        $email_content = str_replace('{name_surname}', $client, $email_content);
                        $email_push_content = str_replace('{list_inside}', $list_insides, $email_push_content);

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

                    // $content_detained_customs = empty($email_push_content) ? $email_content : $email_push_content;
                    // $this->notification->sendNotification($email_title, $email_subject, $content_detained_customs, $package->client_id);
                }
            }

            // send email
            if ($client_id != 0) {
                $email_content = str_replace('{list_inside}', $list_insides, $email_content);

                $job = (new CollectorInWarehouseJob($email_to, $email_title, $email_subject, $email_content, $email_bottom, $email_button))
                    ->delay(Carbon::now()->addSeconds(10));
                dispatch($job);
            }
            $groupedPackages = $packages->groupBy('client_id');
            foreach ($groupedPackages as $clientId => $clientPackages) {
                $client_id = $clientPackages->pluck('client_id')->first();
                $language = $clientPackages->pluck('language')->first();
                $packageNumbers = $clientPackages->pluck('number')->toArray();
                $packageList = implode(', ', $packageNumbers);

                $language = strtolower($language);
                $notification_title = $email->{'title_' . $language}; //from
                $notification_subject = $email->{'subject_' . $language};
                $notification_content = $email->{'push_content_' . $language};
                $notification_content = str_replace('{name_surname}', $client, $notification_content);

                $notification_content = str_replace('{list_inside}', $packageList, $notification_content);

                $this->notification->sendNotification($notification_title, $notification_subject, $notification_content, $client_id);

            }

            // change status
            if (count($packages_arr) > 0) {
                Package::whereIn('id', $packages_arr)->update(['customs_notification' => 1]);
            }

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Sent notifications for ' . count($packages_arr) . ' package(s)!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry something went wrong!']);
        }
    }

    public function get_partner_package()
    {
        try {
            /*[3, 30, 42, 43, 44, 45, 46]*/
            $statuses = Status::where('for_partner', 1)
                ->select('id', 'status_en')
                ->orderBy('id', 'ASC')
                ->get();

            return view('backend.warehouse.partnerPackageStatus', compact('statuses'));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }
    public function partner_change_position(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'track' => ['required', 'string', 'max:255'],
            'status' => ['required', 'int'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $user_id = Auth::id();

            $status = $request->status;
            $track = $request->track;

            $package = Package::whereRaw("(number = '" . $track . "' or internal_id = '" . $track . "')")
                ->whereNotNull('partner_id')
                ->first();


            if ($package) {
                if ($status == Status::delivered || $status == Status::delivered_by_azeripost) {
                    $package->update([
                        'last_status_id' => $status,
                        'last_status_date' => Carbon::now(),
                        'delivered_by' => Auth::id(),
                        'delivered_at' => Carbon::now()
                    ]);
                } else {
                    $package->update([
                        'last_status_id' => $status,
                        'last_status_date' => Carbon::now()
                    ]);
                }


                PackageStatus::create([
                    'package_id' => $package->id,
                    'status_id' => $status,
                    'created_by' => Auth::id()
                ]);

                return response(['case' => 'success', 'change' => true, 'content' => 'Status is changed! ', 'track' => $package->number]);
            } else {
                return response(['case' => 'error', 'change' => false, 'content' => 'Package not found ']);
            }



        } catch (\Exception $exception) {
            //dd($exception);
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Something went wrong!']);
        }
    }
}
