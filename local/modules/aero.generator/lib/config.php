<?php
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 11.09.2017
 * Time: 23:49
 */

namespace Aero\Generator;

use Bitrix\Main\Config\Option;

class Config
{
    private $options;

    protected static $instance;

    public static function getInstance()
    {
        if(empty(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function getOptions():array {
        return $this->options;
    }

    public function getOption(string $option):string
    {
        return $this->options[$option];
    }

    private function __construct()
    {
        $this->options["types_catalog"]         = 1; // 1 catalog is enough
        $this->options["types_product"]         = Option::get("aero.generator", "types_product");
        $this->options["types_price"]           = Option::get("aero.generator", "types_price");
        $this->options["types_store"]           = Option::get("aero.generator", "types_store");
        $this->options["types_productproperty"] = Option::get("aero.generator", "types_productproperty");
        $this->options["types_skuproperty"]     = Option::get("aero.generator", "types_skuproperty");
    }
}