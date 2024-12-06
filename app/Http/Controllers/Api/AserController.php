<?php

namespace App\Http\Controllers\Api;

use App\Category;
use App\ChangeAccountLog;
use App\Container;
use App\Countries;
use App\Currency;
use App\EmailListContent;
use App\ExchangeRate;
use App\Flight;
use App\Http\Controllers\Classes\Collector;
use App\Http\Controllers\Classes\SMS;
use App\Http\Controllers\CollectorReportsController;
use App\Item;
use App\Jobs\CollectorInWarehouseJob;
use App\Location;
use App\Package;
use App\PackageFiles;
use App\PackageStatus;
use App\Platform;
use App\Position;
use App\Seller;
use App\Services\AserCollector;
use App\Services\AserFlight;
use App\Services\AserReportService;
use App\Settings;
use App\SmsTask;
use App\Status;
use App\TariffType;
use App\TrackingLog;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CollectorController;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AserController extends Controller
{
    private $aserCollector;
    private $collectorService;
    private $collectorFlightService;
    private $reports;
    public function __construct(Request $request, AserCollector $collectorService, AserFlight $aserFlight, AserReportService $report)
    {
        $aserCollector = User::where('token', $request->header('token'))->first();

        $this->aserCollector = $aserCollector;
        $this->collectorService = $collectorService;
        $this->collectorFlightService = $aserFlight;
        $this->reports = $report;
    }

    public function check_client(Request $request)
    {
        Log::info([
            'check_client',
            $request->all(),
            $request->ip()
        ]);

        $validator = Validator::make($request->all(), [
            'client_id' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
        }

        if($request->get('client_id') == 0){
            return response()->json(
                [
                    'message' => 'unknow_user'
                ], Response::HTTP_BAD_REQUEST);
        }

        $request->merge([
            'departure_id'=>$this->aserCollector->destination_id,
            'client_id' => $request->get('client_id')
        ]);


        $client = $this->collectorService->check_client($request);

        if(!$client->original['client']){
            Log::info([
                'check_client_response',
                'client_not_found',
                $request->all(),
                $request->ip()
            ]);
            return response()->json(
                [
                    'message' => 'client_not_found'
                ], Response::HTTP_NOT_FOUND);
        };

        Log::info([
            'check_client_response',
            $client->original['case'],
            $client->original['client'],
            $request->ip()
        ]);
        return $client;
    }


    public function check_package(Request $request)
    {
        Log::info([
            $request->all(),
            'check_package',
            filter_input(INPUT_SERVER, 'REMOTE_ADDR'),
            $request->ip()
        ]);
        $request->merge([
            'destination_id'=>$this->aserCollector->destination_id,
            'number' => $request->get('number')
        ]);

        $package = $this->collectorService->get_check_package($request);


        if(!$package->original['package']){
            return response()->json(
                [
                    'message' => 'package_not_found'
                ], Response::HTTP_NOT_FOUND);
        };
        return $package;
    }

    public function position()
    {
        Log::info([
            'position',
            filter_input(INPUT_SERVER, 'REMOTE_ADDR'),
        ]);
        $positions = Position::where('location_id', $this->aserCollector->location())
            ->whereNull('deleted_by')
            ->orderBy('name')
            ->select('id', 'name')
            ->get();

        return $positions;

    }

    public function categories()
    {
        Log::info([
            'categories',
            filter_input(INPUT_SERVER, 'REMOTE_ADDR'),
        ]);
        $categories = Category::whereNull('deleted_by')
            ->orderBy('name_en')
            ->where('country_id',  '!=', 10)
            ->orWhereNull('country_id')
            ->select('id','name_en as name')
            ->get();

        return $categories;

    }

    public function seller()
    {
        Log::info([
            'seller',
            filter_input(INPUT_SERVER, 'REMOTE_ADDR'),
        ]);
        $sellers = Seller::whereNull('deleted_by')
            ->where('only_collector', 0)
            ->orderBy('name')
            ->select('id', 'name')
            ->get();

        return $sellers;

    }

    public function types()
    {
        Log::info([
            'types',
            filter_input(INPUT_SERVER, 'REMOTE_ADDR'),
        ]);
        $types = TariffType::whereNull('deleted_by')
            ->orderBy('name_en')
            ->select('id', 'name_en as name')
            ->get();

        return $types;

    }

    public function statuses()
    {
        Log::info([
            'statuses',
            filter_input(INPUT_SERVER, 'REMOTE_ADDR'),
        ]);
        $statuses = Status::whereNull('deleted_by')->where('for_collector', 1)
            ->select('id', 'status_en as status')
            ->get();

        return response()->json([
            'invoice_status' => [
                'no_invoice' => 1,
                'incorrect_invoice' => 2,
                'invoice_available' => 3,
                'invoice_uploaded' => 4
            ],
            'statuses' => $statuses
        ]);

    }

    public function currencies()
    {
        Log::info([
            'currensies',
            filter_input(INPUT_SERVER, 'REMOTE_ADDR'),
        ]);
        $currencies = Currency::whereNull('deleted_by')
            ->select('id', 'name')
            ->get();

        return $currencies;

    }


    public function add_collector(Request $request, $api = false, $user_id = 0, $departure_id = 0)
    {
        Log::info([
            $request->all(),
            'add_collector',
            filter_input(INPUT_SERVER, 'REMOTE_ADDR'),
        ]);
        //dd($request->all());
        $validator = Validator::make($request->all(), [
            //package
            'number' => ['required', 'string', 'max:255'],
            'tracking_internal_same' => ['nullable', 'integer'],
            'length' => ['nullable', 'integer'],
            'height' => ['nullable', 'integer'],
            'width' => ['nullable', 'integer'],
            'client_id' => ['nullable', 'integer'],
            'client_name_surname' => ['nullable', 'string', 'max:255'],
            'seller' => ['nullable', 'integer'], //seller_id
            'destination' => ['required', 'string', 'max:50'],
            'gross_weight' => ['required'],
            'currency' => ['nullable', 'integer'], //currency_id
            'status_id' => ['required', 'integer'],
            'tariff_type_id' => ['required', 'integer'],
            'description' => ['nullable', 'string', 'max:5000'],
            //item
            'category' => ['nullable', 'integer'], //category_id
            'invoice' => ['nullable'],
            'quantity' => ['required', 'integer'],
            //tracking log
            'container_id' => ['nullable', 'integer'],
            'position' => ['nullable', 'integer'], //position_id
            //images
            'total_images' => ['nullable', 'integer'],
            'is_legal_entity' => 'in:true,false',
            'invoice_status' => ['required', 'integer'],
            'subCat' => ['nullable', 'string', 'max:1000']
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Validation!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
        }

        $request->merge([
            'collector_departure'=>$this->aserCollector->destination_id,
            'collector_user' => $this->aserCollector->id,
        ]);

        return $this->collectorService->collectorService($request, $api = false, $user_id = 0, $departure_id = 0);

    }

    public function get_internal_id()
    {
        try {
            return $this->collectorService->get_internal_id();
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    /*Aser Flight region*/

    public function GetFlights(Request $request)
    {
        Log::info([
            $request->all(),
            'get_flights',
            filter_input(INPUT_SERVER, 'REMOTE_ADDR'),
        ]);
        $request->merge([
            'collector' => $this->aserCollector
        ]);
        return $this->collectorFlightService->show($request);
    }

    public function SelectFlights(Request $request)
    {
        Log::info([
            $request->all(),
            'selectFlights',
            filter_input(INPUT_SERVER, 'REMOTE_ADDR'),
        ]);
        $request->merge([
            'collector' => $this->aserCollector
        ]);
        return $this->collectorFlightService->flights($request);
    }

    public function GetSingleFlight($id)
    {
        Log::info([
            $id,
            'get_single_flights',
            filter_input(INPUT_SERVER, 'REMOTE_ADDR'),
        ]);
        try {
            $flights = Flight::whereNull('deleted_by')
                ->where('id', $id)
                ->where('location_id', $this->aserCollector->destination_id)
                ->orderBy('id', 'DESC')
                ->select(
                    'id',
                    'name',
                    'carrier',
                    'flight_number',
                    'awb',
                    'departure',
                    'destination',
                    'closed_at'
                )->first();

            if(!$flights){
                return response(['case' => 'error', 'content' => 'Flight not found'], Response::HTTP_NOT_FOUND);
            }else{
                return $flights;
            }

        } catch (\Exception $exception) {
            //dd($exception);
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function CreateFlight(Request $request)
    {
        Log::info([
            $request->all(),
            'create_flight',
            filter_input(INPUT_SERVER, 'REMOTE_ADDR'),
        ]);
        $validator = Validator::make($request->all(), [
            'carrier' => ['required', 'string', 'max:3'],
            'flight_number' => ['required', 'date'],
            'awb' => ['nullable', 'string', 'max:15'],
            'departure' => ['required', 'string', 'max:50'],
            'destination' => ['required', 'string', 'max:50'],
            'count' => ['required', 'integer', 'min:1'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
        }

        $request->merge([
            'departure_id'=>$this->aserCollector->destination_id,
            'collector' => $this->aserCollector
        ]);

        return $this->collectorFlightService->addFlight($request);

    }

    public function UpdateFlight(Request $request, $id)
    {
        Log::info([
            $request->all(),
            'upd_flight',
            filter_input(INPUT_SERVER, 'REMOTE_ADDR'),
        ]);
        $validator = Validator::make($request->all(), [
            'carrier' => ['required', 'string', 'max:3'],
            'flight_number' => ['required', 'date'],
            'awb' => ['nullable', 'string', 'max:15'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
        }

        Flight::findOrFail($id);

        return $this->collectorFlightService->update($request);

    }

    public function close(Request $request)
    {
        Log::info([
            $request->all(),
            'close_flight',
            filter_input(INPUT_SERVER, 'REMOTE_ADDR'),
        ]);
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Flight not found!'], Response::HTTP_NOT_FOUND);
        }

        $request->merge([
            'collector' => $this->aserCollector
        ]);

        return $this->collectorFlightService->close($request);
    }


    public function GetContainers(Request $request)
    {
        Log::info([
            $request->all(),
            'get_containers',
            filter_input(INPUT_SERVER, 'REMOTE_ADDR'),
        ]);
        $request->merge([
            'collector' => $this->aserCollector
        ]);
        return $this->collectorFlightService->showContainer($request);

    }

    public function createContainer(Request $request)
    {
        Log::info([
            $request->all(),
            'create_cont',
            filter_input(INPUT_SERVER, 'REMOTE_ADDR'),
        ]);
        $validator = Validator::make($request->all(), [
            'flight_id' => 'required|integer',
            'count' => ['required', 'integer', 'min:1'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Flight not found!']);
        }
        $request->merge([
            'public'=> 0,
            'collector' => $this->aserCollector,
        ]);

        $container = $this->collectorFlightService->create_container($request);

        return \response(['case' => 'success', 'container' => $container]);
    }

    public function PackageAddContainer(Request $request)
    {
        Log::info([
            $request->all(),
            'package_add_container',
            filter_input(INPUT_SERVER, 'REMOTE_ADDR'),
        ]);
        $validator = Validator::make($request->all(), [
            'track' => ['required', 'string', 'max:255'],
            'container' => ['required', 'string', 'max:100'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
        }


        $request->merge([
            'collector' => $this->aserCollector,
        ]);

        return $this->collectorService->change_position($request);

    }

    public function CollectorSearchPackage(Request $request)
    {
        Log::info([
            $request->header('token'),
            $request->all(),
            'collector_search',
            filter_input(INPUT_SERVER, 'REMOTE_ADDR'),
        ]);
        $request->merge([
            'collector' => $this->aserCollector,
        ]);

        return $this->collectorService->collector_search_packages($request);
    }

    public function waybill(Request $request)
    {
        Log::info([
            $request->all(),
            'waybill',
            filter_input(INPUT_SERVER, 'REMOTE_ADDR'),
        ]);
        $request->merge([
            'collector' => $this->aserCollector
        ]);

        return $this->collectorService->createPDF($request);
    }

    public function ReportAllPackage(Request $request, $type)
    {
        Log::info([
            $request->all(),
            'report_all',
            filter_input(INPUT_SERVER, 'REMOTE_ADDR'),
        ]);
        $request->merge([
            'collector' => $this->aserCollector
        ]);
        return $this->reports->post_reports($request, $type);
    }

    public function AddPosition(Request $request) {

        Log::info([
            $request->all(),
            'add_postition',
            filter_input(INPUT_SERVER, 'REMOTE_ADDR'),
        ]);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:50'],
            'partner_position_id' => ['required', 'int', Rule::unique('position', 'partner_position_id')]
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()], Response::HTTP_BAD_REQUEST);
        }
        try {

            $request->merge([
                'created_by' => $this->aserCollector->id,
                'location_id' => $this->aserCollector->location()
            ]);

            Position::create($request->all());

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function UpdatePosition(Request $request, $id) {
        Log::info([
            $request->all(),
            'update_position',
            filter_input(INPUT_SERVER, 'REMOTE_ADDR'),
        ]);
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:50'],
            'partner_position_id' => ['required', 'int']
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()], Response::HTTP_BAD_REQUEST);
        }
        try {

                $position = Position::where('partner_position_id', $id)->first();


                if($position){
                    $request->merge([
                        'created_by' => $this->aserCollector->id,
                        'location_id' => $this->aserCollector->location()
                    ]);

                    //dd($request->all());
                    $position->update($request->all());

                    return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);

                }else{
                    return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Position not found!'], Response::HTTP_NOT_FOUND);

                }

        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function check_package_status()
    {
        Log::info([
            'check_package_status',
            filter_input(INPUT_SERVER, 'REMOTE_ADDR'),
        ]);
        try
        {
                $package = Package::leftJoin('lb_status as statuses', 'statuses.id', 'package.last_status_id')
                    ->where('package.departure_id', $this->aserCollector->location())
                    ->whereNotIn('in_baku', [1]);


                $datas = $package->orderByDesc('package.id')
                    ->select([
                        'statuses.status_en as current_status',
                        'package.internal_id as internal_id',
                        'package.number as track_number',
                        'package.carrier_status_id as carrier_status_id',
                        'package.last_status_id as current_status_id'
                    ])
                    ->get();

                $arr = [];
                $carrier_status_id = '';
                foreach($datas as $data){
                    switch ($data->carrier_status_id) {
                        case '0':
                            {
                                $carrier_status_id = "Not Send";
                            }
                            break;
                        case '1':
                            {
                                $carrier_status_id = "Debt SC";
                            }
                            break;
                        case '2':
                            {
                                $carrier_status_id = "Declared";
                            }
                            break;
                        case '4':
                            {
                                $carrier_status_id = "Posted to Custom";
                            }
                            break;
                        case '7':
                            {
                                $carrier_status_id = "Add to Box";
                            }
                            break;
                        case '8':
                            {
                                $carrier_status_id = "Depesh";
                            }
                            break;
                        case '10':
                            {
                                $carrier_status_id = "Commercial";
                            }
                            break;

                        default:
                        {
                            $carrier_status_id = "Unknow error";
                        }
                    }


                    $response = [
                        "internal_id" => $data->internal_id,
                        "track_number" => $data->track_number,
                        "current_status" => $data->current_status,
                        "current_status_id" => $data->current_status_id,
                        "carrier_status" => $carrier_status_id,
                        'carrier_status_id' => $data->carrier_status_id
                    ];

                    array_push($arr, $response);
                }
                return $arr;

        }
        catch(\Exception $exception)
        {
            //dd($exception);
            return response()->json([
                'message' => 'An error occurred!',
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function change_package_position(Request $request){

        Log::info([
            $request->all(),
            'update_package_position',
            filter_input(INPUT_SERVER, 'REMOTE_ADDR'),
        ]);
        $validator = Validator::make($request->all(), [
            'track' => ['required', 'string', 'max:255'],
            'position_id' => ['required', 'int']
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()], Response::HTTP_BAD_REQUEST);
        }

        try {
            $position = Position::where('id', $request->position_id)
                ->where('location_id', $this->aserCollector->destination_id)
                ->first();

            if ($position == null){
                return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Position not found'], Response::HTTP_NOT_FOUND);
            }

            $package = Package::whereIn('last_status_id', [36, 37, 38, 39, 40, 41])
                ->where('departure_id', $this->aserCollector->destination_id)
                ->where(function ($query) use ($request) {
                    $trackValue = $request->track;
                    $query->where('package.number', $trackValue)
                        ->orWhere('package.internal_id', $trackValue);
                })
                ->first();

            if ($package == null){
                return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Cannot change position!'], Response::HTTP_NOT_FOUND);
            }

            $package->update([
                'position_id' => $position->id
            ]);

            $array = [
                'package_id'=>$package->id,
                'operator_id' => $this->aserCollector->id,
                'position_id' => $position->id,
                'created_by' =>  $this->aserCollector->id,
            ];

            TrackingLog::create($array);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            //dd($exception);
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }
    
    public function check_otp_code(Request $request)
    {
        try {
            
            $track = $request->input('track');
            $result = DB::table('seller_otp')->where('otp_text', $track)
                ->select('otp_text', 'otp_code')
                ->first();
            
            return response()->json(
                [
                    'message' => 'success',
                    'data' => $result
                ], Response::HTTP_OK);
            
        }catch (\Exception $e){
            return 'Error';
        }
    
    }

}
