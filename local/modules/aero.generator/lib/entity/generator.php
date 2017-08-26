<?php
namespace Aero\Generator\Entity;

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
        return 'aero_generator';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true,
                'title' => Loc::getMessage('GENERATOR_ENTITY_ID_FIELD'),
            ),
            'STATUS' => array(
                'data_type' => 'integer',
                'title' => Loc::getMessage('GENERATOR_ENTITY_STATUS_FIELD'),
            ),
            'TYPE' => array(
                'data_type' => 'text',
                'required' => true,
                'title' => Loc::getMessage('GENERATOR_ENTITY_TYPE_FIELD'),
            ),
            'ITEMS_PER_STEP' => array(
                'data_type' => 'integer',
                'title' => Loc::getMessage('GENERATOR_ENTITY_ITEMS_PER_STEP_FIELD'),
            ),
            'CREATED' => array(
                'data_type' => 'datetime',
                'title' => Loc::getMessage('GENERATOR_ENTITY_CREATED_FIELD'),
            ),
        );
    }
}