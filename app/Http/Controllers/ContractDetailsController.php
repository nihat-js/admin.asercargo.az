<?php

namespace App\Http\Controllers;

use App\Contract;
use App\ContractDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ContractDetailsController extends HomeController
{
    public function show(Request $request) {
        $validator = Validator::make($request->all(), [
            'contract_id' => 'required|integer',
            'country_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Contract not found!']);
        }
        try {
            $contract_id = $request->contract_id;
            $country_id = $request->country_id;

            $details = ContractDetail::leftJoin('seller as s', 'contract_detail.seller_id', '=', 's.id')
                ->leftJoin('category as c', 'contract_detail.category_id', '=', 'c.id')
                ->leftJoin('currency as cur', 'contract_detail.currency_id', '=', 'cur.id')
                ->leftJoin('locations as des', 'contract_detail.destination_id', '=', 'des.id')
                ->leftJoin('locations as dep', 'contract_detail.departure_id', '=', 'dep.id')
                ->leftJoin('countries as ctry', 'contract_detail.country_id', '=', 'ctry.id')
                ->leftJoin('tariff_types as type', 'contract_detail.type_id', '=', 'type.id')
                ->where('contract_detail.contract_id', $contract_id)
                ->where('contract_detail.country_id', $country_id)
                ->whereNull('contract_detail.deleted_by')
                ->select(
                    'contract_detail.id',
                    'contract_detail.service_name',
                    'contract_detail.seller_id',
                    's.name as seller',
                    'contract_detail.category_id',
                    'c.name_en as category',
                    'contract_detail.from_weight',
                    'contract_detail.to_weight',
                    'contract_detail.weight_control',
                    'contract_detail.rate',
                    'contract_detail.charge',
                    'contract_detail.currency_id',
                    'cur.name as currency',
                    'contract_detail.destination_id',
                    'des.name as destination',
                    'contract_detail.departure_id',
                    'dep.name as departure',
                    'contract_detail.start_date',
                    'contract_detail.end_date',
                    'contract_detail.default_option',
                    'contract_detail.created_at',
                    'ctry.name_en as country',
                    'contract_detail.country_id',
                    'contract_detail.title_en',
                    'contract_detail.title_az',
                    'contract_detail.title_ru',
                    'contract_detail.description_en',
                    'contract_detail.description_az',
                    'contract_detail.description_ru',
                    'contract_detail.type_id',
                    'type.name_en as type'
                )
                ->get();

            return response(['case'=>'success', 'details'=>$details]);

        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function add(Request $request) {
        $validator = Validator::make($request->all(), [
            'type_id' => ['required', 'integer'],
            'contract_id' => ['required', 'integer'],
            'service_name' => ['required', 'string', 'max:50'],
            'seller_id' => ['nullable', 'integer'],
            'category_id' => ['nullable', 'integer'],
            'from_weight' => ['required'],
            'to_weight' => ['required'],
            'weight_control' => ['nullable', 'integer'],
            'rate' => ['required'],
            'charge' => ['required'],
            'currency_id' => ['required', 'integer'],
            'destination_id' => ['required', 'integer'],
            'departure_id' => ['required', 'integer'],
            'country_id' => ['required', 'integer'],
            'title_az' => ['required', 'string', 'max:255'],
            'title_en' => ['required', 'string', 'max:255'],
            'title_ru' => ['required', 'string', 'max:255'],
            'description_az' => ['nullable', 'string', 'max:150'],
            'description_en' => ['nullable', 'string', 'max:150'],
            'description_ru' => ['nullable', 'string', 'max:150'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            unset($request['id']);
            $request->merge(['created_by'=>Auth::id()]);

            ContractDetail::create($request->all());

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function update(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'integer'],
            'type_id' => ['required', 'integer'],
            'contract_id' => ['required', 'integer'],
            'service_name' => ['required', 'string', 'max:50'],
            'seller_id' => ['nullable', 'integer'],
            'category_id' => ['nullable', 'integer'],
            'from_weight' => ['required'],
            'to_weight' => ['required'],
            'weight_control' => ['nullable', 'integer'],
            'rate' => ['required'],
            'charge' => ['required'],
            'currency_id' => ['required', 'integer'],
            'destination_id' => ['required', 'integer'],
            'departure_id' => ['required', 'integer'],
            'country_id' => ['required', 'integer'],
            'title_az' => ['required', 'string', 'max:255'],
            'title_en' => ['required', 'string', 'max:255'],
            'title_ru' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $id = $request->id;
            unset($request['id'], $request['_token']);
            $request->merge(['created_by'=>Auth::id()]);

            ContractDetail::where('id', $id)->whereNull('deleted_by')->update($request->all());

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
            ContractDetail::where(['id'=>$request->id])->whereNull('deleted_by')->update(['deleted_by'=>Auth::id(), 'deleted_at'=>Carbon::now()]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'id'=>$request->id]);
        } catch (\Exception $e) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }

//    public function set_to_default_contract_detail(Request $request) {
//        $validator = Validator::make($request->all(), [
//            'detail_id' => 'required|integer',
//        ]);
//        if ($validator->fails()) {
//            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Contract not found!']);
//        }
//        try {
//            $detail_id = $request->detail_id;
//
//            $contract = ContractDetail::where('id', $detail_id)->select('contract_id')->first();
//            $contract_id = $contract->contract_id;
//
//            ContractDetail::whereNull('deleted_by')->update(['default_option'=>0]);
//            Contract::whereNull('deleted_by')->update(['default_option'=>0]);
//            ContractDetail::where(['id'=>$detail_id])->whereNull('deleted_by')->update(['default_option'=>1]);
//            Contract::where(['id'=>$contract_id])->whereNull('deleted_by')->update(['default_option'=>1]);
//
//            return response(['case' => 'success', 'title' => 'Set as default!', 'content' => 'Successful!', 'contract_id'=>$contract_id]);
//        } catch (\Exception $e) {
//            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
//        }
//    }

    public function change_volume_consider(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'switch' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Switch not found!']);
        }
        try {
            $switch = $request->switch;

            ContractDetail::where(['id'=>$request->id])->whereNull('deleted_by')->update(['weight_control'=>$switch]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $e) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }
}
