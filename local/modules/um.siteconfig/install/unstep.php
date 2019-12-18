<?php
/**
 * Created by PhpStorm.
 * User: semen
 * Date: 26.11.19
 * Time: 16:07
 */
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if(!check_bitrix_sessid()){

    return;
}

echo(CAdminMessage::ShowNote(Loc::getMessage("UM_SITE_CONFIG_UNSTEP_BEFORE")." ".Loc::getMessage("UM_SITE_CONFIG_UNSTEP_AFTER")));
?>

<form action="<? echo($APPLICATION->GetCurPage()); ?>">
    <input type="hidden" name="lang" value="<? echo(LANG); ?>" />
    <input type="submit" value="<? echo(Loc::getMessage("UM_SITE_CONFIG_UNSTEP_SUBMIT_BACK")); ?>">
</form>