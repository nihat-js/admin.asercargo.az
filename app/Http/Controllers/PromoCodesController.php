<?php

namespace App\Http\Controllers;

use App\PromoCodes;
use App\PromoCodesGroups;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PromoCodesController extends HomeController
{
    public function show_promo_codes_groups() {
        try {
            $query = PromoCodesGroups::leftJoin('users', 'promo_codes_groups.created_by', '=', 'users.id');

            $search_arr = array(
                'name' => '',
            );

            if (!empty(Input::get('name')) && Input::get('name') != '' && Input::get('name') != null) {
                $where_name = Input::get('name');
                $query->where('promo_codes_groups.name', 'LIKE', '%' . $where_name . '%');
                $search_arr['name'] = $where_name;
            }

            //short by start
            $short_by = 'promo_codes_groups.id';
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

            $groups = $query
                ->select(
                    'promo_codes_groups.id',
                    'promo_codes_groups.name',
                    'promo_codes_groups.percent',
                    'promo_codes_groups.count',
                    'promo_codes_groups.used_count',
                    'promo_codes_groups.created_at',
                    'users.name as user_name',
                    'users.surname as user_surname'
                )
                ->orderBy($short_by, $shortType)
                ->paginate(50);

            return view('backend.promo_codes.groups', compact('groups', 'search_arr'));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function create_promo_codes_group(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:100'],
            'percent' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $request->merge(['created_by' => Auth::id()]);

            PromoCodesGroups::create($request->all());

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function delete_promo_codes_group(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Id not found!']);
        }
        try {
            PromoCodesGroups::where(['id'=>$request->id])->whereNull('deleted_by')->update(['deleted_by'=>Auth::id(), 'deleted_at'=>Carbon::now()]);
            PromoCodes::where(['group_id'=>$request->id])->whereNull('deleted_by')->update(['deleted_by'=>Auth::id(), 'deleted_at'=>Carbon::now()]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'id'=>$request->id]);
        } catch (\Exception $e) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }

    public function create_promo_codes(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'integer'],
            'count' => ['required', 'integer'],
            'type' => ['required', 'string', 'max:10'],
            'code' => ['nullable', 'string', 'max:15'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $group_id = $request->id;

            $group = PromoCodesGroups::where('id', $group_id)->select('count', 'percent')->first();

            if (!$group) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Group not found!']);
            }

            $group_old_count = $group->count;
            $percent = $group->percent;

            $type = $request->type;
            $code = $request->code;
            $count = $request->count;

            if ($type == 'manually') {
                // manually
                $promo_code = $code;

                for ($i = 0; $i < $count; $i++) {
                    PromoCodes::create([
                        'code' => $promo_code,
                        'group_id' => $group_id,
                        'percent' => $percent,
                        'created_by' => Auth::id()
                    ]);
                }
            } else {
                // random
                for ($i = 0; $i < $count; $i++) {
                    $generate_code = $this->generate_promo_code();
                    if (!$generate_code) {
                        return response(['case' => 'error', 'title' => 'Oops!', 'content' => 'Error generating code!']);
                    }

                    $promo_code = $generate_code;

                    PromoCodes::create([
                        'code' => $promo_code,
                        'group_id' => $group_id,
                        'percent' => $percent,
                        'created_by' => Auth::id()
                    ]);
                }
            }

            $group_new_count = $count + $group_old_count;

            PromoCodesGroups::where('id', $group_id)->update(['count' => $group_new_count]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    private function generate_promo_code() {
        try {
            $code = strtolower(Str::random(2) . uniqid());

            while (PromoCodes::where('code', $code)->select('id')->first()) {
                $code = strtolower(Str::random(2) . uniqid());
            }

            return $code;
        } catch (\Exception $exception) {
            return false;
        }
    }

    public function show_promo_codes() {
        try {
            $query = PromoCodes::leftJoin('promo_codes_groups', 'promo_codes.group_id', '=', 'promo_codes_groups.id')
                ->leftJoin('users as client', 'promo_codes.client_id', '=', 'client.id')
                ->leftJoin('currency', 'promo_codes.currency_id', '=', 'currency.id')
                ->whereNull('promo_codes_groups.deleted_by');

            $search_arr = array(
                'used_type' => '',
                'group_id' => '',
                'group' => '',
                'code' => ''
            );

            if (!empty(Input::get('used_type')) && Input::get('used_type') != '' && Input::get('used_type') != null) {
                $where_used_type = Input::get('used_type');
                switch ($where_used_type) {
                    case 'used': {
                        $query->whereNotNull('promo_codes.client_id');
                    } break;
                    case 'not_used': {
                        $query->whereNull('promo_codes.client_id');
                    } break;
                }
                $search_arr['used_type'] = $where_used_type;
            }

            if (!empty(Input::get('group_id')) && Input::get('group_id') != '' && Input::get('group_id') != null) {
                $where_group = Input::get('group_id');
                $query->where('promo_codes.group_id', $where_group);
                $search_arr['group_id'] = $where_group;
            }

            if (!empty(Input::get('group')) && Input::get('group') != '' && Input::get('group') != null) {
                $where_group = Input::get('group');
                $query->where('promo_codes_groups.name', 'LIKE', '%' . $where_group . '%');
                $search_arr['group'] = $where_group;
            }

            if (!empty(Input::get('code')) && Input::get('code') != '' && Input::get('code') != null) {
                $where_code = Input::get('code');
                $query->where('promo_codes.code', 'LIKE', '%' . $where_code . '%');
                $search_arr['code'] = $where_code;
            }

            //short by start
            $short_by = 'promo_codes.id';
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

            $codes = $query
                ->select(
                    'promo_codes.id',
                    'promo_codes.code',
                    'promo_codes_groups.name as group',
                    'promo_codes.percent',
                    'promo_codes.reserved_at',
                    'promo_codes.client_id',
                    'client.name as client_name',
                    'client.surname as client_surname',
                    'promo_codes.used_at',
                    'promo_codes.real_price',
                    'promo_codes.discount',
                    'promo_codes.discounted_price',
                    'currency.name as currency',
                    'promo_codes.created_at'
                )
                ->orderBy($short_by, $shortType)
                ->paginate(50);

            return view('backend.promo_codes.promo_codes', compact('codes', 'search_arr'));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function delete_promo_code(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Id not found!']);
        }
        try {
            $code = PromoCodes::where(['id'=>$request->id])->select('group_id', 'client_id')->first();

            if ($code->client_id != null) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Promo code used!']);
            }

            PromoCodes::where(['id'=>$request->id])->whereNull('deleted_by')->update(['deleted_by'=>Auth::id(), 'deleted_at'=>Carbon::now()]);

            PromoCodesGroups::where('id', $code->group_id)->decrement('count');

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'id'=>$request->id]);
        } catch (\Exception $e) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }
}
