<?php
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 22.08.2017
 * Time: 23:25
 */

namespace Catalog\Generator\Types;

use Bitrix\Catalog\StoreTable;
use Faker\Factory;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

class Store implements Generateable
{
    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * @var price name
     */
    protected $name;

    /**
     * @var price code
     */
    protected $code;

    /**
     * Store constructor
     */
    public function __construct()
    {
        $this->faker = Factory::create('ru_RU');
        $this->includeModules();
    }

    /**
     * Generate store
     */
    function generate()
    {
        $code = $this->faker->md5;
        StoreTable::add([
            "TITLE" => $this->faker->address,
            "ACTIVE" => "Y",
            "ADDRESS" => $this->faker->address,
            "DESCRIPTION" => $this->faker->text(150),
            "GPS_N" => $this->faker->latitude,
            "GPS_S" => $this->faker->longitude,
            "PHONE" => $this->faker->phoneNumber,
            "XML_ID" => $code,
            "SORT" => 500,
            "EMAIL" => $this->faker->email,
            "ISSUING_CENTER" => "Y",
            "SHIPPING_CENTER" => "Y",
            "SITE_ID" => "s1",
            "CODE" => $code
        ]);
    }

    /**
     * Including modules
     *
     * @throws \Exception
     */
    private function includeModules()
    {
        if(!Loader::includeModule("catalog"))
            throw new \Exception("Can't include catalog module");
    }

    public function getStepSize():int
    {
        return (int) Option::get("catalog.generator", "types_store");
    }
}