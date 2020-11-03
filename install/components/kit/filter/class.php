<?

/**
 * Copyright (c) 6/3/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

class CKitCatalogFilter extends CBitrixComponent
{
	var $IBLOCK_ID = 0;
	var $SKU_IBLOCK_ID = 0;
	var $SKU_PROPERTY_ID = 0;
	var $SECTION_ID = 0;
	var $SECTION = array();
	var $FILTER_NAME = '';
	var $sefURL = '';
	var $arTranslitParams = array();
	var $arSefParams = array();
	
	var $CHECKED = array();
	var $CHECKED_SKU = array();
	var $allCHECKED = array();
	var $arRequest = array();
	var $element_count = 0;
	
	public function onPrepareComponentParams($arParams)
	{
		global $APPLICATION;
		$arParams['CACHE_TIME'] = isset($arParams['CACHE_TIME']) ?$arParams['CACHE_TIME']: 36000000;
		$arParams['IBLOCK_ID'] = intval($arParams['IBLOCK_ID']);
		$arParams['SECTION_ID'] = intval($arParams['SECTION_ID']);
		
		$arParams['PRICE_CODE'] = is_array($arParams['PRICE_CODE'])? $arParams['PRICE_CODE']: array();
		foreach ($arParams['PRICE_CODE'] as $k=>$v)
		{
			if ($v==='')
				unset($arParams['PRICE_CODE'][$k]);
		}
		
		$arParams['CONVERT_CURRENCY'] = (isset($arParams['CONVERT_CURRENCY']) && 'Y' == $arParams['CONVERT_CURRENCY'] ? true : false);
		$arParams['CURRENCY_ID'] = trim(strval($arParams['CURRENCY_ID']));	
		$arParams['SAVE_IN_SESSION'] = $arParams['SAVE_IN_SESSION'] === 'Y';
		$arParams['HIDE_NOT_AVAILABLE'] = $arParams['HIDE_NOT_AVAILABLE'] === 'Y';
		$arParams['CACHE_GROUPS'] = $arParams['CACHE_GROUPS'] !== 'N';
		$arParams['INSTANT_RELOAD'] = $arParams['INSTANT_RELOAD'] === 'Y';
		$arParams['IS_SEF'] = $arParams['IS_SEF']== 'Y'? 'Y': 'N';
        $arParams['SEF_BASE_URL'] = isset($arParams['SEF_BASE_URL'])? $arParams['SEF_BASE_URL']: '/catalog/';
        $arParams['SECTION_PAGE_URL'] = isset($arParams['SECTION_PAGE_URL'])? $arParams['SECTION_PAGE_URL']: '#SECTION_ID#/';
		$arParams['PAGE_URL'] = strlen($arParams['PAGE_URL'])? $arParams['PAGE_URL']: '';
		$arParams['TOP_DEPTH_LEVEL'] = intval($arParams['TOP_DEPTH_LEVEL']);
		$arParams['INCLUDE_JQUERY'] = $arParams['INCLUDE_JQUERY'] === 'Y';
		$arParams['SORT'] = $arParams['SORT'] === 'Y';
		$arParams['SORT_ORDER'] = in_array(strtoupper($arParams['SORT_ORDER']), array('ASC', 'DESC')) ? strtoupper($arParams['SORT_ORDER']) : 'ASC';
		$arParams['USE_COMPOSITE_FILTER'] = $arParams['USE_COMPOSITE_FILTER'] === 'Y';
		$arParams['EXTENDED_MODE'] = $arParams['EXTENDED_MODE'] == 'Y';
		
		if ('' == $arParams['CURRENCY_ID'])
		{
			$arParams['CONVERT_CURRENCY'] = false;
		}
		elseif (!$arParams['CONVERT_CURRENCY'])
		{
			$arParams['CURRENCY_ID'] = '';
		}
		
		if(
			strlen($arParams['FILTER_NAME']) <= 0
			|| !preg_match('/^[A-Za-z_][A-Za-z01-9_]*$/', $arParams['FILTER_NAME'])
		)
		{
			$arParams['FILTER_NAME'] = 'arrFilter';
		}
		
		if($arParams['IS_SEF'] == 'Y'){
            $arVariables = array();
            
			$component = new CBitrixComponent();
			$component->arParams['IBLOCK_ID'] = $arParams['IBLOCK_ID'];
			$engine = new CComponentEngine($component);
			
            if (CModule::IncludeModule('iblock'))
            {
                    $engine->addGreedyPart('#SECTION_CODE_PATH#');
                    $engine->setResolveCallback(array('CIBlockFindTools', 'resolveComponentEngine'));
            }
            
            $componentPage = $engine->guessComponentPath(
                    $arParams['SEF_BASE_URL'],
                    array(
                        'section' => $arParams['SECTION_PAGE_URL'],
						'detail' => $arParams['DETAIL_PAGE_URL'],
                    ),
                    $arVariables
            );    

            if(isset($arVariables['SECTION_ID']))
                $arParams['SECTION_ID'] = $arVariables['SECTION_ID'];
            else if(isset($arVariables['SECTION_CODE'])) 
                $arParams['SECTION_CODE'] = $arVariables['SECTION_CODE'];
			
			if(!strlen($arParams['PAGE_URL']))
			{
				$arParams['PAGE_URL'] = $arParams['SEF_BASE_URL'];
				if(isset($arVariables['SECTION_CODE']) || isset($arVariables['SECTION_ID']))
				{
					$arParams['PAGE_URL'] .= CComponentEngine::MakePathFromTemplate($arParams['SECTION_PAGE_URL'], $arVariables);
				}
			}
        }
		
        if(intval($arParams['SECTION_ID']))
		{
			if(CModule::IncludeModule('iblock')) {
                $rsSections = CIBlockSection::GetList(array(), array('ID' => $arParams['SECTION_ID'], 'IBLOCK_ID' => $arParams['IBLOCK_ID']), false, array('NAME', 'ID', 'DEPTH_LEVEL', 'LEFT_MARGIN', 'RIGHT_MARGIN'));
                if($arSection = $rsSections->GetNext()){
					$this->SECTION = $arSection;
				}
            }
		}
		elseif(strlen($arParams['SECTION_CODE'])>0) 
		{
            if(CModule::IncludeModule('iblock')) {
                $rsSections = CIBlockSection::GetList(array(), array('CODE' => $arParams['SECTION_CODE'], 'IBLOCK_ID' => $arParams['IBLOCK_ID']), false, array('NAME', 'ID', 'DEPTH_LEVEL', 'LEFT_MARGIN', 'RIGHT_MARGIN'));
                if($arSection = $rsSections->GetNext()){
					$arParams['SECTION_ID'] = $arSection['ID'];
					$this->SECTION = $arSection;
				}
            }
        }

		return $arParams;
	}

	public function executeComponent()
	{
		$this->IBLOCK_ID = $this->arParams['IBLOCK_ID'];
		$this->SECTION_ID = $this->arParams['SECTION_ID'];
		$this->FILTER_NAME = $this->arParams['FILTER_NAME'];
		
		if(CModule::IncludeModule('catalog'))
		{
			$arCatalog = CCatalogSKU::GetInfoByProductIBlock($this->IBLOCK_ID);
			if (!empty($arCatalog) && is_array($arCatalog))
			{
				$this->SKU_IBLOCK_ID = $arCatalog['IBLOCK_ID'];
				$this->SKU_PROPERTY_ID = $arCatalog['SKU_PROPERTY_ID'];
			}
		}
		
		$this->arTranslitParams = array('replace_space' => '_','replace_other' => '_');
		
		$this->arSefParams = array(
			'marker' => 'filter',
			'separator' => '/',
			'from' => 'from',
			'to' => 'to',
			'or' => 'or'
		);
		
		$this->isBitronic = CModule::IncludeModule('yenisite.bitronic') || CModule::IncludeModule('yenisite.bitronicpro') || CModule::IncludeModule('yenisite.bitroniclite');
		$this->arResult['isBitronic'] = $this->isBitronic ? 'Y' : 'N';

		return parent::executeComponent();
	}

	public function getIBlockItems($IBLOCK_ID)
	{
		$items = array();
		$ids = array();
		
		foreach(CIBlockSectionPropertyLink::GetArray($IBLOCK_ID, $this->SECTION_ID) as $PID => $arLink)
		{
			if($arLink['SMART_FILTER'] !== 'Y')
				continue;

			$rsProperty = CIBlockProperty::GetByID($PID);
			$arProperty = $rsProperty->Fetch();
			if($arProperty)
			{
				$ids[$arProperty['ID']] = $arProperty['ID'];
				
				$items[$arProperty['ID']] = array(
					'ID' => $arProperty['ID'],
					'IBLOCK_ID' => $arProperty['IBLOCK_ID'],
					'CODE' => $arProperty['CODE'],
					'CODE_ALT' => ToLower(CUtil::translit($arProperty['CODE'], 'ru', $this->arTranslitParams)),
					'NAME' => $arProperty['NAME'],
					'PROPERTY_TYPE' => $arProperty['PROPERTY_TYPE'],
					'USER_TYPE' => $arProperty['USER_TYPE'],
					'USER_TYPE_SETTINGS' => $arProperty['USER_TYPE_SETTINGS'],
					'HINT' => $arProperty['HINT'],
					'SORT' => $arProperty['SORT'],
					'VALUES' => array(),
				);
			}
		}
		
		if(count($ids))
		{
			$arSettings = array();
			
			$rsPropertySettings = Kit\Filter\PropertySettingsTable::getList(
				array(
					'filter' => array('PROPERTY_ID' => $ids)
				)
			);
			
			while($arPropertySettings = $rsPropertySettings->Fetch())
			{
				if(strlen($arPropertySettings['HINT']) && $arPropertySettings['HINT_TYPE'] == 'text')
				{
					$arPropertySettings['HINT'] = TxtToHtml($arPropertySettings['HINT']);
				}
				
				$arPropertySettings['LIST_MULTI'] = $arPropertySettings['LIST_MULTI'] == 'Y';
				
				$arSettings[$arPropertySettings['PROPERTY_ID']] = $arPropertySettings;
			}
			
			foreach($items as &$item)
			{
				if(isset($arSettings[$item['ID']]))
				{
					$arPropertySettings = $arSettings[$item['ID']];
					
					if(strlen($arPropertySettings['HINT']))
						$item['HINT'] = $arPropertySettings['HINT'];
					
					unset($arPropertySettings['HINT']);
					
					if(strlen($arPropertySettings['VIEW'])){
						if($item['PROPERTY_TYPE'] == 'N')
						{
							if(!in_array($arPropertySettings['VIEW'], array('SLIDER', 'SELECT', 'LIST', 'CHECKBOX', 'RADIO', 'LINK')))
								$arPropertySettings['VIEW'] = 'SLIDER';
						}
						else
						{
							if(!in_array($arPropertySettings['VIEW'], array('SELECT', 'LIST', 'CHECKBOX', 'RADIO', 'LINK', 'TEXT')))
								$arPropertySettings['VIEW'] = 'CHECKBOX';
						}
					}
					else
					{
						if($item['PROPERTY_TYPE'] == 'N')
						{
							$arPropertySettings['VIEW'] = 'SLIDER';
						}
						else
						{
							$arPropertySettings['VIEW'] = 'CHECKBOX';
						}
					}
					
					if(!in_array($item['PROPERTY_TYPE'], array('L', 'G', 'E', 'G:SectionAuto', 'E:SKU', 'E:EList', 'S:ElementXmlID', 'E:EAutocomplete')) && $arPropertySettings['SORT'] == 'SORT')
						$arPropertySettings['SORT'] == 'SORT';
					
					$item['SETTINGS'] = $arPropertySettings;
				}
				else
				{
					if($item['PROPERTY_TYPE'] == 'N')
						$item['SETTINGS'] = array('VIEW' => 'SLIDER');
					else
						$item['SETTINGS'] = array('VIEW' => 'CHECKBOX');
				}
				
				if($item['PROPERTY_TYPE'] == 'N' && $item['SETTINGS']['VIEW'] == 'SLIDER')
				{
					$item['VALUES'] = array(
						'MIN' => array(
							'CONTROL_ID' => htmlspecialcharsbx($this->FILTER_NAME.'_'.$item['ID'].'_MIN'),
							'CONTROL_NAME' => htmlspecialcharsbx($this->FILTER_NAME.'_'.$item['ID'].'_MIN'),
						),
						'MAX' => array(
							'CONTROL_ID' => htmlspecialcharsbx($this->FILTER_NAME.'_'.$item['ID'].'_MAX'),
							'CONTROL_NAME' => htmlspecialcharsbx($this->FILTER_NAME.'_'.$item['ID'].'_MAX'),
						),
					);
				}
			}
			unset($item);
		}
		
		return $items;
	}

	public function getPriceItems()
	{
		$items = array();
		if (!empty($this->arParams['PRICE_CODE']))
		{
			if(CModule::IncludeModule('catalog'))
			{
				$units = '';
				if(strlen($this->arParams['CURRENCY_ID']))
				{
					$units = CKitFilter::getCurrencyFullName($this->arParams['CURRENCY_ID']);
				}
				
				$rsPrice = CCatalogGroup::GetList();
				while($arPrice = $rsPrice->Fetch())
				{
					if(
						($arPrice['CAN_ACCESS'] == 'Y' || $arPrice['CAN_BUY'] == 'Y')
						&& in_array($arPrice['NAME'], $this->arParams['PRICE_CODE'])
					)
					{
						$items[$arPrice['NAME']] = array(
							'ID' => $arPrice['ID'],
							'CODE' => $arPrice['NAME'],
							'CODE_ALT' => ToLower(CUtil::translit($arPrice['NAME'], 'ru', $this->arTranslitParams)),
							'NAME' => $arPrice['NAME_LANG'],
							'PRICE' => true,
							'SORT' => $arPrice['SORT'],
							'SETTINGS' => array(
								'VIEW' => 'SLIDER',
								'SLIDER_UNITS' => $units
							),
							'VALUES' => array(
								'MIN' => array(
									'CONTROL_ID' => htmlspecialcharsbx($this->FILTER_NAME.'_P'.$arPrice['ID'].'_MIN'),
									'CONTROL_NAME' => htmlspecialcharsbx($this->FILTER_NAME.'_P'.$arPrice['ID'].'_MIN'),
								),
								'MAX' => array(
									'CONTROL_ID' => htmlspecialcharsbx($this->FILTER_NAME.'_P'.$arPrice['ID'].'_MAX'),
									'CONTROL_NAME' => htmlspecialcharsbx($this->FILTER_NAME.'_P'.$arPrice['ID'].'_MAX'),
								),
							),
						);
					}
				}
				
				if(count($items) == 1){
					foreach($items as &$item)
						$item['CODE_ALT'] = 'price';
					unset($item);
				}
			}
		}
		return $items;
	}
	
	public function getFieldsItems()
	{
		$items = array();
		
		foreach(array_reverse($this->arParams['FIELDS']) as $field)
		{
			switch($field)
			{
				case 'STORES':
					$items = array(
						'STORES' => array(
							'ID' => 'STORES',
							'IBLOCK_ID' => $this->IBLOCK_ID,
							'CODE' => 'STORES',
							'CODE_ALT' => 'stores',
							'NAME' => GetMessage('KIT_CMP_FILTER_STORES_NAME'),
							'PROPERTY_TYPE' => 'STORES',
							'HINT' => '',
							'SORT' => $this->arParams['SORT'] ? $this->arParams['SORT_STORES'] : '',
							'SETTINGS' => array(
								'VIEW' => 'CHECKBOX'
							),
							'VALUES' => array(),
						)
					) + $items;
					break;
				case 'SECTIONS':
					$items = array(
						'SECTIONS' => array(
							'ID' => 'SECTIONS',
							'IBLOCK_ID' => $this->IBLOCK_ID,
							'CODE' => 'SECTIONS',
							'CODE_ALT' => 'sections',
							'NAME' => GetMessage('KIT_CMP_FILTER_SECTIONS_NAME'),
							'PROPERTY_TYPE' => 'SECTIONS',
							'HINT' => '',
							'SORT' => $this->arParams['SORT'] ? $this->arParams['SORT_SECTIONS'] : '',
							'SETTINGS' => array(
								'VIEW' => 'CHECKBOX'
							),
							'VALUES' => array(),
						)
					) + $items;
					break;
				case 'AVAILABLE':
					if($this->arParams['HIDE_NOT_AVAILABLE'])break;
					$items = array(
						'AVAILABLE' => array(
							'ID' => 'AVAILABLE',
							'IBLOCK_ID' => $this->IBLOCK_ID,
							'CODE' => 'AVAILABLE',
							'CODE_ALT' => 'available',
							'NAME' => GetMessage('KIT_CMP_FILTER_AVAILABLE_NAME'),
							'PROPERTY_TYPE' => 'AVAILABLE',
							'HINT' => '',
							'SORT' => $this->arParams['SORT'] ? $this->arParams['SORT_AVAILABLE'] : '',
							'SETTINGS' => array(
								'VIEW' => 'CHECKBOX'
							),
							'VALUES' => array(),
						)
					) + $items;
					break;
				case 'QUANTITY':
					$items = array(
						'QUANTITY' => array(
							'ID' => 'QUANTITY',
							'IBLOCK_ID' => $this->IBLOCK_ID,
							'CODE' => 'QUANTITY',
							'CODE_ALT' => 'quantity',
							'NAME' => GetMessage('KIT_CMP_FILTER_QUANTITY_NAME'),
							'PROPERTY_TYPE' => 'N',
							'HINT' => '',
							'SORT' => $this->arParams['SORT'] ? $this->arParams['SORT_QUANTITY'] : '',
							'SETTINGS' => array(
								'VIEW' => 'SLIDER'
							),
							'VALUES' => array(
								'MIN' => array(
									'CONTROL_ID' => htmlspecialcharsbx($this->FILTER_NAME.'_QUANTITY_MIN'),
									'CONTROL_NAME' => htmlspecialcharsbx($this->FILTER_NAME.'_QUANTITY_MIN'),
								),
								'MAX' => array(
									'CONTROL_ID' => htmlspecialcharsbx($this->FILTER_NAME.'_QUANTITY_MAX'),
									'CONTROL_NAME' => htmlspecialcharsbx($this->FILTER_NAME.'_QUANTITY_MAX'),
								),
							)
						)
					) + $items;
					break;
			}
		}
		
		return $items;
	}
	
	public function getResultItems()
	{
		$items = $this->getIBlockItems($this->IBLOCK_ID);
		$items = $this->getFieldsItems() + $items;
		
		foreach($items as &$arItem){
			if(in_array($arItem['CODE'], $this->arParams['CLOSED_PROPERTY_CODE']) && strlen($arItem['CODE']))
				$arItem['CLOSED'] = true;
			else
				$arItem['CLOSED'] = false;
				
			$arItem['SKU'] = false;
		}
		unset($arItem);
		
		$this->arResult['PROPERTY_COUNT'] = count($items);

		if (!empty($this->arParams['PRICE_CODE']))
		{
			foreach($this->getPriceItems() as $PID => $arItem)
			{
				$items = array($PID => $arItem) + $items;
			}
		}
		
		return $items;
	}
	
	public function getResultSkuItems()
	{
		$items = array();
		if($this->SKU_IBLOCK_ID)
		{
			foreach($this->getIBlockItems($this->SKU_IBLOCK_ID) as $PID => $arItem)
			{
				if(in_array($arItem['CODE'], $this->arParams['CLOSED_OFFERS_PROPERTY_CODE']) && strlen($arItem['CODE']))
					$arItem['CLOSED'] = true;
				else
					$arItem['CLOSED'] = false;
					
				$arItem['SKU'] = true;
				$items[$PID] = $arItem;
			}
			$this->arResult['PROPERTY_COUNT'] += count($items);
			$this->arResult['SKU_PROPERTY_COUNT'] = count($items);
		}
		
		return $items;
	}
	
	function getElement(&$arItems, &$el)
	{
		$result = array();
			
		foreach($arItems as $PID => &$arItem)
		{
			$result[$PID] = $el[$PID];
			
			//delete empty value
			if(is_array($result[$PID]))
			{
				if(!count($result[$PID]))
					unset($result[$PID]);
			}
			elseif(empty($result[$PID]))
				unset($result[$PID]);
		}
		return $result;
	}
	
	public function hideNotAvailableElements()
	{
		foreach($this->arResult['ELEMENTS'] as $id => &$arElement){
			if($arElement['AVAILABLE'] != 'Y')
			{
				if(isset($this->arResult['ELEMENTS_SKU'][$id]))
				{
					$available = false;
					foreach($this->arResult['ELEMENTS_SKU'][$id] as &$arSku)
					{
						if($arSku['AVAILABLE'] == 'Y')
						{
							$available = true;
							break;
						}
					}
					unset($arSku);
					
					if(!$available)
					{
						unset($this->arResult['ELEMENTS'][$id]);
						unset($this->arResult['ELEMENTS_SKU'][$id]);
					}
				}
				else
					unset($this->arResult['ELEMENTS'][$id]);
			}
		}
		unset($arElement);
	}
	
	public function fillItemValues(&$resultItem, $arProperty, $cnt = false)
	{
		static $cacheL = array();
		static $cacheE = array();
		static $cacheG = array();
		static $cacheU = array();
		static $cacheStore = array();

		if(is_array($arProperty))
		{
			if(isset($arProperty['PRICE']))
			{
				return null;
			}
			$key = $arProperty['VALUE'];
			$PROPERTY_TYPE = $arProperty['PROPERTY_TYPE'];
			$PROPERTY_USER_TYPE = $arProperty['USER_TYPE'];
			$PROPERTY_ID = $arProperty['ID'];
		}
		else
		{
			$key = $arProperty;
			$PROPERTY_TYPE = $resultItem['PROPERTY_TYPE'];
			$PROPERTY_USER_TYPE = $resultItem['USER_TYPE'];
			$PROPERTY_ID = $resultItem['ID'];
			$arProperty = $resultItem;
		}
		
		$VIEW = $arProperty['SETTINGS']['VIEW'];

		if($PROPERTY_TYPE == 'F')
		{
			return null;
		}
		elseif($PROPERTY_TYPE == 'N' && $VIEW == 'SLIDER')
		{
			if(!isset($resultItem['VALUES']['MIN']) || !array_key_exists('VALUE', $resultItem['VALUES']['MIN']) || doubleval($resultItem['VALUES']['MIN']['VALUE']) > doubleval($key))
				$resultItem['VALUES']['MIN']['VALUE'] = preg_replace('/\\.0+\$/', '', $key);
			
			if(!isset($resultItem['VALUES']['MAX']) || !array_key_exists('VALUE', $resultItem['VALUES']['MAX']) || doubleval($resultItem['VALUES']['MAX']['VALUE']) < doubleval($key))
				$resultItem['VALUES']['MAX']['VALUE'] = preg_replace('/\\.0+\$/', '', $key);

			return null;
		}
		elseif($PROPERTY_TYPE == 'E' && $key <= 0)
		{
			return null;
		}
		elseif($PROPERTY_TYPE == 'G' && $key <= 0)
		{
			return null;
		}
		elseif(strlen($key) <= 0)
		{
			return null;
		}
		
		$arUserType = array();
		if($PROPERTY_USER_TYPE != '')
		{
			$arUserType = CIBlockProperty::GetUserType($PROPERTY_USER_TYPE);
			if(array_key_exists('GetPublicViewHTML', $arUserType))
				$PROPERTY_TYPE = 'U';
		}

		$value_id = 0;
		$name_alt = '';
		
		switch($PROPERTY_TYPE)
		{
		case 'L':
			if(!isset($cacheL[$PROPERTY_ID]))
			{
				$cacheL[$PROPERTY_ID] = array();
				$rsEnum = CIBlockPropertyEnum::GetList(array('SORT'=>'ASC', 'VALUE'=>'ASC'), array('PROPERTY_ID' => $PROPERTY_ID));
				while ($enum = $rsEnum->Fetch())
					$cacheL[$PROPERTY_ID][$enum['ID']] = $enum;
			}

			if (!array_key_exists($key,  $cacheL[$PROPERTY_ID]))
				return null;

			$sort = $cacheL[$PROPERTY_ID][$key]['SORT'];
			$value = $cacheL[$PROPERTY_ID][$key]['VALUE'];
			$value_id = $cacheL[$PROPERTY_ID][$key]['ID'];
			break;
		case 'STORES':
			if(!isset($cacheStore[$key]))
			{
				$cacheStore = array();
				$rsStore = CCatalogStore::GetList(array('SORT'=>'ASC', 'VALUE'=>'ASC'), array('ACTIVE' => 'Y'), false, false, array('ID', 'TITLE', 'SORT'));
				while ($arStore = $rsStore->Fetch())
					$cacheStore[$arStore['ID']] = $arStore;
			}
			
			if (!array_key_exists($key,  $cacheStore))
				return null;
				
			$sort = $cacheStore[$key]['SORT'];
			$value = $cacheStore[$key]['TITLE'];
			$value_id = $cacheStore[$key]['ID'];
			break;
		case 'SECTIONS':
			if(!isset($cacheG[$key]))
			{
				$arLinkFilter = array (
					'ID' => $key,
					'GLOBAL_ACTIVE' => 'Y'
				);
				$rsLink = CIBlockSection::GetList(array(), $arLinkFilter, false, array('ID','IBLOCK_ID','NAME','LEFT_MARGIN','DEPTH_LEVEL'));
				$cacheG[$key] = $rsLink->Fetch();
			}
				
			$value = $cacheG[$key]['NAME'];
			$sort = $cacheG[$key]['LEFT_MARGIN'];
			$value_id = $cacheG[$key]['ID'];
			break;
		case 'AVAILABLE':
			$value = GetMessage('KIT_CMP_FILTER_AVAILABLE_VALUE_'.$key);
			$sort = $value;
			$value_id = $key;
			$name_alt = $key == 'Y' ? 'yes' : 'no';
			break;
		case 'E':
			if(!isset($cacheE[$key]))
			{
				$arLinkFilter = array (
					'ID' => $key,
					'ACTIVE' => 'Y',
					'ACTIVE_DATE' => 'Y',
					'CHECK_PERMISSIONS' => 'Y',
				);
				$rsLink = CIBlockElement::GetList(array(), $arLinkFilter, false, false, array('ID','IBLOCK_ID','NAME','SORT'));
				$cacheE[$key] = $rsLink->Fetch();
			}
				
			$value = $cacheE[$key]['NAME'];
			$sort = $cacheE[$key]['SORT'];
			$value_id = $cacheE[$key]['ID'];
			break;
		case 'G':
			if(!isset($cacheG[$key]))
			{
				$arLinkFilter = array (
					'ID' => $key,
					'GLOBAL_ACTIVE' => 'Y',
					'CHECK_PERMISSIONS' => 'Y',
				);
				$rsLink = CIBlockSection::GetList(array(), $arLinkFilter, false, array('ID','IBLOCK_ID','NAME','LEFT_MARGIN','DEPTH_LEVEL'));
				$cacheG[$key] = $rsLink->Fetch();
			}
				
			$value = str_repeat('.', $cacheG['DEPTH_LEVEL']).$cacheG[$key]['NAME'];
			$sort = $cacheG[$key]['LEFT_MARGIN'];
			$value_id = $cacheG[$key]['ID'];
			break;
		case 'U':
			if(!isset($cacheU[$PROPERTY_ID]))
				$cacheU[$PROPERTY_ID] = array();

			if(!isset($cacheU[$PROPERTY_ID][$key]))
			{
				$cacheU[$PROPERTY_ID][$key] = call_user_func_array(
					$arUserType['GetPublicViewHTML'],
					array(
						$arProperty,
						array('VALUE' => $key),
						array('MODE' => 'SIMPLE_TEXT'),
					)
				);
			}

			$value = $cacheU[$PROPERTY_ID][$key];
			$value_id = $key;
			$sort = 0;
			break;
		case 'N':
			$value = floatval($key);
			$sort = floatval($key);
			break;
		default:
			$value = $key;
			$sort = 0;
			break;
		}
		
		if(strlen($value))
		{
			$value = htmlspecialcharsex($value);
			$sort = intval($sort);
			$value_id = intval($value_id);
			if(!strlen($name_alt))
				$name_alt = CUtil::translit($value, 'ru', $this->arTranslitParams);
			
			$resultItem['VALUES'][$key] = array(
				'CONTROL_ID' => htmlspecialcharsbx($this->FILTER_NAME.'_'.$PROPERTY_ID.'_'.abs(crc32($key))),
				'CONTROL_NAME' => htmlspecialcharsbx($this->FILTER_NAME.'_'.$PROPERTY_ID.'_'.abs(crc32($key))),
				'CONTROL_NAME_ALT' => ToLower($name_alt),
				'HTML_VALUE' => 'Y',
				'HTML_VALUE_ALT' => ToLower($name_alt),
				'VALUE' => $value,
				'SORT' => $sort,
				'UPPER' => ToUpper($value),
				'CNT' => $cnt
			);
			
			if($value_id){
				$resultItem['VALUES'][$key]['VALUE_ID'] = $value_id;
			}
		}
		
		return $key;
	}
	
	public function fillItemPrices(&$resultItem, $price)
	{
		if(strlen($price))
		{
			if(!isset($resultItem['VALUES']['MIN']) || !array_key_exists('VALUE', $resultItem['VALUES']['MIN']) || doubleval($resultItem['VALUES']['MIN']['VALUE']) > doubleval($price))
				$resultItem['VALUES']['MIN']['VALUE'] = $price;

			if(!isset($resultItem['VALUES']['MAX']) || !array_key_exists('VALUE', $resultItem['VALUES']['MAX']) || doubleval($resultItem['VALUES']['MAX']['VALUE']) < doubleval($price))
				$resultItem['VALUES']['MAX']['VALUE'] = $price;
		}
	}
	
	function fillVariants(&$arVariants, &$arItems)
	{
		foreach($this->arResult['ELEMENTS'] as $id => &$el)
		{
			foreach($arItems as $PID => &$arItem)
			{
				if(!isset($el[$PID]))continue;
				
				$key = $el[$PID];
				
				if(!is_array($arVariants[$PID]))
					$arVariants[$PID] = array();
				
				if(!is_array($key))
					$key = array($key);
					
				foreach($key as $k)
				{						
					if(!isset($k))continue;
					
					if(!isset($arVariants[$PID][$k]))
						$arVariants[$PID][$k] = array('ID' => array(), 'CNT' => 0);
					
					if(!$arVariants[$PID][$k]['ID'][$id]){
						$arVariants[$PID][$k]['ID'][$id] = true;
						$arVariants[$PID][$k]['CNT']++;
					}
				}
			}
		}
		unset($el);
	}
	
	function fillVariantsSku(&$arVariants, &$arItems)
	{
		foreach($this->arResult['ELEMENTS_SKU'] as $id => &$arEl)
		{
			foreach($arEl as &$el)
			{
				foreach($arItems as $PID => &$arItem)
				{
					if(!isset($el[$PID]))continue;
					
					$key = $el[$PID];
					
					if(!is_array($arVariants[$PID]))
						$arVariants[$PID] = array();
						
					if(!is_array($key))
						$key = array($key);
						
					foreach($key as $k)
					{						
						if(!isset($k))continue;
						
						if(!isset($arVariants[$PID][$k]))
							$arVariants[$PID][$k] = array('ID' => array(), 'CNT' => 0);
						
						if(!$arVariants[$PID][$k]['ID'][$id]){
							$arVariants[$PID][$k]['ID'][$id] = true;
							$arVariants[$PID][$k]['CNT']++;
						}
					}
				}
			}
			unset($el);
		}
		unset($arEl);
	}
	
	public function isStoreCheck()
	{
		if(count($this->arParams['STORES_ID']) > 0)
		{
			$rsStores = CCatalogStore::GetList(
				array(),
				array('ACTIVE' => 'Y'),
				false,
				false,
				array('ID', 'TITLE')
			);
			
			while ($arStore = $rsStores->Fetch())
			{
				if(false == array_search($arStore['ID'], $this->arParams['STORES_ID']))
					return true;
			}
		}
		
		return false;
	}
	
	public function getRequest()
	{
		$arRequest = array();
		
		if(isset($_REQUEST['ajax']) && $_REQUEST['ajax'] === 'y')
			$arRequest = &$_REQUEST;
		elseif(isset($_GET['set_filter']))
			$arRequest = &$_GET;
		elseif($arParams['SAVE_IN_SESSION'] && isset($_SESSION[$FILTER_NAME][$this->IBLOCK_ID][$this->SECTION_ID]))
			$arRequest = $_SESSION[$FILTER_NAME][$this->IBLOCK_ID][$this->SECTION_ID];
		else
			$arRequest = array();
		
		foreach($this->arResult['ITEMS'] as $PID => &$arItem)
		{
			if(isset($_REQUEST[$arItem['CODE_ALT']])){
				$arRequest[$arItem['CODE_ALT']] = $_REQUEST[$arItem['CODE_ALT']];
			}
			else
			{
				if(isset($_REQUEST[$arItem['CODE_ALT'].'_from'])){
					$arRequest[$arItem['CODE_ALT'].'_from'] = $_REQUEST[$arItem['CODE_ALT'].'_from'];
				}
				if(isset($_REQUEST[$arItem['CODE_ALT'].'_to'])){
					$arRequest[$arItem['CODE_ALT'].'_to'] = $_REQUEST[$arItem['CODE_ALT'].'_to'];
				}
			}
		}
		unset($arItem);
		
		if($this->arResult['IS_SEF'] && !count($arRequest))
			$arRequest = $this->getRequestBySef();
		else
			$arRequest = $this->getRequestBySimple($arRequest);

		if(isset($_REQUEST['del_filter']))
			$arRequest = array();
			
		$this->setDefaultValues($arRequest);

		return $arRequest;
	}
	
	protected function getRequestBySef()
	{
		$_SEF = array();
		if(CModule::IncludeModule('kit.filter'))
		{
			$requestURL = CKitFilter::GetCurPage(true);
			$arUrlParts = explode('/', $requestURL);
			if(in_array($this->arSefParams['marker'], $arUrlParts)){
				$requestURL = '/';
				$arItems = array();
				foreach($this->arResult['ITEMS'] as $PID => $arItem)
				{
					$arItems[$arItem['CODE_ALT']] = $PID;
				}
				
				$set_filter = false;
				foreach($arUrlParts as $part)
				{
					if($set_filter && strlen(trim($part)))
					{
						$arParamParts = explode('-', $part);
						$paramName = $arParamParts[0];
						if(strlen($paramName) && $paramName != 'index.php')
						{
							if(isset($arItems[$paramName]))
							{
								unset($arParamParts[0]);
								$arParamValues = array();
								$minValue = false;
								$maxValue = false;
								$bFrom = false;
								$bTo = false;
								$bOr = true;
								$bSet = false;
								
								foreach($arParamParts as $value)
								{
									if(strlen($value))
									{
										if($value == 'from'){
											$bFrom = true;
											continue;
										}
											
										if($value == 'to'){
											$bTo = true;
											continue;
										}
										
										if(is_numeric($value))
										{
											if($bTo){
												$maxValue = doubleval($value);
												continue;
											}
											
											if($bFrom && !$bTo){
												$minValue = doubleval($value);
												continue;
											}
										}
										
										if($value != 'or'){
											if($bOr){
												$bOr = false;
												$arParamValues[$value] = $value;
												continue;
											}
										}
										elseif(!$bOr){
											$bOr = true;
											continue;
										}
										
										if(!defined('KIT_FILTER_ERROR_404'))
											@define('KIT_FILTER_ERROR_404', 'Y');
									}
								}
								
								$arItem = $this->arResult['ITEMS'][$arItems[$paramName]];
								
								if($arItem['SETTINGS']['VIEW'] == 'SLIDER')
								{
									if(!defined('KIT_FILTER_ERROR_404') && !empty($arParamValues))
										@define('KIT_FILTER_ERROR_404', 'Y');
									
									if($minValue !== false){
										$_SEF[$arItem['CODE_ALT'].'_from'] = $minValue;
										$bSet = true;
									}

									if($maxValue !== false){
										$_SEF[$arItem['CODE_ALT'].'_to'] = $maxValue;
										$bSet = true;
									}
								}
								elseif(!empty($arParamValues))
								{
									$_SEF[$arItem['CODE_ALT']] = array();
									foreach($arItem['VALUES'] as $key => $ar)
									{
										if(in_array($ar['HTML_VALUE_ALT'], $arParamValues)){
											unset($arParamValues[$ar['HTML_VALUE_ALT']]);
											$_SEF[$arItem['CODE_ALT']][$ar['HTML_VALUE_ALT']] = $ar['HTML_VALUE_ALT'];
										}
									}
									
									if(!count($_SEF[$arItem['CODE_ALT']]))
									{
										unset($_SEF[$arItem['CODE_ALT']]);
									}
									else
									{
										if(empty($arParamValues))
											$bSet = true;
									}
								}
								
								if(!defined('KIT_FILTER_ERROR_404') && !$bSet)
									@define('KIT_FILTER_ERROR_404', 'Y');
							}
							elseif(!defined('KIT_FILTER_ERROR_404'))
							{
								@define('KIT_FILTER_ERROR_404', 'Y');
							}
						}
					}
					elseif($part == $this->arSefParams['marker'])
						$set_filter = true;
				}
			}
		}

		return $_SEF;
	}
	
	protected function getRequestBySimple($arRequest)
	{
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
	
		foreach($this->arResult['ITEMS'] as $PID => &$arItem)
		{
			if(isset($check[$arItem['CODE_ALT']]) && $arItem['SETTINGS']['VIEW'] !== 'SLIDER'){
				$arRequest[$arItem['CODE_ALT']] = $check[$arItem['CODE_ALT']];
			}
			elseif(isset($arRequest[$arItem['CODE_ALT']])){
				if(!is_array($arRequest[$arItem['CODE_ALT']]))
					$arRequest[$arItem['CODE_ALT']] = array($arRequest[$arItem['CODE_ALT']]);
				
				$arValues = array();
				
				foreach($arRequest[$arItem['CODE_ALT']] as $val)
					$arValues[$val] = $val;
					
				$arRequest[$arItem['CODE_ALT']] = $arValues;
			}
		}
		unset($arItem);

		return $arRequest;
	}
	
	public function isSetFilter()
	{
		$arRequest = $this->getRequest();
		
		$CHECKED = array();
		$CHECKED_SKU = array();
		$allCHECKED = array();
		
		$this->element_count = $this->arResult['ELEMENTS_COUNT'];
		$this->arResult['SET_FILTER'] = false;

		if(count($arRequest))
		{
			foreach($this->arResult['ITEMS'] as $PID => &$arItem)
			{
				$pos = 0;
				foreach($arItem['VALUES'] as $key => &$ar)
				{
					$pos++;
					if(isset($arRequest[$arItem['CODE_ALT']]) || isset($arRequest[$arItem['CODE_ALT'].'_from']) || isset($arRequest[$arItem['CODE_ALT'].'_to']))
					{
						if($arItem['SETTINGS']['VIEW'] == 'SLIDER')
						{
							$value = false;

							if($key == 'MIN' && isset($arRequest[$arItem['CODE_ALT'].'_from']))
							{
								$ar['HTML_VALUE'] = htmlspecialcharsbx($arRequest[$arItem['CODE_ALT'].'_from']);
								$value = doubleval($arRequest[$arItem['CODE_ALT'].'_from']);
								if($value > $ar['VALUE'])
									$CHECKED[$PID][$key] = $value;
							}
							elseif($key == 'MAX' && isset($arRequest[$arItem['CODE_ALT'].'_to']))
							{
								$ar['HTML_VALUE'] = htmlspecialcharsbx($arRequest[$arItem['CODE_ALT'].'_to']);
								$value = doubleval($arRequest[$arItem['CODE_ALT'].'_to']);
								if($value < $ar['VALUE'])
									$CHECKED[$PID][$key] = $value;
							}
						}
						elseif(isset($arRequest[$arItem['CODE_ALT']][$ar['HTML_VALUE_ALT']]))
						{
							$ar['CHECKED'] = true;
							$CHECKED[$PID][$key] = $key;
							$arItem['LAST_CHECKED_POS'] = $pos;
							$arItem['CHECKED'] = true;
						}
						
						$arItem['CLOSED'] = false;
					}
					elseif(isset($arRequest[$ar['CONTROL_NAME']]))
					{
						if($arItem['PROPERTY_TYPE'] == 'N' || isset($arItem['PRICE']))
						{
							$ar['HTML_VALUE'] = htmlspecialcharsbx($arRequest[$ar['CONTROL_NAME']]);
							if(doubleval($arRequest[$ar['CONTROL_NAME']]))
							{
								if($key == 'MIN')
								{
									if($arRequest[$ar['CONTROL_NAME']] > $ar['VALUE'])
										$CHECKED[$PID]['MIN'] = $arRequest[$ar['CONTROL_NAME']];
								}
								else
								{
									if($arRequest[$ar['CONTROL_NAME']] < $ar['VALUE'])
										$CHECKED[$PID]['MAX'] = $arRequest[$ar['CONTROL_NAME']];
								}
							}
						}
						elseif($arRequest[$ar['CONTROL_NAME']] == $ar['HTML_VALUE'])
						{
							$ar['CHECKED'] = true;
							$CHECKED[$PID][$key] = $key;
							$arItem['LAST_CHECKED_POS'] = $pos;
							$arItem['CHECKED'] = true;
						}
						
						$arItem['CLOSED'] = false;
					}
				}
				unset($ar);

				if(isset($CHECKED[$PID]))	
				{
					$allCHECKED[$PID] = $CHECKED[$PID];
						
					if($arItem['SKU']){
						$CHECKED_SKU[$PID] = $CHECKED[$PID];
						unset($CHECKED[$PID]);
					}
					
					if($arItem['PROPERTY_TYPE'] == 'STORES'){
						$CHECKED_SKU[$PID] = $CHECKED[$PID];
					}
					
					if(isset($arItem['PRICE']) && is_array($CHECKED[$PID])){
						$CHECKED_SKU[$PID] = $CHECKED[$PID];
					}
				}
			}
			unset($arItem);
		}
		
		$this->CHECKED = $CHECKED;
		$this->CHECKED_SKU = $CHECKED_SKU;
		$this->allCHECKED = $allCHECKED;
		$this->arRequest = $arRequest;
		$this->arResult['REQUEST'] = $arRequest;

		if($arRequest && (count($CHECKED) || count($CHECKED_SKU)))
		{
			if($this->isBitronic)
			{
				if(isset($CHECKED['AVAILABLE']) && (count($CHECKED) + count($CHECKED_SKU)) == 1)
				{
					if(count($CHECKED['AVAILABLE']) == 1)
					{
						$this->arResult['SET_FILTER'] = false;
						return true;
					}
				}
			}
			
			$this->arResult['SET_FILTER'] = true;
			return true;
		}

		return false;
	}
	
	public function checkElements()
	{
		$CHECKED = $this->CHECKED;
		$CHECKED_SKU = $this->CHECKED_SKU;
		$allCHECKED = $this->allCHECKED;

		$arVariants = array();
		$filterIDs = array();
		
		$this->element_count = 0;

		foreach($this->arResult['ELEMENTS'] as &$res)
		{
			if(isset($CHECKED['STORES']))
			{
				$bAvailable = isset($res['STORES_AVAILABLE']) && isset($res['AVAILABLE']);
				$bQuantity = isset($res['STORES_QUANTITY']) && isset($res['QUANTITY']);
				
				if($bAvailable || $bQuantity)
				{
					if($bAvailable)$res['AVAILABLE'] = 'N';
					if($bQuantity)$res['QUANTITY'] = 0;
					
					foreach($CHECKED['STORES'] as $store_id)
					{
						if($res['STORES_AVAILABLE'][$store_id] && $bAvailable){
							$res['AVAILABLE'] = 'Y';
						}
						
						if($res['STORES_QUANTITY'][$store_id] > $res['QUANTITY'] && $bQuantity){
							$res['QUANTITY'] = $res['STORES_QUANTITY'][$store_id];
						}
					}
				}
			}
			
			$id = $res['ID'];
			$hash = array();
			if(is_array($this->arResult['ELEMENTS_SKU'][$id]))
			{
				$this->checkElement($CHECKED, $res, $hash);
				
				foreach($this->arResult['ELEMENTS_SKU'][$id] as $k => &$resSku)
				{
					$skuID = $resSku['ID'];
					
					if(isset($CHECKED['STORES']))
					{
						$bAvailable = isset($resSku['STORES_AVAILABLE']) && isset($resSku['AVAILABLE']);
						
						if($res['AVAILABLE'] == 'Y')
						{
							$bAvailable = false;
							$resSku['AVAILABLE'] = 'Y';
						}
						
						$bQuantity = isset($resSku['STORES_QUANTITY']) && isset($resSku['QUANTITY']);
						
						if($bAvailable || $bQuantity)
						{
							if($bAvailable)$resSku['AVAILABLE'] = 'N';
							if($bQuantity)$resSku['QUANTITY'] = 0;
							
							foreach($CHECKED['STORES'] as $store_id)
							{
								if($resSku['STORES_AVAILABLE'][$store_id] && $bAvailable){
									$resSku['AVAILABLE'] = 'Y';
								}
								
								if($resSku['STORES_QUANTITY'][$store_id] > $resSku['QUANTITY'] && $bQuantity){
									$resSku['QUANTITY'] = $resSku['STORES_QUANTITY'][$store_id];
								}
							}
						}
					}
			
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

					foreach ($this->arResult['ITEMS'] as $PID => &$arItem)
					{
						if(!isset($resSku[$PID]))continue;
						
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
				if($this->checkElement($allCHECKED, $res, $hash)){
					$filterIDs[$id] = $id;
				}

				foreach ($this->arResult['ITEMS'] as $PID => &$arItem)
				{
					if(!isset($res[$PID]))continue;
					
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
		
		$this->element_count = count($filterIDs);
		
		if($this->arResult['EXTENDED_MODE'])
		{
			if(empty($filterIDs))
				$filterIDs = array(0);
			
			global ${$this->FILTER_NAME};			
			${$this->FILTER_NAME}['ID'] = $filterIDs;
		}
	}
	
	protected function checkElement(&$allCHECKED, &$arElement, &$hash){
		$result = true;
		foreach($allCHECKED as $id => $arValue){
			$hash[$id] = true;
			
			if(!isset($arElement[$id])){
				$result = false;
				$hash[$id] = false;
			}
			
			$arProperty = &$this->arResult['ITEMS'][$id];
			if($arProperty['SETTINGS']['VIEW'] == 'SLIDER')
			{
				if(isset($arValue['MIN']))
				{
					if($arElement[$id]<$arValue['MIN']){
						$result = false;
						$hash[$id] = false;
					}
				}
				
				if(isset($arValue['MAX']))
				{
					if($arElement[$id]>$arValue['MAX']){
						$result = false;
						$hash[$id] = false;
					}
				}
			}
			/*elseif($arProperty['PROPERTY_TYPE'] == 'N')
			{
				if($arElement[$id] != array_pop($arValue)){
					$result = false;
					$hash[$id] = false;
				}
			}*/
			else
			{
				if(is_array($arElement[$id]))
				{
					if(!count(array_intersect($arElement[$id], $arValue))){
						$result = false;
						$hash[$id] = false;
					}
				}
				else
				{
					if($arValue[$arElement[$id]] != $arElement[$id]){
						$result = false;
						$hash[$id] = false;
					}
				}
			}
		}
		return $result;
	}
	
	protected function checkElementByHash(&$allCHECKED, &$hash, $PID = false){
		foreach($allCHECKED as $id => $arValue){
			if($id == $PID)continue;

			if($hash[$id] == false){
				return false;
			}
		}
		return true;
	}
		
	function contractionValues(&$arVariants)
	{
		foreach($this->arResult['ITEMS'] as $PID => &$arItem)
		{
			if($arItem['SETTINGS']['VIEW'] == 'SLIDER')
			{
				foreach ($arVariants[$PID] as $key => $value)
				{
					if($key<$arItem['VALUES']['MIN']['RANGE_VALUE'] || !strlen($arItem['VALUES']['MIN']['RANGE_VALUE'])){
						$arItem['VALUES']['MIN']['RANGE_VALUE'] = $key;
					}
					
					if($key>$arItem['VALUES']['MAX']['RANGE_VALUE'] || !strlen($arItem['VALUES']['MAX']['RANGE_VALUE'])){
						$arItem['VALUES']['MAX']['RANGE_VALUE'] = $key;
					}
				}
			}
			else
			{
				foreach ($arItem['VALUES'] as $key => &$arValue)
				{
					$arValue['CNT'] = intval($arVariants[$PID][$key]['CNT']);
					if(!$arValue['CNT'])
						$arValue['DISABLED'] = true;
				}
				unset($arValue);
			}
		}
		unset($arItem);
	}
	
	public function makeLinkItems($url)
	{
		$arRequest = $this->arRequest;
		
		foreach($this->arResult['ITEMS'] as $PID => &$arItem)
		{
			if($arItem['SETTINGS']['VIEW'] == 'LINK')
			{
				if(!isset($arRequest[$arItem['CODE_ALT']]))
					$arRequest[$arItem['CODE_ALT']] = array();
				
				foreach($arItem['VALUES'] as $key => &$ar)
				{
					$newRequest = $arRequest;
					
					if($ar['CHECKED'])
					{
						unset($newRequest[$arItem['CODE_ALT']][$ar['HTML_VALUE_ALT']]);
						$ar['HREF'] = $this->getUrl($url, $newRequest);
					}
					else
					{
						$newRequest[$arItem['CODE_ALT']][$ar['HTML_VALUE_ALT']] = $ar['HTML_VALUE_ALT'];
						$ar['HREF'] = $this->getUrl($url, $newRequest);
					}
				}
				unset($ar);
			}
		}
		unset($arItem);
	}
	
	public function getUrl($url, $arRequest = array(), $addParams = array())
	{
		$paramsToAdd = array();
		$oldStyle = false;

		if($this->arResult['IS_SEF'])
			$sefURL = $url;

		if(!empty($arRequest))
		{
			if($this->arResult['IS_SEF'])
				$sefURL .= $this->arSefParams['marker'].'/';

			foreach($this->arResult['ITEMS'] as $PID => &$arItem)
			{
				$values = array();
				foreach($arItem['VALUES'] as $key => $ar)
				{
					if(isset($arRequest[$arItem['CODE_ALT'].'_from']) && $key == 'MIN')
					{
						if($arItem['PROPERTY_TYPE'] == 'N' || isset($arItem['PRICE'])){
							$paramsToAdd[$arItem['CODE_ALT'].'_from'] = $arRequest[$arItem['CODE_ALT'].'_from'];
							$values[$key] = $arRequest[$arItem['CODE_ALT'].'_from'];
						}
					}
					elseif(isset($arRequest[$arItem['CODE_ALT'].'_to']) && $key == 'MAX')
					{
						if($arItem['PROPERTY_TYPE'] == 'N' || isset($arItem['PRICE'])){
							$paramsToAdd[$arItem['CODE_ALT'].'_to'] = $arRequest[$arItem['CODE_ALT'].'_to'];
							$values[$key] = $arRequest[$arItem['CODE_ALT'].'_to'];
						}
					}
					elseif(isset($arRequest[$arItem['CODE_ALT']][$ar['HTML_VALUE_ALT']]))
					{
						if(!is_array($paramsToAdd[$arItem['CODE_ALT']]))
							$paramsToAdd[$arItem['CODE_ALT']] = array();
							
						$paramsToAdd[$arItem['CODE_ALT']][] = $ar['HTML_VALUE_ALT'];
						$values[] = $ar['HTML_VALUE_ALT'];
					}
					elseif(isset($arRequest[$ar['CONTROL_NAME']]))
					{
						$oldStyle = true;
						if($arItem['PROPERTY_TYPE'] == 'N' || isset($arItem['PRICE'])){
							$paramsToAdd[$ar['CONTROL_NAME']] = $arRequest[$ar['CONTROL_NAME']];
							$values[$key] = $arRequest[$ar['CONTROL_NAME']];
						}
						elseif($arRequest[$ar['CONTROL_NAME']] == $ar['HTML_VALUE']){
							$paramsToAdd[$ar['CONTROL_NAME']] = $arRequest[$ar['CONTROL_NAME']];
							$values[] = $ar['CONTROL_NAME_ALT'];
						}
					}
				}
				unset($arItem);

				if(count($values) && $this->arResult['IS_SEF'])
				{
					$sefURL .= $this->getUrlParam($PID, $values);
				}
			}
		}
		
		if($this->arResult['IS_SEF'])
		{
			return CKitFilter::urlAddParams($sefURL, $addParams, array(
				'skip_empty' => true,
				'encode' => true,
			));
		}
		else
		{
			static $paramsToDelete = array();
			
			if(empty($paramsToDelete))
			{
				$paramsToDelete = array('set_filter', 'del_filter', 'ajax', 'bxajaxid', 'AJAX_CALL');
				foreach($this->arResult['ITEMS'] as $PID => &$arItem)
				{
					if($arItem['SETTINGS']['VIEW'] == 'SLIDER'){
						$paramsToDelete[] = $arItem['CODE_ALT'].'_from';
						$paramsToDelete[] = $arItem['CODE_ALT'].'_to';
					}
					else
						$paramsToDelete[] = $arItem['CODE_ALT'];
						
					foreach($arItem['VALUES'] as $key => $ar)
						$paramsToDelete[] = $ar['CONTROL_NAME'];
				}
				unset($arItem);
			}
			
			$clearURL = CHTTP::urlDeleteParams($url, $paramsToDelete, array('delete_system_params' => true));
			
			if($oldStyle)
				$paramsToAdd['set_filter'] = 'y';
			
			return CKitFilter::urlAddParams($clearURL, $paramsToAdd + $addParams, array(
				'skip_empty' => true,
				'encode' => true,
			));
		}
	}
	
	public function getUrlParam($PID, $values)
	{	
		$url = '';
		if(!is_array($PID))
			$arItem = $this->arResult['ITEMS'][$PID];
		else
			$arItem = $PID;

		if($arItem['SETTINGS']['VIEW'] == 'SLIDER')
		{
			$url .= $arItem['CODE_ALT'];
			if(isset($values['MIN']))
				$url .= '-from-'.$values['MIN'];
			
			if(isset($values['MAX']))
				$url .= '-to-'.$values['MAX'];
			$url .= '/';
		}
		else
		{
			$url .= $arItem['CODE_ALT'].'-'.implode('-or-', $values).'/';
		}
		return $url;
	}
	
	public function getHidden()
	{
		$arReturn = array();
		$arInputNames = array();
		foreach($this->arResult['ITEMS'] as $PID => &$arItem)
		{
			if($arItem['SETTINGS']['VIEW'] == 'SLIDER'){
				$arInputNames[$arItem['CODE_ALT'].'_from'] = true;
				$arInputNames[$arItem['CODE_ALT'].'_to'] = true;
			}
			else
				$arInputNames[$arItem['CODE_ALT']] = true;
				
			foreach($arItem['VALUES'] as $key => $ar)
				$arInputNames[$ar['CONTROL_NAME']] = true;
		}
		unset($arItem);
		
		$arInputNames['set_filter']=true;
		$arInputNames['del_filter']=true;

		$arSkip = array(
			'AUTH_FORM' => true,
			'TYPE' => true,
			'USER_LOGIN' => true,
			'USER_CHECKWORD' => true,
			'USER_PASSWORD' => true,
			'USER_CONFIRM_PASSWORD' => true,
			'USER_EMAIL' => true,
			'captcha_word' => true,
			'captcha_sid' => true,
			'login' => true,
			'Login' => true,
			'backurl' => true,
			'ajax' => true,
			'mode' => true,
			'clear_cache' => true,
			'f_Quantity' => true
		);

		foreach(array_merge($_GET, $_POST) as $key => $value)
		{
			if(
				!array_key_exists($key, $arInputNames)
				&& !array_key_exists($key, $arSkip)
				&& !is_array($value)
			)
			{
				$arReturn[] = array(
					'CONTROL_ID' => htmlspecialcharsbx($key),
					'CONTROL_NAME' => htmlspecialcharsbx($key),
					'HTML_VALUE' => htmlspecialcharsbx($value),
				);
			}
		}
		
		return $arReturn;
	}
	
	function getFilter()
	{
		global ${$this->FILTER_NAME};
		$arrFilter = ${$this->FILTER_NAME};
		
		if(!is_array($arrFilter))
			$arrFilter = array();
		
		$available = '';
		if($this->arParams['HIDE_NOT_AVAILABLE'])
			$available = 'Y';

		if($this->arResult['SKU_PROPERTY_COUNT'] > 0)
		{
			if(!isset($arrFilter['OFFERS']))
				$arrFilter['OFFERS'] = array();
		}
		
		foreach($this->arResult['ITEMS'] as $PID => &$arItem)
		{
			if(isset($arItem['PRICE']))
			{
				if(strlen($arItem['VALUES']['MIN']['HTML_VALUE']) && strlen($arItem['VALUES']['MAX']['HTML_VALUE']))
					$arrFilter['><CATALOG_PRICE_'.$arItem['ID']] = array($arItem['VALUES']['MIN']['HTML_VALUE'], $arItem['VALUES']['MAX']['HTML_VALUE']);
				elseif(strlen($arItem['VALUES']['MIN']['HTML_VALUE']))
					$arrFilter['>=CATALOG_PRICE_'.$arItem['ID']] = $arItem['VALUES']['MIN']['HTML_VALUE'];
				elseif(strlen($arItem['VALUES']['MAX']['HTML_VALUE']))
					$arrFilter['<=CATALOG_PRICE_'.$arItem['ID']] = $arItem['VALUES']['MAX']['HTML_VALUE'];
			}
			elseif($arItem['PROPERTY_TYPE'] == 'SECTIONS')
			{
				$sections = array();
				foreach($arItem['VALUES'] as $key => $ar)
				{
					if($ar['CHECKED'])
					{
						$sections[] = htmlspecialcharsback($key);
					}
				}
				
				if(count($sections))
					$arrFilter['SECTION_ID'] = $sections;
			}
			elseif($arItem['PROPERTY_TYPE'] == 'AVAILABLE')
			{
				$values = array();
				foreach($arItem['VALUES'] as $key => $ar)
				{
					if($ar['CHECKED'])
					{
						$values[] = htmlspecialcharsback($key);
					}
				}
				
				if(count($values) == 1)
				{
					$available = $values[0];
				}
			}
			elseif($arItem['SETTINGS']['VIEW'] == 'SLIDER')
			{
				$name = 'PROPERTY_'.$PID;
				
				if($arItem['CODE'] == 'QUANTITY')
					$name = 'CATALOG_QUANTITY';
				
				$existMinValue = (strlen($arItem['VALUES']['MIN']['HTML_VALUE']) > 0);
				$existMaxValue = (strlen($arItem['VALUES']['MAX']['HTML_VALUE']) > 0);
				if ($existMinValue || $existMaxValue)
				{
					$filterKey = '';
					$filterValue = '';
					if ($existMinValue && $existMaxValue)
					{
						$filterKey = '><'.$name;
						$filterValue = array($arItem['VALUES']['MIN']['HTML_VALUE'], $arItem['VALUES']['MAX']['HTML_VALUE']);
					}
					elseif($existMinValue)
					{
						$filterKey = '>='.$name;
						$filterValue = $arItem['VALUES']['MIN']['HTML_VALUE'];
					}
					elseif($existMaxValue)
					{
						$filterKey = '<='.$name;
						$filterValue = $arItem['VALUES']['MAX']['HTML_VALUE'];
					}
					
					if ($arItem['IBLOCK_ID'] == $this->SKU_IBLOCK_ID)
						$filter = &$arrFilter['OFFERS'];
					else
						$filter = &$arrFilter;

					$filter[$filterKey] = $filterValue;
				}
			}
			else
			{
				if ($arItem['IBLOCK_ID'] == $this->SKU_IBLOCK_ID)
					$filter = &$arrFilter['OFFERS'];
				else
					$filter = &$arrFilter;

				foreach($arItem['VALUES'] as $key => $ar)
				{
					if($ar['CHECKED'])
					{
						$filterKey = '=PROPERTY_'.$PID;
						if(!array_key_exists($filterKey, $filter))
							$filter[$filterKey] = array(htmlspecialcharsback($key));
						else
							$filter[$filterKey][] = htmlspecialcharsback($key);
					}
				}
			}
		}
		
		if(intval($this->SECTION_ID)){
			if(!isset($arrFilter['SECTION_ID']))
				$arrFilter['SECTION_ID'] = $this->SECTION_ID;
			
			$arrFilter['INCLUDE_SUBSECTIONS'] = 'Y';
		}
		
		if(strlen($available))
		{
			$arPriceFilter = array();
			foreach($arrFilter as $key => $value)
			{
				if(preg_match('/^(>=|<=|><)CATALOG_PRICE_/', $key))
				{
					$arPriceFilter[$key] = $value;
				}
			}

			if($this->arResult['SKU_PROPERTY_COUNT'] > 0)
			{
				if (empty($arPriceFilter))
					$arSubFilter = $arrFilter['OFFERS'];
				else
					$arSubFilter = array_merge($arrFilter['OFFERS'], $arPriceFilter);

				$arSubFilter['IBLOCK_ID'] = $this->SKU_IBLOCK_ID;
				$arSubFilter['ACTIVE_DATE'] = 'Y';
				$arSubFilter['ACTIVE'] = 'Y';
				$arSubFilter['CATALOG_AVAILABLE'] = $available;
				
				$arrFilter[] = array(
					'LOGIC' => 'OR',
					'CATALOG_AVAILABLE' => $available,
					'=ID' => CIBlockElement::SubQuery('PROPERTY_'.$this->SKU_PROPERTY_ID, $arSubFilter)
				);
			}
			else
			{
				$arrFilter['CATALOG_AVAILABLE'] = $available;
			}
		}
		
		return $arrFilter;
	}
	
	public function getElementCount()
	{
		return $this->element_count;
	}
	
	public function saveInSession()
	{
		if(!is_array($_SESSION[$FILTER_NAME][$this->IBLOCK_ID]))
			$_SESSION[$this->FILTER_NAME][$this->IBLOCK_ID] = array();
			
		$_SESSION[$this->FILTER_NAME][$this->IBLOCK_ID][$this->SECTION_ID] = array();
		foreach($this->arResult['ITEMS'] as $PID => &$arItem)
		{
			foreach($arItem['VALUES'] as $key => $ar)
			{
				if(isset($this->arRequest[$ar['CONTROL_NAME']]))
				{
					$_SESSION[$this->FILTER_NAME][$this->IBLOCK_ID][$this->SECTION_ID][$ar['CONTROL_NAME']] =$this->arRequest[$ar['CONTROL_NAME']];
				}
			}
		}
		unset($arItem);
	}
	
	public function roundMinMaxValues(&$arItem)
	{
		if($arItem['SETTINGS']['VIEW'] == 'SLIDER')
		{
			if(isset($arItem['VALUES']['MIN']['VALUE']) && isset($arItem['VALUES']['MAX']['VALUE']) && $arItem['VALUES']['MAX']['VALUE'] > $arItem['VALUES']['MIN']['VALUE'])
			{
				$arItem['VALUES']['MIN']['VALUE'] = floatval($arItem['VALUES']['MIN']['VALUE']);
				$arItem['VALUES']['MAX']['VALUE'] = floatval($arItem['VALUES']['MAX']['VALUE']);
				
				$diaposon = $arItem['VALUES']['MAX']['VALUE'] - $arItem['VALUES']['MIN']['VALUE'];
				
				if(floatval($arItem['SETTINGS']['SLIDER_STEP']) > 0)
				{
					$step = floatval($arItem['SETTINGS']['SLIDER_STEP']);
				}
				else
				{
					$step = 1;
					if($diaposon / 20 < 1)
						$step = round($diaposon / 20 * 1000000) / 1000000;
				}
				
				$precision = 1;
				
				while($step<1){
					$precision *= 10;
					$step *= 10;
				}
				
				$arItem['VALUES']['MIN']['VALUE'] = floor($arItem['VALUES']['MIN']['VALUE'] / $step) * $step;
				$arItem['VALUES']['MIN']['VALUE'] = floor($arItem['VALUES']['MIN']['VALUE']*$precision) / $precision;
				
				$arItem['VALUES']['MAX']['VALUE'] = ceil($arItem['VALUES']['MAX']['VALUE'] / $step) * $step;
				$arItem['VALUES']['MAX']['VALUE'] = ceil($arItem['VALUES']['MAX']['VALUE']*$precision) / $precision;
			}
		}
	}
	
	public function setDefaultValues(&$arRequest)
	{
		foreach (GetModuleEvents('kit.filter', 'OnSetFilterDefault', true) as $arEvent)
            ExecuteModuleEventEx($arEvent, array(&$arRequest));
			
		if($this->isBitronic)
		{
			foreach($this->arResult['ITEMS'] as $PID => &$arItem)
			{
				if($PID == 'AVAILABLE')
				{
					foreach($arItem['VALUES'] as $key => $ar)
					{
						if($key == 'Y' || ($key == 'Y' && $_GET['f_Quantity'] == 'Y')){
							$arRequest[$ar['CONTROL_NAME']] = $ar['HTML_VALUE'];
							
							if(isset($arRequest[$arItem['CODE_ALT']]))
								$arRequest[$arItem['CODE_ALT']][$ar['HTML_VALUE_ALT']] = $ar['HTML_VALUE_ALT'];
							else
								$arRequest[$arItem['CODE_ALT']] = array($ar['HTML_VALUE_ALT'] => $ar['HTML_VALUE_ALT']);
						}
						elseif($key == 'Y' && $_GET['f_Quantity'] == 'N'){
							unset($arRequest[$ar['CONTROL_NAME']]);
							unset($arRequest[$arItem['CODE_ALT']]);
						}
					}
				}
			}
			unset($arItem);
		}
	}
	
	public function ajaxResult()
	{
		$arResult = $this->arResult;
		
		unset($arResult['COMBO']);
		unset($arResult['ELEMENTS']);
		unset($arResult['ELEMENTS_SKU']);
		unset($arResult['REQUEST']);
		
		foreach($arResult['ITEMS'] as &$arItem)
		{
			unset($arItem['HINT']);
		}
		unset($arItem);
		
		return $arResult;
	}
	
	public function sortValues($PID)
	{
		$arItem = &$this->arResult['ITEMS'][$PID];
		$sort = $arItem['SETTINGS']['SORT'];
		
		if($arItem['SETTINGS']['SORT_ORDER'] == 'DESC')
			$sort .= '_DESC';
		else
			$sort .= '_ASC';

		switch($sort)
		{
			case 'NAME_ASC':
				uasort($arItem['VALUES'], array($this, "sortValuesByNameAsc"));
			break;
			case 'NAME_DESC':
				uasort($arItem['VALUES'], array($this, "sortValuesByNameDesc"));
			break;
			case 'SORT_ASC':
				uasort($arItem['VALUES'], array($this, "sortValuesBySortAsc"));
			break;
			case 'SORT_DESC':
				uasort($arItem['VALUES'], array($this, "sortValuesBySortDesc"));
			break;
			case 'CNT_ASC':
				uasort($arItem['VALUES'], array($this, "sortValuesByCntAsc"));
			break;
			case 'CNT_DESC':
				uasort($arItem['VALUES'], array($this, "sortValuesByCntDesc"));
			break;
			default:
				uasort($arItem['VALUES'], array($this, "sortValuesBySortAsc"));
			break;
		}
	}
	
	protected function sortValuesByNameAsc($v1, $v2)
	{
		return strnatcasecmp($v1["UPPER"], $v2["UPPER"]);
	}
	
	protected function sortValuesByNameDesc($v1, $v2)
	{
		return strnatcasecmp($v2["UPPER"], $v1["UPPER"]);
	}
	
	protected function sortValuesBySortAsc($v1, $v2)
	{
		$sort = strnatcasecmp($v1["SORT"], $v2["SORT"]);
		if ($sort > 0)
			return 1;
		elseif ($sort < 0)
			return -1;
		else
			return strnatcasecmp($v1["UPPER"], $v2["UPPER"]);
	}
	
	protected function sortValuesBySortDesc($v1, $v2)
	{
		$sort = strnatcasecmp($v1["SORT"], $v2["SORT"]);
		if ($sort > 0)
			return -1;
		elseif ($sort < 0)
			return 1;
		else
			return strnatcasecmp($v2["UPPER"], $v1["UPPER"]);
	}
	
	protected function sortValuesByCntAsc($v1, $v2)
	{
		$cnt = strnatcasecmp($v1["CNT"], $v2["CNT"]);
		if ($cnt > 0)
			return 1;
		elseif ($cnt < 0)
			return -1;
		else
			return strnatcasecmp($v1["UPPER"], $v2["UPPER"]);
	}
	
	protected function sortValuesByCntDesc($v1, $v2)
	{
		$cnt = strnatcasecmp($v1["CNT"], $v2["CNT"]);
		if ($cnt > 0)
			return -1;
		elseif ($cnt < 0)
			return 1;
		else
			return strnatcasecmp($v2["UPPER"], $v1["UPPER"]);
	}
	
	public function prepareItems()
	{
		foreach($this->arResult['ITEMS'] as &$arItem)
		{
			if(in_array($arItem['SETTINGS']['VIEW'], array('SELECT', 'LIST', 'RADIO')) && !empty($arItem['VALUES']))
			{
				array_unshift(
					$arItem['VALUES'], 
					array(
						'CONTROL_ID' => $arItem['CODE_ALT'].'_empty',
						'CONTROL_NAME' => $arItem['CODE_ALT'].'_empty',
						'HTML_VALUE' => '',
						'HTML_VALUE_ALT' => '',
						'VALUE' => GetMessage('KIT_CMP_FILTER_ITEM_EMPTY'),
						'SORT' => 0,
						'UPPER' => ToUpper(GetMessage('KIT_CMP_FILTER_ITEM_EMPTY')),
						'CNT' => 0,
						'CHECKED' => !$arItem['CHECKED'],
						'DISABLED' => false
					)
				);
			}
		}
		unset($arItem);
	}
	
	protected function getReturn()
	{
		$arChecked = $this->allCHECKED;
		foreach($arChecked as $PID => $arValues)
			$arChecked[$PID] = array_values($arValues);
			
		$arRequest = $this->arResult['REQUEST'];
		foreach($arRequest as $PID => $arValues)
			$arRequest[$PID] = array_values($arValues);
		
		return array(
			'SET_FILTER' => $this->arResult['SET_FILTER'],
			'REQUEST' => $arRequest,
			'CHECKED' => $arChecked
		);
	}
	
	//old functions for old versions bitronic
	public function addSefModeUrlParam($PID, $values, $reset = false)
	{
		$this->sefURL = $this->getUrlParam($PID, $values);
	}
	
	public function getSefModeUrl(){
		return $this->sefURL;
	}
}
?>