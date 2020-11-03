
/*
 * Copyright (c) 6/3/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

$(document).ready(function(){	
	$.kitInherit(
		'kitHorizontalSmartFilter', 
		$.kitSmartFilter, 
		{
			options: { 
				columns: 3
			},
			
			init: function(wrapper, options){
				$.KitSmartFilter.prototype.init.call(this, wrapper, options);
				
				if(this.options.columns <= 0)
					this.options.columns = 3;
				$('.kit-column', this.wrapper).css('width', (100 / this.options.columns) + '%');
				
				this.recalculateColumns();
			},
			
			recalculateColumns: function(){
				var _this = this;
				var cnt = 0;
				$('.kit-column', this.wrapper).css('clear', '').not('.kit-hide').each(function(){
					cnt++;
					if(cnt == _this.options.columns + 1)
					{
						$(this).css('clear', 'both');
						cnt = 0;
					}
				});
			},
			
			initToggleProperties: function()
			{
				var _this = this;
				$('.kit-filter-show-properties a', _this.wrapper).on('click', function(){
					var contaner = $('.kit-filter-show-properties', _this.wrapper);
					if(contaner.hasClass('kit-show')){
						$('.kit-closed', _this.wrapper).show().addClass('kit-hide');
						contaner.addClass('kit-hide').removeClass('kit-show');
						$.cookie('kit-filter-closed', false, { path: '/' });
					}
					else
					{
						$('.kit-closed', _this.wrapper).hide().removeClass('kit-hide');
						contaner.addClass('kit-show').removeClass('kit-hide');
						$.cookie('kit-filter-closed', true, { path: '/' });
					}
					return false;
				});
				
				if($.cookie('kit-filter-closed') != 'false'){
					$('.kit-closed', _this.wrapper).hide().addClass('kit-hide');
					$('.kit-filter-show-properties', _this.wrapper).addClass('kit-show').removeClass('kit-hide');
				}
				else{
					$('.kit-closed', _this.wrapper).show().removeClass('kit-hide');
					$('.kit-filter-show-properties', _this.wrapper).addClass('kit-hide').removeClass('kit-show');
				}
					
				this.recalculateColumns();
			}
		}
	);
});