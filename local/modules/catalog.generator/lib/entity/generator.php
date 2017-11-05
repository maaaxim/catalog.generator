<?php
namespace Catalog\Generator\Entity;

use Bitrix\Main,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class GeneratorTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'catalog_generator';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return [
            'ID' => [
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true,
                'title' => Loc::getMessage('GENERATOR_ENTITY_ID_FIELD'),
            ],
            'STEP' => [
                'data_type' => 'integer',
                'title' => Loc::getMessage('GENERATOR_ENTITY_ITEMS_STEP_FIELD'),
            ],
            'STATUS' => [
                'data_type' => 'integer',
                'title' => Loc::getMessage('GENERATOR_ENTITY_STATUS_FIELD'),
            ],
            'ITEMS_PER_STEP' => [
                'data_type' => 'integer',
                'title' => Loc::getMessage('GENERATOR_ENTITY_ITEMS_PER_STEP_FIELD'),
            ],
            'CREATED' => [
                'data_type' => 'datetime',
                'title' => Loc::getMessage('GENERATOR_ENTITY_CREATED_FIELD'),
            ],
        ];
    }
}