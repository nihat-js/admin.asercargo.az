<?php

namespace App\Http\Controllers;

use App\Item;
use App\Package;
use App\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        switch (Auth::user()->role()) {
            case 4: return redirect()->route("cashier_page"); //cashier
            case 5: return redirect()->route("warehouse_page"); //warehouse (delivery or distributor)
            //case 6: return redirect()->route("distributor_page"); //distributor
            case 7: return redirect()->route("operator_page"); //operator
            case 8: return redirect()->route("courier_courier_page"); //courier
            default: return view('backend.index');
        }
    }

    public function access_denied() {
        return view('backend.access');
    }

    public function create_item_for_package() {
        if (Auth::id() != 1) {
            //only me
            return redirect("/");
        }
        try {
            $packages = Package::whereNull('deleted_by')
                ->whereNull('delivered_by')
                ->where('has_item_control', 0)
                ->select(
                    'id'
                )
                ->take(1000)
                ->get();

            $all = count($packages);
            if ($all == 0) {
                return "No packages";
            }
            $no_item = 0;
            $add_item = array();
            $all_packages = array();
            foreach ($packages as $package) {
                if (Item::where('package_id', $package->id)->whereNull('deleted_by')->count() == 0) {
                    $no_item++;
                    Item::create(['package_id'=>$package->id, 'created_by'=>1]);
                    array_push($add_item, $package->id);
                } else {
                    array_push($all_packages, $package->id);
                }
            }

            Package::whereIn('id', $all_packages)->update(['has_item_control'=>1]);
            Package::whereIn('id', $add_item)->update(['has_item_control'=>2]);

            return "All: " . $all . " | No item: " . $no_item;
        } catch (\Exception $exception) {
            return "Catch";
        }
    }

    public function calculate_amounts() {
        if (Auth::id() != 1) {
            //only me
            return redirect("/");
        }
        try {
            $packages = Package::where('total_charge_value', 0)
                ->whereNull('deleted_by')
                ->whereNull('delivered_by')
                //->where('paid_status', 0)
                ->where('calculate_amount', 0)
                ->select(
                    'id',
                    'client_id',
                    'departure_id',
                    'destination_id',
                    'gross_weight',
                    'volume_weight',
                    'length',
                    'width',
                    'height',
                    'tariff_type_id'
                )
                ->get();

            if (count($packages) == 0) {
                return "No packages";
            }

            $calculate = 0;
            $passed = 0;
            $collector = new CollectorController();
            foreach ($packages as $package) {
                //calculate amount
                $package_id = $package->id;
                $client_id = $package->client_id;
                $departure_id = $package->departure_id;
                $destination_id = $package->destination_id;
                $category_id = 0;
                $seller_id = 0;
                $gross_weight = $package->gross_weight;
                $volume_weight = $package->volume_weight;
                $length = $package->length;
                $width = $package->width;
                $height = $package->height;
                $tariff_type_id = $package->tariff_type_id;

                $amount_response = $collector->calculate_amount($client_id, $departure_id, $destination_id, $category_id, $seller_id, $gross_weight, $volume_weight, $length, $width, $height, $tariff_type_id);
                if ($amount_response['type'] == false) {
                    $passed++;
                    continue;
                }
                $amount = $amount_response['amount'];
                $currency_id_for_amount = $amount_response['currency_id'];
                $chargeable_weight_type = $amount_response['chargeable_weight_type'];
                $used_contract_detail_id = $amount_response['used_contract_detail_id'];

                Package::where('id', $package_id)->update([
                    'chargeable_weight' => $chargeable_weight_type,
                    'new_amount' => $amount,
                    'new_currency_id' => $currency_id_for_amount,
                    'used_contract_detail_id' => $used_contract_detail_id,
                    'calculate_amount' => 1
                ]);

                $calculate++;
            }

            return "OK | Calculate: " . $calculate . " | Passed: " . $passed;
        } catch (\Exception $exception) {
            return "Catch";
        }
    }

    public function add_positions() {
        if (Auth::id() == 1) {
            for ($i = 1; $i < 1000; $i++) {
                $code = $i;
                if ($code < 100) {
                    if ($code < 10) {
                        $code = '00' . $code;
                    } else {
                        $code = '0' . $code;
                    }
                }

                $ps = "A" . $code;

                Position::create([
                    'name' => $ps,
                    'location_id' => 1
                ]);
            }

            return 'OK';
        } else {
            return redirect("/");
        }
    }
}
