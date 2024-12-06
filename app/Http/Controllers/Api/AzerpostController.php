<?php

namespace App\Http\Controllers\Api;

use App\CourierOrders;
use App\PackageStatus;
use App\Status;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AzerpostController extends Controller
{
    public function update_status(Request $request)
    {
        try {
            //throw new \Exception("Bir hata oluştu!");
            /*DB::table('azerpost_log')->insert([
                'type' => 'webhook',
                'request' => json_encode($request->all()),
                'created_at' => Carbon::now()
            ]);*/

            $packages = $request['packages'];
            $azerpost_status = $request['status_id'];

            $courierOrders = CourierOrders::whereIn('azerpost_track', $packages)->get();
            $combinedPackageIds = $courierOrders->pluck('packages')->toArray();

            $separatedStrings = array_merge(...array_map(function($value) {
                return explode(",", $value);
            }, $combinedPackageIds));

            $status = Status::where('azerpost_status', $azerpost_status)->first();

            $updateDataPackage = [
                'last_status_id' => $status->id,
                'last_status_date' => Carbon::now(),
            ];

            if ($azerpost_status == 5) {
                $updateDataPackage['delivered_by'] = 1;
                $updateDataPackage['delivered_at'] = Carbon::now();
            }

            DB::table('package')->whereIn('id', $separatedStrings)->update($updateDataPackage);

            $packageStatuses = array_map(function ($packageId) use ($status) {
                return [
                    'package_id' => $packageId,
                    'status_id' => $status->id,
                    'created_by' => 1,
                    'created_at' => Carbon::now()
                ];
            }, $separatedStrings);

            PackageStatus::insert($packageStatuses);

            $updateData = [
                'last_status_id' => $status->id,
                'last_status_date' => Carbon::now(),
            ];

            if ($azerpost_status == 6) {
                $updateData['canceled_by'] = 1;
                $updateData['canceled_at'] = Carbon::now();
            }

            DB::table('courier_orders')->whereIn('id', $courierOrders->pluck('id')->toArray())->update($updateData);
    
            $processedPackages = $this->processPackages($request);
            DB::table('azerpost_log')->insert([
                'type' => 'webhook',
                'request' => json_encode($request->all()),
                'response' => json_encode($processedPackages),
                'created_at' => Carbon::now()
            ]);
    
            $missingPackages = array_diff($packages, $courierOrders->pluck('azerpost_track')->toArray());
            $missingPackageCount = count($missingPackages);
            $totalPackageCount = count($packages);
    
            if ($missingPackageCount > 0) {
                return response([
                    'case' => 'error',
                    'error_packages' => array_values($missingPackages),
                    'total_packages_sent' => $totalPackageCount,
                    'missing_packages_count' => $missingPackageCount
                ], Response::HTTP_BAD_REQUEST);
            }

            return response(['case' => 'success', 'content' => 'Success'], Response::HTTP_OK);
        }catch (\Exception $exception){
            return response(['case' => 'error', 'content' => 'Error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
    
    private function processPackages(Request $request) {
        $packages = $request['packages'];
        $courierOrders = CourierOrders::whereIn('azerpost_track', $packages)->pluck('azerpost_track')->toArray();
        $processedPackages = [];
        foreach ($packages as $package) {
            $processedPackages[$package] = in_array($package, $courierOrders);
        }
        return $processedPackages;
    }
}
