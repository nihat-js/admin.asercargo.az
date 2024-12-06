<?php

namespace App\Http\Controllers;

use App\Location;
use App\Option;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class OptionController extends HomeController
{
    public function show() {
        try {
            $query = Option::leftJoin('locations as l', 'options.location_id', '=', 'l.id')
                ->whereNull('options.deleted_by');

            $search_arr = array(
                'title' => '',
            );

            if (!empty(Input::get('title')) && Input::get('title') != '' && Input::get('title') != null) {
                $where_title = Input::get('title');
                $query->where('options.title', 'LIKE', '%' . $where_title . '%');
                $search_arr['title'] = $where_title;
            }

            //short by start
            $short_by = 'options.id';
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

            $options = $query
                ->orderBy($short_by, $shortType)
                ->select(
                    'options.id',
                    'options.title',
                    'options.device1',
                    'options.device2',
                    'options.location_id',
                    'l.name as location',
                    'options.created_at'
                )->paginate(50);

            $locations = Location::whereNull('deleted_by')->select('id', 'name')->get();

            return view("backend.options", compact('options', 'locations', 'search_arr'));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function add(Request $request) {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:50'],
            'device1' => ['required', 'string', 'max:50'],
            'device2' => ['required', 'string', 'max:50'],
            'location_id' => ['nullable', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            unset($request['id']);
            $request->merge(['created_by'=>Auth::id()]);

            Option::create($request->all());

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function update(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:50'],
            'device1' => ['required', 'string', 'max:50'],
            'device2' => ['required', 'string', 'max:50'],
            'location_id' => ['nullable', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $id = $request->id;
            unset($request['id'], $request['_token']);

            Option::where(['id'=>$id])->update($request->all());

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
            Option::where(['id'=>$request->id])->whereNull('deleted_by')->update(['deleted_by'=>Auth::id(), 'deleted_at'=>Carbon::now()]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'id'=>$request->id]);
        } catch (\Exception $e) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }
}
