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
	<form name="<?echo $arResult["FILTER_NAME"]."_form"?>" action="<?echo $arResult["FORM_ACTION"]?>" method="get"<?if($arResult['IS_SEF']):?> data-sef="yes"<?endif;?> class="smartfilter">
		<?foreach($arResult["HIDDEN"] as $arItem):?>
			<input
				type="hidden"
				name="<?echo $arItem["CONTROL_NAME"]?>"
				id="<?echo $arItem["CONTROL_ID"]?>"
				value="<?echo $arItem["HTML_VALUE"]?>"
			/>
		<?endforeach;?>

		<?foreach($arResult["ITEMS"] as $arItem):?>
			<?
			if($arItem['SETTINGS']['VIEW'] == "SLIDER"):?>
				<?if(isset($arItem["VALUES"]["MIN"]["VALUE"]) && isset($arItem["VALUES"]["MAX"]["VALUE"]) && $arItem["VALUES"]["MAX"]["VALUE"] > $arItem["VALUES"]["MIN"]["VALUE"]):
					komboxShowField($arItem);
				endif;?>
			<?endif;?>
		<?endforeach;?>
		<table width="100%">
			<tr>
			<?$num = 0;?>
			<?foreach($arResult["ITEMS"] as $arItem):?>
			<?if(!empty($arItem["VALUES"]) && $arItem['SETTINGS']['VIEW'] != "SLIDER"):?>
			<?if($num == 3):?>
			<?$num = 0;?>
			</tr>
			<tr>
			<?endif;?>
				<td width="33%">
					<div class="lvl1<?if($arItem["CLOSED"]):?> kombox-closed<?endif;?> ye_option" data-id="<?echo $arItem["CODE_ALT"].'-'.$arItem["ID"]?>">
						<div class="ya_pole">
							<div class="ye_head">
								<span class="ys-opt <?if($arItem["CLOSED"]):?>ys-hide<?else:?>ys-show<?endif?>">
									<span class="kombox-filter-property-name ye_tit"><?=$arItem["NAME"]?></span>
									<?if(strlen($arItem['HINT'])):?>
									<span class="kombox-filter-property-hint"></span>
									<div class="kombox-filter-property-hint-text"><?echo $arItem['HINT']?></div>
									<?endif;?>
								</span>
							</div>
							<?komboxShowField($arItem);?>
						</div>
					</div>
				</td>
			<?$num++;?>
			<?endif;?>
			<?endforeach;?>
			</tr>
		</table>
		
		<div class="ye_result" id="modef" <?if(!isset($arResult["ELEMENT_COUNT"])) echo 'style="display:none"';?>>
			<?echo GetMessage("CT_BCSF_FILTER_COUNT", array("#ELEMENT_COUNT#" => '<span id="modef_num">'.intval($arResult["ELEMENT_COUNT"]).'</span>'));?>
			<a href="<?echo $arResult["FILTER_URL"]?>" class="showchild"><?echo GetMessage("CT_BCSF_FILTER_SHOW")?></a>
		</div>
		
		<div class="ye_clear"></div>
		
		<div class='inputs-filter inputs-filter-button' style="text-align: right;">
			<a href="<?=$arResult["DELETE_URL"]?>" class="<?if(!$arResult["SET_FILTER"]):?>disabled<?endif;?>" id="del_filter"><?=GetMessage("CT_BCSF_DEL_FILTER")?></a>
			<?if($arParams['AJAX_FILTER'] != "Y"):?>
			<button class="button<?if(!$arResult["SET_FILTER"]):?> disabled<?endif;?>" type="submit" id="set_filter" name="set_filter" value="Y">
				<span class="text show"><?=GetMessage("CT_BCSF_SET_FILTER")?></span>
				<span class="notloader"></span>
			</button>
			<?endif;?>
		</div>
	</div> <!-- div.ye_filter -->
</form>

<div class='f_loader'></div>

<script>
	$(function(){
		$('#ys_filter_bitronic').komboxBitronicHorizontalSmartFilter({
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