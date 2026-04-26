<?php

/**
 * @file
 * Legacy autoloader for systems lacking spl_autoload_register
 *
 * Must be separate to prevent deprecation warning on PHP 7.2
 */

if (function_exists('spl_autoload_register')) {
    spl_autoload_register(array('HTMLPurifier_Bootstrap', 'autoload'));
}

// vim: et sw=4 sts=4
