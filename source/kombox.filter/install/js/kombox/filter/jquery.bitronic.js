$(document).ready(function(){
	if(jQuery().uniform)
		$("#ys_filter_bitronic label > input.checkbox, #ys_filter_bitronic label > input.radio").uniform();
		
	if(jQuery().selectBox)
		$("#ys_filter_bitronic select").selectBox('destroy').selectBox();
	
	$.komboxInherit(
		'komboxBitronicSmartFilter', 
		$.komboxSmartFilter, 
		{
			options: { 
				ajax_enable: 'N',
				cfajaxURL: false,
				site_id: false,
				iblock_id: false
			},
			
			init: function(wrapper, options){
				if (window.location.hash != '') {
					if(!(window.history && history.pushState))
					{
						var uri = window.location.hash.replace('#', '');
						window.location.href = document.location.pathname + uri;
					}
				}

				$.KomboxSmartFilter.prototype.init.call(this, wrapper, options);
				
				var bSef = this.form.data('sef') == 'yes';
				
				if(bSef)
					window.komboxSefUrl = this.getSefUrl();
				else
					window.komboxSefUrl = '';
					
				this.isBitronic = false;
				this.change = false;
				
				var bitronicAjaxFunctionName = "YScatalogLoading"; // set in bitrix\templates\bitronic_XXX\static\js\ajax.js
				var getType = {};
				// if exist function YScatalogLoading
				if ( window[bitronicAjaxFunctionName] && getType.toString.call(window[bitronicAjaxFunctionName]) === '[object Function]')
					this.isBitronic = true;
					
				if(this.isBitronic)
				{
					var _this = this;
					$('a.showchild', this.form).on('click', function(){
						_this.form.data('clicked', 'set_filter');
						_this.submitForm();
						return false;
					});
					
					$(window).on("popstate", function() {
						
					});
					
					this.form.on('click', 'a#del_filter', function() {
						_this.form.data('clicked', 'del_filter');
						_this.submitForm();
						return false;
					});
					
					this.form.on('click', '.kombox-link .lvl2 a', function() {
						var $this = $(this);
						var lvl2 = $this.closest('.lvl2');
						
						if(lvl2.hasClass('kombox-disabled') && !lvl2.hasClass('kombox-checked'))
							return false;
							
						if($this.data('checked') == 'checked'){
							$this.data('checked', '');
							lvl2.removeClass('kombox-checked');
						}
						else{
							$this.data('checked', 'checked');
							lvl2.addClass('kombox-checked');
						}
							
						_this.form.data('clicked', 'set_filter');
						_this.form.find('#set_filter').removeClass('disabled');
						_this.submitForm();
						return false;
					});
				}
			},
			
			initHints: function()
			{
				var _this = this;

				if(jQuery().tooltip) {
					$('.kombox-filter-property-hint', _this.wrapper).each(function(){
						var $this = $(this);
						var hint = $this.next();
						if(hint.length)
						{
							var hintHtml = hint.html();
							$this.attr('title', '');
							$this.data('title', hintHtml);
							hint.remove();
						}
					});
					
					$('.kombox-filter-property-hint', _this.wrapper).tooltip({
						position: {
							my: "center bottom-5",
							at: "center top",
						},
						content: function () {
							return $(this).data('title');
						}
					});
				}
				else
				{
					$.KomboxSmartFilter.prototype.initHints.call(this);
				}
			},
			
			reload: function(input){
				this.input = input;
				this.change = true;
				
				if(this.form.length)
				{
					var values = new Array;
					values[0] = {name: 'ajax', value: 'y'};
					
					this.gatherInputsValues(values, this.form.find('input, select, .kombox-link .lvl2 a'));
					
					if(this.options.ajax_enable == "N") 
					{
						this.loaderObj = $("#set_filter span.notloader");
						this.startButtonLoader();
						BX.ajax.loadJSON(
							this.options.ajaxURL,
							this.values2post(values),
							BX.delegate(this.postHandler, this)
						);
					}
					else
					{
						this.loaderObj = $(".f_loader");
						this.catalogLoading(values);
					}
				}
			},
			
			submitForm: function(){
				var form = this.form;
				
				if(form.data('clicked') == 'set_filter')
				{
					if(form.find('#set_filter').hasClass('disabled'))
						return false;
				}
				
				this.change = false;
				
				if(this.isBitronic)
				{
					var values = new Array;
					
					values[0] = {name: 'ajax', value: 'y'};
						
					if(form.data('clicked') == 'del_filter')
					{
						form.find('input[type=text]').val('');
						
						var checkboxes = form.find('input[type=checkbox], input[type=radio]');
						checkboxes.prop('checked', false);
						checkboxes.closest('span.checked').removeClass('checked');
						
						form.find('select').each(function(){
							var select = $(this);
							select.find('option').prop('selected', false).removeAttr('selected');
							select.find('option:first').prop('selected', true).attr('selected', 'selected');
							select.val('');
							select.selectBox('refresh');
						});
						
						form.find('.kombox-link .lvl2 a').each(function(){
							var link = $(this);
							var lvl2 = link.closest('.lvl2');
							lvl2.removeClass('kombox-checked');
							link.data('checked', '');
						});
						
						form.find('.kombox-range div').ionRangeSlider("reset");
					}
					
					if(this.form.length)
					{
						this.gatherInputsValues(values, this.form.find('input, select, .kombox-link .lvl2 a'));
						
						this.loaderObj = $(".f_loader");
						this.catalogLoading(values);
					}
					return false;
				}
				else
				{
					return $.KomboxSmartFilter.prototype.submitForm.call(this);
				}
			},
			
			catalogLoading: function(values) {
				var _this = this;
				
				_this.startButtonLoader();

				var params = {
					'ys_filter_ajax':'y',
					'site_id':_this.options.site_id,
					'iblock_id':_this.options.iblock_id,
					'set_filter':'y'
				};

				params = _this.updateUrl(params, values);
				
				var param_str = "ys_filter_ajax=y&site_id="+_this.options.site_id+"&iblock_id="+_this.options.iblock_id
				var param_str2 = "ys_filter_ajax=y&amp;site_id="+_this.options.site_id+"&amp;iblock_id="+_this.options.iblock_id
				
				var ys_param = $('input[name="ys-folder-url"], [name="ys-request-uri"], [name="ys-script-name"]');
				if(ys_param.size()>0)
					ys_param.each(function() {
						params[$(this).prop('name')] = $(this).val();
					});
					
				$.post(_this.options.cfajaxURL, params, function(data) {

					_this.stopLoader();
					_this.hideLoader();
					
					var regFilter = /<\!--START KOMBOX_SMART_FILTER-->([\s\S]*?)<\!--END KOMBOX_SMART_FILTER-->/gim;
					var strFilter = regFilter.exec(data);
					if(strFilter != null)
					{
						var jsonFilter = BX.parseJSON(strFilter[1]);
						_this.postHandler(jsonFilter);
					}

					var re = new RegExp("[?]("+param_str+"[&])", "gi");
					var data = data.replace(re, "");

					var re = new RegExp("([?]"+param_str+")", "gi");
					var data = data.replace(re, "");
					
					var re = new RegExp("[&]("+param_str+"[&])", "gi");
					var data = data.replace(re, "&");

					var re = new RegExp("[?]("+param_str2+"&amp;)", "gi");
					var data = data.replace(re, "?");
					
					var re = new RegExp("(/bitrix/templates/.+/ajax/catalog_filter.php)", "gi");
					var data = data.replace(re, "");
					
					var curPath = window.location.pathname;
					var re = /view-\d+\//;
					var curPath = curPath.replace(re, "");
					var re = /sort-\d+\//;
					var curPath = curPath.replace(re, "");
					var re = /page_count-\d+\//;
					var curPath = curPath.replace(re, "");
					var re = /page-\d+\//;
					var curPath = curPath.replace(re, "");
					var re = new RegExp("(/bitrix/templates/.+/ajax/)", "gi");
					var data = data.replace(re, curPath);
					
					var parent = $("<div>"+data+"</div>")
					
					parent.find("input.checkbox, input.radio").uniform();
					$("#container > .ys_article").find("select").selectBox('destroy');

					$("#container > .ys_article > table.abcd").html(parent.find("table.abcd").html())
					$("#container > .ys_article > div > div > div > table.abcd").html(parent.find("table.abcd").html())
					$("#container > .ys_article > form[name='sort_form']").html(parent.find("form[name='sort_form']").html())
					$("#container > .ys_article > div.catalog").html(parent.find("div.catalog").html())
					$("#container > .ys_article > div > div > div > div.catalog").html(parent.find("div.catalog").html())
					$("#container > .ys_article > div.pager-block").html(parent.find("div.pager-block").html())
					$("#container > .ys_article > div > div > div > div.pager-block").html(parent.find("div.pager-block").html())

					$("#container > .ys_article").find("select").selectBox();

						/*slider popup
						 -------------------------------------------------------*/
					$("#container > .ys_article").find('.sl_wrapper li, .catalog-list li').hover(function () {
						$(this).find('.item-popup').show();
						$(this).css({'z-index': 50});

						$(this).find('.item-popup').addClass('item-hover');
					}, function () {
						var openedMenu = $(this).find('.selectBox-menuShowing');

						if (openedMenu.length != 1) {

							$(this).find('.item-popup').fadeOut();
							$(this).css({'z-index': 1});

							$(this).find('.item-popup').removeClass('item-hover');
						}
					});

						
					var minh = 0;
					$("#container > .ys_article").find('.catalog-list li').each(function(){
						if(minh == 0 || $(this).height() > minh) {
							minh = $(this).height();
						}
					});

					$("#container > .ys_article").find('.catalog-list li').css('height', minh + 'px');
					
					parent.html("<div>" + data + "</div>"); // for execute JS in data
				});
			},
		
			updateUrl: function(params, values){
				var form = this.form;
				var url = "";
				var bSef = form.data('sef') == 'yes';
				
				if(bSef){
					var arParts = window.location.pathname.split("/");
					var baseurl = '/';
					var add = true;
					$.each(arParts, function(key, part){
						if(part == 'filter')
							add = false;
						if(add && part.length)
							baseurl += part + '/';
					});

					var sefUrl = this.getSefUrl();
					window.komboxSefUrl = sefUrl;
					
					//url = baseurl;
					
					if(sefUrl.length){
						url += 'filter/' + sefUrl;
					}
					
					if(window.location.search)
						url += window.location.search;
					
				}

				values.forEach(function(entry) {
					if(typeof params[entry.name] != 'undefined' && !$.isArray(params[entry.name])){
						var first = params[entry.name];
						params[entry.name] = new Array;
						params[entry.name].push(first);
					}
					
					if($.isArray(params[entry.name]))
						params[entry.name].push(entry.value);
					else
						params[entry.name] = entry.value;
					
					if(entry.name != 'ajax' && entry.name != 'SECTION_CODE' && !bSef){
						if(url.length)url += '&';
						url += (entry.name + '=' + entry.value);
					}
				});
				
				if(!bSef)
				{
					if(url.length){
						url = '?' + url;
					}
					else{
						url = window.location.pathname;
					}
				}
				
				try {
					if(bSef)
					{
						url = baseurl + url;
						$('[name="ys-request-uri"]').val(url);
						history.pushState(null, null, window.location.origin + url);
					}
					else
					{
						history.pushState(null, null, url);
					}
					return params;
				} 
				catch(e) {}
				location.hash = '#' + url;
				return params;
			},
			
			postHandler: function(result){
				var form = this.form;
				
				if(this.options.ajax_enable == "N") 
				{
					this.stopLoader();
				}

				if(result.ITEMS)
				{
					for(var PID in result.ITEMS)
					{
						var arItem = result.ITEMS[PID];
						if(arItem.VALUES)
						{
							for(var i in arItem.VALUES)
							{
								var ar = arItem.VALUES[i];
								var control = $('#' + ar.CONTROL_ID);
								if(control.length)
								{
									if(ar.DISABLED && !ar.CHECKED)
										control.prop('disabled', true);
									else
										control.prop('disabled', false);
										
									if(arItem.SETTINGS.VIEW == 'LIST' || arItem.SETTINGS.VIEW == 'SELECT')
									{
										if(ar.DISABLED && !ar.CHECKED)
											control.attr('disabled', 'disabled');
										else
											control.removeAttr('disabled');
											
										if(ar.CHECKED)
											control.attr('selected', 'selected');
										else
											control.removeAttr('selected');
									}	
								}
							}
						}
					}
				}
				
				if(result.SET_FILTER || this.change)
				{
					form.find('#set_filter').removeClass('disabled');
				}
				else if(!this.change)
				{
					form.find('#set_filter').addClass('disabled');
				}
				
				$.KomboxSmartFilter.prototype.postHandler.call(this, result);
				
				form.find('select').selectBox('refresh');
				
				this.hideLoader();
			},
			
			startButtonLoader: function (){
				var _this = this;
				_this.loaderObj.VALUE = _this.loaderObj.val();
				_this.loaderObj.WAIT_STATUS = true;
				_this.loaderObj.SYMBOLS = ['0', '1', '2', '3', '4', '5', '6', '7'];
				_this.loaderObj.WAIT_START = 0;
				_this.loaderObj.WAIT_CURRENT = _this.loaderObj.WAIT_START;
				_this.loaderObj.Rate = 10;
				_this.loaderObj.WAIT_FUNC = function(){
					if(_this.loaderObj.WAIT_STATUS)
					{
						_this.loaderObj.css('font-family', 'WebSymbolsLigaRegular');
						_this.loaderObj.siblings('span.text').removeClass('show').addClass('hide');
						_this.loaderObj.html(_this.loaderObj.SYMBOLS[_this.loaderObj.WAIT_CURRENT]);
						_this.loaderObj.WAIT_CURRENT = _this.loaderObj.WAIT_CURRENT < _this.loaderObj.SYMBOLS.length-1 ? _this.loaderObj.WAIT_CURRENT + 1 : _this.loaderObj.WAIT_START;
						setTimeout(_this.loaderObj.WAIT_FUNC, 1000 / _this.loaderObj.Rate);
					}
					else
						_this.loaderObj.removeClass('loader').parent().prop("disabled", false).removeClass('active').removeClass('disable');
				};
				
				_this.loaderObj.addClass('loader').parent().prop("disabled", true).addClass('active').addClass('disable');
				this.showLoader();
				_this.loaderObj.WAIT_FUNC();
			},
			
			stopLoader: function(){
				this.loaderObj.WAIT_STATUS = false;
			},

			showLoader: function(){
				if(this.options.ajax_enable == "N") 
					$(".loader").fadeIn(100);
				else
					$(".f_loader").fadeIn(100);
			},

			hideLoader: function(){
				if(this.options.ajax_enable == "N") 
					$(".loader").fadeOut(500);
				else
					$(".f_loader").fadeOut(500);
			},
			
			showModef: function(result){
				if(this.options.ajax_enable == "N") 
				{
					$.KomboxSmartFilter.prototype.showModef.call(this, result);
				}
			}
		}
	);
	
	$.komboxInherit(
		'komboxBitronicHorizontalSmartFilter', 
		$.komboxBitronicSmartFilter, 
		{
			catalogLoading: function(values) {
				var _this = this;
				
				_this.startButtonLoader();

				var params = {
					'ys_filter_ajax':'y',
					'site_id':_this.options.site_id,
					'iblock_id':_this.options.iblock_id,
					'set_filter':'y'
				};

				params = _this.updateUrl(params, values);
				
				var param_str = "ys_filter_ajax=y&site_id="+_this.options.site_id+"&iblock_id="+_this.options.iblock_id
				var param_str2 = "ys_filter_ajax=y&amp;site_id="+_this.options.site_id+"&amp;iblock_id="+_this.options.iblock_id
				
				var ys_param = $('input[name="ys-folder-url"], [name="ys-request-uri"], [name="ys-script-name"]');
				if(ys_param.size()>0)
					ys_param.each(function() {
						params[$(this).prop('name')] = $(this).val();
					});
					
				$.post(_this.options.cfajaxURL, params, function(data) {

					_this.stopLoader();
					_this.hideLoader();
					
					var regFilter = /<\!--START KOMBOX_SMART_FILTER-->([\s\S]*?)<\!--END KOMBOX_SMART_FILTER-->/gim;
					var strFilter = regFilter.exec(data);
					if(strFilter != null)
					{
						var jsonFilter = BX.parseJSON(strFilter[1]);
						_this.postHandler(jsonFilter);
					}

					var re = new RegExp("[?]("+param_str+"[&])", "gi");
					var data = data.replace(re, "");

					var re = new RegExp("([?]"+param_str+")", "gi");
					var data = data.replace(re, "");
					
					var re = new RegExp("[&]("+param_str+"[&])", "gi");
					var data = data.replace(re, "&");

					var re = new RegExp("[?]("+param_str2+"&amp;)", "gi");
					var data = data.replace(re, "?");
					
					var re = new RegExp("(/bitrix/templates/.+/ajax/catalog_filter.php)", "gi");
					var data = data.replace(re, "");
					
					var curPath = window.location.pathname;
					var re = /view-\d+\//;
					var curPath = curPath.replace(re, "");
					var re = /sort-\d+\//;
					var curPath = curPath.replace(re, "");
					var re = /page_count-\d+\//;
					var curPath = curPath.replace(re, "");
					var re = /page-\d+\//;
					var curPath = curPath.replace(re, "");
					var re = new RegExp("(/bitrix/templates/.+/ajax/)", "gi");
					var data = data.replace(re, curPath);
					
					var parent = $("<div>"+data+"</div>")
					
					parent.find("input.checkbox, input.radio").uniform();
					$("#container > div.content").find("select").selectBox('destroy');

					$("#container > div.content > table.abcd").html(parent.find("table.abcd").html())
					$("#container > div.content > div > div > div > table.abcd").html(parent.find("table.abcd").html())
					$("#container > div.content > form[name='sort_form']").html(parent.find("form[name='sort_form']").html())
					$("#container > div.content > div.catalog").html(parent.find("div.catalog").html())
					$("#container > div.content > div > div > div > div.catalog").html(parent.find("div.catalog").html())
					$("#container > div.content > div.pager-block").html(parent.find("div.pager-block").html())
					$("#container > div.content > div > div > div > div.pager-block").html(parent.find("div.pager-block").html())

					$("#container > div.content").find("select").selectBox();

						/*slider popup
						 -------------------------------------------------------*/
					$("#container > div.content").find('.sl_wrapper li, .catalog-list li').hover(function () {
						$(this).find('.item-popup').show();
						$(this).css({'z-index': 50});

						$(this).find('.item-popup').addClass('item-hover');
					}, function () {
						var openedMenu = $(this).find('.selectBox-menuShowing');

						if (openedMenu.length != 1) {

							$(this).find('.item-popup').fadeOut();
							$(this).css({'z-index': 1});

							$(this).find('.item-popup').removeClass('item-hover');
						}
					});

						
					var minh = 0;
					$("#container > div.content").find('.catalog-list li').each(function(){
						if(minh == 0 || $(this).height() > minh) {
							minh = $(this).height();
						}
					});

					$("#container > div.content").find('.catalog-list li').css('height', minh + 'px');
					
					parent.html("<div>" + data + "</div>"); // for execute JS in data
				});
			},
			
			showModef: function(result){
				if(this.options.ajax_enable == "N") 
				{
					var modef = BX('modef');
					var modef_num = BX('modef_num');

					if(modef && modef_num) {
						if(parseInt(result.ELEMENT_COUNT))
						{
							modef_num.innerHTML = result.ELEMENT_COUNT;
							var hrefFILTER = BX.findChildren(modef, {tag: 'A'}, true);

							if(result.FILTER_URL && hrefFILTER)
								hrefFILTER[0].href = BX.util.htmlspecialcharsback(result.FILTER_URL);

							if(result.FILTER_AJAX_URL && result.COMPONENT_CONTAINER_ID)
							{
								BX.bind(hrefFILTER[0], 'click', function(e)
								{
									var url = BX.util.htmlspecialcharsback(result.FILTER_AJAX_URL);
									BX.ajax.insertToNode(url, result.COMPONENT_CONTAINER_ID);
									return BX.PreventDefault(e);
								});
							}

							if (result.INSTANT_RELOAD && result.COMPONENT_CONTAINER_ID) {
								var url = BX.util.htmlspecialcharsback(result.FILTER_AJAX_URL);
								BX.ajax.insertToNode(url, result.COMPONENT_CONTAINER_ID);
							}else {
								if(modef.style.display == 'none') {
									modef.style.display = 'block';
								}
							}
						}
						else
						{
							modef.style.display = 'none';
						}
					}
				}
			}
		}
	);
	
	$('.ys-props-toggler').off('click').on('click', function() {
		var prev = $(this).prev(),
			next = $(this).next();

		if ($(this).hasClass('ys-props-hide')) {
			prev.animate({height: 'show'}, 300);
			$(this).addClass('ys-props-show').removeClass('ys-props-hide');

			if ($(this).hasClass('ys-props-more')) {
				$(this).hide();
				next.show();

				next.removeClass('ys-props-hide').addClass('ys-props-show');
			}

		} else {
			prev.prev().animate({height: 'hide'}, 300);
			$(this).addClass('ys-props-hide').removeClass('ys-props-show');

			if ($(this).hasClass('ys-props-less')) {
				$(this).hide();
				prev.show();

				prev.removeClass('ys-props-show').addClass('ys-props-hide');
			}
		}
		return false;
	});
});