<?

/**
 * Copyright (c) 6/3/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
global $USER;
global $APPLICATION;

if(!CModule::IncludeModule('collected.filter'))
{
	ShowError(GetMessage('COLLECTED_CMP_FILTER_MODULE_NOT_INSTALLED'));
	return;
}

if(!CModule::IncludeModule('iblock'))
{
	ShowError(GetMessage('COLLECTED_CMP_FILTER_IBLOCK_MODULE_NOT_INSTALLED'));
	return;
}

$arResult['IS_SEF'] = CCollectedFilter::IsSefMode($arParams['PAGE_URL']);

$pageURL = $arParams['PAGE_URL'];
if(!strlen($pageURL))
	$pageURL = $APPLICATION->GetCurPage(false);

$arResult['DELETE_URL'] = $pageURL;

$FILTER_NAME = (string)$arParams['FILTER_NAME'];

global ${$FILTER_NAME};
if(!is_array(${$FILTER_NAME}))
    ${$FILTER_NAME} = array();

$arrFilter = &${$FILTER_NAME};

if($this->StartResultCache(false, array($arrFilter,($arParams['CACHE_GROUPS']? $USER->GetGroups(): false), $arResult['IS_SEF'])))
{
	$arResult['PRICES'] = CIBlockPriceTools::GetCatalogPrices($arParams['IBLOCK_ID'], $arParams['PRICE_CODE']);
	$arItems = $this->getResultItems();
	$arResult['ITEMS'] = $arItems;
	
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
	
	if(!empty($arResult['ITEMS']))
	{
		$arSkuFilter = false;
		$arElementFilter = array(
			'IBLOCK_ID' => $this->IBLOCK_ID,
			'ACTIVE_DATE' => 'Y',
			'ACTIVE' => 'Y',
			'CHECK_PERMISSIONS' => 'Y'
		);
		
		if(intval($this->SECTION_ID)){
			$arElementFilter['SUBSECTION'] = $this->SECTION_ID;
			$arElementFilter['SECTION_SCOPE'] = 'IBLOCK';
		}
		
		if($arParams['HIDE_NOT_AVAILABLE'])
			$arElementFilter['CATALOG_AVAILABLE'] = 'Y';
		
		$arElementFilter = array_merge($arrFilter, $arElementFilter);
		
		$res = CCollectedFilterIBlock::GetPropertyValues($this->IBLOCK_ID, $arElementFilter, $arItems);
		
		$arResult['ELEMENTS'] = array();
		$arIDs = array();
		
		while($el = $res->Fetch())
		{
			$id = $el['IBLOCK_ELEMENT_ID'];
			$arIDs[$id] = $id;
			$result = $this->getElement($arResult['ITEMS'], $el);
			$result['ID'] = $id;
			$arResult['ELEMENTS'][$id] = $result;
		}
		
		if(!empty($arIDs) && $this->SKU_IBLOCK_ID)
		{
			$arItemsSKU = $this->getResultSkuItems();
			
			if($arResult['SKU_PROPERTY_COUNT'] > 0)
			{
				$arResult['ELEMENTS_SKU'] = array();
				$arParentIDs = array();
				
				$arSkuFilter = array(
					'IBLOCK_ID' => $this->SKU_IBLOCK_ID,
					'ACTIVE_DATE' => 'Y',
					'ACTIVE' => 'Y',
					'CHECK_PERMISSIONS' => 'Y',
					'=PROPERTY_'.$this->SKU_PROPERTY_ID => $arIDs,
				);
				
				if($arParams['HIDE_NOT_AVAILABLE'])
					$arSkuFilter['CATALOG_AVAILABLE'] = 'Y';
				
				$res = CCollectedFilterIBlock::GetPropertyValues($this->SKU_IBLOCK_ID, $arSkuFilter, $arItemsSKU);
				while($el = $res->Fetch())
				{
					$id = $el[$this->SKU_PROPERTY_ID];
					$result = $this->getElement($arItemsSKU, $el);
					$result['ID'] = $el['IBLOCK_ELEMENT_ID'];
					$arIDs[$result['ID']] = $result['ID'];
					$arResult['ELEMENTS_SKU'][$id][$result['ID']] = $result;
					
					$arParentIDs[$result['ID']] = $id;
				}
				$arResult['ITEMS'] += $arItemsSKU;
				$arItemsSKU += $arResult['PRICES'] + $this->getFieldsItems();
			}
		}
		
		$arResult['STORES_CHECK'] = $this->isStoreCheck();
	
		if(in_array('STORES', $arParams['FIELDS']) || $arResult['STORES_CHECK'])
		{
			$arFilter =  array('@PRODUCT_ID' => $arIDs, '>AMOUNT' => 0);
			
			if($arResult['STORES_CHECK'])
			{
				$arFilter['@STORE_ID'] = array();
				foreach($arParams['STORES_ID'] as $id)
					$arFilter['@STORE_ID'][$id] = $id;
			}

			$rsStore = CCatalogStoreProduct::GetList(array(), $arFilter, false, false, array('PRODUCT_ID', 'STORE_ID', 'AMOUNT'));
			
			while($arStore = $rsStore->Fetch())
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
					
				if($arResult['STORES_CHECK'])
				{
					if(!isset($arElement['STORES_QUANTITY']))
						$arElement['STORES_QUANTITY'] = array();
					
					$arElement['STORES_QUANTITY'][$store_id] = intval($arStore['AMOUNT']);
				}

				unset($arElement);
			}
		}

		if(
			count($arResult['PRICES']) || 
			$arResult['STORES_CHECK'] || 
			in_array('SECTIONS', $arParams['FIELDS']) || 
			in_array('AVAILABLE', $arParams['FIELDS']) || 
			in_array('QUANTITY', $arParams['FIELDS'])
		)
		{
			$arSelect = array('ID', 'IBLOCK_ID');
			//prices
			foreach($arResult['PRICES'] as $value)
			{
				$arSelect[] = $value['SELECT'];
				$arElementFilter['CATALOG_SHOP_QUANTITY_'.$value['ID']] = 1;
			}
			
			//sections
			$arSections = array();
			if(in_array('SECTIONS', $arParams['FIELDS']))
			{
				$arSelect[] = 'IBLOCK_SECTION_ID';
				
				$arSectionsFilter = array(
					'ACTIVE' => 'Y', 
					'GLOBAL_ACTIVE' => 'Y', 
					'IBLOCK_ID' => $this->IBLOCK_ID
				);
				
				$topDepth = $arParams['TOP_DEPTH_LEVEL'];
				
				if(intval($this->SECTION_ID)){
					$arSectionsFilter['>LEFT_MARGIN'] = $this->SECTION['LEFT_MARGIN'];
					$arSectionsFilter['<RIGHT_MARGIN'] = $this->SECTION['RIGHT_MARGIN'];
					$arSectionsFilter['>DEPTH_LEVEL'] = $this->SECTION['DEPTH_LEVEL'];
					$topDepth += $this->SECTION['DEPTH_LEVEL'];
				}
				
				$parentSectionID = 0;
				
				$rsSections = CIBlockSection::GetList(array('left_margin' => 'asc'), $arSectionsFilter, false, array('ID', 'DEPTH_LEVEL', 'NAME'));
				while($arSection = $rsSections->Fetch())
				{
					if($arSection['DEPTH_LEVEL'] <= $topDepth || $arParams['TOP_DEPTH_LEVEL'] == 0)
					{
						$arSections[$arSection['ID']] = $arSection['ID'];
						$parentSectionID = $arSection['ID'];
					}
					else
					{
						$arSections[$arSection['ID']] = $parentSectionID;
					}
				}
			}
			
			//available
			$bAvailable = false;
			if(in_array('AVAILABLE', $arParams['FIELDS']) || $arResult['STORES_CHECK']){
				$arSelect[] = 'CATALOG_QUANTITY';
				$bAvailable = true;
			}
			
			//quantity
			$bQuantity = false;
			if(in_array('QUANTITY', $arParams['FIELDS'])){
				$arSelect[] = 'CATALOG_QUANTITY';
				$bQuantity = true;
			}
			
			$rsElements = CIBlockElement::GetList(array(), $arElementFilter, false, false, $arSelect);
			while($arElement = $rsElements->Fetch())
			{
				$id = $arElement['ID'];

				foreach($arResult['PRICES'] as $NAME => $arPrice)
				{
					if(isset($arResult['ITEMS'][$NAME]))
					{
						$price = $arElement['CATALOG_PRICE_'.$arResult['ITEMS'][$NAME]['ID']];
						
						if(strlen($price) && $arParams['CONVERT_CURRENCY'])
						{
							$currency = $arElement['CATALOG_CURRENCY_'.$arResult['ITEMS'][$NAME]['ID']];
							if($currency != $arParams['CURRENCY_ID'])
								$price = CCollectedFilter::ConvertCurrency($price, $currency, $arParams['CURRENCY_ID']);
						}
						
						if(!strlen($arResult['ITEMS'][$NAME]['SETTINGS']['SLIDER_UNITS']))
						{
							$currency = $arElement['CATALOG_CURRENCY_'.$arResult['ITEMS'][$NAME]['ID']];
							if(strlen($currency))
							{
								$arResult['ITEMS'][$NAME]['SETTINGS']['SLIDER_UNITS'] = CCollectedFilter::getCurrencyFullName($currency);
							}
						}
						
						$arResult['ELEMENTS'][$id][$NAME] = $price;
					}
				}

				if(intval($arElement['IBLOCK_SECTION_ID'])){
					$section_id = intval($arElement['IBLOCK_SECTION_ID']);
					
					if($section_id != $this->SECTION_ID && isset($arSections[$section_id]))
					{
						$section_id = $arSections[$section_id];
						$arResult['ELEMENTS'][$id]['SECTIONS'] = $section_id;
					}
				}
				
				if($bAvailable)
				{
					if($arResult['STORES_CHECK'])
					{
						$bCanBuyZero = $arElement['CATALOG_CAN_BUY_ZERO'] == 'Y';
						if(is_array($arResult['ELEMENTS'][$id]['STORES_QUANTITY']))
						{
							$available = 'N';
							$arResult['ELEMENTS'][$id]['STORES_AVAILABLE'] = array();
							foreach($arResult['ELEMENTS'][$id]['STORES_QUANTITY'] as $store_id => $quantity)
							{
								if($bCanBuyZero || $quantity > 0)
								{
									$arResult['ELEMENTS'][$id]['STORES_AVAILABLE'][$store_id] = true;
									$available = 'Y';
								}
								else
									$arResult['ELEMENTS'][$id]['STORES_AVAILABLE'][$store_id] = false;
							}
						}
						else
							$available = 'N';
					}
					else
						$available = $arElement['CATALOG_AVAILABLE'];
					
					$arResult['ELEMENTS'][$id]['AVAILABLE'] = $available;
				}
				
				if($bQuantity)
				{
					if(!isset($arResult['ELEMENTS_SKU'][$id]))
					{
						$quantity = $arElement['CATALOG_QUANTITY'];
						$arResult['ELEMENTS'][$id]['QUANTITY'] = $quantity;
					}
				}
			}
		}
		
		if(is_array($arSkuFilter))
		{
			if(
				count($arResult['PRICES']) || 
				$arResult['STORES_CHECK'] || 
				in_array('AVAILABLE', $arParams['FIELDS']) || 
				in_array('QUANTITY', $arParams['FIELDS'])
			)
			{
				$arSelect = array('ID');
				foreach($arResult['PRICES'] as $value)
				{
					$arSelect[] = $value['SELECT'];
					$arSkuFilter['CATALOG_SHOP_QUANTITY_'.$value['ID']] = 1;
				}
				
				//available
				$bAvailable = false;
				if(in_array('AVAILABLE', $arParams['FIELDS']) || $arResult['STORES_CHECK']){
					$arSelect[] = 'CATALOG_QUANTITY';
					$bAvailable = true;
				}
				
				//quantity
				$bQuantity = false;
				if(in_array('QUANTITY', $arParams['FIELDS'])){
					$arSelect[] = 'CATALOG_QUANTITY';
					$bQuantity = true;
				}
				
				$rsElements = CIBlockElement::GetList(array(), $arSkuFilter, false, false, $arSelect);
				while($arSku = $rsElements->Fetch())
				{
					$id = $arParentIDs[$arSku['ID']];
					
					foreach($arResult['PRICES'] as $NAME => $arPrice)
					{
						if(isset($arResult['ITEMS'][$NAME]))
						{
							$price = $arSku['CATALOG_PRICE_'.$arResult['ITEMS'][$NAME]['ID']];
					
							if(strlen($price) && $arParams['CONVERT_CURRENCY'])
							{
								$currency = $arSku['CATALOG_CURRENCY_'.$arResult['ITEMS'][$NAME]['ID']];
								if($currency != $arParams['CURRENCY_ID'])
									$price = CCollectedFilter::ConvertCurrency($price, $currency, $arParams['CURRENCY_ID']);
							}
							
							if(!strlen($arResult['ITEMS'][$NAME]['SETTINGS']['SLIDER_UNITS']))
							{
								$currency = $arSku['CATALOG_CURRENCY_'.$arResult['ITEMS'][$NAME]['ID']];
								if(strlen($currency))
								{
									$arResult['ITEMS'][$NAME]['SETTINGS']['SLIDER_UNITS'] = CCollectedFilter::getCurrencyFullName($currency);
								}
							}
							
							$arResult['ELEMENTS_SKU'][$id][$arSku['ID']][$NAME] = $price;
						}
					}
					
					if($bAvailable)
					{
						if($arResult['STORES_CHECK'])
						{
							$bCanBuyZero = $arSku['CATALOG_CAN_BUY_ZERO'] == 'Y';
							if(is_array($arResult['ELEMENTS_SKU'][$id][$arSku['ID']]['STORES_QUANTITY']))
							{
								$available = 'N';
								$arResult['ELEMENTS_SKU'][$id][$arSku['ID']]['STORES_AVAILABLE'] = array();
								foreach($arResult['ELEMENTS_SKU'][$id][$arSku['ID']]['STORES_QUANTITY'] as $store_id => $quantity)
								{
									if($bCanBuyZero || $quantity > 0)
									{
										$arResult['ELEMENTS_SKU'][$id][$arSku['ID']]['STORES_AVAILABLE'][$store_id] = true;
										$available = 'Y';
									}
									else
										$arResult['ELEMENTS_SKU'][$id][$arSku['ID']]['STORES_AVAILABLE'][$store_id] = false;
								}
							}
							else
								$available = 'N';
						}
						else
							$available = $arSku['CATALOG_AVAILABLE'];
						
						$arResult['ELEMENTS_SKU'][$id][$arSku['ID']]['AVAILABLE'] = $available;
						
						if($arResult['ELEMENTS'][$id]['AVAILABLE'] !== 'Y' && $available == 'Y')
							$arResult['ELEMENTS'][$id]['AVAILABLE'] = 'Y';
					}
					
					if($bQuantity)
					{
						$quantity = $arSku['CATALOG_QUANTITY'];
						$arResult['ELEMENTS_SKU'][$id][$arSku['ID']]['QUANTITY'] = $quantity;
					}
				}
			}
			
			unset($arParentIDs);
		}
		
		if($arParams['HIDE_NOT_AVAILABLE'] && $arResult['STORES_CHECK'])
		{
			$this->hideNotAvailableElements();
			$arResult['ELEMENT_IDS'] = array_keys($arResult['ELEMENTS']);
		}

		$arVariants = array();
		$this->fillVariants($arVariants, $arItems);
		if($arResult['SKU_PROPERTY_COUNT'] > 0)
			$this->fillVariantsSku($arVariants, $arItemsSKU);

		$arResult['ITEMS_COUNT_SHOW'] = 0;
		$arCodes = array();
		
		foreach($arResult['ITEMS'] as $PID => &$arItem)
		{
			if(intval($PID) || in_array($PID, $arParams['FIELDS']))
			{
				foreach($arVariants[$PID] as $value=>$cnt){
					
					$this->fillItemValues($arItem,  $value, $cnt['CNT']);
				}
			}
			elseif(isset($arResult['ITEMS'][$PID]))
			{
				foreach($arVariants[$PID] as $price=>$cnt){
					
					$this->fillItemPrices($arItem, $price);
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

			if($arItem['SETTINGS']['VIEW'] != 'SLIDER')
				$this->sortValues($PID);
			
			if($arItem['SETTINGS']['VIEW'] == 'SLIDER' || isset($arItem['PRICE']))
			{
				$this->roundMinMaxValues($arItem);
				
				if(isset($arItem['VALUES']['MIN']['VALUE']) && isset($arItem['VALUES']['MAX']['VALUE']) && $arItem['VALUES']['MAX']['VALUE'] > $arItem['VALUES']['MIN']['VALUE'])
				{
					$arResult['ITEMS_COUNT_SHOW']++;
				}
			}
			elseif(!empty($arItem['VALUES']) && !isset($arItem['PRICE']))
			{
				$arResult['ITEMS_COUNT_SHOW']++;
			}
		}
		unset($arItem);
		
		$this->makeLinkItems($pageURL);
	}
	
	if($arParams['SORT'])
	{
		$sort = SORT_ASC;
		if($arParams['SORT_ORDER'] == 'DESC')
			$sort = SORT_DESC;
		\Bitrix\Main\Type\Collection::sortByColumn($arResult['ITEMS'], array('SORT' => $sort, 'PROPERTY_ID' => $sort), '', null, true);
	}
	
	if ($arParams['XML_EXPORT'] === 'Y')
	{
		$arResult['SECTION_TITLE'] = '';
		$arResult['SECTION_DESCRIPTION'] = '';

		if ($this->SECTION_ID > 0)
		{
			$arSelect = array('ID', 'IBLOCK_ID', 'LEFT_MARGIN', 'RIGHT_MARGIN');
			if ($arParams['SECTION_TITLE'] !== '')
				$arSelect[] = $arParams['SECTION_TITLE'];
			if ($arParams['SECTION_DESCRIPTION'] !== '')
				$arSelect[] = $arParams['SECTION_DESCRIPTION'];

			$sectionList = CIBlockSection::GetList(array(), array(
				'=ID' => $this->SECTION_ID,
				'IBLOCK_ID' => $this->IBLOCK_ID,
			), false, $arSelect);
			$arResult['SECTION'] = $sectionList->GetNext();

			if ($arResult['SECTION'])
			{
				$arResult['SECTION_TITLE'] = $arResult['SECTION'][$arParams['SECTION_TITLE']];
				if ($arParams['SECTION_DESCRIPTION'] !== '')
				{
					$obParser = new CTextParser;
					$arResult['SECTION_DESCRIPTION'] = $obParser->html_cut($arResult['SECTION'][$arParams['SECTION_DESCRIPTION']], 200);
				}
			}
		}
	}
	
	$arResult['EXTENDED_MODE'] = in_array('STORES', $arParams['FIELDS']) || in_array('SECTIONS', $arParams['FIELDS']) || $arParams['CONVERT_CURRENCY'] || $arResult['STORES_CHECK'] || $arParams["EXTENDED_MODE"];
	$arResult['ELEMENTS_COUNT'] = count($arResult['ELEMENTS']);
	
	$this->EndResultCache();
}

//closed items
foreach($arResult['ITEMS'] as $PID => &$arItem)
{
	if(isset($_COOKIE['collected-filter-closed-'.$arItem['CODE_ALT'].'-'.$arItem['ID']]))
	{
		if($_COOKIE['collected-filter-closed-'.$arItem['CODE_ALT'].'-'.$arItem['ID']] == 'true')
			$arItem['CLOSED'] = true;
		else
			$arItem['CLOSED'] = false;
	}
}
unset($arItem);

if ($this->isSetFilter())
{
	/*Disable composite mode when filter checked*/
	if(method_exists($this, 'setFrameMode') && !$arParams['USE_COMPOSITE_FILTER']) 
		$this->setFrameMode(false);

	$this->checkElements();
	
	$this->makeLinkItems($pageURL);
}
else
{
	if($arParams['HIDE_NOT_AVAILABLE'] && $arResult['STORES_CHECK'])
	{
		if(empty($arResult['ELEMENTS']))
			${$FILTER_NAME}['ID'] = array(0);
		else	
			${$FILTER_NAME}['ID'] = $arResult['ELEMENT_IDS'];
			
		unset($arResult['ELEMENT_IDS']);
	}
}

if(!$arResult['EXTENDED_MODE'] || !isset(${$FILTER_NAME}['ID']))
{
	/*Make iblock filter*/
	${$FILTER_NAME} = $this->getFilter();
}

/*Save to session if needed*/
if($arParams['SAVE_IN_SESSION'])
{
	$this->saveInSession();
}

if(isset($_REQUEST['ajax']) && $_REQUEST['ajax'] === 'y')
{
	$arResult['ELEMENT_COUNT'] = $this->getElementCount();
	$arResult['FILTER_URL'] = $this->getUrl($pageURL, $arResult['REQUEST']);
	$arResult['FILTER_AJAX_URL'] = $this->getUrl($pageURL, $arResult['REQUEST'], array('AJAX_CALL' => 'Y', 'bxajaxid' => $_GET['bxajaxid']));
	
	if (isset($_GET['bxajaxid']))
	{
		$arResult['COMPONENT_CONTAINER_ID'] = htmlspecialcharsbx('comp_'.$_GET['bxajaxid']);
		if ($arParams['INSTANT_RELOAD'])
			$arResult['INSTANT_RELOAD'] = true;
	}
}

$arResult['FORM_ACTION'] = $this->getUrl($pageURL);
$arResult['HIDDEN'] = $this->getHidden();

if ($arParams['XML_EXPORT'] === 'Y')
{
	$exportUrl = CHTTP::urlAddParams($arResult['FORM_ACTION'], array('mode' => 'xml'));
	
	$APPLICATION->AddHeadString('<meta property="ya:interaction" content="XML_FORM" />');
	$APPLICATION->AddHeadString('<meta property="ya:interaction:url" content="'.CHTTP::urn2uri($exportUrl).'" />');
}

if ($arParams['XML_EXPORT'] === 'Y' && $_REQUEST['mode'] === 'xml')
{
	if(method_exists($this, 'setFrameMode')) 
		$this->setFrameMode(false);
	ob_start();
	$this->IncludeComponentTemplate('xml');
	$xml = ob_get_contents();
	$APPLICATION->RestartBuffer();
	while(ob_end_clean());
	header('Content-Type: text/xml; charset=utf-8');
	echo $APPLICATION->convertCharset($xml, LANG_CHARSET, 'utf-8');
	require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_after.php');
	die();
}
elseif(isset($_REQUEST['ajax']) && $_REQUEST['ajax'] === 'y')
{
	if(method_exists($this, 'setFrameMode')) 
		$this->setFrameMode(false);
	$arResult = $this->ajaxResult();
	$this->prepareItems();
	$this->IncludeComponentTemplate('ajax');
	die();
}
elseif($arParams['BITRONIC_AJAX'] == 'Y' && $arResult['isBitronic'] == 'Y')
{
	$arResult = $this->ajaxResult();
	$this->prepareItems();
	$this->IncludeComponentTemplate('ajax_bitronic');
}
else
{
	if($arParams['INCLUDE_JQUERY'])
		CJSCore::Init(array('jquery'));
		
	$this->prepareItems();
	$this->IncludeComponentTemplate();

	return $this->getReturn();
}
?>