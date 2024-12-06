<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\SpecialOrders;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class CashBackController extends ApiController
{
    function control_special_order(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'hash' => ['required', 'string', 'max:255'],
                'url' => ['required', 'string', 'max:1000'],
            ]);
            if ($validator->fails()) {
                return response(['status' => Response::HTTP_BAD_REQUEST, 'type' => 'validation', 'message' => $validator->errors()->toArray()],Response::HTTP_BAD_REQUEST);
            }

            $order = SpecialOrders::where(['group_code'=>$request->hash, 'url'=>$request->url])
                ->whereNull('deleted_by')
                ->whereNotNull('placed_by')
                ->orderBy('id', 'desc')
                ->select(
                    'id',
                    'quantity',
                    'single_price',
                    'is_paid',
                    'order_number'
                )
                ->first();

            if (!$order) {
                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'type' => 'not_found',
                    'message' => 'Order not found!'
                ], Response::HTTP_NOT_FOUND);
            }

            $quantity = $order->quantity;
            $single_price = $order->single_price;
            $is_paid = $order->is_paid;
            $order_number = $order->order_number;

            $price = $quantity * $single_price;
            $price = sprintf('%0.2f', $price);

            if ($is_paid == 1) {
                $paid_status = true;
            } else {
                $paid_status = false;
            }

            return response([
                'status' => Response::HTTP_OK,
                'type' => 'success',
                'order_number' => $order_number,
                'price' => $price,
                'paid_status' => $paid_status
            ], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return response([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'type' => 'error',
                'message' => 'Sorry, An error occurred...'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
