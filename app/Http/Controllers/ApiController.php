<?php

namespace App\Http\Controllers;

//use App\Receipts;
//use App\Item;
//use Carbon\Carbon;
//use Spatie\TranslationLoader\LanguageLine;

//use App\CourierOrders;
//use App\Package;
//use Carbon\Carbon;

//use App\User;
//use Illuminate\Support\Facades\DB;

//use App\Package;
//use App\TrackingLog;

class ApiController extends Controller
{
    public function test()
    {
//        $logs = TrackingLog::where('test', 0)->whereNotNull('container_id')->whereNull('position_id')->whereNull('deleted_by')->select('id', 'package_id', 'created_at')->get();
//
//        $count = 0;
//        $last_id = 0;
//        foreach ($logs as $log) {
//            $package_id = $log->package_id;
//            $date = $log->created_at;
//
//            Package::where('id', $package_id)->update(['container_date' => $date]);
//
//            $last_id = $log->id;
//            TrackingLog::where('id', $last_id)->update(['test' => 1]);
//            $count++;
//        }
//
//        return 'Count: ' . $count . ". Last ID: " . $last_id;

//        $users = DB::select("SELECT id, email, COUNT(*) FROM users GROUP BY email HAVING COUNT(*) > 1 order by id");
//
//        $count = 0;
//        foreach ($users as $user) {
//            $email = $user->email;
//
//            User::where('email', $email)->whereNull('email_verified_at')->update(['deleted_by' => 1907]);
//
//            $count++;
//        }
//
//        return $count;
//        $date = Carbon::today();
//        $rate = ExchangeRate::where(['from_currency_id' => 1, 'to_currency_id' => 3]) // usd -> azn
//        ->whereDate('from_date', '<=', $date)->whereDate('to_date', '>=', $date)
//            ->select('rate')
//            ->first();
//
//        $rate_to_azn = $rate->rate;
//
//        $payments = PaymentTask::where(['payment_type' => 'courier', 'is_paid' => 1])
//            ->where('id', '<', 16680)
//            ->select(
//                'created_by',
//                'amount',
//                'id',
//                'updated_at',
//                'order_id',
//                'packages',
//                'ip_address',
//                'response_str',
//                'pan',
//                'payment_key'
//            )->get();
//
//
//        $total_usd = 0;
//        $total_azn = 0;
//        $total_yes = 0;
//        $total_no = 0;
//        foreach ($payments as $payment) {
//            $user_id = $payment->created_by;
//            $amount_azn = $payment->amount;
//
//            $new_amount_azn = $amount_azn / 100;
//            $new_amount = $new_amount_azn / $rate_to_azn;
//            $new_amount = sprintf('%0.2f', $new_amount);
//
//            $total_azn += $new_amount_azn;
//            $total_usd += $new_amount;
//
//            $user = User::where('id', $user_id)->select('balance', 'phone1')->first();
//            $old_balance = $user->balance;
//            $new_balance = $old_balance - $new_amount;
//            $new_balance = sprintf('%0.2f', $new_balance);
//            if ($new_balance == -0.01) {
//                $new_balance = 0;
//            }
//            User::where('id', $user_id)->update([
//                'balance' => $new_balance,
//                'removed_balance' => $new_amount,
//                'old_balance' => $old_balance
//            ]);
//
//            $menfi = 'yox';
//            $negative = 0;
//            if ($new_balance >= 0) {
//                echo "yes - ";
//                $total_yes++;
//            } else {
//                echo "no - ";
//                $total_no++;
//                $menfi = $new_balance;
//                $negative = 1;
//            }
//
//            BalanceTest::create([
//                'client_id' => $user_id,
//                'amount_azn' => $new_amount_azn,
//                'amount_usd' => $new_amount,
//                'date' => $payment->updated_at,
//                'order_id' => $payment->order_id,
//                'packages' => $payment->packages,
//                'ip' => $payment->ip_address,
//                'old_balance' => $old_balance,
//                'new_balance' => $new_balance,
//                'negative' => $negative,
//                'phone' => $user->phone1,
//                'payment_id' => $payment->id,
//                'payment_key' => $payment->payment_key,
//                'response_str' => $payment->response_str,
//                'cart' => $payment->pan,
//            ]);
//
//            echo $new_amount . " - " . $old_balance . ' - ' . $old_balance . '(' . $menfi . ')'  . ' - ' . $user_id . "<br>";
//        }
//
//        echo "<br><br>" . $total_yes . ' YES <br>' . $total_no . ' NO';
//        echo "<br><br>" . $total_azn . ' AZN <br>' . $total_usd . ' USD';
//
//        return "<br><br><br>OK";


//        $orders = CourierOrders::whereNull('canceled_by')
//            ->whereNull('deleted_by')
//            ->select('packages', 'id')
//            ->get();
//
//        $count = 0;
//        $last_id = 0;
//
//        foreach ($orders as $order) {
//            $packages_str = $order->packages;
//            $packages_arr = explode(',', $packages_str);
//
//            Package::whereIn('id', $packages_arr)
//                ->whereNull('deleted_by')
//                ->update(['courier_order_id' => $order->id]);
//
//            $count++;
//            $last_id = $order->id;
//        }
//
//        return $count . ' of ' . count($orders) . ' success! Last id: ' . $last_id . '.';

//        $orders = CourierOrders::whereNotNull('courier_id')
//            ->whereNotNull('collected_by')
//            ->select('packages', 'id')
//            ->get();
//
//        $date = Carbon::now();
//        $count = 0;
//        $last_id = 0;
//
//        foreach ($orders as $order) {
//            $packages_str = $order->packages;
//            $packages_arr = explode(',', $packages_str);
//
//            Package::whereIn('id', $packages_arr)
//                ->whereNull('deleted_by')
//                ->update(['issued_to_courier_date' => $date]);
//
//            $count++;
//            $last_id = $order->id;
//        }
//
//        return $count . ' of ' . count($orders) . ' success! Last id: ' . $last_id . '.';

//        $items = Item::whereNotNull('package_id')
//            ->whereNull('deleted_by')
//            ->orderBy('package_id')
//            ->orderBy('id')
//            ->select('id', 'package_id')->get();
//
//        $old_id = 0;
//        $old_package_id = 0;
//        $count = 0;
//
//        foreach ($items as $item) {
//            $id = $item->id;
//            $package_id = $item->package_id;
//
//            if ($package_id == $old_package_id) {
//                Item::where('id', $old_id)->update(['deleted_by' => 1, 'deleted_at' => Carbon::now()]);
//                $count++;
//            }
//
//            $old_package_id = $package_id;
//            $old_id = $id;
//        }
//
//        return $count . ' deleted! (' . $old_id . ')';

//        $receipts = Receipts::whereNotNull('courier_order_id')
//            ->orderBy('courier_order_id')
//            ->orderBy('id')
//            ->select('id', 'courier_order_id')->get();
//
//        $old_id = 0;
//        $old_order_id = 0;
//        $count = 0;
//
//        foreach ($receipts as $receipt) {
//            $id = $receipt->id;
//            $order_id = $receipt->courier_order_id;
//
//            if ($order_id == $old_order_id) {
//                Receipts::where('id', $old_id)->update(['deleted_by' => 1, 'deleted_at' => Carbon::now()]);
//                $count++;
//            }
//
//            $old_order_id = $order_id;
//            $old_id = $id;
//        }
//
//        return $count . ' deleted! (' . $old_id . ')';

//        $key = 'date_message';
//        $text = 'Kuryer sifarişi növbəti 3 gün üçün seçilə bilər!';
//
//        $add = LanguageLine::create([
//            'group' => 'courier',
//            'key' => $key,
//            'text' => [
//                'az' => $text,
//                'en' => $text,
//                'ru' => $text
//            ],
//        ]);

        //return $add->id . ' ' . $key . ' OK';

//        $sms = new SMS();
//
//        $users_arr = array();
//
//        array_push($users_arr, '994777220075');
//        array_push($users_arr, '994507639641');
//        array_push($users_arr, '994777220075');
//
//        $users_arr = array_unique($users_arr);
//
//        return $sms->sendBulkSms('Test message 3...', $users_arr, time());

//        $timestamp = strtotime(Carbon::now());
//
//        $day = date('d', $timestamp);
//        $month = date('m', $timestamp);
//        $year = date('Y', $timestamp);
//
//        echo $day.$month.$year;

//        $date = Carbon::today();
//        $rates = ExchangeRate::whereDate('from_date', '<=', $date)
//            ->whereDate('to_date', '>=', $date)
//            ->where(['from_currency_id' => 1]) //to USD
//            ->select('rate', 'to_currency_id')
//            ->orderBy('id', 'desc')
//            ->get();
//
//        $items = Item::where('price', '>', 0)
//            ->whereNotNull('price')
//            ->whereRaw('(price_usd =0 or price_usd is null)')
//            ->whereNull('deleted_by')
//            ->select('id', 'price', 'currency_id')
//            ->get();
//
//        foreach ($items as $item) {
//            $price_usd = 0;
//            $currency_id = $item->currency_id;
//            $price = $item->price;
//            $rate = $this->calculate_exchange_rate($rates, $currency_id);
//            if ($rate != 0) {
//                $price_usd = $price / $rate;
//                $price_usd = sprintf('%0.2f', $price_usd);
//            }
//
//            if ($price_usd != 0) {
//                Item::where('id', $item->id)->update(['price_usd'=>$price_usd]);
//            }
//        }

        return 'Access denied!';
    }

//    private function calculate_exchange_rate($rates, $to)
//    {
//        try {
//            foreach ($rates as $rate) {
//                if ($rate->to_currency_id == $to) {
//                    return $rate->rate;
//                }
//            }
//
//            return 0;
//        } catch (\Exception $exception) {
//            return 0;
//        }
//    }
}
