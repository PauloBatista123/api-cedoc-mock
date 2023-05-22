<?php

namespace App\Http\Services;

use Monolog\Logger;
use Logtail\Monolog\LogtailHandler;
use Illuminate\Support\Facades\Log;

class LogService {

    public function __construct(){}

    public function info(string $message, array $context)
    {
        Log::channel('json')->info($message, $context);
    }

    public function error(string $message, array $context){
        Log::channel('json')->error($message, $context);
    }
}
