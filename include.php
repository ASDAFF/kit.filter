<?
/**
 * Copyright (c) 6/3/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('iblock'))
{
	$APPLICATION->ThrowException(Loc::getMessage('KOMBOX_MODULE_FILTER_ERROR_IBLOCK_NOT_INSTALLED'));
	return false;
}

CModule::AddAutoloadClasses(
	"kombox.filter",
	array(
		"Kombox\\Filter\\PropertySettingsTable" => "lib/property_settings.php",
		"\\Kombox\\Filter\\PropertySettingsTable" => "lib/property_settings.php"
	)
);

class CKomboxFilter
{
	private static $bSefMode = false;
	private static $sDocPath2 = '';
	private static $content = '';
	private static $arFields = array();
	
	public static function IsSefMode($url = '') {
        if(self::$bSefMode)
		{
			return self::$bSefMode;
		}
		elseif(strlen($url))
		{
			$strPath = COption::GetOptionString('kombox.filter', "sef_paths");
			$arPath = explode(';', $strPath);
			
			if(!count($arPath))
				$arPath = array($strPath);
			
			foreach($arPath as $path)
			{
				$path = trim($path);
				if(strlen($path))
				{
					if(strpos($url, $path) === 0)
					{
						return true;
					}
				}
			}
		}
		return false;
    }
	
	public static function GetCurPage($get_index_page=null)
	{
		if (null === $get_index_page)
		{
			if (defined('BX_DISABLE_INDEX_PAGE'))
				$get_index_page = !BX_DISABLE_INDEX_PAGE;
			else
				$get_index_page = true;
		}

		$str = self::$sDocPath2;

		if (!$get_index_page)
		{
			if (($i = strpos($str, '/index.php')) !== false)
				$str = substr($str, 0, $i).'/';
		}

		return $str;
	}
	
	public static function GetCurPageParam($strParam="", $arParamKill=array(), $get_index_page=null)
    {
        $sUrlPath = self::GetCurPage($get_index_page);
        $strNavQueryString = DeleteParam($arParamKill);
        if($strNavQueryString <> "" && $strParam <> "")
            $strNavQueryString = "&".$strNavQueryString;
        if($strNavQueryString == "" && $strParam == "")
            return $sUrlPath;
        else
            return $sUrlPath."?".$strParam.$strNavQueryString;
    }
	
	public static function OnBeforeProlog() {
        if(!defined("ADMIN_SECTION") || ADMIN_SECTION !== true)
		{	
			$strPath = COption::GetOptionString('kombox.filter', "sef_paths");
			$arPath = explode(';', $strPath);
			$isBitronic = CModule::IncludeModule('yenisite.bitronic') || CModule::IncludeModule('yenisite.bitronicpro') || CModule::IncludeModule('yenisite.bitroniclite');
			
			if(!count($arPath))
				$arPath = array($strPath);
			
			global $APPLICATION;
			$requestURL = $APPLICATION->GetCurPage(true);
			$arUrlParts = explode('/', $requestURL);
			
			foreach($arPath as $path)
			{
				$path = trim($path);
				if(strlen($path))
				{
					if(strpos($requestURL, $path) === 0)
					{
						self::$bSefMode = true;
						break;
					}
				}
			}
			
			if((in_array('filter', $arUrlParts) || (strlen($_GET['filter']) && $isBitronic)) && self::$bSefMode)
			{
				self::$sDocPath2 = $requestURL;
				
				$requestURL = '/';
				foreach($arUrlParts as $part)
				{
					if(!strlen(trim($part)))continue;
					if($part == 'filter')break;
					if($part == 'index.php')break;
					$requestURL .= $part.'/';
				}
				
				$param = $APPLICATION->GetCurParam();
				
				if(strlen($_GET['filter']) && $isBitronic){
					$requestURL .= 'filter'.$_GET['filter'];
					$param = DeleteParam(array('filter'));
					
					if(strlen($param))
						$requestURL .= '?'.$param;
					
					LocalRedirect($requestURL);
				}
				
				$requestURL .= 'index.php';

				if($isBitronic)
				{
					$sectionCode = $_GET['SECTION_CODE'];
					
					if(strlen($sectionCode))
					{
						$arUrlBitronicParts = explode('/', $sectionCode);
						if(in_array('filter', $arUrlBitronicParts))
						{
							$sectionCode = '';
							foreach($arUrlBitronicParts as $path)
							{
								if($path == 'filter')break;
								
								if(strlen($sectionCode))
									$sectionCode .= '/';
								
								$sectionCode .= $path;
							}
							
							$_GET['SECTION_CODE'] = $sectionCode;
							$_REQUEST['SECTION_CODE'] = $sectionCode;
							
							$param = DeleteParam(array('SECTION_CODE'));
						}
					}
				}

				if(strlen($param))
				{
					$requestURL .= '?'.$param;
				}
				
				$APPLICATION->SetCurPage($requestURL, $param);
				
				//ugly code: bag iblock 15.5.0
				$context = \Bitrix\Main\Application::getInstance()->getContext();
				$server = $context->getServer();
				
				$arServer = $_SERVER;
				$arServer['REQUEST_URI'] = $requestURL;
				$server->set($arServer);
				
				$request = new \Bitrix\Main\HttpRequest(
					$server,
					$_GET,
					$_POST,
					$_FILES,
					$_COOKIE
				);

				$response = $context->getResponse();

				$context->initialize($request, $response, $server, array('env' => $_ENV));
			}
			
			//добавляем в $_GET параметры из url с одинаковыми названиями и разными значениями
			$check = array();
			$params = explode('&', $_SERVER['QUERY_STRING']);
			
			foreach ($params as $param) {
				$name_value = explode('=', $param);
				$pos = strpos($name_value[0], '[');
				if($pos !== false){
					$name_value[0] = substr($name_value[0], 0, $pos); 
				}
				
				if(strlen($name_value[0])){
					$get = &$check[$name_value[0]];
					
					if(!is_array($get))
						$get = array();
					
					if(is_array($get))
						$get[$name_value[1]] = $name_value[1];
					
					unset($get);
				}
			}
			
			foreach($check as $key => $arParams)
			{
				if(count($arParams)>1)
					$_GET[$key] = array_values($arParams);
			}
		}
		
		self::addDialogFilterParams();
    }
	
	private function addDialogFilterParams()
	{
		if($GLOBALS['APPLICATION']->GetCurPage(true)=='/bitrix/admin/iblock_edit_property.php')
		{
			$ID = intval($_REQUEST['propedit']);
			
			if(!$ID && intval($_REQUEST['ID']))
				$ID = intval($_REQUEST['ID']);
			
			if(!intval($IBLOCK_ID))
				$IBLOCK_ID = $_REQUEST['IBLOCK_ID'];
				
			if(!intval($IBLOCK_ID))
				$IBLOCK_ID = $_REQUEST['PARAMS']['IBLOCK_ID'];
				
			$iblocks = COption::GetOptionString('kombox.filter', "iblocks");
	
			if(strlen($iblocks))
				$iblocks = unserialize($iblocks);
				
			if(!is_array($iblocks))
				$iblocks = array();
				
			if(!in_array($IBLOCK_ID, $iblocks))
				return;

			if(isset($_POST['PROP']['PROPERTY_TYPE']))
				$PROPERTY_TYPE = $_POST['PROP']['PROPERTY_TYPE'];
			elseif(isset($_POST['PROPERTY_PROPERTY_TYPE']))
				$PROPERTY_TYPE = $_POST['PROPERTY_PROPERTY_TYPE'];
			elseif(!isset($arProperty['PROPERTY_TYPE'])){
				if(CModule::IncludeModule('iblock'))
				{
					$rsProperty = CIBlockProperty::GetByID($ID);
					$arBaseProperty = $rsProperty->Fetch();
					$PROPERTY_TYPE = $arBaseProperty['PROPERTY_TYPE'];
				}
			}
			
			if(!strlen($PROPERTY_TYPE)){
				$PROPERTY_TYPE = 'S';
			}
			
			if(!in_array($PROPERTY_TYPE, array('S', 'N', 'L', 'G', 'E', 'S:UserID', 'G:SectionAuto', 'E:SKU', 'E:EList', 'S:ElementXmlID', 'E:EAutocomplete', 'S:directory', 'S:TopicID', 'N:Sequence')))
				return;
			
			CJSCore::RegisterExt('kombox_edit_property', array(
				'js' => '/bitrix/js/kombox/filter/edit_property.js', 
				'rel' => array('jquery'),
			));
			CJSCore::Init(array('kombox_edit_property'));
			
			$arSettings = $_REQUEST;
			$arSettings['sessid'] = bitrix_sessid();
			$arSettings['ID'] = $ID;
			$arSettings['IBLOCK_ID'] = $IBLOCK_ID;
			$arSettings['PROPERTY_TYPE'] = $PROPERTY_TYPE;

			ob_start();?>
			<script type="text/javascript">
				$(function(){
					loadKomboxEditPropertySettings(<?echo CUtil::PhpToJsObject($arSettings);?>);
				});
			</script>
			<?
			$script = ob_get_clean();
			self::$content = $script;
		}
	}
	
	function OnEndBufferContent(&$content)
	{
		if($GLOBALS['APPLICATION']->GetCurPage(true)=='/bitrix/admin/iblock_edit_property.php' && strlen(self::$content))
		{
			if(strpos($content, '</body>') !== false)
			{
				$content = str_replace(
					'</body>', 
					self::$content.'</body>', 
					$content
				);
			}
			else
			{
				$content .= self::$content;
			}
		}
	}
	
	private static function updatePropertySettings($arFields)
	{
		if(intval($arFields['ID']))
		{
			if(!is_array($arFields['USER_TYPE_SETTINGS']))
			{
				if(is_array($_POST['PROPERTY_USER_TYPE_SETTINGS']))
				{
					$arFields['USER_TYPE_SETTINGS'] = $_POST['PROPERTY_USER_TYPE_SETTINGS'];
				}
				elseif(strlen($_POST['IB_PROPERTY_'.intval($arFields['ID']).'_PROPINFO']))
				{
					$arProperty = unserialize(base64_decode($_POST['IB_PROPERTY_'.intval($arFields['ID']).'_PROPINFO']));
					$arFields['USER_TYPE_SETTINGS'] = $arProperty['USER_TYPE_SETTINGS'];
				}
				elseif(isset(self::$arFields['USER_TYPE_SETTINGS']))
				{
					$arFields['USER_TYPE_SETTINGS'] = self::$arFields['USER_TYPE_SETTINGS'];
				}
			}

			if(is_array($arFields['USER_TYPE_SETTINGS']) && strlen($arFields['USER_TYPE_SETTINGS']['KOMBOX_VIEW']))
			{
				$arSettings = array(
					'PROPERTY_ID' => $arFields['ID'],
					'HINT_TYPE' => in_array($arFields['USER_TYPE_SETTINGS']['KOMBOX_HINT_TYPE'], array('text', 'text')) ? $arFields['USER_TYPE_SETTINGS']['KOMBOX_HINT_TYPE'] : 'html',
					'HINT' => $arFields['USER_TYPE_SETTINGS']['KOMBOX_HINT'],
					'VIEW' => $arFields['USER_TYPE_SETTINGS']['KOMBOX_VIEW'],
					'SLIDER_STEP' => floatval($arFields['USER_TYPE_SETTINGS']['KOMBOX_SLIDER_STEP']),
					'SLIDER_UNITS' => trim($arFields['USER_TYPE_SETTINGS']['KOMBOX_SLIDER_UNITS']),
					'LIST_SIZE' => intval($arFields['USER_TYPE_SETTINGS']['KOMBOX_LIST_SIZE']) > 0 ? intval($arFields['USER_TYPE_SETTINGS']['KOMBOX_LIST_SIZE']) : 1,
					'LIST_MULTI' => isset($arFields['USER_TYPE_SETTINGS']['KOMBOX_LIST_MULTI']) ? 'Y' : 'N',
					'VALUES_CNT' => intval($arFields['USER_TYPE_SETTINGS']['KOMBOX_VALUES_CNT']),
					'SORT' => strlen($arFields['USER_TYPE_SETTINGS']['KOMBOX_SORT']) ? $arFields['USER_TYPE_SETTINGS']['KOMBOX_SORT'] : 'NAME',
					'SORT_ORDER' => strlen($arFields['USER_TYPE_SETTINGS']['KOMBOX_SORT_ORDER']) ? $arFields['USER_TYPE_SETTINGS']['KOMBOX_SORT_ORDER'] : 'ASC'
				);
				
				$rsPropertySettings = Kombox\Filter\PropertySettingsTable::getList(
					array(
						'select' => array('ID'),
						'filter' => array('PROPERTY_ID' => $arFields['ID'])
					)
				);
				
				if($arPropertySettings = $rsPropertySettings->Fetch())
				{
					Kombox\Filter\PropertySettingsTable::update($arPropertySettings['ID'], $arSettings);
				}
				else
				{
					Kombox\Filter\PropertySettingsTable::add($arSettings);
				}
			}
			
			self::$arFields = array();
		}
	}
	
	public static function OnAfterIBlockPropertyUpdate(&$arFields)
    {
		self::updatePropertySettings($arFields);
    }
	
	public static function OnBeforeIBlockPropertyAdd(&$arFields)
    {
		self::$arFields = $arFields;
    }
	
	public static function OnAfterIBlockPropertyAdd(&$arFields)
    {
		self::updatePropertySettings($arFields);
    }
	
	public static function OnIBlockPropertyDelete($ID)
    {
		if(intval($ID))
		{
			$rsPropertySettings = Kombox\Filter\PropertySettingsTable::getList(
				array(
					'select' =>array('ID'),
					'filter'=>array('PROPERTY_ID' => $ID),
				)
			);
			
			if($arPropertySettings = $rsPropertySettings->Fetch())
			{
				Kombox\Filter\PropertySettingsTable::delete($arPropertySettings['ID']);
			}
		}
    }
	
	public static function ConvertCurrency($valSum, $curFrom, $curTo, $valDate = "") 
    { 
        static $arConvertParams = array();
		$key = $curFrom.'-'.$curTo.'-'.$valDate;
		if(!isset($arConvertParams[$key]))
		{
			global $DB; 
			if (strlen($valDate)<=0) 
				$valDate = date("Y-m-d"); 
			list($dpYear, $dpMonth, $dpDay) = split("-", $valDate, 3); 
			$dpDay += 1; 
			$valDate = date("Y-m-d", mktime(0, 0, 0, $dpMonth, $dpDay, $dpYear)); 

			$curFromRate = 0; 
			$curFromRateCnt = 0; 
			$strSql =  
				"SELECT C.AMOUNT, C.AMOUNT_CNT, CR.RATE, CR.RATE_CNT ". 
				"FROM b_catalog_currency C ". 
				"    LEFT JOIN b_catalog_currency_rate CR ". 
				"        ON (C.CURRENCY = CR.CURRENCY AND CR.DATE_RATE < '".$valDate."') ". 
				"WHERE C.CURRENCY = '".$DB->ForSql($curFrom)."' ". 
				"ORDER BY DATE_RATE DESC"; 
			$db_res = $DB->Query($strSql); 
			if ($res = $db_res->Fetch()) 
			{ 
				$curFromRate = DoubleVal($res["RATE"]); 
				$curFromRateCnt = IntVal($res["RATE_CNT"]); 
				if ($curFromRate<=0) 
				{ 
					$curFromRate = DoubleVal($res["AMOUNT"]); 
					$curFromRateCnt = IntVal($res["AMOUNT_CNT"]); 
				} 
			} 

			$curToRate = 0; 
			$curToRateCnt = 0; 
			$strSql =  
				"SELECT C.AMOUNT, C.AMOUNT_CNT, CR.RATE, CR.RATE_CNT ". 
				"FROM b_catalog_currency C ". 
				"    LEFT JOIN b_catalog_currency_rate CR ". 
				"        ON (C.CURRENCY = CR.CURRENCY AND CR.DATE_RATE < '".$valDate."') ". 
				"WHERE C.CURRENCY = '".$DB->ForSql($curTo)."' ". 
				"ORDER BY DATE_RATE DESC"; 
			$db_res = $DB->Query($strSql); 
			if ($res = $db_res->Fetch()) 
			{ 
				$curToRate = DoubleVal($res["RATE"]); 
				$curToRateCnt = DoubleVal($res["RATE_CNT"]); 
				if ($curToRate<=0) 
				{ 
					$curToRate = DoubleVal($res["AMOUNT"]); 
					$curToRateCnt = IntVal($res["AMOUNT_CNT"]); 
				} 
			}

			$arConvertParams[$key] = array(
				'curFromRate' 		=> $curFromRate,
				'curToRateCnt' 		=> $curToRateCnt,
				'curToRate' 		=> $curToRate,
				'curFromRateCnt' 	=> $curFromRateCnt
			);		
		}
		
        return DoubleVal(DoubleVal($valSum)*$arConvertParams[$key]['curFromRate']*$arConvertParams[$key]['curToRateCnt']/$arConvertParams[$key]['curToRate']/$arConvertParams[$key]['curFromRateCnt']); 
    }
	
	public static function urlAddParams($url, $add_params, $options = array())
    {
        if(count($add_params))
        {
            $params = array();
            foreach($add_params as $name => $value)
            {
                if(is_array($value))
				{
					foreach($value as $v)
					{
						if($options["skip_empty"] && !strlen($v))
							continue;
						if($options["encode"])
							$params[] = urlencode($name).'='.urlencode($v);
						else
							$params[] = $name.'='.$v;
					}
				}
				else
				{
					if($options["skip_empty"] && !strlen($value))
						continue;
					if($options["encode"])
						$params[] = urlencode($name).'='.urlencode($value);
					else
						$params[] = $name.'='.$value;
				}
            }

            if(count($params))
            {
                $p1 = strpos($url, "?");
                if($p1 === false)
                    $ch = "?";
                else
                    $ch = "&";

                $p2 = strpos($url, "#");
                if($p2===false)
                {
                    $url = $url.$ch.implode("&", $params);
                }
                else
                {
                    $url = substr($url, 0, $p2).$ch.implode("&", $params).substr($url, $p2);
                }
            }
        }
        return $url;
    }
	
	public function getCurrencyFullName($currencyId)
	{
		if(CModule::IncludeModule('currency'))
		{
			$currencyInfo = CCurrencyLang::GetById($currencyId, LANGUAGE_ID);
			if ($currencyInfo['FORMAT_STRING'] != '')
				return trim(str_replace('#', '', $currencyInfo['FORMAT_STRING']));
		}
		return '';
	}
}

class CKomboxFilterIBlock extends CIBlockElement
{
	public static function GetPropertyValues($IBLOCK_ID, $arElementFilter, $arProperties = array(), $extMode = false)
    {
        global $DB;
        $IBLOCK_ID = intval($IBLOCK_ID);
        $VERSION = CIBlockElement::GetIBVersion($IBLOCK_ID);

        $arElementFilter["IBLOCK_ID"] = $IBLOCK_ID;

        $element = new CIBlockElement;
        $element->strField = "ID";
        $element->GetList(array(), $arElementFilter, false, false, array("ID"));

		$arPropertyIDs = array();
		foreach($arProperties as $arProperty)
		{
			if(intval($arProperty['PROPERTY_ID']))
				$arPropertyIDs[$arProperty['PROPERTY_ID']] = $arProperty['PROPERTY_ID'];
		}

        if ($VERSION == 2)
            $strSql = "
                SELECT
                    BEP.*
                FROM
                    ".$element->sFrom."
                    INNER JOIN b_iblock_element_prop_s".$IBLOCK_ID." BEP ON BEP.IBLOCK_ELEMENT_ID = BE.ID
                WHERE 1=1 ".$element->sWhere."
                ORDER BY
                    BEP.IBLOCK_ELEMENT_ID
            ";
        else
            $strSql = "
                SELECT
                    BE.ID IBLOCK_ELEMENT_ID
                    ,BEP.IBLOCK_PROPERTY_ID
                    ,BEP.VALUE
                    ,BEP.VALUE_NUM
                    ".($extMode ?
                        ",BEP.ID PROPERTY_VALUE_ID
                        ,BEP.DESCRIPTION
                        " :
                        ""
                    )."
                FROM
                    ".$element->sFrom."
                    LEFT JOIN b_iblock_element_property BEP ON BEP.IBLOCK_ELEMENT_ID = BE.ID
                WHERE 1=1 ".$element->sWhere.(count($arPropertyIDs) ? " AND BEP.IBLOCK_PROPERTY_ID IN (".implode(",", $arPropertyIDs).") ": "")."
                ORDER BY
                    BEP.IBLOCK_ELEMENT_ID
            ";

        $rs = new CIBlockPropertyResult($DB->Query($strSql));
        $rs->setIBlock($IBLOCK_ID);
        $rs->setMode($extMode);

        return $rs;
    }
} 
?>