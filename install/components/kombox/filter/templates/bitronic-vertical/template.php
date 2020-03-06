<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(method_exists($this, 'setFrameMode')) 
	$this->setFrameMode(true);
	
if(count($arResult['ELEMENTS']) > 1 && $arResult["ITEMS_COUNT_SHOW"]):
$arParams['MESSAGE_ALIGN'] = isset($arParams['MESSAGE_ALIGN']) ? $arParams['MESSAGE_ALIGN'] : 'LEFT';
$arParams['MESSAGE_TIME'] = intval($arParams['MESSAGE_TIME']) >= 0 ? intval($arParams['MESSAGE_TIME']) : 5;

include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/functions.php");

CJSCore::Init(array("popup"));
$APPLICATION->AddHeadScript("/bitrix/js/kombox/filter/ion.rangeSlider.js");
$APPLICATION->AddHeadScript("/bitrix/js/kombox/filter/jquery.cookie.js");
$APPLICATION->AddHeadScript("/bitrix/js/kombox/filter/jquery.filter.js");
$APPLICATION->AddHeadScript("/bitrix/js/kombox/filter/jquery.bitronic.js");
?>

<div id="ys_filter_bitronic" class="item_filters kombox-filter">
	<h2><?=GetMessage('TITLE_FILTER')?></h2>
	<form name="<?echo $arResult["FILTER_NAME"]."_form"?>" action="<?echo $arResult["FORM_ACTION"]?>" method="get"<?if($arResult['IS_SEF']):?> data-sef="yes"<?endif;?> class="smartfilter">
		<?foreach($arResult["HIDDEN"] as $arItem):?>
			<input
				type="hidden"
				name="<?echo $arItem["CONTROL_NAME"]?>"
				id="<?echo $arItem["CONTROL_ID"]?>"
				value="<?echo $arItem["HTML_VALUE"]?>"
			/>
		<?endforeach;?>

		<?foreach($arResult["ITEMS"] as $arItem):
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
			<div class="lvl1<?if($arItem["CLOSED"]):?> kombox-closed<?endif;?>" data-id="<?echo $arItem["CODE_ALT"].'-'.$arItem["ID"]?>">
				<h3 class="ys-opt <?if($arItem["CLOSED"]):?>ys-hide<?else:?>ys-show<?endif?>">
					<a class="kombox-filter-property-name"><?=$arItem["NAME"]?></a>
					<?if(strlen($arItem['HINT'])):?>
					<span class="kombox-filter-property-hint"></span>
					<div class="kombox-filter-property-hint-text"><?echo $arItem['HINT']?></div>
					<?endif;?>
				</h3>
				<span class="for_modef"></span>	
				<?komboxShowField($arItem);?>
			</div>
			<?endif;?>
		<?endforeach;?>

		<div class='inputs-filter inputs-filter-button'>
			<a href="<?=$arResult["DELETE_URL"]?>" class="<?if(!$arResult["SET_FILTER"]):?>disabled<?endif;?>" id="del_filter"><?=GetMessage("CT_BCSF_DEL_FILTER")?></a>
			<?if($arParams['AJAX_FILTER'] != "Y"):?>
			<button class="button<?if(!$arResult["SET_FILTER"]):?> disabled<?endif;?>" type="submit" id="set_filter" name="set_filter" value="Y">
				<span class="text show"><?=GetMessage("CT_BCSF_SET_FILTER")?></span>
				<span class="notloader"></span>
			</button>
			<?endif;?>
		</div>

		<div id="modef" <?if(!isset($arResult["ELEMENT_COUNT"])) echo 'style="display:none"';?>>
			<div class="ye_result">
				<?echo GetMessage("CT_BCSF_FILTER_COUNT", array("#ELEMENT_COUNT#" => '<span id="modef_num">'.intval($arResult["ELEMENT_COUNT"]).'</span>'));?>
				<a href="<?echo $arResult["FILTER_URL"]?>" class="showchild"><?echo GetMessage("CT_BCSF_FILTER_SHOW")?></a>
			</div>
		</div>
		<div class="ye_clear"></div>
	</form>
</div>

<div class='f_loader'></div>

<script>
	$(function(){
		$('#ys_filter_bitronic').komboxBitronicSmartFilter({
			ajaxURL: '<?echo CUtil::JSEscape($arResult["FORM_ACTION"])?>',
			urlDelete: '<?echo CUtil::JSEscape($arResult["DELETE_URL"])?>',
			align: '<?echo $arParams['MESSAGE_ALIGN']?>',
			modeftimeout: <?echo $arParams['MESSAGE_TIME']?>,
			ajax_enable: '<?=($arParams['AJAX_FILTER'] == "Y")? "Y":"N";?>', 
			cfajaxURL: '<?=SITE_TEMPLATE_PATH."/ajax/catalog_filter.php"?>', 
			site_id: '<?=SITE_ID;?>', 
			iblock_id: '<?=$arParams["IBLOCK_ID"];?>'
		});
	});
</script>
<?endif;?>