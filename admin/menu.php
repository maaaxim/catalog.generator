<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$menu = [
    [
        'parent_menu' => 'global_menu_services',
        'sort' => 5000,
        'text' => Loc::getMessage('CATALOG_MAKER_MENU_TITLE'),
        'title' => Loc::getMessage('CATALOG_MAKER_MENU_TITLE'),
        'items_id' => 'menu_references',
        'items' => [
            [
                'text' => Loc::getMessage('CATALOG_MAKER_SUBMENU_TITLE'),
                'url' => 'catalog_generator_controller.php',
                'more_url' => ['catalog_generator_controller.php'],
                'title' => Loc::getMessage('CATALOG_MAKER_SUBMENU_TITLE'),
            ]
        ],
    ],
];

return $menu;
