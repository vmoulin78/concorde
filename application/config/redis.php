<?php
defined('APP_ENTRY_PASS') OR exit('No direct script access allowed');

$section = 'Redis';

/*
| -------------------------------------------------------------------------
| Redis settings
| -------------------------------------------------------------------------
|
|   See: https://codeigniter.com/user_guide/libraries/caching.html#redis-caching
|
*/
$config['socket_type']  = 'tcp';  // 'tcp' or 'unix'
$config['socket']       = '/var/run/redis.sock';  // in case of 'unix' socket type
$config['host']         = '127.0.0.1';
$config['password']     = NULL;
$config['port']         = 6379;
$config['timeout']      = 0;
