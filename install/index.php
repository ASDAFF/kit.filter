<?

/**
 * Copyright (c) 6/3/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

global $MESS;
$PathInstall = str_replace("\\", "/", __FILE__);
$PathInstall = substr($PathInstall, 0, strlen($PathInstall)-strlen("/index.php"));
IncludeModuleLangFile($PathInstall."/install.php");
include($PathInstall."/version.php");

if (class_exists("kit_filter")) return;

class kit_filter extends CModule
{
	var $MODULE_ID = "kit.filter";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $PARTNER_NAME;
	var $PARTNER_URI;
	public $MODULE_GROUP_RIGHTS = 'N';

	function __construct()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}

		$this->PARTNER_NAME = GetMessage("KIT_MODULE_FILTER_INSTALL_NAME");
		$this->PARTNER_URI = "https://asdaff.github.io/";

		$this->MODULE_NAME = GetMessage("KIT_MODULE_FILTER_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("KIT_MODULE_FILTER_DESCRIPTION");

	}
	
	function InstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;
		// Database tables creation
		if (!$DB->Query("SELECT 'x' FROM b_kit_filter_prop_settings WHERE 1=0", true))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kit.filter/install/db/".strtolower($DB->type)."/install.sql");
		}
		if ($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}
		
		return true;
	}
	
	function UnInstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		// remove user data
		CModule::IncludeModule("kit.filter");
		
		if ($DB->Query("SELECT 'x' FROM b_kit_filter_prop_settings WHERE 1=0", true))
		{
			$result = \Kit\Filter\PropertySettingsTable::getList();
			while ($item = $result->fetch())
			{
				\Kit\Filter\PropertySettingsTable::delete($item['ID']);
			}

			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kit.filter/install/db/".strtolower($DB->type)."/uninstall.sql");
		}
		
		if ($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}
		return true;
	}
	
	function InstallEvents()
	{
		RegisterModule("kit.filter");

		RegisterModuleDependences("main", "OnBeforeProlog", "kit.filter", "CKitFilter", "OnBeforeProlog");
		RegisterModuleDependences("main", "OnEndBufferContent", "kit.filter", "CKitFilter", "OnEndBufferContent");
		RegisterModuleDependences("iblock", "OnAfterIBlockPropertyUpdate", "kit.filter", "CKitFilter", "OnAfterIBlockPropertyUpdate");
		RegisterModuleDependences("iblock", "OnBeforeIBlockPropertyAdd", "kit.filter", "CKitFilter", "OnBeforeIBlockPropertyAdd");
		RegisterModuleDependences("iblock", "OnAfterIBlockPropertyAdd", "kit.filter", "CKitFilter", "OnAfterIBlockPropertyAdd");
		RegisterModuleDependences("iblock", "OnIBlockPropertyDelete", "kit.filter", "CKitFilter", "OnIBlockPropertyDelete");
		//RegisterModuleDependences("main", "OnBuildGlobalMenu", "kit.filter", "CKitFilter", "OnBuildGlobalMenu");
		//RegisterModuleDependences("main", "OnEpilog", "kit.filter", "CKitFilter", "OnEpilog");
		
		return true;
	}
	
	function UnInstallEvents()
	{
		UnRegisterModuleDependences("main", "OnBeforeProlog", "kit.filter", "CKitFilter", "OnBeforeProlog");
		UnRegisterModuleDependences("main", "OnEndBufferContent", "kit.filter", "CKitFilter", "OnEndBufferContent");
		UnRegisterModuleDependences("iblock", "OnAfterIBlockPropertyUpdate", "kit.filter", "CKitFilter", "OnAfterIBlockPropertyUpdate");
		UnRegisterModuleDependences("iblock", "OnBeforeIBlockPropertyAdd", "kit.filter", "CKitFilter", "OnBeforeIBlockPropertyAdd");
		UnRegisterModuleDependences("iblock", "OnAfterIBlockPropertyAdd", "kit.filter", "CKitFilter", "OnAfterIBlockPropertyAdd");
		UnRegisterModuleDependences("iblock", "OnIBlockPropertyDelete", "kit.filter", "CKitFilter", "OnIBlockPropertyDelete");
		//UnRegisterModuleDependences("main", "OnBuildGlobalMenu", "kit.filter", "CKitFilter", "OnBuildGlobalMenu");
		//UnRegisterModuleDependences("main", "OnEpilog", "kit.filter", "CKitFilter", "OnEpilog");
		
		UnRegisterModule("kit.filter");
		
		return true;
	}
	
	function InstallFiles($arParams = array())
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kit.filter/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kit.filter/install/components/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kit.filter/install/js/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js/", true, true);
		return true;
	}
	
	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kit.filter/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		DeleteDirFilesEx("/bitrix/components/kit/filter/");
		DeleteDirFilesEx("/bitrix/js/kit/filter/");
		return true;
	}

	function DoInstall()
	{
		global $APPLICATION;

		if ($APPLICATION->GetGroupRight("main") >= "W")
		{
			if ($this->InstallDB())
			{
				$this->InstallEvents();
				$this->InstallFiles();
			}
			$GLOBALS["errors"] = $this->errors;
			$APPLICATION->IncludeAdminFile(GetMessage("KIT_MODULE_FILTER_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kit.filter/install/step1.php");
		}
	}

	function DoUninstall()
	{
		global $APPLICATION;
		
		if ($APPLICATION->GetGroupRight("main") >= "W")
		{
			if($this->UnInstallDB())
			{
				$this->UnInstallEvents();
				$this->UnInstallFiles();
			}
			
			$GLOBALS["errors"] = $this->errors;
			
			$APPLICATION->IncludeAdminFile(GetMessage("KIT_MODULE_FILTER_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kit.filter/install/unstep1.php");
		}
	}
}
?>