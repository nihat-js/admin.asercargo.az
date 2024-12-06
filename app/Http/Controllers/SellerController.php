<?php

namespace App\Http\Controllers;

use App\Category;
use App\Seller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SellerController extends HomeController
{
    public function show()
    {
        try {
            $query = Seller::leftJoin('category as c', 'seller.category_id', '=', 'c.id')
                ->where('seller.only_collector', 0)
                ->whereNull('seller.deleted_by');

            $search_arr = array(
                'name' => '',
            );

            if (!empty(Input::get('name')) && Input::get('name') != '' && Input::get('name') != null) {
                $where_name = Input::get('name');
                $query->where('seller.name', 'LIKE', '%' . $where_name . '%');
                $search_arr['name'] = $where_name;
            }

            //short by start
            $short_by = 'seller.id';
            $shortType = 'DESC';
            if (!empty(Input::get('shortBy')) && Input::get('shortBy') != '' && Input::get('shortBy') != null) {
                $short_by = Input::get('shortBy');
            }
            if (!empty(Input::get('shortType')) && Input::get('shortType') != '' && Input::get('shortType') != null) {
                $short_type = Input::get('shortType');
                if ($short_type == 2) {
                    $shortType = 'DESC';
                } else {
                    $shortType = 'ASC';
                }
            }
            //short by finish

            $sellers = $query
                ->orderBy($short_by, $shortType)
                ->select(
                    'seller.id',
                    'seller.name',
                    'seller.title',
                    'seller.url',
                    'seller.img',
                    'seller.category_id',
                    'c.name_en as category',
                    'seller.created_at'
                )
                ->paginate(50);

            $categories = Category::whereNull('deleted_by')->select('id', 'name_en as name')->get();

            return view("backend.sellers", compact('sellers', 'categories', 'search_arr'));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'mimes:jpeg,png,jpg,gif,svg'],
            'category_id' => ['nullable', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            unset($request['id']);
            $name = $request->name;
            $name = Str::slug($name);

            if (Seller::where('name', $name)->select('id')->first()) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Seller exists!']);
            }

            $request->merge(['created_by' => Auth::id(), 'name' => $name]);

            if (isset($request->icon)) {
                $image_name = $request->name . '_' . str_random(4) . '_' . microtime();
                Storage::disk('uploads')->makeDirectory('files/icons');
                $cover = $request->file('icon');
                $extension = $cover->getClientOriginalExtension();
                Storage::disk('uploads')->put('files/icons/' . $image_name . '.' . $extension, File::get($cover));
                $image_address = '/uploads/files/icons/' . $image_name . '.' . $extension;
                $request['img'] = $image_address;
            }

            unset($request['icon']);
            $request = Input::except('icon');

            Seller::create($request);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'mimes:jpeg,png,jpg,gif,svg'],
            'category_id' => ['nullable', 'integer'],
            'id' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $id = $request->id;
            unset($request['id'], $request['_token']);

            $name = $request->name;
            $name = Str::slug($name);

            if (Seller::where('name', $name)->where('id', '<>', $id)->select('id')->first()) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Seller exists!']);
            }

            $request->merge(['name' => $name]);

            if (isset($request->icon)) {
                $image_name = $request->name . '_' . str_random(4) . '_' . microtime();
                Storage::disk('uploads')->makeDirectory('files/icons');
                $cover = $request->file('icon');
                $extension = $cover->getClientOriginalExtension();
                Storage::disk('uploads')->put('files/icons/' . $image_name . '.' . $extension, File::get($cover));
                $image_address = '/uploads/files/icons/' . $image_name . '.' . $extension;
                $request['img'] = $image_address;
            }

            unset($request['icon']);
            $request = Input::except('icon');

            Seller::where(['id' => $id])->update($request);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Id not found!']);
        }
        try {
            Seller::where(['id' => $request->id])->whereNull('deleted_by')->update(['deleted_by' => Auth::id(), 'deleted_at' => Carbon::now()]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'id' => $request->id]);
        } catch (\Exception $e) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }

    public function delete_icon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Id not found!']);
        }
        try {
            Seller::where(['id' => $request->id])->whereNull('deleted_by')->update(['img' => null]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $e) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }
}
