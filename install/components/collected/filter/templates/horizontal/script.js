
/*
 * Copyright (c) 6/3/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

$(document).ready(function(){	
	$.collectedInherit(
		'collectedHorizontalSmartFilter', 
		$.collectedSmartFilter, 
		{
			options: { 
				columns: 3
			},
			
			init: function(wrapper, options){
				$.CollectedSmartFilter.prototype.init.call(this, wrapper, options);
				
				if(this.options.columns <= 0)
					this.options.columns = 3;
				$('.collected-column', this.wrapper).css('width', (100 / this.options.columns) + '%');
				
				this.recalculateColumns();
			},
			
			recalculateColumns: function(){
				var _this = this;
				var cnt = 0;
				$('.collected-column', this.wrapper).css('clear', '').not('.collected-hide').each(function(){
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
				$('.collected-filter-show-properties a', _this.wrapper).on('click', function(){
					var contaner = $('.collected-filter-show-properties', _this.wrapper);
					if(contaner.hasClass('collected-show')){
						$('.collected-closed', _this.wrapper).show().addClass('collected-hide');
						contaner.addClass('collected-hide').removeClass('collected-show');
						$.cookie('collected-filter-closed', false, { path: '/' });
					}
					else
					{
						$('.collected-closed', _this.wrapper).hide().removeClass('collected-hide');
						contaner.addClass('collected-show').removeClass('collected-hide');
						$.cookie('collected-filter-closed', true, { path: '/' });
					}
					return false;
				});
				
				if($.cookie('collected-filter-closed') != 'false'){
					$('.collected-closed', _this.wrapper).hide().addClass('collected-hide');
					$('.collected-filter-show-properties', _this.wrapper).addClass('collected-show').removeClass('collected-hide');
				}
				else{
					$('.collected-closed', _this.wrapper).show().removeClass('collected-hide');
					$('.collected-filter-show-properties', _this.wrapper).addClass('collected-hide').removeClass('collected-show');
				}
					
				this.recalculateColumns();
			}
		}
	);
});