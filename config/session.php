<?php

return [
    'lifetime' => (int) env('SESSION_LIFETIME', 86400),
    'cookie' => env('SESSION_COOKIE', 'mc_session'),
];
