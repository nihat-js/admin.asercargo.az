<?php

namespace App\Http\Controllers\Classes;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class Functions extends Controller
{
    public function validate_for_api($variables) {
        try {
            $validate = false;
            for ($i = 0; $i < count($variables); $i++) {
                if ($variables[$i] == null || empty($variables[$i])) {
                    $validate = true;
                    break;
                }
            }

            return $validate;
        } catch (\Exception $exception) {
            return true;
        }
    }
}
