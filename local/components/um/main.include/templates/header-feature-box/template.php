<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>
<div class="feature-box feature-box-style-2 align-items-center">
    <?if($arParams["CLASS_ICON"]):?>
        <div class="feature-box-icon d-none d-sm-flex">
            <i class="<?=$arParams["CLASS_ICON"]?> text-7 p-relative"></i>
        </div>
    <?endif;?>
    <?if($arResult["FILE"] <> ''):?>
        <div class="feature-box-info">
            <p class="pb-0 line-height-5 text-2">
                <?include($arResult["FILE"]);?>
            </p>
        </div>
    <?endif;?>
</div>

