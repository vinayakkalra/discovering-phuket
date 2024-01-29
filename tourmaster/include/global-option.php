<?php
	/*	
	*	Tourmaster Plugin
	*	---------------------------------------------------------------------
	*	creating the plugin option
	*	---------------------------------------------------------------------
	*/

	// return the custom stylesheet path
	if( !function_exists('tourmaster_global_get_style_custom') ){
		function tourmaster_global_get_style_custom($local = false){

			$upload_dir = wp_upload_dir();
			$filename = '/tourmaster-global-style-custom.css';
			$local_file = $upload_dir['basedir'] . $filename;
			
			if( $local ){
				return $local_file;
			}else{
				if( file_exists($local_file) ){
					$filemtime = filemtime($local_file);

					if( is_ssl() ){
						$upload_dir['baseurl'] = str_replace('http://', 'https://', $upload_dir['baseurl']);
					}
					return $upload_dir['baseurl'] . $filename . '?' . $filemtime;
				}else{
					return TOURMASTER_URL . '/global-style-custom.css';
				}
			}
		}
	}

	// add margin at the bottom
	add_filter('tourmaster_plugin_option_top_file_write', 'tourmaster_plugin_option_top_file_write', 10, 2);
	if( !function_exists('tourmaster_plugin_option_top_file_write') ){ 
		function tourmaster_plugin_option_top_file_write( $ret, $slug ){

			if( $slug != 'tourmaster_admin_option' ) return $ret;

			$general = get_option('tourmaster_general', array());

			if( !empty($general['item-padding']) ){
				$item_margin_bottom = 2 * intval(str_replace('px', '', $general['item-padding']));
			}else{
				$item_margin_bottom = 30;
			}
			$ret .= '.tourmaster-item-mgb{ margin-bottom: ' . $item_margin_bottom . 'px; } ';
			$ret .= '.tourmaster-tour-category-grid-4 .tourmaster-tour-category-item-wrap .tourmaster-tour-category-thumbnail{ margin-left: -' . $general['item-padding'] . '; margin-right: -' . $general['item-padding'] . '; margin-bottom: -' . $item_margin_bottom . 'px; }';

			if( !empty($general['item-padding']) ){
				$margin = 2 * intval(str_replace('px', '', $general['item-padding']));
				if( !empty($margin) && is_numeric($margin) ){
					$css .= '.cania-item-mgb, .gdlr-core-item-mgb{ margin-bottom: ' . $margin . 'px; }';

					$margin -= 1;
					$css .= '.tourmaster-body .tourmaster-tour-item .gdlr-core-flexslider.gdlr-core-with-outer-frame-element .flex-viewport, '; 
					$css .= '.tourmaster-body .tourmaster-room-item .gdlr-core-flexslider.gdlr-core-with-outer-frame-element .flex-viewport{ '; 
					$css .= 'padding-top: ' . $margin . 'px; margin-top: -' . $margin . 'px; padding-right: ' . $margin . 'px; margin-right: -' . $margin . 'px; ';
					$css .= 'padding-left: ' . $margin . 'px; margin-left: -' . $margin . 'px; padding-bottom: ' . $margin . 'px; margin-bottom: -' . $margin . 'px; ';
					$css .= '}';
				}
			}


			return $ret;
		}
	}

	add_action('after_setup_theme', 'tourmaster_global_init_admin_option');
	if( !function_exists('tourmaster_global_init_admin_option') ){ 
		function tourmaster_global_init_admin_option(){
			if( is_admin() || is_customize_preview() ){
				$tourmaster_option = new tourmaster_admin_option(array(
					'page-title' => esc_html__('Tourmaster Global Settings', 'tourmaster'),
					'menu-title' => esc_html__('Tourmaster Global Settings', 'tourmaster'),
					'slug' => 'tourmaster_global_admin_option', 
					'filewrite' => tourmaster_global_get_style_custom(true),
					'position' => 119
				));

				// general
				$tourmaster_option->add_element(array(
					'title' => esc_html__('General', 'tourmaster'),
					'slug' => 'tourmaster_general',
					'icon' => TOURMASTER_URL . '/images/plugin-options/general.png',
					'options' => array(

						'feature-settings' => array(
							'title' => esc_html__('Feature Settings', 'tourmaster'),
							'options' => array(
								'enable-tour' => array(
									'title' => esc_html__('Enable Tour', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable'
								),
								'enable-room' => array(
									'title' => esc_html__('Enable Room', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable'
								)	
							)
						),
						'general-settings' => array(
							'title' => esc_html__('General Settings', 'tourmaster'),
							'options' => array(
								'container-width' => array(
									'title' => esc_html__('Container Width', 'tourmaster'),
									'type' => 'text',
									'data-type' => 'pixel',
									'data-input-type' => 'pixel',
									'default' => '1180px',
									'selector' => '.tourmaster-container{ max-width: #gdlr#; margin-left: auto; margin-right: auto; }' 
								),
								'container-padding' => array(
									'title' => esc_html__('Container Padding', 'tourmaster'),
									'type' => 'text',
									'data-type' => 'pixel',
									'data-input-type' => 'pixel',
									'default' => '15px',
									'selector' => '.tourmaster-container{ padding-left: #gdlr#; padding-right: #gdlr#; }'
								),
								'item-padding' => array(
									'title' => esc_html__('Item Padding', 'tourmaster'),
									'type' => 'text',
									'data-type' => 'pixel',
									'data-input-type' => 'pixel',
									'default' => '15px',
									'selector' => '.tourmaster-item-pdlr{ padding-left: #gdlr#; padding-right: #gdlr#; }'  . 
										'.tourmaster-item-mglr{ margin-left: #gdlr#; margin-right: #gdlr#; }' .
										'.tourmaster-item-rvpdlr{ margin-left: -#gdlr#; margin-right: -#gdlr#; }'
								),
								'datepicker-date-format' => array(
									'title' => esc_html__('Datepicker Date Format', 'tourmaster'),
									'type' => 'text',
									'default' => 'd M yy',
									'description' => esc_html__('See more details about the date format here. http://api.jqueryui.com/datepicker/#utility-formatDate', 'tourmaster')
								),
								'top-bar-login-style' => array(
									'title' => esc_html__('Top Bar Login Style', 'tourmaster'),
									'type' => 'combobox',
									'options' => array(
										'style-1' => esc_html__('Style 1', 'tourmaster'),
										'style-2' => esc_html__('Style 2', 'tourmaster')
									)
								)
							)
						),
						'money-format' => array(
							'title' => esc_html__('Money Format', 'tourmaster'),
							'options' => array(
								'currency-code' => array(
									'title' => esc_html__('Default Currency Code', 'tourmaster'),
									'type' => 'text',
									'default' => 'USD',
									'description' => esc_html__('Use for multicurrency feature.', 'tourmaster')
								),
								'money-format' => array(
									'title' => esc_html__('Money Format', 'tourmaster'),
									'type' => 'text',
									'default' => '$NUMBER',
									'description' => esc_html__('Fill the format of your currency before or after the "NUMBER" string.', 'tourmaster')
								),
								'price-breakdown-decimal-digit' => array(
									'title' => esc_html__('Price Breakdown Decimal Digit', 'tourmaster'),
									'type' => 'text',
									'default' => '2',
									'description' => esc_html__('Fill only number here', 'tourmaster')
								),
								'price-thousand-separator' => array(
									'title' => esc_html__('Price Thousand Separator', 'tourmaster'),
									'type' => 'text',
									'default' => ',',
								),
								'price-decimal-separator' => array(
									'title' => esc_html__('Price Decimal Separator', 'tourmaster'),
									'type' => 'text',
									'default' => '.',
								),
								'currency-conversion-fee' => array(
									'title' => esc_html__('Currency Conversion Fee (%)', 'tourmaster'),
									'type' => 'text',
									'default' => '',
									'description' => esc_html__('Only fill number here', 'tourmaster')
								),
								'currencies' => array(
									'title' => esc_html__('Additional Currencies', 'tourmaster'),
									'type' => 'custom',
									'item-type' => 'tabs',
									'wrapper-class' => 'gdlr-core-fullsize',
									'options' => array(
										'title' => array(
											'title' => esc_html__('Currency Code', 'tourmaster'),
											'type' => 'text',
										),
										'money-format' => array(
											'title' => esc_html__('Money Format', 'tourmaster'),
											'type' => 'text'
										),
										'exchange-rate' => array(
											'title' => esc_html__('Exchange Rate (fill decimal number here)', 'tourmaster'),
											'type' => 'text',
											'description' => esc_html__('if you leave the field blank, the system will apply live conversion rate from third party source (rate updated once a day)', 'tourmaster')
										),
									),
								)
							)
						),
						'user-page' => array(
							'title' => esc_html__('User / Template', 'tourmaster'),
							'options' => array(
								'enable-recaptcha' => array(
									'title' => esc_html__('Enable Google Recaptcha', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'disable',
									'description' => wp_kses(__('Have to install the <a href="https://wordpress.org/plugins/google-captcha/" target="_blank" >google captcha plugin</a> first.', 'tourmaster'), array('a'=>array('href'=>array(), 'target'=>array()))) . 
										'<br><br>' . esc_html__('Enable this option will removes all lightbox login/registration out.', 'tourmaster')
								),
								'enable-membership' => array(
									'title' => esc_html__('Enable Membership', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable'
								),
								'login-lightbox-style' => array(
									'title' => esc_html__('Login/Register Form Style', 'tourmaster'),
									'type' => 'combobox',
									'options' => array( 
										'style-1' => esc_html__('Style 1', 'tourmaster'),
										'style-2' => esc_html__('Style 2', 'tourmaster'),
									)
								),
								'login-page' => array(
									'title' => esc_html__('Login Page', 'tourmaster'),
									'type' => 'combobox',
									'options' => tourmaster_get_post_list('page', true),
									'description' => esc_html__('Choose the page to use header / footer of that page as template. Select "None" to use homepage settings.', 'tourmaster'),
									'condition' => array( 'enable-membership' => 'enable' )
								),
								'register-page' => array(
									'title' => esc_html__('Register Page', 'tourmaster'),
									'type' => 'combobox',
									'options' => tourmaster_get_post_list('page', true),
									'description' => esc_html__('Choose the page to use header / footer of that page as template. Select "None" to use homepage settings.', 'tourmaster'),
									'condition' => array( 'enable-membership' => 'enable' )
								),
								'register-term-of-service-page' => array(
									'title' => esc_html__('Term Of Service ( Registration ) Page', 'tourmaster'),
									'type' => 'combobox',
									'options' => tourmaster_get_post_list('page', true),
									'condition' => array( 'enable-membership' => 'enable' )
								),
								'register-privacy-statement-page' => array(
									'title' => esc_html__('Privacy Statement ( Registration ) Page', 'tourmaster'),
									'type' => 'combobox',
									'options' => tourmaster_get_post_list('page', true),
									'condition' => array( 'enable-membership' => 'enable' )
								),
								'user-page' => array(
									'title' => esc_html__('User Page', 'tourmaster'),
									'type' => 'combobox',
									'options' => tourmaster_get_post_list('page', true),
									'description' => esc_html__('Choose the page to use header / footer of that page as template. Select "None" to use homepage settings.', 'tourmaster'),
									'condition' => array( 'enable-membership' => 'enable' )
								),
								'user-page-style' => array(
									'title' => esc_html__('User Page Style', 'tourmaster'),
									'type' => 'combobox',
									'options' => array(
										'style-1' => esc_html__('Style 1', 'tourmaster'),
										'style-2' => esc_html__('Style 2', 'tourmaster')
									)
								),
								'user-navigation-bottom-text' => array(
									'title' => esc_html__('User Navigation Bottom Text', 'tourmaster'),
									'type' => 'textarea',
									'condition' => array( 'enable-membership' => 'enable' )
								),
								'user-default-country' => array(
									'title' => esc_html__('User Default Country', 'tourmaster'),
									'type' => 'combobox',
									'options' => tourmaster_get_country_list(true)
								),
								'mobile-login-link' => array(
									'title' => esc_html__('Change Mobile Login/Register (From Lightbox) To Link', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'disable',
									'condition' => array( 'enable-membership' => 'enable' )
								),
							)
						),
						'payment-page' => array(
							'title' => esc_html__('Payment Page', 'tourmaster'),
							'options' => array(
								'enable-guest-booking' => array(
									'title' => esc_html__('Enable Guest Booking', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable'
								),
								'enable-booking-via-email' => array(
									'title' => esc_html__('Enable Booking Via Email ( For Guest )', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable',
									'description' => esc_html__('Guest user will still be able pay without logging in.', 'tourmaster'),
									'condition' => array( 'enable-guest-booking' => 'enable' )
								),
								'payment-page' => array(
									'title' => esc_html__('Payment Page', 'tourmaster'),
									'type' => 'combobox',
									'options' => tourmaster_get_post_list('page', true),
									'description' => esc_html__('Choose the page to use header / footer of that page as template. Select "None" to use homepage settings.', 'tourmaster')
								),
							)
						),
						'registration-email' => array(
							'title' => esc_html__('Registration E-Mail', 'tourmaster'),
							'options' => array(
								'enable-registration-complete-mail' => array(
									'title' => esc_html__('Enable Registration Complete E-Mail', 'tourmaster'),
									'type' => 'checkbox'
								),
								'registration-complete-mail-title' => array(
									'title' => esc_html__('Registration Complete E-Mail Title', 'tourmaster'),
									'type' => 'text',
									'condition' => array( 'enable-registration-complete-mail' => 'enable' )
								),
								'registration-complete-mail' => array(
									'title' => esc_html__('Registration Complete E-Mail', 'tourmaster'),
									'type' => 'textarea',
									'wrapper-class' => 'tourmaster-fullsize',
									'condition' => array( 'enable-registration-complete-mail' => 'enable' )
								),
								'admin-registration-email-address' => array(
									'title' => esc_html__('Admin Registration E-Mail Address', 'tourmaster'),
									'type' => 'text',
									'description' => esc_html__('Fill the admin email here to submit the notification upon completing the user registration process. Leave this field blank to use the same mail as "Booking Email Address"')
								),
								'enable-admin-registration-complete-mail' => array(
									'title' => esc_html__('Enable Admin Registration Complete E-Mail', 'tourmaster'),
									'type' => 'checkbox'
								),
								'admin-registration-complete-mail-title' => array(
									'title' => esc_html__('Admin Registration Complete E-Mail Title', 'tourmaster'),
									'type' => 'text',
									'default' => "New user registration",
									'condition' => array('enable-admin-registration-complete-mail' => 'enable')
								),
								'admin-registration-complete-mail' => array(
									'title' => esc_html__('Admin Registration Complete E-Mail', 'tourmaster'),
									'type' => 'textarea',
									'default' => "<strong>Dear Admin,</strong> \n New customer has created an account \n\n Customer’s name : {customer-name} \n Customer’s email : {customer-email} \n Customer’s contact number : {customer-phone}",
									'wrapper-class' => 'tourmaster-fullsize', 
									'condition' => array('enable-admin-registration-complete-mail' => 'enable')
								),
							)
						)
					)
				));

				// color
				$tourmaster_option->add_element(array(
					'title' => esc_html__('Color', 'tourmaster'),
					'slug' => 'tourmaster_color',
					'icon' => TOURMASTER_URL . '/images/plugin-options/color.png',
					'options' => array(

						'tourmaster-general' => array(
							'title' => esc_html__('Tourmaster General', 'tourmaster'),
							'options' => array(
								'tourmaster-theme-color' => array(
									'title' => esc_html__('Tourmaster Theme Color', 'tourmaster'),
									'type' => 'colorpicker',
									'data-type' => 'rgba',
									'selector' => 
'.tourmaster-body .tourmaster-user-breadcrumbs span.tourmaster-active{ color: #gdlr#; }' .
'.tourmaster-user-content-block .tourmaster-user-content-title{ color: #gdlr#; }' .
'.tourmaster-notification-box, .tourmaster-user-update-notification{ background: #gdlr#; }' . 
'body a.tourmaster-button, body a.tourmaster-button:hover, body a.tourmaster-button:active, body a.tourmaster-button:focus, ' .
'body input[type="button"].tourmaster-button, body input[type="button"].tourmaster-button:hover, body input[type="submit"].tourmaster-button, body input[type="submit"].tourmaster-button:hover{ background-color: #gdlr#; }' .
'.goodlayers-payment-form form input.goodlayers-payment-button[type="submit"], .goodlayers-payment-form form button{ background-color: #gdlr#; }' .
'.tourmaster-body .tourmaster-pagination a:hover, .tourmaster-body .tourmaster-pagination a.tourmaster-active, .tourmaster-body .tourmaster-pagination span{ background-color: #gdlr#; }' .
'.tourmaster-body .tourmaster-filterer-wrap a:hover, .tourmaster-body .tourmaster-filterer-wrap a.tourmaster-active{ color: #gdlr#; }' . 
'.tourmaster-template-wrapper-user .tourmaster-my-booking-filter a:hover, ' .
'.tourmaster-template-wrapper-user .tourmaster-my-booking-filter a.tourmaster-active{ color: #gdlr#; } ' .
'.tourmaster-user-template-style-2 .tourmaster-my-booking-filter a:hover, ' .
'.tourmaster-user-template-style-2 .tourmaster-my-booking-filter a.tourmaster-active{ border-color: #gdlr#; }' . 
'table.tourmaster-my-booking-table a.tourmaster-my-booking-action{ background: #gdlr#; } ' . 
'.tourmaster-user-content-inner-my-booking-single .tourmaster-my-booking-single-title, ' .
'.tourmaster-user-review-table .tourmaster-user-review-action{ color: #gdlr#; }' . 
'.tourmaster-review-form .tourmaster-review-form-title{ color: #gdlr#; }' . 
'.tourmaster-wish-list-item .tourmaster-wish-list-item-title, ' .
'.tourmaster-wish-list-item .tourmaster-wish-list-item-title:hover{ color: #gdlr#; }' . 
'.tourmaster-body .ui-datepicker table tr td a.ui-state-active, .tourmaster-body .ui-datepicker table tr td a:hover, ' .
'.tourmaster-body .ui-datepicker table tr td.tourmaster-highlight a, ' .
'.tourmaster-body .ui-datepicker table tr td.tourmaster-highlight span{ background: #gdlr#; } ' .
'.tourmaster-body .ui-datepicker select{ color: #gdlr# } ' .
'.tourmaster-form-field .tourmaster-combobox-wrap:after{ color: #gdlr#; } ' .
'.tourmaster-login-form .tourmaster-login-lost-password a, ' .
'.tourmaster-login-form .tourmaster-login-lost-password a:hover, ' .
'.tourmaster-login-bottom .tourmaster-login-bottom-link, ' .
'.tourmaster-register-bottom .tourmaster-register-bottom-link{ color: #gdlr#; }' . 
'.tourmaster-tour-search-item .tourmaster-type-filter-more-button{ color: #gdlr#; }' . 
'.tourmaster-payment-method-wrap .tourmaster-payment-paypal > img:hover, .tourmaster-payment-method-wrap .tourmaster-payment-credit-card > img:hover{ border-color: #gdlr#; }' . 
'.tourmaster-tour-category-grid-3 .tourmaster-tour-category-count{ background-color: #gdlr#; }' . 
'.tourmaster-tour-search-item-style-2 .tourmaster-type-filter-term .tourmaster-type-filter-display i{ color: #gdlr#; }' . 
'.tourmaster-user-template-style-2 .tourmaster-user-navigation .tourmaster-user-navigation-item.tourmaster-active a, ' .
'.tourmaster-user-template-style-2 .tourmaster-user-navigation .tourmaster-user-navigation-item.tourmaster-active a:hover, ' .
'.tourmaster-user-template-style-2 .tourmaster-user-navigation .tourmaster-user-navigation-item a:hover{ background: #gdlr#; color: #fff; }',
									'default' => '#485da1',
								),
								'tourmaster-theme-color-link' => array(
									'title' => esc_html__('Tourmaster Theme Color Link', 'tourmaster'),
									'type' => 'colorpicker',
									'data-type' => 'rgba',
									'selector' =>
'.tourmaster-user-navigation .tourmaster-user-navigation-item.tourmaster-active a, ' . 
'.tourmaster-user-navigation .tourmaster-user-navigation-item.tourmaster-active a:hover{ color: #gdlr#; }' .
'.tourmaster-user-navigation .tourmaster-user-navigation-item.tourmaster-active:before{ border-color: #gdlr#; }' .
'.tourmaster-template-wrapper table.tourmaster-table tr td:nth-child(2){ color: #gdlr#; }' . 
'.tourmaster-template-wrapper table.tourmaster-table tr td:nth-child(2) a{ color: #gdlr#; }' . 
'.tourmaster-template-wrapper table.tourmaster-table tr td:nth-child(2) a:hover{ color: #gdlr#; }' . 
'table.tourmaster-my-booking-table .tourmaster-my-booking-title, ' .
'table.tourmaster-my-booking-table .tourmaster-my-booking-title:hover{ color: #gdlr#; } ' .
'.tourmaster-payment-billing-copy-text{ color: #gdlr#; }' .
'.tourmaster-tour-booking-bar-price-breakdown-link{ color: #gdlr#; }' .
'.tourmaster-tour-booking-bar-coupon-wrap .tourmaster-tour-booking-bar-coupon-validate, .tourmaster-tour-booking-bar-coupon-wrap .tourmaster-tour-booking-bar-coupon-validate:hover{ color: #gdlr#; }' .
'.tourmaster-tour-booking-bar-summary .tourmaster-tour-booking-bar-date-edit{ color: #gdlr#; }' .
'.tourmaster-payment-complete-wrap .tourmaster-payment-complete-icon,' .
'.tourmaster-payment-complete-wrap .tourmaster-payment-complete-thank-you{ color: #gdlr#; }' .
'.tourmaster-tour-search-wrap input.tourmaster-tour-search-submit[type="submit"]{ background: #gdlr#; }' .
'.tourmaster-payment-step-item.tourmaster-checked .tourmaster-payment-step-item-icon,' .
'.tourmaster-payment-step-item.tourmaster-enable .tourmaster-payment-step-item-icon{ color: #gdlr#; }' . 
'.gdlr-core-flexslider.tourmaster-nav-style-rect .flex-direction-nav li a{ background-color: #gdlr#; }' . 
'body.tourmaster-template-payment a.tourmaster-button{ background-color: #gdlr#; }' . 
'.tourmaster-tour-item .tourmaster-tour-grid .tourmaster-tour-price-bottom-wrap .tourmaster-tour-price, ' .
'.tourmaster-tour-item .tourmaster-tour-grid .tourmaster-tour-price-bottom-wrap .tourmaster-tour-discount-price{ color: #gdlr#; }' . 
'.tourmaster-payment-service-form-wrap .tourmaster-payment-service-form-price-wrap{ color: #gdlr#; }',
									'default' => '#4674e7'
								),
								'tourmaster-theme-color-link-hover' => array(
									'title' => esc_html__('Tourmaster Theme Color Link Hover', 'tourmaster'),
									'type' => 'colorpicker',
									'data-type' => 'rgba',
									'selector' => 
'.tourmaster-template-wrapper table.tourmaster-table tr td:nth-child(2) a, ' . 
'table.tourmaster-my-booking-table .tourmaster-my-booking-title{ border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: transparent; }' . 
'.tourmaster-template-wrapper table.tourmaster-table tr td:nth-child(2) a:hover,' . 
'table.tourmaster-my-booking-table .tourmaster-my-booking-title:hover{ color: #gdlr#; border-color: #gdlr#; } '
								),
								'tourmaster-theme-color-light' => array(
									'title' => esc_html__('Tourmaster Theme Color Light', 'tourmaster'),
									'type' => 'colorpicker',
									'data-type' => 'rgba',
									'selector' => 
'.tourmaster-tour-info-wrap .tourmaster-tour-info i{ color: #gdlr#; }' . 
'.tourmaster-tour-info-wrap .tourmaster-tour-info svg{ fill: #gdlr#; }' . 
'.tourmaster-tour-modern.tourmaster-with-thumbnail .tourmaster-tour-price .tourmaster-tail, ' .
'.tourmaster-tour-modern.tourmaster-with-thumbnail .tourmaster-tour-discount-price{ color: #gdlr#; }' .
'.tourmaster-tour-item .tourmaster-tour-view-more,' .
'.tourmaster-tour-item .tourmaster-tour-view-more:hover{ background: #gdlr#; }' .
'.single-tour .tourmaster-datepicker-wrap:after,' .
'.single-tour .tourmaster-combobox-wrap:after,' .
'.single-tour .tourmaster-tour-info-wrap .tourmaster-tour-info i, ' . 
'.tourmaster-form-field .tourmaster-combobox-list-display:after{ color: #gdlr#; }' .
'.tourmaster-payment-step-item.tourmaster-current .tourmaster-payment-step-item-icon{ background: #gdlr#; }' .
'.tourmaster-review-content-pagination span:hover,' .
'.tourmaster-review-content-pagination span.tourmaster-active{ background: #gdlr#; }' . 
'.tourmaster-content-navigation-item-outer .tourmaster-content-navigation-slider, .tourmaster-content-navigation-item-outer .tourmaster-content-navigation-slider.tourmaster-style-dot span{ background: #gdlr#; }' . 
'.tourmaster-tour-category-grid.tourmaster-with-thumbnail .tourmaster-tour-category-count, ' .
'.tourmaster-body .tourmaster-tour-category-grid .tourmaster-tour-category-head-link{ color: #gdlr#; }' .
'.tourmaster-tour-category-grid.tourmaster-with-thumbnail .tourmaster-tour-category-head-divider, ' . 
'.tourmaster-tour-category-grid-2.tourmaster-with-thumbnail .tourmaster-tour-category-head-divider{ border-color: #gdlr#; }' . 
'.tourmaster-tour-booking-date > i, .tourmaster-tour-booking-room > i, .tourmaster-tour-booking-people > i, .tourmaster-tour-booking-submit > i,' .
'.tourmaster-tour-booking-package > i, ' . 
'.tourmaster-tour-style-1 .tourmaster-tour-booking-bar-wrap .tourmaster-view-count i, .tourmaster-save-wish-list-icon-wrap .tourmaster-icon-active{ color: #gdlr#; }' . 
'.tourmaster-tour-booking-next-sign:before, .tourmaster-tour-booking-next-sign span, .tourmaster-tour-booking-next-sign:after{ background-color: #gdlr#; }' . 
'.tourmaster-tour-item .tourmaster-tour-grid .tourmaster-tour-discount-price, ' .
'.tourmaster-tour-item .tourmaster-tour-grid .tourmaster-tour-price .tourmaster-tail{ color: #gdlr#; }' .
'.tourmaster-body .tourmaster-tour-order-filterer-style a:hover svg,' .
'.tourmaster-body .tourmaster-tour-order-filterer-style a.tourmaster-active svg{ fill: #gdlr#; }' .
'.tourmaster-body .tourmaster-tour-order-filterer-style a:hover, ' .
'.tourmaster-body .tourmaster-tour-order-filterer-style a.tourmaster-active, ' .
'.tourmaster-urgency-message .tourmaster-urgency-message-icon, ' .
'.tourmaster-payment-receipt-deposit-option label input:checked + span, ' .
'.tourmaster-tour-booking-bar-deposit-option label input:checked + span, ' .
'.tourmaster-type-filter-term input:checked + .tourmaster-type-filter-display{ color: #gdlr#; }' . 
'.tourmaster-body.tourmaster-template-search .tourmaster-pagination a:hover, ' . 
'.tourmaster-body.tourmaster-template-search .tourmaster-pagination a.tourmaster-active, ' . 
'.tourmaster-body.tourmaster-template-search .tourmaster-pagination span{ background-color: #gdlr#; }',
									'default' => '#4692e7'
								),
							)
						),
						
						'tourmaster-user-template' => array(
							'title' => esc_html__('User Template', 'tourmaster'),
							'options' => array(
								'currency-switcher-background-color' => array(
									'title' => esc_html__('Currency Switcher Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-currency-switcher{ background-color: #gdlr#; }'
								),
								'currency-switcher-text-color' => array(
									'title' => esc_html__('Currency Switcher Text', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-currency-switcher .tourmaster-head{ color: #gdlr#; }',
									'default' => '#000'
								),
								'user-login-submenu-background' => array(
									'title' => esc_html__('User Login Submenu Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-user-top-bar-nav-inner, .tourmaster-currency-switcher-content{ background-color: #gdlr#; }',
									'default' => '#ffffff',
								),
								'user-login-submenu-border' => array(
									'title' => esc_html__('User Login Submenu Border', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => 'body .tourmaster-user-top-bar-nav .tourmaster-user-top-bar-nav-item{ border-color: #gdlr#; }',
									'default' => '#e6e6e6',
								),
								'user-login-submenu-text' => array(
									'title' => esc_html__('User Login Submenu Text', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => 'body .tourmaster-user-top-bar-nav .tourmaster-user-top-bar-nav-item a, body .tourmaster-user-top-bar-nav .tourmaster-user-top-bar-nav-item a:hover{ color: #gdlr#; }' . 
										'body .tourmaster-currency-switcher-content a, body .tourmaster-currency-switcher-content a:hover{ color: #gdlr# !important; }',
									'default' => '#878787',
								),
								'user-template-background' => array(
									'title' => esc_html__('User Template Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-template-wrapper-user{ background-color: #gdlr#; }',
									'default' => '#f3f3f3',
								),
								'user-template-navigation-background' => array(
									'title' => esc_html__('User Template Navigation Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-user-navigation{ background: #gdlr#; }',
									'default' => '#ffffff',
								),
								'user-template-navigation-title' => array(
									'title' => esc_html__('User Template Navigation Title', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-user-navigation .tourmaster-user-navigation-head{ color: #gdlr#; }',
									'default' => '#3f3f3f',
								),
								'user-template-navigation-text' => array(
									'title' => esc_html__('User Template Navigation Text', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-user-navigation .tourmaster-user-navigation-item a, .tourmaster-user-navigation .tourmaster-user-navigation-item a:hover{ color: #gdlr#; }',
									'default' => '#7d7d7d',
								),
								'user-template-navigation-border' => array(
									'title' => esc_html__('User Template Navigation Border', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-user-navigation .tourmaster-user-navigation-item-sign-out{ border-color: #gdlr#; }' . 
										'.tourmaster-user-template-style-2 .tourmaster-user-navigation{ border-color: #gdlr#; }',
									'default' => '#e5e5e5',
								),
								'user-template-breadcrumbs-text' => array(
									'title' => esc_html__('User Template Bread Crumbs Text', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-body .tourmaster-user-breadcrumbs a, .tourmaster-body .tourmaster-user-breadcrumbs a:hover, .tourmaster-body .tourmaster-user-breadcrumbs span{ color: #gdlr#; }',
									'default' => '#a5a5a5',
								),
								'user-template-content-block-background' => array(
									'title' => esc_html__('User Template Content Block Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-user-content-block{ background-color: #gdlr#; }' . 
										'.tourmaster-user-template-style-2 .tourmaster-dashboard-profile-wrapper{ background-color: #gdlr#; }',
									'default' => '#ffffff',
								),
								'user-template-content-block-title-link' => array(
									'title' => esc_html__('User Template Content Block Title Link', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-user-content-block .tourmaster-user-content-title-link, .tourmaster-user-content-block .tourmaster-user-content-title-link:hover{ color: #gdlr#; }',
									'default' => '#9e9e9e',
								),
								'user-template-content-block-border' => array(
									'title' => esc_html__('User Template Content Block Border', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-user-content-block .tourmaster-user-content-title-wrap, ' . 
										'table.tourmaster-table th, .tourmaster-template-wrapper table.tourmaster-table tr td{ border-color: #gdlr#; }',
									'default' => '#e8e8e8',
								),
								'user-template-content-block-text' => array(
									'title' => esc_html__('User Template Content Block Text', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-my-profile-info .tourmaster-head, .tourmaster-my-profile-info .tourmaster-tail, ' . 
										'.tourmaster-edit-profile-wrap .tourmaster-head, table.tourmaster-table th, table.tourmaster-table td{ color: #gdlr#; }' . 
										'.tourmaster-user-content-inner-my-booking-single .tourmaster-my-booking-single-field{ color: #gdlr#; }' . 
										'.tourmaster-my-booking-single-price-breakdown .tourmaster-price-breakdown{ color: #gdlr#; }',
									'default' => '#545454',
								),
								'user-template-my-booking-price-text' => array(
									'title' => esc_html__('User Template My Booking Price Text', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => 'table.tourmaster-my-booking-table .tourmaster-my-booking-price{ color: #gdlr#; }',
									'default' => '#424242',
								),								
								'user-template-my-booking-filter-text' => array(
									'title' => esc_html__('User Template My Booking Price Text', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-template-wrapper-user .tourmaster-my-booking-filter a{ color: #gdlr#; }',
									'default' => '#a5a5a5',
								),
							)
						), // tourmaster-user-template

						'tourmaster-user-template2' => array(
							'title' => esc_html__('User Template 2', 'tourmaster'),
							'options' => array(
								'tourmaster-booking-status-text-color' => array(
									'title' => esc_html__('Booking Status Text Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-booking-status, .tourmaster-user-review-status.tourmaster-status-submitted{ color: #gdlr#; }',
									'default' => '#acacac',
								),
								'tourmaster-booking-status-pending-color' => array(
									'title' => esc_html__('Booking Status Pending Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-booking-status.tourmaster-status-pending, .tourmaster-user-review-status.tourmaster-status-pending{ color: #gdlr#; }',
									'default' => '#24a04a',
								),
								'tourmaster-booking-status-online-paid' => array(
									'title' => esc_html__('Booking Status Online Paid', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-booking-status.tourmaster-status-online-paid{ color: #gdlr#; }',
									'default' => '#cd9b45',
								),
								'tourmaster-booking-status-deposit-paid' => array(
									'title' => esc_html__('Booking Status Deposit Paid', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-booking-status.tourmaster-status-wait-for-approval{ color: #gdlr#; }',
									'default' => '#5b9dd9',
								),
								'tourmaster-booking-status-wait-for-approval' => array(
									'title' => esc_html__('Booking Status Wait For Approval', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-booking-status.tourmaster-status-deposit-paid{ color: #gdlr#; }',
									'default' => '#e0724e',
								),
								'tourmaster-booking-receipt-button-background' => array(
									'title' => esc_html__('Submit Receipt Button Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-my-booking-single-sidebar .tourmaster-my-booking-single-receipt-button, ' .
										'.tourmaster-my-booking-single-sidebar .tourmaster-my-booking-single-receipt-button:hover{ background-color: #gdlr#; }',
									'default' => '#48a167',
								),
								'tourmaster-booking-receipt-button-background' => array(
									'title' => esc_html__('Make Payment Button Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-my-booking-single-sidebar .tourmaster-my-booking-single-payment-button, ' .
										'.tourmaster-my-booking-single-sidebar .tourmaster-my-booking-single-payment-button:hover{ background-color: #gdlr#; }',
									'default' => '#48a198',
								),
								'tourmaster-invoice-title-color' => array(
									'title' => esc_html__('Invoice Title Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-invoice-head{ color: #gdlr#; }',
									'default' => '#121212',
								),
								'tourmaster-invoice-price-head-background' => array(
									'title' => esc_html__('Invoice Price Header Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-invoice-price-head, .tourmaster-invoice-payment-info{ background-color: #gdlr#; }',
									'default' => '#f3f3f3',
								),
								'tourmaster-invoice-price-head-text' => array(
									'title' => esc_html__('Invoice Price Header Text', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-invoice-price-head, .tourmaster-invoice-payment-info{ color: #gdlr#; }',
									'default' => '#454545',
								),
								'tourmaster-invoice-price-text' => array(
									'title' => esc_html__('Invoice Price Text', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-invoice-price .tourmaster-head, .tourmaster-invoice-total-price{ color: #gdlr#; }',
									'default' => '#7b7b7b',
								),
								'tourmaster-invoice-price-amount' => array(
									'title' => esc_html__('Invoice Price Amount', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-invoice-price .tourmaster-tail{ color: #gdlr#; }',
									'default' => '#1e1e1e',
								),
							)
						),

						'tourmaster-lightbox' => array(
							'title' => esc_html__('Lightbox Color', 'tourmaster'),
							'options' => array(
								'tourmaster-lightbox-background' => array(
									'title' => esc_html__('Lightbox Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-lightbox-wrapper .tourmaster-lightbox-content-wrap{ background-color: #gdlr#; }',
									'default' => '#ffffff',
								),
								'tourmaster-lightbox-title' => array(
									'title' => esc_html__('Lightbox Title', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-lightbox-wrapper h3, .tourmaster-lightbox-wrapper .tourmaster-lightbox-title, ' .
										'.tourmaster-lightbox-wrapper .tourmaster-lightbox-close, .tourmaster-payment-receipt-field .tourmaster-head, '.
										'.tourmaster-login-bottom .tourmaster-login-bottom-title{ color: #gdlr#; }',
									'default' => '#0e0e0e',
								),
								'tourmaster-lightbox-form-label' => array(
									'title' => esc_html__('Lightbox Form Label', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-login-form label, .tourmaster-login-form2 label, ' .
										'.tourmaster-lost-password-form label, .tourmaster-reset-password-form label, ' .
										'.tourmaster-register-form .tourmaster-profile-field .tourmaster-head{ color: #gdlr#; } ' .
										'.tourmaster-review-form .tourmaster-review-form-description .tourmaster-tail, ' .
										'.tourmaster-review-form .tourmaster-review-form-traveller-type .tourmaster-tail{ color: #gdlr#; }',
									'default' => '#5c5c5c',
								),
							)
						),

						'tourmaster-input-color' => array(
							'title' => esc_html__('Tourmaster Input', 'tourmaster'),
							'options' => array(
								'tourmaster-input-form-label' => array(
									'title' => esc_html__('Input Form Label', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-payment-traveller-info-wrap .tourmaster-head, .tourmaster-payment-contact-wrap .tourmaster-head, ' .
										'.tourmaster-payment-billing-wrap .tourmaster-head, .tourmaster-payment-additional-note-wrap .tourmaster-head, ' .
										'.tourmaster-payment-detail-wrap .tourmaster-payment-detail, .tourmaster-payment-detail-notes-wrap .tourmaster-payment-detail, ' .
										'.tourmaster-payment-traveller-detail .tourmaster-payment-detail{ color: #gdlr#; }' .
										'.goodlayers-payment-form .goodlayers-payment-form-field .goodlayers-payment-field-head{ color: #gdlr#; }' . 
										'.tourmaster-room-payment-contact-form .tourmaster-head, ' .
										'.tourmaster-guest-info-field.tourmaster-display .tourmaster-sub-head, ' .
										'.tourmaster-room-payment-contact-form .tourmaster-payment-billing-separate-wrap{ color: #gdlr#; }',
									'default' => '#5c5c5c',
								),
								'tourmaster-input-box-text' => array(
									'title' => esc_html__('Input Box Text', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-body .tourmaster-form-field input[type="text"], .tourmaster-body .tourmaster-form-field input[type="email"], .tourmaster-body .tourmaster-form-field input[type="password"], ' .
										'.tourmaster-body .tourmaster-form-field textarea, .tourmaster-body .tourmaster-form-field select, .tourmaster-body .tourmaster-form-field input[type="text"]:focus, ' .
										'.tourmaster-form-field.tourmaster-with-border .tourmaster-combobox-list-display, .tourmaster-form-field .tourmaster-combobox-list-wrap ul, ' .
										'.tourmaster-body .tourmaster-form-field input[type="email"]:focus, .tourmaster-body .tourmaster-form-field input[type="password"]:focus, .tourmaster-body .tourmaster-form-field textarea:focus{ color: #gdlr#; }' . 
										'.goodlayers-payment-form .goodlayers-payment-form-field input[type="text"]{ color: #gdlr#; }',
									'default' => '#545454',
								),
								'tourmaster-input-box-background' => array(
									'title' => esc_html__('Input Box Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-body .tourmaster-form-field input[type="text"], .tourmaster-body .tourmaster-form-field input[type="email"], .tourmaster-body .tourmaster-form-field input[type="password"], ' .
										'.tourmaster-body .tourmaster-form-field textarea, .tourmaster-body .tourmaster-form-field select, .tourmaster-body .tourmaster-form-field input[type="text"]:focus, ' .
										'.tourmaster-body .tourmaster-form-field input[type="email"]:focus, .tourmaster-body .tourmaster-form-field input[type="password"]:focus, .tourmaster-body .tourmaster-form-field textarea:focus{ background: #gdlr#; }' . 
										'.tourmaster-form-field.tourmaster-with-border .tourmaster-combobox-list-display, ' .
										'.goodlayers-payment-form .goodlayers-payment-form-field input[type="text"]{ background-color: #gdlr#; }',
									'default' => '#ffffff',
								),
								'tourmaster-input-box-background-validate-error' => array(
									'title' => esc_html__('Input Box Background Validate Error', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-form-field.tourmaster-with-border input.tourmaster-validate-error[type="text"], ' .
										'.tourmaster-form-field.tourmaster-with-border input.tourmaster-validate-error[type="email"], ' .
										'.tourmaster-form-field.tourmaster-with-border input.tourmaster-validate-error[type="password"], ' .
										'.tourmaster-form-field.tourmaster-with-border textarea.tourmaster-validate-error, ' .
										'.tourmaster-form-field.tourmaster-with-border select.tourmaster-validate-error{ background-color: #gdlr#; }' .
										'.tourmaster-form-field.tourmaster-with-border input.tourmaster-validate-error[type="text"]:focus, ' .
										'.tourmaster-form-field.tourmaster-with-border input.tourmaster-validate-error[type="email"]:focus, ' .
										'.tourmaster-form-field.tourmaster-with-border input.tourmaster-validate-error[type="password"]:focus, ' .
										'.tourmaster-form-field.tourmaster-with-border textarea.tourmaster-validate-error:focus, ' .
										'.tourmaster-form-field.tourmaster-with-border select.tourmaster-validate-error:focus{ background-color: #gdlr#; }',
									'default' => '#fff9f9',
								),
								'tourmaster-input-box-border' => array(
									'title' => esc_html__('Input Box Border', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-form-field.tourmaster-with-border input[type="text"], .tourmaster-form-field.tourmaster-with-border input[type="email"], ' .
										'.tourmaster-form-field.tourmaster-with-border input[type="password"], .tourmaster-form-field.tourmaster-with-border textarea, ' .
										'.tourmaster-form-field.tourmaster-with-border select{ border-color: #gdlr#; }' . 
										'.goodlayers-payment-form .goodlayers-payment-form-field input[type="text"]{ border-color: #gdlr#; }' .
										'.tourmaster-room-payment-lb .goodlayers-payment-form input[type="text"], .tourmaster-room-payment-lb .goodlayers-payment-form #card-element{ border-color: #gdlr#; }' . 
										'.tourmaster-user-template-style-2 .tourmaster-form-field input[type="text"], ' .
										'.tourmaster-user-template-style-2 .tourmaster-form-field input[type="email"], ' .
										'.tourmaster-user-template-style-2 .tourmaster-form-field input[type="password"], ' .
										'.tourmaster-user-template-style-2 .tourmaster-form-field textarea, ' .
										'.tourmaster-user-template-style-2 .tourmaster-form-field select{  border-color: #gdlr#; }',
									'default' => '#e6e6e6',
								),
								'tourmaster-checkbox-box-border' => array(
									'title' => esc_html__('Checkbox Box Border', 'tourmaster'),
									'type' => 'colorpicker',
									'default' => '#cccccc',
									'selector' => '.tourmaster-tour-search-item-style-2 .tourmaster-type-filter-term .tourmaster-type-filter-display i{ border-color: #gdlr#; }'
								),
								'tourmaster-upload-box-background' => array(
									'title' => esc_html__('Upload Box Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-form-field .tourmaster-file-label-text{ background-color: #gdlr#; }',
									'default' => '#f3f3f3',
								),
								'tourmaster-upload-box-text' => array(
									'title' => esc_html__('Upload Box Text Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-form-field .tourmaster-file-label-text{ color: #gdlr#; }',
									'default' => '#a6a6a6',
								),
							)
						), // tourmaster-input-color

					)
				));

				/*
				// miscalleneous
				$tourmaster_option->add_element(array(
					'title' => esc_html__('Miscalleneous', 'tourmaster'),
					'slug' => 'tourmaster_plugin',
					'icon' => TOURMASTER_URL . '/images/plugin-options/plugin.png',
					'options' => array(

						'plugins' => array(
							'title' => esc_html__('Plugins', 'tourmaster'),
							'options' => array(

								'font-awesome' => array(
									'title' => esc_html__('Font Awesome', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable',
									'description' => esc_html__('Disable this if the "Font Awesome" is already included on your site.', 'tourmaster'),
								),
								'elegant-icon' => array(
									'title' => esc_html__('Elegant Icon', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable',
									'description' => esc_html__('Disable this if the "Elegant Icon" is already included on your site.', 'tourmaster'),
								)

							)
						),

					)
				));
				*/
			}
		} // tourmaster_init_admin_option
	} // function_exists

