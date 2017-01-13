jQuery(document).ready(function($){
   var new_point = '';
	function google_autocomplete_init(){
		var short_name = '';
		var options = {
	      	types: ['(cities)']
	    };

	    autocompletes = new google.maps.places.Autocomplete(document.getElementById('sp_google_autocomplete'), options);
	    google.maps.event.addListener(autocompletes, 'place_changed', function() {
	        var place = autocompletes.getPlace();
	        if (place.address_components.length == 4) {
	        	short_name = place.address_components[3].short_name;
	        } else {
	        	short_name = place.address_components[2].short_name;
	        }
	        new_point = {
	        	short_name_country : short_name,
	        	place_id : place.place_id
	        }
	        for ( i = 0; i < place.address_components.length; i++ ) {
	            if ( place.address_components[i].types[0] == 'locality' ) {
	                locality = place.address_components[i].long_name;
	                $('#sp_google_autocomplete').val(locality);
	            }
	        }
	    });
	}
	  //  init autocomplete
	google.maps.event.addDomListener(window, 'load', google_autocomplete_init);

	$.get("//ipinfo.io", function (data) {
		$.ajax({
	        url: ajaxurl,
	        type: 'POST',
	        dataType: 'json',
	        data: {
	         	'action': 'woocommerce_geo_info',
	            'city' : data.city,
	            'country' : data.country,
	        }
	  	})	
	}, "jsonp");
	

	$('body').on('click', '.setting_price_options.setting_price_tab.active', function(e) {
		e.preventDefault();
		$('#setting_price_product_data').css('display', 'block');
	})

	$('#add_price').keydown(function (e) {
		if (event.keyCode==13) {
			e.preventDefault();
			save_price();
		}
	})

	$('body').on('click', '#save_choose', function (e) {
		e.preventDefault();
		save_price();
	})

	function save_price() {
		var error = false;
		if ($('#sp_google_autocomplete').val() != '') {
            var pattern = /^[а-яА-ЯёЁa-zA-Z0-9.,-?!№%:;'"`*()\s]+$/i;
            if(!pattern.test($('#sp_google_autocomplete').val())){
                $('#sp_google_autocomplete').css('border', '1px solid #ff0000');
                $('#valid_field_autocomplete').text('Используются недопустимые символы!');
                error = true;
            }	
        } else {
            $('#sp_google_autocomplete').css('border', '1px solid #ff0000');
            $('#valid_field_autocomplete').css('right', '60px');
            $('#valid_field_autocomplete').text('Поле не должно быть пустым');
            error = true;
        }
        if ($('#add_price').val() != '') {
            if ($('#add_price').val() > 0) {
                $('#add_price').css('border', '1px solid #569b44');
            } else {
                $('#add_price').css('border', '1px solid #ff0000');
                $('#valid_field_price').text('Цена не может быть ниже 1!');
                error = true;
            }
        } else {
            $('#add_price').css('border', '1px solid #ff0000');
            $('#valid_field_price').text('Поле не должно быть пустым');
            error = true;
        }
        if ($('#add_sale_price').val() != '') {
            if ($('#add_sale_price').val() > 0) {
                $('#add_sale_price').css('border', '1px solid #569b44');
            } else {
                $('#add_sale_price').css('border', '1px solid #ff0000');
                $('#valid_field_sale_price').text('Цена не может быть ниже 1!');
                error = true;
            }
            if ($('#add_sale_price').val() > $('#add_price').val()) {
            	$('#add_sale_price').css('border', '1px solid #ff0000');
                $('#valid_field_sale_price').text('Цена не может быть выше установленной!');
                error = true;
            } else {
            	$('#add_sale_price').css('border', '1px solid #569b44');
            }
        }
        if (error == false) {
			$.ajax({
		        url: ajaxurl,
		        type: 'POST',
		        dataType: 'json',
		        data: {
		            'action': 'woocommerce_saving_price',
		            'city' : $('#sp_google_autocomplete').val(),
		            'price' : $('#add_price').val(),
		            'post_id' : $('#post_ID').val(),
		            'sale_price' : $('#add_sale_price').val(),
		            'new_point' : new_point
		        },
		        success: function (data) {
		            if(data.res == 'true') {
		              	$('#sp_google_autocomplete').css('border', '1px solid #569b44');
		            	$('#sp_google_autocomplete').val('');
		            	$('#valid_field_autocomplete').text('');
		            	$('#add_price').val('');
		            	$('#valid_field_price').text('');
		            	$('#add_sale_price').val('');
		            	$('#valid_field_sale_price').text('');
		            	if ( data.show_set_price != '' ) {
		            		$('#setting_price_product_data').append('<div id="show_option_add_price">' + data.show_set_price + '</div>');
		            	}
		              	$('.dropdown-menu').prepend('<li>' + data.html + '</li>');
		            } else {
		            	$('#sp_google_autocomplete').css('border', '1px solid #ff0000');
		            	$('#valid_field_autocomplete').css('right', '6px');
               			$('#valid_field_autocomplete').text('Цена для этого города уже установлена!');
		            }
		        }
	      	})	
        }
	}

	$('body').on('click', '#show_price', function (e) {
		e.preventDefault();
		if ($('#set_price').css('display') == 'none') {
			$('#set_price').show();
			$('.btn').removeClass('blue');
			$('.btn').css('color', 'black');
			$('.caret').css({
				borderTop: '0',
				borderBottom: '9px solid',
			    borderRight: '9px solid transparent',
			    borderLeft: '9px solid transparent',
			});
		} else {
			$('#set_price').hide();
			$('.btn').addClass('blue');
			$('.btn').css('color', '#0073aa');
			$('.btn.blue').hover(
				function(){
					$('.btn.blue').css('color', '#00a0d2');
				},
				function(){
				 	$('.btn.blue').css('color', '#0073aa');
				}
			);
			$('.caret').css({
				borderBottom: '0',
				borderTop: '9px solid',
			    borderRight: '9px solid transparent',
			    borderLeft: '9px solid transparent',
			});
		}
	})

	$('body').on('click', '.delete_price_city', function (e) {
		e.preventDefault();
		var name = $(this).prev().prev().attr('name');
		var val = $(this).prev().prev();
		var delete_city = $(this).parent().parent();
		if (name == 'show_sale_price') {
			if (val.attr('value') == '') {
				return false;
			}
		}
		$.ajax({
	        url: ajaxurl,
	        type: 'POST',
	        dataType: 'json',
	        data: {
	           'action' : 'woocommerce_delete_price',
	           'id' : $(this).attr('data-id'),
	           'post_id' : $('#post_ID').val(),
	           'name' : name,
	        },
	        success: function (data) {
           		if (data == 'true') {
           			delete_city.remove();
	           	} else if (data == 'clean') {
       				val.attr('value', '')
	           	} else {
					$('#show_option_add_price').remove();
	           	}
	        }
      	})	
	})

	$('body').on('click', '.update_city_price', function (e) {
		e.preventDefault();
		var save_price = $(this).parent().find('span');
		var name = $(this).prev().attr('name');
		var val = $(this).prev();
		if (name == 'show_sale_price') {
			if (val.attr('value') > $(this).parent().parent().find('.show_set_price').val()) {
				$(this).parent().find('.show_sale_price').css('border', '1px solid #ff0000');
				save_price.css('color', '#ff0000')
				save_price.text('Цена не может быть выше установленной!')
				return false;
			}
		}
		$.ajax({
	        url: ajaxurl,
	        type: 'POST',
	        dataType: 'json',
	        data: {
	           'action' : 'woocommerce_update_price',
	           'id' : $(this).attr('data-id'),
	           'price' : $(this).prev().attr('value'),
	           'post_id' : $('#post_ID').val(),
	           'name' : name,
	        },
	        success: function (data) {
	            if (data == true) {
	            	val.css('border', '1px solid #569b44')
					save_price.css('color', '#569b44');
					save_price.text('Сохранено!');
	            }
	        }
      	})	
	})

	$('body').on('click', '#pagination_option', function (e) {
	    e.preventDefault();
	    var id = $(this).attr('rel');
	    $.ajax({
	        url: ajaxurl,
	        type: 'POST',
	        dataType: 'json',
	        data: {
	           'action': 'woocommerce_pagination_option',
	           'id': id,
	           'post_id' : $('#post_ID').val(),
	        },
	        success: function (data) {
	        	$('.pagination').remove();
	        	$('.dropdown-menu').text('');
	        	for (var i = 0; i < data.res.length; i++) {
	        		$('.dropdown-menu').append('<li><p class="form-field show_set_price_field"><label for="show_set_price"><b>Цена для:</b> <i> ' + data.res[i]["city"] + ' </i></label><input type="number" class="short show_set_price" name="show_set_price" value= ' + data.res[i]["price"] + ' placeholder=" step="1" min="0"><button class="button button-primary save_price update_city_price" data-id= ' + data.res[i]["id"] + '> Сохранить </button><button class="button button-primary delete_price_city" data-id=' + data.res[i]["id"] + '> Удалить </button><span class="button_save"></span></p><p class="form-field show_set_price_field"><label for="show_sale_price"><b>Цена распродажи: </b></label><input type="number" class="short show_sale_price" name="show_sale_price" value= ' + data.res[i]["sale_price"] + ' placeholder=" step="1" min="0"><button class="button button-primary save_price update_city_price" data-id= ' + data.res[i]["id"] + '> Сохранить </button><button class="button button-primary delete_price_city" data-id=' + data.res[i]["id"] + '> Очистить </button><span class="button_save_sale"></span></p><hr></li>');
	        	}
	            $('.dropdown-menu').append(data.html);
	        }
      	})
    })
})
