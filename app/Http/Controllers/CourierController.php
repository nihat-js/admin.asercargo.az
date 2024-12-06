<?php

namespace App\Http\Controllers;

use App\CourierAreas;
use App\CourierDailyLimits;
use App\CourierMetroStations;
use App\CourierSettings;
use App\CourierSettingsLog;
use App\CourierZonePaymentTypes;
use App\CourierZones;
use App\CourierPaymentTypes;
use App\CourierRegion;
use App\CourierRegionTariff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class CourierController extends HomeController
{
    // settings
    public function show_settings() {
        try {
            $settings = CourierSettings::first();

            return view('backend.courier.settings', compact('settings'));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function update_settings(Request $request) {
        $validator = Validator::make($request->all(), [
            'daily_limit' => ['required', 'integer'],
            'closing_time' => ['required'],
            'amount_for_urgent' => ['required'], // 18,2
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $id = 1;
            unset($request['_token']);

            CourierSettings::where(['id'=>$id])->update($request->all());

            $request->merge(['created_by' => Auth::id()]);

            CourierSettingsLog::create($request->all());

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function show_settings_log() {
        try {
            $logs = CourierSettingsLog::leftJoin('users', 'courier_settings_log.created_by', '=', 'users.id')
                ->select(
                    'courier_settings_log.id',
                    'courier_settings_log.created_at as date',
                    'courier_settings_log.daily_limit',
                    'courier_settings_log.closing_time',
                    'courier_settings_log.amount_for_urgent',
                    'users.username as user'
                )
                ->orderBy('id', 'desc')
                ->paginate(50);

            return view('backend.courier.settings_log', compact('logs'));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    // daily limits
    public function show_daily_limits() {
        try {
            $query = CourierDailyLimits::whereNull('deleted_by');

            $search_arr = array(
                'date' => '',
            );

            if (!empty(Input::get('date')) && Input::get('date') != '' && Input::get('date') != null) {
                $where_date = Input::get('date');
                $query->whereDate('date', $where_date);
                $search_arr['date'] = $where_date;
            }

            //short by start
            $short_by = 'id';
            $shortType = 'desc';
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

            $limits = $query
                ->orderBy($short_by, $shortType)
                ->select('id', 'date', 'count', 'used', 'created_at')
                ->paginate(50);

            return view('backend.courier.daily_limits', compact('limits', 'search_arr'));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function add_daily_limit(Request $request) {
        $validator = Validator::make($request->all(), [
            'date' => ['required', 'date'],
            'count' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            unset($request['id']);
            $request->merge(['created_by'=>Auth::id()]);

            CourierDailyLimits::create($request->all());

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function update_daily_limit(Request $request) {
        $validator = Validator::make($request->all(), [
            'date' => ['required', 'date'],
            'count' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $id = $request->id;
            unset($request['id'], $request['_token']);

            CourierDailyLimits::where(['id'=>$id])->update($request->all());

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function delete_daily_limit(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Id not found!']);
        }
        try {
            CourierDailyLimits::where(['id'=>$request->id])->whereNull('deleted_by')->update(['deleted_by'=>Auth::id(), 'deleted_at'=>Carbon::now()]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'id'=>$request->id]);
        } catch (\Exception $e) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }

    // payment types
    public function show_payment_types() {
        try {
            $query = CourierPaymentTypes::whereNull('deleted_by');

            $search_arr = array(
                'name' => '',
            );

            if (!empty(Input::get('name')) && Input::get('name') != '' && Input::get('name') != null) {
                $where_name = Input::get('name');
                $query->where('name_en', 'LIKE', '%' . $where_name . '%');
                $search_arr['name'] = $where_name;
            }

            //short by start
            $short_by = 'id';
            $shortType = 'ASC';
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

            $payment_types = $query
                ->orderBy($short_by, $shortType)
                ->select('id', 'name_en', 'name_az', 'name_ru', 'created_at')
                ->paginate(50);

            return view('backend.courier.payment_types', compact('payment_types', 'search_arr'));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function add_payment_type(Request $request) {
        if (Auth::id() != 1) {
            return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Access denied!']);
        }

        $validator = Validator::make($request->all(), [
            'name_en' => ['required', 'string', 'max:255'],
            'name_az' => ['required', 'string', 'max:255'],
            'name_ru' => ['required', 'string', 'max:255'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            unset($request['id']);
            $request->merge(['created_by'=>Auth::id()]);

            CourierPaymentTypes::create($request->all());

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function update_payment_type(Request $request) {
        $validator = Validator::make($request->all(), [
            'name_en' => ['required', 'string', 'max:255'],
            'name_az' => ['required', 'string', 'max:255'],
            'name_ru' => ['required', 'string', 'max:255'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $id = $request->id;
            unset($request['id'], $request['_token']);

            CourierPaymentTypes::where(['id'=>$id])->update($request->all());

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function delete_payment_type(Request $request) {
        if (Auth::id() != 1) {
            return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Access denied!']);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Id not found!']);
        }
        try {
            CourierPaymentTypes::where(['id'=>$request->id])->whereNull('deleted_by')->update(['deleted_by'=>Auth::id(), 'deleted_at'=>Carbon::now()]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'id'=>$request->id]);
        } catch (\Exception $e) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }

    // zones
    public function show_zones() {
        try {
            $query = CourierZones::whereNull('deleted_by');

            $search_arr = array(
                'name' => '',
            );

            if (!empty(Input::get('name')) && Input::get('name') != '' && Input::get('name') != null) {
                $where_name = Input::get('name');
                $query->where('name_en', 'LIKE', '%' . $where_name . '%');
                $search_arr['name'] = $where_name;
            }

            //short by start
            $short_by = 'id';
            $shortType = 'ASC';
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

            $zones = $query
                ->orderBy($short_by, $shortType)
                ->select('id', 'name_en', 'name_az', 'name_ru', 'default_tariff', 'created_at')
                ->paginate(50);

            $payment_types = CourierPaymentTypes::select('id', 'name_en as name')->get();

            return view('backend.courier.zones', compact('zones', 'payment_types', 'search_arr'));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function add_zone(Request $request) {
        $validator = Validator::make($request->all(), [
            'name_en' => ['required', 'string', 'max:255'],
            'name_az' => ['required', 'string', 'max:255'],
            'name_ru' => ['required', 'string', 'max:255'],
            'default_tariff' => ['required'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            unset($request['id']);
            $request->merge(['created_by'=>Auth::id()]);

            CourierZones::create($request->all());

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function update_zone(Request $request) {
        $validator = Validator::make($request->all(), [
            'name_en' => ['required', 'string', 'max:255'],
            'name_az' => ['required', 'string', 'max:255'],
            'name_ru' => ['required', 'string', 'max:255'],
            'default_tariff' => ['required'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $id = $request->id;
            unset($request['id'], $request['_token']);

            CourierZones::where(['id'=>$id])->update($request->all());

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function delete_zone(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Id not found!']);
        }
        try {
            CourierZones::where(['id'=>$request->id])->whereNull('deleted_by')->update(['deleted_by'=>Auth::id(), 'deleted_at'=>Carbon::now()]);

            CourierAreas::where(['zone_id'=>$request->id])->whereNull('deleted_by')->update(['deleted_by'=>Auth::id(), 'deleted_at'=>Carbon::now()]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'id'=>$request->id]);
        } catch (\Exception $e) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }

    public function get_payment_types_for_zones(Request $request) {
        $validator = Validator::make($request->all(), [
            'zone_id' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $zone_id = $request->zone_id;

            $payment_types = CourierZonePaymentTypes::leftJoin('courier_payment_types as delivery', 'courier_zone_payment_type.delivery_payment_type_id', '=', 'delivery.id')
                ->leftJoin('courier_payment_types as courier', 'courier_zone_payment_type.courier_payment_type_id', '=', 'courier.id')
                ->where('courier_zone_payment_type.zone_id', $zone_id)
                ->select('courier_zone_payment_type.id', 'delivery.name_en as delivery', 'courier.name_en as courier')
                ->get();

            return response(['case' => 'success', 'payment_types' => $payment_types]);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function add_payment_type_for_zones(Request $request) {
        $validator = Validator::make($request->all(), [
            'zone_id' => ['required', 'integer'],
            'delivery_payment_type_id' => ['required', 'integer'],
            'courier_payment_type_id' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $request->merge(['created_by'=>Auth::id()]);

            CourierZonePaymentTypes::create($request->all());

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function delete_payment_type_for_zones(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Id not found!']);
        }
        try {
            CourierZonePaymentTypes::where(['id'=>$request->id])->whereNull('deleted_by')->update(['deleted_by'=>Auth::id(), 'deleted_at'=>Carbon::now()]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'id'=>$request->id]);
        } catch (\Exception $e) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }

    // areas
    public function show_areas() {
        try {
            $query = CourierAreas::leftJoin('courier_zones as zone', 'courier_areas.zone_id', '=', 'zone.id');

            $search_arr = array(
                'zone' => '',
                'name' => '',
            );

            if (!empty(Input::get('zone')) && Input::get('zone') != '' && Input::get('zone') != null) {
                $where_zone = Input::get('zone');
                $query->where('courier_areas.zone_id', $where_zone);
                $search_arr['zone'] = $where_zone;
            }

            if (!empty(Input::get('name')) && Input::get('name') != '' && Input::get('name') != null) {
                $where_name = Input::get('name');
                $query->where('courier_areas.name_en', 'LIKE', '%' . $where_name . '%');
                $search_arr['name'] = $where_name;
            }

            //short by start
            $short_by = 'courier_areas.id';
            $shortType = 'ASC';
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

            $areas = $query
                ->orderBy($short_by, $shortType)
                ->select('courier_areas.id', 'courier_areas.name_en', 'courier_areas.name_az', 'courier_areas.name_ru', 'courier_areas.tariff', 'courier_areas.created_at', 'courier_areas.zone_id', 'zone.name_en as zone', 'courier_areas.active')
                ->paginate(50);

            $zones = CourierZones::select('id', 'name_en as name', 'default_tariff')->get();

            return view('backend.courier.areas', compact('zones', 'areas', 'search_arr'));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function add_area(Request $request) {
        $validator = Validator::make($request->all(), [
            'name_en' => ['required', 'string', 'max:255'],
            'name_az' => ['required', 'string', 'max:255'],
            'name_ru' => ['required', 'string', 'max:255'],
            'zone_id' => ['required', 'integer'],
            'tariff' => ['required'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            unset($request['id']);
            $request->merge(['created_by'=>Auth::id()]);

            CourierAreas::create($request->all());

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function update_area(Request $request) {
        $validator = Validator::make($request->all(), [
            'name_en' => ['required', 'string', 'max:255'],
            'name_az' => ['required', 'string', 'max:255'],
            'name_ru' => ['required', 'string', 'max:255'],
            'zone_id' => ['required', 'integer'],
            'tariff' => ['required'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $id = $request->id;
            unset($request['id'], $request['_token']);

            CourierAreas::where(['id'=>$id])->update($request->all());

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function delete_area(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Id not found!']);
        }
        try {
            CourierAreas::where(['id'=>$request->id])->whereNull('deleted_by')->update(['deleted_by'=>Auth::id(), 'deleted_at'=>Carbon::now()]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'id'=>$request->id]);
        } catch (\Exception $e) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }

    public function active_area(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'switch' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Id not found!']);
        }
        try {
            CourierAreas::where(['id'=>$request->id])->whereNull('deleted_by')->update(['active'=>$request->switch]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'id'=>$request->id]);
        } catch (\Exception $e) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }

    // metro stations
    public function show_metro_stations() {
        try {
            $query = CourierMetroStations::whereNull('deleted_by');

            $search_arr = array(
                'name' => '',
            );

            if (!empty(Input::get('name')) && Input::get('name') != '' && Input::get('name') != null) {
                $where_name = Input::get('name');
                $query->where('name_en', 'LIKE', '%' . $where_name . '%');
                $search_arr['name'] = $where_name;
            }

            //short by start
            $short_by = 'id';
            $shortType = 'ASC';
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

            $metro_stations = $query
                ->orderBy($short_by, $shortType)
                ->select('id', 'name_en', 'name_az', 'name_ru', 'created_at')
                ->paginate(50);

            return view('backend.courier.metro_stations', compact('metro_stations', 'search_arr'));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function add_metro_station(Request $request) {
        $validator = Validator::make($request->all(), [
            'name_en' => ['required', 'string', 'max:255'],
            'name_az' => ['required', 'string', 'max:255'],
            'name_ru' => ['required', 'string', 'max:255'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            unset($request['id']);
            $request->merge(['created_by'=>Auth::id()]);

            CourierMetroStations::create($request->all());

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function update_metro_station(Request $request) {
        $validator = Validator::make($request->all(), [
            'name_en' => ['required', 'string', 'max:255'],
            'name_az' => ['required', 'string', 'max:255'],
            'name_ru' => ['required', 'string', 'max:255'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $id = $request->id;
            unset($request['id'], $request['_token']);

            CourierMetroStations::where(['id'=>$id])->update($request->all());

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function delete_metro_station(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Id not found!']);
        }
        try {
            CourierMetroStations::where(['id'=>$request->id])->whereNull('deleted_by')->update(['deleted_by'=>Auth::id(), 'deleted_at'=>Carbon::now()]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'id'=>$request->id]);
        } catch (\Exception $e) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }


     // region
    public function show_region() {
        try {
            $query = CourierRegion::orderBy('created_at','asc');
            
            $search_arr = array(
                'name' => '',
            );

            if (!empty(Input::get('name')) && Input::get('name') != '' && Input::get('name') != null) {
                $where_name = Input::get('name');
                $query->where('courier_regions.name_en', 'LIKE', '%' . $where_name . '%')->orWhere('courier_regions.name_az', 'LIKE', '%' . $where_name . '%')->orWhere('courier_regions.name_ru', 'LIKE', '%' . $where_name . '%');
                $search_arr['name'] = $where_name;
            }

            $areas = $query
                ->select('courier_regions.id', 'courier_regions.name_en', 'courier_regions.name_az', 'courier_regions.name_ru', 'courier_regions.created_at')
                ->paginate(50);



            return view('backend.courier.region', compact('areas', 'search_arr'));
        } catch (\Exception $exception) {
            dd($exception);
            return view('backend.error');
        }
    }


    public function add_region(Request $request) {
        $validator = Validator::make($request->all(), [
            'name_en' => ['required', 'string', 'max:255'],
            'name_az' => ['required', 'string', 'max:255'],
            'name_ru' => ['required', 'string', 'max:255'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            unset($request['id']);
            $request->merge(['created_by'=>Auth::id()]);

            CourierRegion::create($request->all());

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }


    public function update_region(Request $request) {
        $validator = Validator::make($request->all(), [
            'name_en' => ['required', 'string', 'max:255'],
            'name_az' => ['required', 'string', 'max:255'],
            'name_ru' => ['required', 'string', 'max:255']
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $id = $request->id;
            unset($request['id'], $request['_token']);

            CourierRegion::where(['id'=>$id])->update($request->all());

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function delete_region(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Id not found!']);
        }
        try {
            CourierRegion::where(['id'=>$request->id])->whereNull('deleted_by')->update(['deleted_by'=>Auth::id(), 'deleted_at'=>Carbon::now()]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'id'=>$request->id]);
        } catch (\Exception $e) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }

     // region tariffs
     public function show_region_tariff() {
        try {
            $query = CourierRegionTariff::whereNull('deleted_by');

            $search_arr = array(
                'name' => '',
            );
            // dd($query);
            if (!empty(Input::get('name')) && Input::get('name') != '' && Input::get('name') != null) {
                $where_name = Input::get('name');
                $query->where('name', 'LIKE', '%' . $where_name . '%');
                $search_arr['name'] = $where_name;
            }


            $tariffs = $query
                ->orderBy('created_at','asc')
                ->select('id', 'name', 'from_weight', 'to_weight', 'static_price', 'dynamic_price', 'created_at')
                ->paginate(50);

            return view('backend.courier.region_tariff', compact('tariffs', 'search_arr'));
        } catch (\Exception $exception) {
            dd($exception);
            return view('backend.error');
        }
    }

    public function add_region_tariff(Request $request) {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'from_weight' => ['required'],
            'to_weight' => ['required'],
            'static_price' => ['required'],
            'dynamic_price' => ['required'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            unset($request['id']);
            $request->merge(['created_by'=>Auth::id()]);

            CourierRegionTariff::create($request->all());

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function update_region_tariff(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'from_weight' => ['required'],
            'to_weight' => ['required'],
            'static_price' => ['required'],
            'dynamic_price' => ['required'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $id = $request->id;
            unset($request['id'], $request['_token']);

            CourierRegionTariff::where(['id'=>$id])->update($request->all());

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function delete_region_tariff(Request $request){

        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Id not found!']);
        }
        try {
            CourierRegionTariff::where(['id'=>$request->id])->whereNull('deleted_by')->update(['deleted_by'=>Auth::id(), 'deleted_at'=>Carbon::now()]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'id'=>$request->id]);
        } catch (\Exception $e) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }



}
