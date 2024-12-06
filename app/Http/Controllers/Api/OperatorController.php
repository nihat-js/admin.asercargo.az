<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Classes\Functions;
use App\Package;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;

class OperatorController extends Controller
{
    public function get_client()
    {
        try {
            //id (suite), name, surname, phone, email
            $suite = Input::get("suite");
            $name = Input::get("name");
            $surname = Input::get("surname");
            $passport = Input::get("passport");
            $phone = Input::get("phone");
            $email = Input::get("email");

            $client_query = User::whereNull('deleted_by')->where('role_id', 2); //only clients

            if (isset($suite) && !empty($suite)) {
                $suite = (int)$suite;
                $client_query->where('id', $suite);
            }

            if (isset($name) && !empty($name)) {
                $client_query->where('name', 'LIKE', '%' . $name . '%');
            }

            if (isset($surname) && !empty($surname)) {
                $client_query->where('surname', 'LIKE', '%' . $surname . '%');
            }

            if (isset($passport) && !empty($passport)) {
                $client_query->where('passport', 'LIKE', '%' . $passport . '%');
            }

            if (isset($phone) && !empty($phone)) {
                $client_query->whereRaw("
                    users.phone1 LIKE '%" . $phone . "%' or
                    users.phone2 LIKE '%" . $phone . "%' or
                    users.phone3 LIKE '%" . $phone . "%'
                ");
            }

            if (isset($email) && !empty($email)) {
                $client_query->where('email', 'LIKE', '%' . $email . '%');
            }

            $clients = $client_query->orderBy('name')
                ->select(
                    'id',
                    'suite',
                    'name',
                    'surname',
                    'passport_number as passport',
                    'email',
                    'address1',
                    'address2',
                    'address3',
                    'phone1',
                    'phone2',
                    'phone3',
                    'birthday',
                    'language'
                )
                ->get();

            if (!$clients) {
                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'type' => 'warning',
                    'message' => 'Client not found!'
                ],  Response::HTTP_NOT_FOUND);
            }

            return response([
                'status' => Response::HTTP_OK,
                'type' => 'success',
                'clients' => $clients
            ], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return response([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'type' => 'Error',
                'message' => 'Sorry, An error occurred...'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function get_packages() {
        try {
            $client = Input::get("client");

            $functions = new Functions();
            if ($functions->validate_for_api([$client])) {
                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'type' => 'validate',
                    'message' => 'Fill all inputs!'
                ],  Response::HTTP_NOT_FOUND);
            }

            $packages = Package::leftJoin('position as p', 'package.position_id', '=', 'p.id')
                ->leftJoin('locations as l', 'p.location_id', '=', 'l.id')
                ->leftJoin('lb_status as s', 'package.last_status_id', '=', 's.id')
                ->leftJoin('locations as dep', 'package.departure_id', '=', 'dep.id')
                ->leftJoin('locations as des', 'package.destination_id', '=', 'des.id')
                ->leftJoin('currency as cur', 'package.currency_id', '=', 'cur.id')
                ->leftJoin('seller as sel', 'package.seller_id', '=', 'sel.id')
                ->where('package.client_id', $client)->whereNull('package.deleted_by')
                ->select(
                    'package.number',
                    'package.internal_id',
                    'package.volume_weight',
                    'package.gross_weight',
                    'package.chargeable_weight', //1 - gross; 2- volume
                    'package.total_charge_value as amount',
                    'package.paid',
                    'package.paid_status',
                    'cur.name as currency',
                    'package.payment_receipt',
                    'package.payment_receipt_date',
                    'sel.name as seller',
                    'dep.name as departure',
                    'des.name as destination',
                    's.status as status',
                    'p.name as position',
                    'l.name as location'
                )
                ->get();

            if (!$packages) {
                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'type' => 'warning',
                    'message' => 'Package not found!'
                ],  Response::HTTP_NOT_FOUND);
            }

            return response([
                'status' => Response::HTTP_OK,
                'type' => 'success',
                'packages' => $packages
            ], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return response([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'type' => 'Error',
                'message' => 'Sorry, An error occurred...'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
