<?php

namespace App\Http\Controllers;

use App\Category;
use App\ChangeAccountLog;
use App\Container;
use App\Contract;
use App\ContractDetail;
use App\Countries;
use App\Currency;
use App\EmailListContent;
use App\ExchangeRate;
use App\Flight;
use App\Http\Controllers\Classes\Collector;
use App\Http\Controllers\Classes\SMS;
use App\Item;
use App\Jobs\CollectorInWarehouseJob;
use App\Location;
use App\Package;
use App\PackageClientLog;
use App\PackageFiles;
use App\PackageStatus;
use App\Position;
use App\Seller;
use App\Services\Carrier;
use App\Settings;
use App\SmsTask;
use App\Status;
use App\TariffType;
use App\TrackingLog;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CollectorController extends HomeController
{
    /**
     * @var Carrier
     */
    private Carrier $carrier;

    public function __construct(Carrier $carrier = null, NotificationController $notification)
    {
        parent::__construct();
        if ($carrier) {
            $this->carrier = $carrier;
        }
		$this->notification = $notification;
    }

    public function get_collector()
	{
		try {

			if(Auth::user()->id == 137297){
				$flights = Flight::whereNull('deleted_by')
						->whereNull('closed_by')
						->whereRaw('(public = 1 or location_id = ?)', Auth::user()->location())
						->whereDate('created_at', '>', date('2021-08-20'))
						->orderBy('id', 'desc')
						->select('id', 'name')
						->get();
			}else{
				$flights = Flight::whereNull('deleted_by')
						->whereNull('closed_by')
						->whereRaw('(public = 1 or location_id = ?)', Auth::user()->location())
						->orderBy('id', 'desc')
						->select('id', 'name')
						->get();
			}


			$positions = Position::where('location_id', Auth::user()->location())
					->whereNull('deleted_by')
					->orderBy('name')
					->select('name')
					->get();

			$sellers = Seller::whereNull('deleted_by')
					->where('only_collector', 0)
					->orderBy('name')
					->select('name')
					->get();

			//			$categories = Category::whereNull('deleted_by')
			//					->orderBy('name_en')
			//					->select('name_en as name')
			//					->get();

			$categories = Category::whereNull('deleted_by')
					->orderBy('name_en')
					->where('country_id',  '!=', 10)
					->orWhereNull('country_id')
					->select('name_en as name');
			// dd($categories->pluck('name'));
					

			// if (($countryId = Auth::user()->countryId()) and $countryId == 10) {
			//     $categories = $categories->where('country_id', $countryId);
            // } else {
			//     $categories = $categories->where('country_id', null);
            // }

			$catHong = Category::whereNull('deleted_by')
				->orderBy('name_en')
				->get('name_en as name');
			
			$categories = $categories->get();
			

			$currencies = Currency::whereNull('deleted_by')
					->select('name')
					->get();

			$statuses = Status::whereNull('deleted_by')->where('for_collector', 1)
					->select('id', 'status_en as status')
					->get();

			$types = TariffType::whereNull('deleted_by')->orderBy('name_en')->select('id', 'name_en as name')->get();

			return view('backend.collector', compact(
					'flights',
					'positions',
					'sellers',
					'categories',
					'currencies',
					'statuses',
					'types',
					'catHong'
			));
		} catch (\Exception $exception) {
			return view('backend.error');
		}
	}

	public function add_new_seller_in_collector(Request $request)
	{
		$validator = Validator::make($request->all(), [
				'seller' => ['required', 'string', 'max:255'],
		]);
		if ($validator->fails()) {
			return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
		}
		try {
			if (Auth::user()->has_access_for_add_new_seller() != 1) {
				return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Access denied!']);
			}

			$title = $request->seller;

			$name = explode('.', $title)[0];
			$name = str_replace(' ', '_', $name);

			if (strlen($name) > 50) {
				$name = substr($name, 0, 50);
			}

			if (Seller::where('name', $name)->select('id')->first()) {
				return response(['case' => 'success', 'title' => 'Success!', 'seller' => $name]);
			}

			Seller::create([
					'has_site' => 0,
					'only_collector' => 1,
					'title' => $title,
					'name' => $name,
					'created_by' => Auth::id()
			]);

			return response(['case' => 'success', 'title' => 'Success!', 'seller' => $name]);
		} catch (\Exception $exception) {
			return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
		}
	}

   public function add_new_category_in_collector(Request $request)
   {
       $validator = Validator::make($request->all(), [
           'category' => ['required', 'string', 'max:50'],
       ]);
       if ($validator->fails()) {
           return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
       }
       try {
           $category = $request->category;

           if (Category::where('name_en', $category)->select('id')->first()) {
               return response(['case' => 'success', 'title' => 'Success!', 'category' => $category]);
           }

           Category::create([
               'name_en' => $category,
               'name_az' => $category,
               'name_ru' => $category,
               'created_by' => Auth::id(),
			   'country_id' => Auth::user()->destination_id
           ]);

		//    dd($category);
           return response(['case' => 'success', 'title' => 'Success!', 'category' => $category]);
       } catch (\Exception $exception) {
           return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
       }
   }

	public function get_containers(Request $request, $api = false, $user_location_id = 0)
	{
		$validator = Validator::make($request->all(), [
				'flight_id' => ['required', 'integer'],
		]);
		if ($validator->fails()) {
			return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
		}
		try {
			$flight_id = $request->flight_id;

			if (!$api) {
				$user_location_id = Auth::user()->location();
				$user_id = Auth::id();
			}

			$containers = Container::where(['flight_id' => $flight_id, 'departure_id' => $user_location_id])
					->whereRaw('(public = 1 or departure_id = ?)', Auth::user()->location())
					->whereNull('deleted_by')
					->select('id')
					->get();

			return response(['case' => 'success', 'title' => 'Success!', 'containers' => $containers]);

		} catch (\Exception $exception) {
			return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
		}
	}

	public function get_default_category_for_seller(Request $request)
	{
		$validator = Validator::make($request->all(), [
				'seller' => ['required', 'string', 'max:50'],
		]);
		if ($validator->fails()) {
			return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
		}
		try {
			$collector = new Collector();

			return $collector->get_default_category_for_seller($request->seller);
		} catch (\Exception $exception) {
			return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
		}
	}

	public function check_client(Request $request)
	{
		$validator = Validator::make($request->all(), [
				'client_id' => ['required', 'integer'],
		]);
		if ($validator->fails()) {
			return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
		}
		try {
			$client_id = $request->client_id;
			$departure_id = $request->departure_id == null ? Auth::user()->location() : $request->departure_id ;
			$client_exist = false;
			$client_packages = null;

			$collector = new Collector();
			$client = $collector->client_control($client_id);
			// dd($client);
			if ($client != false) {
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
			}

			return response(['case' => 'success', 'client' => $client, 'client_exist' => $client_exist, 'client_packages' => $client_packages]);
		} catch (\Exception $exception) {
			return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
		}
	}

	private function container_control($id, $departure_id)
	{
		try {
			if (Container::where(['id' => $id, 'departure_id' => $departure_id])
							->whereNull('deleted_by')
							->count() > 0) {
				return true;
			} else {
				return false;
			}
		} catch (\Exception $exception) {
			return false;
		}
	}

	private function get_seller_id($name)
	{
		try {
			if ($name == null || empty($name)) {
				return false;
			}

			$seller = Seller::where('name', $name)->whereNull('deleted_by')->select('id')->first();

			if ($seller) {
				return $seller->id;
			} else {
				return false;
			}
		} catch (\Exception $exception) {
			return false;
		}
	}

	private function get_departure_id($name)
	{
		try {
			if ($name == null || empty($name)) {
				return false;
			}

			$departure = Location::where('name', $name)->whereNull('deleted_by')->select('id')->first();

			if ($departure) {
				return $departure->id;
			} else {
				return false;
			}
		} catch (\Exception $exception) {
			return false;
		}
	}

	private function get_destination_id($name)
	{
		try {
			if ($name == null || empty($name)) {
				return false;
			}

			$destination = Location::where('name', $name)->whereNull('deleted_by')->select('id')->first();

			if ($destination) {
				return $destination->id;
			} else {
				return false;
			}
		} catch (\Exception $exception) {
			return false;
		}
	}

	private function get_currency_id($name)
	{
		try {
			if ($name == null || empty($name)) {
				return false;
			}

			$currency = Currency::where('name', $name)->whereNull('deleted_by')->select('id')->first();

			if ($currency) {
				return $currency->id;
			} else {
				return false;
			}
		} catch (\Exception $exception) {
			return false;
		}
	}

	private function get_category_id($name)
	{
		try {
			if ($name == null || empty($name)) {
				return false;
			}

			$category = Category::where('name_en', $name)->whereNull('deleted_by')->select('id')->first();

			if ($category) {
				return $category->id;
			} else {
				return false;
			}
		} catch (\Exception $exception) {
			return false;
		}
	}

	private function get_position_id($name, $location_id)
	{
		try {
			if ($name == null || empty($name)) {
				return false;
			}

			$position = Position::where('name', $name)->where('location_id', $location_id)->whereNull('deleted_by')->select('id')->first();

			if ($position) {
				return $position->id;
			} else {
				return false;
			}
		} catch (\Exception $exception) {
			return false;
		}
	}

	public function calculate_amount($client_id, $departure, $destination, $category_id, $seller_id, $gross_weight, $volume_weight, $length, $width, $height, $tariff_type_id, $has_chargeable = 1)
	{
		try {
			$current_date = Carbon::today();
			$def = false;
			$contract_id = 0;
			$chargeable_weight = 0;
			$amount = 0;
			$currency_name = '';
			$currency_id = 0;
			$used_detail_id = 0;
			$chargeable_weight_type = 1; // 1 - gross; 2 - volume

			$client_contract = User::where('id', $client_id)->whereNull('deleted_by')->select('contract_id')->first();
			if ($client_contract) {
				if ($client_contract->contract_id != null) {
					$contract_id = $client_contract->contract_id;
					if (Contract::where('id', $contract_id)->where('start_date', '<=', $current_date)->where('end_date', '>=', $current_date)->where('is_active', 1)->whereNull('deleted_by')->count() == 0) {
						//default contract
						$def = true;
					}
					
				} else {
					//default contract
					$def = true;
				}
			
			} else {
				//default contract
				$def = true;
			}
			
			//get default contract
			if ($def) {
				$default_contract = Contract::where('default_option', 1)->where('is_active', 1)->where('start_date', '<=', $current_date)->where('end_date', '>=', $current_date)->whereNull('deleted_by')->orderBy('id', 'desc')->select('id')->first();
				if ($default_contract) {
					$contract_id = $default_contract->id;
				} else {
					$contract_id = 0;
				}
			}
			
			//contract exists control
			if ($contract_id == 0) {
				return ['type' => false, 'response' => 'No valid contract found 0!'];
			}

			//get contract details
			$details = ContractDetail::where(['contract_id' => $contract_id, 'type_id' => $tariff_type_id])->whereNull('deleted_by')
					->where('start_date', '<=', $current_date)
					->where('end_date', '>=', $current_date)
                    ->where(function ($query) use ($seller_id) {
                        $query->where('seller_id', null);
                        $query->orWhere('seller_id', $seller_id);
                    })
                    ->where(function ($query) use ($category_id) {
                        $query->where('category_id', null);
                        $query->orWhere('category_id', $category_id);
                    })
					->where(['departure_id' => $departure, 'destination_id' => $destination])
					->select('id', 'seller_id', 'category_id', 'weight_control', 'from_weight', 'to_weight', 'rate', 'charge', 'currency_id')
					->get();
					// dd($details);
			if (count($details) == 0) {
				// dd($details);
				if ($def) {
					// dd($def);
					return ['type' => false, 'response' => 'No valid rate found 0!'];
				} else {
					//get default contract
					$default_contract = Contract::where('default_option', 1)->where('is_active', 1)->where('start_date', '<=', $current_date)->where('end_date', '>=', $current_date)->whereNull('deleted_by')->orderBy('id', 'desc')->select('id')->first();
					if ($default_contract) {
						$contract_id = $default_contract->id;
						
						$details = ContractDetail::where(['contract_id' => $contract_id, 'type_id' => $tariff_type_id])->whereNull('deleted_by')
								->where('start_date', '<=', $current_date)
								->where('end_date', '>=', $current_date)
								->where(['departure_id' => $departure, 'destination_id' => $destination])
                                ->where(function ($query) use ($seller_id) {
                                    $query->where('seller_id', null);
                                    $query->orWhere('seller_id', $seller_id);
                                })
                                ->where(function ($query) use ($category_id) {
                                    $query->where('category_id', null);
                                    $query->orWhere('category_id', $category_id);
                                })
								->select('id', 'seller_id', 'category_id', 'weight_control', 'from_weight', 'to_weight', 'rate', 'charge', 'currency_id')
								->get();
								// dd($details);
						if (count($details) == 0) {
							return ['type' => false, 'response' => 'No valid rate found 1!'];
						}
					} else {
						return ['type' => false, 'response' => 'No valid contract found 1!'];
					}
				}
			}

			$rates = array();
			$rate_count = 0;
			$choose_details = $this->choose_details($details, $volume_weight, $length, $width, $height, $gross_weight, $category_id, $seller_id, $has_chargeable);
			$rates = $choose_details['rates'];
			$rate_count = $choose_details['rate_count'];
			$chargeable_weight_type = $choose_details['chargeable_weight_type'];
			$chargeable_weight = $choose_details['chargeable_weight'];

			if ($rate_count == 0) {
				if ($def) {
					return ['type' => false, 'response' => 'No valid rate found!'];
				} else {
					//get default contract
					$default_contract = Contract::where('default_option', 1)->where('is_active', 1)->where('start_date', '<=', $current_date)->where('end_date', '>=', $current_date)->whereNull('deleted_by')->orderBy('id', 'desc')->select('id')->first();
					if ($default_contract) {
						$contract_id = $default_contract->id;
						$details = ContractDetail::where(['contract_id' => $contract_id, 'type_id' => $tariff_type_id])->whereNull('deleted_by')
								->where('start_date', '<=', $current_date)
								->where('end_date', '>=', $current_date)
								->where(['departure_id' => $departure, 'destination_id' => $destination])
                                ->where(function ($query) use ($seller_id) {
                                    $query->where('seller_id', null);
                                    $query->orWhere('seller_id', $seller_id);
                                })
                                ->where(function ($query) use ($category_id) {
                                    $query->where('category_id', null);
                                    $query->orWhere('category_id', $category_id);
                                })
								->select('id', 'seller_id', 'category_id', 'weight_control', 'from_weight', 'to_weight', 'rate', 'charge', 'currency_id')
								->get();

						if (count($details) == 0) {
							return ['type' => false, 'response' => 'No valid rate found 2!'];
						}

						$rates = array();
						$rate_count = 0;
						$choose_details = $this->choose_details($details, $volume_weight, $length, $width, $height, $gross_weight, $category_id, $seller_id, $has_chargeable);
						$rates = $choose_details['rates'];
						$rate_count = $choose_details['rate_count'];
						$chargeable_weight_type = $choose_details['chargeable_weight_type'];
						$chargeable_weight = $choose_details['chargeable_weight'];

						if ($rate_count == 0) {
							return ['type' => false, 'response' => 'No valid rate found 3! '];
						}
					} else {
						return ['type' => false, 'response' => 'No valid contract found 2!'];
					}
				}
			}

			//sort rates
			$rates = collect($rates)->sortBy('id')->reverse()->toArray();
			$rates = collect($rates)->sortBy('priority')->reverse()->toArray();

			$selected_rate = false;
			$rate_first = false;
			foreach ($rates as $rate) {
				if ($rate_first == false) {
					$selected_rate = $rate;
					$rate_first = true;
				} else {
					break;
				}
			}

			if ($selected_rate == false) {
				return ['type' => false, 'response' => 'No valid rate found 4!'];
			} else {
				$selected_rate_id = $selected_rate['id'];
			}

			foreach ($details as $detail) {
				if ($detail->id == $selected_rate_id) {
					$used_detail_id = $detail->id;
					$rate_value = $detail->rate;
					$charge = $detail->charge;
					$currency_id = $detail->currency_id;
					$currency = Currency::where('id', $currency_id)->select('name')->first();
					if ($currency) {
						$currency_name = $currency->name;
					}

					$amount = ($chargeable_weight * $rate_value) + $charge;
					break;
				}
			}

			$amount = number_format((float)$amount, 2, '.', '');

			return ['type' => true, 'amount' => $amount, 'currency' => $currency_name, 'currency_id' => $currency_id, 'chargeable_weight_type' => $chargeable_weight_type, 'used_contract_detail_id' => $used_detail_id];
		} catch (\Exception $exception) {
			return ['type' => false, 'response' => 'Something went wrong when contract selected!'];
		}
	}

	private function choose_details($details, $volume_weight, $length, $width, $height, $gross_weight, $category_id, $seller_id, $has_chargeable = 1)
	{
		$rates = array();
		$rate_count = 0;
		$chargeable_weight = 0;
		$chargeable_weight_type = 1; // 1 - gross; 2 - volume
		$i = 0;

		foreach ($details as $detail) {
			if ($has_chargeable === 2) {
				//gross weight
				$chargeable_weight = $gross_weight;
				$chargeable_weight_type = 1;
			} else if ($has_chargeable === 3) {
				//volume weight
				$chargeable_weight = $volume_weight;
				$chargeable_weight_type = 2;
			} else {
				//default
				if ($detail->weight_control === 1) {
					if ($length > 0 && $width > 0 && $height > 0) {
						if ($volume_weight > $gross_weight) {
							$chargeable_weight = $volume_weight;
							$chargeable_weight_type = 2;
						} else {
							$chargeable_weight = $gross_weight;
							$chargeable_weight_type = 1;
						}
					} else {
						$chargeable_weight = $gross_weight;
						$chargeable_weight_type = 1;
					}
				} else {
					$chargeable_weight = $gross_weight;
					$chargeable_weight_type = 1;
				}
			}


			if (($chargeable_weight >= $detail->from_weight) && ($chargeable_weight <= $detail->to_weight)) {
				//ok
				if ($detail->seller_id == null && $detail->category_id == null) {
					//no seller and no category
					//priority = 0
					$rate_count++;
					$rates[$rate_count]['id'] = $detail->id;
					$rates[$rate_count]['priority'] = 0;
				}
				if ($detail->seller_id == null && $detail->category_id != null) {
					//only category
					//priority = 1
					if ($detail->category_id == $category_id) {
						$rate_count++;
						$rates[$rate_count]['id'] = $detail->id;
						$rates[$rate_count]['priority'] = 1;
					}
				}
				if ($detail->category_id == null && $detail->seller_id != null) {
					//only seller
					//priority = 2
					if ($detail->seller_id == $seller_id) {
						$rate_count++;
						$rates[$rate_count]['id'] = $detail->id;
						$rates[$rate_count]['priority'] = 2;
					}
				}
				if ($detail->seller_id != null && $detail->category_id != null) {
					//seller and category
					//priority = 3
					if ($detail->seller_id == $seller_id && $detail->category_id == $category_id) {
						$rate_count++;
						$rates[$rate_count]['id'] = $detail->id;
						$rates[$rate_count]['priority'] = 3;
					}
				}
			} else {
				continue;
			}
		}

		return ['rates' => $rates, 'rate_count' => $rate_count, 'chargeable_weight_type' => $chargeable_weight_type, 'chargeable_weight' => $chargeable_weight];
	}

	public function check_package(Request $request)
	{
		$validator = Validator::make($request->all(), [
				'number' => ['required', 'string', 'max:255'],
		]);
		if ($validator->fails()) {
			return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
		}

		$user_location_id = $request->destination_id == null ? Auth::user()->location() : $request->destination_id;
		$collector = new Collector();

        $response = $collector->check_package($request, $user_location_id);

		return $response;
	}

	public function add_collector(Request $request, $api = false, $user_id = 0, $departure_id = 0)
	{
		// dd($request->all());
		$validator = Validator::make($request->all(), [
			//package
				'number' => ['required', 'string', 'max:255'],
				'tracking_internal_same' => ['nullable', 'integer'],
				'length' => ['nullable', 'integer'],
				'height' => ['nullable', 'integer'],
				'width' => ['nullable', 'integer'],
				'client_id' => ['nullable', 'integer'],
				'client_name_surname' => ['nullable', 'string', 'max:255'],
				'seller' => ['nullable', 'string', 'max:50'], //seller_id
				'destination' => ['required', 'string', 'max:50'], //destination_id
				'gross_weight' => ['required'],
				'currency' => ['nullable', 'string', 'max:50'], //currency_id
				'status_id' => ['required', 'integer'],
				'tariff_type_id' => ['required', 'integer'],
				'description' => ['nullable', 'string', 'max:5000'],
			//item
				'category' => ['nullable', 'string', 'max:50'], //category_id
				'invoice' => ['nullable'],
				'quantity' => ['required', 'integer'],
			//tracking log
				'container_id' => ['nullable', 'integer'],
				'position' => ['nullable', 'string', 'max:50'], //position_id
			//images
                'total_images' => ['nullable', 'integer'],
                'is_legal_entity' => 'in:on,off',
                'invoice_status' => 'in:1,2,3,4',
				'subCat' => ['nullable', 'string', 'max:1000']
		]);
		if ($validator->fails()) {
			return response(['case' => 'warning', 'title' => 'Validation!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
		}
		try {
		    DB::beginTransaction();
			$tracking_number = preg_replace('/\s+/', '', trim($request->number));
			if (Str::startsWith($tracking_number, '42019801')) {
				$tracking_number = Str::after($tracking_number, '42019801');
			}
			$package_arr = array();
			$item_arr = array();
			$tracking_arr = array();
			$has_container = false;
			$currency_id = null;
			$client_name_surname = null;
			$tariff_type_id = $request->tariff_type_id;
			$invoice_status = $request->get('invoice_status');
            $user_location_id = Auth::user()->location();

			if ($user_location_id != 7 and $invoice_status == 3 and $request->invoice < 1) {
			    return response(['case' => 'warning', 'title' => 'Validation!', 'type' => 'warning', 'content' => 'invoice price cannot be 0']);
			}

			$status_id = $request->status_id;

			$tracking_internal_same = 0;
			if (isset($request->tracking_internal_same)) {
				$tracking_internal_same = $request->tracking_internal_same;
			}

			if (!$api) {
				$departure_id = Auth::user()->location();
				$user_id = Auth::id();
			}

			if($request->invoice == null){
				$request->invoice = 0;
			}
			
			$collector_class = new Collector();
			if (isset($request->client_id) && !empty($request->client_id) && $request->client_id != null) {
				$client_control = $collector_class->client_control($request->client_id);
				if (!$client_control) {
					return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'error', 'content' => 'Client not found!']);
				}
				$client_id = $request->client_id;
			} else {
				$client_id = 0;
			}
   
			if ($client_id == 0) {
				if (!isset($request->client_name_surname) && empty($request->client_name_surname) && $request->client_name_surname == null) {
					return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'error', 'content' => 'If the owner of the package is not known, the name and surname must be entered!']);
				}
				$client_name_surname = $request->client_name_surname;
			} else {
				$client_name_surname = '';
			}

			if ($status_id == 36 && $client_id != 0) {
				return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'error', 'content' => 'Unknown client status can only be selected for unknown packages!']);
			}

			if (($request->container_id == null || empty($request->container_id)) && ($request->position == null || empty($request->position))) {
				return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'error', 'content' => 'No container or position selected!']);
			}
			if ($request->container_id != null && $request->position != null) {
				return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'error', 'content' => 'You can only choose one: container or position!']);
			}

			// invoice control
			//		if (($request->invoice == null || empty($request->invoice)) && $status_id == 5) {
			//			return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'error', 'content' => 'Invoice cannot be empty if status is "ready for carriage"!']);
			//		}

			// images control for prohibited or damaged status
			if ($status_id == 8) {
				$has_images = false;
				if ($request->total_images > 0) {
					for ($i = 0; $i < $request->total_images; $i++) {
						if ($request->hasFile('images' . $i)) {
							$validator_file = Validator::make($request->all(), [
									'images' . $i => 'mimes:jpeg,png,jpg,jpeg,gif,svg',
							]);
							if ($validator_file->fails()) {
								continue;
							}
							$has_images = true;
							break;
						}
					}
				}

				if ($has_images === false) {
					// Image must be added
					$package_image_control = PackageFiles::leftJoin('package', 'package_files.package_id', '=', 'package.id')
							->where(['package_files.by_client' => 0])
							->whereRaw("(package.number = '" . $tracking_number . "' or package.internal_id = '" . $tracking_number . "')")
							->whereNull('package_files.deleted_by')
							->whereNull('package.deleted_by')
							->select('package_files.id')
							->first();
			
					if (!$package_image_control) {
						return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'error', 'content' => 'Image must be added for packages which status is "Prohibited" or "Damaged"!']);
					}
				}
			}

			//controls
			$destination_control = $this->get_destination_id($request->destination);
			if ($destination_control) {
				$destination_id = $destination_control;
			} else {
				return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'error', 'content' => 'Destination not found!']);
			}

			if (isset($request->currency) && $request->currency != null && !empty($request->currency) && $request->currency != 'null') {
				$currency_control = $this->get_currency_id($request->currency);
				if ($currency_control) {
					$currency_id = $currency_control;
				} else {
					return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'error', 'content' => 'Currency not found!']);
				}
			}
			$seller_control = $this->get_seller_id($request->seller);
			if ($seller_control) {
				$seller_id = $seller_control;
			} else {
				if ($status_id != 6 && $status_id != 9) {
					// not no_invoice status or incorrect_invoice
			//					return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'error', 'content' => 'Seller cannot be empty if status is not "no invoice" or "incorrect invoice"!']);
				}
				if ($invoice_status == 3) {
				    return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'error', 'content' => 'Seller can not be empty if status is "invoice available" ']);
                }
				$seller_id = null;
			}
			if (!$request->get("title") and $invoice_status == 3) {
                return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'error', 'content' => 'Title can not be empty if status is "invoice available" ']);
            }
			$category_control = $this->get_category_id($request->category);
			if ($category_control) {
				$category_id = $category_control;
			} else {
				if ($status_id != 6 && $status_id != 9) {
					// not no_invoice status or incorrect_invoice
					//					return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'error', 'content' => 'Category cannot be empty if status is not "no invoice" or "incorrect invoice"!']);
				}
                if ($invoice_status == 3) {
                    return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'error', 'content' => 'Category can not be empty if status is "invoice available" ']);
                }
				$category_id = null;
			}
			if ($request->position != null && !empty($request->position)) {
				if ($status_id == 5) {
					return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'error', 'content' => 'Packages which status is "Ready for carriage" cannot be placed in the position!']);
				}
				$position_control = $this->get_position_id($request->position, $departure_id);
				if ($position_control) {
					$position_id = $position_control;
				} else {
					return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'error', 'content' => 'Position not found at your location!']);
				}
				$tracking_arr['position_id'] = $position_id;
				$package_arr['last_container_id'] = null;
			}
			if ($request->container_id != null && !empty($request->container_id)) {
				if ($client_id == 0) {
					return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'error', 'content' => 'Unknown packages cannot be placed in the container!']);
				}
				if ($status_id != 5) {
					return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'error', 'content' => 'Packages which status is "Ready for carriage" can be placed in the container!']);
				}
				$container_control = $this->container_control($request->container_id, $departure_id);
				if (!$container_control) {
					return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'error', 'content' => 'Container not found or Container closed!']);
				}
				$tracking_arr['container_id'] = $request->container_id;
				$package_arr['last_container_id'] = $request->container_id;
				$package_arr['container_date'] = Carbon::now();
				$has_container = $request->container_id;
			}

			//package
            $isLegalEntity = false;
            if ($request->get('is_legal_entity') == 'on') {
                $isLegalEntity = true;
                $package_arr['customer_type_id'] = 2;
            } else {
		        $package_arr['customer_type_id'] = 1;
            }

            
            $branch = null;
            
            if ($client_id != 0){
              $branch =  $client_control['branch_id'];
            }
           
            if ($branch == null){
                $branch = 1;
            }
            
			$package_arr['number'] = $tracking_number;
			$package_arr['description'] = $request->description;
			$package_arr['client_id'] = $client_id;
			$package_arr['client_name_surname'] = $client_name_surname;
			$package_arr['width'] = $request->width;
			$package_arr['height'] = $request->height;
			$package_arr['length'] = $request->length;
			$volume_weight = ($request->width * $request->height * $request->length) / 6000;
			$package_arr['volume_weight'] = $volume_weight;
			$package_arr['gross_weight'] = $request->gross_weight;
			if ($package_arr['gross_weight'] > $package_arr['volume_weight']) {
				$package_arr['chargeable_weight'] = 1;
			} else {
				$package_arr['chargeable_weight'] = 2;
			}
			$package_arr['unit'] = 'kg';
			$package_arr['seller_id'] = $seller_id;
			$country = Location::where('id', $departure_id)->select('country_id')->first();
			if ($country) {
				$country_id = $country->country_id;
			} else {
				$country_id = 0;
			}
			$package_arr['country_id'] = $country_id;
			$package_arr['departure_id'] = $departure_id;
			$package_arr['destination_id'] = $destination_id;
			$package_arr['tariff_type_id'] = $tariff_type_id;
			$package_arr['branch_id'] = $branch;
            
            
            $packageInfo = Package::with('item:id,package_id,invoice_status,title,price,price_usd,currency_id,category_id')
                ->with('status')
                ->whereRaw("(package.number = '" . $package_arr['number'] . "' or package.internal_id = '" . $package_arr['number'] . "')")
                ->first();

	        if ($packageInfo) {
                if ($status_id == 37 and ($packageInfo->getAttribute("last_status_id") == 5)) {
                    return response([
                        'case' => 'warning',
                        'title' => 'warning',
                        'type' => 'warning',
                        'content' => 'Cannot change to not declared, package in ready for carriage status'
                    ]);
                } elseif (
                    in_array($packageInfo->last_status_id, config('customs.package.package_statuses'))
                    and $status_id != 5
                ) {
                    return response([
                        'case' => 'warning',
                        'title' => 'warning',
                        'type' => 'warning',
                        'content' => 'Can only switch to ready for carriage status if the package is in declared status'
                    ]);
                }

				
            }
			$package = $this->add_package($package_arr, $user_id, $status_id, $tracking_internal_same);

			if (isset($package['status'])) {
			    return response([
			        'case' => 'warning',
                    'title' => $package['title'],
                    'type' => 'warning',
                    'content' => $package['case']
                ]);
            }
			
			if ($package) {
				$package_id = $package[0];
				$internal_id = $package[1];
				$first_scan = $package[2];
				$old_last_status_id = $package[3];
				$carrierRegistrationNumber = $package[4];
				$carrierStatusId = $package[5];
			} else {
				return response(['case' => 'error', 'title' => 'Sorry, something went wrong when you saved the package!', 'type' => 'error', 'content' => 'Note: Package can be available in archive.']);
			}

			$images_url_arr_for_email = array();
			if ($request->total_images > 0) {
				for ($i = 0; $i < $request->total_images; $i++) {
					if ($request->hasFile('images' . $i)) {
						$validator_file = Validator::make($request->all(), [
								'images' . $i => 'mimes:jpeg,png,jpg,jpeg,gif,svg',
						]);
						if ($validator_file->fails()) {
							continue;
						}
						$image = $request->file('images' . $i);
						$image_name = $tracking_number . '_' . $i . '_' . Str::random(4) . '_' . time();
						Storage::disk('uploads')->makeDirectory('files/packages/images');
						$cover = $image;
						$extension = $cover->getClientOriginalExtension();
						Storage::disk('uploads')->put('files/packages/images/' . $image_name . '.' . $extension, File::get($cover));
						$url = '/uploads/files/packages/images/' . $image_name . '.' . $extension;
						PackageFiles::create([
								'domain' => $request->root(),
								'url' => $url,
								'package_id' => $package_id,
								'type' => 1, //image
								'name' => $image_name,
								'extension' => $extension,
								'created_by' => $user_id
						]);
						$url_for_email = 'https://manager.asercargo.az' . $url;
						array_push($images_url_arr_for_email, $url_for_email);
					}
				}
			}

			//item
			//			if ($request->get('invoice') == null) {
			//			    return response([
			//			        'case' => 'error',
			//                  'title' => 'Error!',
				//                'type' => 'error',
				//              'content' => 'Invoice price must be set'
					//        ]);
				//		} else {
				if ($currency_id != 1) {
					$date = Carbon::today();
					$rate = ExchangeRate::whereDate('from_date', '<=', $date)
							->whereDate('to_date', '>=', $date)
							->where(['from_currency_id' => 1, 'to_currency_id' => $currency_id]) //to USD
							->select('rate')
							->orderBy('id', 'desc')
							->first();

					$price_usd = 0;
					if ($rate) {
						$price_usd = $request->invoice / $rate->rate;
						$price_usd = sprintf('%0.2f', $price_usd);
					}
				} else {
					$price_usd = $request->invoice;
				}
		//		}

			if(Auth::user()->location() == 6){
				if($request->subCat == null){
					return response(['case' => 'error', 'title' => 'Ooops', 'type' => 'error', 'content' => 'Note: Sub category is required.']);
				}
			}

           /* if($user_location_id == 7 && $status_id != 7 && $status_id != 8){
                //dd($status_id);
                $invoice_status = 3;
            }*/

            //dd('here');
			$item_arr['category_id'] = $category_id;
			$item_arr['price'] = $request->invoice;
			$item_arr['price_usd'] = $price_usd;
			$item_arr['currency_id'] = $currency_id;
			$item_arr['quantity'] = $request->quantity;
			$item_arr['package_id'] = $package_id;
			$item_arr['invoice_status'] = $invoice_status;
			$item_arr['title'] = $request->title;
			if($request->subCat == 'undefined'){
				$item_arr['subCat'] = null;
			}else{
				$item_arr['subCat'] = $request->subCat;
			}
	
			$item = $this->add_item($item_arr, $user_id, $status_id);

			if ($packageInfo) {
                if (
                    $packageInfo->item->getAttribute('title') != $item_arr['title'] or
                    $packageInfo->item->getAttribute('price') != $item_arr['price'] or
                    $packageInfo->item->getAttribute('currency_id') != $item_arr['currency_id'] or
                    $packageInfo->item->getAttribute('price_usd') != $item_arr['price_usd']
			//                    $packageInfo->item->getAttribute('quantity') != $item_arr['quantity']
                ) {
				//                    Package::where('id', $item_arr['package_id'])
				//                        ->whereNotIn('carrier_status_id', [7, 8])
				//                        ->update([
				//                            'carrier_status_id' => 9
				//                        ]);
                }

				

				if (
					$status_id == 5 and
					!in_array($carrierStatusId, config('customs.package.declaration_statuses')) and
					!$isLegalEntity
					and !count($packageInfo->status->where('status_id', 5))
				) {
					return response([
						'case' => 'error',
						'type' => 'error',
						'title' => 'Error! (Smart Customs)',
						'content' => 'Cannot be set Ready for Carriage! Smart Customs Permission!'
					]);
				}

				if (
					$status_id == 5 and
					!in_array($invoice_status, [3, 4])
				) {
					return response([
						'case' => 'warning',
						'type' => 'warning',
						'title' => 'Warning! (Invoice Status)',
						'content' => 'Cannot be set Ready for Carriage! Invoice must be correct!'
					]);
				}
            }

            if (!$item) {
				return response(['case' => 'error', 'title' => 'Error!', 'type' => 'error', 'content' => 'Sorry, something went wrong when you saved the item!']);
			}

			//tracking log
			$tracking_arr['package_id'] = $package_id;
			$tracking_arr['operator_id'] = $user_id;
			$tracking = $this->add_tracking_log($tracking_arr, $user_id);
			if (!$tracking) {
				return response(['case' => 'error', 'title' => 'Error!', 'type' => 'error', 'content' => 'Sorry, something went wrong when you saved the tracking log!']);
			}

			//calculate amount
			$amount_response = $this->calculate_amount($client_id, $departure_id, $destination_control, $category_id, $seller_id, $request->gross_weight, $volume_weight, $request->length, $request->width, $request->height, $tariff_type_id);

			if ($amount_response['type'] == false) {
				return response(['case' => 'error', 'title' => 'Error!', 'type' => 'error', 'content' => $amount_response['response']]);
			}
			$amount = $amount_response['amount'];
			$currency = $amount_response['currency'];
			$currency_id_for_amount = $amount_response['currency_id'];
			$chargeable_weight_type = $amount_response['chargeable_weight_type'];
			$used_contract_detail_id = $amount_response['used_contract_detail_id'];

            $amount_azn = $this->CalculateToAzn($currency_id_for_amount, 3, $amount);

			if ($currency_id_for_amount == 1) {
				//usd
				$amount_usd = $amount;
			} else {
				$date = Carbon::today();
				$rate_for_amount = ExchangeRate::whereDate('from_date', '<=', $date)
						->whereDate('to_date', '>=', $date)
						->where(['from_currency_id' => $currency_id_for_amount, 'to_currency_id' => 1]) //to USD
						->select('rate')
						->orderBy('id', 'desc')
						->first();
				if ($rate_for_amount) {
					$amount_usd = $rate_for_amount->rate * $amount;
				} else {
					return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'warning', 'content' => 'Exchange rate not found (Amount USD)!']);
				}
			}

			Package::where('id', $package_id)->update([
					'chargeable_weight' => $chargeable_weight_type,
					'total_charge_value' => $amount,
					'amount_usd' => $amount_usd,
					'amount_azn' => $amount_azn,
					'currency_id' => $currency_id_for_amount,
					'used_contract_detail_id' => $used_contract_detail_id
			]);

			$flight_details = false;
			if ($has_container) {
				$flight_details = Container::leftJoin('flight as flt', 'container.flight_id', '=', 'flt.id')
						->where('container.id', $has_container)
						->select('flt.departure', 'flt.destination', 'flt.plan_take_off')
						->first();
			}

			$container_details_arr = array();
			$has_container_details = false;
			if ($has_container) {
				$container_id = $has_container;
				$packages = Item::leftJoin('package', 'item.package_id', '=', 'package.id')
						->where('package.container_id', $container_id)
						->whereNull('package.deleted_by')
						->select('package.gross_weight')
						->get();

				$total_weight = 0;
				$packages_count = 0;

				foreach ($packages as $package) {
					$packages_count++;
					if ($package->gross_weight != null) {
						$total_weight += $package->gross_weight;
					}
				}

				$container_details_arr['container'] = 'CN' . $container_id;
				$container_details_arr['count'] = "Packages count: " . $packages_count;
				$container_details_arr['weight'] = "Total weight: " . $total_weight . " kg";

				$has_container_details = true;
			}
            if ($packageInfo and
                $status_id == 5 and
                !in_array($carrierStatusId, config('customs.package.declaration_statuses')) and
                !$isLegalEntity
                and !count($packageInfo->status->where('status_id', 5))
            ) {
                return response([
                    'case' => 'error',
                    'type' => 'error',
                    'title' => 'Error! (Smart Customs)',
                    'content' => 'Cannot be set Ready for Carriage! Smart Customs Permission!'
                ]);
            }
            if (
                $status_id == 5 and
                !in_array($invoice_status, [3, 4])
            ) {
                return response([
                    'case' => 'warning',
                    'type' => 'warning',
                    'title' => 'Warning! (Invoice Status)',
                    'content' => 'Cannot be set Ready for Carriage! Invoice must be correct!'
                ]);
            }

			if ($client_id != 0) {
				// calculate last 30 days amount
				$last_30_days_amount = $this->packages_price_for_last_month($client_id);
				User::where('id', $client_id)->update(['last_30_days_amount' => $last_30_days_amount]);

				// email notification
				$client_inform = User::where('id', $client_id)->select('name', 'surname', 'email', 'language', 'passport_fin', 'phone1')->first();
				$lang = $client_inform->language;
				$lang = strtolower($lang);
				if ($client_inform) {
					// smart_customs
					switch ($status_id) {
						case 100: {
							$emails = EmailListContent::where(['type' => 'in_warehouse'])->first();

								if ($emails) {
									$country_check = Countries::where('id', $country_id)->select('name_' . $lang . ' as name')->first();
								if ($country_check) {
									$country_name = $country_check->name;
								} else {
									$country_name = '---';
								}
								$store_name = $request->seller;
								$track = $tracking_number;
								$client = $client_inform->name . ' ' . $client_inform->surname;

								$email_to = $client_inform->email;
								$email_title = $emails->{'title_' . $lang}; //from
								$email_subject = $emails->{'subject_' . $lang};
								$email_subject = str_replace('{country_name}', $country_name, $email_subject);
								$email_bottom = $emails->{'content_bottom_' . $lang};
								$email_content = $emails->{'content_' . $lang};

								$email_content = str_replace('{name_surname}', $client, $email_content);
								$email_content = str_replace('{store_name}', $store_name, $email_content);
								$email_content = str_replace('{tracking_number}', $track, $email_content);
								$email_content = str_replace('{country_name}', $country_name, $email_content);

								

								$job = (new CollectorInWarehouseJob($email_to, $email_title, $email_subject, $email_content, $email_bottom))
										->delay(Carbon::now()->addSeconds(10));
								dispatch($job);
							}

						} break;

						case 5:
							{
								// ready
								$email = EmailListContent::where(['type' => 'ready_for_carriage'])->first();

								if ($email) {
									$package = Package::with('client')->where('id', $package_id)->first();
									// dd($package);
									$country = Countries::where('id', $country_id)
									->select('name_' . $lang . ' as name')
									->first();
									if ($country) {
										$countryName = $country->name;
									} else {
										$countryName = '---';
									}
									$storeName = $request->seller;
									$tracking = $tracking_number;
									$internal = $internal_id;
									$clientName = $client_inform->name . ' ' . $client_inform->surname;
									$mailTo = $client_inform->email;
									$mailTitle = $email->{'title_az'};
									$mailSubject = $email->{'subject_' . $lang};
									$mailSubject = str_replace('{tracking_number}', $package->number, $mailSubject);
									$mailBottom = $email->{'content_bottom_az'};
									$mailContent = $email->{'content_' .$lang};
									$email_push_content = $email->{'push_content_' . $lang};
									$email_push_content = str_replace('{tracking_number}', $package->number, $email_push_content);
									
									$mailContent = str_replace('{seller}', $storeName, $mailContent);
									$mailContent = str_replace('{client_name}', $clientName, $mailContent);
									$mailContent = str_replace('{country}', $countryName, $mailContent);
									$mailContent = str_replace('{tracking_number}', $package->number, $mailContent);
									$mailContent = str_replace('{internal}', $internal, $mailContent);

									$this->notification->sendNotification($mailTitle, $mailSubject, $email_push_content, $client_id);
								}
							}
							break;

						case 6:	{
								// no invoice
								$emails = EmailListContent::where(['type' => 'invoice_notification'])->first();

								if ($emails) {
									$country_check = Countries::where('id', $country_id)->select('name_' . $lang . ' as name')->first();
									if ($country_check) {
										$country_name = $country_check->name;
									} else {
										$country_name = '---';
									}
									$store_name = $request->seller;
									$track = $tracking_number;
									$client = $client_inform->name . ' ' . $client_inform->surname;

									$email_to = $client_inform->email;
									$email_title = $emails->{'title_' . $lang}; //from
									$email_subject = $emails->{'subject_' . $lang};
									$email_bottom = $emails->{'content_bottom_' . $lang};
									$email_content = $emails->{'content_' . $lang};
									$email_button = $emails->{'button_name_' . $lang};

									$today = Carbon::today()->toDateString();
									if ($country_id == 7) {
										// for turkey
										$today_week_day_no = date('w');
										if ($today_week_day_no >= 3 && $today_week_day_no < 5) {
											$date_for_email = date('Y-m-d', strtotime('next friday', strtotime($today)));
										} else {
											$date_for_email = date('Y-m-d', strtotime('next wednesday', strtotime($today)));
										}
										$date_for_email .= ' 13:00';
									} else {
										// for others
										$date_for_email = date('Y-m-d', strtotime('next friday', strtotime($today)));
										$date_for_email .= ' 11:00';
									}

									$email_push_content = $emails->{'push_content_' . $lang};
									$email_push_content = str_replace('{tracking_number}', $track, $email_push_content);

									$email_content = str_replace('{name_surname}', $client, $email_content);
									$email_content = str_replace('{date}', $date_for_email, $email_content);
									$email_content = str_replace('{store_name}', $store_name, $email_content);
									$email_content = str_replace('{tracking_number}', $track, $email_content);
									$email_content = str_replace('{country_name}', $country_name, $email_content);

									$this->notification->sendNotification($email_title, $email_subject, $email_push_content, $client_id);

									$job = (new CollectorInWarehouseJob($email_to, $email_title, $email_subject, $email_content, $email_bottom, $email_button))
											->delay(Carbon::now()->addSeconds(10));
									dispatch($job);
								}
							}
							break;
						case 7:
							{
								// prohibited
								$emails = EmailListContent::where(['type' => 'prohibited_item'])->first();

								if ($emails) {

                                    $country_check = Countries::where('id', $country_id)->select('name_' . $lang . ' as name')->first();
                                    if ($country_check) {
                                        $country_name = $country_check->name;
                                    } else {
                                        $country_name = '---';
                                    }

									$track = $tracking_number;
									$client = $client_inform->name . ' ' . $client_inform->surname;

									$email_to = $client_inform->email;
									$email_title = $emails->{'title_' . $lang}; //from
									$email_subject = $emails->{'subject_' . $lang};
									$email_bottom = $emails->{'content_bottom_' . $lang};
									$email_content = $emails->{'content_' . $lang};
									$email_button = $emails->{'button_name_' . $lang};
									$email_list_inside = $emails->{'list_inside_' . $lang};
									$email_push_content = $emails->{'push_content_' . $lang};
									$email_push_content = str_replace('{tracking_number}', $track, $email_push_content);
                                    $email_push_content = str_replace('{country_name}', $country_name, $email_push_content);

									$list_insides = '';
									for ($k = 0; $k < count($images_url_arr_for_email); $k++) {
										$image_url_for_email = '<a href="' . $images_url_arr_for_email[$k] . '">' . $images_url_arr_for_email[$k] . '</a>';

										$list_inside = $email_list_inside;

										$list_inside = str_replace('{image_url}', $image_url_for_email, $list_inside);

										$list_insides .= $list_inside;
									}

									$email_content = str_replace('{name_surname}', $client, $email_content);
									$email_content = str_replace('{tracking_number}', $track, $email_content);
									$email_content = str_replace('{list_inside}', $list_insides, $email_content);
                                    $email_push_content = str_replace('{list_inside}', $list_insides, $email_push_content);

                                    $content_prohibited_item = empty($email_push_content) ? $email_content : $email_push_content;

									$this->notification->sendNotification($email_title, $email_subject, $email_push_content, $client_id);

									$job = (new CollectorInWarehouseJob($email_to, $email_title, $email_subject, $content_prohibited_item, $email_bottom, $email_button))
											->delay(Carbon::now()->addSeconds(10));
									dispatch($job);
								}
							}
							break;
						case 8:
							{
								// damaged
								$emails = EmailListContent::where(['type' => 'damaged_item'])->first();

								if ($emails) {
									$country_check = Countries::where('id', $country_id)->select('name_' . $lang . ' as name')->first();
									if ($country_check) {
										$country_name = $country_check->name;
									} else {
										$country_name = '---';
									}

									$track = $tracking_number;
									$client = $client_inform->name . ' ' . $client_inform->surname;

									$email_to = $client_inform->email;
									$email_title = $emails->{'title_' . $lang}; //from
									$email_subject = $emails->{'subject_' . $lang};
									$email_bottom = $emails->{'content_bottom_' . $lang};
									$email_content = $emails->{'content_' . $lang};
									$email_button = $emails->{'button_name_' . $lang};
									$email_list_inside = $emails->{'list_inside_' . $lang};
									$email_push_content = $emails->{'push_content_' . $lang};
									$email_push_content = str_replace('{tracking_number}', $track, $email_push_content);
									$email_push_content = str_replace('{country_name}', $country_name, $email_push_content);

									$list_insides = '';
									for ($k = 0; $k < count($images_url_arr_for_email); $k++) {
										$image_url_for_email = '<a href="' . $images_url_arr_for_email[$k] . '">' . $images_url_arr_for_email[$k] . '</a>';

										$list_inside = $email_list_inside;

										$list_inside = str_replace('{image_url}', $image_url_for_email, $list_inside);

										$list_insides .= $list_inside;
									}

									$email_content = str_replace('{name_surname}', $client, $email_content);
									$email_content = str_replace('{tracking_number}', $track, $email_content);
									$email_content = str_replace('{country_name}', $country_name, $email_content);
									$email_content = str_replace('{list_inside}', $list_insides, $email_content);
                                    $email_push_content = str_replace('{list_inside}', $list_insides, $email_push_content);

                                    $content_damaged_item = empty($email_push_content) ? $email_content : $email_push_content;

                                    $this->notification->sendNotification($email_title, $email_subject, $content_damaged_item, $client_id);

									$job = (new CollectorInWarehouseJob($email_to, $email_title, $email_subject, $email_content, $email_bottom, $email_button))
											->delay(Carbon::now()->addSeconds(10));
									dispatch($job);
								}
							}
							break;
                        case 37:
                        {
                            if ($first_scan) {
                                if ($invoice_status == 1) {
                                    $email = EmailListContent::where([
                                        'type' => 'not_declared_no_invoice'
                                    ])->first();

                                    if ($email) {
                                        $country = Countries::where('id', $country_id)
                                            ->select('name_' . $lang . ' as name')
                                            ->first();
                                        if ($country) {
                                            $countryName = $country->name;
                                        } else {
                                            $countryName = '---';
                                        }
                                        $storeName = $request->seller;
                                        $tracking = $tracking_number;
                                        $internal = $internal_id;
                                        $clientName = $client_inform->name . ' ' . $client_inform->surname;
                                        $mailTo = $client_inform->email;
                                        $mailTitle = $email->{'title_az'};
                                        $mailSubject = $email->{'subject_az'};
                                        $mailSubject = str_replace('{country_name}', $countryName, $mailSubject);
                                        $mailBottom = $email->{'content_bottom_az'};
                                        $mailContent = $email->{'content_az'};

										$email_push_content = $email->{'push_content_' . $lang};
										$email_push_content = str_replace('{tracking_number}', $tracking, $email_push_content);

                                        $mailContent = str_replace('{seller}', $storeName, $mailContent);
                                        $mailContent = str_replace('{client_name}', $clientName, $mailContent);
                                        $mailContent = str_replace('{country}', $countryName, $mailContent);
                                        $mailContent = str_replace('{tracking}', $tracking, $mailContent);
                                        $mailContent = str_replace('{internal}', $internal, $mailContent);

                                        $content = empty($email_push_content) ? $mailContent : $email_push_content;

										$this->notification->sendNotification($mailTitle, $mailSubject, $content, $client_id);

                                        $job = (new CollectorInWarehouseJob($mailTo, $mailTitle, $mailSubject, $mailContent, $mailBottom))
                                            ->delay(Carbon::now()->addSeconds(10));
                                        dispatch($job);
                                    }
                                } else {
                                    $emails = EmailListContent::where(['type' => 'not_declared_notification'])->first();
			
                                    if ($emails) {
                                        $country_check = Countries::where('id', $country_id)->select('name_' . $lang . ' as name')->first();
                                        if ($country_check) {
                                            $country_name = $country_check->name;
                                        } else {
                                            $country_name = '---';
                                        }
                                        $store_name = $request->seller;
                                        $track = $tracking_number;
                                        $client = $client_inform->name . ' ' . $client_inform->surname;
                                        $internal = $internal_id;

                                        $email_to = $client_inform->email;
                                        $email_title = $emails->{'title_' . 'az'}; //from
                                        $email_subject = $emails->{'subject_' . 'az'};
                                        $email_subject = str_replace('{country_name}', $country_name, $email_subject);
                                        $email_bottom = $emails->{'content_bottom_' . 'az'};
                                        $email_content = $emails->{'content_' . 'az'};

										$email_push_content = $emails->{'push_content_' . $lang};
										$email_push_content = str_replace('{tracking_number}', $track, $email_push_content);

                                        $email_content = str_replace('{client_name}', $client, $email_content);
                                        $email_content = str_replace('{seller}', $store_name, $email_content);
                                        $email_content = str_replace('{tracking}', $track, $email_content);
                                        $email_content = str_replace('{country}', $country_name, $email_content);
                                        $email_content = str_replace('{internal}', $internal, $email_content);

                                        $content_not_declared_notification = isset($email_push_content) ? $email_content : $email_push_content;
										$this->notification->sendNotification($email_title, $email_subject, $content_not_declared_notification, $client_id);

                                        $job = (new CollectorInWarehouseJob($email_to, $email_title, $email_subject, $email_content, $email_bottom))
                                            ->delay(Carbon::now()->addSeconds(10));
                                        dispatch($job);
                                    }
                                }
                            }
                        }
                            break;
                        case 41:
                        {
                            if ($first_scan) {
                                if ($invoice_status == 1) {
                                    $email = EmailListContent::where([
                                        'type' => 'no_invoice_legal_entity'
                                    ])->first();

                                    if ($email) {
                                        $country = Countries::where('id', $country_id)
                                            ->select('name_' . $lang . ' as name')
                                            ->first();
                                        if ($country) {
                                            $countryName = $country->name;
                                        } else {
                                            $countryName = '---';
                                        }
                                        $storeName = $request->seller;
                                        $tracking = $tracking_number;
                                        $clientName = $client_inform->name . ' ' . $client_inform->surname;
                                        $mailTo = $client_inform->email;
                                        $mailTitle = $email->{'title_az'};
                                        $mailSubject = $email->{'subject_az'};
                                        $mailSubject = str_replace('{country_name}', $countryName, $mailSubject);
                                        $mailBottom = $email->{'content_bottom_az'};
                                        $mailContent = $email->{'content_az'};

										$email_push_content = $email->{'push_content_' . $lang};
										$email_push_content = str_replace('{tracking_number}', $tracking, $email_push_content);

                                        $mailContent = str_replace('{seller}', $storeName, $mailContent);
                                        $mailContent = str_replace('{client_name}', $clientName, $mailContent);
                                        $mailContent = str_replace('{country_name}', $countryName, $mailContent);
                                        $mailContent = str_replace('{tracking}', $tracking, $mailContent);

                                        $content_no_invoice_legal_entity = empty($email_push_content) ? $mailContent : $email_push_content;

                                        $this->notification->sendNotification($mailTitle, $mailSubject, $content_no_invoice_legal_entity, $client_id);

                                        $job = (new CollectorInWarehouseJob($mailTo, $mailTitle, $mailSubject, $mailContent, $mailBottom))
                                            ->delay(Carbon::now()->addSeconds(10));
                                        dispatch($job);
                                    }
                                }
                            }
                        }
                        break;
					}
					switch ($invoice_status)
                    {
                        case 2:
                            {
                                if ($packageInfo->item->invoice_status != 2) {

                                    // incorrect invoice
                                    $emails = EmailListContent::where(['type' => 'incorrect_invoice'])->first();

                                    if ($emails) {
                                        $country_check = Countries::where('id', $country_id)->select('name_' . $lang . ' as name')->first();
                                        if ($country_check) {
                                            $country_name = $country_check->name;
                                        } else {
                                            $country_name = '---';
                                        }
                                        $store_name = $request->seller;
                                        $track = $tracking_number;
                                        $client = $client_inform->name . ' ' . $client_inform->surname;

                                        $email_to = $client_inform->email;
                                        $email_title = $emails->{'title_' . $lang}; //from
                                        $email_subject = $emails->{'subject_' . $lang};
                                        $email_bottom = $emails->{'content_bottom_' . $lang};
                                        $email_button = $emails->{'button_name_' . $lang};
                                        $email_content = $emails->{'content_' . $lang};

                                        $email_content = str_replace('{name_surname}', $client, $email_content);

										$email_push_content = $email->{'push_content_' . $lang};
										$email_push_content = str_replace('{tracking_number}', $tracking, $email_push_content);

                                        $email_content = str_replace('{store_name}', $store_name, $email_content);
                                        $email_content = str_replace('{tracking_number}', $track, $email_content);
                                        $email_content = str_replace('{country_name}', $country_name, $email_content);

										$this->notification->sendNotification($email_title, $email_subject, $email_push_content, $client_id);

                                        $job = (new CollectorInWarehouseJob($email_to, $email_title, $email_subject, $email_content, $email_bottom, $email_button))
                                            ->delay(Carbon::now()->addSeconds(10));
                                        dispatch($job);
                                    }
                                }
                            }
                            break;
                    }
				}
			}

		


			if($first_scan){
				$sellerName = $seller_id;
				$sms = new SMS();
				$date = Carbon::now();
				$phone_arr_az = array();
				$phone_arr_en = array();
				$phone_arr_ru = array();
				$text = '';
				$client_id_for_sms = 0;

				$email = EmailListContent::where(['type' => 'canada_shop'])->first();

				if($sellerName === 1338){
					$package = Package::with('client')->where('id', $package_id)->first();

					if ($package->client_id != 0 && $package->client_id != null && $package->client != null
						&& $package->client->phone1 != null
					) {
						if ($package->client_id != $client_id_for_sms) {
							// new client
							$language_for_sms = strtoupper($package->client->language);
							switch ($language_for_sms) {
								case 'AZ':
									{
										array_push($phone_arr_az,  $package->client->phone1);
									}
									break;
								case 'EN':
									{
										array_push($phone_arr_en,  $package->client->phone1);
									}
									break;
								case 'RU':
									{
										array_push($phone_arr_ru, $package->client->phone1);
									}
									break;
							}
	
							$client_id_for_sms = $package->client_id;
						}
					}

					if ($package->client) {
						$text = $email->sms_az;

						$control_id = time() . 'az';
						$phone_arr_az = array_unique($phone_arr_az);
						$send_bulk_sms = $sms->sendBulkSms($text, $phone_arr_az, $control_id);
		

						if ($send_bulk_sms[0] == true) {
							$response = simplexml_load_string($send_bulk_sms[1], 'SimpleXMLElement', LIBXML_NOCDATA);
							$json = json_decode(json_encode((array)$response), TRUE);
							if (isset($json['head']['responsecode'])) {
								$response_code = $json['head']['responsecode'];
							} else {
								$response_code = 'error';
							}
							if (isset($json['body']['taskid'])) {
								$task_id = $json['body']['taskid'];
							} else {
								$task_id = 'error';
							}
		
							if ($response_code == '000') {
								//success
								$sms_status = 1;
							} else {
								//failed
								$sms_status = 0;
							}
		
							$package_arr_for_sms = array();
	
							array_push($package_arr_for_sms, $package->id);
	
							SmsTask::create([
								'type' => 'canada_shop',
								'code' => $response_code,
								'task_id' => $task_id,
								'control_id' => $control_id,
								'package_id' => $package->id,
								'client_id' => $package->client_id,
								'number' => $package->client->phone1,
								'message' => $text,
								'created_by' => Auth::id()
							]);
							
						}
					}

					 // send sms en
					if (count($phone_arr_en) > 0) {
						$text = $email->sms_en;
		
						$control_id = time() . 'en';
						$phone_arr_en = array_unique($phone_arr_en);
						$send_bulk_sms = $sms->sendBulkSms($text, $phone_arr_en, $control_id);
		
						if ($send_bulk_sms[0] == true) {
							$response = simplexml_load_string($send_bulk_sms[1], 'SimpleXMLElement', LIBXML_NOCDATA);
							$json = json_decode(json_encode((array)$response), TRUE);
		
							if (isset($json['head']['responsecode'])) {
								$response_code = $json['head']['responsecode'];
							} else {
								$response_code = 'error';
							}
		
							if (isset($json['body']['taskid'])) {
								$task_id = $json['body']['taskid'];
							} else {
								$task_id = 'error';
							}
		
							if ($response_code == '000') {
								//success
								$sms_status = 1;
							} else {
								//failed
								$sms_status = 0;
							}
		
							$package_arr_for_sms = array();
							
							
		
							array_push($package_arr_for_sms, $package->id);
	
							SmsTask::create([
								'type' => 'canada_shop',
								'code' => $response_code,
								'task_id' => $task_id,
								'control_id' => $control_id,
								'package_id' => $package->id,
								'client_id' => $package->client_id,
								'number' => $package->client->phone1,
								'message' => $text,
								'created_by' => Auth::id()
							]);
							
						}
					}
		
					// send sms ru
					if (count($phone_arr_ru) > 0) {
						$text = $email->sms_ru;
		
						$control_id = time() . 'ru';
						$phone_arr_ru = array_unique($phone_arr_ru);
						$send_bulk_sms = $sms->sendBulkSms($text, $phone_arr_ru, $control_id);
		
						if ($send_bulk_sms[0] == true) {
							$response = simplexml_load_string($send_bulk_sms[1], 'SimpleXMLElement', LIBXML_NOCDATA);
							$json = json_decode(json_encode((array)$response), TRUE);
		
							if (isset($json['head']['responsecode'])) {
								$response_code = $json['head']['responsecode'];
							} else {
								$response_code = 'error';
							}
		
							if (isset($json['body']['taskid'])) {
								$task_id = $json['body']['taskid'];
							} else {
								$task_id = 'error';
							}
							if ($response_code == '000') {
								//success
								$sms_status = 1;
							} else {
								//failed
								$sms_status = 0;
							}
		
							$package_arr_for_sms = array();
		
							array_push($package_arr_for_sms, $package->id);
	
							SmsTask::create([
								'type' => 'canada_shop',
								'code' => $response_code,
								'task_id' => $task_id,
								'control_id' => $control_id,
								'package_id' => $package->id,
								'client_id' => $package->client_id,
								'number' => $package->client->phone1,
								'message' => $text,
								'created_by' => Auth::id()
							]);
							
						}
					}
				}

				$check_is_ok_custom = $amount != 0 && $amount !=null && $amount_usd != 0 && $amount_usd != null;
                $isOkCustom = 0;
                $paid = 0;
                $paid_status = 0;
                if($check_is_ok_custom){
                    $isOkCustom = 1;
                }else{
                    $isOkCustom = 0;
                }
                if($sellerName === 3426){
                    $paid = $amount;
                    $paid_status = 1;
                }

					Package::where('id', $package_id)->update([
						'is_ok_custom' => $isOkCustom,
                        'paid' => $paid,
                        'paid_status' => $paid_status
					]);


			}

			if(!$first_scan){
				if($packageInfo->last_status_id == 14 || $packageInfo->in_baku == 1){
					return response([
						'case' => 'error',
						'title' => 'Ooops',
						'type' => 'error',
						'content' => 'Cannot change to package flight or container. Because, the package is either on the way or in Baku'
					]);
				}
			}


			if(!$first_scan){

				$invoice = $request->invoice;
				$hash = $packageInfo->hash;
	
				if($hash != null){
					if($invoice > 0 &&  $invoice_status == 1){
						return response([
							'case' => 'error',
							'title' => 'Ooops',
							'type' => 'error',
							'content' => 'This is API package. If the invoice is greater than 0, the invoice status, cannot be no invoice. Please change invoice status to invoice available'
						]);
					}elseif($invoice == 0){
						return response([
							'case' => 'error',
							'title' => 'Ooops',
							'type' => 'error',
							'content' => 'This is API package. Invoice price cannot be 0'
						]);
					}
					
				}
			}
   

			DB::commit();

			return response(['case' => 'success', 'title' => 'Success!', 'type' => 'success', 'content' => 'Success!', 'amount_response' => $amount_response, 'internal_id' => $internal_id, 'flight_details' => $flight_details, 'has_container_details' => $has_container_details, 'container_details' => $container_details_arr]);
		} catch (QueryException $exception) {
		    DB::rollBack();
		    $errorCode = $exception->errorInfo[1];
            	if ($errorCode == 1062) {
                	return response([
                    'case' => 'error',
                    'title' => 'Error!',
                    'type' => 'error',
                    'content' => 'Sorry, the ASR tracking ID was generated by another collector till you save. Please refresh page'
                ]);
            }
        } catch (\Exception $exception) {
			dd($exception);
		    DB::rollBack();
		    Log::error('collector_add_package_fail', [
		        'request' => $request->all(),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'client_id' => isset($client_id) ? $client_id : 0,
                'user_id' => Auth::id()
            ]);
			return response(['case' => 'error', 'title' => 'Error!', 'type' => 'error', 'content' => 'Sorry, something went wrong!']);
		}
	}

    //	private function create_internal_id()
    //	{
    //		try {
    //			$settings = Settings::where('id', 1)->select('last_internal_id')->first();
    //			$last_internal_id = $settings->last_internal_id + 1;
    //			Settings::where('id', 1)->update(['last_internal_id' => $last_internal_id]);
    //			$len = strlen($last_internal_id);
    //			if ($len < 6) {
    //				for ($i = 0; $i < 6 - $len; $i++) {
    //					$last_internal_id = '0' . $last_internal_id;
    //				}
    //			}
    //
    //			$internal_id = 'ASR' . $last_internal_id;
    //
    //			return $internal_id;
    //		} catch (\Exception $exception) {
    //			return false;
    //		}
    //	}
    
    	private function create_internal_id()
    	{
    		try {
                $microtime = substr(microtime(), 2, 8);
            
                $internal_id = 'ASR' . $microtime;
                
    			return $internal_id;
    		} catch (\Exception $exception) {
    			return false;
    		}
    	}

	public function get_internal_id()
	{
		try {
			$internal_id = $this->create_internal_id();

			if (!$internal_id) {
				return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong when created internal id!']);
			}

			return response(['case' => 'success', 'title' => 'Success!', 'internal_id' => $internal_id]);
		} catch (\Exception $exception) {
			return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
		}
	}

	private function add_package($array, $user_id, $status_id = 0, $tracking_internal_same = 0)
	{
		try {
		    $carrierRegistrationNumber = null;
			$package_exist = Package::whereRaw("(package.number = '" . $array['number'] . "' or package.internal_id = '" . $array['number'] . "')")
					->whereNull('deleted_by')
					->orderBy('id', 'desc')
					->select('id', 'internal_id', 'collected_at', 'gross_weight', 'delivered_by', 'last_status_id', 'is_warehouse', 'seller_id', 'collected_at', 'client_id', 'carrier_status_id', 'carrier_registration_number')
                    ->with('status')
					->first();

			if ($package_exist) {
			    $carrierRegistrationNumber = $package_exist->carrier_registration_number;
			    $carrierStatusId = $package_exist->carrier_status_id;

				//update
				if ($package_exist->delivered_by != null) {
					return false;
				}

				if ($package_exist->collected_at == null) {
					//collected
					$array['collected_by'] = $user_id;
					$array['collected_at'] = Carbon::now();
				}

				$old_last_status_id = $package_exist->last_status_id;
				$old_client_id = $package_exist->client_id;

				if ($package_exist->is_warehouse == 0) {
					// first scan
					$first_scan = true;
					$array['is_warehouse'] = 1;
				} else {
					// update scan
					$first_scan = false;
				}

				if ($package_exist->internal_id == null) {
					if ($tracking_internal_same == 1) {
						$internal_id = $array['number'];
					} else {
						$internal_id = $this->create_internal_id();

						if (!$internal_id) {
							return false;
						}
					}

					$array['internal_id'] = $internal_id;
				} else {
					$internal_id = $package_exist->internal_id;
				}

				$array['updated_by'] = $user_id;

				if ($package_exist->seller_id == 0 && $array['seller_id'] == null) {
					$array['seller_id'] = 0;
				}

				Package::where('id', $package_exist->id)->whereNull('deleted_by')->update($array);
				$package_id = $package_exist->id;

				if ($package_exist->last_status_id != $status_id) {
					PackageStatus::create([
							'package_id' => $package_id,
							'status_id' => $status_id,
							'created_by' => $user_id
					]);
				}
				if ($old_client_id !== null && $old_client_id != $array['client_id']) {
                    Package::where('id', $package_id)
                        ->whereNotIn('carrier_status_id', [1, 2, 3, 7, 8])
                        ->update([
                            'client_id' => $array['client_id'],
                            'carrier_status_id' => 9
                        ]);
					ChangeAccountLog::create([
							'old_client_id' => $old_client_id,
							'new_client_id' => $array['client_id'],
							'package_id' => $package_id,
							'created_by' => Auth::id()
					]);
				}
            } else {
				//create
				$old_last_status_id = 0;

				$first_scan = true;
				$array['is_warehouse'] = 1;

				if ($tracking_internal_same == 1) {
					$internal_id = $array['number'];
				} else {
					$internal_id = $this->create_internal_id();

					if (!$internal_id) {
						return false;
					}
				}

				$array['internal_id'] = $internal_id;
				$array['created_by'] = $user_id;

				//collected
				$array['collected_by'] = $user_id;
				$array['collected_at'] = Carbon::now();

				$package = Package::create($array);

				$package_id = $package->id;
				$carrierStatusId = 0;

				PackageStatus::create([
						'package_id' => $package_id,
						'status_id' => $status_id,
						'created_by' => $user_id
				]);
			}

			return [$package_id, $internal_id, $first_scan, $old_last_status_id, $carrierRegistrationNumber, $carrierStatusId];
		} catch (\Exception $exception) {
			return false;
		}
	}

	private function add_item($array, $user_id, $status_id = 0)
	{
		try {
			$item_id = 0;
			$item_exist = Item::where('package_id', $array['package_id'])->whereNull('deleted_by')->orderBy('id', 'desc')->select('id', 'invoice_doc')->first();
			// dd($array);
			
			if ($item_exist) {
				//update
				$array['updated_by'] = $user_id;

				if ($status_id == 5) {
					$array['invoice_confirmed'] = 1; // invoice is okay
				} else if ($status_id == 9 || $status_id == 6) {
					$array['invoice_confirmed'] = 0; // incorrect invoice or no invoice
				}

				$item = Item::where('id', $item_exist->id)->whereNull('deleted_by')->update($array);
				$item_id = $item_exist->id;
			} else {
				//create
				$array['created_by'] = $user_id;
				$item = Item::create($array);
				$item_id = $item->id;
			}

			return $item_id;
		} catch (\Exception $exception) {
			return false;
		}
	}

	private function add_tracking_log($array, $user_id)
	{
		try {
			$array['created_by'] = $user_id;

			$tracking = TrackingLog::create($array);

			return $tracking->id;
		} catch (\Exception $exception) {
			return false;
		}
	}

	private function package_client_log($package_id, $user_id, $client_id)
	{
		try {
			
			$pack = Package::where('id', $package_id)->first()->client_id;
			$log_check = ChangeAccountLog::where('package_id', $package_id)
			->get();

			// dd($pack);
			if($log_check->count() == 0){
				$log = ChangeAccountLog::create([
					'package_id' => $package_id,
					'created_by' => $user_id,
					'new_client_id' => $client_id,
					'old_client_id' => $pack
				]);
			}else{
				foreach($log_check as $log){
					$client_ids[] = $log->new_client_id;
				}
				
				$last_array = end($client_ids);
			
				if($last_array != $client_id){
					$log = ChangeAccountLog::create([
						'package_id' => $package_id,
						'created_by' => $user_id,
						'new_client_id' => $client_id,
						'old_client_id' => $pack
					]);
				}

			}

			return $log->id;
		} catch (\Exception $exception) {
			return false;
		}
	}

	
	public function show_images(Request $request)
	{
		$validator = Validator::make($request->all(), [
				'package' => ['required', 'integer'],
		]);
		if ($validator->fails()) {
			return response(['case' => 'warning', 'title' => 'Warning!', 'content' => "Package not found!"]);
		}
		try {
			$package_id = $request->package;

			$images = PackageFiles::where(['package_id' => $package_id])
					->whereNull('deleted_by')
					->select(
							'id',
							'domain',
							'url',
							'name',
							'type'
					)
					->get();

			return response(['case' => 'success', 'images' => $images]);
		} catch (\Exception $exception) {
			return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
		}
	}

	public function delete_image(Request $request)
	{
		$validator = Validator::make($request->all(), [
				'image' => ['required', 'integer'],
		]);
		if ($validator->fails()) {
			return response(['case' => 'warning', 'title' => 'Warning!', 'content' => "Image not found!"]);
		}
		try {
			$id = $request->image;

			$image = PackageFiles::where(['id' => $id, 'by_client' => 0])->select('url')->first();

			if (!$image) {
				return response(['case' => 'warning', 'title' => 'Warning!', 'content' => "Image not found!"]);
			}

			PackageFiles::where('id', $id)
					->update([
							'deleted_by' => Auth::id(),
							'deleted_at' => Carbon::now()
					]);

			$path = public_path() . $image->url;

			if (File::exists($path)) {
				File::delete($path);
			}

			return response(['case' => 'success']);
		} catch (\Exception $exception) {
			return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
		}
	}

	private function packages_price_for_last_month($client_id)
	{
		try {
			$date = Carbon::now();

			$rates = ExchangeRate::whereDate('from_date', '<=', $date)->whereDate('to_date', '>=', $date)
					->where('to_currency_id', 1) // to USD
					->select('rate', 'from_currency_id', 'to_currency_id')
					->get();

			$has_rate = true;
			if (count($rates) == 0) {
				$has_rate = false;
			}

			$last_month_date = Carbon::today()->subDays(30)->toDateString();

			$packages_price_for_last_month = Item::leftJoin('package as p', 'item.package_id', '=', 'p.id')
					->whereNull('p.deleted_by')
					->whereNotNull('p.container_date')
					->where('p.client_id', $client_id)
					->whereDate('p.container_date', '>=', $last_month_date)
					->select('item.price', 'item.currency_id as price_currency', 'p.total_charge_value as amount', 'p.currency_id as amount_currency')
					->get();

			$price_for_last_month = 0;
			$amounts_for_last_month = 0;
			foreach ($packages_price_for_last_month as $package) {
				$price_currency = $package->price_currency;
				$amount_currency = $package->amount_currency;

				if ($has_rate) {
					$price_rate_to_usd = $this->calculate_exchange_rate($rates, $price_currency, 1);
					$amount_rate_to_usd = $this->calculate_exchange_rate($rates, $amount_currency, 1);
				} else {
					$price_rate_to_usd = 1;
					$amount_rate_to_usd = 1;
				}

				$price_usd = $package->price * $price_rate_to_usd;
				$price_usd = sprintf('%0.2f', $price_usd);

				$price_for_last_month += $price_usd;

				$amount_usd = $package->amount * $amount_rate_to_usd;
				$amount_usd = sprintf('%0.2f', $amount_usd);

				$amounts_for_last_month += $amount_usd;
			}

			return sprintf('%0.2f', $price_for_last_month + $amounts_for_last_month);
		} catch (\Exception $exception) {
			return 0;
		}
	}

	private function calculate_exchange_rate($rates, $from, $to)
	{
		try {
			if ($from == $to) {
				return 1;
			}

			foreach ($rates as $rate) {
				if ($rate->from_currency_id == $from && $rate->to_currency_id == $to) {
					return $rate->rate;
				}
			}

			return 0;
		} catch (\Exception $exception) {
			return 0;
		}
	}

	public function foreign_courier_companies()
	{
		return view('backend.collector_foreign_couriers');
	}

	public function add_container_page()
    {
        try {
            $flights = Flight::whereNull('flight.deleted_by')
                ->whereNull('flight.status_in_baku_date')
                ->orderBy('flight.id', 'desc')
                ->take(100)
                ->select('flight.id', 'flight.name')
                ->get();

            return view('backend.collector.distributor', compact('flights'));
        } catch (\Exception $exception) {
			//dd($exception);
            return view('backend.error');
        }
    }

    public function change_position(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'track' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:100'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $user_id = Auth::id();
            $user_location_id = Auth::user()->location();

            $position_no = $request->position;
            $package_number = $request->track;

            $legal_entity = Package::whereRaw("(number = '" . $package_number . "' or internal_id = '" . $package_number . "')")
                ->leftJoin('item', 'package.id', 'item.package_id')
                ->whereNull('package.deleted_by')
                ->whereIn('package.last_status_id', [41, 5])
                //->where('package.carrier_status_id', 0)
                ->where('package.customer_type_id', 2)
                ->whereIn('item.invoice_status', [3,4])
                ->where('package.departure_id', $user_location_id)
                ->orderBy('package.id', 'desc')
                ->select('package.id', 'package.number', 'package.container_id', 'package.position_id','package.last_status_id','package.carrier_status_id')
                ->first();
            $package = null;
            //dd($legal_entity);
            if(!$legal_entity){
                if (substr($package_number, 0, 8) == '42019801') {
                    $package_number_search = substr($package_number, -22);
                    $package = Package::whereRaw("(number = '" . $package_number_search . "' or internal_id = '" . $package_number_search . "')")
                        ->whereNull('deleted_by')
                        ->whereIn('last_status_id', [39, 40, 5])
                        ->whereIn('carrier_status_id', [1, 2, 7])
                        ->where('departure_id', $user_location_id)
                        ->orderBy('id', 'desc')
                        ->select('id', 'number', 'container_id', 'position_id','last_status_id','carrier_status_id')
                        ->first();

                    if (!$package) {
                        $package_number_search = substr($package_number, 10, strlen($package_number) - 1);
                        $package = Package::whereRaw("(number = '" . $package_number_search . "' or internal_id = '" . $package_number_search . "')")
                            ->whereNull('deleted_by')
                            ->whereIn('last_status_id', [39, 40, 5])
                            ->whereIn('carrier_status_id', [1, 2, 7])
                            ->where('departure_id', $user_location_id)
                            ->orderBy('id', 'desc')
                            ->select('id', 'number', 'container_id', 'position_id','last_status_id','carrier_status_id')
                            ->first();
                    }
                }
                else {
                    $package_number_search = $package_number;
                    $package = Package::whereRaw("(number = '" . $package_number_search . "' or internal_id = '" . $package_number_search . "')")
                        ->whereNull('deleted_by')
                        ->whereIn('last_status_id', [39, 40, 5])
                        ->whereIn('carrier_status_id', [1, 2, 7])
                        ->where('departure_id', $user_location_id)
                        ->orderBy('id', 'desc')
                        ->select('id', 'number', 'container_id', 'position_id','last_status_id','carrier_status_id')
                        ->first();

                }
            }


	
            if (!$package && !$legal_entity) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Package not found or not declared!']);
            }

            $package_id = $package != null ? $package->id : $legal_entity->id;
            $package_number_response = $package != null ? $package->number : $legal_entity->number;
			
	
            $position = DB::table('container')->leftJoin('flight', 'container.flight_id', '=', 'flight.id')
				->where('container.id', $position_no)
				->whereNull('flight.deleted_by')
				->whereNull('flight.closed_at')
				->where('location_id', $user_location_id)
                ->select('container.id')
                ->first();

            if (!$position) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Container is not found or flight closed!']);
            }

            $position_id = $position->id;


			$res = Package::where('id', $package_id)->update(['last_status_id' => 5, 'last_status_date' => Carbon::now(),'container_id'=>$position_id, 'last_container_id'=>$position_id, 'container_date'=>Carbon::now(), 'position_id'=> null]);


            PackageStatus::create([
                'package_id' => $package_id,
                'status_id' => 5,
                'created_by' => Auth::id()
            ]);



            return response(['case' => 'success', 'change' => true, 'content' => 'Position is changed! ' . $package_id, 'track' => $package_number_response]);
        } catch (\Exception $exception) {
            //dd($exception);
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Something went wrong!']);
        }
    }

    private function CalculateToAzn($fromCurrency, $toCurrency, $amount)
    {
        try {

            $date = Carbon::today();
            $rate_for_amount = ExchangeRate::whereDate('from_date', '<=', $date)
                ->whereDate('to_date', '>=', $date)
                ->where(['from_currency_id' => $fromCurrency, 'to_currency_id' => $toCurrency]) //to azn
                ->select('rate')
                ->orderBy('id', 'desc')
                ->first();


            if ($rate_for_amount) {
                $amount_azn = $rate_for_amount->rate * $amount;
                $amount_azn_rounded = ceil($amount_azn * 100) / 100;
            } else {
                return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'warning', 'content' => 'Exchange rate not found (Amount USD)!']);
            }
            return $amount_azn_rounded;
        } catch (\Exception $exception) {
            //dd($exception);
            return 0;
        }

    }

}

