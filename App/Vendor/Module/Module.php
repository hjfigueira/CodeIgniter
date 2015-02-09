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
                'index'             => 'Vendor/Module/Welcome/home',
                'index/(:any)'      => 'Vendor/Module/Welcome/home/$1'
            );
        }
    }

}