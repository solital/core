<?php

/**
 * Solital Framework 3.0.4
 * 
 * teste3 - comentario de teste para a rota personalizada
 * 
 * @generated class generated using Vinci Console
 */
use Solital\Core\Course\Course;

Course::get('/', function () {
    return view('');
});
