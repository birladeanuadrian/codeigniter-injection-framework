<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(FCPATH.'application/libraries/ExitHandler.php');

class PostBuildHook
{
    /**
     * @var MY_Controller
     */
    protected $o_ci;

    protected $s_controller;

    protected $s_method;

    protected $o_exit_handler;

    public function __construct()
    {
        $this->o_ci = &get_instance();

        $this->s_controller = $this->o_ci->router->class;
        $this->s_method = $this->o_ci->router->method;

        $this->o_exit_handler = new ExitHandler($this->o_ci);
    }

    /**
     * @param $o_reflection ReflectionClass|ReflectionMethod
     */
    protected function get_annotations($o_reflection)
    {
        $doc = $o_reflection->getDocComment();
        $a_annotations = $this->get_php_doc_arguments($doc);
        foreach ($a_annotations as $idx => $s_annotation)
        {
            $a_annotations[$idx] = str_replace(array("\n", "\r"), '', $s_annotation);
        }

        return $a_annotations;
    }

    protected function get_php_doc_arguments($s_php_doc)
    {
        preg_match_all('#@(.*?)\n#s', $s_php_doc, $annotations);
        return $annotations[1];
    }
}