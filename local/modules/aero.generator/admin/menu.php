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
                'url' => 'aero_generator_controller.php?param1=paramval&lang=' . LANGUAGE_ID,
                'more_url' => ['aero_generator_controller.php?param1=paramval&lang=' . LANGUAGE_ID],
                'title' => Loc::getMessage('CATALOG_MAKER_SUBMENU_TITLE'),
            ]
        ],
    ],
];

return $menu;
