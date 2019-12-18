<?php
/**
 * Created by PhpStorm.
 * User: semen
 * Date: 26.11.19
 * Time: 15:13
 */
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Iblock\InheritedProperty;

Loc::loadMessages(__FILE__);

define("LOG_FILENAME", __DIR__."/log.txt");
@set_time_limit(99600);
ini_set("memory_limit", "4096M");
ini_set("max_execution_time", "0");
ini_set("max_input_time", "0");

$request = HttpApplication::getInstance()->getContext()->getRequest();

$module_id = htmlspecialcharsbx($request["mid"] != "" ? $request["mid"] : $request["id"]);

Loader::includeModule($module_id);
Loader::includeModule('iblock');

$dbItems = Bitrix\Main\SiteTable::getList(array(
    'order' => array('SORT' => 'DESC'),
    'select' => array('LID', 'NAME'),
    'filter' => array(),
));
while ($arSite = $dbItems->fetch())
{
    $arSites[$arSite["LID"]] = $arSite["NAME"];
}

$dbItems = Bitrix\Iblock\IblockTable::getList(array(
    'order' => array('SORT' => 'DESC'),
    'select' => array('ID', 'NAME'),
    'filter' => array(),
));
while ($arIB = $dbItems->fetch())
{
    $arIBlocks[$arIB["ID"]] = $arIB["NAME"];
}

$aTabs = array(
    array(
        "DIV"       => "edit",
        "TAB"       => Loc::getMessage("UM_SITE_CONFIG_OPTIONS_TAB_NAME"),
        "TITLE"   => Loc::getMessage("UM_SITE_CONFIG_OPTIONS_TAB_NAME"),
        "OPTIONS" => array(
            Loc::getMessage("UM_SITE_CONFIG_OPTIONS_TAB_COMMON"),
            array(
                "iblock",
                Loc::getMessage("UM_SITE_CONFIG_OPTIONS_TAB_IBLOCK"),
                "",
                array("selectbox", $arIBlocks)
            ),
            array(
                "site_of",
                Loc::getMessage("UM_SITE_CONFIG_OPTIONS_TAB_SITE_DONNER"),
                "",
                array("selectbox", $arSites)
            ),
            array(
                "site_in",
                Loc::getMessage("UM_SITE_CONFIG_OPTIONS_TAB_SITE"),
                "left",
                array("selectbox",$arSites)
            ),
            array(
                "switch_delete",
                Loc::getMessage("UM_SITE_CONFIG_OPTIONS_TAB_DELETE"),
                "Y",
                array("checkbox")
            ),
            Loc::getMessage("UM_SITE_CONFIG_OPTIONS_TAB_REPLACE"),
            array(
                "switch_replace",
                Loc::getMessage("UM_SITE_CONFIG_OPTIONS_TAB_REPLACE_SWITCH"),
                "Y",
                array("checkbox")
            ),
            array(
                "from",
                Loc::getMessage("UM_SITE_CONFIG_OPTIONS_TAB_REPLACE_FROM"),
                "",
                array("text", 50)
            ),
            array(
                "before",
                Loc::getMessage("UM_SITE_CONFIG_OPTIONS_TAB_REPLACE_BEFORE"),
                "",
                array("text", 50)
            )
        )
    )
);

$tabControl = new CAdminTabControl(
    "tabControl",
    $aTabs
);

$tabControl->Begin();
?>
    <form action="<? echo($APPLICATION->GetCurPage()); ?>?mid=<? echo($module_id); ?>&lang=<? echo(LANG); ?>" method="post">

        <?
        foreach($aTabs as $aTab){

            if($aTab["OPTIONS"]){

                $tabControl->BeginNextTab();

                __AdmSettingsDrawList($module_id, $aTab["OPTIONS"]);
            }
        }

        $tabControl->Buttons();
        ?>

        <input type="submit" name="apply" value="<? echo(Loc::GetMessage("UM_SITE_CONFIG_OPTIONS_INPUT_APPLY")); ?>" class="adm-btn-save" />

        <?
        echo(bitrix_sessid_post());
        ?>

    </form>
<?$tabControl->End();

if($request->isPost() && check_bitrix_sessid()){

    foreach($aTabs as $aTab){

        foreach($aTab["OPTIONS"] as $arOption){

            if(!is_array($arOption)){

                continue;
            }

            if($arOption["note"]){

                continue;
            }

            if($request["apply"]){

                $optionValue = $request->getPost($arOption[0]);

                if($arOption[0] == "switch_delete" || $arOption[0] == "switch_replace"){

                    if($optionValue == ""){

                        $optionValue = "N";
                    }
                }

                Option::set($module_id, $arOption[0], is_array($optionValue) ? implode(",", $optionValue) : $optionValue);
            }
        }
    }

    $error = 0;
    $success = 0;
    $deletes = 0;

    if($request->getPost("switch_delete")=="Y"){
        $rsDelete = CIBlockElement::GetList(Array(),Array("IBLOCK_ID"=>$request->getPost("iblock"),"PROPERTY_SITE"=>$request->getPost("site_in")),false,false,array("ID","PREVIEW_PICTURE"));
        while($arDelete = $rsDelete->GetNext()){
            CIBlockElement::Delete($arDelete["ID"]);
            $deletes++;
        }
    }

    $arSelect = Array("ID", "IBLOCK_ID", "NAME", "PREVIEW_TEXT", "PREVIEW_PICTURE", "IBLOCK_SECTION_ID");
    $properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$request->getPost("iblock")));
    while ($prop_fields = $properties->GetNext())
        $arSelect[] = "PROPERTY_".$prop_fields["CODE"];

    $arFilter = Array(
        "ACTIVE"=>"Y",
        "IBLOCK_ID"=>$request->getPost("iblock"),
        "PROPERTY_SITE"=>$request->getPost("site_of")
    );
    $rsDonner = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
    while($arDonner = $rsDonner->GetNext()){
        $db_props = CIBlockElement::GetProperty($request->getPost("iblock"), $arDonner["ID"], array("sort" => "asc"), Array(/*"PROPERTY_TYPE"=>"S"*/));
        while($ar_props = $db_props->Fetch()){
            if($ar_props["PROPERTY_TYPE"]=="S" && is_array($ar_props["VALUE"]) && strlen($ar_props["VALUE"]["TEXT"])>0)
                $PROP[$ar_props["CODE"]] = array(
                    "TEXT" => $ar_props["VALUE"]["TEXT"],
                    "TYPE" => $ar_props["VALUE"]["TYPE"],
                );
            elseif(strlen($ar_props["VALUE"]))
                $PROP[$ar_props["CODE"]] = $ar_props["VALUE"];
        }

        $arLoadProductArray = Array(
            "IBLOCK_SECTION_ID" => $arDonner["IBLOCK_SECTION_ID"],
            "IBLOCK_ID"      => $arDonner["IBLOCK_ID"],
            "PROPERTY_VALUES"=> $PROP,
            "NAME"           => $arDonner["NAME"],
            "ACTIVE"         => "Y",
            "PREVIEW_TEXT"   => $arDonner["PREVIEW_TEXT"],
            "PREVIEW_PICTURE"   => CFile::MakeFileArray(CFile::GetPath($arDonner["PREVIEW_PICTURE"])),
        );

        $ipropValues = new InheritedProperty\ElementValues($arDonner["IBLOCK_ID"], $arDonner["ID"]);
        $arElMetaProp = $ipropValues->getValues();

        if($request->getPost("switch_replace")=="Y"){
            $from = explode(", ", $request->getPost("from"));
            $before = explode(", ", $request->getPost("before"));
            $arLoadProductArray["NAME"] = str_replace(
                $from,
                $before,
                $arLoadProductArray["NAME"]
            );
            $arLoadProductArray["PREVIEW_TEXT"] = str_replace(
                $from,
                $before,
                $arLoadProductArray["PREVIEW_TEXT"]
            );
            foreach ($arLoadProductArray["PROPERTY_VALUES"] as $CODE => $arReplace){
                if(is_array($arReplace)){
                    $arLoadProductArray["PROPERTY_VALUES"][$CODE] = array(
                        "TEXT" => str_replace($from,$before,$arReplace["TEXT"]),
                        "TYPE" => $arReplace["TYPE"],
                    );
                }else{
                    $arLoadProductArray["PROPERTY_VALUES"][$CODE] = str_replace(
                        $from,
                        $before,
                        $arReplace
                    );
                }
            }
            $arElMetaProp["ELEMENT_META_TITLE"] = str_replace(
                $from,
                $before,
                $arElMetaProp["ELEMENT_META_TITLE"]
            );
            $arElMetaProp["ELEMENT_META_DESCRIPTION"] = str_replace(
                $from,
                $before,
                $arElMetaProp["ELEMENT_META_DESCRIPTION"]
            );
        }
        $arLoadProductArray["PROPERTY_VALUES"]["SITE"] = array("VALUE" => $request->getPost("site_in"));

        $el = new CIBlockElement;

        if($PRODUCT_ID = $el->Add($arLoadProductArray)){
            $ipropTemplates = new InheritedProperty\ElementTemplates($arDonner["IBLOCK_ID"], $PRODUCT_ID);
            $ipropTemplates->set(array(
                "ELEMENT_META_TITLE" 	=> $arElMetaProp["ELEMENT_META_TITLE"],
                "ELEMENT_META_DESCRIPTION" 	=> $arElMetaProp["ELEMENT_META_DESCRIPTION"]
            ));
            $success++;
        }else{
            AddMessage2Log($el->LAST_ERROR, "Error:".$PRODUCT_ID);
            $error++;
        }

    }

    LocalRedirect($APPLICATION->GetCurPage()."?mid=".$module_id."&lang=".LANG."&success=".$success."&errors=".$error."&deletes=".$deletes);
}

if($request->get("success")){
    echo CAdminMessage::ShowNote(Loc::getMessage("UM_SITE_CONFIG_MESSAGE_SUCCESS", array("#COUNT#"=>$request->get("success"), "#ERRORS#"=>$request->get("errors"), "#DELETES#"=>$request->get("deletes"))));
}
