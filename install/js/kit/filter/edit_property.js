/*
 * Copyright (c) 6/3/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

function loadKitEditPropertySettings(settings)
{
	//console.log(settings);
	BX.ajax.loadJSON(
		'/bitrix/admin/kit_edit_property_ajax.php',
		settings,
		function(data){
			if(data.HTML)
			{
				$html = $(data.HTML);
				
				$contaner = $('#form_content table.edit-table > tbody');
				
				if(!$contaner.length){
					$contaner = $('#edit1_edit_table > tbody');
				}
				
				if($contaner.length){
					$contaner.append($html);
					
					$('#kit_properties_view select').on('change', 
						function()
						{
							var $this = $(this);
							var val = $(this).val();
							
							if(val == 'SLIDER')
							{
								$('#kit_properties_slider_step').show();
								$('#kit_properties_slider_slider_units').show();
								$('#kit_properties_sort').hide();
								$('#kit_properties_sort_order').hide();
								
								$('#kit_properties_logic').hide();
								$('#kit_properties_logic select').val('OR');
							}
							else
							{
								$('#kit_properties_slider_step').hide();
								$('#kit_properties_slider_slider_units').hide();
								$('#kit_properties_sort').show();
								$('#kit_properties_sort_order').show();
								
								$checked = $('#PROPERTY_MULTIPLE_Y').checked();
								
								if($checked)
								{
									$('#kit_properties_logic').show();
								}
								else
								{
									$('#kit_properties_logic').hide();
									$('#kit_properties_logic select').val('OR');
								}
							}
							
							if(val == 'LIST')
							{
								$('#kit_properties_slider_list_size').show();
								$('#kit_properties_slider_list_multi').show();
							}
							else
							{
								$('#kit_properties_slider_list_size').hide();
								$('#kit_properties_slider_list_multi').hide();
							}
							
							if(val == 'CHECKBOX' || val == 'RADIO' || val == 'LINK')
							{
								$('#kit_properties_slider_values_cnt').show();
							}
							else
							{
								$('#kit_properties_slider_values_cnt').hide();
							}
						}
					);
					
					$('#kit_properties_slider_step select').on('change', 
						function()
						{
							var $this = $(this);
							var val = $(this).val();
							
							if(val == 'SET')
							{
								$('#kit_properties_slider_step input').show();
							}
							else
							{
								$('#kit_properties_slider_step input').val(0).hide();
							}
						}
					);
					
					$('#PROPERTY_MULTIPLE_Y').on('change',
						function()
						{
							var $this = $(this);
							var checked = $(this).prop('checked');
							
							if(checked)
							{
								$view = $('#kit_properties_view select').val();
								
								if($view == 'SLIDER')
								{
									$('#kit_properties_logic').hide();
									$('#kit_properties_logic select').val('OR');
								}
								else
								{
									$('#kit_properties_logic').show();
								}
							}
							else
							{
								$('#kit_properties_logic').hide();
								$('#kit_properties_logic select').val('OR');
							}
						}
					);
				}
			}
		}
	);
}