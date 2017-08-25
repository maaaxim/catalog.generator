<?php
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 22.08.2017
 * Time: 23:08
 */

namespace Aero;

class Generator
{
    public function setConfig(){}

    public function createItem(Types\Generateable $item){
        $item->generate();
    }
}