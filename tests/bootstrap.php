<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Keep global scope clean
call_user_func(function(){
    
    $mpxConfigFile = __DIR__ . '/config/mpx/local.php';
    
    if (is_readable($mpxConfigFile)) {
        
        $mpxConfig = include $mpxConfigFile;

        // Environment variables for MPX Auth tests
        defined('DWSLA_SERVICE_MPX_AUTH_USER') || define('DWSLA_SERVICE_MPX_AUTH_USER', !empty($mpxConfig['user']) ? $mpxConfig['user'] : '');
        defined('DWSLA_SERVICE_MPX_AUTH_PASS') || define('DWSLA_SERVICE_MPX_AUTH_PASS', !empty($mpxConfig['pass']) ? $mpxConfig['pass'] : '');

        // Environment variable for MPC Mediafeed tests
        defined('DWSLA_SERVICE_MPX_MEDIAFEED_ACCTPID') || define('DWSLA_SERVICE_MPX_MEDIAFEED_ACCTPID', !empty($mpxConfig['acctPid']) ? $mpxConfig['acctPid'] : '');
        defined('DWSLA_SERVICE_MPX_MEDIAFEED_FEEDPID') || define('DWSLA_SERVICE_MPX_MEDIAFEED_FEEDPID', !empty($mpxConfig['feedPid']) ? $mpxConfig['feedPid'] : '');
    }    
});
    
