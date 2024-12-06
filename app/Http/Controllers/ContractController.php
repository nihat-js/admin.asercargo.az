<?php

namespace App\Http\Controllers;

use App\Category;
use App\Contract;
use App\ContractDetail;
use App\Countries;
use App\Currency;
use App\Location;
use App\Seller;
use App\TariffType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class ContractController extends HomeController
{
	public function show()
	{
		try {
			$query = Contract::whereNull('deleted_by');

			$search_arr = array(
					'system' => '',
					'start_date' => '',
					'end_date' => '',
			);

			if (!empty(Input::get('system')) && Input::get('system') != '' && Input::get('system') != null) {
				$where_system = Input::get('system');
				$query->where('system', 'LIKE', '%' . $where_system . '%');
				$search_arr['system'] = $where_system;
			}

			if (!empty(Input::get('start_date')) && Input::get('start_date') != '' && Input::get('start_date') != null) {
				$where_start_date = Input::get('start_date');
				$query->where('start_date', '>=', $where_start_date);
				$search_arr['start_date'] = $where_start_date;
			}

			if (!empty(Input::get('end_date')) && Input::get('end_date') != '' && Input::get('end_date') != null) {
				$where_end_date = Input::get('end_date');
				$query->where('end_date', '<=', $where_end_date);
				$search_arr['end_date'] = $where_end_date;
			}

			//short by start
			$short_by = 'id';
			$shortType = 'ASC';
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

			$contracts = $query
					->orderBy($short_by, $shortType)
					->select(
							'id',
							'system',
							'description',
							'start_date',
							'end_date',
							'default_option',
							'created_at'
					)->paginate(50);

			$sellers = Seller::whereNull('deleted_by')->where('only_collector', 0)->orderBy('name')->select('id', 'name')->get();
			$categories = Category::whereNull('deleted_by')->orderBy('name_en')->select('id', 'name_en as name')->get();
			$currencies = Currency::whereNull('deleted_by')->orderBy('name')->select('id', 'name')->get();
			$locations = Location::whereNull('deleted_by')->orderBy('name')->select('id', 'name', 'country_id')->get();
			$countries = Countries::whereNull('deleted_by')->where('id', '<>', 1)->orderBy('name_en')->select('id', 'name_en as name')->get();
			$types = TariffType::whereNull('deleted_by')->orderBy('name_en')->select('id', 'name_en as name')->get();

			return view("backend.contracts", compact(
					'contracts',
					'search_arr',
					'sellers',
					'categories',
					'currencies',
					'locations',
					'countries',
					'types'
			));
		} catch (\Exception $exception) {
			return view('backend.error');
		}
	}

	public function add(Request $request)
	{
		$validator = Validator::make($request->all(), [
				'system' => ['required', 'string', 'max:50'],
				'description' => ['nullable', 'string'],
				'start_date' => ['required', 'date'],
				'end_date' => ['required', 'date'],
		]);
		if ($validator->fails()) {
			return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
		}
		try {
			unset($request['id']);
			$request->merge(['created_by' => Auth::id()]);

			Contract::create($request->all());

			return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
		} catch (\Exception $exception) {
			return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
		}
	}

	public function update(Request $request)
	{
		$validator = Validator::make($request->all(), [
				'id' => ['required', 'integer'],
				'system' => ['required', 'string', 'max:50'],
				'description' => ['nullable', 'string'],
				'start_date' => ['required', 'date'],
				'end_date' => ['required', 'date'],
		]);
		if ($validator->fails()) {
			return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
		}
		try {
			$id = $request->id;
			unset($request['id'], $request['_token']);

			Contract::where(['id' => $id])->whereNull('deleted_by')->update($request->all());

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
			$delete = Contract::where(['id' => $request->id])->whereNull('deleted_by')->update(['deleted_by' => Auth::id(), 'deleted_at' => Carbon::now()]);

			if ($delete) {
				ContractDetail::where(['contract_id' => $request->id])->whereNull('deleted_by')->update(['deleted_by' => Auth::id(), 'deleted_at' => Carbon::now()]);
			}

			return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'id' => $request->id]);
		} catch (\Exception $e) {
			return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
		}
	}

	public function set_to_default_contract(Request $request)
	{
		$validator = Validator::make($request->all(), [
				'contract_id' => 'required|integer',
		]);
		if ($validator->fails()) {
			return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Contract not found!']);
		}
		try {
			Contract::whereNull('deleted_by')->update(['default_option' => 0]);
			Contract::where(['id' => $request->contract_id])->whereNull('deleted_by')->update(['default_option' => 1]);

			return response(['case' => 'success', 'title' => 'Set as default!', 'content' => 'Successful!']);
		} catch (\Exception $e) {
			return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
		}
	}
}
