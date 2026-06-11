<?php
// config/routes.php

use App\Core\Router;

$router = new Router($container);

$router->get('/',                         'HomeController@index');
$router->get('/movies',                   'MovieController@index');
$router->get('/movies/{id}',              'MovieController@detail');
$router->get('/booking/{showtimeId}',     'BookingController@seatMap');
$router->post('/booking/hold',            'BookingController@holdSeats');
$router->post('/booking/apply-promo',     'BookingController@applyPromo');
$router->get('/payment',                  'PaymentController@index');
$router->post('/payment/confirm',         'PaymentController@confirm');
$router->get('/payment/success',          'PaymentController@success');

$router->get('/login',                    'AuthController@loginForm');
$router->post('/login',                   'AuthController@login');
$router->get('/register',                 'AuthController@registerForm');
$router->post('/register',                'AuthController@register');
$router->post('/logout',                  'AuthController@logout');

$router->get('/my-tickets',               'MovieController@myTickets');

$router->get('/admin/dashboard',          'AdminController@dashboard');
$router->get('/admin/movies',             'AdminController@movies');
$router->post('/admin/movies',            'AdminController@storeMovie');

return $router;
