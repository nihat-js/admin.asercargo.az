<?php

namespace App\Http\Controllers\Classes;

use App\Item;
use App\Package;
use App\Seller;
use App\TrackingLog;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Collector extends Controller
{
	public function check_package(Request $request, $departure_id)
	{
		try {
			$tracking_number = preg_replace('/\s+/', '', trim($request->number));
			if (Str::startsWith($tracking_number, '42019801')) {
				$tracking_number = Str::after($tracking_number, '42019801');
			}
			$package_details = array();
            $destination_id = $request->destination_id == null ? Auth::user()->destination_id : $request->destination_id;
			//comment test
			$package = Package::leftJoin('users as c', 'package.client_id', '=', 'c.id')
					->leftJoin('seller as s', 'package.seller_id', '=', 's.id')
					->leftJoin('currency as cur', 'package.currency_id', '=', 'cur.id')
					->leftJoin('locations as des', 'package.destination_id', '=', 'des.id')
					->leftJoin('container as con', 'package.container_id', '=', 'con.id')
					->leftJoin('position as pos', 'package.position_id', '=', 'pos.id')
					->leftJoin('flight as flt', 'con.flight_id', '=', 'flt.id')
					->whereRaw("(package.number = '" . $tracking_number . "' or package.internal_id = '" . $tracking_number . "')")
					->whereNull('package.deleted_by')
					->whereNull('package.delivered_by')
					->where('package.departure_id', $destination_id)
					->orderBy('id', 'desc')
					->select(
							'package.id',
							'package.number as track',
							'package.internal_id',
							'package.client_id',
							'package.customer_type_id',
							'package.client_name_surname',
							'package.carrier_status_id',
							'package.carrier_registration_number',
							'package.send_legality',
							'c.suite',
							'c.name as c_name',
							'c.surname as c_surname',
							'c.phone1 as c_phone',
							'c.address1 as c_address',
							'c.is_legality as c_legality',
							'package.length',
							'package.width',
							'package.height',
							'package.gross_weight',
							'package.volume_weight',
							'package.seller_id',
							'package.other_seller',
							's.only_collector as seller_only_collector',
							's.name as seller',
							's.title as seller_title',
							'package.currency_id',
							'cur.name as currency',
							'package.destination_id',
							'des.name as destination',
							'package.delivered_by',
							'package.paid_status',
							'flt.name as flight_name',
							'flt.departure as flt_dep',
							'flt.destination as  flt_des',
							'flt.plan_take_off',
							'package.total_charge_value',
							'package.last_status_id as status_id',
							'package.tariff_type_id',
							'package.description',
							'package.remark as client_comment',
							'package.return_label_doc',
                            'package.container_id',
                            'package.position_id',
                            'pos.name as position'
					)
					->first();

			if (!$package) {
				return response(['case' => 'success', 'package_exist' => false, 'package' => null]);
			}

			if ($package->delivered_by != null) {
				return response(['case' => 'success', 'package_exist' => false, 'package' => $tracking_number . ' : This package has already been delivered.']);
			}

			$package_id = $package->id;

			$item = Item::leftJoin('category as c', 'item.category_id', '=', 'c.id')
					->leftJoin('currency as cur', 'item.currency_id', '=', 'cur.id')
					->where('item.package_id', $package_id)->whereNull('item.deleted_by')
					->select('c.name_en as category', 'item.price', 'item.price_usd', 'cur.name as currency', 'item.quantity', 'item.invoice_doc', 'item.invoice_confirmed', 'item.invoice_status', 'item.title', 'item.subCat')
					->first();

			if ($item) {
				$package_details['category'] = mb_strtolower($item->category);
				$package_details['quantity'] = $item->quantity;
				$package_details['invoice'] = $item->price;
				$package_details['invoice_usd'] = $item->price_usd;
				$package_details['currency'] = $item->currency;
				$package_details['invoice_doc'] = $item->invoice_doc;
				$package_details['invoice_confirmed'] = $item->invoice_confirmed;
				$package_details['invoice_status'] = $item->invoice_status;
				$package_details['title'] = $item->title;
				$package_details['subCat'] = $item->subCat;
			} else {
				$package_details['category'] = '';
				$package_details['quantity'] = 1;
				$package_details['invoice'] = '';
				$package_details['currency'] = '';
				$package_details['invoice_doc'] = '';
				$package_details['invoice_confirmed'] = '';
				$package_details['invoice_status'] = 0;
				$package_details['title'] = '';
				$package_details['subCat'] = '';
			}

			$tracking = TrackingLog::leftJoin('position as p', 'tracking_log.position_id', '=', 'p.id')
					->where('tracking_log.package_id', $package_id)->whereNull('tracking_log.deleted_by')
					->select('tracking_log.container_id', 'p.name as position')
					->orderBy('tracking_log.id', 'desc')
					->first();

			/*if ($tracking) {
				$package_details['container_id'] = $tracking->container_id;
				$package_details['position'] = $tracking->position;
			} else {
				$package_details['container_id'] = null;
				$package_details['position'] = null;
			}
            ;*/
            if ($package->container_id != null) {
				$package_details['container_id'] = $package->container_id;
                $package_details['position'] = null;
			} else {
				$package_details['container_id'] = null;
                $package_details['position'] = $package->position;
			}
            //dd($package_details['position_id']);
			$client_id = $package->client_id;

			if ($client_id === null) {
				$package_details['client_id'] = null;
				$package_details['client'] = "AS";
				$package_details['client_name'] = "";
				$package_details['client_phone'] = "";
				$package_details['client_address'] = "";
			} else {
				$len = strlen($client_id);
				if ($len < 6) {
					for ($i = 0; $i < 6 - $len; $i++) {
						$client_id = '0' . $client_id;
					}
				}

				$package_details['client_id'] = $package->client_id;
				if ($package->client_id != 0) {
					$package_details['client'] = $package->suite . $client_id;
					$package_details['client_name'] = $package->c_name . ' ' . $package->c_surname;
					$package_details['client_phone'] = $package->c_phone;
					$package_details['client_address'] = $package->c_address;
					$package_details['client_legality'] = $package->c_legality;
				} else {
					$package_details['client'] = 'AS0';
					$package_details['client_name'] = $package->client_name_surname;
					$package_details['client_phone'] = '';
					$package_details['client_address'] = '';
				}
			}

			if ($package->currency == null) {
				$package_details['amount_currency'] = "";
			} else {
				$package_details['amount_currency'] = $package->currency;
			}

			$package_details['id'] = $package->id;
			$package_details['description'] = $package->description;
			$package_details['client_comment'] = $package->client_comment;
			$package_details['customer_type_id'] = $package->customer_type_id;
			$package_details['internal_id'] = $package->internal_id;
			$package_details['carrier_status_id'] = $package->carrier_status_id;
			$package_details['carrier_registration_number'] = $package->carrier_registration_number;
			$package_details['track'] = $package->track;
			$package_details['send_legality'] = $package->send_legality;
			$package_details['status_id'] = $package->status_id;
			$package_details['tariff_type_id'] = $package->tariff_type_id;
			$package_details['length'] = $package->length;
			$package_details['width'] = $package->width;
			$package_details['height'] = $package->height;
			$package_details['gross_weight'] = $package->gross_weight;
			$package_details['volume_weight'] = $package->volume_weight;
			if ($package->seller_id != null && $package->seller_id != 0) {
				$package_details['seller'] = mb_strtolower($package->seller);
			} else {
				$package_details['seller'] = '';
			}
			$package_details['seller_id'] = $package->seller_id;
			$package_details['seller_only_collector'] = $package->seller_only_collector;
			$package_details['other_seller'] = $package->other_seller;
			$package_details['departure'] = $package->departure;
			$package_details['destination'] = $package->destination;
			$package_details['paid_status'] = $package->paid_status;
			$package_details['seller_title'] = $package->seller_title;
			$package_details['flight_departure'] = $package->flt_dep;
			$package_details['flight_destination'] = $package->flt_des;
			$package_details['flight_date'] = $package->plan_take_off;
			$package_details['flight_name'] = $package->flight_name;
			$package_details['amount'] = $package->total_charge_value . ' ' . $package->currency;
			$package_details['return_label_doc'] = $package->return_label_doc;

			$client_packages = Package::leftJoin('seller as s', 'package.seller_id', '=', 's.id')
					->leftJoin('locations as des', 'package.destination_id', '=', 'des.id')
					->leftJoin('currency as cur', 'package.currency_id', '=', 'cur.id')
					->leftJoin('lb_status as ps', 'package.last_status_id', '=', 'ps.id')
					->where(['package.client_id' => $client_id, 'package.departure_id' => $departure_id])
					->whereNull('package.deleted_by')
					->whereNull('package.delivered_by')
					->select(
							'number',
							'internal_id',
							'volume_weight',
							'gross_weight',
							'chargeable_weight', // 1 - gross, 2 - volume
							'total_charge_value as amount',
							'cur.name as currency',
							's.name as seller',
							'des.name as destination',
							'ps.status_en as status',
							'ps.color as status_color'
					)
					->orderBy('package.id', 'desc')
					->get();

			if (!$client_packages) {
				$client_packages = null;
				$client_exist = false;
			} else {
				$client_exist = true;
			}
           // dd($client_packages);
			return response(['case' => 'success', 'package_exist' => true, 'package' => $package_details, 'client_exist' => $client_exist, 'client_packages' => $client_packages]);
		} catch (\Exception $exception) {
            //dd($exception);
			return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
		}
	}

	public function client_control($client_id)
	{
		try {
			$user = User::where(['id' => $client_id, 'role_id' => 2])->whereNull('deleted_by')->select('id', 'suite', 'name', 'surname', 'is_legality', 'branch_id')->first();
			if ($user) {
				$user_arr = array();
				$user_name = $user->name . ' ' . $user->surname;
				$user_id = $user->id;
				$len = strlen($user_id);
				if ($len < 6) {
					for ($i = 0; $i < 6 - $len; $i++) {
						$user_id = '0' . $user_id;
					}
				}

				$suite = $user->suite . $user_id;
				$legality = $user->is_legality;
				$user_arr['name'] = $user_name;
				$user_arr['suite'] = $suite;
				$user_arr['is_legality'] = $legality;
				$user_arr['branch_id'] = $user->branch_id;
				return $user_arr;
			} else {
				return false;
			}
		} catch (\Exception $exception) {
			return false;
		}
	}

	public function get_default_category_for_seller($seller)
	{
		try {
			$category = Seller::leftJoin('category as c', 'seller.category_id', '=', 'c.id')
					->where('seller.name', $seller)
					->whereNull('seller.deleted_by')->whereNull('c.deleted_by')
					->select('c.name_en as category')
					->first();

			$response = null;
			if ($category) {
				$response = $category->category;
			}

			return response(['case' => 'success', 'category' => $response]);
		} catch (\Exception $exception) {
			return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
		}
	}

}
