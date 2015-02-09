<?php
/**
 * Created by PhpStorm.
 * User: agencia110
 * Date: 02/02/15
 * Time: 18:59
 */

namespace System\Core\Interfaces;


interface StandardModule {

    public function getName();

    public function getRoute();

    public function getInternalRoute();

}