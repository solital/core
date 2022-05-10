<?php

use Solital\Core\Course\Course;

/** Forgot Routers */
Course::get('/forgot', 'Auth\ForgotController@forgot')->name('forgot');
Course::post('/forgot-post', 'Auth\ForgotController@forgotPost')->name('forgot.post');
Course::get('/change/{hash}', 'Auth\ForgotController@change')->name('change');
Course::post('/change-post/{hash}', 'Auth\ForgotController@changePost')->name('change.post');
