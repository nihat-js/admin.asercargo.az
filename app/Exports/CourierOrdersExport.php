<?php

namespace App\Exports;

use App\CourierOrders;
use App\Package;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class CourierOrdersExport implements FromCollection, WithHeadings, WithColumnFormatting, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */

    public $no;
    public $name;
    public $surname;
    public $suite;
    public $status;
    public $courier;
    public $area;
    public $region;
    public $courier_payment_type;
    public $delivery_payment_type;
    public $date;
    public $admin_report;
    public $from_date;
    public $to_date;

    public function __construct($no, $name, $surname, $suite, $status, $courier, $area, $region, $courier_payment_type, $delivery_payment_type, $date, $admin_report = false, $from_date = null, $to_date = null)
    {
        $this->no = $no;
        $this->name = $name;
        $this->surname = $surname;
        $this->suite = $suite;
        $this->status = $status;
        $this->courier = $courier;
        $this->area = $area;
        $this->region = $region;
        $this->courier_payment_type = $courier_payment_type;
        $this->delivery_payment_type = $delivery_payment_type;
        $this->date = $date;
        $this->admin_report = $admin_report;
        $this->from_date = $from_date;
        $this->to_date = $to_date;
    }

    public function collection()
    {
        try {
            $query = CourierOrders::leftJoin('courier_areas', 'courier_orders.area_id', '=', 'courier_areas.id')
                ->leftJoin('courier_metro_stations', 'courier_orders.metro_station_id', '=', 'courier_metro_stations.id')
                ->leftJoin('courier_regions', 'courier_orders.region_id', '=', 'courier_regions.id')
                ->leftJoin('courier_payment_types', 'courier_orders.courier_payment_type_id', '=', 'courier_payment_types.id')
                ->leftJoin('courier_payment_types as delivery_payment_types', 'courier_orders.delivery_payment_type_id', '=', 'delivery_payment_types.id')
                ->leftJoin('users as client', 'courier_orders.client_id', '=', 'client.id')
                ->leftJoin('users as courier', 'courier_orders.courier_id', '=', 'courier.id')
                ->leftJoin('lb_status as status', 'courier_orders.last_status_id', '=', 'status.id')
                ->whereRaw('(
                (courier_orders.courier_payment_type_id = 1 and courier_orders.is_paid = 1) or
                (courier_orders.courier_payment_type_id <> 1 and courier_orders.delivery_payment_type_id <> 1) or
                (courier_orders.courier_payment_type_id <> 1 and courier_orders.delivery_amount = 0) or
                (courier_orders.courier_payment_type_id <> 1 and courier_orders.delivery_payment_type_id = 1 and courier_orders.delivery_amount > 0 and courier_orders.is_paid = 1)
                )');

            if (isset($this->no) && !empty($this->no) && $this->no != null && $this->no != "null" && $this->no != "undefined") {
                $query->where('courier_orders.id', $this->no);
            }
            if (isset($this->name) && !empty($this->name) && $this->name != null && $this->name != "null" && $this->name != "undefined") {
                $query->where('client.name', 'like', '%' . $this->name . '%');
            }
            if (isset($this->surname) && !empty($this->surname) && $this->surname != null && $this->surname != "null" && $this->surname != "undefined") {
                $query->where('client.surname', 'like', '%' . $this->surname . '%');
            }
            if (isset($this->suite) && !empty($this->suite) && $this->suite != null && $this->suite != "null" && $this->suite != "undefined") {
                $query->where('courier_orders.client_id', $this->suite);
            }
            if (isset($this->status) && !empty($this->status) && $this->status != null && $this->status != "null" && $this->status != "undefined") {
                $query->where('courier_orders.last_status_id', $this->status);
            }
            if (isset($this->courier) && !empty($this->courier) && $this->courier != null && $this->courier != "null" && $this->courier != "undefined") {
                $query->where('courier_orders.courier_id', $this->courier);
            }
            if (isset($this->area) && !empty($this->area) && $this->area != null && $this->area != "null" && $this->area != "undefined") {
                $query->where('courier_orders.area_id', $this->area);
            }
            if (isset($this->region) && !empty($this->region) && $this->region != null && $this->region != "null" && $this->region != "undefined") {
                $query->where('courier_orders.region_id', $this->region);
            }
            if (isset($this->courier_payment_type) && !empty($this->courier_payment_type) && $this->courier_payment_type != null && $this->courier_payment_type != "null" && $this->courier_payment_type != "undefined") {
                $query->where('courier_orders.courier_payment_type_id', $this->courier_payment_type);
            }
            if (isset($this->delivery_payment_type) && !empty($this->delivery_payment_type) && $this->delivery_payment_type != null && $this->delivery_payment_type != "null" && $this->delivery_payment_type != "undefined") {
                $query->where('courier_orders.delivery_payment_type_id', $this->delivery_payment_type);
            }
            if (isset($this->date) && !empty($this->date) && $this->date != null && $this->date != "null" && $this->date != "undefined") {
                $query->whereDate('courier_orders.date', $this->date);
            } else {
                if (!$this->admin_report) {
                    $packageObj = new PackageObj();
                    $packageObj->TR_NUMBER = 'Date must be selected!';
                    return collect(['error' => $packageObj]);
                }
            }

            if ($this->admin_report) {
                if (isset($this->from_date) && !empty($this->from_date) && $this->from_date != null && $this->from_date != "null" && $this->from_date != "undefined") {
                    $query->whereDate('courier_orders.date', '>=', $this->from_date);
                } else {
                    $packageObj = new PackageObj();
                    $packageObj->TR_NUMBER = 'From date must be selected!';
                    return collect(['error' => $packageObj]);
                }
                if (isset($this->to_date) && !empty($this->to_date) && $this->to_date != null && $this->to_date != "null" && $this->to_date != "undefined") {
                    $query->whereDate('courier_orders.date', '<=', $this->to_date);
                } else {
                    $packageObj = new PackageObj();
                    $packageObj->TR_NUMBER = 'To date must be selected!';
                    return collect(['error' => $packageObj]);
                }
            } else {
                $query->whereNotNull('courier_orders.courier_id');
                $query->whereNull('courier_orders.delivered_at');
            }

            $orders = $query->orderBy('courier_orders.date')
                ->orderBy('courier_orders.id')
                ->select(
                'courier_orders.id',
                'courier_orders.packages',
                'courier_orders.client_id as suite',
                'client.passport_number',
                'client.name as client_name',
                'client.surname as client_surname',
                'courier_orders.phone',
                'courier_areas.name_en as area',
                'courier_regions.name_az as region',
                'courier_metro_stations.name_en as metro_station',
                'courier_orders.address',
                'courier_orders.date',
                'courier_orders.post_zip',
                'courier_orders.courier_payment_type_id',
                'courier_orders.delivery_payment_type_id',
                'courier_payment_types.name_en as courier_payment_type',
                'delivery_payment_types.name_en as delivery_payment_type',
                'courier_orders.amount as delivery_amount',
                'courier_orders.delivery_amount as shipping_amount',
                'courier_orders.total_amount as summary_amount',
                'courier_orders.is_paid',
                'courier.name as courier_name',
                'courier.surname as courier_surname',
                'status.status_en as status',
                    'courier_orders.azerpost_track',
                    'courier_orders.order_weight'
            )->get();

            $orders_arr = array();
            $i = 0;
            foreach ($orders as $order) {
                $i++;

                $total_cash_amount = 0;

                if ($this->admin_report) {
                    $courier_amount = $order->delivery_amount;
                    $shipping_amount = $order->shipping_amount;

                    $total_cash_amount = $courier_amount + $shipping_amount;
                } else {
//                    if ($order->is_paid == 0) {
//                        $courier_amount = $order->delivery_amount;
//                        $shipping_amount = $order->shipping_amount;
//                    } else {
//                        $courier_amount = 0;
//                        $shipping_amount = 0;
//                    }
//
//                    if ($order->courier_payment_type_id == 2) {
//                        $total_cash_amount += $courier_amount;
//                    }
//
//                    if ($order->delivery_payment_type_id == 2) {
//                        $total_cash_amount += $shipping_amount;
//                    }
    
                    $courier_amount = $order->delivery_amount;
                    $shipping_amount = $order->shipping_amount;
                    $total_cash_amount = $order->summary_amount;
                }

                $packages_str = $order->packages;
                $packages_arr = explode(',', $packages_str);
                $packages = Package::whereIn('id', $packages_arr)->select('number')->get();
                $tracks = '';

                foreach ($packages as $package) {
                    $track = $package->number;
                    if (strlen($track) > 7) {
                        $track = substr($track, strlen($track) - 7);
                    }

                    $tracks .= $track . ', ';
                }
                $tracks = trim($tracks);
                if (strlen($tracks) > 0) {
                    $tracks = substr($tracks, 0, -1);
                }

                $courierOrderObj = new CourierOrdersObj();
                $courierOrderObj->no = $i;
                $courierOrderObj->order_number = $order->id;
                $courierOrderObj->suite = $order->suite;
                $courierOrderObj->client = $order->client_name . ' ' . $order->client_surname;
                $courierOrderObj->passport = $order->passport_number;
                $courierOrderObj->phone = $order->phone;
                if($order->post_zip == null){
                    $courierOrderObj->area = $order->area;
                }else{
                    $courierOrderObj->area = $order->region;
                }
                $courierOrderObj->metro_station = $order->metro_station;
                $courierOrderObj->post_zip = $order->post_zip;
                $courierOrderObj->address = $order->address;
                $courierOrderObj->date = $order->date;
                $courierOrderObj->courier_payment_type = $order->courier_payment_type;
                $courierOrderObj->delivery_payment_type = $order->delivery_payment_type;
                $courierOrderObj->courier = $order->courier_name . ' ' . $order->courier_surname;
                $courierOrderObj->delivery_amount = $courier_amount;
                $courierOrderObj->shipping_amount = $shipping_amount;
                $courierOrderObj->summary_amount = $total_cash_amount;
                if ($this->admin_report) {
                    if ($order->is_paid == 1) {
                        $courierOrderObj->paid = 'YES';
                    } else {
                        $courierOrderObj->paid = 'No';
                    }
                    $courierOrderObj->status = $order->status;
                } else {
                    unset(
                        $courierOrderObj->paid,
                        $courierOrderObj->status
                    );
                }
                $courierOrderObj->tracks = $tracks;
                $courierOrderObj->azerpost_track = $order->azerpost_track;
                $courierOrderObj->order_weight = $order->order_weight;

                array_push($orders_arr, $courierOrderObj);
            }

            return collect($orders_arr);
        } catch (\Exception $exception) {
            $packageObj = new PackageObj();
            $packageObj->TR_NUMBER = 'Something went wrong!';
            return collect(['error' => $packageObj]);
        }
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_NUMBER,
            'J' => NumberFormat::FORMAT_DATE_DATETIME
        ];
    }

    public function headings(): array
    {
        if ($this->admin_report) {
            return [
                'No',
                'Order number',
                'Suite',
                'Client',
                'Passport',
                'Phone',
                'Area or Region',
                'Metro station',
                'Post Zip',
                'Address',
                'Date',
                'Courier payment type',
                'Delivery payment type',
                'Courier',
                'Delivery amount',
                'Shipping amount',
                'Total amount',
                'Paid',
                'Status',
                'Tracks',
                'Azerpost Track',
                'Weight'
            ];
        } else {
            return [
                'No',
                'Order number',
                'Suite',
                'Client',
                'Passport',
                'Phone',
                'Area or Region',
                'Metro station',
                'Post Zip',
                'Address',
                'Date',
                'Courier payment type',
                'Delivery payment type',
                'Courier',
                'Delivery amount',
                'Shipping amount',
                'Summary amount',
                'Tracks',
                'Azerpost Track',
                'Weight'
            ];
        }
    }
}
