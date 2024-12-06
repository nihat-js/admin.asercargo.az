<?php

namespace App\Http\Controllers;

use App\CourierAreas;
use App\News;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    public function show_news() {
        try {
            $query = News::where('is_active', 1);
            
            $search_arr = array(
                'name' => '',
            );
            
            if (!empty(Input::get('name')) && Input::get('name') != '' && Input::get('name') != null) {
                $where_name = Input::get('name');
                $query->where('name_az', 'LIKE', '%' . $where_name . '%');
                $query->where('name_en', 'LIKE', '%' . $where_name . '%');
                $query->where('name_ru', 'LIKE', '%' . $where_name . '%');
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
            
            $newses = $query
                ->orderBy($short_by, $shortType)
                ->select('id', 'name_en', 'name_az', 'name_ru', 'content_en', 'content_az', 'content_ru', 'image', 'created_at', 'is_active')
                ->paginate(50);
            
            
            return view('backend.news', compact( 'newses', 'search_arr'));
        } catch (\Exception $exception) {
            //dd($exception);
            return view('backend.error');
        }
    }
    
    public function add_news(Request $request) {
        $validator = Validator::make($request->all(), [
            'name_en' => ['required', 'string', 'max:255'],
            'name_az' => ['required', 'string', 'max:255'],
            'name_ru' => ['required', 'string', 'max:255'],
            'content_en' => ['required', 'string', 'max:1800'],
            'content_az' => ['required', 'string', 'max:1800'],
            'content_ru' => ['required', 'string', 'max:1800'],
            'icon' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048']
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            unset($request['id']);
            $request->merge(['created_by'=>Auth::id()]);
            $slug = Str::slug($request->name_az);
            if (isset($request->icon)) {
                $image_name = $slug . '_' . str_random(4) . '_' . microtime();
                Storage::disk('uploads')->makeDirectory('news');
                $cover = $request->file('icon');
                $extension = $cover->getClientOriginalExtension();
                Storage::disk('uploads')->put('news/' . $image_name . '.' . $extension, File::get($cover));
                $image_address = '/uploads/files/news/' . $image_name . '.' . $extension;
                $request['image'] = $image_address;
            }
    
            unset($request['icon']);
            $request['slug'] = $slug;
            $request = $request->except(['id', '_token', 'created_at', 'updated_at', 'icon']);
    

            News::create($request);
            
            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }
    
    public function update_news(Request $request) {
        $validator = Validator::make($request->all(), [
            'name_en' => ['required', 'string', 'max:255'],
            'name_az' => ['required', 'string', 'max:255'],
            'name_ru' => ['required', 'string', 'max:255'],
            'content_en' => ['required', 'string', 'max:1800'],
            'content_az' => ['required', 'string', 'max:1800'],
            'content_ru' => ['required', 'string', 'max:1800'],
            'icon' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048']
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $id = $request->id;
            unset($request['id'], $request['_token']);
            $slug = Str::slug($request->name_az);
            if (isset($request->icon)) {
                $image_name = $slug . '_' . str_random(4) . '_' . microtime();
                Storage::disk('uploads')->makeDirectory('news');
                $cover = $request->file('icon');
                $extension = $cover->getClientOriginalExtension();
                Storage::disk('uploads')->put('news/' . $image_name . '.' . $extension, File::get($cover));
                $image_address = '/uploads/files/news/' . $image_name . '.' . $extension;
                $request['image'] = $image_address;
            }

            unset($request['icon']);
            $request['slug'] = $slug;
            $updateData = $request->except(['id', '_token', 'created_at', 'updated_at', 'icon']);

            News::where(['id'=>$id])->update($updateData);
            
            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            //dd($exception);
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }
    
    public function delete_news(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Id not found!']);
        }
        try {
            News::where(['id'=>$request->id])->forceDelete();
            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'id'=>$request->id]);
        } catch (\Exception $e) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }
}
