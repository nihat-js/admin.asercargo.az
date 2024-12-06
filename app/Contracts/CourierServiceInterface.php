<?php

namespace App\Contracts;

interface CourierServiceInterface
{
    // Your interface definition here
    public function sendOrders(array $data): void;
}
