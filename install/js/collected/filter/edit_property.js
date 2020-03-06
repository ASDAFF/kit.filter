/*
 * Copyright (c) 6/3/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

function loadCollectedEditPropertySettings(settings)
{
	//console.log(settings);
	BX.ajax.loadJSON(
		'/bitrix/admin/collected_edit_property_ajax.php',
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
					
					$('#collected_properties_view select').on('change', 
						function()
						{
							var $this = $(this);
							var val = $(this).val();
							
							if(val == 'SLIDER')
							{
								$('#collected_properties_slider_step').show();
								$('#collected_properties_slider_slider_units').show();
								$('#collected_properties_sort').hide();
								$('#collected_properties_sort_order').hide();
								
								$('#collected_properties_logic').hide();
								$('#collected_properties_logic select').val('OR');
							}
							else
							{
								$('#collected_properties_slider_step').hide();
								$('#collected_properties_slider_slider_units').hide();
								$('#collected_properties_sort').show();
								$('#collected_properties_sort_order').show();
								
								$checked = $('#PROPERTY_MULTIPLE_Y').checked();
								
								if($checked)
								{
									$('#collected_properties_logic').show();
								}
								else
								{
									$('#collected_properties_logic').hide();
									$('#collected_properties_logic select').val('OR');
								}
							}
							
							if(val == 'LIST')
							{
								$('#collected_properties_slider_list_size').show();
								$('#collected_properties_slider_list_multi').show();
							}
							else
							{
								$('#collected_properties_slider_list_size').hide();
								$('#collected_properties_slider_list_multi').hide();
							}
							
							if(val == 'CHECKBOX' || val == 'RADIO' || val == 'LINK')
							{
								$('#collected_properties_slider_values_cnt').show();
							}
							else
							{
								$('#collected_properties_slider_values_cnt').hide();
							}
						}
					);
					
					$('#collected_properties_slider_step select').on('change', 
						function()
						{
							var $this = $(this);
							var val = $(this).val();
							
							if(val == 'SET')
							{
								$('#collected_properties_slider_step input').show();
							}
							else
							{
								$('#collected_properties_slider_step input').val(0).hide();
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
								$view = $('#collected_properties_view select').val();
								
								if($view == 'SLIDER')
								{
									$('#collected_properties_logic').hide();
									$('#collected_properties_logic select').val('OR');
								}
								else
								{
									$('#collected_properties_logic').show();
								}
							}
							else
							{
								$('#collected_properties_logic').hide();
								$('#collected_properties_logic select').val('OR');
							}
						}
					);
				}
			}
		}
	);
}