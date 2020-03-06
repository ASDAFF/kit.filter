<?
global $MESS;
$PathInstall = str_replace("\\", "/", __FILE__);
$PathInstall = substr($PathInstall, 0, strlen($PathInstall)-strlen("/index.php"));
IncludeModuleLangFile($PathInstall."/install.php");
include($PathInstall."/version.php");

if (class_exists("kombox_filter")) return;

class kombox_filter extends CModule
{
	var $MODULE_ID = "kombox.filter";
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

		$this->PARTNER_NAME = GetMessage("KOMBOX_MODULE_FILTER_INSTALL_NAME");
		$this->PARTNER_URI = "http://filter.kombox.ru/";

		$this->MODULE_NAME = GetMessage("KOMBOX_MODULE_FILTER_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("KOMBOX_MODULE_FILTER_DESCRIPTION");

	}
	
	function InstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;
		// Database tables creation
		if (!$DB->Query("SELECT 'x' FROM b_kombox_filter_prop_settings WHERE 1=0", true))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kombox.filter/install/db/".strtolower($DB->type)."/install.sql");
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
		CModule::IncludeModule("kombox.filter");
		
		if ($DB->Query("SELECT 'x' FROM b_kombox_filter_prop_settings WHERE 1=0", true))
		{
			$result = \Kombox\Filter\PropertySettingsTable::getList();
			while ($item = $result->fetch())
			{
				\Kombox\Filter\PropertySettingsTable::delete($item['ID']);
			}

			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kombox.filter/install/db/".strtolower($DB->type)."/uninstall.sql");
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
		RegisterModule("kombox.filter");

		RegisterModuleDependences("main", "OnBeforeProlog", "kombox.filter", "CKomboxFilter", "OnBeforeProlog");
		RegisterModuleDependences("main", "OnEndBufferContent", "kombox.filter", "CKomboxFilter", "OnEndBufferContent");
		RegisterModuleDependences("iblock", "OnAfterIBlockPropertyUpdate", "kombox.filter", "CKomboxFilter", "OnAfterIBlockPropertyUpdate");
		RegisterModuleDependences("iblock", "OnBeforeIBlockPropertyAdd", "kombox.filter", "CKomboxFilter", "OnBeforeIBlockPropertyAdd");
		RegisterModuleDependences("iblock", "OnAfterIBlockPropertyAdd", "kombox.filter", "CKomboxFilter", "OnAfterIBlockPropertyAdd");
		RegisterModuleDependences("iblock", "OnIBlockPropertyDelete", "kombox.filter", "CKomboxFilter", "OnIBlockPropertyDelete");
		//RegisterModuleDependences("main", "OnBuildGlobalMenu", "kombox.filter", "CKomboxFilter", "OnBuildGlobalMenu");
		//RegisterModuleDependences("main", "OnEpilog", "kombox.filter", "CKomboxFilter", "OnEpilog");
		
		return true;
	}
	
	function UnInstallEvents()
	{
		UnRegisterModuleDependences("main", "OnBeforeProlog", "kombox.filter", "CKomboxFilter", "OnBeforeProlog");
		UnRegisterModuleDependences("main", "OnEndBufferContent", "kombox.filter", "CKomboxFilter", "OnEndBufferContent");
		UnRegisterModuleDependences("iblock", "OnAfterIBlockPropertyUpdate", "kombox.filter", "CKomboxFilter", "OnAfterIBlockPropertyUpdate");
		UnRegisterModuleDependences("iblock", "OnBeforeIBlockPropertyAdd", "kombox.filter", "CKomboxFilter", "OnBeforeIBlockPropertyAdd");
		UnRegisterModuleDependences("iblock", "OnAfterIBlockPropertyAdd", "kombox.filter", "CKomboxFilter", "OnAfterIBlockPropertyAdd");
		UnRegisterModuleDependences("iblock", "OnIBlockPropertyDelete", "kombox.filter", "CKomboxFilter", "OnIBlockPropertyDelete");
		//UnRegisterModuleDependences("main", "OnBuildGlobalMenu", "kombox.filter", "CKomboxFilter", "OnBuildGlobalMenu");	
		//UnRegisterModuleDependences("main", "OnEpilog", "kombox.filter", "CKomboxFilter", "OnEpilog");
		
		UnRegisterModule("kombox.filter");
		
		return true;
	}
	
	function InstallFiles($arParams = array())
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kombox.filter/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kombox.filter/install/components/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kombox.filter/install/js/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js/", true, true);
		return true;
	}
	
	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kombox.filter/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		DeleteDirFilesEx("/bitrix/components/kombox/filter/");
		DeleteDirFilesEx("/bitrix/js/kombox/filter/");
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
			$APPLICATION->IncludeAdminFile(GetMessage("KOMBOX_MODULE_FILTER_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kombox.filter/install/step1.php");
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
			
			$APPLICATION->IncludeAdminFile(GetMessage("KOMBOX_MODULE_FILTER_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kombox.filter/install/unstep1.php");
		}
	}
}
?>