<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if($this->__folder)
	$pathToTemplateFolder = $this->__folder ;
else
	$pathToTemplateFolder = str_replace(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '', dirname(__FILE__));

$arColorSchemes = array('red', 'green', 'ice', 'metal', 'pink', 'yellow');
$tmp = COption::GetOptionString('yenisite.market', 'color_scheme');

if ($arParams['THEME'] && in_array($arParams['THEME'], $arColorSchemes, true)) 
	$color_scheme = $arParams['THEME'];
else if ($arParams['THEME'] === "blue")
	$color_scheme = 'ice';
else if (strlen($tmp) != 0)
{
	if (($bitronic_color_scheme = getBitronicSettings("COLOR_SCHEME")) && in_array($bitronic_color_scheme, $arColorSchemes))
		$color_scheme = $bitronic_color_scheme;
}
else
	$color_scheme = 'red';

global $APPLICATION;

if(strlen($tmp) == 0)
{
	$arResult["IS_BITRONIC"] = "N";

	$APPLICATION->AddHeadScript("{$pathToTemplateFolder}/js/selectbox.js");
	$APPLICATION->AddHeadScript("{$pathToTemplateFolder}/js/jquery.uniform.min.js");
	
	$APPLICATION->SetAdditionalCSS("{$pathToTemplateFolder}/css/selectbox.css");
	$APPLICATION->SetAdditionalCSS("{$pathToTemplateFolder}/css/uniform.default.css");
}

$APPLICATION->SetAdditionalCSS("{$pathToTemplateFolder}/themes/{$color_scheme}.css");

global $YS_AJAX_FILTER_ENABLE;
$arParams['AJAX_FILTER'] = $YS_AJAX_FILTER_ENABLE;

if($arResult['isBitronic'])
{
	foreach($arResult["ITEMS"] as $PID => &$arItem)
	{
		if($PID == 'AVAILABLE')
		{
			foreach($arItem["VALUES"] as $key => $ar)
			{
				if($key == 'Y')unset($arItem["VALUES"][$key]);
			}
		}
	}
	unset($arItem);
}
?>