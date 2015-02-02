<?php

namespace App\Vendor\Module
{
    //@todo
    // use System/Core/StandardModule();
    // use System/Core/Intefaces/ModuleInit();

    //class Init extends StandardModule implements SystemInit
    class Module
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