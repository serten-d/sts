<?php

/**
 * Ustawienie odpowiedniej konfiguracji PHPUnit dla akol i app-partner
 */

//Testy Akolowe
include_once('modules/unittest/bootstrap_all_modules.php');

//ustawiamy, aby wylaczyc informacje o przestarzalej bibliotece mcrypt
error_reporting(E_ALL & ~E_DEPRECATED);
