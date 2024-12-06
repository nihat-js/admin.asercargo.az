<?php

namespace App\Exports;

use App\User;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ClientEmailsWithParentIdExport implements FromCollection, WithHeadings, ShouldAutoSize
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
				return collect(['error' => $clientPhonesObj]);
			}

			return User::distinct('email')->select('email', 'parent_id')->get();

		} catch (\Exception $exception) {
			$clientPhonesObj = new ClientPhonesObj();
			$clientPhonesObj->no = 'Something went wrong!';
			return collect(['error' => $clientPhonesObj]);
		}
	}


	public function headings(): array
	{
		return [
				'E-mail',
				'Parent ID',
		];
	}
}
