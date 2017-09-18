<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/

$hook['post_controller_constructor'][] = array(
    'class' => 'Validator',
    'function' => 'validate',
    'filename' => 'Validator.php',
    'filepath' => 'hooks',
    'params' => null
);

$hook['post_controller_constructor'][] = array(
    'class' => 'Injector',
    'function' => 'inject',
    'filename' => 'Injector.php',
    'filepath' => 'hooks',
    'params' => null
);
