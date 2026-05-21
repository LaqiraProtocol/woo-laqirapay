(function($){

	"use strict";

	$( document ).ready(function() {

		/**
		* General Functions
		*/

		// Initialize SemanticUI Menu Functions

		//Whitelabel Logo
		$('#laqira-payments-logo-upload-btn-whitelabel').on('click', function(e) {
	       e.preventDefault();

	       var image = wp.media({ 
	           title: 'Upload Image',
	           multiple: false
	       }).open()
	       .on('select', function(e){
	           // This will return the selected image from the Media Uploader, the result is an object
	           var uploaded_image = image.state().get('selection').first();
	           // Convert uploaded_image to a JSON object 
	           var laqira_payments_image_url = uploaded_image.toJSON().url;
	           // Assign the url value to the input field
	           $('#laqira_payments_whitelabel_logo_setting').val(laqira_payments_image_url).trigger('change');
	       });
	   	});

   		//Whitelabel Icon
   		$('#laqira-payments-logo-upload-btn-whitelabelicon').on('click', function(e) {
   	       e.preventDefault();

   	       var image = wp.media({ 
   	           title: 'Upload Image',
   	           multiple: false
   	       }).open()
   	       .on('select', function(e){
   	           // This will return the selected image from the Media Uploader, the result is an object
   	           var uploaded_image = image.state().get('selection').first();
   	           // Convert uploaded_image to a JSON object 
   	           var laqira_payments_image_url = uploaded_image.toJSON().url;
   	           // Assign the url value to the input field
   	           $('#laqira_payments_whitelabel_icon_setting').val(laqira_payments_image_url).trigger('change');
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
                var $menuItems = $('#laqira_payments_admin_menu .item');
                $('#laqira_payments_admin_menu .item').tab({
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
                                previous.find('.laqira_payments_attached_content_wrapper').css('opacity', '0');
                                current.find('.laqira_payments_attached_content_wrapper').css('opacity', '0');
                                setTimeout(function(){
                                        previous.hide();
                                        current.show();
                                        setTimeout(function(){
                                                current.find('.laqira_payments_attached_content_wrapper').css('opacity', '1');
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

                var $adminForms = $('.laqira-payments-admin-form');
                $adminForms.each(function() {
                        var $form = $(this);
                        var $tabInput = $form.find('input[name="laqira_payments_current_tab_setting"]');
                        var formTab = $form.data('tab') || $form.closest('.ui.tab.segment').data('tab') || '';

                        if ($tabInput.length && !$tabInput.val()) {
                                $tabInput.val(formTab || defaultTabValue);
                        }

                        $form.on('submit', function() {
                                var activeItem = document.querySelector('#laqira_payments_admin_menu .item.active');
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

                if (window.laqira_payments && typeof window.laqira_payments.tables_language_option !== 'undefined') {
                        $('.laqira_payments_tables_language_option_setting').dropdown('set selected', window.laqira_payments.tables_language_option);
                }


		// Logo Upload
		$('#laqira-payments-upload-btn').on('click', function(e) {
	       e.preventDefault();

	       var image = wp.media({ 
	           title: 'Upload Image',
	           multiple: false
	       }).open()
	       .on('select', function(e){
	           // This will return the selected image from the Media Uploader, the result is an object
	           var uploaded_image = image.state().get('selection').first();
	           // Convert uploaded_image to a JSON object 
	           var laqira_payments_image_url = uploaded_image.toJSON().url;
	           // Assign the url value to the input field
	           $('#laqira_payments_logo_setting').val(laqira_payments_image_url);
	           $('.laqira_payments_email_preview_logo').attr('src', laqira_payments_image_url);
	       });
	   	});

		// Favicon Upload
		$('#laqira-payments-upload-btn-favicon').on('click', function(e) {
	       e.preventDefault();

	       var image = wp.media({ 
	           title: 'Upload Image',
	           multiple: false
	       }).open()
	       .on('select', function(e){
	           // This will return the selected image from the Media Uploader, the result is an object
	           var uploaded_image = image.state().get('selection').first();
	           // Convert uploaded_image to a JSON object 
	           var laqira_payments_image_url = uploaded_image.toJSON().url;
	           // Assign the url value to the input field
	           $('#laqira_payments_logo_favicon_setting').val(laqira_payments_image_url);
	           $('.laqira_payments_email_preview_logo').attr('src', laqira_payments_image_url);
	       });
	   	});

	   	// LaqiraPayments agent ID and agent dropdown settings are mutually exclusive
	   	$('input[name="laqira_payments_enable_agent_id_registration_dropdown_setting"]').on('change', function(){
	   		let checked = $(this).is(':checked');
	   		if (checked == true){
	   			// deactivate other setting
	   			$('input[name="laqira_payments_enable_agent_id_registration_setting"]').prop( "checked", false );
	   		}
	   	});
	   	$('input[name="laqira_payments_enable_agent_id_registration_setting"]').on('change', function(){
	   		let checked = $(this).is(':checked');
	   		if (checked == true){
	   			// deactivate other setting
	   			$('input[name="laqira_payments_enable_agent_id_registration_dropdown_setting"]').prop( "checked", false );
	   		}
	   	});

	   	// check if license activation
	   	const urlParams = new URLSearchParams(window.location.search);
	   	const myParam = urlParams.get('tab');
	   	if (myParam === 'activate'){
	   		$('.laqira_payments_license').click();
	   	}

	});

})(jQuery);
