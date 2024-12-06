<?php

namespace App\Http\Controllers;

use App\Countries;
use App\Exports\CashierReportExport;
use App\Exports\ClientPhonesExport;
use App\Exports\ClientEmailsWithParentIdExport;
use App\Exports\CourierOrdersExport;
use App\Exports\CourierOrdersPackagesExport;
use App\Exports\DeclarationExport;
use App\Exports\DeliveredPackagesExport;
use App\Exports\ExternalPartnerPaymentsReportExport;
use App\Exports\FlightDepeshExport;
use App\Exports\InBakuExport;
use App\Exports\InboundPackagesExport;
use App\Exports\ManifestAdminExport;
use App\Exports\OnlinePaymentsReportExport;
use App\Exports\PackagesExport;
use App\Exports\PartnerReportExport;
use App\Exports\PaymentsReportExport;
use App\Exports\WareHouseExport;
use App\Flight;
use App\Location;
use App\Seller;
use App\Status;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ReportsController extends HomeController
{
	public function clients_phones()
	{
		try {
			if (Auth::id() != 1) {
				Session::flash('message', 'Access denied!');
				Session::flash('class', 'warning');
				Session::flash('display', 'block');
				return redirect()->route("home");
			}

			return Excel::download(new ClientPhonesExport(), 'phones.xlsx');
		} catch (\Exception $e) {
			return view('backend.error');
		}
	}

	public function clients_emails_with_parent_id()
	{
		try {
			if (Auth::id() != 1) {
				Session::flash('message', 'Access denied!');
				Session::flash('class', 'warning');
				Session::flash('display', 'block');
				return redirect()->route("home");
			}

			return Excel::download(new ClientEmailsWithParentIdExport(), 'emails.xlsx');
		} catch (\Exception $e) {
			return view('backend.error');
		}
	}

	public function get_declaration_page()
	{
		try {
			if(Auth::user()->role() == 9){
				$flights = Flight::whereNull('flight.deleted_by')->orderBy('flight.id', 'desc')
						->where('flight.flight_number', '>', Carbon::now()->subDay(15))
						->select('flight.id', 'flight.name')
						->get();
			}else{
				$flights = Flight::whereNull('flight.deleted_by')->orderBy('flight.id', 'desc')
				->take(50)
				->select('flight.id', 'flight.name')
				->get();
			}

			return view('backend.reports.declaration', compact(
					'flights'
			));
		} catch (\Exception $exception) {
			return view('backend.error');
		}
	}

	public function get_declaration(Request $request)
	{
		$validator = Validator::make($request->all(), [
				'flight' => 'required|integer'
		]);
		if ($validator->fails()) {
			Session::flash('message', 'Flight not found!');
			Session::flash('class', 'warning');
			Session::flash('display', 'block');
			return redirect()->route("get_declaration_page");
		}
		try {
			return Excel::download(new DeclarationExport($request->flight), 'declaration.xlsx');
		} catch (\Exception $e) {
			Session::flash('message', 'Sorry something went wrong!');
			Session::flash('class', 'danger');
			Session::flash('display', 'block');
			return redirect()->route("get_declaration_page");
		}
	}

	public function get_admin_manifest_page()
	{
		try {
			$flights = Flight::whereNull('flight.deleted_by')->orderBy('flight.id', 'desc')
					->select('flight.id', 'flight.name')
					->get();

			return view('backend.reports.admin_manifest', compact(
					'flights'
			));
		} catch (\Exception $exception) {
			return view('backend.error');
		}
	}

	public function admin_manifest(Request $request)
	{
		$validator = Validator::make($request->all(), [
				'flight' => 'required|integer'
		]);
		if ($validator->fails()) {
			Session::flash('message', 'Flight not found!');
			Session::flash('class', 'warning');
			Session::flash('display', 'block');
			return redirect()->route("get_admin_manifest_page");
		}
		try {
			return Excel::download(new ManifestAdminExport($request->flight), 'manifest.xlsx');
		} catch (\Exception $e) {
			Session::flash('message', 'Sorry something went wrong!');
			Session::flash('class', 'danger');
			Session::flash('display', 'block');
			return redirect()->route("get_admin_manifest_page");
		}
	}


	public function get_flight_depesh()
	{
		try {
			$flights = Flight::whereNull('flight.deleted_by')->orderBy('flight.id', 'desc')
					->select('flight.id', 'flight.name')
					->get();

			return view('backend.reports.flight_depesh', compact(
					'flights'
			));
		} catch (\Exception $exception) {
			return view('backend.error');
		}
	}

	public function flight_depesh(Request $request)
	{
		$validator = Validator::make($request->all(), [
				'flight' => 'required|integer'
		]);
		if ($validator->fails()) {
			Session::flash('message', 'Flight not found!');
			Session::flash('class', 'warning');
			Session::flash('display', 'block');
			return redirect()->route("get_flight_depesh");
		}
		try {
			return Excel::download(new FlightDepeshExport($request->flight), 'depesh.xlsx');
		} catch (\Exception $e) {
			Session::flash('message', 'Sorry something went wrong!');
			Session::flash('class', 'danger');
			Session::flash('display', 'block');
			return redirect()->route("get_flight_depesh");
		}
	}

	public function get_no_invoice()
	{
		try {
			return Excel::download(new PackagesExport(), 'noinvoice.xlsx');
		} catch (\Exception $e) {
			return view('backend.error');
		}
	}

	public function get_cashier_page()
	{
		try {
			return view('backend.reports.cashier');
		} catch (\Exception $exception) {
			return view('backend.error');
		}
	}

	public function post_cashier(Request $request)
	{
		$validator = Validator::make($request->all(), [
				'from_date' => 'required|date',
				'to_date' => 'required|date',
		]);
		if ($validator->fails()) {
			Session::flash('message', 'Date not found!');
			Session::flash('class', 'warning');
			Session::flash('display', 'block');
			if (Auth::user()->role() == 3) {
				// for cashier
				return redirect()->route("cashier_page");
			}
			return redirect()->route("reports_get_cashier_page");
		}
		try {
			$from_date = $request->from_date;
			$to_date = $request->to_date;

			return Excel::download(new CashierReportExport($from_date, $to_date), 'cashier_report.xlsx');
		} catch (\Exception $e) {
			Session::flash('message', 'An error occurred!');
			Session::flash('class', 'warning');
			Session::flash('display', 'block');
			if (Auth::user()->role() == 3) {
				// for cashier
				return redirect()->route("cashier_page");
			}
			return redirect()->route("reports_get_cashier_page");
		}
	}

	public function get_payments_page()
	{
		try {
			return view('backend.reports.payments');
		} catch (\Exception $exception) {
			return view('backend.error');
		}
	}

	public function post_payments(Request $request)
	{
		$validator = Validator::make($request->all(), [
				'from_date' => 'required|date',
				'to_date' => 'required|date',
		]);
		if ($validator->fails()) {
			Session::flash('message', 'Date not found!');
			Session::flash('class', 'warning');
			Session::flash('display', 'block');
			if (Auth::user()->role() == 3) {
				// for cashier
				return redirect()->route("cashier_page");
			}
			return redirect()->route("reports_get_payments_page");
		}
		try {
			$from_date = $request->from_date;
			$to_date = $request->to_date;

			return Excel::download(new PaymentsReportExport($from_date, $to_date), 'payments_report.xlsx');
		} catch (\Exception $e) {
			Session::flash('message', 'An error occurred!');
			Session::flash('class', 'warning');
			Session::flash('display', 'block');
			if (Auth::user()->role() == 3) {
				// for cashier
				return redirect()->route("cashier_page");
			}
			return redirect()->route("reports_get_payments_page");
		}
	}

	public function get_warehouse_page()
	{
		try {
			$countries = Countries::whereNull('deleted_by')->orderBy('name_en')->select('id', 'name_en as name')->get();
			$flights = Flight::whereNull('deleted_by')->orderBy('id', 'desc')->select('id', 'name')->get();
			$locations = Location::whereNull('deleted_by')->orderBy('name')->select('id', 'name')->get();
			$statuses = Status::whereNull('deleted_by')->orderBy('status_en')->select('id', 'status_en as status')->get();

			return view('backend.reports.warehouse', compact(
					'countries',
					'flights',
					'locations',
					'statuses'
			));
		} catch (\Exception $exception) {
			return view('backend.error');
		}
	}

	public function post_warehouse(Request $request)
	{
		$validator = Validator::make($request->all(), [
				'from_date' => 'required|date',
				'to_date' => 'required|date',
				'country' => 'nullable|integer',
				'flight' => 'nullable|integer',
				'status' => 'nullable|integer',
				'paid' => 'nullable|string|max:3',
				'warehouse' => 'required|string|max:20',
		]);
		if ($validator->fails()) {
			Session::flash('message', 'Validation error!');
			Session::flash('class', 'warning');
			Session::flash('display', 'block');
			if (Auth::user()->role() == 5) {
				return redirect()->route("report_page");
			}
			return redirect()->route("reports_get_warehouse_page");
		}
		try {
			$from_date = $request->from_date;
			$to_date = $request->to_date;
			$country = $request->country;
			$flight = $request->flight;
			$warehouse = $request->warehouse;
			$status = $request->status;
			$paid = $request->paid;

			if (Auth::user()->role() == 1) {
				$access_paid = 1;
			} else {
				$access_paid = 0;
			}

			return Excel::download(new WareHouseExport($from_date, $to_date, $country, $flight, $warehouse, $status, $paid, $access_paid), 'warehouse_report.xlsx');
		} catch (\Exception $e) {
			Session::flash('message', 'An error occurred!');
			Session::flash('class', 'warning');
			Session::flash('display', 'block');
			if (Auth::user()->role() == 5) {
				return redirect()->route("report_page");
			}
			return redirect()->route("reports_get_warehouse_page");
		}
	}

	public function reports_in_baku_page()
	{
		try {
			if (Auth::user()->role() == 1) {
				$paid_access = 1; // for only admin
			} else {
				$paid_access = 0;
			}

			return Excel::download(new InBakuExport($paid_access), 'in_baku.xlsx');
		} catch (\Exception $e) {
			return view('backend.error');
		}
	}

	public function get_inbound_packages_page()
	{
		try {
			return view('backend.reports.inbound_packages');
		} catch (\Exception $exception) {
			return view('backend.error');
		}
	}

	public function post_inbound_packages(Request $request)
	{
		$validator = Validator::make($request->all(), [
				'from_date' => 'required|date',
				'to_date' => 'required|date',
		]);
		if ($validator->fails()) {
			Session::flash('message', 'Date not found!');
			Session::flash('class', 'warning');
			Session::flash('display', 'block');
			return redirect()->route("reports_get_inbound_packages_page");
		}
		try {
			$from_date = $request->from_date;
			$to_date = $request->to_date;

			$paid_access = 1; // for only admin

			return Excel::download(new InboundPackagesExport($from_date, $to_date, $paid_access), 'inbound_packages.xlsx');
		} catch (\Exception $exception) {
			Session::flash('message', 'An error occurred!');
			Session::flash('class', 'warning');
			Session::flash('display', 'block');
			return redirect()->route("reports_get_inbound_packages_page");
		}
	}

	public function warehouse_inbound_packages()
	{
		try {
			$paid_access = 0;

			$flight_id = Input::get("flight");

			if (isset($flight_id) && !empty($flight_id) && $flight_id != null && $flight_id != "null" && $flight_id != "undefined") {
				return Excel::download(new InboundPackagesExport('', '', $paid_access, $flight_id), 'inbound_packages.xlsx');
			} else {
				Session::flash('message', 'Flight not found!');
				Session::flash('class', 'warning');
				Session::flash('display', 'block');
				return redirect()->back();
			}
		} catch (\Exception $exception) {
			Session::flash('message', 'An error occurred!');
			Session::flash('class', 'warning');
			Session::flash('display', 'block');
			return redirect()->back();
		}
	}

	public function get_delivered_packages_page()
	{
		try {
			return view('backend.reports.delivered_packages');
		} catch (\Exception $exception) {
			return view('backend.error');
		}
	}

	public function post_delivered_packages(Request $request)
	{
		$validator = Validator::make($request->all(), [
				'from_date' => 'required|date',
				'to_date' => 'required|date',
		]);
		if ($validator->fails()) {
			Session::flash('message', 'Date not found!');
			Session::flash('class', 'warning');
			Session::flash('display', 'block');
			return redirect()->route("reports_get_inbound_packages_page");
		}
		try {
			$from_date = $request->from_date;
			$to_date = $request->to_date;

			if (Auth::user()->role() == 1) {
				$paid_access = 1; // for only admin
			} else {
				$paid_access = 0;
			}

			return Excel::download(new DeliveredPackagesExport($from_date, $to_date, $paid_access), 'delivered_packages.xlsx');
		} catch (\Exception $exception) {
			Session::flash('message', 'An error occurred!');
			Session::flash('class', 'warning');
			Session::flash('display', 'block');
			return redirect()->route("reports_get_inbound_packages_page");
		}
	}

	public function warehouse_delivered_packages()
	{
		try {
			$paid_access = 0;

			$from = Input::get("from_date");
			$to = Input::get("to_date");

			if (isset($from) && !empty($from) && $from != null && $from != "null" && $from != "undefined") {
				$from_date = $from;
			} else {
				Session::flash('message', 'From date not found!');
				Session::flash('class', 'warning');
				Session::flash('display', 'block');
				return redirect()->back();
			}

			if (isset($to) && !empty($to) && $to != null && $to != "null" && $to != "undefined") {
				$to_date = $to;
			} else {
				Session::flash('message', 'To date not found!');
				Session::flash('class', 'warning');
				Session::flash('display', 'block');
				return redirect()->back();
			}

			return Excel::download(new DeliveredPackagesExport($from_date, $to_date, $paid_access), 'delivered_packages.xlsx');
		} catch (\Exception $exception) {
			Session::flash('message', 'An error occurred!');
			Session::flash('class', 'warning');
			Session::flash('display', 'block');
			return redirect()->back();
		}
	}

	public function get_courier_orders_page()
	{
		try {
			return view('backend.reports.courier_orders');
		} catch (\Exception $exception) {
			return view('backend.error');
		}
	}

	public function post_courier_orders(Request $request)
	{
		$validator = Validator::make($request->all(), [
				'from_date' => 'required|date',
				'to_date' => 'required|date',
		]);
		if ($validator->fails()) {
			Session::flash('message', 'Date not found!');
			Session::flash('class', 'warning');
			Session::flash('display', 'block');
			return redirect()->route("reports_get_courier_orders_page");
		}
		try {
			$from_date = $request->from_date;
			$to_date = $request->to_date;

			return Excel::download(new CourierOrdersExport(null, null, null, null, null, null, null, null, null, null, true, $from_date, $to_date), 'courier_orders_report.xlsx');
		} catch (\Exception $exception) {
			Session::flash('message', 'An error occurred!');
			Session::flash('class', 'warning');
			Session::flash('display', 'block');
			return redirect()->route("reports_get_courier_orders_page");
		}
	}

	public function get_courier_orders_packages_page()
	{
		try {
			return view('backend.reports.courier_orders_packages');
		} catch (\Exception $exception) {
			return view('backend.error');
		}
	}

	public function post_courier_orders_packages(Request $request)
	{
		$validator = Validator::make($request->all(), [
				'from_date' => 'required|date',
				'to_date' => 'required|date',
		]);
		if ($validator->fails()) {
			Session::flash('message', 'Date not found!');
			Session::flash('class', 'warning');
			Session::flash('display', 'block');
			return redirect()->route("reports_get_courier_orders_packages_page");
		}
		try {
			$from_date = $request->from_date;
			$to_date = $request->to_date;

			return Excel::download(new CourierOrdersPackagesExport($from_date, $to_date), 'courier_orders_packages_report.xlsx');
		} catch (\Exception $exception) {
			Session::flash('message', 'An error occurred!');
			Session::flash('class', 'warning');
			Session::flash('display', 'block');
			return redirect()->route("reports_get_courier_orders_packages_page");
		}
	}


	public function get_partner_reports()
	{
		try {
			
			// $seller_id = Seller::whereNull('deleted_at')
			// 	->whereIn('id', [1338, 3697])
			// 	->select('id', 'name')
			// 	->get();
			// 	, compact(
			// 		'seller_id')
			return view('backend.reports.partner');
		} catch (\Exception $exception) {
			return view('backend.error');
		}
	}

	public function partner_reports(Request $request){
		$validator = Validator::make($request->all(), [
			'from_date' => 'required|date',
			'to_date' => 'required|date',
			// 'seller_id' => 'required|int',
		]);
		if ($validator->fails()) {
			Session::flash('message', 'Date not found!');
			Session::flash('class', 'warning');
			Session::flash('display', 'block');
			return redirect()->route("get_partner_reports");
		}
		try {
			// $seller_id = $request->seller_id;
			$from_date = $request->from_date;
			$to_date = $request->to_date;

			return Excel::download(new PartnerReportExport($from_date, $to_date), 'partner_report.xlsx');
		} catch (\Exception $exception) {
			Session::flash('message', 'An error occurred!');
			Session::flash('class', 'warning');
			Session::flash('display', 'block');
			return redirect()->route("get_partner_reports");
		}
	}


    public function get_payment_task_reports()
    {
        try {
            return view('backend.reports.payment');
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function payment_task_reports(Request $request){
        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date',
            'to_date' => 'required|date',
        ]);
        if ($validator->fails()) {
            Session::flash('message', 'Date not found!');
            Session::flash('class', 'warning');
            Session::flash('display', 'block');
            return redirect()->route("get_payment_task_reports");
        }
        try {
            $from_date = $request->from_date;
            $to_date = $request->to_date;

            return Excel::download(new OnlinePaymentsReportExport($from_date, $to_date), 'online_payment_report.xlsx');
        } catch (\Exception $exception) {
            //dd($exception);
            Session::flash('message', 'An error occurred!');
            Session::flash('class', 'warning');
            Session::flash('display', 'block');
            return redirect()->route("get_payment_task_reports");
        }
    }


    public function get_partner_payment_reports()
    {
        try {
            return view('backend.reports.external_partner_payment');
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function partner_payment_reports(Request $request){
        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date',
            'to_date' => 'required|date',
        ]);
        if ($validator->fails()) {
            Session::flash('message', 'Date not found!');
            Session::flash('class', 'warning');
            Session::flash('display', 'block');
            return redirect()->route("get_partner_payment_reports");
        }
        try {
            $from_date = $request->from_date;
            $to_date = $request->to_date;

            return Excel::download(new ExternalPartnerPaymentsReportExport($from_date, $to_date), 'external_partner_payment_report.xlsx');
        } catch (\Exception $exception) {
            //dd($exception);
            Session::flash('message', 'An error occurred!');
            Session::flash('class', 'warning');
            Session::flash('display', 'block');
            return redirect()->route("get_partner_payment_reports");
        }
    }
}
