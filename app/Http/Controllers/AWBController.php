<?php

namespace App\Http\Controllers;

use App\AWB;
use App\Location;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class AWBController extends HomeController
{
    public function show() {
        try {
            $query = AWB::leftJoin('locations as l', 'awb.location_id', '=', 'l.id')
                ->whereNull('l.deleted_by')->whereNull('awb.deleted_by');

            $search_arr = array(
                'number' => '',
                'series' => '',
                'location' => '',
                'created' => '',
            );

            if (!empty(Input::get('location')) && Input::get('location') != ''  && Input::get('location') != null) {
                $where_location = Input::get('location');
                $query->where('awb.location_id', $where_location);
                $search_arr['location'] = $where_location;
            }

            if (!empty(Input::get('number')) && Input::get('number') != ''  && Input::get('number') != null) {
                $where_number = Input::get('number');
                $query->where('awb.number', 'LIKE', '%'.$where_number.'%');
                $search_arr['number'] = $where_number;
            }

            if (!empty(Input::get('series')) && Input::get('series') != ''  && Input::get('series') != null) {
                $where_series = Input::get('series');
                $query->where('awb.series', 'LIKE', '%'.$where_series.'%');
                $search_arr['series'] = $where_series;
            }

            if (Auth::user()->role() == 3) {
                if (!empty(Input::get('created')) && Input::get('created') != '' && Input::get('created') != null) {
                    $where_created = Input::get('created');
                    $search_arr['created'] = $where_created;
                    if ($where_created == 'byme') {
                        $query->where('awb.created_by', Auth::id());
                    }
                }
            }

            //short by start
            $short_by = 'awb.id';
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

            $awb_list = $query
                ->orderBy($short_by, $shortType)
                ->select(
                    'awb.id',
                    'awb.number',
                    'awb.series',
                    'l.name as warehouse',
                    'awb.location_id'
                )
                ->paginate(50);

            $locations = Location::whereNull('deleted_by')->select('id', 'name')->get();

            if (Auth::user()->role() == 3) {
                return view("backend.awb_for_collector", compact('awb_list', 'locations', 'search_arr'));
            }

            return view("backend.awb", compact('awb_list', 'locations', 'search_arr'));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function add(Request $request) {
        $validator = Validator::make($request->all(), [
            'number' => ['required', 'integer'],
            'series' => ['required', 'string', 'max:50'],
            'location_id' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            unset($request['id']);
            $request->merge(['created_by'=>Auth::id()]);

            AWB::create($request->all());

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function update(Request $request) {
        $validator = Validator::make($request->all(), [
            'number' => ['required', 'integer'],
            'series' => ['required', 'string', 'max:50'],
            'location_id' => ['required', 'integer'],
            'id' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $id = $request->id;
            unset($request['id'], $request['_token']);

            if (Auth::user()->role() == 3) {
                if (AWB::where(['id'=>$id, 'created_by'=>Auth::id()])->count() == 0) {
                    return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Access denied!']);
                }
            }

            AWB::where(['id'=>$id])->update($request->all());

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
            if (Auth::user()->role() == 3) {
                if (AWB::where(['id'=>$request->id, 'created_by'=>Auth::id()])->count() == 0) {
                    return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Access denied!']);
                }
            }

            AWB::where(['id'=>$request->id])->whereNull('deleted_by')->update(['deleted_by'=>Auth::id(), 'deleted_at'=>Carbon::now()]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'id'=>$request->id]);
        } catch (\Exception $e) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }
}
