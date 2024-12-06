<?php

/**
 * STATUSES (carrier_status_id)
 *  1 = Success pay
 *  2 = No needs to payment
 *  3 = Debt at Smart Customs
 *  4 = Successfully posted to Smart Customs
 *  5 = Package not sent
 *  6 = Ready to AddToBox
 *  7 = Added to Box
 *  8 = Depesh (Delivered)
 *  9 = Client Data Changed (will resend)
 */

namespace App\Services;

use App\CarrierRequestsLog;
use App\Countries;
use App\Currency;
use App\CustomCategory;
use App\ExchangeRate;
use App\Item;
use App\Jobs\UpdatePackageCarrierStatus;
use App\Package;
use App\PackageCarrierStatusTracking;
use App\PackageStatus;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Class Carrier
 * @package App\Services
 */
class Carrier
{
    /**
     * Default currency type id (USD)
     */
    const DEFAULT_CURRENCY_TYPE = 840;
    /**
     * @var string
     */
    private string $url;
    /**
     * @var Client
     */
    private Client $http;
    /**
     * @var array
     */
    private array $headers;
    /**
     * @var string
     */
    private string $proxy;
    /**
     * @var int
     */
    private int $timeout;
    /**
     * @var int
     */
    private int $connectTimeout;
    /**
     * @var array
     */
    private array $options;
    /**
     * @var Countries[]|Collection
     */
    private $countries;
    /**
     * @var Currency[]|Collection
     */
    private $currency;

    /**
     * Carrier constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->url = 'https://ecarrier-fbusiness.customs.gov.az:7545';
        $this->http = $client;
        $this->headers = [
            'cache-control' => 'no-cache',
            'ApiKey' => '95HH16GDAC070D3TP68BC1E22G837866RB5AADV3',
            'Accept'        => 'application/json',
            "Content-Type"  => "application/json"
        ];
        $this->proxy = '127.0.0.1:8081';
        $this->timeout = 120;
        $this->connectTimeout = 5;
        $this->options = [
            'headers' => $this->headers,
            // 'proxy' => $this->proxy,
            'timeout' => $this->timeout,
            'connect_timeout' => $this->connectTimeout,
            'http_errors' => true
        ];
	    $this->countries = (Schema::hasTable('countries')) ? Countries::all() : null;
	    $this->currency = (Schema::hasTable('currency')) ? Currency::all() : null;
    }

    /**
     * @return bool
     */
    public function ping(): bool
    {
        $url = $this->url . '/api/v2/carriers/Ping';
        $request = $this->http->get($url, $this->options);
        $status = $request ? $request->getStatusCode() : 500;

        return $status == 200;
    }

    /**
     * @param $packages
     * @return void|JsonResponse
     */
    public function addPackages($packages): void
    {
        $data = [];
        $packageIds = [];
        $packageIdsError = [];
        $url = $this->url . '/api/v2/carriers';
        foreach ($packages as $package) {
            if (!$package['tracking_id'] or !$package['fin']) {
                continue;
            }
            if (!$package['shipping_amount']) {
                continue;
            }
            if ($package['fin'] == null) {
                continue;
            }
            $data[] = [
                'direction' => 1,
                'tracking_no' => $package['tracking_id'],
                'transp_costs' => $package['shipping_amount'],
                'weight_goods' => $package['weight'],
                'quantity_of_goods' => $package['quantity'],
                'invoys_price' => $package['invoice_price'],
                'currency_type' => $this->currency->where('id', $package['currency_id'])
                                                        ->first()->code ?? self::DEFAULT_CURRENCY_TYPE,
                'fin' => $package['fin'],
                'document_type' => $package['document_type'],
                'idxal_name' => mb_substr($package['import_name'], 0, 30),
                'idxal_adress' => $package['import_address'] ?? '---',
                'ixrac_name' => mb_substr($package['export_name'] ?? '---', 0, 30),
                'ixrac_adress' => $package['export_address'] ?? '---',
                'phone' => $package['phone'],
                'goods_traffic_fr' => $this->countries->where('id', $package['goods_fr_id'])->first()->goods_fr,
                'goods_traffic_to' => $package['goods_to_id'],
                'goodslist' => $package['goodslist']
            ];
        }

        $this->options['body'] = json_encode($data);
        $this->options['content-type'] = 'application/json';
        // dd($data);
        try {
            if (count($data) != 0) {
	            $request = $this->http->post($url, $this->options);
         
	            if ($request->getStatusCode() == 200) {
	                $response = json_decode($request->getBody()->getContents(), true);

                    
	                foreach ($response['data'] as $key => $package) {
                   
                        if($package == 200){
                            $packageIds[] = $key;
                        }
                     
                        // if (isset($package['trackinG_NO'])) {
                        //     $packageIds[] = $package['trackinG_NO'];
                        //     dd($packageIds);
                        // }
                    }

	                if ($packageIds) {
                            dispatch(new UpdatePackageCarrierStatus($packageIds, 4))
                                ->delay(Carbon::now()->addSeconds(10));
                    }
                    CarrierRequestsLog::create([
                        'code' => $request->getStatusCode(),
                        'request' => $this->options['body'],
                        'response' => null,
                        'message' => 'successfully_added_to_customs'
                    ]);
                }
            }
        } catch (RequestException $exception) {
            if ($response = $exception->getResponse()) {
                $code = $response->getStatusCode();
                $response = $exception->getResponse();
                $body = $response ? $response->getBody() : null;
                $contents = $body ? $body->getContents() : null;
                $error = json_decode($contents, true);
                $errorMessage = isset($error['exception']) ? json_encode($error['exception']) : null;
                $packages = json_decode($contents, true);

                foreach ($packages['exception']['validationError'] as $errKey => $errValue) {
                    if($errValue == "200"){
                        $packageIdsError[] = $errKey;
                    }
                }
           
                CarrierRequestsLog::create([
                    'code' => $code,
                    'request' => $this->options['body'],
                    'response' =>  $contents,
                    'message' => $errorMessage
                ]);

                switch ($code) {
                    case 400:
                        {
                            if ($packageIdsError) {
                                dispatch(new UpdatePackageCarrierStatus($packageIdsError, 4))
                                    ->delay(Carbon::now()->addSeconds(10));
                            }
                        }
                        break;
                }
            } else {
                Log::error('customs_connection_fail', [
                    'message' => $exception->getMessage(),
                    'line' => $exception->getLine(),
                    'file' => $exception->getFile()
                ]);
            }
        }
    }

    /**
     * @param $packagesCoomercial
     * @return void|JsonResponse
     */
    public function addCommercialPackages($packages): void
    {
        $data = [];
        $packageIds = [];
        $packageIdsError = [];
        $url = $this->url . '/api/v2/carriers/commercial';
        foreach ($packages as $package) {
            if (!$package['tracking_id'] or !$package['voen']) {
                continue;
            }
            if (!$package['shipping_amount']) {
                continue;
            }
            if ($package['fin'] == null) {
                continue;
            }
            $data[] = [
                'direction' => 1,
                'tracking_no' => $package['tracking_id'],
                'transp_costs' => $package['shipping_amount'],
                'weight_goods' => $package['weight'],
                'quantity_of_goods' => $package['quantity'],
                'invoys_price' => $package['invoice_price'],
                'currency_type' => $this->currency->where('id', $package['currency_id'])
                                                        ->first()->code ?? self::DEFAULT_CURRENCY_TYPE,
                'fin' => $package['fin'],
                'voen' => $package['voen'],
                'airwaybill' => $package['airWayBill'],
                'depesh_number' => $package['depeshNumber'],
                'document_type' => $package['document_type'],
                'idxal_name' => mb_substr($package['import_name'], 0, 30),
                'idxal_adress' => $package['import_address'] ?? '---',
                'ixrac_name' => mb_substr($package['export_name'] ?? '---', 0, 30),
                'ixrac_adress' => $package['export_address'] ?? '---',
                'phone' => $package['phone'],
                'goods_traffic_fr' => $this->countries->where('id', $package['goods_fr_id'])->first()->goods_fr,
                'goods_traffic_to' => $package['goods_to_id'],
                'goodslist' => $package['goodslist']
            ];
        }

        $this->options['body'] = json_encode($data);
        $this->options['content-type'] = 'application/json';
        //dd($data);
        try {
            if (count($data) != 0) {
                $request = $this->http->post($url, $this->options);
         
	            if ($request->getStatusCode() == 200) {
	                $response = json_decode($request->getBody()->getContents(), true);

	                foreach ($response['data'] as $key => $package) {
                   
                        if($package == 200){
                            $packageIds[] = $key;
                        }
                    }

	                if ($packageIds) {
                        dispatch(new UpdatePackageCarrierStatus($packageIds, 10))
                            ->delay(Carbon::now()->addSeconds(10));
                        CarrierRequestsLog::create([
                            'code' => $request->getStatusCode(),
                            'request' => $this->options['body'],
                            'response' => $response,
                            'message' => 'depesh_commercial',
                        ]);
                    }
                }
            }
        } catch (RequestException $exception) {
            if ($response = $exception->getResponse()) {
                $code = $response->getStatusCode();
                $response = $exception->getResponse();
                $body = $response ? $response->getBody() : null;
                $contents = $body ? $body->getContents() : null;
                $error = json_decode($contents, true);
                $errorMessage = isset($error['exception']) ? json_encode($error['exception']) : null;
                $packages = json_decode($contents, true);

                foreach ($packages['exception']['validationError'] as $errKey => $errValue) {
                    if($errValue == "200"){
                        $packageIdsError[] = $errKey;
                    }
                }
           
                CarrierRequestsLog::create([
                    'code' => $code,
                    'request' => $this->options['body'],
                    'response' =>  $contents,
                    'message' => $errorMessage
                ]);

                switch ($code) {
                    case 400:
                        {
                            if ($packageIdsError) {
                                dispatch(new UpdatePackageCarrierStatus($packageIdsError, 10))
                                    ->delay(Carbon::now()->addSeconds(10));
                            }
                        }
                        break;
                }
            } else {
                Log::error('customs_connection_fail', [
                    'message' => $exception->getMessage(),
                    'line' => $exception->getLine(),
                    'file' => $exception->getFile()
                ]);
            }
        }
    }

    /**
     * @return array[]
     */
    public function getAddedPackages(): array
    {
        $url = $this->url . '/api/v2/carriers/carriersposts/0/100';
        $this->options['body'] = json_encode([
            'dateFrom' => '2021-01-05T00:00:20.906Z',
            'dateTo' => '2021-01-12T23:59:20.906Z'
        ]);

        $request = $this->http->post($url, $this->options);

        $response = json_decode($request->getBody()->getContents(), true);

        $packageIds = [];

        foreach ($response['data'] as $package) {
            $packageIds[] = $package['trackinG_NO'];
        }

        return [
            'packages' => $packageIds
        ];
    }

    /**
     * @param null $pinNumber
     * @param null $trackingId
     * @param null $dateFrom
     * @param null $dateTo
     * @return mixed|null
     */
    public function getDeclarations($pinNumber = null, $trackingId = null, $dateFrom = null, $dateTo = null)
    {
        $url = $this->url . '/api/v2/carriers/declarations/0/100';
        $this->options['body'] = json_encode([
            'dateFrom' => null,
            'dateTo' => null,
            'pinNumber' => null,
            'trackingNumber' => null
        ]);
                
        // json_encode([
        //     'dateFrom' => $dateFrom ?? Carbon::now()->subDays(14),
        //     'dateTo' => $dateTo ?? Carbon::now()->setTimezone('+4')->endOfDay(),
        //     'pinNumber' => $pinNumber,
        //     'trackingNumber' => $trackingId
        // ]);
        
        $request = $this->http->post($url, $this->options);
        $response = json_decode($request->getBody()->getContents(), true);

        if (!isset($response['data'])) {
            return null;
        }

        return $response['data'];
    }

    /**
     * @param null $pinNumber
     * @param null $trackingId
     * @return mixed|null
     */
    public function getDeletedDeclarations($pinNumber = null, $trackingId = null)
    {
        $url = $this->url . '/api/v2/carriers/deleteddeclarations/0/100';
        $this->options['body'] = json_encode([
            'dateFrom' => null,
            'dateTo' => null,
            'pinNumber' => null,
            'trackingNumber' => null
        ]);

        $request = $this->http->post($url, $this->options);
        $response = json_decode($request->getBody()->getContents(), true);

        if (!isset($response['data'])) {
            return null;
        }

        return $response['data'];
    }

    /**
     * @param null $pinNumber
     * @param null $trackingId
     * @return void
     */
    public function checkPackagesStatuses($pinNumber = null, $trackingId = null): void
    {
        try {
            $regNumbers = [];
            $deletedRegs = [];
            $deletedRegsArray = [];
            $statusesArray = [];
            $declarations = $this->getDeclarations($pinNumber, $trackingId);
            sleep(2);
            $deletedDeclarations = $this->getDeletedDeclarations($pinNumber, $trackingId);
            foreach ($deletedDeclarations as $deletedDeclaration) {
                $deletedRegs[] = $deletedDeclaration['REGNUMBER'];
            }
            if (count($deletedRegs) >= config('customs.approve.max')) {
                $deletedRegsArray = array_chunk($deletedRegs, config('customs.approve.max'));
            } else {
                $deletedRegsArray[] = $deletedRegs;
            }
            foreach ($deletedRegsArray as $array) {
                sleep(2);
                $this->approveSearch($array, 'DeletedDeclarations');
            }
            if (count($deletedRegs)) {
                $packagesIds = Package::whereIn('carrier_registration_number', $deletedRegs)
                    ->pluck('id');
                Package::whereIn('carrier_registration_number', $deletedRegs)->update([
                    'carrier_status_id' => 4,
                    'carrier_registration_number' => 1,
                    'last_status_id' => 37
                ]);
                PackageStatus::whereIn('package_id', $packagesIds)
                    ->whereIn('status_id', config('customs.package.package_statuses'))
                    ->delete();
                foreach ($packagesIds as $id) {
                    $statusesArray[] = [
                        'package_id' => $id,
                        'carrier_status_id' => 4,
                        'note' => 'deleted_declaration',
                        'created_at' => Carbon::now()
                    ];
                }
                if (count($statusesArray)) {
                    PackageCarrierStatusTracking::insert($statusesArray);
                }
            }
            foreach ($declarations as $declaration) {
                switch ($declaration['payStatus_Id']) {
                    case 3:
                        $statusId = 38;
                        break;
                    case 1:
                        $statusId = 39;
                        break;
                    case 2:
                        $statusId = 40;
                        break;
                    default:
                        $statusId = 41; //default expression
                        break;
                }

                $package = Package::where('internal_id', $declaration['trackingNumber'])->first();
                if ($package) {
                    $rate = null;
                    $defaultRate = null;
                    $itemRate = null;
                    $itemCurrencyRate = null;
                    $itemDefaultRate = null;
                    $currencyRate = null;
                    if ($item = Item::where('package_id', $package->id)->first()) {
                        $currencyId = $item->currency_id;
                        $currencyFromCustoms = $this->currency->where('code', $declaration['goodsList'][0]['currencyType'])->first();
		                if ($currencyFromCustoms) {
                            $currencyIdFromCustoms = $currencyFromCustoms->id;
                        } else {
                            continue;
                        }
                        if ($currencyIdFromCustoms != 1) {
                            $date = Carbon::now();
                            $rate = ExchangeRate::whereDate('from_date', '<=', $date)
                                ->whereDate('to_date', '>=', $date)
                                ->where(['from_currency_id' => $currencyIdFromCustoms, 'to_currency_id' => 1]) //to USD
                                ->select('rate')
                                ->orderBy('id', 'desc')
                                ->first();
                        } else {
                            $defaultRate = 1;
                        }
                        if ($currencyId != 1) {
                            $date = Carbon::now();
                            $itemRate = ExchangeRate::whereDate('from_date', '<=', $date)
                                ->whereDate('to_date', '>=', $date)
                                ->where(['from_currency_id' => $currencyIdFromCustoms, 'to_currency_id' => $currencyId]) //to USD
                                ->select('rate')
                                ->orderBy('id', 'desc')
                                ->first();
                        } else {
                            $itemDefaultRate = 1;
                        }
                    }
                    if (isset($rate)) {
                        $currencyRate = $rate->rate;
                    } elseif (isset($defaultRate)) {
                        $currencyRate = 1;
                    } else {
                        $currencyRate = 1;
                    }
                    if (isset($itemRate)) {
                        $itemCurrencyRate = $itemRate->rate;
                    } elseif (isset($itemDefaultRate)) {
                        $itemCurrencyRate = 1;
                    } else {
                        $itemCurrencyRate = 1;
                    }
                    $totalInvoicePrice = 0;
                    $totalInvoicePriceUsd = 0;
                    $subGoodsUnicCode = '';
                    foreach ($declaration['goodsList'] as $items) {
                        $totalInvoicePrice += $items['invoicePrice'];
                        //  * $itemCurrencyRate;
                        $totalInvoicePriceUsd += $items['invoicePrice'] * $currencyRate;
                        $subGoodsUnicCode .= $items['subGoodsUnicCode'] .',';
                    }
                    $subGoodsUnicCode = substr($subGoodsUnicCode, 0, -1);
                    Item::where('package_id', $package->id)->update([
                        'price' => $totalInvoicePrice,
                        'price_usd' => $totalInvoicePriceUsd,
                        'currency_id' => $currencyIdFromCustoms,
                        // 'custom_cat_id' => $subGoodsUnicCode
                    ]);
                    Package::where('internal_id', $declaration['trackingNumber'])->update([
                        'carrier_status_id' => $declaration['payStatus_Id'],
                        'carrier_registration_number' => $declaration['regNumber']
                    ]);
                    PackageCarrierStatusTracking::create([
                        'package_id' => $package->id,
                        'internal_id' => $declaration['trackingNumber'],
                        'carrier_status_id' => $declaration['payStatus_Id'],
                        'carrier_registration_number' => $declaration['regNumber'],
                        'note' => 'declared'
                    ]);
                    PackageStatus::create([
                        'package_id' => $package->id,
                        'status_id' => $statusId,
                        'created_by' => 1
                    ]);
                }
                $regNumbers[] = $declaration['regNumber'];
            }

            if($package){
                if (count($regNumbers) >= config('customs.approve.max')) {
                    $regsArray = array_chunk($regNumbers, config('customs.approve.max'));
                } else {
                    $regsArray[] = $regNumbers;
                }
                foreach ($regsArray as $array) {
                    sleep(2);
                    $this->approveSearch($array);
                }
            }

        } catch (\Exception $exception) {
            Log::error('carrier_check_status_error', [
                'message' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
                'declarations' => isset($declarations) ? count($declarations) : null,
                'deletedDeclarations' => isset($deletedDeclarations) ? count($deletedDeclarations) : null
            ]);
        }
    }

    /**
     * @param $regNumbers
     * @param string $dataType
     * @return false|mixed
     */
    public function approveSearch($regNumbers, $dataType = 'Declarations'): void
    {
        if (!$regNumbers) {
            return;
        }

        $url = $this->url . '/api/v2/carriers/approvesearch';
        $body = [];

        foreach ($regNumbers as $regNumber) {
            $body[] = [
                'regNumber' => $regNumber
            ];
        }

        $this->headers['dataType'] = $dataType;
        $this->options['headers'] = $this->headers;
        $this->options['body'] = json_encode($body);

        try {
	        $this->http->post($url, $this->options);
        } catch (RequestException $exception) {
            if ($response = $exception->getResponse()) {
                if ($response->getStatusCode() == 422) {
                    Log::error('customs_approve_validation_error', [
                        'message' => $exception->getMessage(),
                        'response' => json_decode($response->getBody()->getContents(), true)
                    ]);
                }
            }
        }
    }

    /**
     * @param $packages
     * @return void
     */
    public function addToBoxes($packages): void
    {
        $url = $this->url . '/api/v2/carriers/addtoboxes';
        $body = [];
        $trackingNumbers = [];

        foreach ($packages as $package) {
            $body[] = [
                'regNumber' => $package['registration_number'],
                'trackingNumber' => $package['tracking_number']
            ];
            $trackingNumbers[] = $package['tracking_number'];
        }

        $this->options['body'] = json_encode($body);
        try {
            if (count($body) == 0) {
                return;
            }
            $request = $this->http->post($url, $this->options);

            if ($request->getStatusCode() == 200) {
                dispatch(new UpdatePackageCarrierStatus($trackingNumbers, 7))
                    ->delay(Carbon::now()->addSeconds(10));
                CarrierRequestsLog::create([
                    'code' => $request->getStatusCode(),
                    'request' => $this->options['body'],
                    'response' => $request->getBody()->getContents(),
                    'message' => 'added_to_box'
                ]);
            }
        } catch (RequestException $exception) {
            if ($response = $exception->getResponse()) {
                $code = $response->getStatusCode();
                $body = $response->getBody();
                $contents = $body ? $body->getContents() : null;
                $error = json_decode($contents, true);
                $errorMessage = isset($error['exception']) ? json_encode($error['exception']) : null;

                CarrierRequestsLog::create([
                    'code' => $code,
                    'request' => $this->options['body'],
                    'response' =>  $contents,
                    'message' => $errorMessage
                ]);
            }

            $response = json_decode($contents, true);
            $tracks = [];
            $notDeclared = [];

            if (is_array($response) and $response['code'] == 400) {
                if (isset($response['data'])) {
                    foreach ($response['data'] as $track => $code) {
                        if (($code == '200') or ($code == '048')) {
                            $tracks[] = $track;
                        } elseif ($code == '042') {
                            $notDeclared[] = $track;
                        }
                    }
                }
            }
            if ($tracks) {
                dispatch(new UpdatePackageCarrierStatus($tracks, 7))
                    ->delay(Carbon::now()->addSeconds(10));
            }
            if ($notDeclared) {
                Package::whereIn('internal_id', $notDeclared)->update([
                    'carrier_status_id' => 4,
                    'carrier_registration_number' => null
                ]);
            }
        }
    }

    /**
     * @param $packages
     * @return void
     */
    public function depesh($packages): void
    {
        $url = $this->url . '/api/v2/carriers/depesh';
        $body = [];
        $trackingNumbers = [];

        foreach ($packages as $item) {
            $body[] = [
                'regNumber' => $item['regNumber'],
                'trackingNumber' => $item['trackingNumber'],
                'airWaybill' => $item['airWayBill'],
                'depeshNumber' => $item['depeshNumber']
            ];
            $trackingNumbers[] = $item['trackingNumber'];
        }

        $this->options['body'] = json_encode($body);

        try {
	    if (count($body) == 0) {
		return;
            }
            $request = $this->http->post($url, $this->options);
            $response = $request->getBody()->getContents();

            if ($request->getStatusCode() == 200) {
                dispatch(new UpdatePackageCarrierStatus($trackingNumbers, 8))
                    ->delay(Carbon::now()->addSeconds(10));
                CarrierRequestsLog::create([
                    'code' => $request->getStatusCode(),
                    'request' => $this->options['body'],
                    'response' => $response,
                    'message' => 'depesh',
                ]);
            }
        } catch (RequestException $exception) {
            if ($response = $exception->getResponse()) {
                $code = $response->getStatusCode();
                $response = $exception->getResponse();
                $body = $response ? $response->getBody() : null;
                $contents = $body ? $body->getContents() : null;
                $error = json_decode($contents, true);
                $errorMessage = isset($error['exception']) ? json_encode($error['exception']) : null;

                CarrierRequestsLog::create([
                    'code' => $code,
                    'request' => $this->options['body'],
                    'response' =>  $contents,
                    'message' => $errorMessage
                ]);

                switch ($code) {
                    case 400:
                        {
                            $packages = json_decode($contents, true);
                            if (count($packages['data'])) {
                                foreach ($packages['data'] as $package => $code) {
                                    if (($code == '200') or ($code == '048')) {
                                        $tracks[] = $package;
                                    }
                                }
                                if (isset($tracks)) {
                                    dispatch(new UpdatePackageCarrierStatus($tracks, 8))
                                        ->delay(Carbon::now()->addSeconds(10));
                                }
                            }
                        }
                    break;
                }
            }
        } catch (\Exception $exception) {
            Log::error('depesh_fail', [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ]);
        }
    }

    /**
     * @param $regNumber
     * @param $fin
     * @param null $trackingNumber
     * @return bool
     */
    public function deleteDeclaration($regNumber, $fin, $trackingNumber = null): bool
    {
        if (!$regNumber or !$fin) {
            return false;
        }
        $url = $this->url . '/deleteDeclaration/';
        $url .= $regNumber . '/' . $fin;
        try {
            $request = $this->http->post($url, $this->options);

            if ($request->getStatusCode() == 200) {
                CarrierRequestsLog::create([
                    'code' => $request->getStatusCode(),
                    'request' => json_encode([
                        'url' => $url
                    ]),
                    'response' => $request->getBody()->getContents(),
                    'message' => 'delete_declaration'
                ]);
            }

            return true;
        } catch (RequestException $exception) {

            $response = $exception->getRequest();
            $body = $response ? $response->getBody() : null;
            $contents = $body ? $body->getContents() : null;
            CarrierRequestsLog::create([
                'code' => $exception->getCode(),
                'request' => json_encode([
                    'url' => $url
                ]),
                'response' => $contents,
                'message' => 'delete_declaration'
            ]);
        }

        return false;
    }

    /**
     * @param $trackingNumber
     * @return bool
     */
    public function deletePackage($trackingNumber): bool
    {
        if (!$trackingNumber) {
            return false;
        }

        $url = $this->url . '/api/v2/carriers/' . $trackingNumber;

        try {
            $request = $this->http->delete($url, $this->options);
            if ($request->getStatusCode() == 200) {
                CarrierRequestsLog::create([
                    'code' => $request->getStatusCode(),
                    'request' => json_encode([
                        'url' => $url
                    ]),
                    'response' => $request->getBody()->getContents(),
                    'message' => 'delete_package'
                ]);
            }

            return $request->getStatusCode() == 200;
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
            $body = $response ? $response->getBody() : null;
            $contents = $body ? $body->getContents() : null;
            $responseBody = json_decode($contents, true);
            $responseException = isset($responseBody['exception']) ? $responseBody['exception'] : null;
            $responseExceptionCode = isset($responseException['code']) ? $responseException['code'] : null;

            CarrierRequestsLog::create([
                'code' => $exception->getCode(),
                'request' => json_encode([
                    'url' => $url
                ]),
                'response' => $contents,
                'message' => 'delete_package'
            ]);

            if ($responseExceptionCode == '042') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $id
     * @return array|false
     */
    public function destroyPackage($id)
    {
        if (!$id) {
            return [
		'deleted' => false,
		'message' => 'package cannot be null',
		'id' => $id
	    ];
        }
        $package = Package::with('client')
            ->where('id', $id)
            ->first();
        if ($package) {
            $cdn = $package->getAttribute('carrier_registration_number');
            $carrierStatus = $package->getAttribute('carrier_status_id');

            if (in_array($carrierStatus, [7,8])) {
                return [
                    'deleted' => false,
                    'message' => 'Cant delete package in statuses: AddedToBox, Depesh'
                ];
            }
            if (!$package->client->passport_fin) {
                return [
                    'deleted' => false,
                    'message' => 'User has not correct fin number, cant resend to customs'
                ];
            }
            if (in_array($carrierStatus, config('customs.package.declaration_statuses'))) {
                $isDeletedDeclaration = $this->deleteDeclaration($cdn, $package->client->passport_fin);
                $isDeletedPackage = $this->deletePackage($package->getAttribute('internal_id'));
            } else {
                $isDeletedPackage = $this->deletePackage($package->getAttribute('internal_id'));
            }

            return [
                'deleted' => $isDeletedPackage,
                'message' => 'successfully deleted'
            ];
        }

        return [
            'deleted' => false,
            'message' => 'package not found'
        ];
    }


    public function getGroupList(){
        $url = 'https://ecarrier-fbusiness.customs.gov.az:7545/api/v2/carriers/goodsgroupslist';
        $this->options['content-type'] = 'application/json';

        try{
            $request = $this->http->get($url, $this->options);
       
    
            $response = json_decode($request->getBody()->getContents(), true);
    
            foreach ($response['data'] as $package) {
    
                $custom_category = CustomCategory::where('id', $package['id'])->first();
                
                    if($custom_category){
                        $custom_category->update([
                            'id' => $package['id'],
                            'parentId' => $package['parentId'],
                            'goodsNameAz' => $package['goodsNameAz'],
                            'goodsNameEn' => $package['goodsNameEn'],
                            'goodsNameRu' => $package['goodsNameRu'],
                            'isDeleted' => $package['isDeleted']
                        ]);
                    }else{
                        CustomCategory::create([
                            'id' => $package['id'],
                            'parentId' => $package['parentId'],
                            'goodsNameAz' => $package['goodsNameAz'],
                            'goodsNameEn' => $package['goodsNameEn'],
                            'goodsNameRu' => $package['goodsNameRu'],
                            'isDeleted' => $package['isDeleted']
                        ]);
        
                    }
                
            }

        }catch (RequestException $exception) {
            if ($response = $exception->getResponse()) {
                $code = $response->getStatusCode();
                $response = $exception->getResponse();
                $body = $response ? $response->getBody() : null;
                $contents = $body ? $body->getContents() : null;
                $error = json_decode($contents, true);
                $errorMessage = isset($error['exception']) ? json_encode($error['exception']) : null;
                $packages = json_decode($contents, true);
                CarrierRequestsLog::create([
                    'code' => $code,
                    'request' => $this->options['body'],
                    'response' =>  $contents,
                    'message' => $errorMessage
                ]);
            } else {
                Log::error('customs_connection_fail', [
                    'message' => $exception->getMessage(),
                    'line' => $exception->getLine(),
                    'file' => $exception->getFile()
                ]);
            }
        }

    }


    public function putAirWay(){

        $container = [
            6520, 6527, 6529, 6530, 6547, 6548, 6555
        ];

        $package = Package::whereIn('container_id', $container)->select('internal_id', 'container_id')->get();
        $arr = [];
        
        foreach($package as $pack){
            $data = [
                "trackinG_NO" => $pack->internal_id,
                "airwaybill" => "501-1150 0016",
                "depesH_NUMBER" => "CCN-" . (string)$pack->container_id
            ]; 
            array_push($arr, $data);
        }
  
        $encode = json_encode($arr);

        dd($encode);

    }

}

