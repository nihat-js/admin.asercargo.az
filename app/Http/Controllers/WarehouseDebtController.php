<?php

namespace App\Http\Controllers;

use App\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WarehouseDebtController extends Controller
{
    public function getDebt(){
        $debts = DB::table('WarehouseCalcDebt')->get();
    
        return view("backend.warehouse_debt", compact('debts'));
    }
    
    public function updateDebt(Request $request){
        $validator = Validator::make($request->all(), [
            'amount' => ['required', 'numeric'],
            'day' => ['required', 'integer'],
            'id' => ['required', 'integer'],
            'limitDay' => ['required', 'integer'],
            'type' => ['nullable', 'string'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $id = $request->id;
            unset($request['id'], $request['_token'], $request['type']);
        
            DB::table('WarehouseCalcDebt')->where(['id'=>$id])->update($request->all());
        
            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }
}
