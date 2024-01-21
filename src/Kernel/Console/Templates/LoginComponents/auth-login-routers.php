<?php

use Solital\Core\Course\Course;

/** Login Routers */

Course::get('/auth', 'Auth\LoginController@auth')->name('auth');
Course::post('/auth-post', 'Auth\LoginController@authPost')->name('auth.post');

Course::group(['middleware' => middleware('auth')], function () {
    Course::get('/dashboard', 'Auth\LoginController@dashboard')->name('dashboard');
    Course::get('/logoff', 'Auth\LoginController@exit')->name('logoff');
});
