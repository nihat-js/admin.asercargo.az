<?php

namespace App\Http\Controllers;

use App\Exports\Collector\CollectorPackageExport;
use App\Flight;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class CollectorReportsController extends HomeController
{
    public function get_reports_page($type)
    {
        try {
            $flights = false;

            switch ($type) {
                case 'manifest': {
                    $title = 'Manifest';

                    if(Auth::user()->id == 137297){
                        $flights = Flight::whereNull('flight.deleted_by')
                        ->whereDate('created_at', '>', date('2021-08-20'))
                        ->whereRaw('(public = 1 or location_id = ?)', Auth::user()->location())
                        ->select('flight.id', 'flight.name')
                        ->orderBy('flight.id', 'desc')
                        ->get();
                    }else{
                        $flights = Flight::whereNull('flight.deleted_by')
                            ->whereDate('flight_number', '>', Carbon::now()->subDays(30))
                            ->whereRaw('(public = 1 or location_id = ?)', Auth::user()->location())
                            ->select('flight.id', 'flight.name')
                            ->orderBy('flight.id', 'desc')
                            ->get();
                    }
                } break;
                case 'no_invoice': {
                    $title = 'No invoice';
                } break;
                case 'incorrect_invoice': {
                    $title = 'Incorrect invoice';
                } break;
                case 'prohibited': {
                    $title = 'Prohibited';
                } break;
                case 'damaged': {
                    $title = 'Damaged';
                } break;
                case 'all_packages': {
                    $title = 'All packages';
                } break;
                default: {
                    return view('backend.error');
                }
            }

            return view('backend.collector.reports', compact(
                'type',
                'title',
                'flights'
            ));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function post_reports(Request $request, $type) {
        try {
            switch ($type) {
                case 'manifest': {
                    return $this->report_manifest($request);
                } break;
                case 'no_invoice': {
                    return $this->report_no_invoice($request);
                } break;
                case 'incorrect_invoice': {
                    return $this->report_incorrect_invoice($request);
                } break;
                case 'prohibited': {
                    return $this->report_prohibited($request);
                } break;
                case 'damaged': {
                    return $this->report_damaged($request);
                } break;
                case 'all_packages': {
                    return $this->report_all_packages($request);
                } break;
                default: {
                    Session::flash('message', 'Wrong type!');
                    Session::flash('class', 'danger');
                    Session::flash('display', 'block');
                    return redirect()->refresh();
                }
            }
        } catch (\Exception $exception) {
            Session::flash('message', 'Sorry something went wrong!');
            Session::flash('class', 'danger');
            Session::flash('display', 'block');
            return redirect()->refresh();
        }
    }


    // manifest
    private function report_manifest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'flight' => ['required', 'integer']
        ]);
        if ($validator->fails()) {
            Session::flash('message', 'Flight cannot be empty!');
            Session::flash('class', 'warning');
            Session::flash('display', 'block');
            return redirect()->refresh();
        }
        try {
            $type = 'manifest';

            return Excel::download(new CollectorPackageExport($type, $request->flight, null, null, $request), $type . '.xlsx');
        } catch (\Exception $e) {
            //dd($e);
            Session::flash('message', 'Sorry something went wrong!');
            Session::flash('class', 'danger');
            Session::flash('display', 'block');
            return redirect()->refresh();
        }
    }

    // no invoice
    private function report_no_invoice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date']
        ]);
        if ($validator->fails()) {
            Session::flash('message', 'From date and to date cannot be empty!');
            Session::flash('class', 'warning');
            Session::flash('display', 'block');
            return redirect()->refresh();
        }
        try {
            $type = 'no_invoice';

            return Excel::download(new CollectorPackageExport($type, null, $request->from_date, $request->to_date, $request), $type . '.xlsx');
        } catch (\Exception $e) {
            Session::flash('message', 'Sorry something went wrong!');
            Session::flash('class', 'danger');
            Session::flash('display', 'block');
            return redirect()->refresh();
        }
    }

    // incorrect invoice
    private function report_incorrect_invoice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date']
        ]);
        if ($validator->fails()) {
            Session::flash('message', 'From date and to date cannot be empty!');
            Session::flash('class', 'warning');
            Session::flash('display', 'block');
            return redirect()->refresh();
        }
        try {
            $type = 'incorrect_invoice';

            return Excel::download(new CollectorPackageExport($type, null, $request->from_date, $request->to_date, $request), $type . '.xlsx');
        } catch (\Exception $e) {
            Session::flash('message', 'Sorry something went wrong!');
            Session::flash('class', 'danger');
            Session::flash('display', 'block');
            return redirect()->refresh();
        }
    }

    // prohibited
    private function report_prohibited(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date']
        ]);
        if ($validator->fails()) {
            Session::flash('message', 'From date and to date cannot be empty!');
            Session::flash('class', 'warning');
            Session::flash('display', 'block');
            return redirect()->refresh();
        }
        try {
            $type = 'prohibited';

            return Excel::download(new CollectorPackageExport($type, null, $request->from_date, $request->to_date, $request), $type . '.xlsx');
        } catch (\Exception $e) {
            Session::flash('message', 'Sorry something went wrong!');
            Session::flash('class', 'danger');
            Session::flash('display', 'block');
            return redirect()->refresh();
        }
    }

    // damaged
    private function report_damaged(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date']
        ]);
        if ($validator->fails()) {
            Session::flash('message', 'From date and to date cannot be empty!');
            Session::flash('class', 'warning');
            Session::flash('display', 'block');
            return redirect()->refresh();
        }
        try {
            $type = 'damaged';

            return Excel::download(new CollectorPackageExport($type, null, $request->from_date, $request->to_date, $request), $type . '.xlsx');
        } catch (\Exception $e) {
            Session::flash('message', 'Sorry something went wrong!');
            Session::flash('class', 'danger');
            Session::flash('display', 'block');
            return redirect()->refresh();
        }
    }

    // all packages
    private function report_all_packages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date']
        ]);
        if ($validator->fails()) {
            Session::flash('message', 'From date and to date cannot be empty!');
            Session::flash('class', 'warning');
            Session::flash('display', 'block');
            return redirect()->refresh();
        }
        try {
            $type = 'all_packages';

            return Excel::download(new CollectorPackageExport($type, null, $request->from_date, $request->to_date, $request), $type . '.xlsx');
        } catch (\Exception $e) {
            Session::flash('message', 'Sorry something went wrong!');
            Session::flash('class', 'danger');
            Session::flash('display', 'block');
            return redirect()->refresh();
        }
    }
}
