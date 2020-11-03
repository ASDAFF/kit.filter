<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * Copyright (c) 6/3/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

if(method_exists($this, 'setFrameMode')) 
	$this->setFrameMode(true);
	
if(count($arResult['ELEMENTS']) > 1 && $arResult["ITEMS_COUNT_SHOW"]):
$arParams['MESSAGE_ALIGN'] = isset($arParams['MESSAGE_ALIGN']) ? $arParams['MESSAGE_ALIGN'] : 'LEFT';
$arParams['MESSAGE_TIME'] = intval($arParams['MESSAGE_TIME']) >= 0 ? intval($arParams['MESSAGE_TIME']) : 5;

include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/functions.php");

CJSCore::Init(array("popup"));
$APPLICATION->AddHeadScript("/bitrix/js/kit/filter/ion.rangeSlider.js");
$APPLICATION->AddHeadScript("/bitrix/js/kit/filter/jquery.cookie.js");
$APPLICATION->AddHeadScript("/bitrix/js/kit/filter/jquery.filter.js");
?>
<div class="kit-filter" id="kit-filter">
	<form name="<?echo $arResult["FILTER_NAME"]."_form"?>" action="<?echo $arResult["FORM_ACTION"]?>" method="get"<?if($arResult['IS_SEF']):?> data-sef="yes"<?endif;?>>
		<?foreach($arResult["HIDDEN"] as $arItem):?>
			<input
				type="hidden"
				name="<?echo $arItem["CONTROL_NAME"]?>"
				id="<?echo $arItem["CONTROL_ID"]?>"
				value="<?echo $arItem["HTML_VALUE"]?>"
			/>
		<?endforeach;?>
		<ul>	
		<?
		foreach($arResult["ITEMS"] as $arItem):
			$showProperty = false;
			if($arItem["SETTINGS"]["VIEW"] == "SLIDER")
			{
				if(isset($arItem["VALUES"]["MIN"]["VALUE"]) && isset($arItem["VALUES"]["MAX"]["VALUE"]) && $arItem["VALUES"]["MAX"]["VALUE"] > $arItem["VALUES"]["MIN"]["VALUE"])
					$showProperty = true;
			}
			elseif(!empty($arItem["VALUES"]) && !isset($arItem["PRICE"]))
			{
				$showProperty = true;
			}
			?>
			<?if($showProperty):?>
			<li class="lvl1<?if($arItem["CLOSED"]):?> kit-closed<?endif;?>" data-id="<?echo $arItem["CODE_ALT"].'-'.$arItem["ID"]?>">
				<div class="kit-filter-property-head">
					<i class="kit-filter-property-i"></i>
					<span class="kit-filter-property-name"><?echo $arItem["NAME"]?></span>
					<?if(strlen($arItem['HINT'])):?>
					<span class="kit-filter-property-hint"></span>
					<div class="kit-filter-property-hint-text"><?echo $arItem['HINT']?></div>
					<?endif;?>
				</div>
				<span class="for_modef"></span>	
				<?kitShowField($arItem);?>
			</li>	
			<?endif;?>
		<?endforeach;?>
		</ul>
		<input type="submit" id="set_filter" value="<?=GetMessage("KIT_CMP_FILTER_SET_FILTER")?>" />
		<?if($arResult['SET_FILTER']):?>
		<a href="<?=$arResult["DELETE_URL"]?>" class="kit-del-filter"><?=GetMessage("KIT_CMP_FILTER_DEL_FILTER")?></a>
		<?endif;?>
		<div class="modef" id="modef" style="display:none">
			<div class="modef-wrap">
				<?echo GetMessage("KIT_CMP_FILTER_FILTER_COUNT", array("#ELEMENT_COUNT#" => '<span id="modef_num">'.intval($arResult["ELEMENT_COUNT"]).'</span>'));?>
				<a href="<?echo $arResult["FILTER_URL"]?>"><?echo GetMessage("KIT_CMP_FILTER_FILTER_SHOW")?></a>
				<span class="ecke"></span>
			</div>
		</div>
	</form>
	<div class="kit-loading"></div>
</div>
<script>
	$(function(){
		$('#kit-filter').kitSmartFilter({
			ajaxURL: '<?echo CUtil::JSEscape($arResult["FORM_ACTION"])?>',
			urlDelete: '<?echo CUtil::JSEscape($arResult["DELETE_URL"])?>',
			align: '<?echo $arParams['MESSAGE_ALIGN']?>',
			modeftimeout: <?echo $arParams['MESSAGE_TIME']?>
		});
	});
</script>
<?endif;?>