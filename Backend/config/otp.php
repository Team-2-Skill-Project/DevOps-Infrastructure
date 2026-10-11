<?php

return [
    'static_enabled' => (bool) env('STATIC_OTP_ENABLED', false),
    'static_code' => env('STATIC_OTP_CODE'),
];
