<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    public $a_current_user_info;

    protected $b_valid_controller;

    public function __construct()
    {
        parent::__construct();

        $this->b_valid_controller = true;
    }

    public function _get_logged_user_flags(){

    }

    public function _get_logged_user_name(){

    }

    public function _is_valid_controller(){
        return $this->b_valid_controller;
    }
}