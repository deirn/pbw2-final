<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$views = [
    '/'                            => 'root',
    'home',
    'search',
    'info',
    'settings/profile'             => 'profile_settings',
    'status/(:num)'                => 'status/$1',
    'profile/(:segment)'           => 'profile/$1',
    'profile/(:segment)/following' => 'following/$1',
    'profile/(:segment)/followers' => 'followers/$1',
    'profile/(:segment)/export'    => 'export_connections/$1',
];

$api = [
    'get_home_status',
    'get_profile_status',
    'get_status_ancestor',
    'get_reply',
    'post_status',
    'post_reply',
    'edit_status',
    'edit_profile',
    'like',
    'follow',
    'search',
    'delete_status',
];

$auth = [
    'login',
    'register',
];

// ---

foreach ($views as $key => $val) {
    $url = is_int($key) ? $val : $key;
    $routes->get($url, "ViewController::$val");
}

foreach ($api as $key => $val) {
    $url = is_int($key) ? $val : $key;
    $routes->post("api/$url", "ApiController::$val");
}

foreach ($auth as $key => $val) {
    $url = is_int($key) ? $val : $key;
    $routes->get($url, "AuthController::$val");
}
