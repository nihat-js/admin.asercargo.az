<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ExcelImport;

class ExcelController extends Controller
{

    public function show(){
        return view('backend.Excel');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $file = $request->file('file');
        $data = Excel::toArray([], $file);

        // Başlık satırını atlayarak veri satırlarını işlemeye başla
        dd($file);
        foreach (array_slice($data[0], 1) as $row) {
            // Dizinin indeksleri kullanarak bireysel sütunlara eriş
            $orderNumber = $row[1]; // "Order number" sütunu ikinci sırada varsayılan olarak kabul edilmiştir
            dd($orderNumber);
            // Veri ile ilgili işlemleri gerçekleştir, örneğin, veriyi veritabanına kaydet
            // Eloquent veya başka bir yöntem kullanarak veriyi veritabanına kaydedebilirsiniz

            // Eloquent kullanarak örnek:
            // YourModel::create(['order_number' => $orderNumber]);

            // Varolan bir kaydı güncellemeniz gerekiyorsa, önce veritabanını sorgulayabilirsiniz:
            // YourModel::where('order_number', $orderNumber)->update(['column' => 'value']);
        }

        return redirect()->back()->with('success', 'Veri eklendi.');
    }

}

