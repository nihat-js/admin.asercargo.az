<?php

namespace App\Exports;

use App\User;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ClientPhonesExport implements FromCollection, WithColumnFormatting, WithHeadings, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */

    public function collection()
    {
        try {
            if (Auth::id() != 1) {
                $clientPhonesObj = new ClientPhonesObj();
                $clientPhonesObj->no = 'Access denied!';
                return collect(['error'=>$clientPhonesObj]);
            }

            $clients = User::where('role_id', 2)->select('id', 'email', 'phone1', 'phone2', 'created_at')->get();

            if (count($clients) == 0) {
                $clientPhonesObj = new ClientPhonesObj();
                $clientPhonesObj->no = 'Clients not found!';
                return collect(['error'=>$clientPhonesObj]);
            }

            $clients_arr = array();
            $i = 0;
            foreach ($clients as $client) {
                $i++;

                $clientPhonesObj = new ClientPhonesObj();
                $clientPhonesObj->no = $i;
                $clientPhonesObj->suite = 'AS' . $client->id;
                $clientPhonesObj->email = $client->email;
                $clientPhonesObj->phone1 = $client->phone1;
                $clientPhonesObj->phone2 = $client->phone2;
                $clientPhonesObj->created_date = substr($client->created_at, 0, 16);

                array_push($clients_arr, $clientPhonesObj);
            }

            return collect($clients_arr);
        } catch (\Exception $exception) {
            $clientPhonesObj = new ClientPhonesObj();
            $clientPhonesObj->no = 'Something went wrong!';
            return collect(['error'=>$clientPhonesObj]);
        }
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Suite',
            'E-mail',
            'Phone 1',
            'Phone 2',
            'Created date'
        ];
    }
}
