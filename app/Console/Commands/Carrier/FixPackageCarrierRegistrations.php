<?php

namespace App\Console\Commands\Carrier;

use App\Package;
use App\PackageStatus;
use App\Services\Carrier;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixPackageCarrierRegistrations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carrier:fix-regs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    /**
     * @var Carrier
     */
    private Carrier $carrier;
    /**
     * @var int[]
     */
    private array $packageIds;

    /**
     * Create a new command instance.
     *
     * @param Carrier $carrier
     */
    public function __construct(Carrier $carrier)
    {
        parent::__construct();
        $this->carrier = $carrier;
        $this->packageIds = [210209];
    }



    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $packages = Package::whereIn('id', $this->packageIds)
            ->select(['id', 'internal_id', 'carrier_status_id', 'carrier_registration_number', 'updated_at'])
            ->get();
        $packagesWithoutDeclarations = [];
        $packagesWithDeclarations = [];
        $statuses = [];

        try {
            DB::beginTransaction();
            foreach ($packages as $package) {
                $dateFrom = '2021-02-10';
                $dateTo = '2021-04-01';
                $declaration = $this->carrier->getDeclarations(null, $package->internal_id, $dateFrom, $dateTo);

                if (count($declaration) and isset($declaration[0])) {
                    $declaration = $declaration[0];
                    $packagesWithDeclarations[] = [
                        'internal_id' => $package->internal_id,
                        'reg_number' => $declaration['regNumber'],
                        'status_id' => $declaration['payStatus_Id']
                    ];
                    $statuses[] = [
                        'package_id' => $package->id,
                        'status_id' => $declaration['payStatus_Id'] == 1 ? 39 : 40,
                        'created_by' => 1,
                        'created_at' => Carbon::now()
                    ];
                } else {
                    $packagesWithoutDeclarations[] = $package->internal_id;
                }
                sleep(1);
            }

            foreach ($packagesWithDeclarations as $packageInfo) {
                if (isset($packageInfo['internal_id'])) {
                    Package::where('internal_id', $packageInfo['internal_id'])->update([
                        'carrier_registration_number' => $packageInfo['reg_number'],
                        'carrier_status_id' => $packageInfo['status_id']
                    ]);
                }
            }

            PackageStatus::insert($statuses);

            file_put_contents('packages_success' . Carbon::now(), json_encode($packagesWithDeclarations));
            file_put_contents('packages_failed' . Carbon::now(), json_encode($packagesWithoutDeclarations));

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            var_dump($exception->getMessage());
        }
    }
}
