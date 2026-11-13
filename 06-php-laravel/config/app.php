<?php
return [
 'name'=>env('APP_NAME','WebShoes'),
 'env'=>env('APP_ENV','production'),
 'debug'=>(bool) env('APP_DEBUG',false),
 'url'=>env('APP_URL','http://localhost'),
 'timezone'=>'America/Sao_Paulo',
 'locale'=>env('APP_LOCALE','pt_BR'),
 'fallback_locale'=>'en',
 'faker_locale'=>'pt_BR',
 'key'=>env('APP_KEY'),
 'previous_keys'=>array_filter(explode(',',env('APP_PREVIOUS_KEYS',''))),
 'cipher'=>'AES-256-CBC',
 'maintenance'=>['driver'=>'file'],
];
