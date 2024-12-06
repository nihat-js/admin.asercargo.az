<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WareHouseController extends HomeController
{
    public function index()
    {
        return view('backend.warehouse.index');
    }

    public function get_partner_page(){
        return view('backend.warehouse.partner_index');
    }
}
