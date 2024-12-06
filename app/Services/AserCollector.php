<?php

namespace App\Services;

use App\Category;
use App\ChangeAccountLog;
use App\Container;
use App\Contract;
use App\ContractDetail;
use App\Countries;
use App\Currency;
use App\EmailListContent;
use App\ExchangeRate;
use App\Http\Controllers\Classes\Collector;
use App\Http\Controllers\Classes\SMS;
use App\Http\Controllers\NotificationController;
use App\Item;
use App\Jobs\CollectorInWarehouseJob;
use App\Location;
use App\Package;
use App\PackageFiles;
use App\PackageStatus;
use App\Position;
use App\Seller;
use App\Settings;
use App\SmsTask;
use App\TrackingLog;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AserCollector
{

    public function collectorService(Request $request, $api = false, $user_id = 0, $departure_id = 0){
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

            if ($invoice_status == 3 and $request->invoice < 1) {
                return response(['case' => 'warning', 'title' => 'Validation!', 'type' => 'warning', 'content' => 'invoice price cannot be 0'], Response::HTTP_BAD_REQUEST);
            }

            $status_id = $request->status_id;

            $tracking_internal_same = 0;
            if (isset($request->tracking_internal_same)) {
                $tracking_internal_same = $request->tracking_internal_same;
            }


            $departure_id = $request->collector_departure;
            $user_id = $request->collector_user;


            if($request->invoice == null){
                $request->invoice = 0;
            }


            if (isset($request->client_id) && !empty($request->client_id) && $request->client_id != null) {
                $client_control = $this->client_control($request->client_id);
                if (!$client_control) {
                    return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'error', 'content' => 'Client not found!'], Response::HTTP_NOT_FOUND);
                }
                $client_id = $request->client_id;
            } else {
                $client_id = 0;
            }

            if ($client_id == 0) {
                if (!isset($request->client_name_surname) && empty($request->client_name_surname) && $request->client_name_surname == null) {
                    return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'error', 'content' => 'If the owner of the package is not known, the name and surname must be entered!'], Response::HTTP_NOT_ACCEPTABLE);
                }
                $client_name_surname = $request->client_name_surname;
            } else {
                $client_name_surname = '';
            }

            if ($status_id == 36 && $client_id != 0) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'error', 'content' => 'Unknown client status can only be selected for unknown packages!'], Response::HTTP_NOT_ACCEPTABLE);
            }

            if (($request->container_id == null || empty($request->container_id)) && ($request->position == null || empty($request->position))) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'error', 'content' => 'No container or position selected!'], Response::HTTP_BAD_REQUEST);
            }
            if ($request->container_id != null && $request->position != null) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'error', 'content' => 'You can only choose one: container or position!'], Response::HTTP_BAD_REQUEST);
            }



            // images control for prohibited or damaged status
            if ($status_id == 8 || $status_id == 7) {
                //dd($status_id);
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
                        return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'error', 'content' => 'Image must be added for packages which status is "Prohibited" or "Damaged"!'], Response::HTTP_NOT_ACCEPTABLE);
                    }
                }
            }

            //controls
            $destination_control = $this->get_destination_id($request->destination);
            if ($destination_control) {
                $destination_id = $destination_control;
            } else {
                return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'error', 'content' => 'Destination not found!'], Response::HTTP_NOT_FOUND);
            }

            if (isset($request->currency) && $request->currency != null && !empty($request->currency) && $request->currency != 'null') {
                $currency_control = $this->get_currency_id($request->currency);
                if ($currency_control) {
                    $currency_id = $currency_control;
                } else {
                    return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'error', 'content' => 'Currency not found!'], Response::HTTP_NOT_FOUND);
                }
            }
            $seller_control = $this->get_seller_id($request->seller);
            if ($seller_control) {
                $seller_id = $seller_control;
            } else {

                if ($invoice_status == 3) {
                    return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'error', 'content' => 'Seller can not be empty if status is "invoice available" '], Response::HTTP_BAD_REQUEST);
                }
                $seller_id = null;
            }
            if (!$request->get("title") and $invoice_status == 3) {
                return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'error', 'content' => 'Title can not be empty if status is "invoice available" '], Response::HTTP_BAD_REQUEST);
            }
            $category_control = $this->get_category_id($request->category);

            if ($category_control) {
                $category_id = $category_control;
            } else {

                if ($invoice_status == 3) {
                    return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'error', 'content' => 'Category can not be empty if status is "invoice available" '], Response::HTTP_BAD_REQUEST);
                }
                $category_id = null;
            }
            if ($request->position != null && !empty($request->position)) {
                if ($status_id == 5) {
                    return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'error', 'content' => 'Packages which status is "Ready for carriage" cannot be placed in the position!'], Response::HTTP_BAD_REQUEST);
                }
                $position_control = $this->get_position_id($request->position, $departure_id);
                if ($position_control) {
                    $position_id = $position_control;
                } else {
                    return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'error', 'content' => 'Position not found at your location!'], Response::HTTP_NOT_FOUND);
                }

                $tracking_arr['position_id'] = $position_id;
                $package_arr['position_id'] = $position_id;
                $package_arr['last_container_id'] = null;
            }
            if ($request->container_id != null && !empty($request->container_id)) {
                if ($client_id == 0) {
                    return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'error', 'content' => 'Unknown packages cannot be placed in the container!'], Response::HTTP_BAD_REQUEST);
                }
                if ($status_id != 5) {
                    return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'error', 'content' => 'Packages which status is "Ready for carriage" can be placed in the container!'], Response::HTTP_BAD_REQUEST);
                }
                $container_control = $this->container_control($request->container_id, $departure_id);
                if (!$container_control) {
                    return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'error', 'content' => 'Container not found or Container closed!'], Response::HTTP_NOT_FOUND);
                }
                $tracking_arr['container_id'] = $request->container_id;
                $package_arr['last_container_id'] = $request->container_id;
                $package_arr['container_date'] = Carbon::now();
                $has_container = $request->container_id;
            }

            //package
            $isLegalEntity = false;
            if ($request->get('is_legal_entity') == 'true') {
                $isLegalEntity = true;
                $package_arr['customer_type_id'] = 2;
            } else {
                $package_arr['customer_type_id'] = 1;
            }
    
            $branch = null;
            if ($client_id != 0){
                $branch =  $client_control['branch_id'];
            }
            //$branch = $client_control['branch_id'];
            if ($branch == null || $seller_id == 1338){
                $branch = 1;
            }
            
            $package_arr['number'] = $tracking_number;
            $package_arr['description'] = $request->description;
            $package_arr['package_img'] = $request->package_img;
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
            if ($status_id == 7 || $status_id == 8){
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
            }



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


            if($departure_id == 6){
                if($request->subCat == null){
                    return response(['case' => 'error', 'title' => 'Ooops', 'type' => 'error', 'content' => 'Note: Sub category is required.']);
                }
            }

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

            $notification = new NotificationController();

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

                                    $notification->sendNotification($mailTitle, $mailSubject, $email_push_content, $client_id);

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

                                $notification->sendNotification($email_title, $email_subject, $email_push_content, $client_id);
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

                                    $content_prohibited_item = empty($email_push_content) ? $email_content : $email_push_content;

                                    $notification->sendNotification($email_title, $email_subject, $email_push_content, $client_id);

                                    $job = (new CollectorInWarehouseJob($email_to, $email_title, $email_subject, $email_content, $email_bottom, $email_button))
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

                                    $content_damaged_item = empty($email_push_content) ? $email_content : $email_push_content;

                                    $notification->sendNotification($email_title, $email_subject, $content_damaged_item, $client_id);

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

                                            $notification->sendNotification($mailTitle, $mailSubject, $content, $client_id);

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
                                            $notification->sendNotification($email_title, $email_subject, $content_not_declared_notification, $client_id);

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
                                            $notification->sendNotification($mailTitle, $mailSubject, $content_no_invoice_legal_entity, $client_id);


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

                                        $notification->sendNotification($email_title, $email_subject, $email_push_content, $client_id);

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
                                'created_by' => $user_id
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
                                'created_by' => $user_id
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
                                'created_by' => $user_id
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
            //dd($exception);
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

    private function get_currency_id($id)
    {
        try {
            if ($id == null || empty($id)) {
                return false;
            }

            $currency = Currency::where('id', $id)->whereNull('deleted_by')->select('id')->first();

            if ($currency) {
                return $currency->id;
            } else {
                return false;
            }
        } catch (\Exception $exception) {
            return false;
        }
    }

    private function get_seller_id($id)
    {
        try {
            if ($id == null || empty($id)) {
                return false;
            }

            $seller = Seller::where('id', $id)->whereNull('deleted_by')->select('id')->first();

            if ($seller) {
                return $seller->id;
            } else {
                return false;
            }
        } catch (\Exception $exception) {
            return false;
        }
    }

    private function get_category_id($id)
    {
        try {
            if ($id == null || empty($id)) {
                return false;
            }

            $category = Category::where('id', $id)->whereNull('deleted_by')->select('id')->first();

            if ($category) {
                return $category->id;
            } else {
                return false;
            }
        } catch (\Exception $exception) {
            return false;
        }
    }

    private function get_position_id($id, $location_id)
    {
        try {
            if ($id == null || empty($id)) {
                return false;
            }

            $position = Position::where('id', $id)->where('location_id', $location_id)->whereNull('deleted_by')->select('id')->first();

            if ($position) {
                return $position->id;
            } else {
                return false;
            }
        } catch (\Exception $exception) {
            return false;
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
            Log::info([
                "internal_create_log", $internal_id
            ]);
            if (!$internal_id) {
                return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong when created internal id!']);
            }

            return response(['case' => 'success', 'title' => 'Success!', 'internal_id' => $internal_id]);
        } catch (\Exception $exception) {
            //dd($exception);
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
                        'created_by' => $user_id,
                        'which_platform' => 'aser'
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
                    'old_client_id' => $pack,
                    'which_platform' => 'aser'
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
                        'old_client_id' => $pack,
                        'which_platform' => 'aser'
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

    public function change_position($request)
    {

        try {
            $user_id = $request->collector->id;
            $user_location_id = $request->collector->location();

            $package = null;
            $legal_entity = null;

            $position_no = $request->container;
            $package_number = $request->track;
            //$package_number_search = $package_number;

            $package = Package::whereRaw("(number = '" . $package_number . "' or internal_id = '" . $package_number . "')")
                ->whereNull('deleted_by')
                ->where('package.customer_type_id', 1)
                ->whereIn('last_status_id', [39, 40, 5])
                ->whereIn('carrier_status_id', [1, 2, 7])
                ->where('departure_id', $user_location_id)
                ->orderBy('id', 'desc')
                ->select('id', 'number', 'container_id', 'position_id','last_status_id','carrier_status_id')
                ->first();

            if(!$package) {
                $legal_entity = Package::whereRaw("(number = '" . $package_number . "' or internal_id = '" . $package_number . "')")
                    ->leftJoin('item', 'package.id', 'item.package_id')
                    ->whereNull('package.deleted_by')
                    ->whereIn('package.last_status_id', [41, 5])
                    ->where('package.carrier_status_id', 0)
                    ->where('package.customer_type_id', 2)
                    ->whereIn('item.invoice_status', [3,4])
                    ->where('package.departure_id', $user_location_id)
                    ->orderBy('package.id', 'desc')
                    ->select('package.id', 'package.number', 'package.container_id', 'package.position_id','package.last_status_id','package.carrier_status_id')
                    ->first();
            }

            //dd($package);
            if (!$package && !$legal_entity) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Package not found or not declared!'], Response::HTTP_NOT_FOUND);
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
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Container is not found or flight closed!'], Response::HTTP_NOT_FOUND);
            }

            $position_id = $position->id;


            $res = Package::where('id', $package_id)->update(['last_status_id' => 5,'container_id'=>$position_id, 'last_container_id'=>$position_id, 'container_date'=>Carbon::now(), 'position_id'=> null]);


            return response(['case' => 'success', 'change' => true, 'content' => 'Position is changed! ' . $package_id, 'track' => $package_number_response]);
        } catch (\Exception $exception) {
           // dd($exception);
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Something went wrong!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function collector_search_packages($request) {
        try {

           /* if($request->get('track') == null && $request->get('client') == null){
                return \response(['case' => 'warning', 'message' => 'Track or client ID must be selected' ], Response::HTTP_BAD_REQUEST);
            }*/

            if(!$request->get('client') && !$request->get('track')){
                return \response(['case' => 'warning', 'message' => 'Track or client ID must be selected' ], Response::HTTP_BAD_REQUEST);
            }


                $packages = Package::leftJoin('item', 'package.id', '=', 'item.package_id')
                ->leftJoin('currency as cur', 'item.currency_id', '=', 'cur.id')
                ->leftJoin('seller as s', 'package.seller_id', '=', 's.id')
                ->leftJoin('category as cat', 'item.category_id', '=', 'cat.id')
                ->leftJoin('position as pos', 'package.position_id', '=', 'pos.id')
                ->leftJoin('lb_status as status', 'package.last_status_id', '=', 'status.id')
                //->when($request->get('client'), fn ($query) => $query->where('package.client_id', $request->get('client')))
                //->when($request->get('track'), fn ($query) => $query->whereRaw("(package.number = '" . $request->get('track') . "' or package.internal_id = '" . $request->get('track') . "')"))
                ->where('package.departure_id', $request->collector->location())
                ->whereNull('package.deleted_at')
                    ->where(function ($query) use ($request) {
                        if ($request->filled('client')) {
                            $query->where('package.client_id', $request->get('client'));
                        }
                        if ($request->filled('track')) {
                            $trackValue = $request->get('track');
                            $query->where(function ($subquery) use ($trackValue) {
                                $subquery->where('package.number', $trackValue)
                                    ->orWhere('package.internal_id', $trackValue);
                            });
                        }
                    })
                ->orderBy('package.id', 'Desc')
                ->select(
                    'package.id',
                    'package.number',
                    'package.internal_id',
                    'status.status_en as status',
                    'package.container_id',
                    'package.position_id',
                    'pos.name as position',
                    'package.gross_weight',
                    's.name as seller',
                    'cat.name_en as category',
                    'item.price',
                    'cur.name as currency',
                    'item.invoice_doc',
                    'item.invoice_uploaded_date'
                )
                ->paginate(50);

            //dd($packages);

            return \response(['case' => 'success', 'packages' => $packages]);
        } catch (\Exception $exception) {
            return \response(['case' => 'error', 'message' => 'Error found'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createPDF($request)
    {

        try{
            $query = Package::LeftJoin('item', 'package.id', '=', 'item.package_id')
                ->leftJoin('users as client', 'package.client_id', '=', 'client.id')
                ->leftJoin('currency as cur', 'item.currency_id', '=', 'cur.id')
                ->leftJoin('currency as pack_cur', 'package.currency_id', '=', 'pack_cur.id')
                ->leftJoin('seller as s', 'package.seller_id', '=', 's.id')
                ->leftJoin('category as cat', 'item.category_id', '=', 'cat.id')
                ->leftJoin('position as pos', 'package.position_id', '=', 'pos.id')
                ->leftJoin('container', 'package.last_container_id', '=', 'container.id')
                ->leftJoin('flight', 'container.flight_id', '=', 'flight.id')
                ->leftJoin('lb_status as status', 'package.last_status_id', '=', 'status.id')
                ->leftJoin('locations', 'package.country_id', '=', 'locations.id')
                ->whereNull('package.deleted_at')
                ->where('package.departure_id', $request->collector->location())
                ->whereRaw("(package.number = '" . $request->get('track') . "' or package.internal_id = '" . $request->get('track') . "')");

            $data = $query->orderBy('package.id')
                ->select(
                    'package.client_id as Suit',
                    'client.name as client_name',
                    'client.surname as client_surname',
                    'client.phone1 as phone',
                    'client.address1 as address',
                    'package.number as track',
                    'package.internal_id',
                    'package.gross_weight',
                    'package.volume_weight',
                    'package.total_charge_value',
                    'pack_cur.name as pack_cur',
                    'package.carrier_registration_number',
                    'package.carrier_status_id',
                    'package.last_status_id as last_status',
                    'status.status_en as status',
                    'package.container_id',
                    'package.position_id',
                    'pos.name as position',
                    's.name as seller',
                    'cat.name_en as category',
                    'item.price',
                    'item.price_usd',
                    'item.quantity',
                    'cur.name as currency',
                    'flight.name',
                    'flight.departure',
                    'flight.destination',
                    'flight.awb',
                    'locations.address as location',
                    'item.invoice_doc as invoice_document'
                )
                ->first();

            //dd($data);

            if (!$data) {
                return response(['case' => 'Error', 'title' => 'Oops!', 'content' => 'Bad request'], Response::HTTP_BAD_REQUEST);
            }

            if ($data->price !== null && $data->price != 0) {
                $charge_collect = "x";
            } else {
                $charge_collect = "";
            }

            $total_price = $data->total_charge_value + $data->price_usd;

            $response = [
                "track" => $data->track,
                "chargeCollect" => $charge_collect,
                'suit' => 'AS' . $data->Suit,
                'fullname' => $data->client_name . ' ' . $data->client_surname,
                'phone' => $data->phone,
                'deliveryAddress' => $data->address,
                'fromAddress' => $data->location,
                'trackingNumber' => $data->number,
                'internal_id' => $data->internal_id,
                'totalGrossWeight' => $data->gross_weight,
                'chargeableVolumeWeight' => $data->volume_weight,
                'shippingPrice' => $data->total_charge_value . ' ' . $data->pack_cur,
                'cdn' => $data->carrier_registration_number,
                'seller' => $data->seller,
                'category' => $data->category,
                'totalNumberOfPackages' => $data->quantity,
                'declaredValueForCustoms' => $data->price . ' ' . $data->currency,
                'totalPrice' => $total_price . ' USD',
                'aserExpressFlight' => $data->name,
                'origin' => $data->departure,
                'destination' => $data->destination,
                'awb' => $data->awb,
                'invoice_document' => $data->invoice_document
            ];

            if($data->last_status != 37){
                return $response;
            }else{
                return response([
                    'Message' => 'Your package not prepared to flight',
                    'Status' => $data->status
                ]);
            }

        }catch (\Exception $exception) {
            //dd($exception);
            return response()->json([
                'message' => 'Error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }


    }


    public function check_client(Request $request)
    {
        $client_id = $request->client_id;
        $client = $this->getOrCreateClient($client_id);

        if (!$client) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Client not found!']);
        }

        return response(['case' => 'success', 'client' => $client]);
    }

    private function getOrCreateClient($client_id)
    {
        $cacheKey = 'client_' . $client_id;
        $cacheDuration = now()->addMinutes(10);


        $client = Cache::get($cacheKey);

        if ($client === null) {
           // dd($client);
            $client = $this->client_control($client_id);

            if ($client) {

                Cache::put($cacheKey, $client, $cacheDuration);
            }
        }

        return $client;
    }

    private function client_control($client_id)
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


    public function get_check_package(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'number' => ['required', 'string', 'max:255'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
        }

        $user_location_id = $request->destination_id == null ? Auth::user()->location() : $request->destination_id;


        $response = $this->check_package($request, $user_location_id);

        return $response;
    }

    private function check_package(Request $request, $departure_id)
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
           // dd($exception);
            return 0;
        }

    }

}