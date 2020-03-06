function loadKomboxEditPropertySettings(settings)
{
	//console.log(settings);
	BX.ajax.loadJSON(
		'/bitrix/admin/kombox_edit_property_ajax.php',
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
					
					$('#kombox_properties_view select').on('change', 
						function()
						{
							var $this = $(this);
							var val = $(this).val();
							
							if(val == 'SLIDER')
							{
								$('#kombox_properties_slider_step').show();
								$('#kombox_properties_slider_slider_units').show();
								$('#kombox_properties_sort').hide();
								$('#kombox_properties_sort_order').hide();
								
								$('#kombox_properties_logic').hide();
								$('#kombox_properties_logic select').val('OR');
							}
							else
							{
								$('#kombox_properties_slider_step').hide();
								$('#kombox_properties_slider_slider_units').hide();
								$('#kombox_properties_sort').show();
								$('#kombox_properties_sort_order').show();
								
								$checked = $('#PROPERTY_MULTIPLE_Y').checked();
								
								if($checked)
								{
									$('#kombox_properties_logic').show();
								}
								else
								{
									$('#kombox_properties_logic').hide();
									$('#kombox_properties_logic select').val('OR');
								}
							}
							
							if(val == 'LIST')
							{
								$('#kombox_properties_slider_list_size').show();
								$('#kombox_properties_slider_list_multi').show();
							}
							else
							{
								$('#kombox_properties_slider_list_size').hide();
								$('#kombox_properties_slider_list_multi').hide();
							}
							
							if(val == 'CHECKBOX' || val == 'RADIO' || val == 'LINK')
							{
								$('#kombox_properties_slider_values_cnt').show();
							}
							else
							{
								$('#kombox_properties_slider_values_cnt').hide();
							}
						}
					);
					
					$('#kombox_properties_slider_step select').on('change', 
						function()
						{
							var $this = $(this);
							var val = $(this).val();
							
							if(val == 'SET')
							{
								$('#kombox_properties_slider_step input').show();
							}
							else
							{
								$('#kombox_properties_slider_step input').val(0).hide();
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
								$view = $('#kombox_properties_view select').val();
								
								if($view == 'SLIDER')
								{
									$('#kombox_properties_logic').hide();
									$('#kombox_properties_logic select').val('OR');
								}
								else
								{
									$('#kombox_properties_logic').show();
								}
							}
							else
							{
								$('#kombox_properties_logic').hide();
								$('#kombox_properties_logic select').val('OR');
							}
						}
					);
				}
			}
		}
	);
}