<?php
	/*	
	*	Tourmaster Plugin
	*	---------------------------------------------------------------------
	*	creating the plugin option
	*	---------------------------------------------------------------------
	*/

	// return the custom stylesheet path
	if( !function_exists('tourmaster_room_get_style_custom') ){
		function tourmaster_room_get_style_custom($local = false){

			$upload_dir = wp_upload_dir();
			$filename = '/tourmaster-room-style-custom.css';
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
					return TOURMASTER_URL . '/room/style-custom.css';
				}
			}
		}
	}
	
	add_action('after_setup_theme', 'tourmaster_room_init_admin_option');
	if( !function_exists('tourmaster_room_init_admin_option') ){ 
		function tourmaster_room_init_admin_option(){
			if( is_admin() || is_customize_preview() ){

				$search_filters = array(
					'room_category' => esc_html__('Category', 'tourmaster'),
					'room_tag' => esc_html__('Tag', 'tourmaster')
				);
				$search_filters = $search_filters + tourmaster_get_custom_tax_list('room');

				$tourmaster_option = new tourmaster_admin_option(array(
					'page-title' => esc_html__('Room Settings', 'tourmaster'),
					'menu-title' => esc_html__('Room Settings', 'tourmaster'),
					'slug' => 'tourmaster_room_admin_option', 
					'filewrite' => tourmaster_room_get_style_custom(true),
					'position' => 121
				));

				// general
				$tourmaster_option->add_element(array(
					'title' => esc_html__('General', 'tourmaster'),
					'slug' => 'tourmaster_room_general',
					'icon' => TOURMASTER_URL . '/images/plugin-options/general.png',
					'options' => array(

						
						'general-settings' => array(
							'title' => esc_html__('General Settings', 'tourmaster'),
							'options' => array(
								'min-night-stay' => array(
									'title' => esc_html__('Min Night Stay', 'tourmaster'),
									'type' => 'text',
									'default' => 1,
									'description' => esc_html__('Minimum night to book the room.', 'tourmaster')
								),
								'block-date' => array(
									'title' => esc_html__('Block Date On All Rooms', 'tourmaster'),
									'type' => 'textarea',
									'description' => esc_html__('Fill the date in yyyy-mm-dd format and separated the date using comma. Eg. 2020-12-25,2020-12-26,2020-12-27', 'tourmaster')
								),
								'cancel-booking-day' => array(
									'title' => esc_html__('Cancel booking if the payment is not processed within # days (After booking date)', 'tourmaster'),
									'type' => 'text',
									'description' => esc_html__('Only number is allowed here. Leave this field blank to omit this option.', 'tourmaster')
								),
								'enable-cancel-booking-mail' => array(
									'title' => esc_html__('Enable Cancel Booking E-mail', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable'
								),
								'tax-rate' => array(
									'title' => esc_html__('Tax Rate ( Percent )', 'tourmaster'),
									'type' => 'text',
									'default' => '9',
									'description' => esc_html__('Fill only number ( as percent ) here', 'tourmaster')
								),
								'apply-coupon-after-tax' => array(
									'title' => esc_html__('Apply Coupon After Tax', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'disable',
								),
								'included-tax-in-price' => array(
									'title' => esc_html__('Included Tax In Tour Price', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'disable',
									'description' => esc_html__('When enable, the tax is included in the tour price. If disable, tax will be addition from tour price', 'tourmaster')
								),
								'top-bar-cart-page' => array(
									'title' => esc_html__('Cart Page', 'tourmaster'),
									'type' => 'combobox',
									'options' => tourmaster_get_post_list('page', true),
									'description' => esc_html__('Select cart page to display link in user top bar area.', 'tourmaster')
								),
								'enable-navigation-checkout-button' => array(
									'title' => esc_html__('Enable Navigation Checkout Button', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'disable',
								),
								'navigation-checkout-button-link' => array(
									'title' => esc_html__('Navigation Checkout Button Link', 'tourmaster'),
									'type' => 'text',
									'default' => '#',
									'description' => esc_html__('Book now link when the cart is empty', 'tourmaster'),
									'condition' => array('enable-navigation-checkout-button' => 'enable')
								),
								'navigation-checkout-button-top-margin' => array(
									'title' => esc_html__('Navigation Checkout Button Top Margin', 'tourmaster'),
									'type' => 'text',
									'data-type' => 'pixel',
									'data-input-type' => 'pixel',
			 						'default' => '',
									'selector' => '.tourmaster-room-navigation-checkout-button{ margin-top: #gdlr#; }',
									'condition' => array('enable-navigation-checkout-button' => 'enable')
								),
								'ical-cache-time' => array(
									'title' => __('Ical Cache Time ( Mins )', 'tourmaster'),
									'type'=> 'text',
									'default'=> '5'
								),
								'ical-start-time' => array(
									'title' => __('Ical File Start Time ( Months )', 'tourmaster'),
									'type'=> 'text',
									'default'=> '2'
								),
							)
						),
						
						'payment-page' => array(
							'title' => esc_html__('Payment Page', 'tourmaster'),
							'options' => array(
								
								'contact-detail-fields' => array(
									'title' => esc_html__('Contact Detail Fields', 'tourmaster'),
									'type' => 'textarea',
									'description' => wp_kses(__('Leave blank for default. You can see how to create the fields <a href="http://support.goodlayers.com/document/2018/05/01/tourmaster-modifying-the-contact-detail-fields-since-v3-0-8/" target="_blank" >here</a>', 'tourmaster'), array('a'=>array( 'href'=> array(), 'target'=>array())) )
								),
								'required-guest-info' => array(
									'title' => esc_html__('Required Guest Info', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable'
								),
								'additional-guest-fields' => array(
									'title' => esc_html__('Additional Guest Fields', 'tourmaster'),
									'type' => 'textarea',
									'description' => wp_kses(__('Use to add new fields at the "guest details" area. Learn more about this <a href="http://support.goodlayers.com/document/2018/05/03/tourmaster-modifying-the-traveller-detail-fields-since-v3-0-8/" target="_blank" >here</a>', 'tourmaster'), array('a'=>array( 'href'=> array(), 'target'=>array())) ),
									'condition' => array( 'required-guest-info' => 'enable' )
								),
							)
						),
						
						'archive-page' => array(
							'title' => esc_html__('Archive Page', 'tourmaster'),
							'options' => array(
								
								'archive-description' => array(
									'title' => esc_html__('Enable Archive Description', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable',
								),
								'archive-sidebar' => array(
									'title' => esc_html__('Archive Room Sidebar', 'tourmaster'),
									'type' => 'radioimage',
									'options' => 'sidebar',
									'default' => 'right',
									'wrapper-class' => 'tourmaster-fullsize'
								),
								'archive-sidebar-left' => array(
									'title' => esc_html__('Archive Tour Sidebar Left', 'tourmaster'),
									'type' => 'combobox',
									'options' => 'sidebar',
									'default' => 'none',
									'condition' => array( 'archive-sidebar'=>array('left', 'both') )
								),
								'archive-sidebar-right' => array(
									'title' => esc_html__('Archive Tour Sidebar Right', 'tourmaster'),
									'type' => 'combobox',
									'options' => 'sidebar',
									'default' => 'none',
									'condition' => array( 'archive-sidebar'=>array('right', 'both') )
								),
								'archive-room-style' => array(
									'title' => esc_html__('Archive Room Style', 'tourmaster'),
									'type' => 'combobox',
									'options' => array(
										'grid' => esc_html__('Grid', 'tourmaster'),
										'grid2' => esc_html__('Grid 2', 'tourmaster'),
										'grid3' => esc_html__('Grid 3', 'tourmaster'),
										'grid4' => esc_html__('Grid 4', 'tourmaster'),
										'modern' => esc_html__('Modern', 'tourmaster'),
										'side-thumbnail' => esc_html__('Side Thumbnail', 'tourmaster')
									),
									'default' => 'grid',
								),
								'archive-room-with-frame' => array(
									'title' => esc_html__('With Frame', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'disable',
									'condition' => array( 'archive-room-style' => array('grid', 'grid2', 'grid3', 'grid4') )
								),
								'archive-room-column-size' => array(
									'title' => esc_html__('Archive Column Size', 'tourmaster'),
									'type' => 'combobox',
									'options' => array( 60=>1, 30=>2, 20=>3, 15=>4, 12=>5 ),
									'default' => 20,
									'condition' => array( 'archive-room-style' => array('grid', 'grid2', 'grid3', 'grid4', 'modern') )
								),
								'archive-room-thumbnail-size' => array(
									'title' => esc_html__('Archive Thumbnail Size', 'tourmaster'),
									'type' => 'combobox',
									'options' => 'thumbnail-size'
								),
								'archive-room-display-price' => array(
									'title' => esc_html__('Display Price', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable',
									'condition' => array( 'archive-room-style' => array('grid', 'grid2', 'grid3', 'grid4', 'side-thumbnail') )
								),
								'archive-room-enable-price-prefix' => array(
									'title' => esc_html__('Enable Price Prefix', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable',
									'condition' => array( 'archive-display-price' => 'enable' )
								),
								'archive-room-enable-price-suffix' => array(
									'title' => esc_html__('Enable Price Suffix', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable',
									'condition' => array( 'archive-display-price' => 'enable' )
								),
								'archive-room-price-decimal-digit' => array(
									'title' => esc_html__('Price Decimal Digit', 'tourmaster'),
									'type' => 'text',
									'condition' => array( 'archive-display-price' => 'enable', 'room-style' => array('grid', 'grid2', 'grid3', 'grid4', 'side-thumbnail') )
								),
								'archive-room-display-ribbon' => array(
									'title' => esc_html__('Display Ribbon', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable',
									'condition' => array( 'archive-room-style' => array('grid', 'grid2', 'grid3', 'grid4', 'side-thumbnail') )
								),
								'archive-room-info' => array(
									'title' => esc_html__('Room Info', 'tourmaster'),
									'type' => 'multi-combobox',
									'options' => array(
										'bed-type' => esc_html__('Bed Type', 'tourmaster'),
										'guest-amount' => esc_html__('Guest Amount', 'tourmaster'),
										'room-size' => esc_html__('Room Size', 'tourmaster'),
										'custom-excerpt' => esc_html__('Custom Excerpt', 'tourmaster'),
										'location' => esc_html__('Location', 'tourmaster')
									),
									'description' => esc_html__('You can use Ctrl/Command button to select multiple items or remove the selected item. Leave this field blank to select all items in the list.', 'tourmaster'),
								),
								'archive-room-excerpt' => array(
									'title' => esc_html__('Excerpt Type', 'tourmaster'),
									'type' => 'combobox',
									'options' => array(
										'specify-number' => esc_html__('Specify Number', 'tourmaster'),
										'show-all' => esc_html__('Show All ( use <!--more--> tag to cut the content )', 'tourmaster'),
										'none' => esc_html__('Disable Exceprt', 'tourmaster'),
									),
									'default' => 'specify-number',
								),
								'archive-room-excerpt-number' => array(
									'title' => esc_html__('Excerpt Number', 'tourmaster'),
									'type' => 'text',
									'default' => 55,
								),	
								'archive-room-enable-rating' => array(
									'title' => esc_html__('Enable Rating', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable'
								),
								'archive-room-title-font-size' => array(
									'title' => esc_html__('Room Title Font Size', 'tourmaster'),
									'type' => 'text',
									'data-input-type' => 'pixel',
								),
								'archive-room-title-font-weight' => array(
									'title' => esc_html__('Room Title Font Weight', 'tourmaster'),
									'type' => 'text',
									'description' => esc_html__('Eg. lighter, bold, normal, 300, 400, 600, 700, 800', 'tourmaster')
								),
								'archive-room-title-letter-spacing' => array(
									'title' => esc_html__('Room Title Letter Spacing', 'tourmaster'),
									'type' => 'text',
									'data-input-type' => 'pixel',
								),
								'archive-room-title-text-transform' => array(
									'title' => esc_html__('Room Title Text Transform', 'tourmaster'),
									'type' => 'combobox',
									'data-type' => 'text',
									'options' => array(
										'uppercase' => esc_html__('Uppercase', 'tourmaster'),
										'lowercase' => esc_html__('Lowercase', 'tourmaster'),
										'capitalize' => esc_html__('Capitalize', 'tourmaster'),
										'none' => esc_html__('None', 'tourmaster'),
									),
									'default' => 'none'
								)
							)
						),

						'search-page' => array(
							'title' => esc_html__('Search Page', 'tourmaster'),
							'options' => array(
								'search-page' => array(
									'title' => esc_html__('Search Page', 'tourmaster'),
									'type' => 'combobox',
									'options' => tourmaster_get_post_list('page', true),
									'description' => esc_html__('Choose the page to use header / footer of that page as template. Select "None" to use homepage settings.', 'tourmaster')
								),
								'search-filters' => array(
									'title' => esc_html__('Search filter', 'tourmaster'),
									'type' => 'multi-combobox',
									'options' => $search_filters
								),
								'search-filters-content' => array(
									'title' => esc_html__('Search Filter Content', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable'
								),
								'search-room-style' => array(
									'title' => esc_html__('Search Room Style', 'tourmaster'),
									'type' => 'combobox',
									'options' => array(
										'grid' => esc_html__('Grid', 'tourmaster'),
										'grid2' => esc_html__('Grid 2', 'tourmaster'),
										'grid3' => esc_html__('Grid 3', 'tourmaster'),
										'grid4' => esc_html__('Grid 4', 'tourmaster'),
										'modern' => esc_html__('Modern', 'tourmaster'),
										'side-thumbnail' => esc_html__('Side Thumbnail', 'tourmaster')
									),
									'default' => 'side-thumbnail',
								),
								'search-room-with-frame' => array(
									'title' => esc_html__('With Frame', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'disable',
									'condition' => array( 'archive-room-style' => array('grid', 'grid2', 'grid3', 'grid4') )
								),
								'search-room-column-size' => array(
									'title' => esc_html__('Search Column Size', 'tourmaster'),
									'type' => 'combobox',
									'options' => array( 60=>1, 30=>2, 20=>3, 15=>4, 12=>5 ),
									'default' => 30,
									'condition' => array( 'archive-room-style' => array('grid', 'grid2', 'grid3', 'grid4', 'modern') )
								),
								'search-room-thumbnail-size' => array(
									'title' => esc_html__('Search Thumbnail Size', 'tourmaster'),
									'type' => 'combobox',
									'options' => 'thumbnail-size'
								),
								'search-room-display-price' => array(
									'title' => esc_html__('Display Price', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable',
									'condition' => array( 'archive-room-style' => array('grid', 'grid2', 'grid3', 'grid4', 'side-thumbnail') )
								),
								'search-room-enable-price-prefix' => array(
									'title' => esc_html__('Enable Price Prefix', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable',
									'condition' => array( 'archive-display-price' => 'enable' )
								),
								'search-room-enable-price-suffix' => array(
									'title' => esc_html__('Enable Price Suffix', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable',
									'condition' => array( 'archive-display-price' => 'enable' )
								),
								'search-room-price-decimal-digit' => array(
									'title' => esc_html__('Price Decimal Digit', 'tourmaster'),
									'type' => 'text',
									'condition' => array( 'archive-display-price' => 'enable', 'room-style' => array('grid', 'grid2', 'grid3', 'grid4', 'side-thumbnail') )
								),
								'search-room-display-ribbon' => array(
									'title' => esc_html__('Display Ribbon', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable',
									'condition' => array( 'archive-room-style' => array('grid', 'grid2', 'grid3', 'grid4', 'side-thumbnail') )
								),
								'search-room-info' => array(
									'title' => esc_html__('Room Info', 'tourmaster'),
									'type' => 'multi-combobox',
									'options' => array(
										'bed-type' => esc_html__('Bed Type', 'tourmaster'),
										'guest-amount' => esc_html__('Guest Amount', 'tourmaster'),
										'room-size' => esc_html__('Room Size', 'tourmaster'),
										'custom-excerpt' => esc_html__('Custom Excerpt', 'tourmaster'),
										'location' => esc_html__('Location', 'tourmaster')
									),
									'description' => esc_html__('You can use Ctrl/Command button to select multiple items or remove the selected item. Leave this field blank to select all items in the list.', 'tourmaster'),
								),
								'search-room-excerpt' => array(
									'title' => esc_html__('Excerpt Type', 'tourmaster'),
									'type' => 'combobox',
									'options' => array(
										'specify-number' => esc_html__('Specify Number', 'tourmaster'),
										'show-all' => esc_html__('Show All ( use <!--more--> tag to cut the content )', 'tourmaster'),
										'none' => esc_html__('Disable Exceprt', 'tourmaster'),
									),
									'default' => 'specify-number',
								),
								'search-room-excerpt-number' => array(
									'title' => esc_html__('Excerpt Number', 'tourmaster'),
									'type' => 'text',
									'default' => 55,
								),	
								'search-room-enable-rating' => array(
									'title' => esc_html__('Enable Rating', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable'
								),	
								'search-room-title-font-size' => array(
									'title' => esc_html__('Room Title Font Size', 'tourmaster'),
									'type' => 'text',
									'data-input-type' => 'pixel',
								),
								'search-room-title-font-weight' => array(
									'title' => esc_html__('Room Title Font Weight', 'tourmaster'),
									'type' => 'text',
									'description' => esc_html__('Eg. lighter, bold, normal, 300, 400, 600, 700, 800', 'tourmaster')
								),
								'search-room-title-letter-spacing' => array(
									'title' => esc_html__('Room Title Letter Spacing', 'tourmaster'),
									'type' => 'text',
									'data-input-type' => 'pixel',
								),
								'search-room-title-text-transform' => array(
									'title' => esc_html__('Room Title Text Transform', 'tourmaster'),
									'type' => 'combobox',
									'data-type' => 'text',
									'options' => array(
										'uppercase' => esc_html__('Uppercase', 'tourmaster'),
										'lowercase' => esc_html__('Lowercase', 'tourmaster'),
										'capitalize' => esc_html__('Capitalize', 'tourmaster'),
										'none' => esc_html__('None', 'tourmaster'),
									),
									'default' => 'none'
								)
							)
						),
						'invoice-settings' => array(
							'title' => esc_html__('Invoice Settings', 'tourmaster'),
							'options' => array(
								'invoice-logo' => array(
									'title' => esc_html__('Invoice Logo', 'tourmaster'),
									'type' => 'upload'
								),
								'invoice-logo-width' => array(
									'title' => esc_html__('Invoice Logo Width', 'tourmaster'),
									'type' => 'text',
									'data-type' => 'pixel',
									'data-input-type' => 'pixel',
									'default' => '250px',
									'selector' => '.tourmaster-room-invoice-logo{ width: #gdlr#; margin-bottom: 35px; }'
								),
								'invoice-company-name' => array(
									'title' => esc_html__('Invoice Company Name', 'tourmaster'),
									'type' => 'text',
									'default' => esc_html__('Company Name', 'tourmaster'),
								),
								'invoice-company-info' => array(
									'title' => esc_html__('Invoice Company Info', 'tourmaster'),
									'type' => 'textarea',
								),
								'invoice-customer-address' => array(
									'title' => esc_html__('Invoice Customer Address', 'tourmaster'),
									'type' => 'textarea',
									'description' => wp_kses(__('Fill this to modify customer address format, if you change the <a href="http://support.goodlayers.com/document/2018/05/01/tourmaster-modifying-the-contact-detail-fields-since-v3-0-8/" target="_blank" >contact detail fileds</a>', 'tourmaster'), array('a'=>array( 'href'=> array(), 'target'=>array())) )
								),
							)
						),
						'single-tour' => array(
							'title' => esc_html__('Room Title', 'tourmaster'),
							'options' => array(
								
								'enable-room-title' => array(
									'title' => esc_html__('Enable Room Title', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable'
								),
								'room-header-background-color' => array(
									'title' => esc_html__('Room Header Background Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-single-header-title-wrap{ background-color: #gdlr#; }'
								),
								'room-header-title-color' => array(
									'title' => esc_html__('Room Header Title Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => 'body .tourmaster-room-single-header-title-wrap h1{ color: #gdlr#; }'
								),
								'room-header-background-image' => array(
									'title' => esc_html__('Room Header Background Image', 'tourmaster'),
									'type' => 'upload',
									'data-type' => 'file',
									'selector' => '.tourmaster-room-single-header-title-wrap{ background-image: url(#gdlr#); }'
								),
								'room-header-top-padding' => array(
									'title' => esc_html__('Room Header Top Padding', 'tourmaster'),
									'type' => 'fontslider',
									'data-type' => 'pixel',
									'data-min' => '0',
									'data-max' => '600',
			 						'default' => '240px',
									'selector' => '.tourmaster-room-single-header-title-wrap{ padding-top: #gdlr#; }'
								),
								'room-header-bottom-padding' => array(
									'title' => esc_html__('Room Header Bottom Padding', 'tourmaster'),
									'type' => 'fontslider',
									'data-type' => 'pixel',
									'data-min' => '0',
									'data-max' => '600',
			 						'default' => '140px',
									'selector' => '.tourmaster-room-single-header-title-wrap{ padding-bottom: #gdlr#; }'
								),
								'room-title-background-top-radius' => array(
									'title' => esc_html__('Room Title Background Top Radius', 'hotale'),
									'type' => 'text',
									'data-input-type' => 'pixel',
								),
								'room-title-background-bottom-radius' => array(
									'title' => esc_html__('Room Title Background Bottom Radius', 'hotale'),
									'type' => 'text',
									'data-input-type' => 'pixel',
								),
								'room-header-overlay-opacity' => array(
									'title' => esc_html__('Room Header Overlay Opacity', 'tourmaster'),
									'type' => 'text',
									'data-type' => 'text',
									'selector' => '.tourmaster-room-single-header-background-overlay{ opacity: #gdlr#; }'
								),
								'room-title-font-size' => array(
									'title' => esc_html__('Room Title Font Size', 'tourmaster'),
									'type' => 'fontslider',
									'data-type' => 'pixel',
									'data-min' => '0',
									'data-max' => '120',
			 						'default' => '76px',
									'selector' => 'body .tourmaster-room-single-header-title-wrap h1{ font-size: #gdlr#; }'
								),	
								'room-title-font-style' => array(
									'title' => esc_html__('Room Title Font Style', 'tourmaster'),
									'type' => 'combobox',
									'data-type' => 'text',
									'options' => array(
										'normal' => esc_html__('Normal', 'tourmaster'),
										'italic' => esc_html__('Italic', 'tourmaster'),
									),
									'default' => 'normal',
									'selector' => 'body .tourmaster-room-single-header-title-wrap h1{ font-style: #gdlr#; }'
								),
								'room-title-font-weight' => array(
									'title' => esc_html__('Navigation Font Weight', 'tourmaster'),
									'type' => 'text',
									'data-type' => 'text',
									'selector' => 'body .tourmaster-room-single-header-title-wrap h1{ font-weight: #gdlr#; }',
									'description' => esc_html__('Eg. lighter, bold, normal, 300, 400, 600, 700, 800', 'tourmaster')
								),	
								'room-title-font-letter-spacing' => array(
									'title' => esc_html__('Navigation Font Letter Spacing', 'tourmaster'),
									'type' => 'text',
									'data-type' => 'pixel',
									'data-input-type' => 'pixel',
									'selector' => 'body .tourmaster-room-single-header-title-wrap h1{ letter-spacing: #gdlr#; }'
								),
								'room-title-text-transform' => array(
									'title' => esc_html__('Navigation Text Transform', 'tourmaster'),
									'type' => 'combobox',
									'data-type' => 'text',
									'options' => array(
										'uppercase' => esc_html__('Uppercase', 'tourmaster'),
										'lowercase' => esc_html__('Lowercase', 'tourmaster'),
										'capitalize' => esc_html__('Capitalize', 'tourmaster'),
										'none' => esc_html__('None', 'tourmaster'),
									),
									'default' => 'none',
									'selector' => 'body .tourmaster-room-single-header-title-wrap h1{ text-transform: #gdlr#; }',
								),
								'room-title-align' => array(
									'title' => esc_html__('Room Title Alignment', 'tourmaster'),
									'type' => 'combobox',
									'data-type' => 'text',
									'options' => array(
										'center' => esc_html__('Center', 'tourmaster'),
										'left' => esc_html__('Left', 'tourmaster'),
										'right' => esc_html__('Right', 'tourmaster'),
									),
									'default' => 'center',
									'selector' => 'body .tourmaster-room-single-header-title-wrap h1{ text-align: #gdlr#; }',
								),
							)
						),
						'mail-settings' => array(
							'title' => esc_html__('E-Mail Settings', 'tourmaster'),
							'options' => array(
								'system-email-name' => array(
									'title' => esc_html__('System Name ( For E-mail Sending )', 'tourmaster'),
									'type' => 'text',
									'default' => 'WORDPRESS'
								),
								'system-email-address' => array(
									'title' => esc_html__('System E-Mail Address', 'tourmaster'),
									'type' => 'text'
								),
								'admin-email-address' => array(
									'title' => esc_html__('Admin Booking E-Mail Address', 'tourmaster'),
									'type' => 'text',
									'description' => esc_html__('Fill the admin email here to submit the notification upon completing booking process.')
								),
								'mail-header-logo' => array(
									'title' => esc_html__('E-Mail Header Logo', 'tourmaster'),
									'type' => 'upload',
								),
								'mail-footer-left' => array(
									'title' => esc_html__('E-Mail Footer Left', 'tourmaster'),
									'type' => 'textarea',
								),
								'mail-footer-right' => array(
									'title' => esc_html__('E-Mail Footer Right', 'tourmaster'),
									'type' => 'textarea',
								),
							)
						),
						'admin-mail-content' => array(
							'title' => esc_html__('Admin E-Mail Content', 'tourmaster'),
							'options' => array(
								'enable-admin-booking-made-mail' => array(
									'title' => esc_html__('Enable Admin Booking Made E-Mail', 'tourmaster'),
									'type' => 'checkbox'
								),
								'admin-booking-made-mail-title' => array(
									'title' => esc_html__('Admin Booking Made E-Mail Title', 'tourmaster'),
									'type' => 'text',
									'default' => 'A new booking has been made',
									'condition' => array('enable-admin-booking-made-mail' => 'enable')
								),
								'admin-booking-made-mail' => array(
									'title' => esc_html__('Admin Booking Made E-Mail', 'tourmaster'),
									'type' => 'textarea',
									'wrapper-class' => 'tourmaster-fullsize', 
									'default' => "<strong>Dear Admin,</strong>\nA new booking form {customer-name} has been made.\n\n{order-number}\n{booking-info}\n\nCustomer's Note: {customer-note}\n{spaces}\n\nYou can view <a href=\"{admin-transaction-link}\">the transaction here</a>",
									'condition' => array('enable-admin-booking-made-mail' => 'enable')
								),
								'enable-admin-booking-made-approval-mail' => array(
									'title' => esc_html__('Enable Admin Booking Made ( Need Approval ) E-Mail', 'tourmaster'),
									'type' => 'checkbox'
								),
								'admin-booking-made-approval-mail-title' => array(
									'title' => esc_html__('Admin Booking Made ( Need Approval ) E-Mail Title', 'tourmaster'),
									'type' => 'text',
									'default' => 'A new booking has been made. Please approve the booking so customer can pay.',
									'condition' => array('enable-admin-booking-made-approval-mail' => 'enable')
								),
								'admin-booking-made-approval-mail' => array(
									'title' => esc_html__('Admin Booking Made ( Need Approval ) E-Mail', 'tourmaster'),
									'type' => 'textarea',
									'default' => "<strong>Dear Admin,</strong>\nA new booking form {customer-name} has been made.\n\n{order-number}\n{booking-info}\n\nCustomer's Note: {customer-note}\n{spaces}\n\nYou can view <a href=\"{admin-transaction-link}\">the transaction here</a>\n\nPlease note that this customer can't process the payment untill you approvde thier booking.",
									'wrapper-class' => 'tourmaster-fullsize', 
									'condition' => array('enable-admin-booking-made-approval-mail' => 'enable')
								),
								'enable-admin-guest-booking-made-mail' => array(
									'title' => esc_html__('Enable Admin Guest Booking Made E-Mail', 'tourmaster'),
									'type' => 'checkbox'
								),
								'admin-guest-booking-made-mail-title' => array(
									'title' => esc_html__('Admin Guest Booking Made E-Mail Title', 'tourmaster'),
									'type' => 'text',
									'default' => 'A new booking has been made (Guest booked via email)',
									'condition' => array('enable-admin-guest-booking-made-mail' => 'enable')
								),
								'admin-guest-booking-made-mail' => array(
									'title' => esc_html__('Admin Guest Booking Made E-Mail', 'tourmaster'),
									'type' => 'textarea',
									'wrapper-class' => 'tourmaster-fullsize', 
									'condition' => array('enable-admin-guest-booking-made-mail' => 'enable'),
									'default' => "<strong>Dear Admin,</strong> \nA new booking form {customer-name} has been made.\n\n{order-number}\n{booking-info}\n{spaces}\n\nYou can view <a href=\"{admin-transaction-link}\">the transaction here</a>\n{spaces}\n\nCustomer's email : {customer-email}\nPlease contact to your customer back for further details."
								),
								'enable-admin-payment-submitted-mail' => array(
									'title' => esc_html__('Enable Payment Submitted E-Mail', 'tourmaster'),
									'type' => 'checkbox'
								),
								'admin-payment-submitted-mail-title' => array(
									'title' => esc_html__('Admin Payment Submitted E-Mail Title', 'tourmaster'),
									'type' => 'text',
									'default' => 'A new payment receipt has been submitted',
									'condition' => array('enable-admin-payment-submitted-mail' => 'enable')
								),
								'admin-payment-submitted-mail' => array(
									'title' => esc_html__('Admin Payment Submitted E-Mail', 'tourmaster'),
									'type' => 'textarea',
									'wrapper-class' => 'tourmaster-fullsize', 
									'default' => "<strong>Dear Admin,</strong>\nA new payment receipt has been submitted\n\n{order-number}\n{booking-info}\n{spaces}\n\nYou can view <a href=\"{admin-transaction-link}\">the transaction here</a>",
									'condition' => array('enable-admin-payment-submitted-mail' => 'enable')
								),
								'enable-admin-online-payment-made-mail' => array(
									'title' => esc_html__('Enable Online Full Payment Made E-Mail', 'tourmaster'),
									'type' => 'checkbox'
								),
								'admin-online-payment-made-mail-title' => array(
									'title' => esc_html__('Online Full Payment Made E-Mail Title', 'tourmaster'),
									'type' => 'text',
									'default' => 'A new booking has been made and successfully paid',
									'condition' => array('enable-admin-online-payment-made-mail' => 'enable')
								),
								'admin-online-payment-made-mail' => array(
									'title' => esc_html__('Online Full Payment Made E-Mail', 'tourmaster'),
									'type' => 'textarea',
									'wrapper-class' => 'tourmaster-fullsize', 
									'default' => "<strong>Dear Admin,</strong>\nA new booking has been made and sucessfully paid.\n\n{payment-method}\n{payment-date}\n{transaction-id}\n{spaces}\n\n{order-number}\n{booking-info}\n\nCustomer's Note: {customer-note}\n{spaces}\n\nYou can view <a href=\"{admin-transaction-link}\">the transaction here</a>",
									'condition' => array('enable-admin-online-payment-made-mail' => 'enable')
								),
								'enable-admin-deposit-payment-made-mail' => array(
									'title' => esc_html__('Enable Online Deposit Payment Made E-Mail', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'disable',
								),
								'admin-deposit-payment-made-mail-title' => array(
									'title' => esc_html__('Online Deposit Payment Made E-Mail Title', 'tourmaster'),
									'type' => 'text',
									'default' => 'New deposit has been successfully paid',
									'condition' => array('enable-admin-deposit-payment-made-mail' => 'enable')
								),
								'admin-deposit-payment-made-mail' => array(
									'title' => esc_html__('Online Deposit Payment Made E-Mail', 'tourmaster'),
									'type' => 'textarea',
									'default' => "<strong>Dear Admin,</strong>\nNew deposit has been successfully paid.\n\n{payment-method}\n{payment-date}\n{transaction-id}\n{amount}\n{spaces}\n\n{order-number}\n{booking-info}\n\nCustomer's Note: {customer-note}\n{spaces}\n\nYou can view <a href=\"{admin-transaction-link}\">the transaction here</a>", 
									'wrapper-class' => 'tourmaster-fullsize', 
									'condition' => array('enable-admin-deposit-payment-made-mail' => 'enable')
								),
							)
						),
						'customer-mail-content' => array(
							'title' => esc_html__('Customer E-Mail Content', 'tourmaster'),
							'options' => array(
								'enable-booking-made-mail' => array(
									'title' => esc_html__('Enable Booking Made E-Mail', 'tourmaster'),
									'type' => 'checkbox'
								),
								'booking-made-mail-title' => array(
									'title' => esc_html__('Booking Made E-Mail Title', 'tourmaster'),
									'type' => 'text',
									'default' => 'You have made a new booking',
									'condition' => array( 'enable-booking-made-mail' => 'enable' )
								),
								'booking-made-mail' => array(
									'title' => esc_html__('Booking Made E-Mail', 'tourmaster'),
									'type' => 'textarea',
									'wrapper-class' => 'tourmaster-fullsize',
									'default' => "<strong>Dear {customer-name}</strong>,\nYou have made a booking on\n\n{order-number}\n{booking-info}\n{total-price}\nCustomer's Note: {customer-note}\n\n<a href=\"{payment-link}\" >Make a payment</a>\n<a href=\"{invoice-link}\" >View Invoice</a>\n{divider}\nIf you wish to do the bank transfer. Please use the information below.\n\n<strong>Bank name: Center London Bank</strong>\n<strong>Account Number: 4455-4445-333</strong>\n<strong>Swift Code: XXCCVV</strong>\n\nAfter transferring, please submit payment receipt from your dashboard. We'll get back to you when the submission verified.",
									'condition' => array( 'enable-booking-made-mail' => 'enable' )
								),
								'enable-booking-made-approval-mail' => array(
									'title' => esc_html__('Enable Booking Made ( Need Approval ) E-Mail', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'disable'
								),
								'booking-made-approval-mail-title' => array(
									'title' => esc_html__('Booking Made ( Need Approval ) E-Mail Title', 'tourmaster'),
									'type' => 'text',
									'default' => 'You have made a new booking. Please wait for approval before processing payment',
									'condition' => array( 'enable-booking-made-approval-mail' => 'enable' )
								),
								'booking-made-approval-mail' => array(
									'title' => esc_html__('Booking Made ( Need Approval ) E-Mail', 'tourmaster'),
									'type' => 'textarea',
									'default' => "<strong>Dear {customer-name}</strong>,\nYou have made a booking on\n\n{order-number}\n{booking-info}\n{total-price}\nCustomer's Note: {customer-note}\n\nAt this point, please do nothing yet. \nAfter admin approve your booking, you will get email notification and then you can process payment later.",
									'wrapper-class' => 'tourmaster-fullsize',
									'condition' => array( 'enable-booking-made-approval-mail' => 'enable' )
								),
								'enable-booking-approve-mail' => array(
									'title' => esc_html__('Enable Booking Approve ( Ready For Payment ) E-Mail', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'disable'
								),
								'booking-approve-mail-title' => array(
									'title' => esc_html__('Booking Approve E-Mail Title', 'tourmaster'),
									'type' => 'text',
									'default' => 'Your booking has been approved to process the payment',
									'condition' => array( 'enable-booking-approve-mail' => 'enable' )
								),
								'booking-approve-mail' => array(
									'title' => esc_html__('Booking Approve E-Mail', 'tourmaster'),
									'type' => 'textarea',
									'default' => "<strong>Dear {customer-name}</strong>,\nYou have made a booking on\n\n{order-number}\n{booking-info}\n{total-price}\nCustomer's Note: {customer-note}\n\nAdmin has now approved your booking so you can process the payment. \nPlease note that this is not the final approve until you finish the payment.",
									'wrapper-class' => 'tourmaster-fullsize',
									'condition' => array( 'enable-booking-approve-mail' => 'enable' )
								),
								'enable-guest-booking-made-mail' => array(
									'title' => esc_html__('Enable Guest Booking Made E-Mail', 'tourmaster'),
									'type' => 'checkbox'
								),
								'guest-booking-made-mail-title' => array(
									'title' => esc_html__('Guest Booking Made E-Mail Title', 'tourmaster'),
									'type' => 'text',
									'condition' => array( 'enable-guest-booking-made-mail' => 'enable' ),
									'default' => 'You have made a new booking via email',
								),
								'guest-booking-made-mail' => array(
									'title' => esc_html__('Guest Booking Made E-Mail', 'tourmaster'),
									'type' => 'textarea',
									'wrapper-class' => 'tourmaster-fullsize',
									'condition' => array( 'enable-guest-booking-made-mail' => 'enable' ),
									'default' => "<strong>Dear {customer-name}</strong>,\nYou have made a booking on\n\n{order-number}\n{booking-info}\n{total-price}\n{divider}\nOur team will contact you back via the email you provided,\n{customer-email}"
								),
								'enable-customer-invoice' => array(
									'title' => esc_html__('Send Invoice To Customer E-mail', 'tourmaster'),
									'type' => 'checkbox'
								),
								'enable-payment-made-mail' => array(
									'title' => esc_html__('Enable Full Payment Made E-Mail', 'tourmaster'),
									'type' => 'checkbox'
								),
								'payment-made-mail-title' => array(
									'title' => esc_html__('Payment Made E-Mail Title', 'tourmaster'),
									'type' => 'text',
									'default' => 'Your payment has been successfully processed',
									'condition' => array( 'enable-payment-made-mail' => 'enable' )
								),
								'payment-made-mail' => array(
									'title' => esc_html__('Payment E-Made Mail', 'tourmaster'),
									'type' => 'textarea',
									'wrapper-class' => 'tourmaster-fullsize',
									'default' => "<strong>Dear {customer-name}</strong>,\nCongratulations! Your payment has been sucessfully processed.\n\n{order-number}\n{booking-info}\n{total-price}\nCustomer's Note: {customer-note}\n\n{payment-method}\n{payment-date}\n{transaction-id}\n{spaces}\n\nYou can view <a href=\"{invoice-link}\">the receipt here</a>",
									'condition' => array( 'enable-payment-made-mail' => 'enable' )
								),
								'enable-deposit-payment-made-mail' => array(
									'title' => esc_html__('Enable Deposit Payment Made E-Mail', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'disable'
								),
								'deposit-payment-made-mail-title' => array(
									'title' => esc_html__('Deposit Payment Made E-Mail Title', 'tourmaster'),
									'type' => 'text',
									'default' => 'Your deposit has been successfully processed',
									'condition' => array( 'enable-deposit-payment-made-mail' => 'enable' )
								),
								'deposit-payment-made-mail' => array(
									'title' => esc_html__('Deposit Payment E-Made Mail', 'tourmaster'),
									'type' => 'textarea',
									'default' => "<strong>Dear {customer-name}</strong>,\nCongratulations! Your deposit has been sucessfully processed.\n\n{order-number}\n{booking-info}\n{amount}\n\nCustomer's Note: {customer-note}\n\n{payment-method}\n{payment-date}\n{transaction-id}\n{spaces}\n\nYou can view <a href=\"{invoice-link}\">the receipt here</a>",
									'wrapper-class' => 'tourmaster-fullsize',
									'condition' => array( 'enable-deposit-payment-made-mail' => 'enable' )
								),
								'enable-booking-cancelled-mail' => array(
									'title' => esc_html__('Enable Booking Cancelled E-Mail', 'tourmaster'),
									'type' => 'checkbox',
								),
								'booking-cancelled-mail-title' => array(
									'title' => esc_html__('Booking Cancelled E-Mail Title', 'tourmaster'),
									'type' => 'text',
									'default' => 'Your booking has been cancelled',
									'condition' => array( 'enable-booking-cancelled-mail' => 'enable' )
								),
								'booking-cancelled-mail' => array(
									'title' => esc_html__('Booking Cancelled E-Mail', 'tourmaster'),
									'type' => 'textarea',
									'wrapper-class' => 'tourmaster-fullsize',
									'default' => "<strong>Dear {customer-name}</strong>,\nWe are here to inform that your booking has been cancelled.\n\n{order-number}\n{booking-info}",
									'condition' => array( 'enable-booking-cancelled-mail' => 'enable' )
								),
								'enable-booking-reject-mail' => array(
									'title' => esc_html__('Enable Booking Reject E-Mail', 'tourmaster'),
									'type' => 'checkbox'
								),
								'booking-reject-mail-title' => array(
									'title' => esc_html__('Booking Reject E-Mail Title', 'tourmaster'),
									'type' => 'text',
									'default' => 'Your booking has been rejected',
									'condition' => array( 'enable-booking-reject-mail' => 'enable' )
								),
								'booking-reject-mail' => array(
									'title' => esc_html__('Booking Reject E-Mail', 'tourmaster'),
									'type' => 'textarea',
									'wrapper-class' => 'tourmaster-fullsize',
									'default' => "<strong>Dear {customer-name}</strong>,\nWe are sorry to inform that your booking has been rejected. Your booking was rejected because of your payment was not successfully processed or your booking might be in the pending status for too long.\n\n{order-number}\n{booking-info}",
									'condition' => array( 'enable-booking-reject-mail' => 'enable' )
								),
								'enable-receipt-submission-mail' => array(
									'title' => esc_html__('Enable Receipt Submission E-Mail', 'tourmaster'),
									'type' => 'checkbox'
								),
								'receipt-submission-mail-title' => array(
									'title' => esc_html__('Receipt Submission E-Mail Title', 'tourmaster'),
									'type' => 'text',
									'default' => 'Thank you for payment submission.',
									'condition' => array( 'enable-receipt-submission-mail' => 'enable' )
								),
								'receipt-submission-mail' => array(
									'title' => esc_html__('Receipt Submission E-Mail', 'tourmaster'),
									'type' => 'textarea',
									'default' => "<strong>Dear {customer-name}</strong>,\nThank you for payment submission. After reveiwing, we will get back to you soon. \n\n{order-number}\n{booking-info}\n\n{payment-date}\n{payment-method}\n{amount}\n{transaction-id}\n\n<a href=\"{payment-link}\" >Make a payment</a>\n<a href=\"{invoice-link}\" >View Invoice</a>\n{divider}\nIf you wish to do the bank transfer. Please use the information below.\n\n<strong>Bank name: Center London Bank</strong>\n<strong>Account Number: 4455-4445-333</strong>\n<strong>Swift Code: XXCCVV</strong>\n\nAfter transferring, please submit payment receipt from your dashboard. We'll get back to you when the submission verified.",
									'wrapper-class' => 'tourmaster-fullsize',
									'condition' => array( 'enable-receipt-submission-mail' => 'enable' )
								),
								'enable-receipt-approve-mail' => array(
									'title' => esc_html__('Enable Receipt Approve E-Mail', 'tourmaster'),
									'type' => 'checkbox'
								),
								'receipt-approve-mail-title' => array(
									'title' => esc_html__('Receipt Approve E-Mail Title', 'tourmaster'),
									'type' => 'text',
									'default' => 'Your payment submission has been approved.',
									'condition' => array( 'enable-receipt-approve-mail' => 'enable' )
								),
								'receipt-approve-mail' => array(
									'title' => esc_html__('Receipt Approve E-Mail', 'tourmaster'),
									'type' => 'textarea',
									'default' => "<strong>Dear {customer-name}</strong>,\nYour payment submission has been approved. You can make another deposit or the final payment from your dashboard.\n\n{order-number}\n{booking-info}\n\n{payment-date}\n{payment-method}\n{amount}\n{transaction-id}\n\n<a href=\"{payment-link}\" >Make a payment</a>\n<a href=\"{invoice-link}\" >View Invoice</a>\n{divider}\nIf you wish to do the bank transfer. Please use the information below.\n\n<strong>Bank name: Center London Bank</strong>\n<strong>Account Number: 4455-4445-333</strong>\n<strong>Swift Code: XXCCVV</strong>\n\nAfter transferring, please submit payment receipt from your dashboard. We'll get back to you when the submission verified.",
									'wrapper-class' => 'tourmaster-fullsize',
									'condition' => array( 'enable-receipt-approve-mail' => 'enable' )
								),
								'enable-receipt-reject-mail' => array(
									'title' => esc_html__('Enable Receipt Reject E-Mail', 'tourmaster'),
									'type' => 'checkbox'
								),
								'receipt-reject-mail-title' => array(
									'title' => esc_html__('Receipt Reject E-Mail Title', 'tourmaster'),
									'type' => 'text',
									'default' => 'Your payment submission has been rejected.',
									'condition' => array( 'enable-receipt-reject-mail' => 'enable' )
								),
								'receipt-reject-mail' => array(
									'title' => esc_html__('Receipt Reject E-Mail', 'tourmaster'),
									'type' => 'textarea',
									'default' => "<strong>Dear {customer-name}</strong>,\nUnfortunately, your payment submission is not valid. Please review your payment receipt and submit again. \n\n{order-number}\n{booking-info}\n\n{payment-date}\n{payment-method}\n{amount}\n{transaction-id}\n\n<a href=\"{payment-link}\" >Make a payment</a>\n<a href=\"{invoice-link}\" >View Invoice</a>\n{divider}\nIf you wish to do the bank transfer. Please use the information below.\n\n<strong>Bank name: Center London Bank</strong>\n<strong>Account Number: 4455-4445-333</strong>\n<strong>Swift Code: XXCCVV</strong>\n\nAfter transferring, please submit payment receipt from your dashboard. We'll get back to you when the submission verified.",
									'wrapper-class' => 'tourmaster-fullsize',
									'condition' => array( 'enable-receipt-reject-mail' => 'enable' )
								),
							)
						), // customer mail content
						'enquiry-mail-content' => array(
							'title' => esc_html__('Enquiry E-Mail Content', 'tourmaster'),
							'options' => array(

								'enquiry-form-fields' => array(
									'title' => esc_html__('Enquiry Form Fields', 'tourmaster'),
									'type' => 'textarea',
									'description' => wp_kses(__('Leave blank for default. You can see how to create the fields <a href="http://support.goodlayers.com/document/2017/10/06/tourmaster-modifying-the-enquiry-form/" target="_blank" >here</a>', 'tourmaster'), array('a'=>array( 'href'=> array(), 'target'=>array())) )
								),
								'admin-enquiry-mail-title' => array(
									'title' => esc_html__('Enquiry E-Mail Title ( Admin )', 'tourmaster'),
									'type' => 'text',
									'default' => 'You received a new enquiry'
								),
								'admin-enquiry-mail-content' => array(
									'title' => esc_html__('Enquiry Mail Content ( Admin )', 'tourmaster'),
									'type' => 'textarea',
									'wrapper-class' => 'tourmaster-fullsize',
									'default' => "Dear Admin,\n\nYou received a new enquiry from {tour-name}\n\nFrom: {full-name}\n\nEmail: {email-address}\n\nMessage: {your-enquiry}"
								),
								'enquiry-mail-title' => array(
									'title' => esc_html__('Enquiry E-Mail Title ( Customer )', 'tourmaster'),
									'type' => 'text',
									'default' => esc_html__('You have submitted an enquiry', 'tourmaster')
								),
								'enquiry-mail-content' => array(
									'title' => esc_html__('Enquiry Mail Content ( Customter )', 'tourmaster'),
									'type' => 'textarea',
									'wrapper-class' => 'tourmaster-fullsize',
									'default' => "Dear {full-name},\n\nYou have sumiited an enquiry from {tour-name}\n\nMessage: {your-enquiry}\n\nOur team will contact you back via the email you provided, {email-address}\n\nThank you!"
								),
							)
						)
					)
				));

				// payment
				$room_payment_option = apply_filters('goodlayers_plugin_payment_option', array(
						
					'payment-settings' => array(
						'title' => esc_html__('Payment Settings', 'tourmaster'),
						'options' => array(

							'payment-admin-approval' => array(
								'title' => esc_html__('Needs Admin Approval Before Payment', 'tourmaster'),
								'type' => 'checkbox',
								'default' => 'disable',
								'description' => esc_html__('Booking payment method needs to be enable to use this feature.', 'tourmaster')
							),
							'enable-woocommerce-payment' => array(
								'title' => esc_html__('Enable Woocommerce Payment', 'tourmaster'),
								'type' => 'checkbox',
								'default' => 'disable',
								'description' => esc_html__('All others option will be omitted after enabling this option.', 'tourmaster'),
							),
							'payment-method' => array(
								'title' => esc_html__('Payment Method', 'tourmaster'),
								'type' => 'multi-combobox',
								'options' => array(
									'booking' => esc_html__('Booking', 'tourmaster'),
									'paypal' => esc_html__('Paypal', 'tourmaster'),
									'credit-card' => esc_html__('Credit Card', 'tourmaster'),
									'hipayprofessional' => esc_html__('Hipay Professional', 'tourmaster'),
								),
								'default' => array('booking', 'paypal', 'credit-card'),
								'condition' => array( 'enable-woocommerce-payment' => 'disable' ),
								'description' => esc_html__('You can use Ctrl/Command button to select multiple items or remove the selected item.', 'tourmaster'),
							),
							'enable-full-payment' => array(
								'title' => esc_html__('Enable Full Payment', 'tourmaster'),
								'type' => 'checkbox',
								'default' => 'enable',
								'condition' => array( 'enable-woocommerce-payment' => 'disable' )
							),
							'enable-deposit-payment' => array(
								'title' => esc_html__('Enable Deposit Payment', 'tourmaster'),
								'type' => 'checkbox',
								'default' => 'disable',
								'condition' => array( 'enable-woocommerce-payment' => 'disable' )
							),
							'deposit-payment-amount' => array(
								'title' => esc_html__('Deposit 1 Payment Amount (%)', 'tourmaster'),
								'type' => 'text',
								'default' => 0,
								'condition' => array( 'enable-deposit-payment' => 'enable', 'enable-woocommerce-payment' => 'disable' ),
								'description' => esc_html__('Only fill number here', 'tourmaster')
							),
							'deposit2-payment-amount' => array(
								'title' => esc_html__('Deposit 2 Payment Amount (%)', 'tourmaster'),
								'type' => 'text',
								'default' => 0,
								'condition' => array( 'enable-deposit-payment' => 'enable', 'enable-woocommerce-payment' => 'disable' ),
								'description' => esc_html__('Only fill number here', 'tourmaster')
							),
							'deposit3-payment-amount' => array(
								'title' => esc_html__('Deposit 3 Payment Amount (%)', 'tourmaster'),
								'type' => 'text',
								'default' => 0,
								'condition' => array( 'enable-deposit-payment' => 'enable', 'enable-woocommerce-payment' => 'disable' ),
								'description' => esc_html__('Only fill number here', 'tourmaster')
							),
							'deposit4-payment-amount' => array(
								'title' => esc_html__('Deposit 4 Payment Amount (%)', 'tourmaster'),
								'type' => 'text',
								'default' => 0,
								'condition' => array( 'enable-deposit-payment' => 'enable', 'enable-woocommerce-payment' => 'disable' ),
								'description' => esc_html__('Only fill number here', 'tourmaster')
							),
							'deposit5-payment-amount' => array(
								'title' => esc_html__('Deposit 5 Payment Amount (%)', 'tourmaster'),
								'type' => 'text',
								'default' => 0,
								'condition' => array( 'enable-deposit-payment' => 'enable', 'enable-woocommerce-payment' => 'disable' ),
								'description' => esc_html__('Only fill number here', 'tourmaster')
							),
							'credit-card-payment-gateway' => array(
								'title' => esc_html__('Credit Card Payment Gateway', 'tourmaster'),
								'type' => 'combobox',
								'options' => apply_filters('goodlayers_credit_card_payment_gateway_options', array('' => esc_html__('None', 'tourmaster'))),
								'condition' => array( 'enable-woocommerce-payment' => 'disable' )
							),
							'credit-card-service-fee' => array(
								'title' => esc_html__('Credit Card Service Fee (%)', 'tourmaster'),
								'type' => 'text',
								'default' => '',
								'description' => esc_html__('Fill only number here', 'tourmaster'),
								'condition' => array( 'enable-woocommerce-payment' => 'disable' )
							),
							'accepted-credit-card-type' => array(
								'title' => esc_html__('Accepted Credit Card Type', 'tourmaster'),
								'type' => 'multi-combobox',
								'options' => array(
									'visa' => esc_html__('visa', 'tourmaster'),
									'master-card' => esc_html__('Master Card', 'tourmaster'),
									'american-express' => esc_html__('American Express', 'tourmaster'),
									'jcb' => esc_html__('JCB', 'tourmaster'),
								),
								'default' => array('visa', 'master-card', 'american-express', 'jcb'),
								'condition' => array( 'enable-woocommerce-payment' => 'disable' ),
								'description' => esc_html__('Only display images below credit card option.', 'tourmaster')
							),
							'term-of-service-page' => array(
								'title' => esc_html__('Term Of Service Page', 'tourmaster'),
								'type' => 'combobox',
								'options' => tourmaster_get_post_list('page', true),
							),
							'privacy-statement-page' => array(
								'title' => esc_html__('Privacy Statement Page', 'tourmaster'),
								'type' => 'combobox',
								'options' => tourmaster_get_post_list('page', true),
							),
						)
					)
				));
				unset($room_payment_option['hipayprofessional']);
				unset($room_payment_option['payment-settings']['options']['payment-method']['options']['hipayprofessional']);

				$tourmaster_option->add_element(array(
					'title' => esc_html__('Payment', 'tourmaster'),
					'slug' => 'tourmaster_room_payment',
					'icon' => TOURMASTER_URL . '/images/plugin-options/general.png',
					'options' => $room_payment_option
				));

				// color
				$tourmaster_option->add_element(array(
					'title' => esc_html__('Color', 'tourmaster'),
					'slug' => 'tourmaster_room_color',
					'icon' => TOURMASTER_URL . '/images/plugin-options/color.png',
					'options' => array(
						
						'tourmaster-input-color' => array(
							'title' => esc_html__('Tourmaster Input', 'tourmaster'),
							'options' => array(

								'tourmaster-submit-button-color' => array(
									'title' => esc_html__('Button Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => 'body input[type="submit"].tourmaster-room-button{ color: #gdlr#; border-color: #gdlr#; }' . 
										'body input[type="submit"].tourmaster-room-button:hover{ color: #fff; background-color: #gdlr#; }' . 
										'.tourmaster-room-search-form .tourmaster-room-search-submit.tourmaster-style-solid{ background: #gdlr#; color: #fff; }' .
										'.tourmaster-room-search-form .tourmaster-room-search-submit.tourmaster-style-border{ color: #gdlr#; border-color: #gdlr#; }' . 
										'.tourmaster-body .tourmaster-room-button, ' .
										'.tourmaster-body .tourmaster-room-button:hover, ' .
										'.tourmaster-body .tourmaster-room-button.tourmaster-now-loading{ background-color: #gdlr#; color: #fff; }',
									'default' => '#0c0c0c'
								),
								'tourmaster-button-grey-text-color' => array(
									'title' => esc_html__('Button Grey Text Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-body .tourmaster-room-button.tourmaster-grey{ color: #gdlr#; }',
									'default' => '#141414'
								),
								'tourmaster-button-grey-background-color' => array(
									'title' => esc_html__('Button Grey Background Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-body .tourmaster-room-button.tourmaster-grey{ background-color: #gdlr#; }',
									'default' => '#f2f2f2'
								),
								'tourmaster-button-blue-text-color' => array(
									'title' => esc_html__('Button Blue Text Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-price-sidebar .tourmaster-room-button.tourmaster-blue, ' .
										'.tourmaster-room-payment-lb .goodlayers-payment-form button{ color: #gdlr#; }' . 
										'#goodlayers-authorize-payment-form .goodlayers-payment-button.submit{ color: #gdlr#; }',
									'default' => '#ffffff'
								),
								'tourmaster-button-blue-background-color' => array(
									'title' => esc_html__('Button Blue Background Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-price-sidebar .tourmaster-room-button.tourmaster-blue, ' .
										'.tourmaster-room-payment-lb .goodlayers-payment-form button{ background-color: #gdlr#; }' . 
										'#goodlayers-authorize-payment-form .goodlayers-payment-button.submit{ background-color: #gdlr#; }',
									'default' => '#0654b0'
								),


								'tourmaster-enquiry-box-text' => array(
									'title' => esc_html__('Enquiry Box Text', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => 
										'.tourmaster-body .tourmaster-form-field.tourmaster-room input[type="text"], ' . 
										'.tourmaster-body .tourmaster-form-field.tourmaster-room input[type="email"], ' . 
										'.tourmaster-body .tourmaster-form-field.tourmaster-room input[type="password"], ' .
										'.tourmaster-body .tourmaster-form-field.tourmaster-room textarea, ' . 
										'.tourmaster-body .tourmaster-form-field.tourmaster-room select, ' . 
										// '.tourmaster-form-field.tourmaster-with-border .tourmaster-combobox-list-display, ' . 
										// '.tourmaster-form-field .tourmaster-combobox-list-wrap ul, ' .
										'.tourmaster-body .tourmaster-form-field.tourmaster-room input[type="text"]:focus, ' .
										'.tourmaster-body .tourmaster-form-field.tourmaster-room input[type="email"]:focus, ' . 
										'.tourmaster-body .tourmaster-form-field.tourmaster-room input[type="password"]:focus, ' . 
										'.tourmaster-body .tourmaster-form-field.tourmaster-room textarea:focus{ color: #gdlr#; }',
									'default' => '#4b4b4b',
								),
								'tourmaster-input-box-background' => array(
									'title' => esc_html__('Enquiry Box Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => 
										'.tourmaster-body .tourmaster-form-field.tourmaster-room input[type="text"], ' . 
										'.tourmaster-body .tourmaster-form-field.tourmaster-room input[type="email"], ' . 
										'.tourmaster-body .tourmaster-form-field.tourmaster-room input[type="password"], ' .
										'.tourmaster-body .tourmaster-form-field.tourmaster-room textarea, ' . 
										'.tourmaster-body .tourmaster-form-field.tourmaster-room select, ' . 
										'.tourmaster-body .tourmaster-form-field.tourmaster-room input[type="text"]:focus, ' .
										'.tourmaster-body .tourmaster-form-field.tourmaster-room input[type="email"]:focus, ' . 
										'.tourmaster-body .tourmaster-form-field.tourmaster-room input[type="password"]:focus, ' . 
										'.tourmaster-body .tourmaster-form-field.tourmaster-room textarea:focus{ background: #gdlr#; }',
									'default' => '#f5f5f5',
								),
								'date-selection-background-color' => array(
									'title' => esc_html__('Date Selection Background Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-date-selection .tourmaster-custom-start-date, .tourmaster-room-date-selection .tourmaster-custom-end-date, ' .
										'.tourmaster-room-amount-selection .tourmaster-custom-amount-display,' .
										'.tourmaster-custom-amount-selection-wrap, .tourmaster-custom-datepicker-wrap, ' . 
										'.tourmaster-room-search-form.tourmaster-style-full-background{ background-color: #gdlr#; }',
									'default' => '#ffffff',
								),
								'single-date-selection-background-color' => array(
									'title' => esc_html__('Single/Search Date Selection Background Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-booking-wrap .tourmaster-room-date-selection .tourmaster-custom-start-date, ' .
										'.tourmaster-room-booking-wrap .tourmaster-room-date-selection .tourmaster-custom-end-date, ' .
										'.tourmaster-room-booking-wrap .tourmaster-room-amount-selection .tourmaster-custom-amount-display{ background: #gdlr#; } ' .
										'.tourmaster-template-room-search .tourmaster-room-date-selection .tourmaster-custom-start-date, ' .
										'.tourmaster-template-room-search .tourmaster-room-date-selection .tourmaster-custom-end-date, ' . 
										'.tourmaster-template-room-search .tourmaster-room-amount-selection .tourmaster-custom-amount-display{ background: #gdlr#; }',
									'default' => '#f5f5f5',
								),
								'date-selection-title-color' => array(
									'title' => esc_html__('Date Selection Title Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-date-selection .tourmaster-head, .tourmaster-custom-amount-display .tourmaster-head{ color: #gdlr#; }',
									'default' => '#8f8f8f',
								),
								'date-selection-text-color' => array(
									'title' => esc_html__('Date Selection Text Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-date-selection .tourmaster-tail, .tourmaster-custom-amount-display .tourmaster-tail, .tourmaster-custom-datepicker-close{ color: #gdlr#; }' . 
										'.tourmaster-custom-datepicker-calendar .ui-datepicker .ui-datepicker-title, ' .
										'.tourmaster-custom-datepicker-calendar .ui-datepicker .ui-datepicker-title select, ' .
										'.tourmaster-custom-datepicker-calendar .ui-datepicker-prev, ' .
										'.tourmaster-custom-datepicker-calendar .ui-datepicker-prev:hover, ' .
										'.tourmaster-custom-datepicker-calendar .ui-datepicker-next, ' .
										'.tourmaster-custom-datepicker-calendar .ui-datepicker-next:hover{ color: #gdlr#; } ' .
										'.tourmaster-custom-datepicker-calendar .ui-datepicker table tr th{ color: #gdlr#; } ' .
										'.tourmaster-body .tourmaster-custom-datepicker-calendar .ui-datepicker table tr td a, ' .
										'.tourmaster-body .tourmaster-custom-datepicker-calendar .ui-datepicker table tr td a:hover{ color: #gdlr#; }' . 
										'.tourmaster-body .tourmaster-custom-datepicker-calendar .ui-datepicker table tr td.tourmaster-start a, ' .
										'.tourmaster-body .tourmaster-custom-datepicker-calendar .ui-datepicker table tr td.tourmaster-start span, ' .
										'.tourmaster-body .tourmaster-custom-datepicker-calendar .ui-datepicker table tr td.tourmaster-end a, .tourmaster-body .tourmaster-custom-datepicker-calendar .ui-datepicker table tr td.tourmaster-end a:hover{ background: #gdlr#; } ' .
										'.tourmaster-body .tourmaster-custom-datepicker-calendar .ui-datepicker table tr td.tourmaster-end span{ background: #gdlr#; }' . 
										'.tourmaster-custom-amount-selection-item{ color: #gdlr#; }',
									'default' => '#000000',
								),
								'datepicker-head-background-color' => array(
									'title' => esc_html__('Datepicker Head Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-custom-datepicker-title{ background-color: #gdlr#; }',
									'default' => '#f6f6f6',
								),
								'datepicker-head-text-color' => array(
									'title' => esc_html__('Datepicker Head Text Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-custom-datepicker-title{ color: #gdlr#; }',
									'default' => '#767676',
								),
								'datepicker-date-disable-color' => array(
									'title' => esc_html__('Datepicker Date Disable Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-body .tourmaster-custom-datepicker-calendar .ui-datepicker table tr td span{ color: #gdlr#; }',
									'default' => '#c4c4c4',
								),
								'datepicker-date-interval-color' => array(
									'title' => esc_html__('Datepicker Date Interval Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-body .tourmaster-custom-datepicker-calendar .ui-datepicker table tr td.tourmaster-start:before, ' . 
										'.tourmaster-body .tourmaster-custom-datepicker-calendar .ui-datepicker table tr td.tourmaster-interval:before, ' . 
										'.tourmaster-body .tourmaster-custom-datepicker-calendar .ui-datepicker table tr td.tourmaster-end:before{ background-color: #gdlr#; }',
									'default' => '#f2f2f2',
								),
							)
						), // tourmaster-input-color

						'tourmaster-booking-color' => array(
							'title' => esc_html__('Tourmaster Booking', 'tourmaster'),
							'options' => array(

								'single-review-title-color' => array(
									'title' => esc_html__('Single Review Title/Rating Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.single-room .tourmaster-single-review-head, ' .
										'.single-room .tourmaster-single-review-content .tourmaster-single-review-user-name{ color: #gdlr#; } ' .
										'.single-room .tourmaster-single-review-head .tourmaster-room-rating i, ' .
										'.single-room .tourmaster-single-review-content .tourmaster-single-review-detail-rating i, ' .
										'.single-room .tourmaster-single-review-content .tourmaster-single-review-detail-date{ color: #gdlr#; }',
									'default' => '#000000',
								),

								'nav-checkout-button-color' => array(
									'title' => esc_html__('Navigation Checkout Button Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-navigation-checkout-wrap .tourmaster-room-navigation-checkout-button, .tourmaster-room-navigation-checkout-wrap .tourmaster-room-navigation-checkout-button:hover{ border-color: #gdlr#; color: #gdlr#; }',
									'default' => '#000000',
								),
								'nav-checkout-button-active-text-color' => array(
									'title' => esc_html__('Navigation Checkout Button Active Text Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-navigation-checkout-wrap.tourmaster-active .tourmaster-room-navigation-checkout-button{ color: #gdlr#; }',
									'default' => '#000000',
								),
								'nav-checkout-button-active-color' => array(
									'title' => esc_html__('Navigation Checkout Button Active Background Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-navigation-checkout-wrap.tourmaster-active .tourmaster-room-navigation-checkout-button{ border-color: #gdlr#; background: #gdlr#; }',
									'default' => '#ffffff',
								),
								'nav-checkout-active-number-background' => array(
									'title' => esc_html__('Navigation Checkout Button Active Number Background', 'tourmaster'),
									'type' => 'colorpicker', 
									'selector' => '.tourmaster-room-navigation-checkout-button .tourmaster-count{ background: #gdlr#; }',
									'default' => '#dedede'
								),
								'nav-checkout-active-number-text' => array(
									'title' => esc_html__('Navigation Checkout Button Active Number Text', 'tourmaster'),
									'type' => 'colorpicker', 
									'selector' => '.tourmaster-room-navigation-checkout-button .tourmaster-count{ color: #gdlr#; }',
									'default' => '#000'
								),
								'nav-checkout-button-submenu-background' => array(
									'title' => esc_html__('Navigation Checkout Button Submenu Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-cart-items{ background-color: #gdlr#; }',
									'default' => '#ffffff',
								),
								'nav-checkout-button-submenu-text' => array(
									'title' => esc_html__('Navigation Checkout Button Submenu Text', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-cart-items li, .tourmaster-room-cart-items .tourmaster-checkout-button{ color: #gdlr#; }',
									'default' => '#000000',
								),
								'nav-checkout-button-submenu-border' => array(
									'title' => esc_html__('Navigation Checkout Button Submenu Border', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-cart-items .tourmaster-checkout-button{ border-color: #gdlr#; }' . 
										'.tourmaster-room-cart-items li i.tourmaster-remove{ color: #gdlr#; }',
									'default' => '#d2d2d2',
								),

								'booking-bar-title-color' => array(
									'title' => esc_html__('Booking Bar Tab Title', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-booking-bar-title{ color: #gdlr#; }',
									'default' => '#bebebe',
								),
								'booking-bar-title-active-color' => array(
									'title' => esc_html__('Booking Bar Tab Title Active', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-booking-bar-title .tourmaster-active{ color: #gdlr#; border-color: #gdlr#; }',
									'default' => '#000000',
								),
								'booking-detail-title-color' => array(
									'title' => esc_html__('Booking Detail Title', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-booking-bar-summary .tourmaster-room-price .tourmaster-head, ' .
										'.tourmaster-room-booking-bar-summary .tourmaster-price, ' .
										'.tourmaster-room-booking-bar-summary .tourmaster-price .tourmaster-tail.tourmaster-em, ' .
										'.tourmaster-room-booking-bar-summary .tourmaster-or{ color: #gdlr#; }',
									'default' => '#141414',
								),
								'booking-detail-info-color' => array(
									'title' => esc_html__('Booking Detail Info', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-booking-bar-summary .tourmaster-room-price .tourmaster-tail, ' .
										'.tourmaster-room-booking-bar-summary .tourmaster-price .tourmaster-tail{ color: #gdlr#; }',
									'default' => '#a5a5a5',
								),

								'payment-step-border-color' => array(
									'title' => esc_html__('Payment Step Border Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => 'body .tourmaster-room-payment-step{ border-color: #gdlr#; }',
									'default' => '#e6e6e6',
								),
								'payment-step-text-color' => array(
									'title' => esc_html__('Payment Step Text Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-payment-step .tourmaster-step .tourmaster-head{ color: #gdlr#; }',
									'default' => '#d0d0d0',
								),
								'payment-step-bullet-color' => array(
									'title' => esc_html__('Payment Step Bullet Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-payment-step .tourmaster-step .tourmaster-bullet{ border-color: #gdlr#; }',
									'default' => '#dddddd',
								),
								'payment-step-text-active-color' => array(
									'title' => esc_html__('Payment Step Text Active Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-payment-step .tourmaster-step.tourmaster-active .tourmaster-head{ color: #gdlr#; }',
									'default' => '#000000',
								),
								'payment-step-bullet-active-color' => array(
									'title' => esc_html__('Payment Step Bullet Active Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-payment-step .tourmaster-step.tourmaster-active .tourmaster-bullet{ border-color: #gdlr#; }',
									'default' => '#5a5a5a',
								),

								'checkbox-border-color' => array(
									'title' => esc_html__('Checkbox Border Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => 'body .tourmaster-label-checkbox{ border-color: #gdlr#; }',
									'default' => '#c7c7c7'
								),
								'checkbox-icon-color' => array(
									'title' => esc_html__('Checkbox Icon Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-label-checkbox span{ color: #gdlr#; }',
									'default' => '#4f4f4f'
								),

								'payment-price-summary-title' => array(
									'title' => esc_html__('Payment Price Summary Title', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-price-summary-room-title{ color: #gdlr#; }' .
										'.tourmaster-room-price-summary-item .tourmaster-service, ' .
										'.tourmaster-room-price-summary-item .tourmaster-service-total{ color: #gdlr#; }' .
										'.tourmaster-room-single-price-breakdown .tourmaster-title, ' .
										'.tourmaster-room-single-price-breakdown .tourmaster-room-title, ' .
										'.tourmaster-room-single-price-breakdown .tourmaster-amount-title, ' .
										'.tourmaster-room-single-price-breakdown .tourmaster-room-total-price{ color: #gdlr#; }' .
										'.tourmaster-room-price-sidebar .tourmaster-price .tourmaster-head, ' .
										'.tourmaster-room-price-sidebar .tourmaster-price .tourmaster-tail.tourmaster-em{ color: #gdlr#; }' .
										'.tourmaster-room-price-sidebar .tourmaster-room-pay-type-item.tourmaster-active{ color: #gdlr#; }',
									'default' => '#141414'
								),
								'payment-price-summary-info' => array(
									'title' => esc_html__('Payment Price Summary Info', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-price-summary-room-duration{ color: #gdlr#; }' .
										'.tourmaster-room-price-summary-item .tourmaster-service-total .tourmaster-tail,' .
										'.tourmaster-room-price-summary-item .tourmaster-title .tourmaster-price{ color: #gdlr#; }' .
										'.tourmaster-room-single-price-breakdown{ color: #gdlr#; }',
									'default' => '#9e9e9e'
								),
								'payment-sidebar-price' => array(
									'title' => esc_html__('Payment Sidebar Price', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-price-sidebar .tourmaster-room-pay-type-item, ' .
										'.tourmaster-room-price-sidebar .tourmaster-price .tourmaster-tail{ color: #gdlr#; }',
									'default' => '#a5a5a5'
								),

								'payment-error-message-background' => array(
									'title' => esc_html__('Payment Error Message Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-payment-error, .tourmaster-room-booking-submit-error, ' .
										'.tourmaster-room-price-sidebar .tourmaster-error-message{ background-color: #gdlr#; color: #fff; }',
									'default' => '#f13232'
								),

							)
						),
						'tourmaster-single2' => array(
							'title' => esc_html__('Tourmaster Template 2', 'tourmaster'),
							'options' => array(
								
								'enquery-success-message-background' => array(
									'title' => esc_html__('Enquery Form Success Message Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-enquiry-form.tourmaster-room .tourmaster-enquiry-form-message.tourmaster-success{ background-color: #gdlr#; }',
									'default' => '#f1f8ff',
								),
								'enquery-success-message-border' => array(
									'title' => esc_html__('Enquery Form Success Message Border', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-enquiry-form.tourmaster-room .tourmaster-enquiry-form-message.tourmaster-success{ border-color: #gdlr#; }',
									'default' => '#e1ebfe',
								),
								'enquery-success-message-text' => array(
									'title' => esc_html__('Enquery Form Success Message Text', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-enquiry-form.tourmaster-room .tourmaster-enquiry-form-message.tourmaster-success{ color: #gdlr#; }',
									'default' => '#758ea8',
								),
								'enquery-failed-message-background' => array(
									'title' => esc_html__('Enquery Form Failed Message Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-enquiry-form.tourmaster-room .tourmaster-enquiry-form-message.tourmaster-failed{ background-color: #gdlr#; }',
									'default' => '#fff1f1',
								),
								'enquery-failed-message-border' => array(
									'title' => esc_html__('Enquery Form Failed Message Border', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-enquiry-form.tourmaster-room .tourmaster-enquiry-form-message.tourmaster-failed{ border-color: #gdlr#; }',
									'default' => '#fee1e1',
								),
								'enquiry-failed-message-text' => array(
									'title' => esc_html__('Enquiry Form Failed Message Text', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-enquiry-form.tourmaster-room .tourmaster-enquiry-form-message.tourmaster-failed{ color: #gdlr#; }',
									'default' => '#a87575',
								),
								
							)
						),

						'tourmaster-room-item' => array(
							'title' => esc_html__('Tourmaster Room Item', 'tourmaster'),
							'options' => array(
								'room-title-item-price-color' => array(
									'title' => esc_html__('Room Title Item Price', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-title-item .tourmaster-room-title-price .tourmaster-label, .tourmaster-room-title-item .tourmaster-room-title-price .tourmaster-price{ color: #gdlr#; }',
									'default' => '#0f0f0f',
								),
								'room-title-item-price-discount-color' => array(
									'title' => esc_html__('Room Title Item Price Discount', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-title-item .tourmaster-room-title-price .tourmaster-price-discount{ color: #gdlr#; }',
									'default' => '#a6a6a6',
								),

								'room-item-rating-color' => array(
									'title' => esc_html__('Room Item Rating Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-rating i{ color: #gdlr#; }',
									'default' => '#ffc100',
								),
								'room-item-title-color' => array(
									'title' => esc_html__('Room Item Title Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-item .tourmaster-room-title a, .tourmaster-room-item .tourmaster-info-wrap i{ color: #gdlr#; }' . 
										'.tourmaster-room-item .tourmaster-room-side-thumbnail .tourmaster-price-wrap.tourmaster-no-bg{ color: #gdlr# }',
									'default' => '#000000',
								),
								'room-item-info-color' => array(
									'title' => esc_html__('Room Item Info Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-item .tourmaster-info-wrap{ color: #gdlr#; }',
									'default' => '#848484',
								),
								'room-item-location-color' => array(
									'title' => esc_html__('Room Item Location Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-item .tourmaster-location{ color: #gdlr#; }',
									'default' => '#000000',
								),
								'room-item-grid4-info-icon-color' => array(
									'title' => esc_html__('Room Item Grid 4 Info Icon Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-grid4 .tourmaster-info-wrap i{ color: #gdlr#; }',
									'default' => '#33c390',
								),
								'room-item-frame-background-color' => array(
									'title' => esc_html__('Room Item Frame Background Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-item .tourmaster-grid-frame .tourmaster-room-content-wrap{ background-color: #gdlr#; }',
									'default' => '#ffffff',
								),
								'room-item-ribbon-background-color' => array(
									'title' => esc_html__('Room Item Ribbon Background Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-item .tourmaster-ribbon{ background-color: #gdlr#; }',
									'default' => '#e45154',
								),
								'room-item-price-text-color' => array(
									'title' => esc_html__('Room Item Price (no background) Text Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-item .tourmaster-price-wrap.tourmaster-no-bg{ color: #gdlr#; }' . 
										'.tourmaster-room-item .tourmaster-room-side-thumbnail .tourmaster-price-wrap.tourmaster-no-bg .tourmaster-tail{ color: #gdlr#; }',
									'default' => '#949494',
								),
								'room-item-price-background-color' => array(
									'title' => esc_html__('Room Item Price (with) Background Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-item .tourmaster-price-wrap.tourmaster-with-bg{ background-color: #gdlr#; }',
									'default' => '#000000',
								),
								'room-item-price-background-text-color' => array(
									'title' => esc_html__('Room Item Price (with) Background Text Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-item .tourmaster-price-wrap.tourmaster-with-bg{ color: #gdlr#; }',
									'default' => '#ffffff',
								),
								'room-item-price-background-discount-color' => array(
									'title' => esc_html__('Room Item Price (with) Background Discount Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-item .tourmaster-price-wrap.tourmaster-with-bg .tourmaster-price-discount{ color: #gdlr#; }',
									'default' => '#a6a6a6',
								),
								'room-item-category-thumbnail-background-color' => array(
									'title' => esc_html__('Room Item Category Thumbnail Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-grid5 .tourmaster-thumbnail-category{ background: #gdlr#; }',
									'default' => '#fff',
								),
								'room-item-category-thumbnail-text-color' => array(
									'title' => esc_html__('Room Item Category Thumbnail Text', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-grid5 .tourmaster-thumbnail-category a, .tourmaster-room-grid5 .tourmaster-thumbnail-category a:hover{ color: #gdlr#; }',
									'default' => '#888',
								),
								'room-item-button-text-color' => array(
									'title' => esc_html__('Room Item Button Text Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-item a.tourmaster-read-more.tourmaster-type-text, .tourmaster-room-item a.tourmaster-read-more.tourmaster-type-text:hover{ color: #gdlr#; }',
									'default' => '#000000',
								),
								'room-item-button-background-color' => array(
									'title' => esc_html__('Room Item Button Background Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-item a.tourmaster-read-more.tourmaster-type-button, .tourmaster-room-item a.tourmaster-read-more.tourmaster-type-button:hover{ background-color: #gdlr#; }',
									'default' => '#c4975e',
								),
								'room-item-button-background-text-color' => array(
									'title' => esc_html__('Room Item Button Background Text Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-item a.tourmaster-read-more.tourmaster-type-button, .tourmaster-room-item a.tourmaster-read-more.tourmaster-type-button:hover{ color: #gdlr#; }',
									'default' => '#ffffff',
								),
								'room-item-button-border-color' => array(
									'title' => esc_html__('Room Item Button Border Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-item .tourmaster-read-more.tourmaster-type-border-button{ border-color: #gdlr#; color: #gdlr#; }',
									'default' => '#959595',
								),
								'room-cart-empty-icon' => array(
									'title' => esc_html__('Room Cart Empty Icon', 'tourmaster'),
									'type' => 'colorpicker',
									'default' => '#cccccc',
								),
								'room-cart-empty-title' => array(
									'title' => esc_html__('Room Cart Empty Title', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-cart-empty .tourmaster-title{ color: #gdlr#; }',
									'default' => '#000000',
								),
								'room-cart-empty-caption' => array(
									'title' => esc_html__('Room Cart Empty Title', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-room-cart-empty .tourmaster-caption{ color: #gdlr#; }',
									'default' => '#9e9e9e',
								),
							)
						),
					)
				));

			}
		} // tourmaster_init_admin_option
	} // function_exists

