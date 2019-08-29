$(function () {
	//	   ;(function(){ //移动端快速点击
	//		 var isTouch = ('ontouchstart' in document.documentElement) ? 'touchstart' : 'click', _on = $.fn.on;
	//		 $.fn.on = function(){
	//		 arguments[0] = (arguments[0] === 'click') ? isTouch: arguments[0];
	//			return _on.apply(this, arguments);
	//			};
	//		})();					

	//绑定移动端a：active效果
	document.body.addEventListener('touchstart', function () { });

	$(function () {
		//移动端快速点击,fastclick!
		window.addEventListener('load', function () {
			FastClick.attach(document.body);
		}, false);
	});


});