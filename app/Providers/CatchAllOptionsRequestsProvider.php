<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class CatchAllOptionsRequestsProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $request = app('request');

        if ($request->isMethod('OPTIONS')) {
            app()->options($request->path(), function() {
                return response('', 200)->header('Access-Control-Allow-Methods', 'HEAD, GET, POST, PUT, PATCH, DELETE')
                                        ->header('Access-Control-Allow-Origin', '*')
                                        ->header('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization');
            });
        }
    }
}
