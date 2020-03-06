<?
define('STOP_STATISTICS', true);
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);
define('BX_SECURITY_SHOW_MESSAGE', true);
define("PUBLIC_AJAX_MODE", true);
define("NOT_CHECK_PERMISSIONS", true);

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
Loc::loadMessages(__FILE__);
header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

if ($USER->IsAuthorized() && check_bitrix_sessid() && intval($_REQUEST['ID']) && intval($_REQUEST['IBLOCK_ID']))
{
	if (!Loader::includeModule('iblock') || !Loader::includeModule('kombox.filter'))
	{
		echo CUtil::PhpToJsObject(array('ERROR' => 'SS_MODULE_NOT_INSTALLED'));
		die();
	}

	$ID = intval($_REQUEST['ID']);
	
	$arProperty = array();

	if(is_array($_REQUEST['PROP']))
	{
		$arProperty = $_REQUEST['PROP'];
	}
	elseif(strlen($_REQUEST['PROP']['PROPINFO'])){
		$arProperty = unserialize(base64_decode($_REQUEST['PROP']['PROPINFO']));
	}
	else
	{
		$rsProperty = CIBlockProperty::GetByID($ID);
		$arProperty = $rsProperty->Fetch();
	}
	
	if(!intval($arProperty['IBLOCK_ID']))
		$arProperty['IBLOCK_ID'] = $_REQUEST['IBLOCK_ID'];

	if(isset($_REQUEST['PROPERTY_TYPE']))
		$arProperty['PROPERTY_TYPE'] = $_REQUEST['PROPERTY_TYPE'];
	
	if(!strlen($arProperty['PROPERTY_TYPE'])){
		$arProperty['PROPERTY_TYPE'] = 'S';
	}

	global $APPLICATION;
	foreach($_REQUEST as $key => $value)
	{
		if(strpos($key, 'PROPERTY_') !== false)
		{
			$field = substr($key, 9); 
			
			if(strtolower(LANG_CHARSET) !== 'utf-8')
			{
				if(is_string($value))
				{
					$value = $APPLICATION->ConvertCharset($value, 'UTF-8', 'windows-1251');
					$value = iconv('UTF-8', 'windows-1251', $value);
				}
				else if(is_array($value))
				{
					foreach($value as $key2 => $value2)
						$value[$key2] = $APPLICATION->ConvertCharset($value2, 'UTF-8', 'windows-1251');
				}
			}
			$arProperty[$field] = $value;
		}
	}
	
	if(!isset($arProperty['USER_TYPE_SETTINGS']['KOMBOX_VIEW']) && $ID)
	{
		$rsPropertySettings = \Kombox\Filter\PropertySettingsTable::getList(array(
			'filter' => array('PROPERTY_ID' => $ID)
		));
		
		if($arPropertySettings = $rsPropertySettings->Fetch())
		{
			if(!isset($arProperty['USER_TYPE_SETTINGS']))
				$arProperty['USER_TYPE_SETTINGS'] = array();
				
			$arProperty['USER_TYPE_SETTINGS']['KOMBOX_HINT_TYPE'] = $arPropertySettings['HINT_TYPE'];
			$arProperty['USER_TYPE_SETTINGS']['KOMBOX_HINT'] = $arPropertySettings['HINT'];
			$arProperty['USER_TYPE_SETTINGS']['KOMBOX_VIEW'] = $arPropertySettings['VIEW'];
			$arProperty['USER_TYPE_SETTINGS']['KOMBOX_SLIDER_STEP'] = $arPropertySettings['SLIDER_STEP'];
			$arProperty['USER_TYPE_SETTINGS']['KOMBOX_SLIDER_UNITS'] = $arPropertySettings['SLIDER_UNITS'];
			$arProperty['USER_TYPE_SETTINGS']['KOMBOX_LIST_SIZE'] = $arPropertySettings['LIST_SIZE'];
			$arProperty['USER_TYPE_SETTINGS']['KOMBOX_LIST_MULTI'] = $arPropertySettings['LIST_MULTI'];
			$arProperty['USER_TYPE_SETTINGS']['KOMBOX_VALUES_CNT'] = $arPropertySettings['VALUES_CNT'];
			$arProperty['USER_TYPE_SETTINGS']['KOMBOX_SORT'] = $arPropertySettings['SORT'];
			$arProperty['USER_TYPE_SETTINGS']['KOMBOX_SORT_ORDER'] = $arPropertySettings['SORT_ORDER'];
			$arProperty['USER_TYPE_SETTINGS']['KOMBOX_LOGIC'] = $arPropertySettings['LOGIC'];
		}
	}

	$html = 
	'<tr class="heading"><td colspan="2">'.Loc::getMessage('KOMBOX_MODULE_FILTER_PROPERTIES_SECTION').'</td></tr>'.
	'<tr>'. 
		'<td width="40%" class="adm-detail-content-cell-l">'.Loc::getMessage('KOMBOX_MODULE_FILTER_PROPERTIES_HINT').':</td>'.
		'<td class="adm-detail-content-cell-r">'.
			'<table>'.
				'<tbody>'.
					'<tr>'.
						'<td>'.Loc::getMessage('KOMBOX_MODULE_FILTER_PROPERTIES_HINT_TYPE').':</td>'.
						'<td>'.
							'<label>'.
								'<input type="radio" name="PROPERTY_USER_TYPE_SETTINGS[KOMBOX_HINT_TYPE]" value="text"'.($arProperty['USER_TYPE_SETTINGS']['KOMBOX_HINT_TYPE'] == 'text' ? ' checked="checked"' : '') .'>'.
								'text'.
							'</label> /'.
							'<label>'.
								'<input type="radio" name="PROPERTY_USER_TYPE_SETTINGS[KOMBOX_HINT_TYPE]" value="html"'.($arProperty['USER_TYPE_SETTINGS']['KOMBOX_HINT_TYPE'] == 'html' || $arProperty['USER_TYPE_SETTINGS']['KOMBOX_HINT_TYPE'] == '' ? ' checked="checked"' : '') .'>'.
								'html'.
							'</label>'.
						'</td>'.
					'</tr>'.
					'<tr>'.
						'<td colspan="2" align="center">'.
							'<textarea name="PROPERTY_USER_TYPE_SETTINGS[KOMBOX_HINT]" rows="10" cols="60" style="width:100%">'.$arProperty['USER_TYPE_SETTINGS']['KOMBOX_HINT'].'</textarea>'.
						'</td>'.
					'</tr>'.
				'</tbody>'.
			'</table>'.
		'</td>'.
	'</tr>';

	if($arProperty['PROPERTY_TYPE'] == 'N')
		$arFilterViews = array('SLIDER', 'CHECKBOX', 'RADIO', 'LINK', 'SELECT', 'LIST', /*'TEXT'*/);
	elseif($arProperty['PROPERTY_TYPE'] == 'S:directory')
		$arFilterViews = array('CHECKBOX', 'CHECKBOX_WITH_IMAGES', 'CHECKBOX_WITH_NAME_AND_IMAGES', 'RADIO', 'LINK', 'SELECT', 'SELECT_WITH_IMAGES', 'LIST', /*'TEXT'*/);
	else
		$arFilterViews = array('CHECKBOX', 'RADIO', 'LINK', 'SELECT', 'LIST', /*'TEXT'*/);
		
	$filterView = $arProperty['USER_TYPE_SETTINGS']['KOMBOX_VIEW'];
	
	if(!in_array($filterView, $arFilterViews))
		$filterView = $arFilterViews[0];
	
	$html .= 
	'<tr id="kombox_properties_view">'. 
		'<td width="40%" class="adm-detail-content-cell-l">'.Loc::getMessage('KOMBOX_MODULE_FILTER_PROPERTIES_VIEW').':</td>'.
		'<td class="adm-detail-content-cell-r">'.
			'<select name="PROPERTY_USER_TYPE_SETTINGS[KOMBOX_VIEW]">';
			
	foreach($arFilterViews as $view)
	{
		$html .= '<option value="'.$view.'"'.($filterView == $view ? ' selected="selected"' : '').'>'.Loc::getMessage('KOMBOX_MODULE_FILTER_PROPERTIES_VIEW_'.$view).'</option>';
	}
	
	$html .= 
			'</select>'.
		'</td>'.
	'</tr>';
	
	$sliderStep = floatval($arProperty['USER_TYPE_SETTINGS']['KOMBOX_SLIDER_STEP']);

	$html .= 
	'<tr id="kombox_properties_slider_step" '.($filterView !== 'SLIDER' ? ' style="display:none;"' : '').'>'. 
		'<td width="40%" class="adm-detail-content-cell-l">'.Loc::getMessage('KOMBOX_MODULE_FILTER_PROPERTIES_SLIDER_STEP').':</td>'.
		'<td class="adm-detail-content-cell-r">'.
			'<select name="PROPERTY_USER_TYPE_SETTINGS[KOMBOX_SLIDER_STEP_TYPE]">'.
				'<option value="AUTO"'.($sliderStep == 0 ? ' selected="selected"' : '').'>'.Loc::getMessage('KOMBOX_MODULE_FILTER_PROPERTIES_SLIDER_STEP_AUTO').'</option>'.
				'<option value="SET"'.($sliderStep != 0 ? ' selected="selected"' : '').'>'.Loc::getMessage('KOMBOX_MODULE_FILTER_PROPERTIES_SLIDER_STEP_SET').'</option>'.
			'</select>'.
			'&nbsp;&nbsp;'.
			'<input type="text" name="PROPERTY_USER_TYPE_SETTINGS[KOMBOX_SLIDER_STEP]" value="'.$sliderStep.'" '.($sliderStep<=0 ? ' style="display: none;"' : '').' />'.
		'</td>'.
	'</tr>';
	
	$sliderUnits = trim($arProperty['USER_TYPE_SETTINGS']['KOMBOX_SLIDER_UNITS']);
		
	$html .= 
	'<tr id="kombox_properties_slider_slider_units" '.($filterView !== 'SLIDER' ? ' style="display:none;"' : '').'>'. 
		'<td width="40%" class="adm-detail-content-cell-l">'.Loc::getMessage('KOMBOX_MODULE_FILTER_PROPERTIES_SLIDER_UNITS').':</td>'.
		'<td class="adm-detail-content-cell-r">'.
			'<input type="text" name="PROPERTY_USER_TYPE_SETTINGS[KOMBOX_SLIDER_UNITS]" value="'.$sliderUnits.'" />'.
		'</td>'.
	'</tr>';
	
	$listSize = intval($arProperty['USER_TYPE_SETTINGS']['KOMBOX_LIST_SIZE']);
	
	if($listSize <= 0)
		$listSize = 8;
		
	$html .= 
	'<tr id="kombox_properties_slider_list_size" '.($filterView !== 'LIST' ? ' style="display:none;"' : '').'>'. 
		'<td width="40%" class="adm-detail-content-cell-l">'.Loc::getMessage('KOMBOX_MODULE_FILTER_PROPERTIES_LIST_SIZE').':</td>'.
		'<td class="adm-detail-content-cell-r">'.
			'<input type="text" name="PROPERTY_USER_TYPE_SETTINGS[KOMBOX_LIST_SIZE]" value="'.$listSize.'" />'.
		'</td>'.
	'</tr>';
	
	$listMulti = $arProperty['USER_TYPE_SETTINGS']['KOMBOX_LIST_MULTI'] == 'Y';
	
	$html .= 
	'<tr id="kombox_properties_slider_list_multi" '.($filterView !== 'LIST' ? ' style="display:none;"' : '').'>'. 
		'<td width="40%" class="adm-detail-content-cell-l">'.Loc::getMessage('KOMBOX_MODULE_FILTER_PROPERTIES_LIST_MULTI').':</td>'.
		'<td class="adm-detail-content-cell-r">'.
			'<input type="checkbox" name="PROPERTY_USER_TYPE_SETTINGS[KOMBOX_LIST_MULTI]" value="Y" '.($listMulti ? 'checked="checked"' : '').' />'.
		'</td>'.
	'</tr>';
	
	$valuesCnt = intval($arProperty['USER_TYPE_SETTINGS']['KOMBOX_VALUES_CNT']);
	$html .= 
	'<tr id="kombox_properties_slider_values_cnt" '.(!in_array($filterView, array('CHECKBOX', 'RADIO', 'LINK')) ? ' style="display:none;"' : '').'>'. 
		'<td width="40%" class="adm-detail-content-cell-l" valign="top">'.Loc::getMessage('KOMBOX_MODULE_FILTER_PROPERTIES_VALUES_CNT').':</td>'.
		'<td class="adm-detail-content-cell-r">'.
			'<input type="text" name="PROPERTY_USER_TYPE_SETTINGS[KOMBOX_VALUES_CNT]" value="'.$valuesCnt.'" />'.
			'<div class="adm-info-message-wrap">'.
				'<div class="adm-info-message">'.
					Loc::getMessage('KOMBOX_MODULE_FILTER_PROPERTIES_VALUES_CNT_TIP').
				'</div>'.
			'</div>'.
		'</td>'.
	'</tr>';
	
	if(in_array($arProperty['PROPERTY_TYPE'], array('L', 'G', 'E', 'G:SectionAuto', 'E:SKU', 'E:EList', 'S:ElementXmlID', 'E:EAutocomplete')))
		$arFilterSorts = array('NAME', 'SORT', 'CNT');
	else
		$arFilterSorts = array('NAME', 'CNT');
	
	$filterSort = $arProperty['USER_TYPE_SETTINGS']['KOMBOX_SORT'];
	
	if(!in_array($filterSort, $arFilterSorts))
		$filterSort = $arFilterSorts[0];
	
	$html .= 
	'<tr id="kombox_properties_sort"'.($filterView == 'SLIDER' ? ' style="display:none;"' : '').'>'. 
		'<td width="40%" class="adm-detail-content-cell-l">'.Loc::getMessage('KOMBOX_MODULE_FILTER_PROPERTIES_SORT').':</td>'.
		'<td class="adm-detail-content-cell-r">'.
			'<select name="PROPERTY_USER_TYPE_SETTINGS[KOMBOX_SORT]">';
			
	foreach($arFilterSorts as $sort)
	{
		$html .= '<option value="'.$sort.'"'.($filterSort == $sort ? ' selected="selected"' : '').'>'.Loc::getMessage('KOMBOX_MODULE_FILTER_PROPERTIES_SORT_'.$sort).'</option>';
	}
	
	$html .= 
			'</select>'.
		'</td>'.
	'</tr>';
	
	$arFilterSortOrders = array('ASC', 'DESC');
	
	$filterOrder = $arProperty['USER_TYPE_SETTINGS']['KOMBOX_SORT_ORDER'];
	
	if(!in_array($filterOrder, $arFilterSortOrders))
		$filterOrder = $arFilterSortOrders[0];
	
	$html .= 
	'<tr id="kombox_properties_sort_order"'.($filterView == 'SLIDER' ? ' style="display:none;"' : '').'>'. 
		'<td width="40%" class="adm-detail-content-cell-l">'.Loc::getMessage('KOMBOX_MODULE_FILTER_PROPERTIES_SORT_ORDER').':</td>'.
		'<td class="adm-detail-content-cell-r">'.
			'<select name="PROPERTY_USER_TYPE_SETTINGS[KOMBOX_SORT_ORDER]">';
			
	foreach($arFilterSortOrders as $order)
	{
		$html .= '<option value="'.$order.'"'.($filterOrder == $order ? ' selected="selected"' : '').'>'.Loc::getMessage('KOMBOX_MODULE_FILTER_PROPERTIES_SORT_ORDER_'.$order).'</option>';
	}
	
	$html .= 
			'</select>'.
		'</td>'.
	'</tr>';
	
	$arFilterLogic = array('OR', 'AND');
	
	$filterLogic = $arProperty['USER_TYPE_SETTINGS']['KOMBOX_LOGIC'];
	
	if(!in_array($filterLogic, $arFilterLogic))
		$filterLogic = $arFilterLogic[0];

	$html .= 
	'<tr id="kombox_properties_logic"'.($filterView == 'SLIDER' ||  $arProperty['MULTIPLE'] !== 'Y' ? ' style="display:none;"' : '').'>'. 
		'<td width="40%" class="adm-detail-content-cell-l">'.Loc::getMessage('KOMBOX_MODULE_FILTER_PROPERTIES_LOGIC').':</td>'.
		'<td class="adm-detail-content-cell-r">'.
			'<select name="PROPERTY_USER_TYPE_SETTINGS[KOMBOX_LOGIC]">';
			
	foreach($arFilterLogic as $logic)
	{
		$html .= '<option value="'.$logic.'"'.($filterLogic == $logic ? ' selected="selected"' : '').'>'.Loc::getMessage('KOMBOX_MODULE_FILTER_PROPERTIES_LOGIC_'.$logic).'</option>';
	}
	
	$html .= 
			'</select>'.
		'</td>'.
	'</tr>';
	
	echo CUtil::PhpToJSObject(array('HTML' => $html));
	die();
}

echo CUtil::PhpToJSObject(array('ERROR' => Loc::getMessage('KOMBOX_MODULE_FILTER_EDIT_PROPERTY_AJAX_ERROR')));