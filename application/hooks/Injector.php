<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(FCPATH.'application/hooks/PostBuildHook.php');

class Injector extends PostBuildHook
{
    public function __construct()
    {
        parent::__construct();
    }

    public function inject()
    {
        $reflection = new ReflectionClass($this->s_controller);
        $a_properties = $reflection->getProperties();

        /**
         * @var ReflectionProperty $o_prop
         */
        foreach ($a_properties as $o_prop)
        {
            $s_docs = $o_prop->getDocComment();
            $a_annotations = $this->get_php_doc_arguments($s_docs);
            foreach ($a_annotations as $s_annotation)
            {
                preg_match('/([\w\_\d]+)\(([\w\W]*)\)/', $s_annotation, $a_elements);
                if (count($a_elements) === 0)
                    continue;

                $s_method = $a_elements[1];
                $s_arguments = $a_elements[2];


                $arg = array('Prop' => $o_prop, 'Arguments' => $s_arguments);
                if (self::STATUS_SUCCESS !== call_user_func(array($this, $s_method), $arg))
                {
                    $s_message = "Call to $s_method failed";
                    $this->o_exit_handler->exit_ci("Internal Server Error", $s_message);
                }
            }
        }

        return self::STATUS_SUCCESS;
    }

    private function model($arg)
    {
        /**
         * @var ReflectionProperty $o_prop
         */
        $o_prop = $arg['Prop'];
        $s_arguments = $arg['Arguments'];
        $s_model = $s_arguments;

        if ($s_model == '$construct')
        {
            $o_prop->setAccessible(true);
            $s_model = $o_prop->getValue($this->o_ci);
            if ($s_model == null) {
                $message = "You must set " . $arg['Prop'] . " to the name of the model received in the construct.\n";
                $this->o_exit_handler->exit_ci("Internal Server Error", $message);
                return self::STATUS_UNSUCCESSFUL;
            }
            $o_prop->setAccessible(false);
        }

        $this->o_ci->load->model($s_model);
        if (empty($this->o_ci->$s_model))
        {
            return self::STATUS_UNSUCCESSFUL;
        }

        $o_prop->setAccessible(true);
        $o_prop->setValue($this->o_ci, $this->o_ci->$s_model);
        $o_prop->setAccessible(false);
        return self::STATUS_SUCCESS;
    }

    private function config($arg)
    {
        /**
         * @var ReflectionProperty $o_prop
         */
        $o_prop = $arg['Prop'];
        $s_config_name = $o_prop->getName();
        $s_arguments = $arg['Arguments'];

        $s_arguments = str_replace(' ', '', $s_arguments);
        $a_arguments = explode(',', $s_arguments);

        $s_item = $a_arguments[0];

        if ($s_item === '$construct')
        {
            $o_prop->setAccessible(true);
            $s_item = $o_prop->getValue($this->o_ci);
            if ($s_item === null) {
                $message = "You must set $s_item to the name of the config received in the construct.\n";
                $this->o_exit_handler->exit_ci("Internal Server Error", $message);
                return self::STATUS_UNSUCCESSFUL;
            }
            $o_prop->setAccessible(false);
        }

        if ($s_item === false)
        {
            return self::STATUS_SUCCESS;
        }

        $m_config = $this->o_ci->config->item($s_item);

        $b_has_type = true;
        if (count($a_arguments) === 1)
        {

            $s_message = "Config $s_config_name does not have a defined type";
            $b_has_type = false;
        }

        if ($b_has_type)
        {
            $s_type = $a_arguments[1];

            $s_actual_type = gettype($m_config);
            if (strcmp($s_type, $s_actual_type) !== 0)
            {
                $s_message = "Configuration $s_config_name is a $s_actual_type, not a $s_type";
                $this->o_exit_handler->exit_ci("Invalid configuration", $s_message);
            }
        }

        $o_prop->setAccessible(true);
        $o_prop->setValue($this->o_ci, $m_config);
        $o_prop->setAccessible(false);

        return self::STATUS_SUCCESS;
    }
}