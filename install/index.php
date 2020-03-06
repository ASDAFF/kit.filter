<?

/**
 * Copyright (c) 6/3/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

global $MESS;
$PathInstall = str_replace("\\", "/", __FILE__);
$PathInstall = substr($PathInstall, 0, strlen($PathInstall)-strlen("/index.php"));
IncludeModuleLangFile($PathInstall."/install.php");
include($PathInstall."/version.php");

if (class_exists("collected_filter")) return;

class collected_filter extends CModule
{
	var $MODULE_ID = "collected.filter";
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

		$this->PARTNER_NAME = GetMessage("COLLECTED_MODULE_FILTER_INSTALL_NAME");
		$this->PARTNER_URI = "https://asdaff.github.io/";

		$this->MODULE_NAME = GetMessage("COLLECTED_MODULE_FILTER_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("COLLECTED_MODULE_FILTER_DESCRIPTION");

	}
	
	function InstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;
		// Database tables creation
		if (!$DB->Query("SELECT 'x' FROM b_collected_filter_prop_settings WHERE 1=0", true))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/collected.filter/install/db/".strtolower($DB->type)."/install.sql");
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
		CModule::IncludeModule("collected.filter");
		
		if ($DB->Query("SELECT 'x' FROM b_collected_filter_prop_settings WHERE 1=0", true))
		{
			$result = \Collected\Filter\PropertySettingsTable::getList();
			while ($item = $result->fetch())
			{
				\Collected\Filter\PropertySettingsTable::delete($item['ID']);
			}

			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/collected.filter/install/db/".strtolower($DB->type)."/uninstall.sql");
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
		RegisterModule("collected.filter");

		RegisterModuleDependences("main", "OnBeforeProlog", "collected.filter", "CCollectedFilter", "OnBeforeProlog");
		RegisterModuleDependences("main", "OnEndBufferContent", "collected.filter", "CCollectedFilter", "OnEndBufferContent");
		RegisterModuleDependences("iblock", "OnAfterIBlockPropertyUpdate", "collected.filter", "CCollectedFilter", "OnAfterIBlockPropertyUpdate");
		RegisterModuleDependences("iblock", "OnBeforeIBlockPropertyAdd", "collected.filter", "CCollectedFilter", "OnBeforeIBlockPropertyAdd");
		RegisterModuleDependences("iblock", "OnAfterIBlockPropertyAdd", "collected.filter", "CCollectedFilter", "OnAfterIBlockPropertyAdd");
		RegisterModuleDependences("iblock", "OnIBlockPropertyDelete", "collected.filter", "CCollectedFilter", "OnIBlockPropertyDelete");
		//RegisterModuleDependences("main", "OnBuildGlobalMenu", "collected.filter", "CCollectedFilter", "OnBuildGlobalMenu");
		//RegisterModuleDependences("main", "OnEpilog", "collected.filter", "CCollectedFilter", "OnEpilog");
		
		return true;
	}
	
	function UnInstallEvents()
	{
		UnRegisterModuleDependences("main", "OnBeforeProlog", "collected.filter", "CCollectedFilter", "OnBeforeProlog");
		UnRegisterModuleDependences("main", "OnEndBufferContent", "collected.filter", "CCollectedFilter", "OnEndBufferContent");
		UnRegisterModuleDependences("iblock", "OnAfterIBlockPropertyUpdate", "collected.filter", "CCollectedFilter", "OnAfterIBlockPropertyUpdate");
		UnRegisterModuleDependences("iblock", "OnBeforeIBlockPropertyAdd", "collected.filter", "CCollectedFilter", "OnBeforeIBlockPropertyAdd");
		UnRegisterModuleDependences("iblock", "OnAfterIBlockPropertyAdd", "collected.filter", "CCollectedFilter", "OnAfterIBlockPropertyAdd");
		UnRegisterModuleDependences("iblock", "OnIBlockPropertyDelete", "collected.filter", "CCollectedFilter", "OnIBlockPropertyDelete");
		//UnRegisterModuleDependences("main", "OnBuildGlobalMenu", "collected.filter", "CCollectedFilter", "OnBuildGlobalMenu");	
		//UnRegisterModuleDependences("main", "OnEpilog", "collected.filter", "CCollectedFilter", "OnEpilog");
		
		UnRegisterModule("collected.filter");
		
		return true;
	}
	
	function InstallFiles($arParams = array())
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/collected.filter/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/collected.filter/install/components/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/collected.filter/install/js/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js/", true, true);
		return true;
	}
	
	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/collected.filter/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		DeleteDirFilesEx("/bitrix/components/collected/filter/");
		DeleteDirFilesEx("/bitrix/js/collected/filter/");
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
			$APPLICATION->IncludeAdminFile(GetMessage("COLLECTED_MODULE_FILTER_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/collected.filter/install/step1.php");
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
			
			$APPLICATION->IncludeAdminFile(GetMessage("COLLECTED_MODULE_FILTER_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/collected.filter/install/unstep1.php");
		}
	}
}
?>