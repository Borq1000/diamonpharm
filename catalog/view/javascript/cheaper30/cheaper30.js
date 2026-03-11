$(document).ready(function() {
	ajaxCheaperSetting();
});
function ajaxCheaper(href, _this){
	$('body > .divshadow').remove();
	$('body').prepend('<div class="divshadow"></div>');
	$('body').append('<div class="modal-bg-cheaper"></div>');
	$.ajax({
		url: href,
		type:'get',
		beforeSend: function(){
			$(_this).button('loading');
		},
		complete: function(){
			$(_this).button('reset');
		},
		success: function(msg){
			//setTimeout(function() {
			$('body .divshadow').empty();
			$('body .divshadow').append(msg);
			if ($('body .divshadow').hasClass("show")) {
				$('body .divshadow').removeClass('show');
			} else {
				$('body .divshadow').addClass('show');
				$('.modal-bg').addClass('show');
			}
			blurCheaper();
			//},700);
		}
	});
}
function ajaxCheaperSetting(){
	var module_id = '';
	$('button[onclick^=\'ajaxCheaper\'].cheapering').each(function(){
		module_id = '&module_id=' + $(this).attr('data-module_id');
		var _this = $(this);
		$.ajax({
			url: 'index.php?route=extension/module/cheaper30/jsonsettings' + module_id,
			type: 'post',
			dataType: 'json',
			beforeSend: function(){},
			success: function(json){
				if (json['cheaper30_h1']){
					_this.text(json['cheaper30_h1']).addClass('show_button');
					if (json['product'] == 1){
						hrefButton(_this);
					}
				} else {
					_this.remove();
				}
			}
		});
	});
	
	
	
	
		
}
function blurCheaper() {
	$('.modal-bg-cheaper').addClass('show');
	$('body .divshadow').removeClass('animated').removeClass('bounceOut').removeClass('bounceIn').addClass('animated bounceIn');
}
function centering(diving){
	var wsize = windowWorkSize(),
	testElem = $(diving),
	testElemWid =  testElem.outerWidth(),
	testElemHei =  testElem.outerHeight(),
	toPing = wsize[1]/2 - testElemHei/2 + (document.body.scrollTop || document.documentElement.scrollTop);
	if (toPing < 0) {toPing = 100;}
			
	testElem.css('top', toPing + 'px');

	function windowWorkSize(){
	var wwSize = new Array();
		if (window.innerHeight !== undefined) {wwSize= [window.innerWidth,window.innerHeight]} else {
			wwSizeIE = (document.body.clientWidth) ? document.body : document.documentElement; 
			wwSize= [wwSizeIE.clientWidth, wwSizeIE.clientHeight];
		};
		
		return wwSize;
	};
}

