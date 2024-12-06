<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Kutia\Larafirebase\Facades\Larafirebase;
use App\Notifications\SendPushNotification;
use App\User;
use Notification;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function sendNotification($mailTitle, $mailSubject, $mailContent, $client_id) : void
    {
        $fcm_token = DB::table('user_devices')->where('user_id', $client_id)->select('fcm_token')->get();
        $user_arr = [];

        if ($fcm_token->count() > 0){
            if($fcm_token != null){
                foreach($fcm_token as $fcmt){
                    array_push($user_arr, $fcmt->fcm_token);
                }
            }

            $SERVER_API_KEY = '';

            $replacements = [
                '&ouml;' => 'ö',
                '&Ouml;' => 'Ö',
                '&uuml;' => 'ü',
                '&Uuml;' => 'Ü',
                '&nbsp;' => ' ',
                '&ccedil;' => 'ç',
                '&Ccedil;' => 'Ç',
            ];

            $updatedContent = str_replace(array_keys($replacements), array_values($replacements), $mailContent);

            // dd($updatedContent, $mailContent);
            $data = [
                "registration_ids" => (array)$user_arr,
                "notification" => [
                    "title" => $mailTitle,
                    "body" => $mailSubject,
                    "Content" => $updatedContent ? $updatedContent : ''
                ]
            ];
            //dd($data);
            $dataString = json_encode($data);

            $headers = [
                'Authorization: key=' . $SERVER_API_KEY,
                'Content-Type: application/json',
            ];

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

            $response = curl_exec($ch);
             //dd($client_id);

            curl_close($ch);

            $responseData = json_decode($response);

            if ($responseData && isset($responseData->success) && $responseData->success > 0) {

                $cleaned_content = strip_tags($updatedContent);
                DB::table('user_notification_details')->insert([
                    'client_id' => $client_id,
                    'subject_header' => $mailSubject,
                    'subject_content' => $updatedContent ? $cleaned_content : '',
                    'is_read' => 0,
                    'created_at' => Carbon::now(),
                ]);

                $user = User::find($client_id);
                $user->increment('read_notification_count');
            }
        }

    }

    public function message($type)
    {
        switch($type){
            case 'courier':
                {
                    $title = "Baglamaniz Bakidadir";
                    $body = "Hormetli musteri baglamaniz artiq Baki ofisimizdedi. Gelib goturun";
                }
                break;
            default:
            echo "test";
        }

        return response([
            'title' => $title,
            'body' => $body
        ]);
    }
}
