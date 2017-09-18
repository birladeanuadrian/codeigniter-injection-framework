<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class ExitHandler
 * This class is meant to be application specific and won't suffer any major modifications,
 * except possibly adding more public functions to be implemented by users
 */
class ExitHandler
{
    private static $b_ajax_request;

    private $o_ci;


    public function __construct($o_ci=null)
    {
        $this->o_ci = $o_ci;
        ExitHandler::$b_ajax_request = false;
    }

    public function set_ajax_request()
    {
        ExitHandler::$b_ajax_request = true;
    }

    public function exit_ci($s_header, $s_message)
    {
        if (!ExitHandler::$b_ajax_request)
            $this->exit_view($s_header, $s_message);
        else
            $this->exit_ajax($s_header, $s_message);

        exit();
    }

    private function exit_ajax($s_header, $s_message)
    {
        echo json_encode(array("Error" => "$s_header; $s_message"));
    }

    private function exit_view($s_header, $s_message)
    {
        exit("<b>$s_header</b> $s_message");
    }

    public function redirect_login()
    {
        redirect(site_url(array('login')));
    }

}