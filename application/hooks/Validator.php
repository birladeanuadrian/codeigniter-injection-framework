<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(FCPATH.'application/hooks/PostBuildHook.php');

class Validator extends PostBuildHook
{
    const INVALID_CONTROLLER_HEADER = "Internal Server Error";
    const INVALID_CONTROLLER_MESSAGE = "Controller is not valid";

    const UNKNOWN_PERMISSION_HEADER = "Internal Server Error";
    const UNKNOWN_PERMISSION_MESSAGE = "Unknown User permission ";

    const NOT_AJAX_REQUEST_HEADER = "Bad Request";
    const NOT_AJAX_REQUEST_MESSAGE = "Direct access is not allowed";

    const PERMISSION_DENIED_HEADER = "Permission denied";
    const PERMISSION_DENIED_MESSAGE = "You do not have permission to access this resource";

    public function __construct()
    {
        parent::__construct();
    }

    public function validate()
    {
        if (!$this->o_ci->_is_valid_controller())
        {
            $this->o_exit_handler->exit_ci(self::INVALID_CONTROLLER_HEADER,
                self::INVALID_CONTROLLER_MESSAGE);
        }

        $s_method = $this->o_ci->router->method;
        $o_class_reflection = new ReflectionClass($this->s_controller);
        $o_method_reflection = new ReflectionMethod($this->s_controller, $s_method);

        $a_annotations = array_merge($this->get_annotations($o_class_reflection),
            $this->get_annotations($o_method_reflection));

        foreach ($a_annotations as $s_annotation)
        {
            preg_match('/([\w\_\d]+)\(([\w\W]*)\)/', $s_annotation, $a_elements);
            if (count($a_elements) === 0)
                continue;

            $s_method = $a_elements[1];
            $s_arguments = $a_elements[2];

            $arg = array('Arguments' => $s_arguments);
            if (self::STATUS_SUCCESS !== call_user_func(array($this, $s_method), $arg))
            {
                $s_message = "Call to $s_method failed";
                echo $s_message;
            }
        }
    }

    private function ajaxRequest($arg)
    {
        if (!$this->o_ci->input->is_ajax_request())
        {
            $this->o_exit_handler->exit_ci(self::NOT_AJAX_REQUEST_HEADER,
                self::NOT_AJAX_REQUEST_MESSAGE);
        }
        $this->o_exit_handler->set_ajax_request();
        return self::STATUS_SUCCESS;
    }

    private function permission($arg)
    {
        $s_arguments = $arg['Arguments'];
        $s_permission = $s_arguments;

        $i_permission_flag = constant($s_permission);

        if (!$i_permission_flag)
        {
            $this->o_exit_handler->exit_ci(self::UNKNOWN_PERMISSION_HEADER,
                self::UNKNOWN_PERMISSION_MESSAGE . "$s_permission");
        }

        $i_user_flags = $this->o_ci->_get_logged_user_flags();

        if (!($i_permission_flag & $i_user_flags))
        {
            $this->o_exit_handler->exit_ci(self::PERMISSION_DENIED_HEADER,
                self::PERMISSION_DENIED_MESSAGE);
        }
        return self::STATUS_SUCCESS;
    }

    private function session()
    {
        $this->o_ci->load->library('session');
        return self::STATUS_SUCCESS;
    }

    /**
     * Should be placed after @session(), since it requires access to session
     * @return int
     */
    private function auth()
    {
        $a_user_data = $this->o_ci->session->userdata('userInfo');
        if (!is_array($a_user_data))
        {
            $this->o_ci->session->set_userdata("CurrentUrl", current_url());
            $this->o_exit_handler->redirect_login();
        }
        else
        {
            $this->o_ci->a_current_user_info = $a_user_data;
        }
        return self::STATUS_SUCCESS;
    }

}
