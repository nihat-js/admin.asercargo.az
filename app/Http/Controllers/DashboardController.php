<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
        public function show()
        {
            $query = DB::table('package')
                ->select(
                    DB::raw('COUNT(DISTINCT package.id) as package_count'),
                    DB::raw('SUM(CASE 
                        WHEN package.gross_weight > package.volume_weight
                        THEN package.gross_weight 
                        ELSE package.volume_weight
                    END) as total_weight')
                )
                ->where('package.last_status_id', 15)
                ->where('in_baku', 1)
                ->whereNull('package.deleted_by');

            $results = $query->first();
    
            $now = Carbon::now();
    
            $last24Hours = DB::table('users')
                ->where('created_at', '>=', DB::raw('DATE_FORMAT(NOW(), "%Y-%m-%d 00:00:00")'))
                ->where('created_at', '<', DB::raw('DATE_ADD(DATE_FORMAT(NOW(), "%Y-%m-%d 00:00:00"), INTERVAL 1 DAY)'))
                ->count();
    
    
            //dd($last24Hours);
            $last3Days = DB::table('users')
                ->whereBetween('created_at', [
                    DB::raw('DATE_SUB(CURDATE(), INTERVAL 2 DAY)'),
                    DB::raw('NOW()')
                ])
                ->count();
            
            $thisMonth = DB::table('users')
                ->whereBetween('created_at', [
                    DB::raw('DATE_FORMAT(NOW() ,\'%Y-%m-01\')'),
                    DB::raw('NOW()')
                ])
                ->count();

            return view('backend.dashboard', compact('results', 'last24Hours', 'last3Days', 'thisMonth'));
        }
}
