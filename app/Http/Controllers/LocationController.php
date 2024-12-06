<?php

namespace App\Http\Controllers;

use App\Countries;
use App\Location;
use App\Position;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class LocationController extends HomeController
{
    public function show() {
        try {
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

            $locations = Location::leftJoin('countries as c', 'country_id', '=', 'c.id')
                ->whereNull('locations.deleted_by')
                ->whereNull('c.deleted_by')
                ->orderBy($short_by, $shortType)
                ->select(
                'locations.id',
                'locations.city',
                'locations.name',
                'locations.country_id',
                'c.name_en as country',
                'locations.created_at'
            )->paginate(50);

            $countries = Countries::whereNull('deleted_by')->select('id', 'name_en as name')->orderBy('name_en')->get();

            return view("backend.locations", compact('locations', 'countries'));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function add(Request $request) {
        $validator = Validator::make($request->all(), [
            'city' => ['required', 'string', 'max:50'],
            'country_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:50'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            unset($request['id']);
            $request->merge(['created_by'=>Auth::id()]);

            Location::create($request->all());

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function update(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'integer'],
            'city' => ['required', 'string', 'max:50'],
            'country_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:50'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $id = $request->id;
            unset($request['id'], $request['_token']);

            Location::where(['id'=>$id])->update($request->all());

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
            $delete = Location::where(['id'=>$request->id])->whereNull('deleted_by')->update(['deleted_by'=>Auth::id(), 'deleted_at'=>Carbon::now()]);

            if ($delete) {
                Position::where('location_id', $request->id)->whereNull('deleted_by')->update(['deleted_by'=>Auth::id(), 'deleted_at'=>Carbon::now()]);
            }

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'id'=>$request->id]);
        } catch (\Exception $e) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }

//    public function change_volume_consider(Request $request) {
//        $validator = Validator::make($request->all(), [
//            'id' => 'required|integer',
//            'switch' => 'required|integer',
//        ]);
//        if ($validator->fails()) {
//            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Switch not found!']);
//        }
//        try {
//            $switch = $request->switch;
//
//            Location::where(['id'=>$request->id])->whereNull('deleted_by')->update(['is_volume'=>$switch]);
//
//            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
//        } catch (\Exception $e) {
//            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
//        }
//    }
}
