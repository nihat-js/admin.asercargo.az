<?php

namespace App\Exports;

use App\Container;
use App\Flight;
use App\Item;
use App\Package;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class FlightDepeshExport implements FromCollection, WithColumnFormatting, WithHeadings, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public  $flight_id;

    public function __construct($flight_id)
    {
        $this->flight_id = $flight_id;
    }

    public function collection()
    {
        try {
            $flight_id = $this->flight_id;

            $flight = Flight::where('id', $flight_id)->whereNull('deleted_by')
                ->select('name')
                ->first();

            if (!$flight) {
                $packageObj = new PackageObj();
                $packageObj->TR_NUMBER = 'Flight not found!';
                return collect(['error'=>$packageObj]);
            }

            $flight_name = $flight->name;

            $containers = Container::where('flight_id', $flight_id)->whereNull('deleted_by')
                ->select('id')->get();

            if (count($containers) == 0) {
                $packageObj = new PackageObj();
                $packageObj->TR_NUMBER = 'Container not found!';
                return collect(['error'=>$packageObj]);
            }

            $containers_arr = array();
            foreach ($containers as $container) {
                array_push($containers_arr, $container->id);
            }

             
            $packages = Package::whereNull('deleted_by')
                        ->with([
                            'carrierLog' => function ($query) {
                                $query->whereIn('carrier_status_id', [8, 10]);
                            }
                        ])
                        ->whereIn('last_container_id', $containers_arr)
                        ->select([
                            'number as track',
                            'last_container_id as container',
                            'internal_id'
                        ])
                        ->get();
            // dd($packages);
            if (count($packages) == 0) {
                $packageObj = new PackageObj();
                $packageObj->TR_NUMBER = 'Packages not found!';
                return collect(['error'=>$packageObj]);
            }

            $packages_arr = array();

            foreach ($packages as $package) {
                $manifest = new DepeshObj();
  
                $manifest->flight = $flight_name;
                if ($package->container != null) {
                    $container = 'CONTAINER' . $package->container;
                } else {
                    $container = '---';
                }
            
                $manifest->container = $container;
                $manifest->track = '"' . $package->track . '"';
                $manifest->internal_id = $package->internal_id;
                
                // dd($package->carrierLog->first());

                foreach($package->carrierLog as $depesh){
                    $depesh_status = $depesh->carrier_status_id;
                    $depesh_note = $depesh->note;
                    $depesh_date = $depesh->created_at;

                    if($depesh_status == 8 || $depesh_status == 10){
                        $depes = $depesh_note;
                        $depesh_time = $depesh_date;
                    }else{
                        $depes = '---';
                        $depesh_time = '---';

                    }
    
               
                    $manifest->depesh = $depes;
                    $manifest->depeshDate = substr($depesh_time, 0, 19);

                }
                
                


                array_push($packages_arr, $manifest);
            }

            return collect($packages_arr);
        } catch (\Exception $e) {
            // dd($e);
            $packageObj = new PackageObj();
            $packageObj->TR_NUMBER = 'Something went wrong!';
            return collect(['error'=>$packageObj]);
        }
    }

    public function columnFormats(): array
    {
        return [
            'P' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER,
            'R' => NumberFormat::FORMAT_NUMBER
        ];
    }

    public function headings(): array
    {
        return [
            'Flight',
            'Container',
            'Tracking',
            'Internal ID',
            'Depesh',
            'Depesh Date'
        ];
    }
}
