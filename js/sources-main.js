jQuery(document).ready(function() {
	
	//слайдеры
	var slider1 = jQuery('#slider1'),
		 slider2 = jQuery('#slider2');
	
	if (slider1 !== null) {
		slider1.owlCarousel({
			items: 1,
			loop: true,
			//autoplay: true,
			//autoplayTimeout: 5000,
			smartSpeed: 1000
		});
	}
	if (slider2 !== null) {
		slider2.owlCarousel({
			center: true,
			loop: true,
			nav: true,
			//autoplay: true,
			//autoplayTimeout: 5000,
			smartSpeed: 1000,
			responsive: {
				1920: {
					items: 1.9
				},
				1600: {
					items: 1.6
				},
				1030: {
					items: 1.4
				},
				768: {
					items: 1.2
				},
				320: {
					items: 1
				}
			}
		});
	}
	
	//плавная перемотка
	jQuery('a[href^="#_"]').click(function() {
		var target = jQuery(this).attr('href');
		jQuery('html, body').animate({scrollTop: jQuery(target).offset().top}, 800);
		return false;
	});
	
	//верхний бургер
	jQuery('#burger').click(function() {
		jQuery('#nav-menu').slideDown();
	});
	jQuery('#crest').click(function() {
		jQuery('#nav-menu').slideUp();
	});
	jQuery(document).mouseup(function(e) {
		var div = jQuery('#nav-menu'),
			 burger = jQuery('#burger');
		if (!div.is(e.target) 
				&& div.has(e.target).length === 0	
				&& div.is(':visible')
				&& burger.is(':visible')) {
			jQuery('#nav-menu').slideUp();
		}
	});
	jQuery(window).resize(function() {
		jQuery('#nav-menu').removeAttr('style');
	});
	
	//открытие подкатегорий
	jQuery('.view4-pict').click(function(e) {
		jQuery('.styles-wrap').slideToggle();
		e.preventDefault();
	});
	
	//стилизация инпутов
	jQuery('#file-field, #agree, .matrac-feat').styler();
	
	//маска ввода телефона
	var tel_number = jQuery('#tel_number');
	if (tel_number !== null) {
		tel_number.mask('(999) 999 99 99');
	}	
	//маска ввода телефона для модального окна
	var flag = 0;
	setInterval(function() {
		var display = jQuery('#order-call').css('display');
		if (flag == 0) {
			if (display == 'block') {
				jQuery('#tel-modal').mask('(999) 999 99 99');
				flag = 1;
			}
		}
	}, 200);

	//галерея товара
	jQuery('#thumbnail a').click(function(){
		jQuery('#large img').hide().attr({
			"src": jQuery(this).attr('href')
		});
		return false;
	});
	jQuery('#large img').load(function() {
		jQuery('#large img:hidden').fadeIn('slow');
	});
	
	//лайтбокс
	jQuery('a[data-rel^=lightcase]').lightcase();

	
	var files="";
	
	$("input[type=file]").on("change", function(){
		files = this.files;
		//console.log(this.files);
	});
	
	// Отправка запроса или обращения
	$("#order-form").submit(function(e) {
	
		//e.stopPropagation();
		e.preventDefault();
	
		var url 	 = document.location.href,
			index 	 = url.lastIndexOf("nolte-crimea.ru"),
			subUrl	 = url.substring(index + 15),
			indexSub = subUrl.lastIndexOf("/"),
			urlSend	 = "";	
	
		if (indexSub > 0) urlSend = "../send.php";
		else urlSend = "send.php";
		
		// создадим данные файлов в подходящем для отправки формате
		var data = new FormData();
		
		if (files!=""){
			$.each( files, function( key, value ){
				data.append( key, value );
			});
		}
		// добавим переменную идентификатор запроса
		data.append("my_file_upload", 1);
		data.append("action_type", "send-order");
		data.append("name", jQuery("input[name='name']").val());
		data.append("tel", jQuery("input[name='tel']").val());
		data.append("email", jQuery("input[name='email']").val());
		data.append("address", jQuery("textarea[name='address']").val());
		data.append("agree", jQuery("input[name='agree']").val());
		data.append("url", url);

		
		// AJAX запрос
		$.ajax({
			url         : urlSend,
			type        : 'POST',
			data        : data,
			cache       : false,
			dataType    : 'json',
			// отключаем обработку передаваемых данных, пусть передаются как есть
			processData : false,
			// отключаем установку заголовка типа запроса. Так jQuery скажет серверу что это строковой запрос
			contentType : false,
			// функция успешного ответа сервера
			success     : function( respond, status, jqXHR ){
				// ОК
				if( typeof respond.error === 'undefined' ){
					$("#order-form")[0].reset();
					$(".jq-file__name").html("");
					$(".thanks").modal("toggle");
				}
				// error
				else {
					console.log('ОШИБКА: ' + respond.error );
				}
			},
			// функция ошибки ответа сервера
			error: function( jqXHR, status, errorThrown ){
				console.log( 'ОШИБКА AJAX запроса: ' + status, jqXHR );
			}
		});

	});
	
	
	// Отправка запроса или обращения из главной страницы
	$("#order-form-main").submit(function(e) {
		//e.stopPropagation();
		e.preventDefault();

		// создадим данные файлов в подходящем для отправки формате
		var data = new FormData();
		
		// добавим переменную идентификатор запроса
		data.append("action_type", "send-order-main");
		data.append("name", jQuery("input[name='name']").val());
		data.append("tel", jQuery("input[name='tel']").val());
		data.append("email", jQuery("input[name='email']").val());
		data.append("url", "http://nolte-crimea.ru");

		// AJAX запрос
		$.ajax({
			url         : 'send.php',
			type        : 'POST',
			data        : data,
			cache       : false,
			dataType    : 'json',
			// отключаем обработку передаваемых данных, пусть передаются как есть
			processData : false,
			// отключаем установку заголовка типа запроса. Так jQuery скажет серверу что это строковой запрос
			contentType : false,
			// функция успешного ответа сервера
			success     : function( respond, status, jqXHR ){
				// ОК
				if( typeof respond.error === 'undefined' ){
					$("#order-form-main")[0].reset();
					$(".thanks").modal("toggle");
				}
				// error
				else {
					console.log('ОШИБКА: ' + respond.error );
				}
			},
			// функция ошибки ответа сервера
			error: function( jqXHR, status, errorThrown ){
				console.log( 'ОШИБКА AJAX запроса: ' + status, jqXHR );
			}
		});
		
	});	

});
