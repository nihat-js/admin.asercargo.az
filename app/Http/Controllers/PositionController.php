<?php

namespace App\Http\Controllers;

use App\Location;
use App\Position;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class PositionController extends HomeController
{
    public function show() {
        try {
            $query = Position::leftJoin('locations as l', 'position.location_id', '=', 'l.id')
                ->whereNull('l.deleted_by')->whereNull('position.deleted_by');

            $search_arr = array(
                'name' => '',
                'location' => '',
            );

            if (!empty(Input::get('location')) && Input::get('location') != ''  && Input::get('location') != null) {
                $where_location = Input::get('location');
                $query->where('position.location_id', $where_location);
                $search_arr['location'] = $where_location;
            }

            if (!empty(Input::get('name')) && Input::get('name') != ''  && Input::get('name') != null) {
                $where_name = Input::get('name');
                $query->where('position.name', 'LIKE', '%'.$where_name.'%');
                $search_arr['name'] = $where_name;
            }

            //short by start
            $short_by = 'l.name';
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

            $positions = $query
                ->orderBy($short_by, $shortType)
                ->orderByRAW('LENGTH(position.name)')
                ->orderBy('position.name')
                ->select(
                    'position.id',
                    'position.name',
                    'l.name as location',
                    'position.location_id',
                    'position.created_at'
                )
                ->paginate(50);

            $locations = Location::whereNull('deleted_by')->select('id', 'name')->get();

            return view("backend.positions", compact('positions', 'locations', 'search_arr'));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function add(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:50'],
            'location_id' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            unset($request['id']);
            $request->merge(['created_by'=>Auth::id()]);

            Position::create($request->all());

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function update(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:50'],
            'location_id' => ['required', 'integer'],
            'id' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $id = $request->id;
            unset($request['id'], $request['_token']);

            Position::where(['id'=>$id])->update($request->all());

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
            Position::where(['id'=>$request->id])->whereNull('deleted_by')->update(['deleted_by'=>Auth::id(), 'deleted_at'=>Carbon::now()]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'id'=>$request->id]);
        } catch (\Exception $e) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }
}
