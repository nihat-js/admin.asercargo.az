<?php

namespace App\Http\Controllers;

use App\AWB;
use App\Container;
use App\Flight;
use App\Item;
use App\Location;
use App\Package;
use App\TrackingLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class ContainerController extends HomeController
{
    public function show() {
        try {
            $query = Container::leftJoin('flight as flt', 'container.flight_id', '=', 'flt.id')
                ->leftJoin('locations as dep', 'container.departure_id', '=', 'dep.id')
                ->leftJoin('locations as des', 'container.destination_id', '=', 'des.id')
                ->whereNull('container.deleted_by');

            if (Auth::user()->role() == 3 || Auth::user()->role() == 11) {
                //collector
                $query->whereRaw('(container.public = 1 or container.created_by = ?)', Auth::id());

                if(Auth::user()->id == 137297){
                    $query->whereRaw('(container.public = 1 or container.created_by = ?)', Auth::id());
                    $query->whereDate('container.created_at', '>', date('2021-08-20'));
                }
            }

            $search_arr = array(
                'flight' => '',
                'status' => '',
                'created' => '',
            );

            if (!empty(Input::get('flight')) && Input::get('flight') != '' && Input::get('flight') != null) {
                $where_flight = Input::get('flight');
                $query->where('container.flight_id', $where_flight);
                $search_arr['flight'] = $where_flight;
            }

            if (Auth::user()->role() == 3 || Auth::user()->role() == 11) {
                if (!empty(Input::get('created')) && Input::get('created') != '' && Input::get('created') != null) {
                    $where_created = Input::get('created');
                    $search_arr['created'] = $where_created;
                    if ($where_created == 'byme') {
                        $query->where('container.created_by', Auth::id());
                    }
                }
                if(Auth::user()->id == 137297){
                    $query->whereRaw('(container.public = 1 or container.created_by = ?)', Auth::id());
                    $query->whereDate('container.created_at', '>', date('2021-08-20'));
                }
            }

            //short by start
            $short_by = 'container.id';
            $shortType = 'DESC';
            if (!empty(Input::get('shortBy')) && Input::get('shortBy') != ''  && Input::get('shortBy') != null) {
                $short_by = Input::get('shortBy');
            }
            if (!empty(Input::get('shortType')) && Input::get('shortType') != ''  && Input::get('shortType') != null) {
                $short_type = Input::get('shortType');
                if ($short_type == 2) {
                    $shortType = 'DESC';
                } else {
                    $shortType = 'ASC';
                }
            }
            //short by finish

            $containers = $query
                ->orderBy($short_by, $shortType)
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
                )->paginate(20);

            // $before = microtime(true);
            foreach ($containers as $container) {
                $container_id = $container->id;
                // $packages = Item::leftJoin('package', 'item.package_id', '=', 'package.id')
                //     ->where('package.container_id', $container_id)
                //     ->whereNull('package.deleted_by')
                //     ->select('package.gross_weight')
                //     ->get();

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

            // $after = microtime(true);
            // dd($after-$before);

            if(Auth::user()->id == 137297){
                $flights = Flight::whereNull('deleted_by')
                ->whereDate('created_at', '>', date('2021-08-20'))
                ->whereRaw('(public = 1 or location_id = ?)', Auth::user()->location())
                ->select('id', 'carrier', 'flight_number')
                ->orderBy('carrier')
                ->orderBy('flight_number')
                ->get();
			}else{
                $flights = Flight::whereNull('deleted_by')->select('id', 'carrier', 'flight_number')->orderBy('carrier')->orderBy('flight_number')->get();
            }

            $locations = Location::whereNull('deleted_by')->select('id', 'name')->orderBy('name')->get();

            if (Auth::user()->role() == 3 || Auth::user()->role() == 11) {
                
                return view("backend.containers_for_collector", compact(
                    'containers',
                    'flights',
                    'locations',
                    'search_arr'
                ));
            }

            return view("backend.containers", compact(
                'containers',
                'flights',
                'locations',
                'search_arr'
            ));
        } catch (\Exception $exception) {
            //dd($exception);
            return view('backend.error');
        }
    }

    public function add(Request $request) {
        $validator = Validator::make($request->all(), [
            'flight_id' => ['required', 'integer'],
//            'awb_id' => ['required', 'integer'],
            'departure_id' => ['required', 'integer'],
            'destination_id' => ['required', 'integer'],
            'count' => ['required', 'integer', 'min:1'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            unset($request['id']);
            if (Auth::user()->role() == 3 || Auth::user()->role() == 11) {
                //collector
                $public = 0;
            } else {
                $public = 1;
            }
            $request->merge(['created_by'=>Auth::id(), 'public'=>$public]);

            $count = $request->count;
            unset($request['count']);

            for ($i = 0; $i < $count; $i++) {
                Container::create($request->all());
            }

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function delete(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Id not found!']);
        }
        try {
            if (Auth::user()->role() == 3 || Auth::user()->role() == 11) {
                if (Container::where(['id'=>$request->id, 'created_by'=>Auth::id()])->count() == 0) {
                    return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Access denied!']);
                }
            }

            if (TrackingLog::where('container_id', $request->id)->whereNull('deleted_by')->count() > 0) {
                return response(['case' => 'warning', 'title' => 'Stop!', 'content' => 'The container cannot be deleted. Because this container is used.']);
            }

            Container::where(['id'=>$request->id])->whereNull('deleted_by')->update(['deleted_by'=>Auth::id(), 'deleted_at'=>Carbon::now()]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'id'=>$request->id]);
        } catch (\Exception $e) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }

    public function create_single_container(Request $request) {
        $validator = Validator::make($request->all(), [
            'flight' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $flight_id = $request->flight;

            $add = Container::create([
                'flight_id' => $flight_id,
                'departure_id' => Auth::user()->location(),
                'destination_id' => 1, //Baku
                'created_by' => Auth::id()
            ]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'container' => $add->id]);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }
}
