<?php

namespace App\Http\Controllers;

use App\Http\Model\sfc\City;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;

class SfcController extends Controller
{
    public function getCities(Request $request) {
        if (!Redis::command('EXISTS', ['cities']))
            return $this->refresh($request);

        $cities = Redis::get('cities');
        return response($cities)->header('Content-Type', 'application/json');
    }
    
    public function refresh(Request $request) {
        Redis::command('DEL', ['cities']);  // 先清Cache
        
        $cities = City::find();             // 再取DB
        $data = json_encode($cities);
        Redis::set('cities', $data);
        
        return $this->getCities($request);
    }
}