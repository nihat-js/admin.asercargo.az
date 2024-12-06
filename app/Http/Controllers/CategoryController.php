<?php

namespace App\Http\Controllers;

use App\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class CategoryController extends HomeController
{
    public function show() {
        try {
            $query = Category::whereNull('deleted_by');

            $search_arr = array(
                'name' => '',
            );

            if (!empty(Input::get('name')) && Input::get('name') != '' && Input::get('name') != null) {
                $where_name = Input::get('name');
                $query->where('name_' . App::getLocale() , 'LIKE', '%' . $where_name . '%');
                $search_arr['name_' . App::getLocale()] = $where_name;
            }

            //short by start
            $short_by = 'id';
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

            $categories = $query
                ->orderBy($short_by, $shortType)
                ->select(
                    'id',
                    'name_' . App::getLocale() . ' as name',
                    'created_at'
                )->paginate(50);

            return view("backend.categories", compact('categories', 'search_arr'));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function add(Request $request) {
        $validator = Validator::make($request->all(), [
            'name_en' => ['required', 'string', 'max:255', 'unique:category'],
            'name_az' => ['required', 'string', 'max:255', 'unique:category'],
            'name_ru' => ['required', 'string', 'max:255', 'unique:category'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            unset($request['id']);
            $request->merge(['created_by'=>Auth::id()]);

            Category::create($request->all());

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function update(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:50', 'unique:category,name,'.$request->id],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $id = $request->id;
            unset($request['id'], $request['_token']);

            Category::where(['id'=>$id])->update($request->all());

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
            Category::where(['id'=>$request->id])->whereNull('deleted_by')->update(['deleted_by'=>Auth::id(), 'deleted_at'=>Carbon::now()]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'id'=>$request->id]);
        } catch (\Exception $e) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }
}
