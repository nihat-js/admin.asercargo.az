<?php

namespace App\Http\Controllers;

use App\BalanceLog;
use App\ExchangeRate;
use App\Flight;
use App\Package;
use App\PackageStatus;
use App\PaymentLog;
use App\Status;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Jobs\CanadaShopChangeStatusJob;

class AllPartnerController extends Controller
{
    public function get_canadashop()
    {
        try {
            /*[3, 30, 42, 43, 44, 45, 46]*/
            $flights = Flight::whereIn('location_id', [2, 14])
                ->orderBy('id', 'DESC')
                ->get();

            return view('backend.warehouse.canadaShopPackageStatus', compact('flights'));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function set_canadashop_flight_package(Request $request){
        try {

            $containers = Flight::where('flight.id', $request->flight)
                ->leftJoin('container', 'flight.id', 'container.flight_id')
                ->whereNull('flight.deleted_at')
                ->select('container.id')
                ->get();

            $packages = Package::whereIn('last_container_id', $containers)
                ->where('seller_id', 1338)
                ->where('in_baku', 1)
                ->whereNull('deleted_at')
                ->select('id', 'amount_usd', 'client_id')
                ->get();

            if ($packages->count() > 0) {
                ini_set('max_execution_time', 360);
                DB::table('package')->whereIn('id', $packages->pluck('id')->toArray())->update([
                    'paid' => DB::raw('amount_usd'),
                    'paid_status' => 1,
                    'payment_type_id' => 1,
                    'last_status_id' => 3,
                    'last_status_date' => Carbon::now(),
                    'delivered_by' => Auth::id(),
                    'delivered_at' => Carbon::now()
                ]);

                return response(['case' => 'success', 'change' => true, 'content' => 'Status is changed!']);
            } else {
                return response(['case' => 'error', 'change' => false, 'content' => 'Package not found ']);
            }

        }catch (\Exception $exception){
            //dd($exception);
            return 'error';
        }
    }
}
