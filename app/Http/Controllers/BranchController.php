<?php

namespace App\Http\Controllers;

use App\Location;
use App\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class BranchController extends Controller
{
    public function show() {
        try {
            $query = DB::table('filial')
                ->where('is_active', 1);
            
            $search_arr = array(
                'title' => '',
            );
            
            if (!empty(Input::get('title')) && Input::get('title') != '' && Input::get('title') != null) {
                $where_title = Input::get('title');
                $query->where('title', 'LIKE', '%' . $where_title . '%');
                $search_arr['title'] = $where_title;
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
            
            $branchs = $query
                ->orderBy($short_by, $shortType)
                ->select(
                    'id',
                    'name',
                    'is_active',
                    'longitude',
                    'latitude',
                )->paginate(50);
            

            return view("backend.branch", compact('branchs', 'search_arr'));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }
    
    public function add(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:50'],
            'longitude' => ['nullable', 'string', 'max:50'],
            'latitude' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            unset($request['id']);
            $request->merge(['created_by'=>Auth::id()]);
    
            DB::table('filial')->insert([
                'name' => $request->input('name'),
                'is_active' => $request->has('is_active') ? $request->input('is_active') : true,
                'longitude' => $request->input('longitude'),
                'latitude' => $request->input('latitude'),
            ]);
            
            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }
    
    public function update(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:50'],
            'longitude' => ['nullable', 'string', 'max:50'],
            'latitude' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $id = $request->id;
            unset($request['id'], $request['_token']);
            
            DB::table('filial')->where(['id'=>$id])->update($request->all());
            
            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }
}
