<?php

namespace App\Http\Controllers\Api;

use App\BalanceLog;
use App\CourierOrders;
use App\EmailListContent;
use App\ExchangeRate;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\Classes\Functions;
use App\Jobs\CollectorInWarehouseJob;
use App\Location;
use App\Package;
use App\PaymentLog;
use App\Queue;
use App\Receipts;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class QueueController extends ApiController
{
 
	public function get_user_details()
	{
		try {
			$user = Input::get("passport");
			$client_role = 2;

			$functions = new Functions();
			if ($functions->validate_for_api([$user])) {
				return response([
						'status' => Response::HTTP_NOT_FOUND,
						'type' => 'validate',
						'message' => 'Fill all inputs!'
				], Response::HTTP_NOT_FOUND);
			}
   
			$where_user = array();
            if (strtoupper(substr($user, 0, 2)) === 'AS') {
                $suite = (int)substr($user, 2);
                $where_user['id'] = $suite;
            }
            else  if (strpos(strtoupper($user), 'AS') === 0) {

                $suite = (int)substr($user, 2);
                $where_user['id'] = $suite;
            }
            else {
				// passport
				$where_user['passport_number'] = $user;
			}

			$user = User::where(['role_id' => $client_role])
					->where($where_user)
					->whereNull('deleted_by')
					->orderBy('id', 'desc')
					->select(
							'id',
							'suite',
							'passport_fin',
							'name',
							'surname',
							'email',
							'address1',
							'address2',
							'address3',
							'zip1',
							'zip2',
							'zip3',
							'phone1',
							'phone2',
							'phone3',
							'language',
							'balance'
					)
					->first();

			if (!$user) {
				return response([
						'status' => Response::HTTP_NOT_FOUND,
						'message' => 'Client not found!'
				], Response::HTTP_NOT_FOUND);
			}

			Log::info('get_user_details: ' . 'User id: ' . $user);
			
			return response([
					'status' => Response::HTTP_OK,
					'data' => $user
			], Response::HTTP_OK);
		} catch (\Exception $exception) {
			return response([
					'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
					'type' => 'Error',
					'message' => 'Sorry, An error occurred...'
			], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}

	public function get_user_packages()
	{
		try {
			$user = Input::get("user");
			$location = Input::get("location");
			$branch = Input::get("office");
            
            if (strtoupper(substr($user, 0, 2)) === 'AS') {
                $suite = (int)substr($user, 2);
                $where_user['id'] = $suite;
            }
       
			$functions = new Functions();
			if ($functions->validate_for_api([$user, $location])) {
				return response([
						'status' => Response::HTTP_NOT_FOUND,
						'type' => 'validate',
						'message' => 'Fill all inputs!'
				], Response::HTTP_NOT_FOUND);
			}

			$users = array();
			$users[] = $where_user;

			$sub_accounts = User::where('parent_id', $where_user)->whereNull('deleted_by')
					->select('id')->get();

			foreach ($sub_accounts as $sub_account) {
				$users[] = $sub_account->id;
			}

			$packages = Package::leftJoin('lb_status as s', 'package.last_status_id', '=', 's.id')
					//->leftJoin('currency as c', 'package.currency_id', '=', 'c.id')
					->leftJoin('position as p', 'package.position_id', '=', 'p.id')
					->leftJoin('users as client', 'package.client_id', '=', 'client.id')
					->whereIn('package.client_id', $users)
					->where('p.location_id', $location)
					->where('package.in_baku', 1)
					->where('package.branch_id', $branch)
					->whereRaw('(package.is_warehouse = 3 or package.customs_date is not null)')
					->whereNull('package.deleted_by')
					->whereNull('package.delivered_by')
					->orderBy('client.parent_id')
					->orderBy('package.position_id')
					->orderBy('package.id')
					->select(
							'package.id',
							'package.number',
							'package.internal_id',
							'package.total_charge_value as amount',
							'package.paid',
							'package.paid_status as is_paid',
							//'c.name as currency',
							'package.currency_id',
							'package.last_status_id as status_id',
							's.status_az as status',
							's.color as status_color',
							'client.id as suite',
							'client.name as client_name',
							'client.surname as client_surname',
							'package.issued_to_courier_date'
					)
					->get();

			$date = Carbon::today();
			$rates = ExchangeRate::whereDate('from_date', '<=', $date)->whereDate('to_date', '>=', $date)
					->select('rate', 'from_currency_id', 'to_currency_id')
					->get();
			if (!$rates) {
				// rate note found
				return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Rate not found!']);
			}

			foreach ($packages as $package) {
				$currency_id = $package->currency_id;
				$rate_to_usd = $this->calculate_exchange_rate($rates, $currency_id, 1);
				$package->amount = sprintf('%0.2f', $package->amount * $rate_to_usd);
				$package->paid = sprintf('%0.2f', $package->paid * $rate_to_usd);
				$package->currency = 'USD';

				if ($package->issued_to_courier_date == null) {
					$package->has_courier = 0;
				} else {
					$package->has_courier = 1;
				}

				unset($package->issued_to_courier_date);
			}

			Log::info('get_user_packages: ' . 'User id: ' . $user . ' location :' . $location . ' packages ' . $packages);

			return response([
					'status' => Response::HTTP_OK,
					'data' => $packages
			], Response::HTTP_OK);
		} catch (\Exception $exception) {
			return response([
					'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
					'type' => 'Error',
					'message' => 'Sorry, An error occurred...'
			], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}

	public function get_queue()
	{
		try {
			$user = Input::get("user");
			$type = Input::get("type");
			$location = Input::get("location");

			return $this->create_queue($user, $type, $location);
		} catch (\Exception $exception) {
			return response([
					'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
					'type' => 'Error',
					'message' => 'Sorry, An error occurred...'
			], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}

	public function create_queue($passport, $type, $location, $user_type = 'passport')
	{
		try {
			$no = 0;
			$waiting = 0;
			$last_no = 0;
			$last_used = 0;
			$validate_arr = array();
			$user_control = true;

			$today = Carbon::today()->toDateString();

			array_push($validate_arr, $type);
			array_push($validate_arr, $location);
			if ($type != 'i' && $type != 'o') {
				array_push($validate_arr, $passport);
				$user_control = true;
			} else {
				$user_control = false;
			}

			$functions = new Functions();
			if ($functions->validate_for_api($validate_arr)) {
				return response([
						'status' => Response::HTTP_NOT_FOUND,
						'type' => 'validate',
						'message' => 'Fill all inputs!'
				], Response::HTTP_NOT_FOUND);
			}

			$location_query = Location::where('id', $location)->select('name')->first();
			if (!$location_query) {
				return response([
						'status' => Response::HTTP_NOT_FOUND,
						'type' => 'warning',
						'message' => 'Location not found!'
				], Response::HTTP_NOT_FOUND);
			}
			$location_name = $location_query->name;

			if ($user_control) {
				if ($user_type === 'passport') {
					$where_user = array();
                    if (strtoupper(substr($passport, 0, 2)) === 'AS') {
                        $suite = (int)substr($passport, 2);
                        $where_user['id'] = $suite;
                    } else {
						// passport
						$where_user['passport_number'] = $passport;
					}

					$user_detail = User::where($where_user)->whereNull('deleted_by')->where('role_id', 2)
							->select('id')->orderBy('id', 'desc')->first();

					if (!$user_detail) {
						return response([
								'status' => Response::HTTP_NOT_FOUND,
								'type' => 'warning',
								'message' => 'Client not found!'
						], Response::HTTP_NOT_FOUND);
					}

					$user = $user_detail->id;
				} else {
					$user = $passport;
				}
			} else {
				$user = null;
			}

			$queue = Queue::whereDate('date', $today)->where('type', $type)->where('location_id', $location)->select('no', 'used')->orderBy('id', 'desc')->first();

			if ($queue) {
				$last_no = $queue->no;
				$last_used = $queue->used;
			} else {
				$last_no = 0;
				$last_used = 1;
			}

			if ($last_no == 999) {
				$last_no = 0;
			}

			$queue_name = '';
			switch ($type) {
				case 'c':
					{
						//cashier
						$queue_name = 'K';
					}
					break;
				case 'd':
					{
						//delivery
						$queue_name = 'A';
					}
					break;
				case 'o':
					{
						//online (for special orders)
						$queue_name = 'S';
					}
					break;
				default:
				{
					//information
					$queue_name = 'I';
				}
			}

			$no = $last_no + 1;

			if ($last_used == 1) {
				$waiting = 0;
			} else {
				$queue_for_waiting = Queue::whereDate('date', $today)->where('type', $type)->where('location_id', $location)->where('used', 1)->select('no')->orderBy('id', 'desc')->first();

				if ($queue_for_waiting) {
					$no_for_waiting = $queue_for_waiting->no + 1;
				} else {
					$no_for_waiting = 1;
				}

				$waiting = $no - $no_for_waiting;
			}

			Queue::create([
					'date' => $today,
					'type' => $type,
					'no' => $no,
					'user_id' => $user,
					'location_id' => $location,
					'used' => 0
			]);

			if ($no < 100) {
				if ($no < 10) {
					$no = '00' . $no;
				} else {
					$no = '0' . $no;
				}
			}
			// dd($queue);
			Log::info('create_queue: ' . $queue_name . $no . ' ' . $passport . ' ' . $type);

			return response([
					'status' => Response::HTTP_OK,
					'no' => $queue_name . $no,
					'waiting' => $waiting,
					'queue_type' => $type,
					'location' => $location_name,
					'date' => Carbon::now()->toDateTimeString()
			], Response::HTTP_OK);
		} catch (\Exception $exception) {
			return response([
					'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
					'type' => 'Error',
					'message' => 'Sorry, An error occurred...'
			], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}

	// pay from balance in queue
	public function pay_from_balance()
	{
		try {
			$tracks = Input::get("tracks");
			$client_id = Input::get("client");
			$date = Carbon::now()->toDateTimeString();
            $suite = 0;
            
			$functions = new Functions();
			if ($functions->validate_for_api([$tracks, $client_id])) {
				return response([
						'status' => Response::HTTP_NOT_FOUND,
						'type' => 'validate',
						'message' => 'Fill all inputs!'
				], Response::HTTP_NOT_FOUND);
			}
            
            if (strtoupper(substr($client_id, 0, 2)) === 'AS') {
                $suite = (int)substr($client_id, 2);
            }

			if ($tracks[strlen($tracks) - 1] == ',') {
				$tracks = substr($tracks, 0, -1);
			}

			$track_arr = explode(',', $tracks);

			
		for($j = 0; $j < count($track_arr); $j++) {
			$track_arr[$j] = 'ASR' . $track_arr[$j];
		}

			$client_code = $suite;
		
			$time = time();
			$payment_code = rand(1, 9) . $client_code . substr($time, 5);

			$clients_arr = array();
			array_push($clients_arr, $client_id);

			$referrals = User::where(['parent_id' => $client_id])->whereNull('deleted_by')->select('id')->get();
			if (count($referrals) > 0) {
				foreach ($referrals as $referral) {
					array_push($clients_arr, $referral->id);
				}
			}

			$packages = Package::whereIn('internal_id', $track_arr)
					->whereIn('client_id', $clients_arr)
					->where('in_baku', 1)
			//                ->where('package.is_warehouse', 3)
					->whereRaw('(is_warehouse = 3 or customs_date is not null)')
					->whereNull('issued_to_courier_date')
					//->where('client_id', $client_id)
					/*->select(
							'id',
							'total_charge_value as amount',
							'amount_usd',
							'amount_azn',
							'paid',
							'paid_sum',
							'paid_azn',
							'paid_status',
							'currency_id',
							'chargeable_weight',
							'gross_weight',
							'volume_weight',
							'number',
							'courier_order_id'
					)*/
					->get();
     
			if (count($packages) == 0) {
				return response([
						'status' => Response::HTTP_NOT_FOUND,
						'type' => 'warning',
						'message' => 'Packages not found!'
				], Response::HTTP_NOT_FOUND);
			}

			$date = Carbon::today();
			$rates = ExchangeRate::whereDate('from_date', '<=', $date)->whereDate('to_date', '>=', $date)
					->select('rate', 'from_currency_id', 'to_currency_id')
					->get();
			if (!$rates) {
				// rate note found
				return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Rate not found!']);
			}

			//$packages_id = array();
			$total_amount = 0; // usd
			$total_amount_azn = 0; // azn
			foreach ($packages as $package) {
				$paid_status = $package->paid_status;
				if ($paid_status == 1) {
					continue;
				}
				//$package_amount = $package->amount - $package->paid;
				//$rate_to_usd = $this->calculate_exchange_rate($rates, $package->currency_id, 1);
				//$package_amount = sprintf('%0.2f', $package_amount * $rate_to_usd);
                
                $result = $this->CalculatePaid($package);
                $package_amount = $result['pay_usd'];
                $package_amount_azn = $result['pay_azn'];

                
				$total_amount += $package_amount;
				$total_amount_azn += $package_amount_azn;
				//array_push($packages_id, $package->id);
			}

			$client = User::where('id', $client_id)->whereNull('deleted_by')->select('balance', 'name', 'surname', 'email', 'language')->first();

			if ($client) {
				$balance = $client->balance;
				if ($balance < $total_amount) {
					return response([
							'status' => Response::HTTP_PAYMENT_REQUIRED,
							'type' => 'warning',
							'message' => 'Your balance is insufficient!',
							'balance' => $balance,
							'payment' => $total_amount
					], Response::HTTP_PAYMENT_REQUIRED);
				}

                //dd($total_amount);
				$packages_arr = array();
				foreach ($packages as $package) {
					$paid_status = $package->paid_status;
					if ($paid_status == 1) {
						continue;
					}
                    
                    $resultCalc = $this->CalculatePaid($package);
                    $total_pay = $resultCalc['total_pay'];
                    $total_pay_usd = $resultCalc['total_pay_usd'];
                    $total_pay_azn = $resultCalc['total_pay_azn'];
                    $pay_currency = $resultCalc['pay'];
                    $pay_azn = $resultCalc['pay_azn'];
                    $pay_usd = $resultCalc['pay_usd'];
                    
					//$amount = $package->amount - $package->paid;
					$package_id = $package->id;

					Package::where('id', $package_id)->update([
							'paid' => $total_pay,
                            'paid_sum' => $total_pay_usd,
                            'paid_azn' => $total_pay_azn,
							'paid_status' => 1,
							'payment_type_id' => 1 // online
					]);

					PaymentLog::create([
							'payment' => $pay_currency,
							'currency_id' => $package->currency_id,
							'client_id' => $client_id,
							'package_id' => $package_id,
							'type' => 3, // balance
							'created_by' => $client_id
					]);

					// packages array for email
					$package_arr = array();
					//$pay = $package->amount - $package->paid;
					//$rate_to_azn = $this->calculate_exchange_rate($rates, $package->currency_id, 3);
					//$pay_azn = sprintf('%0.2f', $pay * $rate_to_azn);

					$package_arr['paid'] = $pay_azn;

					$weight_type = $package->chargeable_weight;
					if ($weight_type == 2) {
						// volume
						$weight = $package->volume_weihght;
					} else {
						// gross
						$weight = $package->gross_weight;
					}

					$package_arr['weight'] = $weight;
					$package_arr['tracking'] = $package->number;
					array_push($packages_arr, $package_arr);

					// courier order control
					if ($package->courier_order_id != null) {
						$courier_order_id = $package->courier_order_id;

						$courier_order = CourierOrders::where('id', $courier_order_id)
								->select('delivery_amount', 'total_amount')
								->first();

						if ($courier_order) {
							$old_delivery_amount = $courier_order->delivery_amount;
							$old_total_amount = $courier_order->total_amount;

							$new_delivery_amount = $old_delivery_amount - $pay_azn;
							if ($new_delivery_amount < 0) {
								$new_delivery_amount = 0;
							}

							$new_total_amount = $old_total_amount - $pay_azn;
							if ($new_total_amount < 0) {
								$new_total_amount = 0;
							}

							$courier_order_update_arr = array();
							$courier_order_update_arr['delivery_amount'] = $new_delivery_amount;
							$courier_order_update_arr['total_amount'] = $new_total_amount;

							if ($new_delivery_amount == 0) {
								$courier_order_update_arr['delivery_payment_type_id'] = 1; // online
							}

							CourierOrders::where('id', $courier_order_id)->update($courier_order_update_arr);
						}
					}
				}

				$your_balance = $balance - $total_amount;

				if ($your_balance < 0) {
					$your_balance = 0;
				}

				User::where('id', $client_id)->update([
						'balance' => $your_balance
				]);

				//$rate_usd_azn = $this->calculate_exchange_rate($rates, 1, 3);
				//$total_amount_azn = sprintf('%0.2f', $total_amount * $rate_usd_azn);

				BalanceLog::create([
						'payment_code' => $payment_code,
						'amount' => $total_amount,
						'amount_azn' => $total_amount_azn,
						'client_id' => $client_id,
						'status' => 'out',
						'type' => 'balance',
						'created_by' => $client_id
				]);

				$email = EmailListContent::where(['type' => 'paid_from_balance_cashier'])->first();

				if ($email) {
					$client_full_name = $client->name . ' ' . $client->surname;
					$email_to = $client->email;
					$lang = strtolower($client->language);

					$email_title = $email->{'title_' . $lang}; //from
					$email_subject = $email->{'subject_' . $lang};
					$email_bottom = $email->{'content_bottom_' . $lang};
					$email_content = $email->{'content_' . $lang};
					$email_list_inside = $email->{'list_inside_' . $lang};

					$email_content = str_replace('{name_surname}', $client_full_name, $email_content);

					$list_insides = '';

					for ($i = 0, $iMax = count($packages_arr); $i < $iMax; $i++) {
						$no = $i + 1;
						$package_for_email = $packages_arr[$i];

						$track = $package_for_email['tracking'];
						$weight = $package_for_email['weight'] . ' kg';
						$amount_for_email = $package_for_email['paid'] . ' AZN';

						$list_inside = $email_list_inside;

						$list_inside = str_replace('{no}', $no, $list_inside);
						$list_inside = str_replace('{tracking_number}', $track, $list_inside);
						$list_inside = str_replace('{weight}', $weight, $list_inside);
						$list_inside = str_replace('{amount}', $amount_for_email, $list_inside);

						$list_insides .= $list_inside;
					}

					$email_content = str_replace('{list_inside}', $list_insides, $email_content);

					$job = (new CollectorInWarehouseJob($email_to, $email_title, $email_subject, $email_content, $email_bottom))
							->delay(Carbon::now()->addSeconds(10));
					dispatch($job);
				}

				return response([
						'status' => Response::HTTP_OK,
						'message' => 'Success!',
						'balance' => $your_balance,
						'payment' => $total_amount,
						'code' => $payment_code,
						'date' => $date
				], Response::HTTP_OK);
			} else {
				return response([
						'status' => Response::HTTP_NOT_FOUND,
						'type' => 'warning',
						'message' => 'Client not found!'
				], Response::HTTP_NOT_FOUND);
			}
		} catch (\Exception $exception) {
            dd($exception);
			return response([
					'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
					'type' => 'Error',
					'message' => 'Sorry, An error occurred...'
			], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}

	//receipt for cashier
	public function create_payment_receipt()
	{
		try {
            $suite = Input::get("user");
			$tracks = Input::get("tracks");
			$location = Input::get("location");
			$date = Carbon::now()->toDateTimeString();

			$functions = new Functions();
			if ($functions->validate_for_api([$tracks, $suite, $location])) {
				return response([
						'status' => Response::HTTP_NOT_FOUND,
						'type' => 'validate',
						'message' => 'Fill all inputs!'
				], Response::HTTP_NOT_FOUND);
			}
            
            if (strtoupper(substr($suite, 0, 2)) === 'AS') {
                $user = (int)substr($suite, 2);
            }
            
            $tracks = str_replace('ğ', '%', $tracks);
			$tracks = str_replace('ş', '?', $tracks);
			$tracks = str_replace('ç', '&', $tracks);

			if ($tracks[strlen($tracks) - 1] == ',') {
				$tracks = substr($tracks, 0, -1);
			}

			$track_arr = explode(',', $tracks);

			for ($j = 0, $jMax = count($track_arr); $j < $jMax; $j++) {
				$track_arr[$j] = str_replace('ə', ',', $track_arr[$j]);
				$track_arr[$j] = 'ASR' . $track_arr[$j];
			}

			$time = time();
			$receipt = 'RC' . substr($time, -5) . rand(0, 9);

			while (Receipts::where('receipt', $receipt)->select('id')->first()) {
				$receipt = 'RC' . substr($time, -5) . rand(0, 9);
			}

			Receipts::create([
					'receipt' => $receipt,
					'created_by' => $user
			]);

			$packages = Package::leftJoin('position as p', 'package.position_id', '=', 'p.id')
					//->whereIn('package.number', $track_arr)
					->whereIn('package.internal_id', $track_arr)
					->where('package.in_baku', 1)
				//                ->where('package.is_warehouse', 3)
					->whereRaw('(package.is_warehouse = 3 or package.customs_date is not null)')
					->whereNull('package.issued_to_courier_date')
					->where('p.location_id', $location)
					->select('package.id', 'package.internal_id')
					->get();

			if (count($packages) == 0) {
				return response([
						'status' => Response::HTTP_NOT_FOUND,
						'type' => 'warning',
						'message' => 'Packages not found!'
				], Response::HTTP_NOT_FOUND);
			}

			$packages_arr = array();
			foreach ($packages as $package) {
				array_push($packages_arr, $package->id);
			}

			Package::whereIn('id', $packages_arr)
					->whereNull('issued_to_courier_date')
					->update(['payment_receipt' => $receipt, 'payment_receipt_date' => $date]);

			Log::info('create_payment_receipt: ' . 'receipt ' . $receipt . ' package: ' . $packages);

			return response([
					'status' => Response::HTTP_OK,
					'receipt' => $receipt,
					'date' => $date
			], Response::HTTP_OK);
		} catch (\Exception $exception) {
            //dd($exception);
			return response([
					'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
					'type' => 'Error',
					'message' => 'Sorry, An error occurred...'
			], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}

	//receipt for delivery
	public function packages_for_delivery()
	{
		try {
			$user = Input::get("user");
			$tracks = Input::get("tracks");
			$location = Input::get("location");
			$date = Carbon::now()->toDateTimeString();
            $branch = Input::get("office");

			$functions = new Functions();
			if ($functions->validate_for_api([$tracks, $location, $user])) {
				return response([
						'status' => Response::HTTP_NOT_FOUND,
						'type' => 'validate',
						'message' => 'Fill all inputs!'
				], Response::HTTP_NOT_FOUND);
			}

			$tracks = str_replace('ğ', '%', $tracks);
			$tracks = str_replace('ş', '?', $tracks);
			$tracks = str_replace('ç', '&', $tracks);

			if ($tracks[strlen($tracks) - 1] == ',') {
				$tracks = substr($tracks, 0, -1);
			}

			$track_arr = explode(',', $tracks);

			for ($j = 0, $jMax = count($track_arr); $j < $jMax; $j++) {
				$track_arr[$j] = str_replace('ə', ',', $track_arr[$j]);
				$track_arr[$j] = 'ASR' . $track_arr[$j];
			}
            
            if (strtoupper(substr($user, 0, 2)) === 'AS') {
                $suite = (int)substr($user, 2);
            }
            
			$time = time();
			$receipt = 'RC' . substr($time, -5) . rand(0, 9);

			while (Receipts::where('receipt', $receipt)->select('id')->first()) {
				$receipt = 'RC' . substr($time, -5) . rand(0, 9);
			}

			Receipts::create([
					'receipt' => $receipt,
					'created_by' => $suite
			]);

			$packages = Package::leftJoin('position as p', 'package.position_id', '=', 'p.id')
					->leftJoin('users as client', 'package.client_id', '=', 'client.id')
					//->whereIn('package.number', $track_arr)
					->whereIn('package.internal_id', $track_arr)
					->where('package.in_baku', 1)
                    ->where('package.branch_id', $branch)
				//                ->where('package.is_warehouse', 3)
					->whereRaw('(package.is_warehouse = 3 or package.customs_date is not null)')
					->whereNull('package.issued_to_courier_date')
					->where('p.location_id', $location)
					//->where('package.paid_status', 1)
					->select('package.id', 'package.number', 'p.name as position', 'package.client_id as suite', 'client.name as client_name', 'client.surname as client_surname', 'package.internal_id')
					->orderBy('client.id', 'desc')
					->orderBy('p.name', 'asc')
					->get();

			if (count($packages) === 0) {
				return response([
						'status' => Response::HTTP_NOT_FOUND,
						'type' => 'warning',
						'message' => 'Packages not found or not at office or delivered to the courier!'
				], Response::HTTP_NOT_FOUND);
			}

			$packages_arr = array();
			foreach ($packages as $package) {
				$packages_arr[] = $package->id;
			}

			Package::whereIn('internal_id', $track_arr)->whereNull('issued_to_courier_date')->update(['payment_receipt' => $receipt, 'payment_receipt_date' => $date]);

			Log::info('packages_for_delivery: ' . 'receipt ' . $receipt . ' package: ' . $packages);
			return response([
					'status' => Response::HTTP_OK,
					'receipt' => $receipt,
					'date' => $date,
					'packages' => $packages
			], Response::HTTP_OK);
		} catch (\Exception $exception) {
			return response([
					'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
					'type' => 'Error',
					'message' => 'Sorry, An error occurred...'
			], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}

	public function show_queue_table()
	{
		try {
			$location = Input::get("location");
			$today = Carbon::today();

			$functions = new Functions();
			if ($functions->validate_for_api([$location])) {
				return response([
						'status' => Response::HTTP_NOT_FOUND,
						'type' => 'validate',
						'message' => 'Fill all inputs!'
				], Response::HTTP_NOT_FOUND);
			}

			$stations = Queue::whereDate('date', $today)
					->where(['location_id' => $location, 'used' => 1])
					->distinct('station')
					->orderBy('station')
					->select('station')
					->get();

			$queues = array();
			foreach ($stations as $station) {
				$station_no = $station->station;
				$queue = Queue::whereDate('date', $today)
						->where(['location_id' => $location, 'used' => 1, 'station' => $station_no])
						->orderBy('id', 'desc')
						->select('no', 'type', 'operator_role')
						->first();

				$operator_role_id = $queue->operator_role;
				$operator_role = '';
				switch ($operator_role_id) {
					case 4:
						$operator_role = 'Kassa';
						break; // cashier
					case 5:
						$operator_role = 'Anbar';
						break; // delivery
					case 7:
						$operator_role = 'Operator';
						break; // operator
					default:
						$operator_role = '';
				}
				$queue_name = '';
				$queue_type = $queue->type;
				switch ($queue_type) {
					case 'c':
						{
							//cashier
							$queue_name = 'K';
						}
						break;
					case 'd':
						{
							//delivery
							$queue_name = 'A';
						}
						break;
					case 'o':
						{
							//online (for special orders)
							$queue_name = 'S';
						}
						break;
					default:
					{
						//information
						$queue_name = 'I';
					}
				}
				$queue_no = $queue->no;
				if ($queue_no < 100) {
					if ($queue_no < 10) {
						$queue_no = '00' . $queue_no;
					} else {
						$queue_no = '0' . $queue_no;
					}
				}
				$queue_name .= $queue_no;
				$single_queue['station'] = $operator_role . ' ' . $station_no;
				$single_queue['queue'] = $queue_name;
				array_push($queues, $single_queue);
			}
		
			return response([
					'status' => Response::HTTP_OK,
					'queues' => $queues,
			], Response::HTTP_OK);
		} catch (\Exception $exception) {
			return response([
					'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
					'type' => 'Error',
					'message' => 'Sorry, An error occurred...'
			], Response::HTTP_INTERNAL_SERVER_ERROR);
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
    
    private function CalculatePaid($package){
        
        $user_id = $package->client_id;
        $package_id = $package->id;
        
        $amount = $package->total_charge_value;
        $amount_usd = $package->amount_usd;
        $amount_azn = $package->amount_azn;
        
        $paid = $package->paid;
        $paid_usd = $package->paid_sum;
        $paid_azn = $package->paid_azn;
        
        $currency_id = $package->currency_id;
        
        $external_w_debt_azn = $package->external_w_debt_azn;
        $external_w_debt_usd = $package->external_w_debt;
        $internal_w_debt_azn = $package->internal_w_debt;
        $internal_w_debt_usd = $package->internal_w_debt_usd;
        
        $allDebtUsd = $amount_usd + $internal_w_debt_usd + $external_w_debt_usd;
        $allDebtAzn = $amount_azn + $internal_w_debt_azn + $external_w_debt_azn;
        
        $pay_azn = $allDebtAzn - $paid_azn;
        $pay_azn = sprintf('%0.2f', $pay_azn);
        
        $pay_usd = $allDebtUsd - $paid_usd;
        $pay_usd = sprintf('%0.2f', $pay_usd);
        
        $calculate_rate = 1;
        $pay = 0;
        if ($currency_id != 1) { // currency != USD
            $rate = $this->GetExchangeRate(1, $currency_id);
            $calculate_rate = $rate;
            $pay = (($internal_w_debt_usd + $external_w_debt_usd) * $calculate_rate) + $amount;
            $pay = $pay - $paid;
            $pay = sprintf('%0.2f', $pay);
        }else{
            $pay = $pay_usd;
        }
        
        $paid_status = 1;
        $total_paid = $paid + $pay;
        $total_paid_usd = $paid_usd + $pay_usd;
        $total_paid_azn = $paid_azn + $pay_azn;
        
        $total_paid = sprintf('%0.2f', $total_paid);
        
        $response = [
            'total_pay' => $total_paid,
            'total_pay_azn' => $total_paid_azn,
            'total_pay_usd' => $total_paid_usd,
            'pay_usd' => $pay_usd,
            'pay_azn' => $pay_azn,
            'pay' => $pay,
        ];
        
        return $response;
    }
    
    private function GetExchangeRate($from_currency_id, $to_currency_id = 1){
        $date = Carbon::today();
        $rate = ExchangeRate::whereDate('from_date', '<=', $date)
            ->whereDate('to_date', '>=', $date)
            ->where(['from_currency_id' => $from_currency_id, 'to_currency_id' => $to_currency_id]) //to USD
            ->select('rate')
            ->orderBy('id', 'desc')
            ->first();
        
        if (!$rate) {
            return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Rate not found!']);
        }
        return $rate->rate;
    }
}
