<?

/**
 * Copyright (c) 6/3/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

if (!CModule::IncludeModule("iblock"))
	return;
	
global $APPLICATION;

$arIBlockType = CIBlockParameters::GetIBlockTypes();
$rsIBlock = CIBlock::GetList(array(
	"sort" => "asc",
), array(
	"TYPE" => $arCurrentValues["IBLOCK_TYPE"],
	"ACTIVE" => "Y",
));
while ($arr = $rsIBlock->Fetch())
	$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];

$boolCatalog = CModule::IncludeModule("catalog");
$arPrice = array();
if ($boolCatalog)
{
	$rsPrice = CCatalogGroup::GetList($v1 = "sort", $v2 = "asc");
	while ($arr = $rsPrice->Fetch())
		$arPrice[$arr["NAME"]] = "[".$arr["NAME"]."] ".$arr["NAME_LANG"];
}

$arProperty = array();
$rsProp = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$arCurrentValues["IBLOCK_ID"]));
while ($arr=$rsProp->Fetch())
{
	if($arr["PROPERTY_TYPE"] != "F")
		$arProperty[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
}

$arOffers = CIBlockPriceTools::GetOffersIBlock($arCurrentValues["IBLOCK_ID"]);
$OFFERS_IBLOCK_ID = is_array($arOffers)? $arOffers["OFFERS_IBLOCK_ID"]: 0;
$arProperty_Offers = array();
if($OFFERS_IBLOCK_ID)
{
	$rsProp = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$OFFERS_IBLOCK_ID));
	while($arr=$rsProp->Fetch())
	{
		if($arr["PROPERTY_TYPE"] != "F")
			$arProperty_Offers[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
	}
}

$arProperty_UF = array();
$arSProperty_LNS = array();
$arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("IBLOCK_".$arCurrentValues["IBLOCK_ID"]."_SECTION");
foreach($arUserFields as $FIELD_NAME=>$arUserField)
{
	$arProperty_UF[$FIELD_NAME] = $arUserField["LIST_COLUMN_LABEL"]? $arUserField["LIST_COLUMN_LABEL"]: $FIELD_NAME;
	if($arUserField["USER_TYPE"]["BASE_TYPE"]=="string")
		$arSProperty_LNS[$FIELD_NAME] = $arProperty_UF[$FIELD_NAME];
}

$arComponentParameters = array(
	"GROUPS" => array(
		"SEF" => array(
			"NAME" => GetMessage("COLLECTED_CMP_FILTER_GROUP_SEF"),
		),
		"FIELDS" => array(
			"NAME" => GetMessage("COLLECTED_CMP_FILTER_GROUP_FIELDS"),
		),
		"PRICES" => array(
			"NAME" => GetMessage("COLLECTED_CMP_FILTER_GROUP_PRICES"),
		),
		"STORES" => array(
			"NAME" => GetMessage("COLLECTED_CMP_FILTER_GROUP_STORES"),
		),
		"XML_EXPORT" => array(
			"NAME" => GetMessage("COLLECTED_CMP_FILTER_GROUP_XML_EXPORT"),
		),
	),
	"PARAMETERS" => array(
		"IBLOCK_TYPE" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("COLLECTED_CMP_FILTER_IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y",
		),
		"IBLOCK_ID" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("COLLECTED_CMP_FILTER_IBLOCK_ID"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlock,
			"REFRESH" => "Y",
		),
		"FILTER_NAME" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("COLLECTED_CMP_FILTER_FILTER_NAME"),
			"TYPE" => "STRING",
			"DEFAULT" => "arrFilter",
		),
		"PRICE_CODE" => array(
			"PARENT" => "PRICES",
			"NAME" => GetMessage("COLLECTED_CMP_FILTER_PRICE_CODE"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arPrice,
		),
		"CACHE_TIME" => array(
			"DEFAULT" => 36000000,
		),
		"CACHE_GROUPS" => array(
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("COLLECTED_CMP_FILTER_CACHE_GROUPS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"SAVE_IN_SESSION" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("COLLECTED_CMP_FILTER_SAVE_IN_SESSION"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"INCLUDE_JQUERY" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("COLLECTED_CMP_FILTER_INCLUDE_JQUERY"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"CLOSED_PROPERTY_CODE" => array(
			"PARENT" => "FIELDS",
			"NAME" => GetMessage("COLLECTED_CMP_FILTER_CLOSED_PROPERTY_CODE"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arProperty,
			"ADDITIONAL_VALUES" => "Y",
		),
		"CLOSED_OFFERS_PROPERTY_CODE" => array(
			"PARENT" => "FIELDS",
			"NAME" => GetMessage("COLLECTED_CMP_FILTER_CLOSED_OFFERS_PROPERTY_CODE"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arProperty_Offers,
			"ADDITIONAL_VALUES" => "Y",
		),
		"SORT" => array(
			"PARENT" => "FIELDS",
			"NAME" => GetMessage("COLLECTED_CMP_FILTER_SORT"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y",
		),
		"XML_EXPORT" => array(
			"PARENT" => "XML_EXPORT",
			"NAME" => GetMessage("COLLECTED_CMP_FILTER_XML_EXPORT"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y",
		),
		"PAGE_URL" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME"=>GetMessage("COLLECTED_CMP_FILTER_PAGE_URL"),
			"TYPE"=>"STRING",
			"DEFAULT"=>"",
		),
		"IS_SEF" => array(
			"PARENT" => "SEF",
			"NAME" => GetMessage("COLLECTED_CMP_FILTER_IS_SEF"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y",
		),
		"MESSAGE_ALIGN" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("COLLECTED_CMP_FILTER_MESSAGE_ALIGN"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"LEFT" => GetMessage("COLLECTED_CMP_FILTER_MESSAGE_ALIGN_LEFT"),
				"RIGHT" => GetMessage("COLLECTED_CMP_FILTER_MESSAGE_ALIGN_RIGHT"),
			),
			"DEFAULT" => "LEFT"
		),
		"MESSAGE_TIME" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("COLLECTED_CMP_FILTER_MESSAGE_TIME"),
			"TYPE" => "STRING",
			"DEFAULT" => '5',
		)
	),
);

//sef
if (isset($arCurrentValues['IS_SEF']) && 'Y' == $arCurrentValues['IS_SEF'])
{
	$arComponentParameters['PARAMETERS']['SEF_BASE_URL'] = array(
		"PARENT" => "SEF",
		"NAME"=>GetMessage("COLLECTED_CMP_FILTER_SEF_BASE_URL"),
		"TYPE"=>"STRING",
		"DEFAULT"=>'/catalog/',
	);
	
	$arComponentParameters['PARAMETERS']['SECTION_PAGE_URL'] = CIBlockParameters::GetPathTemplateParam(
		"SECTION",
		"SECTION_PAGE_URL",
		GetMessage("COLLECTED_CMP_FILTER_SECTION_PAGE_URL"),
		"#SECTION_ID#/",
		"SEF"
	);
	
	$arComponentParameters['PARAMETERS']['DETAIL_PAGE_URL'] = CIBlockParameters::GetPathTemplateParam(
		"DETAIL",
		"DETAIL_PAGE_URL",
		GetMessage("COLLECTED_CMP_FILTER_DETAIL_PAGE_URL"),
		"#SECTION_ID#/#ELEMENT_ID#/",
		"SEF"
	);
}
else
{
	$arComponentParameters['PARAMETERS']['SECTION_ID'] = array(
		"PARENT" => "DATA_SOURCE",
		"NAME" => GetMessage("COLLECTED_CMP_FILTER_SECTION_ID"),
		"TYPE" => "STRING",
		"DEFAULT" => '={$_REQUEST["SECTION_ID"]}',
	);
	
	$arComponentParameters['PARAMETERS']['SECTION_CODE'] = array(
		"PARENT" => "DATA_SOURCE",
		"NAME" => GetMessage("COLLECTED_CMP_FILTER_SECTION_CODE"),
		"TYPE" => "STRING",
		"DEFAULT" => '',
	);
}

//yandex islands
if (isset($arCurrentValues['XML_EXPORT']) && 'Y' == $arCurrentValues['XML_EXPORT'])
{
	$arComponentParameters['PARAMETERS']['SECTION_TITLE'] = array(
		"PARENT" => "XML_EXPORT",
		"NAME" => GetMessage("COLLECTED_CMP_FILTER_SECTION_TITLE"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"DEFAULT" => "-",
		"VALUES" => array_merge(
			array(
				"-" => " ",
				"NAME" => GetMessage("IBLOCK_FIELD_NAME"),
			), $arSProperty_LNS
		),
	);
	
	$arComponentParameters['PARAMETERS']['SECTION_DESCRIPTION'] = array(
		"PARENT" => "XML_EXPORT",
		"NAME" => GetMessage("COLLECTED_CMP_FILTER_SECTION_DESCRIPTION"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"DEFAULT" => "-",
		"VALUES" => array_merge(
			array(
				"-" => " ",
				"NAME" => GetMessage("IBLOCK_FIELD_NAME"),
				"DESCRIPTION" => GetMessage("IBLOCK_FIELD_DESCRIPTION"),
			), $arSProperty_LNS
		),
	);
}

$arFields = array(
	'SECTIONS' => GetMessage('COLLECTED_CMP_FILTER_FIELDS_SECTIONS')
);

if ($boolCatalog)
{
	//hide not available
	$arComponentParameters["PARAMETERS"]['HIDE_NOT_AVAILABLE'] = array(
		'PARENT' => 'STORES',
		'NAME' => GetMessage('COLLECTED_CMP_FILTER_HIDE_NOT_AVAILABLE'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
	);
	
	//stores
	$arStores = array();
	$rsStores = CCatalogStore::GetList(
		array(),
		array('ACTIVE' => 'Y'),
		false,
		false,
		array('ID', 'TITLE')
	);
	
	while ($arStore = $rsStores->Fetch())
	{
		$arStores[$arStore['ID']] = $arStore['TITLE'];
	}
	
	if(count($arStores))
	{
		$arComponentParameters['PARAMETERS']['STORES_ID'] = array(
			'PARENT' => 'STORES',
			'NAME' => GetMessage('COLLECTED_CMP_FILTER_STORES_ID'),
			'TYPE' => 'LIST',
			'DEFAULT' => '',
			"MULTIPLE" => "Y",
			"VALUES" => $arStores
		);
	}
	
	//currency
	if (CModule::IncludeModule('currency'))
	{
		$arComponentParameters["PARAMETERS"]['CONVERT_CURRENCY'] = array(
			'PARENT' => 'PRICES',
			'NAME' => GetMessage('COLLECTED_CMP_FILTER_CONVERT_CURRENCY'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
			'REFRESH' => 'Y',
		);

		if (isset($arCurrentValues['CONVERT_CURRENCY']) && 'Y' == $arCurrentValues['CONVERT_CURRENCY'])
		{
			$arCurrencyList = array();
			$by = 'SORT';
			$order = 'ASC';
			$rsCurrencies = CCurrency::GetList($by, $order);
			while ($arCurrency = $rsCurrencies->Fetch())
			{
				$arCurrencyList[$arCurrency['CURRENCY']] = $arCurrency['CURRENCY'];
			}
			$arComponentParameters['PARAMETERS']['CURRENCY_ID'] = array(
				'PARENT' => 'PRICES',
				'NAME' => GetMessage('COLLECTED_CMP_FILTER_CURRENCY_ID'),
				'TYPE' => 'LIST',
				'VALUES' => $arCurrencyList,
				'DEFAULT' => CCurrency::GetBaseCurrency(),
				"ADDITIONAL_VALUES" => "Y",
			);
		}
	}
	
	$arFields['STORES'] = GetMessage('COLLECTED_CMP_FILTER_FIELDS_STORES');
	$arFields['QUANTITY'] = GetMessage('COLLECTED_CMP_FILTER_FIELDS_QUANTITY');
	
	if($arCurrentValues['HIDE_NOT_AVAILABLE'] !== 'Y')
	{
		$arFields['AVAILABLE'] = GetMessage('COLLECTED_CMP_FILTER_FIELDS_AVAILABLE');
	}
}

//sort fields
if(isset($arCurrentValues['SORT']) && 'Y' == $arCurrentValues['SORT'])
{
	$arComponentParameters['PARAMETERS']['SORT_ORDER'] = array(
		"PARENT" => "FIELDS",
		"NAME" => GetMessage("COLLECTED_CMP_FILTER_SORT_ORDER"),
		"TYPE" => "LIST",
		"DEFAULT" => "ASC",
		"VALUES" => array(
			"ASC" => GetMessage("COLLECTED_CMP_FILTER_SORT_ORDER_ASC"),
			"DESC" => GetMessage("COLLECTED_CMP_FILTER_SORT_ORDER_DESC")
		)
	);
}

//fields
$arComponentParameters["PARAMETERS"]['FIELDS'] = array(
	'PARENT' => 'FIELDS',
	'NAME' => GetMessage('COLLECTED_CMP_FILTER_FIELDS'),
	'TYPE' => 'LIST',
	'DEFAULT' => '',
	"MULTIPLE" => "Y",
	"VALUES" => $arFields,
	'REFRESH' => 'Y',
);

if (is_array($arCurrentValues['FIELDS']) && count($arCurrentValues['FIELDS']))
{
	$sort = 0;
	foreach($arCurrentValues['FIELDS'] as $field)
	{
		if(isset($arCurrentValues['SORT']) && 'Y' == $arCurrentValues['SORT'])
		{
			if(in_array($field, $arCurrentValues['FIELDS']) && isset($arFields[$field]))
			{
				$arComponentParameters['PARAMETERS']['SORT_'.$field] = array(
					'PARENT' => 'FIELDS',
					'NAME' => GetMessage('COLLECTED_CMP_FILTER_SORT_'.$field),
					'TYPE' => 'STRING',
					'DEFAULT' => ++$sort*10
				);
			}
		}
		
		if($field == 'SECTIONS')
		{
			$arComponentParameters['PARAMETERS']['TOP_DEPTH_LEVEL'] = array(
				'PARENT' => 'FIELDS',
				'NAME' => GetMessage('COLLECTED_CMP_FILTER_TOP_DEPTH_LEVEL'),
				'TYPE' => 'STRING',
				'DEFAULT' => '0'
			);
		}
	}
}

if(empty($arPrice))
	unset($arComponentParameters["PARAMETERS"]["PRICE_CODE"]);
	
if($arCurrentValues["IS_SEF"] === "Y"){
    unset($arComponentParameters["PARAMETERS"]["SECTION_ID"]);
	unset($arComponentParameters["PARAMETERS"]["SECTION_CODE"]);
}
else {
    unset($arComponentParameters["PARAMETERS"]["SEF_BASE_URL"]);
    unset($arComponentParameters["PARAMETERS"]["SECTION_PAGE_URL"]);
}
?>