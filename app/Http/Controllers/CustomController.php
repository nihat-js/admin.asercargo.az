<?php

namespace App\Http\Controllers;

use App\Package;
use App\Services\Carrier;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestValueResolver;

class CustomController extends Controller
{
    private Carrier $carrier;


    public function __construct(Carrier $carrier)
    {
        $this->url = 'https://ecarrier-fbusiness.customs.gov.az:7545';
    }

    public function index()
    {
        return view("backend.custom");
    }

    public function httpResponse($url, $data)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => array(
            'ApiKey: 95HH16GDAC070D3TP68BC1E22G837866RB5AADV3',
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        

        return $response;
    }


    public function post_custom_response(Request $request)
    {

        try{
            $url = $this->url . '/api/v2/carriers/carriersposts/0/100';
            $data = [
                "trackingNumber" => $request->sendCustom 
            ];

            $reponse = $this->httpResponse($url, $data);

            return $reponse;         

        }catch(Exception $ex){
            dd($ex);
        }
    }

    public function post_declaration(Request $request)
    {

        try{
            $url = $this->url . '/api/v2/carriers/declarations/0/100';
            $data = [
                "trackingNumber" => $request->declaration 
            ];

            $reponse = $this->httpResponse($url, $data);

            return $reponse;         

        }catch(Exception $ex){
            dd($ex);
        }
    }


    public function post_awb(Request $request)
    {

        try{
            $url = $this->url . '/api/v2/carriers/airwaybillpackages';
            $data = [
                "airWaybill"=> $request->awb,
                "depeshNumber" => 'CCN-' . $request->depeshNumber 
            ];

            $reponse = $this->httpResponse($url, $data);

            return $reponse;         

        }catch(Exception $ex){
            dd($ex);
        }
    }

    public function post_custom_deleted(Request $request)
    {

        try{

            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://ecarrier-fbusiness.customs.gov.az:7545/api/v2/carriers/' . $request->delete,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => array(
                'ApiKey: 95HH16GDAC070D3TP68BC1E22G837866RB5AADV3'
            ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);


            return $response;         

        }catch(Exception $ex){
            dd($ex);
        }
    }

    public function putAirWay(Request $request){
        
        
        $package = Package::whereIn('container_id', explode(',',$request->ccn))->whereNull('deleted_at')->select('internal_id', 'container_id')->get();
        $arr = [];
        
        foreach($package as $pack){
            $data = [
                "trackinG_NO" => $pack->internal_id,
                "airwaybill" => $request->awb,
                "depesH_NUMBER" => "CCN-" . (string)$pack->container_id
            ]; 
            array_push($arr, $data);
        }
  
        $encode = json_encode($arr);

        dd($encode);

    }

    public function checkPack(Request $req)
    {
        $package = Package::leftJoin('item', 'item.package_id', '=', 'package.id')
            ->where('package.internal_id', $req->track)
            ->select(
                'package.id',
                'package.customer_type_id',
                'package.number',
                'package.internal_id',
                'package.last_status_id',
                'package.carrier_status_id',
                'package.carrier_registration_number',
                'package.container_id',
                'package.last_container_id',
                'package.position_id',
                'item.id',
                'item.price',
                'item.price_usd',
                'item.invoice_doc',
                'item.invoice_confirmed',
                'item.invoice_status',
                'item.title'
            )->first();

            return $package;
    }

    public function updatePackage(Request $request)
    {
        try{
            // dd($request->all());
            $track = $request->track;
            $value = $request->value;
            $type = $request->type;

            $package = Package::leftJoin('item', 'item.package_id', '=', 'package.id')
                    ->where('package.internal_id', $track)
                    ->select('package.*')->first();


            if($package !=null){
                if($type == 1)
                    $package->update(['carrier_status_id'=>$value]);
                elseif($type == 2)
                    $package->update(['container_id'=>$value]);
                elseif($type == 3)
                    $package->update(['last_container_id'=>$value]);
                elseif($type == 4)
                    $package->update(['position_id'=>$value]);
                elseif($type == 5)
                    $package->update(['customer_type_id'=>$value]);
                elseif($type == 6)
                    if ($package->item) {
                        $package->item->update(['title' => $value]);
                    }
                elseif($type == 7)
                    $package->update(['last_status_id'=>$value]);
            }else{
                return false;
            }

            return 'Success';

        }catch(Exception $ex){
            return $ex;
        }
    }
}
