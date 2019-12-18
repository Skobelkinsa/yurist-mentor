<?php
/**
 * Created by PhpStorm.
 * User: semen
 * Date: 26.11.19
 * Time: 18:04
 */
$arMenu = array(

    'parent_menu' => 'global_menu_content',
    'sort' => 150,
    'text' => GetMessage('UM_SITE_CONFIG_REPORTS_MENU'),
    'title' => GetMessage('UM_SITE_CONFIG_MENU'),
    'icon' => 'util_menu_icon',
    'page_icon' => 'util_menu_icon',
    'items_id' => 'global_menu_site_config_um_items',
    'items' => array(
        array(
            'text' => GetMessage('UM_SITE_CONFIG_MENU_1'),
            'title' => GetMessage('UM_SITE_CONFIG_REPORTS_MENU_1'),
            'url' => '/bitrix/admin/settings.php?lang=ru&mid=um.siteconfig',
        ),
        array(
            'text' => GetMessage('UM_SITE_CONFIG_MENU_2'),
            'title' => GetMessage('UM_SITE_CONFIG_REPORTS_MENU_2'),
            'url' => '/test/',
        ),

    )
);

return (!empty($arMenu) ? $arMenu : false);