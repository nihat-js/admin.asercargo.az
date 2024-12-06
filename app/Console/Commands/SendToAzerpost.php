<?php

namespace App\Console\Commands;

use App\CourierOrders;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SendToAzerpost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:azerpost';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send to azerpost';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $courier_orders = CourierOrders::whereNotNull('courier_orders.azerpost_track')
            ->leftJoin('users', 'courier_orders.client_id', 'users.id')
            ->whereNotNull('courier_orders.order_weight')
            ->where('courier_orders.is_send_azerpost', 0)
            ->where('courier_orders.is_set_azerpost', 1)
            ->where('courier_orders.is_error', 0)
            ->whereNotNull('courier_orders.post_zip')
            ->whereNull('courier_orders.canceled_at')
            ->whereNull('courier_orders.deleted_at')
            ->select(
                'courier_orders.id',
                'courier_orders.azerpost_track',
                'users.zip1',
                'courier_orders.order_weight',
                'courier_orders.address',
                'users.name',
                'users.surname',
                'users.email',
                'users.phone1',
                'users.passport_fin',
                'courier_orders.post_zip'
            )
            ->get();
        //dd($courier_orders);
        if ($courier_orders->count() > 0){
            foreach ($courier_orders as $order) {
                $curl = curl_init();

                $payload = (object) [
                    'vendor_id' => 'CR001',
                    'package_id' => $order->azerpost_track,
                    'delivery_post_code' => $order->post_zip,
                    'package_weight' => (float) $order->order_weight,
                    'customer_address' => $order->address,
                    'first_name' => $order->name,
                    'last_name' => $order->surname,
                    'email' => $order->email,
                    'phone_no' => $order->phone1,
                    'user_passport' => $order->passport_fin,
                    'delivery_type' => '0',
                    'vendor_payment' => 0,
                    'fragile' => 0,
                    'vendor_payment_status' => 1
                ];

                curl_setopt_array($curl, array(
                    //CURLOPT_URL => 'https://api.azpost.co/order/create',
                    CURLOPT_URL => 'https://api.azerpost.az/order/create',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => json_encode($payload),
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        'x-api-key: cfbffda47207492d9771773f8178abbd'
                    ),
                ));

                $response = curl_exec($curl);

                curl_close($curl);

                $responseData = [
                    'type' => 'create_package',
                    'response' => $response,
                    'request' => json_encode($payload),
                    'track' => $order->azerpost_track,
                    'created_at' => Carbon::now()
                ];

                DB::table('azerpost_log')->insert($responseData);

                $responseStatus = json_decode($response, true);
                if (isset($responseStatus['status']) && ($responseStatus['status'] == 200 || $responseStatus['status'] == 201)) {
                    $updateData = ['is_send_azerpost' => 1];
                    DB::table('courier_orders')->where('id', $order->id)->update($updateData);
                }else{
                    $updateData = ['is_error' => 1];
                    DB::table('courier_orders')->where('id', $order->id)->update($updateData);
                }

            }
        }

    }
}
