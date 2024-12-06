<?php

namespace App\Http\Controllers;

use App\Currency;
use App\ExchangeRate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class ExchangeRateController extends HomeController
{
    public function show()
    {
        try {
            $query = ExchangeRate::leftJoin('currency as from_cur', 'exchange_rate.from_currency_id', '=', 'from_cur.id')
                ->leftJoin('currency as to_cur', 'exchange_rate.to_currency_id', '=', 'to_cur.id')
                ->whereNull('exchange_rate.deleted_by');

            //short by start
            $short_by = 'exchange_rate.id';
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

            $exchange_rates = $query
                ->orderBy($short_by, $shortType)
                ->select(
                    'exchange_rate.id',
                    'exchange_rate.rate',
                    'exchange_rate.from_date',
                    'exchange_rate.to_date',
                    'from_cur.name as from_currency',
                    'to_cur.name as to_currency',
                    'exchange_rate.from_currency_id',
                    'exchange_rate.to_currency_id',
                    'exchange_rate.created_at'
                )->paginate(50);

            $currencies = Currency::whereNull('deleted_by')->orderBy('name')->select('id', 'name')->get();

            return view("backend.exchange_rates", compact('exchange_rates', 'currencies'));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rate' => ['required'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date'],
            'from_currency_id' => ['required', 'integer'],
            'to_currency_id' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            unset($request['id']);
            $request->merge(['created_by' => Auth::id()]);

            ExchangeRate::create($request->all());

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'integer'],
            'rate' => ['required'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date'],
            'from_currency_id' => ['required', 'integer'],
            'to_currency_id' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $id = $request->id;
            unset($request['id'], $request['_token']);

            ExchangeRate::where(['id' => $id])->update($request->all());

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
            ExchangeRate::where(['id' => $request->id])->whereNull('deleted_by')->update(['deleted_by' => Auth::id(), 'deleted_at' => Carbon::now()]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'id' => $request->id]);
        } catch (\Exception $e) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }
}
