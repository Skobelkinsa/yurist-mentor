<?php
/**
 * Created by PhpStorm.
 * User: semen
 * Date: 26.11.19
 * Time: 15:14
 */
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ModuleManager,
    Bitrix\Main\Config\Option,
    Bitrix\Main\EventManager,
    Bitrix\Main\Application,
    Bitrix\Main\IO\Directory;

Loc::loadMessages(__FILE__);

class um_siteconfig extends CModule{

    // В конструкторе определяем информацию о модуле
    public function __construct(){

        if(file_exists(__DIR__."/version.php")){

            $arModuleVersion = array();

            include_once(__DIR__."/version.php");

            $this->MODULE_ID            = str_replace("_", ".", get_class($this));
            $this->MODULE_VERSION       = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE  = $arModuleVersion["VERSION_DATE"];
            $this->MODULE_NAME          = Loc::getMessage("UM_SITE_CONFIG_NAME");
            $this->MODULE_DESCRIPTION   = Loc::getMessage("UM_SITE_CONFIG_DESCRIPTION");
            $this->PARTNER_NAME         = Loc::getMessage("UM_SITE_CONFIG_PARTNER_NAME");
            $this->PARTNER_URI          = Loc::getMessage("UM_SITE_CONFIG_PARTNER_URI");
        }

        return false;
    }

    // Метод установки решения
    public function DoInstall(){

        global $APPLICATION;

        if(CheckVersion(ModuleManager::getVersion("main"), "14.00.00")){

            $this->InstallFiles();
            $this->InstallDB();

            ModuleManager::registerModule($this->MODULE_ID);

            $this->InstallEvents();
        }else{

            $APPLICATION->ThrowException(
                Loc::getMessage("UM_SITE_CONFIG_INSTALL_ERROR_VERSION")
            );
        }

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage("UM_SITE_CONFIG_INSTALL_TITLE")." \"".Loc::getMessage("UM_SITE_CONFIG_NAME")."\"",
            __DIR__."/step.php"
        );

        return false;
    }

    // Метод копирование файлов
    public function InstallFiles(){

        CopyDirFiles(
            __DIR__."/assets/scripts",
            Application::getDocumentRoot()."/bitrix/js/".$this->MODULE_ID."/",
            true,
            true
        );

        CopyDirFiles(
            __DIR__."/assets/styles",
            Application::getDocumentRoot()."/bitrix/css/".$this->MODULE_ID."/",
            true,
            true
        );

        return false;
    }

    // Метод установки таблиц
    public function InstallDB(){
        return false;
    }

    // Метод регистрации событий
    public function InstallEvents(){

       /* EventManager::getInstance()->registerEventHandler(
            "main",
            "OnBuildGlobalMenu",
            $this->MODULE_ID,
            "SiteConfig"
        );*/
        return false;
    }

    // Метод деинсталяции
    public function DoUninstall(){

        global $APPLICATION;

        $this->UnInstallFiles();
        $this->UnInstallDB();
        $this->UnInstallEvents();

        ModuleManager::unRegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage("UM_SITE_CONFIG_UNINSTALL_TITLE")." \"".Loc::getMessage("UM_SITE_CONFIG_NAME")."\"",
            __DIR__."/unstep.php"
        );

        return false;
    }

    // Метод удаляющий файлы нашего модуля
    public function UnInstallFiles(){

        Directory::deleteDirectory(
            Application::getDocumentRoot()."/bitrix/js/".$this->MODULE_ID
        );

        Directory::deleteDirectory(
            Application::getDocumentRoot()."/bitrix/css/".$this->MODULE_ID
        );

        return false;
    }

    // Удаляем настройки модуля из системы
    public function UnInstallDB(){

        Option::delete($this->MODULE_ID);

        return false;
    }

    // Метод удаления эвентов
    public function UnInstallEvents(){

     /*   EventManager::getInstance()->unRegisterEventHandler(
            "main",
            "OnBuildGlobalMenu",
            $this->MODULE_ID,
            "SiteConfig"
        );*/

        return false;
    }
}