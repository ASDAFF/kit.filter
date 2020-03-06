<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!function_exists('komboxShowField'))
{
	function komboxShowField($arItem)
	{
		switch($arItem['SETTINGS']['VIEW'])
		{
			case 'SLIDER':
			?>
				<div class="kombox-filter-property-body price_slider kombox-num" data-name="<?=$arItem["CODE_ALT"]?>">
					<?
					$minValue = is_numeric($arItem["VALUES"]["MIN"]["HTML_VALUE"]) ? $arItem["VALUES"]["MIN"]["HTML_VALUE"] : $arItem["VALUES"]["MIN"]["VALUE"];
					$maxValue = is_numeric($arItem["VALUES"]["MAX"]["HTML_VALUE"]) ? $arItem["VALUES"]["MAX"]["HTML_VALUE"] : $arItem["VALUES"]["MAX"]["VALUE"];
					?>
					<div class="kombox-range"> 
						<div  
							data-value="<?echo floatval($minValue)?>;<?echo floatval($maxValue)?>" 
							data-min="<?echo floatval($arItem["VALUES"]["MIN"]["VALUE"])?>" 
							data-max="<?echo floatval($arItem["VALUES"]["MAX"]["VALUE"])?>" 
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
					
					<?=GetMessage('CT_BCSF_FILTER_FROM')?>
					<input 
						class="kombox-num-from txt" 
						type="text" 
						name="<?echo $arItem["CODE_ALT"]?>_from" 
						id="<?echo $arItem["VALUES"]["MIN"]["CONTROL_ID"]?>" 
						value="<?echo $arItem["VALUES"]["MIN"]["HTML_VALUE"]?>" 
						size="3" 
						placeholder="<?echo floatval($arItem["VALUES"]["MIN"]["VALUE"])?>" 
					/>
					<?echo GetMessage("CT_BCSF_FILTER_TO")?> 
					<input 
						class="kombox-num-to txt" 
						type="text" 
						name="<?echo $arItem["CODE_ALT"]?>_to" 
						id="<?echo $arItem["VALUES"]["MAX"]["CONTROL_ID"]?>" 
						value="<?echo $arItem["VALUES"]["MAX"]["HTML_VALUE"]?>" 
						size="3" 
						placeholder="<?echo floatval($arItem["VALUES"]["MAX"]["VALUE"])?>" 
					/>	
					<?=$arItem["SETTINGS"]["SLIDER_UNITS"]?>
				</div>
			<?
			break;
			case 'SELECT':
			?>
				<div class="kombox-select kombox-filter-property-body" data-name="<?=$arItem["CODE_ALT"]?>"<?if($arItem["CLOSED"]):?> style="display:none;"<?endif;?>>
					<select name="<?echo $arItem["CODE_ALT"]?>">
					<?foreach($arItem["VALUES"] as $val => $ar):?>
						<option 
							value="<?echo $ar["HTML_VALUE_ALT"]?>" 
							id="<?echo $ar["CONTROL_ID"]?>"
							<?echo $ar["CHECKED"]? 'selected="selected"': ''?>
							<?echo $ar["DISABLED"]? ' disabled="disabled"': ''?> 
							class="<?echo $ar["DISABLED"]? 'kombox-disabled': ''?><?echo $ar["CHECKED"]? ' kombox-checked': ''?>"
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
				<div class="kombox-list kombox-filter-property-body" data-name="<?=$arItem["CODE_ALT"]?>"<?if($arItem["CLOSED"]):?> style="display:none;"<?endif;?>>
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
							class="<?echo $ar["DISABLED"]? 'kombox-disabled': ''?><?echo $ar["CHECKED"]? ' kombox-checked': ''?>"
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
				<div class="kombox-filter-property-body ys-opt-labels kombox-combo" data-name="<?=$arItem["CODE_ALT"]?>"<?if($arItem["CLOSED"]):?> style="display:none;"<?endif;?>>
				<?foreach($arItem["VALUES"] as $val => $ar):?>
					<?komboxOtherValues($arItem, 'start');?>
					<label for="<?echo $ar["CONTROL_ID"]?>" class="checkbox lvl2<?echo $ar["DISABLED"]? ' kombox-disabled ': ''?><?echo $ar["CHECKED"]? ' kombox-checked': ''?>">
						<input 
							type="checkbox" 
							class="checkbox" 
							value="<?echo $ar["HTML_VALUE_ALT"]?>" 
							name="<?echo $arItem["CODE_ALT"]?>" 
							id="<?echo $ar["CONTROL_ID"]?>" 
							<?echo $ar["CHECKED"] ? 'checked="checked"' : ''?> 
							<?echo !$ar["CHECKED"] & $ar["DISABLED"] ? 'disabled="disabled"' : ''?> 
						/>
						<?echo $ar["VALUE"];?> <span class="kombox-cnt">(<?echo $ar["CNT"];?>)</span>
					</label>
					<br />
				<?endforeach;?>
				<?komboxOtherValues($arItem);?>
				</div>
			<?
			break;
			case 'RADIO':
			?>
				<div class="kombox-filter-property-body ys-opt-labels kombox-radio" data-name="<?=$arItem["CODE_ALT"]?>"<?if($arItem["CLOSED"]):?> style="display:none;"<?endif;?>>
					<?foreach($arItem["VALUES"] as $val => $ar):?>
					<?komboxOtherValues($arItem, 'start');?>
					<label class="lvl2<?echo $ar["DISABLED"]? ' kombox-disabled ': ''?><?echo $ar["CHECKED"]? ' kombox-checked': ''?>">
						<input
							type="radio" 
							class="radio" 
							value="<?echo $ar["HTML_VALUE_ALT"]?>" 
							name="<?echo $arItem["CODE_ALT"]?>" 
							id="<?echo $ar["CONTROL_ID"]?>" 
							<?echo $ar["CHECKED"]? 'checked="checked"': ''?> 
						/>
						<?echo $ar["VALUE"];?> <span class="kombox-cnt">(<?echo $ar["CNT"];?>)</span>
					</label>
					<br />
					<?endforeach;?>
					<?komboxOtherValues($arItem);?>
				</div>
			<?
			break;
			case 'LINK':
			?>
				<div class="kombox-filter-property-body ys-opt-labels kombox-link" data-name="<?=$arItem["CODE_ALT"]?>"<?if($arItem["CLOSED"]):?> style="display:none;"<?endif;?>>
					<?foreach($arItem["VALUES"] as $val => $ar):?>
						<?komboxOtherValues($arItem, 'start');?>
						<div class="lvl2<?echo $ar["DISABLED"]? ' kombox-disabled': ''?><?echo $ar["CHECKED"]? ' kombox-checked': ''?>">
							<?if($ar["CHECKED"]):?><input type="hidden" value="<?echo $ar["HTML_VALUE_ALT"]?>" name="<?echo $arItem["CODE_ALT"]?>" /><?endif;?>
							<a 
								href="<?echo $ar["HREF"]?>" 
								id="<?echo $ar["CONTROL_ID"]?>"
								data-value="<?echo $ar["HTML_VALUE_ALT"]?>" 
								data-name="<?echo $arItem["CODE_ALT"]?>" 
								<?echo $ar["CHECKED"]? 'data-checked="checked"': ''?> 
							>
								<?echo $ar["VALUE"];?>
								<span class="kombox-remove-link"></span>
							</a>
							<span class="kombox-cnt">(<?echo $ar["CNT"];?>)</span>
						</div>
					<?endforeach;?>
					<?komboxOtherValues($arItem);?>
				</div>
			<?
			break;
		}
	}
}

if(!function_exists('komboxOtherValues'))
{
	function komboxOtherValues($arItem, $action = 'end')
	{
		static $cnt = 0;
		$flag = $arItem['LAST_CHECKED_POS'] > intval($arItem["SETTINGS"]["VALUES_CNT"]);
		
		if($action == 'start')
		{
			$cnt++;
			if($cnt == intval($arItem["SETTINGS"]["VALUES_CNT"]) + 1 && intval($arItem["SETTINGS"]["VALUES_CNT"])):?>
				<div<?if(!$flag):?> style="display: none;"<?endif;?>>
			<?endif;
		}
		elseif($action == 'end')
		{
			if($cnt > intval($arItem["SETTINGS"]["VALUES_CNT"]) && intval($arItem["SETTINGS"]["VALUES_CNT"])):?>
				</div>
				<a class="ys-props-toggler ys-props-hide ys-props-more" href="#"<?if($flag):?> style="display: none;"<?endif;?>><?=GetMessage('MORE')?></a>
				<a class="ys-props-toggler ys-props-show ys-props-less" href="#"<?if(!$flag):?> style="display: none;"<?endif;?>><?=GetMessage('LESS')?></a>
			<?endif;
			$cnt = 0;
		}
	}
}
?>