<?php

namespace App\Http\Controllers;

use App\Category;
use App\Countries;
use App\ExchangeRate;
use App\Item;
use App\Mail\SpecialOrderMail;
use App\Package;
use App\PackageStatus;
use App\Seller;
use App\SpecialOrders;
use App\SpecialOrderStatus;
use App\Status;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrderController extends HomeController
{
    public function get_all_orders() {
        try {
            $query = Item::leftJoin('package', 'item.package_id', '=', 'package.id')
                ->leftJoin('users', 'package.client_id', '=', 'users.id')
                ->leftJoin('seller', 'package.seller_id', '=', 'seller.id')
                ->leftJoin('lb_status as status', 'package.last_status_id', '=', 'status.id')
                ->whereNull('item.deleted_by')
                ->whereNull('package.deleted_by');

            $search_arr = array(
                'no' => '',
                'number' => '',
                'seller' => '',
                'client' => '',
                'status' => ''
            );

            if (!empty(Input::get('no')) && Input::get('no') != ''  && Input::get('no') != null) {
                $where_no = Input::get('no');
                $query->where('package.id', $where_no);
                $search_arr['no'] = $where_no;
            }

            if (!empty(Input::get('number')) && Input::get('number') != ''  && Input::get('number') != null) {
                $where_number = Input::get('number');
                $query->where('package.number', 'LIKE', '%'.$where_number.'%');
                $search_arr['number'] = $where_number;
            }

            if (!empty(Input::get('client')) && Input::get('client') != ''  && Input::get('client') != null) {
                $where_client = Input::get('client');
                $query->whereRaw("
                    users.name LIKE '%".$where_client."%' or
                    users.surname LIKE '%".$where_client."%'
                ");
                $search_arr['client'] = $where_client;
            }

            if (!empty(Input::get('seller')) && Input::get('seller') != ''  && Input::get('seller') != null) {
                $where_seller = Input::get('seller');
                $query->where('package.seller_id', $where_seller);
                $search_arr['seller'] = $where_seller;
            }

            if (!empty(Input::get('status')) && Input::get('status') != ''  && Input::get('status') != null) {
                $where_status = Input::get('status');
                $query->where('package.last_status_id', $where_status);
                $search_arr['status'] = $where_status;
            }

            //short by start
            $short_by = 'package.id';
            $shortType = 'DESC';
            if (!empty(Input::get('shortBy')) && Input::get('shortBy') != ''  && Input::get('shortBy') != null) {
                $short_by = Input::get('shortBy');
            }
            if (!empty(Input::get('shortType')) && Input::get('shortType') != ''  && Input::get('shortType') != null) {
                $short_type = Input::get('shortType');
                if ($short_type == 2) {
                    $shortType = 'DESC';
                } else {
                    $shortType = 'ASC';
                }
            }
            //short by finish

            $orders = $query->orderBy($short_by, $shortType)
                ->select(
                    'package.id',
                    'users.name',
                    'users.surname',
                    'package.number',
                    'seller.title as seller',
                    'status.status_en as status'
                )
                ->paginate(50);

            $sellers = Seller::whereNull('deleted_by')->select('id', 'name')->get();
            $statuses = Status::whereNull('deleted_by')->select('id', 'status_en as status')->get();

            return view('backend.admin.all_orders', compact(
                'orders',
                'search_arr',
                'sellers',
                'statuses'
            ));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function get_special_orders() {
        try {
            $orders = SpecialOrders::leftJoin('users as client', 'special_orders.client_id', '=', 'client.id')
                ->leftJoin('countries as c', 'special_orders.country_id', '=', 'c.id')
                ->leftJoin('lb_status as s', 'special_orders.last_status_id', '=', 's.id')
                ->leftJoin('users as operator', 'special_orders.operator_id', '=', 'operator.id')
                ->leftJoin('currency as cur', 'special_orders.currency_id', '=', 'cur.id')
                ->where('is_paid', 1)
                ->whereNull('special_orders.deleted_by')
                ->whereNull('client.deleted_by')
                ->orderBy('special_orders.id', 'desc')
                ->select(
                    'special_orders.id',
                    'special_orders.pay_id',
                    'client.id as suite',
                    'client.name',
                    'client.surname',
                    'c.name_en as country',
                    's.status_en as status',
                    'operator.name as operator_name',
                    'operator.surname as operator_surname',
                    'special_orders.description',
                    'special_orders.quantity',
                    'special_orders.canceled_by',
                    'special_orders.placed_by',
                    'special_orders.is_paid',
                    'special_orders.price_azn',
                    'special_orders.price',
                    'cur.name as currency',
                    'special_orders.declarated_at',
                    'special_orders.package_id',
                    'special_orders.created_at'
                )
                ->paginate(50);

            $sellers = Seller::whereNull('deleted_by')->orderBy('title')->select('id', 'title')->get();
            $categories = Category::whereNull('deleted_by')->orderBy('name_en')->select('id', 'name_en as name')->get();

            return view('backend.admin.special_orders', compact(
                'orders',
                'sellers',
                'categories'
            ));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function get_update_special_order($order_id) {
        try {
            $order = SpecialOrders::leftJoin('users as client', 'special_orders.client_id', '=', 'client.id')
                ->leftJoin('countries as c', 'special_orders.country_id', '=', 'c.id')
                ->leftJoin('currency as cur', 'special_orders.currency_id', '=', 'cur.id')
                ->where('special_orders.id', $order_id)
                ->select(
                    'special_orders.id',
                    'client.id as suite',
                    'client.phone1 as phone',
                    'client.email',
                    'client.language',
                    'client.name',
                    'client.surname',
                    'c.name_en as country',
                    'special_orders.url',
                    'special_orders.title',
                    'special_orders.quantity',
                    'special_orders.single_price',
                    'special_orders.price',
                    'special_orders.common_debt',
                    'special_orders.cargo_debt',
                    'cur.name as currency',
                    'special_orders.color',
                    'special_orders.size',
                    'special_orders.description',
                    'special_orders.last_status_id',
                    'special_orders.disable',
                    'special_orders.placed_by',
                    'special_orders.canceled_by',
                    'special_orders.placed_by',
                    'special_orders.canceled_by'
                )
                ->first();

            if (!$order) {
                Session::flash('message', 'Order not found!');
                Session::flash('class', 'warning');
                Session::flash('display', 'block');
                return redirect()->route("show_special_orders");
            }

            $statuses = Status::where('for_special_order', 1)->select('id', 'status_en as status')->orderBy('status_en')->get();

            return view('backend.admin.update_special_order', compact(
                'order',
                'statuses'
            ));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function post_update_special_order(Request $request, $order_id) {
        try {
            if (!is_numeric($order_id)) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Wrong order format!']);
            }
            $validator = Validator::make($request->all(), [
                'status_id' => ['required', 'integer'],
                'quantity' => ['required', 'integer'],
                'price' => ['required'],
                'common_debt' => ['nullable'],
                'cargo_debt' => ['nullable'],
                'title' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:1000'],
            ]);
            if ($validator->fails()) {
                return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
            }

            $order = SpecialOrders::where('id', $order_id)
                ->whereNull('deleted_by')
                ->select('currency_id', 'last_status_id', 'client_id')
                ->first();
            if (!$order) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Order not found!']);
            }

            $status_id = $request->status_id;
            $quantity = $request->quantity;
            $price = $request->price;
            $common_debt = $request->common_debt;
            $cargo_debt = $request->cargo_debt;
            $description = $request->description;
            $title = $request->title;

            $date = Carbon::today();
            $rate = ExchangeRate::whereDate('from_date', '<=', $date)
                ->whereDate('to_date', '>=', $date)
                ->where(['from_currency_id'=>$order->currency_id, 'to_currency_id'=>3]) //to AZN
                ->select('rate')
                ->orderBy('id', 'desc')
                ->first();

            $price_azn = 0;
            if ($rate) {
                $price_azn = $rate->rate * $price;
            }

            $order_arr = array();
            $order_arr['operator_id'] = Auth::id();
            $order_arr['quantity'] = $quantity;
            $order_arr['price'] = $price;
            $order_arr['price_azn'] = $price_azn;
            $order_arr['common_debt'] = $common_debt;
            $order_arr['cargo_debt'] = $cargo_debt;
            $order_arr['title'] = $title;
            $order_arr['description'] = $description;

            $message = "";
            if ($status_id == 13) {
                // placed
                $order_arr['placed_by'] = Auth::id();
                $order_arr['placed_at'] = $date;
                $message = "sifarişiniz yerinə yetirilmişdir.";
            } else if ($status_id == 12) {
                // canceled
                $order_arr['canceled_by'] = Auth::id();
                $order_arr['canceled_at'] = $date;
                $message = "sifarişiniz ləğv edilmişdir.";
            } else if ($status_id == 18) {
                // debt
                $message = "sifarişinizə borc məbləğ əlavə edilmişdir.";
                if ($common_debt > 0) {
                    // common debt
                    $message .= "</br> Ümumi borc (Məhsulun qiyməti artmışdır): " . $common_debt . " TL.";
                }
                if ($cargo_debt > 0) {
                    // cargo debt
                    $message .= "</br> Daxili karqo borcu: " . $cargo_debt . " TL";
                }
            }

            SpecialOrders::where('id', $order_id)->update($order_arr);

            if ($order->last_status_id != $request->status_id) {
                SpecialOrderStatus::create([
                    'order_id' => $order_id,
                    'status_id' => $status_id,
                    'created_by' => Auth::id()
                ]);
            }

            if (strlen($message) > 0) {
                $this->send_mail_for_special_order($order_id, $order->client_id, $message);
            }

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Success!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function declare_special_order(Request $request, $order_id) {
        $validator = Validator::make($request->all(), [
            'track' => ['required', 'string', 'max:255'],
            'seller_id' => ['required', 'integer'],
            'category_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'integer'],
            'price' => ['required'],
            'invoice' => ['required', 'mimes:pdf,docx,doc,png,jpg,jpeg'],
            'remark' => ['nullable', 'string', 'max:5000'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $order = SpecialOrders::where('id', $order_id)->whereNull('deleted_by')
                ->select('country_id', 'client_id')
                ->first();
            if (!$order) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Order not found!']);
            }
            $real_client = $order->client_id;

            $country = Countries::where('id', $order->country_id)->select('currency_id')->first();
            if ($country) {
                $currency_id = $country->currency_id;
            } else {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Country not found!']);
            }

            $package_arr = array();
            $package_arr['country_id'] = $order->country_id;
            $package_arr['number'] = $request->track;
            $package_arr['seller_id'] = $request->seller_id;
            $package_arr['remark'] = $request->remark;

            $package_exist = Package::where(['number'=>$request->number])->select('id', 'client_id')->orderBy('id', 'desc')->first();
            if ($package_exist) {
                // update
                $client_id = $package_exist->client_id;
                if ($client_id != null && $client_id != $real_client) {
                    return response(['case' => 'warning', 'title' => 'Oops!', 'content' => "This pack is someone else's!"]);
                }
                $package_id = $package_exist->id;
                Package::where('id', $package_id)->update($package_arr);
            } else {
                // insert
                $package_arr['client_id'] = $real_client;
                $package_arr['created_by'] = Auth::id();
                $package = Package::create($package_arr);
                $package_id = $package->id;
            }

            PackageStatus::create([
                'package_id' => $package_id,
                'status_id' => 11, //declared
                'created_by' => Auth::id()
            ]);

            $date = Carbon::today();
            $rate = ExchangeRate::whereDate('from_date', '<=', $date)
                ->whereDate('to_date', '>=', $date)
                ->where(['from_currency_id'=>$currency_id, 'to_currency_id'=>1]) //to USD
                ->select('rate')
                ->orderBy('id', 'desc')
                ->first();

            $price_usd = 0;
            if ($rate) {
                $price_usd = $rate->rate * $request->price;
            }

            $item_arr = array();
            $item_arr['package_id'] = $package_id;
            $item_arr['category_id'] = $request->category_id;
            $item_arr['price'] = $request->price;
            $item_arr['price_usd'] = $price_usd;
            $item_arr['currency_id'] = $currency_id;
            $item_arr['quantity'] = $request->quantity;
            $item_arr['title'] = $request->title;
            $file = $request->file('invoice');
            $file_name = $request->track . '_invoice_' . Str::random(4) . '_' . time();
            Storage::disk('uploads')->makeDirectory('files/packages/invoices');
            $cover = $file;
            $extension = $cover->getClientOriginalExtension();
            Storage::disk('uploads')->put('files/packages/invoices/' . $file_name . '.' . $extension, File::get($cover));
            $url = '/uploads/files/packages/invoices/' . $file_name . '.' . $extension;
            $item_arr['invoice_doc'] = $url;
            $item_arr['invoice_confirmed'] = 1; // because added by operator
            $item_exist = Item::where('package_id', $package_id)->select('id')->orderBy('id', 'desc')->first();
            if ($item_exist) {
                // update
                Item::where('id', $item_exist->id)->update($item_arr);
            } else {
                // insert
                $item_arr['created_by'] = $request->created_by;
                Item::create($item_arr);
            }

            SpecialOrders::where('id', $order_id)->update(['declarated_at'=>$date]);
            SpecialOrderStatus::create([
                'order_id' => $order_id,
                'status_id' => 11,
                'created_by' => Auth::id()
            ]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Success!', 'id' => $order_id]);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function disable_order_for_client(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            SpecialOrders::where('id', $request->id)->update(['disable'=>1]);
            SpecialOrderStatus::create([
                'order_id' => $request->id,
                'status_id' => 20, // disable for client
                'created_by' => Auth::id()
            ]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function enable_order_for_client(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            SpecialOrders::where('id', $request->id)->update(['disable'=>0]);
            SpecialOrderStatus::create([
                'order_id' => $request->id,
                'status_id' => 1, // pending
                'created_by' => Auth::id()
            ]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    private function send_mail_for_special_order($order_id, $client_id, $message) {
        try {
            $client = User::where('id', $client_id)->select('phone1', 'email', 'name', 'surname')->first();
            if ($client) {
                Mail::to($client->email)->send(new SpecialOrderMail($order_id, $client->name . " " . $client->surname, $message));

                return true;
            }

            return false;
        } catch (\Exception $exception) {
            return false;
        }
    }
}
