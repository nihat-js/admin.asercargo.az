<?php

namespace App\Services;

use App\Contracts\CourierServiceInterface;
use App\Package;
use App\User;
use GuzzleHttp\Client;

class Colibri189CourierService implements CourierServiceInterface
{
    protected $httpClient;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function sendOrders(array $orders): void
    {
        $this->sendDataToApi($orders);
    }

    public function sendDataToApi(array $orders): void
    {
        $staticData = [
            'tariff' => 'INADAY',
            'pickupInfo' => [
                'name' => 'Colibri',
                'phoneNumber' => '+994123103939',
                'address' => 'Dilarə Əliyeva küç., 251,',
                'district' => 'nesimi-rayonu',
                'comment' => 'merkezi ofis',
                'coordinate' => [
                    'latitude' => null,
                    'longitude' => null,
                ],
            ],
        ];

        $deliveries = $this->processDeliveries($orders);
        $chunkedData = array_chunk($deliveries, 500);

        $responseBatch = $this->sendDataToCourierService('http://88.99.33.229:8062/services/189couriermsorders/api/request/v1/request/batch', $staticData, $chunkedData[0]);
       // dd($responseBatch);
        if (!$responseBatch) {
            //
        }

        foreach ($chunkedData[1] as $delivery) {
            $responseSingle = $this->sendDataToCourierService('http://88.99.33.229:8062/services/189couriermsorders/api/request/v1/request', $staticData, [$delivery]);

            if (!$responseSingle) {
                //
            }
        }


    }

    private function sendDataToCourierService($apiUrl, $staticData, $deliveries)
    {
        $staticData['paletteName'] = $this->generatePaletteName();

        $requestData = [
            'paletteName' => $staticData['paletteName'],
            'tariff' => $staticData['tariff'],
            'pickupInfo' => $staticData['pickupInfo'],
            'deliveries' => $deliveries,
        ];

        $response = $this->httpClient->post($apiUrl, [
            'headers' => [
                'X-Username' => 'colibridev',
                'X-Access-Token' => '221144TCOLIBAH4321FBLIFKGDSFRILIMFHFHFHR',
                'Content-Type' => 'application/json',
            ],
            'json' => $requestData,
        ]);

        return json_decode($response->getBody(), true);
    }


    private function processDeliveries(array $orders)
    {
        $deliveries = [];
        $kuryerSifarisleri = $orders;

        foreach ($kuryerSifarisleri as $kuryerSifarisi) {
            if ($this->isOrderValid($kuryerSifarisi)) {
                $addressInfo = $this->getUserAddressInfo($kuryerSifarisi['client_id']);
                $items = $this->getPackageItems($kuryerSifarisi['packages']);

                $deliveries[] = [
                    'addressInfo' => $addressInfo,
                    'internalOrderId' => $kuryerSifarisi['id'],
                    'items' => $items,
                ];
            }
        }

        return $deliveries;
    }

    private function isOrderValid($kuryerSifarisi)
    {
        return true;
    }

    private function getUserAddressInfo($userId)
    {
        $user = User::find($userId);

        return [
            'name' => $user->name . ' ' . $user->surname,
            'phoneNumber' => $user->phone1,
            'address' => $user->address1,
            'district' => $user->region,
            'comment' => null,
            'coordinate' => [
                'latitude' => null,
                'longitude' => null,
            ],
        ];
    }

    private function getPackageItems($packageItemIds)
    {
        $packageItems = Package::whereIn('id', explode(',', $packageItemIds))->get();

        $items = [];

        foreach ($packageItems as $packageItem) {
            $items[] = [
                'packageType' => "DOCUMENT",
                'title' => null,
                'barcode' => $packageItem->internal_id,
                'width' => $packageItem->width,
                'length' => $packageItem->length,
                'height' => $packageItem->height,
                'weight' => $packageItem->gross_weight,
                'internalItemId' => $packageItem->internal_id,
            ];
        }

        return $items;
    }

    private function generatePaletteName()
    {
        $dynamicDate = now()->format('Ymd');

        return "Colibri-$dynamicDate";
    }
}
