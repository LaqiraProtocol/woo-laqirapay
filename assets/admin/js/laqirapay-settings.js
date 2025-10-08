(function($){

	"use strict";

	$( document ).ready(function() {

		/**
		* General Functions
		*/

		// Initialize SemanticUI Menu Functions

		//Whitelabel Logo
		$('#laqirapay-logo-upload-btn-whitelabel').on('click', function(e) {
	       e.preventDefault();

	       var image = wp.media({ 
	           title: 'Upload Image',
	           multiple: false
	       }).open()
	       .on('select', function(e){
	           // This will return the selected image from the Media Uploader, the result is an object
	           var uploaded_image = image.state().get('selection').first();
	           // Convert uploaded_image to a JSON object 
	           var laqirapay_image_url = uploaded_image.toJSON().url;
	           // Assign the url value to the input field
	           $('#laqirapay_whitelabel_logo_setting').val(laqirapay_image_url).trigger('change');
	       });
	   	});

   		//Whitelabel Icon
   		$('#laqirapay-logo-upload-btn-whitelabelicon').on('click', function(e) {
   	       e.preventDefault();

   	       var image = wp.media({ 
   	           title: 'Upload Image',
   	           multiple: false
   	       }).open()
   	       .on('select', function(e){
   	           // This will return the selected image from the Media Uploader, the result is an object
   	           var uploaded_image = image.state().get('selection').first();
   	           // Convert uploaded_image to a JSON object 
   	           var laqirapay_image_url = uploaded_image.toJSON().url;
   	           // Assign the url value to the input field
   	           $('#laqirapay_whitelabel_icon_setting').val(laqirapay_image_url).trigger('change');
   	       });
   	   	});

		// radio buttons
		$('.ui.checkbox').checkbox();
		
                // Tab transition effect
                var $tabSegments = $('.ui.tab.segment');
                var previous = $tabSegments.filter('.active');
                if (!previous.length && $tabSegments.length) {
                        previous = $tabSegments.first();
                }
                // Cache the menu items so Semantic UI initialises every tab trigger (all three sections).
                var $menuItems = $('#laqirapay_admin_menu .item');
                $('#laqirapay_admin_menu .item').tab({
                        onVisible: function () {
                                var current = $('.ui.tab.segment.active');
                                if (!current.length) {
                                        return;
                                }
                                if (!previous.length) {
                                        previous = current;
                                }
                                // hide the current and show the previous, so that we can animate them
                                previous.show();
                                current.hide();

                                // hide the previous tab - once this is done, we can show the new one
                                previous.find('.laqirapay_attached_content_wrapper').css('opacity', '0');
                                current.find('.laqirapay_attached_content_wrapper').css('opacity', '0');
                                setTimeout(function(){
                                        previous.hide();
                                        current.show();
                                        setTimeout(function(){
                                                current.find('.laqirapay_attached_content_wrapper').css('opacity', '1');
                                                // remember the current tab for next change
                                                previous = current;
                                        },10);
                                },150);

                        }
                });

                $('.ui.dropdown').dropdown();
	
		$('.message .close').on('click', function() {
		    $(this).closest('.message').transition('fade');
		});

                // On Submit (Save Settings), Get Current Tab and Pass The Tab as a Setting.
                // Keep a fallback to the first tab so the form still remembers the correct
                // section even if no menu item is currently marked as active.
                var defaultTabValue = '';
                if ($menuItems.length) {
                        defaultTabValue = $menuItems.first().data('tab') || '';
                }

                var $adminForms = $('.laqirapay-admin-form');
                $adminForms.each(function() {
                        var $form = $(this);
                        var $tabInput = $form.find('input[name="laqirapay_current_tab_setting"]');
                        var formTab = $form.data('tab') || $form.closest('.ui.tab.segment').data('tab') || '';

                        if ($tabInput.length && !$tabInput.val()) {
                                $tabInput.val(formTab || defaultTabValue);
                        }

                        $form.on('submit', function() {
                                var activeItem = document.querySelector('#laqirapay_admin_menu .item.active');
                                var tabValue = formTab || defaultTabValue;

                                if (activeItem && activeItem.dataset && activeItem.dataset.tab) {
                                        tabValue = activeItem.dataset.tab;
                                }

                                if (!tabValue) {
                                        tabValue = defaultTabValue;
                                }

                                if ($tabInput.length) {
                                        $tabInput.val(tabValue);
                                }

                                return true;
                        });
                });

                if (window.laqirapay && typeof window.laqirapay.tables_language_option !== 'undefined') {
                        $('.laqirapay_tables_language_option_setting').dropdown('set selected', window.laqirapay.tables_language_option);
                }


		// Logo Upload
		$('#laqirapay-upload-btn').on('click', function(e) {
	       e.preventDefault();

	       var image = wp.media({ 
	           title: 'Upload Image',
	           multiple: false
	       }).open()
	       .on('select', function(e){
	           // This will return the selected image from the Media Uploader, the result is an object
	           var uploaded_image = image.state().get('selection').first();
	           // Convert uploaded_image to a JSON object 
	           var laqirapay_image_url = uploaded_image.toJSON().url;
	           // Assign the url value to the input field
	           $('#laqirapay_logo_setting').val(laqirapay_image_url);
	           $('.laqirapay_email_preview_logo').attr('src', laqirapay_image_url);
	       });
	   	});

		// Favicon Upload
		$('#laqirapay-upload-btn-favicon').on('click', function(e) {
	       e.preventDefault();

	       var image = wp.media({ 
	           title: 'Upload Image',
	           multiple: false
	       }).open()
	       .on('select', function(e){
	           // This will return the selected image from the Media Uploader, the result is an object
	           var uploaded_image = image.state().get('selection').first();
	           // Convert uploaded_image to a JSON object 
	           var laqirapay_image_url = uploaded_image.toJSON().url;
	           // Assign the url value to the input field
	           $('#laqirapay_logo_favicon_setting').val(laqirapay_image_url);
	           $('.laqirapay_email_preview_logo').attr('src', laqirapay_image_url);
	       });
	   	});

	   	// LaqiraPay agent ID and agent dropdown settings are mutually exclusive
	   	$('input[name="laqirapay_enable_agent_id_registration_dropdown_setting"]').on('change', function(){
	   		let checked = $(this).is(':checked');
	   		if (checked == true){
	   			// deactivate other setting
	   			$('input[name="laqirapay_enable_agent_id_registration_setting"]').prop( "checked", false );
	   		}
	   	});
	   	$('input[name="laqirapay_enable_agent_id_registration_setting"]').on('change', function(){
	   		let checked = $(this).is(':checked');
	   		if (checked == true){
	   			// deactivate other setting
	   			$('input[name="laqirapay_enable_agent_id_registration_dropdown_setting"]').prop( "checked", false );
	   		}
	   	});

	   	// check if license activation
	   	const urlParams = new URLSearchParams(window.location.search);
	   	const myParam = urlParams.get('tab');
	   	if (myParam === 'activate'){
	   		$('.laqirapay_license').click();
	   	}

	});

})(jQuery);
