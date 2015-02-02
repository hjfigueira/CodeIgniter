<?php

namespace App\Vendor\Module
{

    use System\Core\Interfaces\StandardModule;

    class Module implements StandardModule
    {
        public function getName()
        {
            return 'Module';
        }

        public function getRoute()
        {
            return 'module';
        }

        public function getInternalRoute()
        {
            return array(
                'default_controller'    => 'welcome',
                '404_override'          => '',
                'translate_uri_dashes'  => FALSE
            );
        }
    }

}