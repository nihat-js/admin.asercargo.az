<?php

namespace App\Services;

use App\Contracts\CourierServiceInterface;
use App\CourierOrders;

class CourierOrderService
{
    private $courierService;

    public function __construct(CourierServiceInterface $courierService)
    {
        $this->courierService = $courierService;
    }

    public function processOrders()
    {
        // Kuryer sifarislerini topla
        $orders = CourierOrders::where('courier_payment_type_id', 1)
            ->where('delivery_payment_type_id', 1)
            ->whereNull('deleted_at')
            ->whereNull('canceled_at')
            ->whereNull('delivered_at')
            ->whereNull('azerpost_track')
            ->where('client_id', 142712)
            ->whereIn('id', [52792, 52791])
            ->get();

        //dd($orders);
        // Kuryer servisine gönder
        $this->courierService->sendOrders($orders->toArray());
    }
}