<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * Copyright (c) 6/3/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

if(!function_exists('collectedShowField'))
{
	function collectedShowField($arItem)
	{
		switch($arItem['SETTINGS']['VIEW'])
		{
			case 'SLIDER':
			?>
				<div class="collected-num collected-filter-property-body" data-name="<?=$arItem["CODE_ALT"]?>">
					<?
					$minValue = !empty($arItem["VALUES"]["MIN"]["HTML_VALUE"]) ? $arItem["VALUES"]["MIN"]["HTML_VALUE"] : $arItem["VALUES"]["MIN"]["VALUE"];
					$maxValue = !empty($arItem["VALUES"]["MAX"]["HTML_VALUE"]) ? $arItem["VALUES"]["MAX"]["HTML_VALUE"] : $arItem["VALUES"]["MAX"]["VALUE"];
					?>
					<?echo GetMessage("COLLECTED_CMP_FILTER_FROM")?> 
					<input 
						class="collected-input collected-num-from" 
						type="text" 
						name="<?echo $arItem["CODE_ALT"]?>_from" 
						id="<?echo $arItem["VALUES"]["MIN"]["CONTROL_ID"]?>" 
						value="<?echo $arItem["VALUES"]["MIN"]["HTML_VALUE"]?>" 
						size="5" 
						placeholder="<?echo $arItem["VALUES"]["MIN"]["VALUE"]?>" 
					/>
					&nbsp;&nbsp;<?echo GetMessage("COLLECTED_CMP_FILTER_TO")?> 
					<input 
						class="collected-input collected-num-to" 
						type="text" 
						name="<?echo $arItem["CODE_ALT"]?>_to" 
						id="<?echo $arItem["VALUES"]["MAX"]["CONTROL_ID"]?>" 
						value="<?echo $arItem["VALUES"]["MAX"]["HTML_VALUE"]?>" 
						size="5" 
						placeholder="<?echo $arItem["VALUES"]["MAX"]["VALUE"]?>" 
					/>
					 <?=$arItem["SETTINGS"]["SLIDER_UNITS"]?>
					<div class="collected-range"> 
						<div  
							data-value="<?echo $minValue?>;<?=$maxValue?>" 
							data-min="<?echo $arItem["VALUES"]["MIN"]["VALUE"]?>" 
							data-max="<?echo $arItem["VALUES"]["MAX"]["VALUE"]?>" 
							data-range-from="<?echo $arItem["VALUES"]["MIN"]["RANGE_VALUE"]?>" 
							data-range-to="<?echo $arItem["VALUES"]["MAX"]["RANGE_VALUE"]?>" 
							<?if($arItem["CODE"] == "QUANTITY"):?> 
							data-step="1" 
							<?elseif(floatval($arItem["SETTINGS"]["SLIDER_STEP"]) > 0):?> 
							data-step="<?=floatval($arItem["SETTINGS"]["SLIDER_STEP"])?>" 
							<?endif?>
						>
						</div>
					</div>
				</div>
			<?
			break;
			case 'SELECT':
			?>
				<div class="collected-select collected-filter-property-body" data-name="<?=$arItem["CODE_ALT"]?>"<?if($arItem["CLOSED"]):?> style="display:none;"<?endif;?>>
					<select name="<?echo $arItem["CODE_ALT"]?>">
					<?foreach($arItem["VALUES"] as $val => $ar):?>
						<option 
							value="<?echo $ar["HTML_VALUE_ALT"]?>" 
							id="<?echo $ar["CONTROL_ID"]?>"
							<?echo $ar["CHECKED"]? 'selected="selected"': ''?>
							<?echo $ar["DISABLED"]? ' disabled="disabled"': ''?> 
							class="<?echo $ar["DISABLED"]? 'collected-disabled': ''?><?echo $ar["CHECKED"]? ' collected-checked': ''?>"
						>
							<?echo $ar["VALUE"];?>
							<?if($ar["CNT"]):?> (<?echo $ar["CNT"];?>)<?endif;?>
						</option>
					<?endforeach;?>
					</select>
				</div>
			<?
			break;
			case 'LIST':
			?>
				<div class="collected-list collected-filter-property-body" data-name="<?=$arItem["CODE_ALT"]?>"<?if($arItem["CLOSED"]):?> style="display:none;"<?endif;?>>
					<select 
						name="<?echo $arItem["CODE_ALT"]?>"
						<?if($arItem["SETTINGS"]["LIST_MULTI"]):?> multiple="multiple"<?endif;?> 
						size="<?echo intval($arItem["SETTINGS"]["LIST_SIZE"]) ? intval($arItem["SETTINGS"]["LIST_SIZE"]) : 8;?>"
					>
					<?foreach($arItem["VALUES"] as $val => $ar):?>
						<option 
							value="<?echo $ar["HTML_VALUE_ALT"]?>" 
							id="<?echo $ar["CONTROL_ID"]?>"
							<?echo $ar["CHECKED"]? 'selected="selected"': ''?>
							<?echo $ar["DISABLED"]? ' disabled="disabled"': ''?> 
							class="<?echo $ar["DISABLED"]? 'collected-disabled': ''?><?echo $ar["CHECKED"]? ' collected-checked': ''?>"
						>
							<?echo $ar["VALUE"];?>
							<?if($ar["CNT"]):?> (<?echo $ar["CNT"];?>)<?endif;?>
						</option>
					<?endforeach;?>
					</select>
				</div>
			<?
			break;
			case 'CHECKBOX':
			?>
				<div class="collected-combo collected-filter-property-body" data-name="<?=$arItem["CODE_ALT"]?>"<?if($arItem["CLOSED"]):?> style="display:none;"<?endif;?>>
					<?foreach($arItem["VALUES"] as $val => $ar):?>
						<?collectedOtherValues($arItem, 'start');?>
						<div class="lvl2<?echo $ar["DISABLED"]? ' collected-disabled': ''?><?echo $ar["CHECKED"]? ' collected-checked': ''?>">
							<input
								type="checkbox" 
								value="<?echo $ar["HTML_VALUE_ALT"]?>" 
								name="<?echo $arItem["CODE_ALT"]?>" 
								id="<?echo $ar["CONTROL_ID"]?>" 
								<?echo $ar["CHECKED"]? 'checked="checked"': ''?> 
							/>
							<label for="<?echo $ar["CONTROL_ID"]?>"><?echo $ar["VALUE"];?> <span class="collected-cnt">(<?echo $ar["CNT"];?>)</span></label>
						</div>
					<?endforeach;?>
					<?collectedOtherValues($arItem);?>
				</div>
			<?
			break;
			case 'RADIO':
			?>
				<div class="collected-radio collected-filter-property-body" data-name="<?=$arItem["CODE_ALT"]?>"<?if($arItem["CLOSED"]):?> style="display:none;"<?endif;?>>
					<?foreach($arItem["VALUES"] as $val => $ar):?>
						<?collectedOtherValues($arItem, 'start');?>
						<div class="lvl2<?echo $ar["DISABLED"]? ' collected-disabled': ''?><?echo $ar["CHECKED"]? ' collected-checked': ''?>">
							<input
								type="radio" 
								value="<?echo $ar["HTML_VALUE_ALT"]?>" 
								name="<?echo $arItem["CODE_ALT"]?>" 
								id="<?echo $ar["CONTROL_ID"]?>" 
								<?echo $ar["CHECKED"]? 'checked="checked"': ''?> 
							/>
							<label for="<?echo $ar["CONTROL_ID"]?>"><?echo $ar["VALUE"];?> <span class="collected-cnt">(<?echo $ar["CNT"];?>)</span></label>
						</div>
					<?endforeach;?>
					<?collectedOtherValues($arItem);?>
				</div>
			<?
			break;
			case 'LINK':
			?>
				<div class="collected-link collected-filter-property-body" data-name="<?=$arItem["CODE_ALT"]?>"<?if($arItem["CLOSED"]):?> style="display:none;"<?endif;?>>
					<?foreach($arItem["VALUES"] as $val => $ar):?>
						<?collectedOtherValues($arItem, 'start');?>
						<div class="lvl2<?echo $ar["DISABLED"]? ' collected-disabled': ''?><?echo $ar["CHECKED"]? ' collected-checked': ''?>">
							<?if($ar["CHECKED"]):?><input type="hidden" value="<?echo $ar["HTML_VALUE_ALT"]?>" name="<?echo $arItem["CODE_ALT"]?>" /><?endif;?>
							<a 
								href="<?echo $ar["HREF"]?>" 
								id="<?echo $ar["CONTROL_ID"]?>"
								data-value="<?echo $ar["HTML_VALUE_ALT"]?>" 
								data-name="<?echo $arItem["CODE_ALT"]?>" 
								<?echo $ar["CHECKED"]? 'data-checked="checked"': ''?> 
							>
								<?echo $ar["VALUE"];?>
								<span class="collected-remove-link"></span>
							</a>
							<span class="collected-cnt">(<?echo $ar["CNT"];?>)</span>
						</div>
					<?endforeach;?>
					<?collectedOtherValues($arItem);?>
				</div>
			<?
			break;
		}
	}
}

if(!function_exists('collectedOtherValues'))
{
	function collectedOtherValues($arItem, $action = 'end')
	{
		static $cnt = 0;
		$flag = $arItem['LAST_CHECKED_POS'] > intval($arItem["SETTINGS"]["VALUES_CNT"]);
		
		if($action == 'start')
		{
			$cnt++;
			if($cnt == intval($arItem["SETTINGS"]["VALUES_CNT"]) + 1 && intval($arItem["SETTINGS"]["VALUES_CNT"])):?>
			<div class="collected-values-other" <?if(!$flag):?> style="display: none;"<?endif;?>>
			<?endif;
		}
		elseif($action == 'end')
		{
			if($cnt > intval($arItem["SETTINGS"]["VALUES_CNT"]) && intval($arItem["SETTINGS"]["VALUES_CNT"])):?>
			</div>
			<a class="collected-values-other-show" href="#"<?if($flag):?> style="display: none;"<?endif;?>><?=GetMessage('COLLECTED_CMP_FILTER_VALUES_SHOW')?></a>
			<a class="collected-values-other-hide" href="#"<?if(!$flag):?> style="display: none;"<?endif;?>><?=GetMessage('COLLECTED_CMP_FILTER_VALUES_HIDE')?></a>
			<?endif;
			$cnt = 0;
		}
	}
}
?>