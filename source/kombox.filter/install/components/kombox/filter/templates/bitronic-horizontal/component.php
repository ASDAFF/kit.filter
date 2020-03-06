<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
global $USER;
global $APPLICATION;

if(!CModule::IncludeModule("iblock"))
{
	ShowError(GetMessage("KOMBOX_CMP_FILTER_MODULE_NOT_INSTALLED"));
	return;
}

$FILTER_NAME = (string)$arParams["FILTER_NAME"];

global ${$FILTER_NAME};
if(!is_array(${$FILTER_NAME}))
    ${$FILTER_NAME} = array();

$arrFilter = &${$FILTER_NAME};

if($this->StartResultCache(false, array($arrFilter,($arParams["CACHE_GROUPS"]? $USER->GetGroups(): false))))
{
	$arResult["PRICES"] = CIBlockPriceTools::GetCatalogPrices($arParams["IBLOCK_ID"], $arParams["PRICE_CODE"]);
	$arResult["ITEMS"] = $this->getResultItems();
	$propertyEmptyValuesCombination = array();
	foreach($arResult["ITEMS"] as $PID => $arItem)
		$propertyEmptyValuesCombination[$arItem["ID"]] = array();
		
	if ($arParams['CONVERT_CURRENCY'])
	{
		if (!CModule::IncludeModule('currency'))
		{
			$arParams['CONVERT_CURRENCY'] = false;
			$arParams['CURRENCY_ID'] = '';
		}
		else
		{
			$arCurrencyInfo = CCurrency::GetByID($arParams['CURRENCY_ID']);
			if (!empty($arCurrencyInfo) && is_array($arCurrencyInfo))
			{
				$arParams['CURRENCY_ID'] = $arCurrencyInfo['CURRENCY'];
			}
			else
			{
				$arParams['CONVERT_CURRENCY'] = false;
				$arParams['CURRENCY_ID'] = '';
			}
		}
	}
	
	$arResult["EXTENDED_MODE"] = in_array('STORES', $arParams['FIELDS']) || in_array('SECTIONS', $arParams['FIELDS']) || $arParams['CONVERT_CURRENCY'];
	
	if(!empty($arResult["ITEMS"]))
	{
		$arElementFilter = array(
			"IBLOCK_ID" => $this->IBLOCK_ID,
			"ACTIVE_DATE" => "Y",
			"ACTIVE" => "Y",
			"CHECK_PERMISSIONS" => "Y"
		);
		
		if(intval($this->SECTION_ID)){
			$arElementFilter['SUBSECTION'] = $this->SECTION_ID;
			$arElementFilter['SECTION_SCOPE'] = "IBLOCK";
		}
		
		if($arParams['HIDE_NOT_AVAILABLE'])
			$arElementFilter['CATALOG_AVAILABLE'] = 'Y';
		
		$res = CIBlockElement::GetPropertyValues($this->IBLOCK_ID, array_merge($arrFilter, $arElementFilter));
		
		$arResult['ELEMENTS'] = array();
		$arVariants = array();
		$arIDs = array();
		
		while($el = $res->Fetch())
		{
			$id = $el['IBLOCK_ELEMENT_ID'];
			$arIDs[$id] = $id;
			$result = $this->fillVariants($arVariants, $arResult["ITEMS"], $el, $id);
			$result['ID'] = $id;
			$arResult['ELEMENTS'][$id] = $result;
		}
		
		if(!empty($arIDs) && $this->SKU_IBLOCK_ID)
		{
			$arItemsSKU = $this->getResultSkuItems();
			
			if($arResult["SKU_PROPERTY_COUNT"] > 0)
			{
				$arResult['ELEMENTS_SKU'] = array();
				$arParentIDs = array();
				
				$arSkuFilter = array(
					"IBLOCK_ID" => $this->SKU_IBLOCK_ID,
					"ACTIVE_DATE" => "Y",
					"ACTIVE" => "Y",
					"CHECK_PERMISSIONS" => "Y",
					"=PROPERTY_".$this->SKU_PROPERTY_ID => $arIDs,
				);
				
				if($arParams['HIDE_NOT_AVAILABLE'])
					$arSkuFilter['CATALOG_AVAILABLE'] = 'Y';
				
				$res = CIBlockElement::GetPropertyValues($this->SKU_IBLOCK_ID, $arSkuFilter);
				while($el = $res->Fetch())
				{
					$id = $el[$this->SKU_PROPERTY_ID];	
					$result = $this->fillVariants($arVariants, $arItemsSKU, $el, $id);
					$result['ID'] = $el['IBLOCK_ELEMENT_ID'];
					$arIDs[$result['ID']] = $result['ID'];
					$arResult['ELEMENTS_SKU'][$id][$result['ID']] = $result;
					
					$arParentIDs[$result['ID']] = $id;
				}
				$arResult["ITEMS"] += $arItemsSKU;

				if(count($arResult["PRICES"]))
				{
					$arSelect = array("ID");
					foreach($arResult["PRICES"] as $value)
					{
						$arSelect[] = $value["SELECT"];
						$arSkuFilter["CATALOG_SHOP_QUANTITY_".$value["ID"]] = 1;
					}
					
					//available
					$bAvailable = false;
					if(in_array('AVAILABLE', $arParams['FIELDS'])){
						$arSelect[] = "CATALOG_QUANTITY";
						$bAvailable = true;
					}
					
					//quantity
					$bQuantity = false;
					if(in_array('QUANTITY', $arParams['FIELDS'])){
						$arSelect[] = "CATALOG_QUANTITY";
						$bQuantity = true;
					}
					
					$rsElements = CIBlockElement::GetList(array(), $arSkuFilter, false, false, $arSelect);
					while($arSku = $rsElements->Fetch())
					{
						$id = $arParentIDs[$arSku['ID']];
						
						foreach($arResult["PRICES"] as $NAME => $arPrice)
							if(isset($arResult["ITEMS"][$NAME])){
								$price = $this->fillItemPrices($arResult["ITEMS"][$NAME], $arSku);
								$arResult['ELEMENTS_SKU'][$id][$arSku['ID']][$NAME] = $price;
							}
						
						/*
						if($bAvailable)
						{
							$available = $arSku['CATALOG_AVAILABLE'];
							$arResult['ELEMENTS_SKU'][$id][$arSku['ID']]['AVAILABLE'] = $available;
							
							if(!isset($arVariants['AVAILABLE'][$available]))
								$arVariants['AVAILABLE'][$available] = array('ID' => array(), 'CNT' => 0);
							
							if(!$arVariants['AVAILABLE'][$available]['ID'][$id]){
								$arVariants['AVAILABLE'][$available]['ID'][$id] = true;
								$arVariants['AVAILABLE'][$available]['CNT']++;
							}
						}
						*/
						
						if($bQuantity)
						{
							$quantity = $arSku['CATALOG_QUANTITY'];
							$arResult['ELEMENTS_SKU'][$id][$arSku['ID']]['QUANTITY'] = $quantity;
							
							if(!isset($arVariants['QUANTITY'][$quantity]))
								$arVariants['QUANTITY'][$quantity] = array('ID' => array(), 'CNT' => 0);
							
							if(!$arVariants['QUANTITY'][$quantity]['ID'][$id]){
								$arVariants['QUANTITY'][$quantity]['ID'][$id] = true;
								$arVariants['QUANTITY'][$quantity]['CNT']++;
							}
						}
					}
				}
			}
		}
		
		if(count($arResult["PRICES"]) || in_array('SECTIONS', $arParams['FIELDS']) || in_array('AVAILABLE', $arParams['FIELDS']) || in_array('QUANTITY', $arParams['FIELDS']))
		{
			$arSelect = array("ID", "IBLOCK_ID");
			//prices
			foreach($arResult["PRICES"] as $value)
			{
				$arSelect[] = $value["SELECT"];
				$arElementFilter["CATALOG_SHOP_QUANTITY_".$value["ID"]] = 1;
			}
			
			//sections
			$arSections = array();
			if(in_array('SECTIONS', $arParams['FIELDS']))
			{
				$arSelect[] = "IBLOCK_SECTION_ID";
				
				$arSectionsFilter = array(
					"ACTIVE" => "Y", 
					"GLOBAL_ACTIVE" => "Y", 
					"IBLOCK_ID" => $this->IBLOCK_ID
				);
				
				$topDepth = $arParams["TOP_DEPTH_LEVEL"];
				
				if(intval($this->SECTION_ID)){
					$arSectionsFilter[">LEFT_MARGIN"] = $this->SECTION['LEFT_MARGIN'];
					$arSectionsFilter["<RIGHT_MARGIN"] = $this->SECTION['RIGHT_MARGIN'];
					$arSectionsFilter[">DEPTH_LEVEL"] = $this->SECTION['DEPTH_LEVEL'];
					$topDepth += $this->SECTION['DEPTH_LEVEL'];
				}
				
				$parentSectionID = 0;
				
				$rsSections = CIBlockSection::GetList(array("left_margin" => "asc"), $arSectionsFilter, false, array("ID", "DEPTH_LEVEL", "NAME"));
				while($arSection = $rsSections->Fetch())
				{
					if($arSection["DEPTH_LEVEL"] <= $topDepth || $arParams["TOP_DEPTH_LEVEL"] == 0)
					{
						$arSections[$arSection["ID"]] = $arSection["ID"];
						$parentSectionID = $arSection["ID"];
					}
					else
					{
						$arSections[$arSection["ID"]] = $parentSectionID;
					}
				}
			}
			
			//available
			$bAvailable = false;
			if(in_array('AVAILABLE', $arParams['FIELDS'])){
				$arSelect[] = "CATALOG_QUANTITY";
				$bAvailable = true;
			}
			
			//quantity
			$bQuantity = false;
			if(in_array('QUANTITY', $arParams['FIELDS'])){
				$arSelect[] = "CATALOG_QUANTITY";
				$bQuantity = true;
			}
			
			$rsElements = CIBlockElement::GetList(array(), $arElementFilter, false, false, $arSelect);
			while($arElement = $rsElements->Fetch())
			{
				$id = $arElement['ID'];

				foreach($arResult["PRICES"] as $NAME => $arPrice){
					if(isset($arResult["ITEMS"][$NAME])){
						$price = $this->fillItemPrices($arResult["ITEMS"][$NAME], $arElement);
						$arResult['ELEMENTS'][$id][$NAME] = $price;
					}
				}

				if(intval($arElement['IBLOCK_SECTION_ID'])){
					$section_id = intval($arElement['IBLOCK_SECTION_ID']);
					
					if($section_id != $this->SECTION_ID && isset($arSections[$section_id]))
					{
						$section_id = $arSections[$section_id];
						$arResult['ELEMENTS'][$id]['SECTIONS'] = $section_id;
						
						if(!isset($arVariants['SECTIONS'][$section_id]))
							$arVariants['SECTIONS'][$section_id] = array('ID' => array(), 'CNT' => 0);
						
						if(!$arVariants['SECTIONS'][$section_id]['ID'][$id]){
							$arVariants['SECTIONS'][$section_id]['ID'][$id] = true;
							$arVariants['SECTIONS'][$section_id]['CNT']++;
						}
					}
				}
				
				if($bAvailable)
				{
					//if(!isset($arResult['ELEMENTS_SKU'][$id]))
					//{
						$available = $arElement['CATALOG_AVAILABLE'];
						$arResult['ELEMENTS'][$id]['AVAILABLE'] = $available;
						
						if(!isset($arVariants['AVAILABLE'][$available]))
							$arVariants['AVAILABLE'][$available] = array('ID' => array(), 'CNT' => 0);
						
						if(!$arVariants['AVAILABLE'][$available]['ID'][$id]){
							$arVariants['AVAILABLE'][$available]['ID'][$id] = true;
							$arVariants['AVAILABLE'][$available]['CNT']++;
						}
					//}
				}
				
				if($bQuantity)
				{
					if(!isset($arResult['ELEMENTS_SKU'][$id]))
					{
						$quantity = $arElement['CATALOG_QUANTITY'];
						$arResult['ELEMENTS'][$id]['QUANTITY'] = $quantity;
						
						if(!isset($arVariants['QUANTITY'][$quantity]))
							$arVariants['QUANTITY'][$quantity] = array('ID' => array(), 'CNT' => 0);
						
						if(!$arVariants['QUANTITY'][$quantity]['ID'][$id]){
							$arVariants['QUANTITY'][$quantity]['ID'][$id] = true;
							$arVariants['QUANTITY'][$quantity]['CNT']++;
						}
					}
				}
			}
		}
		
		if(in_array('STORES', $arParams['FIELDS']))
		{
			$rsStore = CCatalogStoreProduct::GetList(array(), array("@PRODUCT_ID" => $arIDs, ">AMOUNT" => 0), false, false, array('PRODUCT_ID', 'STORE_ID', 'AMOUNT'));
			while($arStore = $rsStore->Fetch())
			{
				if(intval($arStore['AMOUNT']))
				{
					$arElement = array();
					$store_id = $arStore['STORE_ID'];
					$id = $arStore['PRODUCT_ID'];
					
					if(isset($arParentIDs[$id])){
						$id = $arParentIDs[$id];
						$arElement = &$arResult['ELEMENTS_SKU'][$id][$arStore['PRODUCT_ID']];
					}
					else
						$arElement = &$arResult['ELEMENTS'][$id];
					
					if(!isset($arElement['STORES']))
						$arElement['STORES'] = array();
						
					$arElement['STORES'][] = $store_id;
					
					if(!isset($arVariants['STORES'][$store_id]))
						$arVariants['STORES'][$store_id] = array('ID' => array(), 'CNT' => 0);
					
					if(!$arVariants['STORES'][$store_id]['ID'][$id]){
						$arVariants['STORES'][$store_id]['ID'][$id] = true;
						$arVariants['STORES'][$store_id]['CNT']++;
					}
					unset($arElement);
				}
			}
			unset($arParentIDs);
		}
		
		$arResult["ITEMS_COUNT_SHOW"] = 0;
		$arCodes = array();
		
		foreach($arResult["ITEMS"] as $PID => &$arItem)
		{
			if(intval($PID) || in_array($PID, $arParams['FIELDS']))
			{
				foreach($arVariants[$PID] as $value=>$cnt){
					
					$this->fillItemValues($arItem,  $value, $cnt['CNT']);
				}
			}
			
			if(intval($arCodes[$arItem['CODE_ALT']]))
			{
				$arItem['CODE_ALT'] .= ++$arCodes[$arItem['CODE_ALT']];
			}
			else
			{
				$arCodes[$arItem['CODE_ALT']] = 1;
			}

			if(intval($PID) && $arItem['PROPERTY_TYPE'] != 'N' || in_array($PID, $arParams['FIELDS']))
				uasort($arResult["ITEMS"][$PID]["VALUES"], array($this, "_natsort"));
			
			if($arItem["PROPERTY_TYPE"] == "N" || isset($arItem["PRICE"]))
			{
				$this->roundMinMaxValues($arItem);
				
				if(isset($arItem["VALUES"]["MIN"]["VALUE"]) && isset($arItem["VALUES"]["MAX"]["VALUE"]) && $arItem["VALUES"]["MAX"]["VALUE"] > $arItem["VALUES"]["MIN"]["VALUE"])
				{
					$arResult["ITEMS_COUNT_SHOW"]++;
				}
			}
			elseif(!empty($arItem["VALUES"]) && !isset($arItem["PRICE"]))
			{
				$arResult["ITEMS_COUNT_SHOW"]++;
			}
		}
		unset($arItem);
	}
	
	if($arParams["SORT"])
	{
		$sort = SORT_ASC;
		if($arParams["SORT_ORDER"] == "DESC")
			$sort = SORT_DESC;
		\Bitrix\Main\Type\Collection::sortByColumn($arResult['ITEMS'], array("SORT" => $sort, "PROPERTY_ID" => $sort), '', null, true);
	}
	
	if ($arParams["XML_EXPORT"] === "Y")
	{
		$arResult["SECTION_TITLE"] = "";
		$arResult["SECTION_DESCRIPTION"] = "";

		if ($this->SECTION_ID > 0)
		{
			$arSelect = array("ID", "IBLOCK_ID", "LEFT_MARGIN", "RIGHT_MARGIN");
			if ($arParams["SECTION_TITLE"] !== "")
				$arSelect[] = $arParams["SECTION_TITLE"];
			if ($arParams["SECTION_DESCRIPTION"] !== "")
				$arSelect[] = $arParams["SECTION_DESCRIPTION"];

			$sectionList = CIBlockSection::GetList(array(), array(
				"=ID" => $this->SECTION_ID,
				"IBLOCK_ID" => $this->IBLOCK_ID,
			), false, $arSelect);
			$arResult["SECTION"] = $sectionList->GetNext();

			if ($arResult["SECTION"])
			{
				$arResult["SECTION_TITLE"] = $arResult["SECTION"][$arParams["SECTION_TITLE"]];
				if ($arParams["SECTION_DESCRIPTION"] !== "")
				{
					$obParser = new CTextParser;
					$arResult["SECTION_DESCRIPTION"] = $obParser->html_cut($arResult["SECTION"][$arParams["SECTION_DESCRIPTION"]], 200);
				}
			}
		}
	}
	
	$arResult['ELEMENTS_COUNT'] = count($arResult['ELEMENTS']);
	$this->EndResultCache();
}

//closed items
foreach($arResult["ITEMS"] as $PID => &$arItem)
{
	if(isset($_COOKIE['kombox-filter-closed-'.$arItem['CODE_ALT'].'-'.$arItem['ID']]))
	{
		if($_COOKIE['kombox-filter-closed-'.$arItem['CODE_ALT'].'-'.$arItem['ID']] == 'true')
			$arItem['CLOSED'] = true;
		else
			$arItem['CLOSED'] = false;
	}
}
unset($arItem);

$arResult['IS_SEF'] = $this->IsSefMode();

/*Handle checked for checkboxes and html control value for numbers*/
if(isset($_REQUEST["ajax"]) && $_REQUEST["ajax"] === "y")
	$_CHECK = &$_REQUEST;
elseif(isset($_REQUEST["del_filter"]))
	$_CHECK = array();
elseif(isset($_GET["set_filter"]))
	$_CHECK = &$_GET;
elseif($arParams["SAVE_IN_SESSION"] && isset($_SESSION[$FILTER_NAME][$this->IBLOCK_ID][$this->SECTION_ID]))
	$_CHECK = $_SESSION[$FILTER_NAME][$this->IBLOCK_ID][$this->SECTION_ID];
else
	$_CHECK = array();
	
if($arResult['IS_SEF'] && !count($_CHECK))
	$_CHECK = $this->GetSefModeParams();

/*Set state of the html controls depending on filter values*/
$CHECKED = array();
$CHECKED_SKU = array();
$allCHECKED = array();

foreach($arResult["ITEMS"] as $PID => $arItem)
{
	foreach($arItem["VALUES"] as $key => $ar)
	{
		if(isset($_CHECK[$ar["CONTROL_NAME"]]))
		{
			if($arItem["PROPERTY_TYPE"] == "N" || isset($arItem["PRICE"]))
			{
				$arResult["ITEMS"][$PID]["VALUES"][$key]["HTML_VALUE"] = htmlspecialcharsbx($_CHECK[$ar["CONTROL_NAME"]]);
				if(doubleval($_CHECK[$ar["CONTROL_NAME"]]))
				{
					if($key == "MIN")
					{
						if($_CHECK[$ar["CONTROL_NAME"]] > $ar["VALUE"])
							$CHECKED[$PID]["MIN"] = $_CHECK[$ar["CONTROL_NAME"]];
					}
					else
					{
						if($_CHECK[$ar["CONTROL_NAME"]] < $ar["VALUE"])
							$CHECKED[$PID]["MAX"] = $_CHECK[$ar["CONTROL_NAME"]];
					}
				}
			}
			elseif($_CHECK[$ar["CONTROL_NAME"]] == $ar["HTML_VALUE"])
			{
				$arResult["ITEMS"][$PID]["VALUES"][$key]["CHECKED"] = true;
				$CHECKED[$PID][$key] = $key;
			}
		}
	}

	if(isset($CHECKED[$PID]))	
	{
		$allCHECKED[$PID] = $CHECKED[$PID];
			
		if($arItem["SKU"]){
			$CHECKED_SKU[$PID] = $CHECKED[$PID];
			unset($CHECKED[$PID]);
		}
		
		if($arItem['PROPERTY_TYPE'] == 'STORES'){
			$CHECKED_SKU[$PID] = $CHECKED[$PID];
		}
		
		if(isset($arItem["PRICE"]) && is_array($CHECKED[$PID])){
			$CHECKED_SKU[$PID] = $CHECKED[$PID];
		}
	}
}

$arResult['SET_FILTER'] = false;	
$filterIDs = false;

if ($_CHECK && (count($CHECKED) || count($CHECKED_SKU)))
{
	/*Disable composite mode when filter checked*/
	if(method_exists($this, 'setFrameMode')) 
		$this->setFrameMode(false);
	
	$arResult['SET_FILTER'] = true;
	$arVariants = array();
	$filterIDs = array();
	
	foreach($arResult['ELEMENTS'] as &$res)
	{
		$id = $res['ID'];
		$hash = array();
		if(is_array($arResult['ELEMENTS_SKU'][$id]))
		{
			$this->checkElement($CHECKED, $res, $hash);
			
			foreach($arResult['ELEMENTS_SKU'][$id] as $k=>&$resSku)
			{
				$skuID = $resSku['ID'];
				$skuHash = array();
				$this->checkElement($CHECKED_SKU, $resSku, $skuHash);

				$mergeHash = $hash + $skuHash;
				$resSku += $res;
	
				foreach($mergeHash as $key => &$value){
					if(isset($skuHash[$key]) && isset($hash[$key]))
						$value = $skuHash[$key] | $hash[$key];
				}
				unset($value);
				
				$insertToFilter = true;

				foreach ($arResult["ITEMS"] as $PID => &$arItem)
				{
					if($this->checkElementByHash($allCHECKED, $mergeHash, $PID))
					{
						$key = $resSku[$PID];

						if(!is_array($key))
							$key = array($key);
	
						foreach($key as $k)
						{						
							if(!isset($arVariants[$PID][$k]))
								$arVariants[$PID][$k] = array('ID' => array(), 'CNT' => 0);
							
							if(!$arVariants[$PID][$k]['ID'][$id]){
								$arVariants[$PID][$k]['ID'][$id] = true;
								$arVariants[$PID][$k]['CNT']++;
							}
						}
					}
					else
					{
						$insertToFilter = false;
					}
				}
				
				if($insertToFilter)
				{
					$filterIDs[$id] = $id;
				}
			}
		}
		else
		{
			if($this->checkElement($allCHECKED, $res, $hash))
				$filterIDs[$id] = $id;

			foreach ($arResult["ITEMS"] as $PID => &$arItem)
			{
				if($this->checkElementByHash($allCHECKED, $hash, $PID))
				{
					$key = $res[$PID];
					
					if(!is_array($key))
						$key = array($key);

					foreach($key as $k)
					{						
						if(!isset($arVariants[$PID][$k]))
							$arVariants[$PID][$k] = array('ID' => array(), 'CNT' => 0);
						
						if(!$arVariants[$PID][$k]['ID'][$id]){
							$arVariants[$PID][$k]['ID'][$id] = true;
							$arVariants[$PID][$k]['CNT']++;
						}
					}
				}
			}
		}
	}
	
	$this->contractionValues($arVariants);

	unset($arItem);
	unset($arVariants);
	
	if($arResult["EXTENDED_MODE"])
	{
		if(!count($filterIDs))
			$filterIDs = array(0);
			
		${$FILTER_NAME}['ID'] = $filterIDs;
	}
}

if(!$arResult["EXTENDED_MODE"] || !isset(${$FILTER_NAME}['ID']))
{
	/*Make iblock filter*/
	${$FILTER_NAME} = $this->generatePropertiesFilter();
}

/*Save to session if needed*/
if($arParams["SAVE_IN_SESSION"])
{
	if(!is_array($_SESSION[$FILTER_NAME][$this->IBLOCK_ID]))
		$_SESSION[$FILTER_NAME][$this->IBLOCK_ID] = array();
		
	$_SESSION[$FILTER_NAME][$this->IBLOCK_ID][$this->SECTION_ID] = array();
	foreach($arResult["ITEMS"] as $PID => $arItem)
	{
		foreach($arItem["VALUES"] as $key => $ar)
		{
			if(isset($_CHECK[$ar["CONTROL_NAME"]]))
			{
				if($arItem["PROPERTY_TYPE"] == "N" || isset($arItem["PRICE"]))
					$_SESSION[$FILTER_NAME][$this->IBLOCK_ID][$this->SECTION_ID][$ar["CONTROL_NAME"]] = $_CHECK[$ar["CONTROL_NAME"]];
				elseif($_CHECK[$ar["CONTROL_NAME"]] == $ar["HTML_VALUE"])
					$_SESSION[$FILTER_NAME][$this->IBLOCK_ID][$this->SECTION_ID][$ar["CONTROL_NAME"]] = $_CHECK[$ar["CONTROL_NAME"]];
			}
		}
	}
}

$pageURL = $arParams["PAGE_URL"];
if(!strlen($pageURL))
	$pageURL = $APPLICATION->GetCurPage();

$arResult["DELETE_URL"] = $APPLICATION->GetCurPageParam('del_filter=y');

if($arResult['IS_SEF'])
{
	$arResult["DELETE_URL"] = $pageURL;
	$sefURL = $pageURL;	
}

$paramsToDelete = array("set_filter", "del_filter", "ajax", "bxajaxid", "AJAX_CALL");
foreach($arResult["ITEMS"] as $PID => $arItem)
{
	foreach($arItem["VALUES"] as $key => $ar)
		$paramsToDelete[] = $ar["CONTROL_NAME"];
}

$clearURL = CHTTP::urlDeleteParams($pageURL, $paramsToDelete, array("delete_system_params" => true));

if(isset($_REQUEST["ajax"]) && $_REQUEST["ajax"] === "y")
{
	$arFilter = $this->makeFilter(${$FILTER_NAME});
	$arResult["ELEMENT_COUNT"] = CIBlockElement::GetList(array(), $arFilter, array(), false);

	$paramsToAdd = array(
		"set_filter" => "y",
	);
	
	if($arResult['IS_SEF'])
		$sefURL .= 'filter/';
	
	foreach($arResult["ITEMS"] as $PID => $arItem)
	{
		$values = array();
		foreach($arItem["VALUES"] as $key => $ar)
		{
			if(isset($_CHECK[$ar["CONTROL_NAME"]]))
			{
				if($arItem["PROPERTY_TYPE"] == "N" || isset($arItem["PRICE"])){
					$paramsToAdd[$ar["CONTROL_NAME"]] = $_CHECK[$ar["CONTROL_NAME"]];
					$values[$key] = $_CHECK[$ar["CONTROL_NAME"]];
				}
				elseif($_CHECK[$ar["CONTROL_NAME"]] == $ar["HTML_VALUE"]){
					$paramsToAdd[$ar["CONTROL_NAME"]] = $_CHECK[$ar["CONTROL_NAME"]];
					$values[] = $ar["CONTROL_NAME_ALT"];
				}
			}
		}
		
		if(count($values) && $arResult['IS_SEF'])
		{
			if($arItem["PROPERTY_TYPE"] == "N" || isset($arItem["PRICE"]))
			{
				$sefURL .= $arItem["CODE_ALT"];
				if(isset($values['MIN']))
					$sefURL .= '-from-'.$values['MIN'];
				
				if(isset($values['MAX']))
					$sefURL .= '-to-'.$values['MAX'];
				$sefURL .= '/';
			}
			else
			{
				$sefURL .= $arItem["CODE_ALT"].'-'.implode('-or-', $values).'/';
			}
		}
	}
	
	if($arResult['IS_SEF'])
	{
		$arResult["FILTER_URL"] = htmlspecialcharsbx(CHTTP::urlAddParams($sefURL, array(), array(
			"skip_empty" => true,
			"encode" => true,
		)));
	}
	else
	{
		$arResult["FILTER_URL"] = htmlspecialcharsbx(CHTTP::urlAddParams($clearURL, $paramsToAdd, array(
			"skip_empty" => true,
			"encode" => true,
		)));
	}
	
	if (isset($_GET["bxajaxid"]))
	{
		$arResult["COMPONENT_CONTAINER_ID"] = htmlspecialcharsbx("comp_".$_GET["bxajaxid"]);
		if ($arParams["INSTANT_RELOAD"])
			$arResult["INSTANT_RELOAD"] = true;
	}
	
	if($arResult['IS_SEF'])
	{
		$arResult["FILTER_AJAX_URL"] = htmlspecialcharsbx(CHTTP::urlAddParams($sefURL, array(
			"AJAX_CALL" => "Y",
			"bxajaxid" => $_GET["bxajaxid"],
		), array(
			"skip_empty" => true,
			"encode" => true,
		)));
	}
	else
	{
		$arResult["FILTER_AJAX_URL"] = htmlspecialcharsbx(CHTTP::urlAddParams($clearURL, $paramsToAdd + array(
			"AJAX_CALL" => "Y",
			"bxajaxid" => $_GET["bxajaxid"],
		), array(
			"skip_empty" => true,
			"encode" => true,
		)));
	}
}

$arInputNames = array();
foreach($arResult["ITEMS"] as $PID => $arItem)
{
	foreach($arItem["VALUES"] as $key => $ar)
		$arInputNames[$ar["CONTROL_NAME"]] = true;
}
$arInputNames["set_filter"]=true;
$arInputNames["del_filter"]=true;

$arSkip = array(
	"AUTH_FORM" => true,
	"TYPE" => true,
	"USER_LOGIN" => true,
	"USER_CHECKWORD" => true,
	"USER_PASSWORD" => true,
	"USER_CONFIRM_PASSWORD" => true,
	"USER_EMAIL" => true,
	"captcha_word" => true,
	"captcha_sid" => true,
	"login" => true,
	"Login" => true,
	"backurl" => true,
	"ajax" => true,
	"mode" => true,
	"clear_cache" => true
);

$arResult["FORM_ACTION"] = $clearURL;
$arResult["HIDDEN"] = array();
foreach(array_merge($_GET, $_POST) as $key => $value)
{
	if(
		!array_key_exists($key, $arInputNames)
		&& !array_key_exists($key, $arSkip)
		&& !is_array($value)
	)
	{
		$arResult["HIDDEN"][] = array(
			"CONTROL_ID" => htmlspecialcharsbx($key),
			"CONTROL_NAME" => htmlspecialcharsbx($key),
			"HTML_VALUE" => htmlspecialcharsbx($value),
		);
	}
}

if (
	$arParams["XML_EXPORT"] === "Y"
	&& $arResult["SECTION"]
	&& ($arResult["SECTION"]["RIGHT_MARGIN"] - $arResult["SECTION"]["LEFT_MARGIN"]) === 1
)
{
	$exportUrl = CHTTP::urlAddParams($clearURL, array("mode" => "xml"));
	$APPLICATION->AddHeadString('<meta property="ya:interaction" content="XML_FORM" />');
	$APPLICATION->AddHeadString('<meta property="ya:interaction:url" content="'.CHTTP::urn2uri($exportUrl).'" />');
}

${$FILTER_NAME} = $this->makeFilter(${$FILTER_NAME});
unset(${$FILTER_NAME}['OFFERS']);
p(${$FILTER_NAME});
if ($arParams["XML_EXPORT"] === "Y" && $_REQUEST["mode"] === "xml")
{
	if(method_exists($this, 'setFrameMode')) 
		$this->setFrameMode(false);
	ob_start();
	$this->IncludeComponentTemplate("xml");
	$xml = ob_get_contents();
	$APPLICATION->RestartBuffer();
	while(ob_end_clean());
	header("Content-Type: text/xml; charset=utf-8");
	echo $APPLICATION->convertCharset($xml, LANG_CHARSET, "utf-8");
	require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
	die();
}
elseif(isset($_REQUEST["ajax"]) && $_REQUEST["ajax"] === "y")
{
	if(method_exists($this, 'setFrameMode')) 
		$this->setFrameMode(false);
	$this->IncludeComponentTemplate("ajax");
	die();
}
elseif($arParams["BITRONIC_AJAX"] == "Y")
{
	if(IsModuleInstalled("yenisite.bitronic"))
		$this->IncludeComponentTemplate("ajax_bitronic");
}
else
{
	if($arParams["INCLUDE_JQUERY"])
		CJSCore::Init(array("jquery"));
		
	$this->IncludeComponentTemplate();
}
?>