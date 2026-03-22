<?php
define('SUPABASE_URL',         getenv('SUPABASE_URL')         ?: 'https://dablnrggyfcddmdeiqxi.supabase.co');
define('SUPABASE_KEY',         getenv('SUPABASE_KEY')         ?: 'sb_publishable_d8mzJ3iulCU7YdlV_lrdQw_32pOzDXc');
define('SUPABASE_SERVICE_KEY', getenv('SUPABASE_SERVICE_KEY') ?: 'sb_secret_VlGl6UXSTT8CB_YIqJZ-zw_anyyL2d2');
define('ADMIN_KEY',            getenv('ADMIN_KEY')            ?: 'JaynesAdmin@2025!ConfirmKey');
define('PLANS', [
    'wiki'   => ['days'=>7,   'amount'=>500,   'label'=>'Wiki'],
    'mwezi'  => ['days'=>30,  'amount'=>1500,  'label'=>'Mwezi'],
    'miezi3' => ['days'=>90,  'amount'=>3500,  'label'=>'Miezi 3'],
    'miezi6' => ['days'=>180, 'amount'=>6000,  'label'=>'Miezi 6'],
    'mwaka'  => ['days'=>365, 'amount'=>10000, 'label'=>'Mwaka'],
]);
define('PAY_METHODS', [
    'mpesa'       => ['name'=>'M-PESA',      'number'=>'0616393956'],
    'tigopesa'    => ['name'=>'Tigo Pesa',   'number'=>'0616393956'],
    'airtelmoney' => ['name'=>'Airtel Money','number'=>'0616393956'],
    'halopesa'    => ['name'=>'Halo Pesa',   'number'=>'0616393956'],
]);
