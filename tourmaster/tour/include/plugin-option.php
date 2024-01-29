<?php
	/*	
	*	Tourmaster Plugin
	*	---------------------------------------------------------------------
	*	creating the plugin option
	*	---------------------------------------------------------------------
	*/

	// return the custom stylesheet path
	if( !function_exists('tourmaster_get_style_custom') ){
		function tourmaster_get_style_custom($local = false){

			$upload_dir = wp_upload_dir();
			$filename = '/tourmaster-style-custom.css';
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
					return TOURMASTER_URL . '/style-custom.css';
				}
			}
		}
	}

	add_action('after_setup_theme', 'tourmaster_init_admin_option');
	if( !function_exists('tourmaster_init_admin_option') ){ 
		function tourmaster_init_admin_option(){
			if( is_admin() || is_customize_preview() ){
				$tourmaster_option = new tourmaster_admin_option(array(
					'page-title' => esc_html__('Tour Settings', 'tourmaster'),
					'menu-title' => esc_html__('Tour Settings', 'tourmaster'),
					'slug' => 'tourmaster_admin_option', 
					'filewrite' => tourmaster_get_style_custom(true),
					'position' => 120
				));

				// general
				$tourmaster_option->add_element(array(
					'title' => esc_html__('General', 'tourmaster'),
					'slug' => 'tourmaster_general',
					'icon' => TOURMASTER_URL . '/images/plugin-options/general.png',
					'options' => array(

						'general-settings' => array(
							'title' => esc_html__('General Settings', 'tourmaster'),
							'options' => array(
								'enable-tour-schema' => array(
									'title' => esc_html__('Enable Tour Schema / Structure data', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable',
								),
								'tour-schema-price-currency' => array(
									'title' => esc_html__('Price Currency', 'tourmaster'),
									'type' => 'text',
									'default' => '',
									'description' => esc_html__('Only use for tour schema / structure data.', 'tourmaster')
								),
								'header-price-decimal-digit' => array(
									'title' => esc_html__('Header Price Decimal Digit', 'tourmaster'),
									'type' => 'text',
									'default' => '0',
									'description' => esc_html__('Fill only number here', 'tourmaster')
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
								)
							)
						),

						'tour-manager' => array(
							'title' => esc_html__('Tour Manager', 'tourmaster'),
							'options' => array(
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
								'block-seat-status' => array(
									'title' => esc_html__('Block seat after user', 'tourmaster'),
									'type' => 'combobox',
									'options' => array(
										'book' => esc_html__('Booked', 'tourmaster'),
										'paid' => esc_html__('Paid', 'tourmaster')
									)
								),
								'tour-staff-capability' => array(
									'title' => esc_html__('Tour Staff Capability', 'tourmaster'),
									'type' => 'multi-combobox',
									'options' => array(
										'edit_tour' => esc_html__('Edit Tour', 'tourmaster'),
										'read_tour' => esc_html__('Read Tour', 'tourmaster'),
										'delete_tour' => esc_html__('Delete Tour', 'tourmaster'),
										'delete_tours' => esc_html__('Delete Tours', 'tourmaster'),
										'edit_tours' => esc_html__('Edit Tours', 'tourmaster'),
										'edit_others_tours' => esc_html__('Edit Others Tours', 'tourmaster'),
										'publish_tours' => esc_html__('Publish Tours', 'tourmaster'),
										'read_private_tours' => esc_html__('Read Private Tours', 'tourmaster'),
										'manage_tour_category' => esc_html__('Manage Tour Category', 'tourmaster'),
										'manage_tour_tag' => esc_html__('Manage Tour Tag', 'tourmaster'),
										'manage_tour_filter' => esc_html__('Manage Tour Filter', 'tourmaster'),
										'edit_coupon' => esc_html__('Edit Coupon', 'tourmaster'),
										'read_coupon' => esc_html__('Read Coupon', 'tourmaster'),
										'delete_coupon' => esc_html__('Delete Coupon', 'tourmaster'),
										'edit_coupons' => esc_html__('Edit Coupons', 'tourmaster'),
										'edit_others_coupons' => esc_html__('Edit Others Coupons', 'tourmaster'),
										'publish_coupons' => esc_html__('Publish Coupons', 'tourmaster'),
										'read_private_coupons' => esc_html__('Read Private Coupons', 'tourmaster'),
										'manage_tour_order' => esc_html__('Manage Tour Order', 'tourmaster'),
										'edit_service' => esc_html__('Edit Service', 'tourmaster'),
										'read_service' => esc_html__('Read Service', 'tourmaster'),
										'delete_service' => esc_html__('Delete Service', 'tourmaster'),
										'edit_services' => esc_html__('Edit Services', 'tourmaster'),
										'edit_others_services' => esc_html__('Edit Others Services', 'tourmaster'),
										'publish_services' => esc_html__('Publish Services', 'tourmaster'),
										'read_private_services' => esc_html__('Read Private Services', 'tourmaster'),
										'manage_tour_order' => esc_html__('Manage Tour Order', 'tourmaster'),
										'upload_files' => esc_html__('Upload Files', 'tourmaster'),
									),
									'default' => array( 
										'edit_tour', 'read_tour', 'delete_tour', 'delete_tours', 
										'edit_tours', 'edit_others_tours', 'publish_tours', 'read_private_tours',
										'manage_tour_category', 'manage_tour_tag', 'manage_tour_filter',
										'edit_coupon', 'read_coupon', 'delete_coupon', 
										'edit_coupons', 'edit_others_coupons', 'publish_coupons', 'read_private_coupons',
										'edit_service', 'read_service', 'delete_service', 
										'edit_services', 'edit_others_services', 'publish_services', 'read_private_services',
										'manage_tour_order', 'upload_files'
									)
								),
								'tour-author-capability' => array(
									'title' => esc_html__('Tour Author Capability', 'tourmaster'),
									'type' => 'multi-combobox',
									'options' => array(
										'edit_tour' => esc_html__('Edit Tour', 'tourmaster'),
										'read_tour' => esc_html__('Read Tour', 'tourmaster'),
										'delete_tour' => esc_html__('Delete Tour', 'tourmaster'),
										'delete_tours' => esc_html__('Delete Tours', 'tourmaster'),
										'edit_tours' => esc_html__('Edit Tours', 'tourmaster'),
										'edit_others_tours' => esc_html__('Edit Others Tours', 'tourmaster'),
										'publish_tours' => esc_html__('Publish Tours', 'tourmaster'),
										'read_private_tours' => esc_html__('Read Private Tours', 'tourmaster'),
										'manage_tour_category' => esc_html__('Manage Tour Category', 'tourmaster'),
										'manage_tour_tag' => esc_html__('Manage Tour Tag', 'tourmaster'),
										'manage_tour_filter' => esc_html__('Manage Tour Filter', 'tourmaster'),
										'edit_coupon' => esc_html__('Edit Coupon', 'tourmaster'),
										'read_coupon' => esc_html__('Read Coupon', 'tourmaster'),
										'delete_coupon' => esc_html__('Delete Coupon', 'tourmaster'),
										'edit_coupons' => esc_html__('Edit Coupons', 'tourmaster'),
										'edit_others_coupons' => esc_html__('Edit Others Coupons', 'tourmaster'),
										'publish_coupons' => esc_html__('Publish Coupons', 'tourmaster'),
										'read_private_coupons' => esc_html__('Read Private Coupons', 'tourmaster'),
										'manage_tour_order' => esc_html__('Manage Tour Order', 'tourmaster'),
										'upload_files' => esc_html__('Upload Files', 'tourmaster'),
									),
									'default' => array( 
										'edit_tour', 'read_tour', 'delete_tour', 'delete_tours', 
										'edit_tours', 'edit_others_tours', 'publish_tours', 'read_private_tours',
										'manage_tour_category', 'manage_tour_tag', 'manage_tour_filter',
										'edit_coupon', 'read_coupon', 'delete_coupon', 
										'edit_coupons', 'edit_others_coupons', 'publish_coupons', 'read_private_coupons',
										'edit_service', 'read_service', 'delete_service', 
										'edit_services', 'edit_others_services', 'publish_services', 'read_private_services',
										'upload_files'
									)
								),
							)
						),

						'payment-page' => array(
							'title' => esc_html__('Payment Page', 'tourmaster'),
							'options' => array(
								'payment-page-style' => array(
									'title' => esc_html__('Payment Page Style', 'tourmaster'),
									'type' => 'combobox',
									'options' => array(
										'style-1' => esc_html__('Style 1', 'tourmaster'),
										'style-2' => esc_html__('Style 2', 'tourmaster'),
									)
								),
								'payment-page-sidebar' => array(
									'title' => esc_html__('Payment Page Sidebar', 'tourmaster'),
									'type' => 'combobox',
									'options' => 'sidebar'
								),
								'payment-complete-bottom-text' => array(
									'title' => esc_html__('Payment Complete Bottom Text', 'tourmaster'),
									'type' => 'textarea',
								),
								'contact-detail-fields' => array(
									'title' => esc_html__('Contact Detail Fields', 'tourmaster'),
									'type' => 'textarea',
									'description' => wp_kses(__('Leave blank for default. You can see how to create the fields <a href="http://support.goodlayers.com/document/2018/05/01/tourmaster-modifying-the-contact-detail-fields-since-v3-0-8/" target="_blank" >here</a>', 'tourmaster'), array('a'=>array( 'href'=> array(), 'target'=>array())) )
								),
								'additional-traveller-fields' => array(
									'title' => esc_html__('Additional Traveller Fields', 'tourmaster'),
									'type' => 'textarea',
									'description' => wp_kses(__('Use to add new fields at the "traveller details" area. Learn more about this <a href="http://support.goodlayers.com/document/2018/05/03/tourmaster-modifying-the-traveller-detail-fields-since-v3-0-8/" target="_blank" >here</a>', 'tourmaster'), array('a'=>array( 'href'=> array(), 'target'=>array())) )
								),
							)
						),
						'archive-page' => array(
							'title' => esc_html__('Archive Page', 'tourmaster'),
							'options' => array(
								// 'archive-page' => array(
								// 	'title' => esc_html__('Archive ( Category ) Page', 'tourmaster'),
								// 	'type' => 'combobox',
								// 	'options' => tourmaster_get_post_list('page', true),
								// 	'description' => esc_html__('Choose the page to use header / footer of that page as template. Select "None" to use homepage settings.', 'tourmaster')
								// ),
								'archive-description' => array(
									'title' => esc_html__('Enable Archive Description', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable',
								),
								'archive-search-sidebar' => array(
									'title' => esc_html__('Enable Archive Search Sidebar', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'disable',
								),
								'search-sidebar' => array(
									'title' => esc_html__('Archive Tour Sidebar', 'tourmaster'),
									'type' => 'radioimage',
									'options' => 'sidebar',
									'default' => 'right',
									'wrapper-class' => 'tourmaster-fullsize'
								),
								'search-sidebar-left' => array(
									'title' => esc_html__('Archive Tour Sidebar Left', 'tourmaster'),
									'type' => 'combobox',
									'options' => 'sidebar',
									'default' => 'none',
									'condition' => array( 'search-sidebar'=>array('left', 'both') )
								),
								'search-sidebar-right' => array(
									'title' => esc_html__('Archive Tour Sidebar Right', 'tourmaster'),
									'type' => 'combobox',
									'options' => 'sidebar',
									'default' => 'none',
									'condition' => array( 'search-sidebar'=>array('right', 'both') )
								),
								'search-page-tour-style' => array(
									'title' => esc_html__('Archive Tour Style', 'tourmaster'),
									'type' => 'radioimage',
									'options' => array(
										'full' => TOURMASTER_URL . '/images/tour-style/full.jpg',
										'full-with-frame' => TOURMASTER_URL . '/images/tour-style/full-with-frame.jpg',
										'medium' => TOURMASTER_URL . '/images/tour-style/medium.jpg',
										'medium-with-frame' => TOURMASTER_URL . '/images/tour-style/medium-with-frame.jpg',
										'modern' => TOURMASTER_URL . '/images/tour-style/modern.jpg',
										'modern-no-space' => TOURMASTER_URL . '/images/tour-style/modern-no-space.jpg',
										'grid' => TOURMASTER_URL . '/images/tour-style/grid.jpg',
										'grid-with-frame' => TOURMASTER_URL . '/images/tour-style/grid-with-frame.jpg',
										'grid-no-space' => TOURMASTER_URL . '/images/tour-style/grid-no-space.jpg',
										'widget' => TOURMASTER_URL . '/images/tour-style/widget.jpg',
									),
									'default' => 'full',
									'wrapper-class' => 'tourmaster-fullsize'
								),
								'search-page-tour-grid-style' => array(
									'title' => esc_html__('Archive Grid Style', 'tourmaster'),
									'type' => 'combobox',
									'options' => array(
										'style-1' => esc_html__('Style 1', 'tourmaster'),
										'style-2' => esc_html__('Style 2', 'tourmaster'),
										'style-3' => esc_html__('Style 3', 'tourmaster'),
										'style-4' => esc_html__('Style 4', 'tourmaster'),
										'style-5' => esc_html__('Style 5', 'tourmaster'),
										'style-6' => esc_html__('Style 6', 'tourmaster'),
									),
									'condition' => array( 'search-page-tour-style' => array('grid', 'grid-with-frame', 'grid-no-space') )
								),
								'search-page-column-size' => array(
									'title' => esc_html__('Archive Column Size', 'tourmaster'),
									'type' => 'combobox',
									'options' => array( 60=>1, 30=>2, 20=>3, 15=>4, 12=>5 ),
									'default' => 20,
									'condition' => array( 'search-page-tour-style' => array('modern', 'modern-no-space', 'grid', 'grid-with-frame', 'grid-no-space') )
								),
								'search-page-thumbnail-size' => array(
									'title' => esc_html__('Archive Thumbnail Size', 'tourmaster'),
									'type' => 'combobox',
									'options' => 'thumbnail-size'
								),
								'search-page-tour-info' => array(
									'title' => esc_html__('Archive Tour Info', 'tourmaster'),
									'type' => 'multi-combobox',
									'options' => array(
										'duration-text' => esc_html__('Duration', 'tourmaster'),
										'availability' => esc_html__('Availability', 'tourmaster'),
										'departure-location' => esc_html__('Departure Location', 'tourmaster'),
										'return-location' => esc_html__('Return Location', 'tourmaster'),
										'minimum-age' => esc_html__('Minimum Age', 'tourmaster'),
										'maximum-people' => esc_html__('Maximum People', 'tourmaster'),
										'custom-excerpt' => esc_html__('Custom Excerpt ( In Tour Option )', 'tourmaster'),
									),

								),								
								'search-page-excerpt' => array(
									'title' => esc_html__('Archive Excerpt Type', 'tourmaster'),
									'type' => 'combobox',
									'options' => array(
										'specify-number' => esc_html__('Specify Number', 'tourmaster'),
										'show-all' => esc_html__('Show All ( use <!--more--> tag to cut the content )', 'tourmaster'),
										'none' => esc_html__('Disable Exceprt', 'tourmaster'),
									),
									'condition' => array( 'search-page-tour-style' => array('full', 'full-with-frame', 'medium', 'medium-with-frame', 'grid', 'grid-with-frame', 'grid-no-space') ),
									'default' => 'specify-number',
								),
								'search-page-excerpt-number' => array(
									'title' => esc_html__('Archive Excerpt Number', 'tourmaster'),
									'type' => 'text',
									'default' => 55,
									'condition' => array( 'search-page-tour-style' => array('full', 'full-with-frame', 'medium', 'medium-with-frame', 'grid', 'grid-with-frame', 'grid-no-space'), 'search-page-excerpt' => 'specify-number' )
								),
								'search-page-tour-rating' => array(
									'title' => esc_html__('Archive Tour Rating', 'tourmaster'),
									'type' => 'checkbox',
									'condition' => array( 'search-page-tour-style' => array('full', 'full-with-frame', 'medium', 'medium-with-frame', 'grid', 'grid-with-frame', 'grid-no-space') ),
									'default' => 'enable'
								),
								'search-page-tour-frame-border-radius' => array(
									'title' => esc_html__('Archive Tour Frame/Thumbnail Border Radius', 'tourmaster'),
									'type' => 'text',
									'data-input-type' => 'pixel',
								),
								'search-page-tour-frame-shadow-size' => array(
									'title' => esc_html__('Archive Tour Shadow Size ( for image/frame )', 'tourmaster'),
									'type' => 'text',
									'data-input-type' => 'pixel',
								),
								'search-page-tour-frame-shadow-x' => array(
									'title' => esc_html__('Archive Tour Shadow X ( for image/frame )', 'tourmaster'),
									'type' => 'text',
									'data-input-type' => 'pixel',
								),
								'search-page-tour-frame-shadow-y' => array(
									'title' => esc_html__('Archive Tour Shadow Y ( for image/frame )', 'tourmaster'),
									'type' => 'text',
									'data-input-type' => 'pixel',
								),
								'search-page-tour-frame-shadow-color' => array(
									'title' => esc_html__('Archive Tour Shadow Color ( for image/frame )', 'tourmaster'),
									'type' => 'colorpicker'
								),
								'search-page-tour-frame-shadow-opacity' => array(
									'title' => esc_html__('Archive Tour Shadow Opacity ( for image/frame )', 'tourmaster'),
									'type' => 'text',
									'default' => '0.2',
									'description' => esc_html__('Fill the number between 0.01 to 1', 'tourmaster')
								),		
								'search-page-tour-title-font-size' => array(
									'title' => esc_html__('Archive Tour Title Font Size', 'tourmaster'),
									'type' => 'text',
									'data-input-type' => 'pixel',
								),
								'search-page-tour-title-font-weight' => array(
									'title' => esc_html__('Archive Tour Title Font Weight', 'tourmaster'),
									'type' => 'text',
									'description' => esc_html__('Eg. lighter, bold, normal, 300, 400, 600, 700, 800', 'tourmaster')
								),
								'search-page-tour-title-letter-spacing' => array(
									'title' => esc_html__('Archive Tour Title Letter Spacing', 'tourmaster'),
									'type' => 'text',
									'data-input-type' => 'pixel',
								),
								'search-page-tour-title-text-transform' => array(
									'title' => esc_html__('Archive Tour Title Text Transform', 'tourmaster'),
									'type' => 'combobox',
									'data-type' => 'text',
									'options' => array(
										'' => esc_html__('Default', 'tourmaster'),
										'none' => esc_html__('None', 'tourmaster'),
										'uppercase' => esc_html__('Uppercase', 'tourmaster'),
										'lowercase' => esc_html__('Lowercase', 'tourmaster'),
										'capitalize' => esc_html__('Capitalize', 'tourmaster'),
									)
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
								'search-page-style' => array(
									'title' => esc_html__('Search Page Style', 'tourmaster'),
									'type' => 'combobox', 
									'options' => array(
										'style-1' => esc_html__('Style 1', 'tourmaster'),
										'style-2' => esc_html__('Style 2', 'tourmaster'),
									)
								),	
								'search-month-amount' => array(
									'title' => esc_html__('Tour Search Item Month Amount (Number)', 'tourmaster'),
									'type' => 'text',
									'default' => '12',
									'description' => esc_html__('Display number of specified month when select the "month" option in search item.', 'tourmaster')
								),
								'tour-search-item-num-fetch' => array(
									'title' => esc_html__('Tour Search Display Amount', 'tourmaster'),
									'type' => 'text',
									'default' => 9,
								),
								'tour-search-default-style' => array(
									'title' => esc_html__('Tour Search Default Style', 'tourmaster'),
									'type' => 'combobox',
									'options' => array(
										'list' => esc_html__('List', 'tourmaster'),
										'grid' => esc_html__('Grid', 'tourmaster'),
									),
									'default' => 'list'
								),
								'tour-search-item-style' => array(
									'title' => esc_html__('Tour Search List Style', 'tourmaster'),
									'type' => 'combobox',
									'options' => array(
										'full' => esc_html__('Full', 'tourmaster'),
										'full-with-frame' => esc_html__('Full With Frame', 'tourmaster'),
										'medium' => esc_html__('Medium', 'tourmaster'),
										'medium-with-frame' => esc_html__('Medium With Frame', 'tourmaster'),
										'widget' => esc_html__('Widget', 'tourmaster'),
									),
									'default' => 'medium-with-frame'
								),
								'tour-search-item-thumbnail' => array(
									'title' => esc_html__('Tour Search List Thumbnail', 'tourmaster'),
									'type' => 'combobox',
									'options' => 'thumbnail-size',
									'default' => 'large',
								),
								'tour-search-item-info' => array(
									'title' => esc_html__('Tour Search Item Info', 'tourmaster'),
									'type' => 'multi-combobox',
									'options' => array(
										'duration-text' => esc_html__('Duration', 'tourmaster'),
										'availability' => esc_html__('Availability', 'tourmaster'),
										'departure-location' => esc_html__('Departure Location', 'tourmaster'),
										'return-location' => esc_html__('Return Location', 'tourmaster'),
										'minimum-age' => esc_html__('Minimum Age', 'tourmaster'),
										'maximum-people' => esc_html__('Maximum People', 'tourmaster'),
										'custom-excerpt' => esc_html__('Custom Excerpt ( In Tour Option )', 'tourmaster'),
									),
								),								
								'tour-search-item-excerpt' => array(
									'title' => esc_html__('Tour Search Item Excerpt', 'tourmaster'),
									'type' => 'combobox',
									'options' => array(
										'specify-number' => esc_html__('Specify Number', 'tourmaster'),
										'show-all' => esc_html__('Show All ( use <!--more--> tag to cut the content )', 'tourmaster'),
										'none' => esc_html__('Disable Exceprt', 'tourmaster'),
									),
									'default' => 'specify-number',
								),
								'tour-search-item-excerpt-number' => array(
									'title' => esc_html__('Tour Search Item Excerpt Number', 'tourmaster'),
									'type' => 'text',
									'condition' => array( 'tour-search-item-excerpt' => 'specify-number' ),
									'default' => 55,
								),
								'tour-search-item-rating' => array(
									'title' => esc_html__('Tour Search Item Rating', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable'
								),
								'tour-search-item-frame-border-radius' => array(
									'title' => esc_html__('Tour Search Frame/Thumbnail Border Radius', 'tourmaster'),
									'type' => 'text',
									'data-input-type' => 'pixel',
								),
								'tour-search-item-frame-shadow-size' => array(
									'title' => esc_html__('Tour Search Item Shadow Size ( for image/frame )', 'tourmaster'),
									'type' => 'text',
									'data-input-type' => 'pixel',
								),
								'tour-search-item-frame-shadow-x' => array(
									'title' => esc_html__('Tour Search Item Shadow X ( for image/frame )', 'tourmaster'),
									'type' => 'text',
									'data-input-type' => 'pixel',
								),
								'tour-search-item-frame-shadow-y' => array(
									'title' => esc_html__('Tour Search Item Shadow Y ( for image/frame )', 'tourmaster'),
									'type' => 'text',
									'data-input-type' => 'pixel',
								),
								'tour-search-item-frame-shadow-color' => array(
									'title' => esc_html__('Tour Search Item Shadow Color ( for image/frame )', 'tourmaster'),
									'type' => 'colorpicker'
								),
								'tour-search-item-frame-shadow-opacity' => array(
									'title' => esc_html__('Tour Search Item Shadow Opacity ( for image/frame )', 'tourmaster'),
									'type' => 'text',
									'default' => '0.2',
									'description' => esc_html__('Fill the number between 0.01 to 1', 'tourmaster')
								),
								'tour-search-order-filterer-grid-style' => array(
									'title' => esc_html__('Order Filterer Grid Style', 'tourmaster'),
									'type' => 'combobox',
									'options' => array(
										'none' => esc_html__('None', 'tourmaster'),
										'modern' => esc_html__('Modern', 'tourmaster'),
										'modern-no-space' => esc_html__('Modern No Space', 'tourmaster'),
										'grid' => esc_html__('Grid', 'tourmaster'),
										'grid-with-frame' => esc_html__('Grid With Frame', 'tourmaster'),
										'grid-no-space' => esc_html__('Grid No Space', 'tourmaster'),
									),
									'default' => 'none'
								),
								'tour-search-order-filterer-grid-style-type' => array(
									'title' => esc_html__('Order Filterer Grid Style Type', 'tourmaster'),
									'type' => 'combobox',
									'options' => array(
										'style-1' => esc_html__('Style 1', 'tourmaster'),
										'style-2' => esc_html__('Style 2', 'tourmaster'),
										'style-3' => esc_html__('Style 3', 'tourmaster'),
										'style-4' => esc_html__('Style 4', 'tourmaster'),
										'style-5' => esc_html__('Style 5', 'tourmaster'),
										'style-6' => esc_html__('Style 6', 'tourmaster'),
									),
									'condition' => array( 'tour-search-order-filterer-grid-style' => array('grid', 'grid-with-frame', 'grid-no-space') )
								),
								'tour-search-order-filterer-grid-style-thumbnail' => array(
									'title' => esc_html__('Order Filterer Grid Style Thumbnail', 'tourmaster'),
									'type' => 'combobox',
									'options' => 'thumbnail-size',
									'default' => 'large',
									'condition' => array( 'tour-search-order-filterer-grid-style' => array('modern', 'modern-no-space', 'grid', 'grid-with-frame', 'grid-no-space') )
								),
								'tour-search-order-filterer-grid-style-column' => array(
									'title' => esc_html__('Order Filterer Grid Style Column', 'tourmaster'),
									'type' => 'combobox',
									'options' => array( 60=>1, 30=>2, 20=>3, 15=>4, 12=>5 ),
									'default' => 30,
									'condition' => array( 'tour-search-order-filterer-grid-style' => array('modern', 'modern-no-space', 'grid', 'grid-with-frame', 'grid-no-space') )
								),
								'enable-tour-search-filter' => array(
									'title' => esc_html__('Enable Tour Search Filter', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'disable'
								),
								'tour-search-filter-state' => array(
									'title' => esc_html__('Tour Search Filter State', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'disable',
									'condition' => array('enable-tour-search-filter' => 'enable'),
								),
								'tour-search-fields' => array(
									'title' => esc_html__('Search Filter Fields', 'tourmaster'),
									'type' => 'multi-combobox',
									'options' => tourmaster_get_tour_search_fields('default'),
									'condition' => array('enable-tour-search-filter' => 'enable'),
									'description' => esc_html__('You can use Ctrl/Command button to select multiple items or remove the selected item. Leave this field blank to select all items in the list.', 'tourmaster'),
								),
								'tour-search-rating-field' => array(
									'title' => esc_html__('Enable Search Filter Rating', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'disable',
									'condition' => array('enable-tour-search-filter' => 'enable'),
								),
								'tour-search-filters' => array(
									'title' => esc_html__('Select Search Custom Filter', 'tourmaster'),
									'type' => 'multi-combobox',
									'options' => tourmaster_get_tour_search_fields('custom'),
									'condition' => array('enable-tour-search-filter' => 'enable'),
									'description' => esc_html__('You can use Ctrl/Command button to select multiple items or remove the selected item.', 'tourmaster'),
								),
								'search-not-found-fields' => array(
									'title' => esc_html__('Search Not Found Fields', 'tourmaster'),
									'type' => 'multi-combobox',
									'options' => tourmaster_get_tour_search_fields(),
									'condition' => array('enable-tour-search-filter' => 'disable'),
									'description' => esc_html__('You can use Ctrl/Command button to select multiple items or remove the selected item. Leave this field blank to select all items in the list.', 'tourmaster'),
								),
								'search-not-found-style' => array(
									'title' => esc_html__('Search Not Found Style', 'tourmaster'),
									'type' => 'combobox',
									'options' => array(
										'column' => esc_html__('Column', 'tourmaster'),
										'half' => esc_html__('Half', 'tourmaster'),
										'full' => esc_html__('Full', 'tourmaster'),
									),
									'condition' => array('enable-tour-search-filter' => 'disable'),
								),		
								'tour-search-title-font-size' => array(
									'title' => esc_html__('Tour Search Item Title Font Size', 'tourmaster'),
									'type' => 'text',
									'data-input-type' => 'pixel',
								),
								'tour-search-title-font-weight' => array(
									'title' => esc_html__('Tour Search Item Title Font Weight', 'tourmaster'),
									'type' => 'text',
									'description' => esc_html__('Eg. lighter, bold, normal, 300, 400, 600, 700, 800', 'tourmaster')
								),
								'tour-search-title-letter-spacing' => array(
									'title' => esc_html__('Tour Search Item Title Letter Spacing', 'tourmaster'),
									'type' => 'text',
									'data-input-type' => 'pixel',
								),
								'tour-search-title-text-transform' => array(
									'title' => esc_html__('Tour Search Item Title Text Transform', 'tourmaster'),
									'type' => 'combobox',
									'data-type' => 'text',
									'options' => array(
										'' => esc_html__('Default', 'tourmaster'),
										'none' => esc_html__('None', 'tourmaster'),
										'uppercase' => esc_html__('Uppercase', 'tourmaster'),
										'lowercase' => esc_html__('Lowercase', 'tourmaster'),
										'capitalize' => esc_html__('Capitalize', 'tourmaster'),
									)
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
									'selector' => '.tourmaster-invoice-logo{ width: #gdlr#; }'
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
							'title' => esc_html__('Single Tour', 'tourmaster'),
							'options' => array(
								'single-tour-style' => array(
									'title' => esc_html__('Single Tour Style', 'tourmaster'),
									'type' => 'combobox',
									'options' => array(
										'blank' => esc_html__('Blank', 'tourmaster'),
										'style-1' => esc_html__('Style 1', 'tourmaster'),
										'style-2' => esc_html__('Style 2', 'tourmaster'),
										'style-3' => esc_html__('Style 3', 'tourmaster'),
									),
									'description' => esc_html__('Blank style need to enable booking bar via Sidebar Wrapper setting', 'tourmaster')
								),
								'booking-bar-top-margin' => array(
									'title' => esc_html__('Booking Bar Top Margin', 'tourmaster'),
									'type' => 'text',
									'data-input-type' => 'pixel',
									'data-type' => 'pixel',
									'selector' => '.tourmaster-tour-style-2 .tourmaster-tour-booking-bar-wrap, .tourmaster-tour-style-2 .tourmaster-tour-booking-bar-anchor{ margin-top: #gdlr#; }',
									'condition' => array( 'single-tour-style' => 'style-2' )
								),
								'booking-bar-radius' => array(
									'title' => esc_html__('Booking Bar Radius', 'tourmaster'),
									'type' => 'text',
									'data-input-type' => 'pixel',
									'data-type' => 'pixel',
									'selector' => '.tourmaster-tour-style-blank .tourmaster-tour-booking-bar-outer, .tourmaster-tour-style-2 .tourmaster-tour-booking-bar-outer{ border-radius: #gdlr#; -webkit-border-radius: #gdlr#; -moz-border-radius: #gdlr#; }',
									'condition' => array( 'single-tour-style' => array('style-2', 'blank') )
								),
								'booking-bar-shadow-x' => array(
									'title' => esc_html__('Booking Bar Shadow X', 'infinite'),
									'type' => 'text',
									'data-input-type' => 'pixel',
									'condition' => array( 'single-tour-style' => array('blank', 'style-2') )
								),
								'booking-bar-shadow-y' => array(
									'title' => esc_html__('Booking Bar Shadow Y', 'infinite'),
									'type' => 'text',
									'data-input-type' => 'pixel',
									'condition' => array( 'single-tour-style' => array('blank', 'style-2') )
								),
								'booking-bar-shadow-size' => array(
									'title' => esc_html__('Booking Bar Shadow Size', 'infinite'),
									'type' => 'text',
									'data-type' => 'pixel',
									'data-input-type' => 'pixel',
									'selector-extra' => true,
									'selector' => '.tourmaster-tour-style-blank .tourmaster-tour-booking-bar-outer, ' . 
										'.tourmaster-tour-style-2 .tourmaster-tour-booking-bar-outer, ' .
										'.tourmaster-tour-booking-bar-outer{ ' . 
										'box-shadow: <booking-bar-shadow-x>t <booking-bar-shadow-y>t #gdlr# rgba(0, 0, 0, 0.1); ' . 
										'-webkit-box-shadow: <booking-bar-shadow-x>t <booking-bar-shadow-y>t #gdlr# rgba(0, 0, 0, 0.1); ' . 
										'-moz-box-shadow: <booking-bar-shadow-x>t <booking-bar-shadow-y>t #gdlr# rgba(0, 0, 0, 0.1); }',
								),
								'mobile-content-read-more' => array(
									'title' => esc_html__('Mobile Content Read More', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable'
								),
								'mobile-booking-bar-position' => array(
									'title' => esc_html__('Mobile Booking Bar Position', 'tourmaster'),
									'type' => 'combobox',
									'options' => array(
										'bottom' => esc_html__('Bottom', 'tourmaster'),
										'top' => esc_html__('Top', 'tourmaster'),
									)
								),
								'tour-header-top-padding' => array(
									'title' => esc_html__('Tour Header Top Padding', 'tourmaster'),
									'type' => 'fontslider',
									'data-type' => 'pixel',
									'data-min' => '0',
									'data-max' => '1000',
			 						'default' => '400px',
									'selector' => '.tourmaster-single-header-title-wrap{ padding-top: #gdlr#; }'
								),
								'tour-header-bottom-padding' => array(
									'title' => esc_html__('Tour Header Bottom Padding', 'tourmaster'),
									'type' => 'fontslider',
									'data-type' => 'pixel',
									'data-min' => '0',
									'data-max' => '200',
			 						'default' => '45px',
									'selector' => '.tourmaster-single-header-title-wrap{ padding-bottom: #gdlr#; }'
								),
								'tour-header-overlay-opacity' => array(
									'title' => esc_html__('Tour Header Overlay Opacity', 'tourmaster'),
									'type' => 'fontslider',
									'data-type' => 'opacity',
									'default' => '30',
									'selector' => '.tourmaster-single-header-background-overlay{ opacity: #gdlr#; }'
								),
								'single-tour-header-gradient' => array(
									'title' => esc_html__('Single Tour Header Gradient', 'tourmaster'),
									'type' => 'combobox',
									'options' => array(
										'both' => esc_html__('Both', 'tourmaster'),
										'top' => esc_html__('Top', 'tourmaster'),
										'bottom' => esc_html__('Bottom', 'tourmaster'),
										'none' => esc_html__('None', 'tourmaster'),
									),
									'default' => 'both'
								),
								'single-tour-top-gradient-size' => array(
									'title' => esc_html__('Single Tour Top Gradient Size', 'tourmaster'),
									'type' => 'fontslider',
									'data-type' => 'pixel',
									'data-min' => '0',
									'data-max' => '1000',
			 						'default' => '500px',
									'selector' => '.tourmaster-single-header-top-overlay{ height: #gdlr#; }',
									'condition' => array( 'single-tour-header-gradient' => array('top', 'both') )
								),
								'single-tour-bottom-gradient-size' => array(
									'title' => esc_html__('Single Tour Bottom Gradient Size', 'tourmaster'),
									'type' => 'fontslider',
									'data-type' => 'pixel',
									'data-min' => '0',
									'data-max' => '1000',
			 						'default' => '300px',
									'selector' => '.tourmaster-single-header-overlay{ height: #gdlr#; }',
									'condition' => array( 'single-tour-header-gradient' => array('bottom', 'both') )
								),
								'update-header-price' => array(
									'title' => esc_html__('Update Header price', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable',
									'description' => esc_html__('Update header price after all necessary information is selected', 'tourmaster')
								),
								'show-remaining-available-number' => array(
									'title' => esc_html__('Show Remaining Available Number', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'disable'
								),
								'max-dropdown-people-amount' => array(
									'title' => esc_html__('Max Dropdown People Amount', 'tourmaster'),
									'type' => 'text',
									'default' => '5',
									'description' => esc_html__('Will be overrided by "Maximum People Per Booking" option in each tour', 'tourmaster')
								),
								'max-dropdown-room-amount' => array(
									'title' => esc_html__('Max Dropdown Room Amount', 'tourmaster'),
									'type' => 'text',
									'default' => '10'
								),
								'require-adult-to-book-tour' => array(
									'title' => esc_html__('Require an "adult" to book the tour', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'disable',
									'description' => esc_html__('Only for variable price tour', 'tourmaster')
								),
								'single-tour-extra-booking-info' => array(
									'title' => esc_html__('Single Tour Extra Booking Info', 'tourmaster'),
									'type' => 'textarea',
									'description' => wp_kses(__('You can see how to create the fields <a href="http://support.goodlayers.com/document/2017/10/06/tourmaster-modifying-the-enquiry-form/" target="_blank" >here</a>', 'tourmaster'), array('a'=>array( 'href'=> array(), 'target'=>array())) )
								),
								'single-tour-default-sidebar' => array(
									'title' => esc_html__('Single Tour Default Sidebar ( Widget )', 'tourmaster'),
									'type' => 'combobox',
									'options' => 'sidebar'
								),
								'enable-single-sidebar-widget-on-mobile' => array(
									'title' => esc_html__('Enable Single Sidebar Widget On Mobile', 'tourmaster'),
									'type' => 'checkbox',
									'options' => 'enable'
								),
								'enable-single-related-tour' => array(
									'title' => esc_html__('Enable Single Related Tour', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable'
								),
								'single-related-tour-style' => array( 
									'title' => esc_html__('Tour Style', 'tourmaster'),
									'type' => 'radioimage',
									'options' => array(
										'modern' => TOURMASTER_URL . '/images/tour-style/modern.jpg',
										'modern-no-space' => TOURMASTER_URL . '/images/tour-style/modern-no-space.jpg',
										'grid' => TOURMASTER_URL . '/images/tour-style/grid.jpg',
										'grid-with-frame' => TOURMASTER_URL . '/images/tour-style/grid-with-frame.jpg',
										'grid-no-space' => TOURMASTER_URL . '/images/tour-style/grid-no-space.jpg',
									),
									'default' => 'grid',
									'wrapper-class' => 'tourmaster-fullsize',
									'condition' => array( 'enable-single-related-tour' => 'enable' )
								),
								'single-related-tour-grid-style' => array(
									'title' => esc_html__('Grid Style', 'tourmaster'),
									'type' => 'combobox',
									'options' => array(
										'style-1' => esc_html__('Style 1', 'tourmaster'),
										'style-2' => esc_html__('Style 2', 'tourmaster'),
										'style-3' => esc_html__('Style 3', 'tourmaster'),
										'style-4' => esc_html__('Style 4', 'tourmaster'),
										'style-5' => esc_html__('Style 5', 'tourmaster'),
										'style-6' => esc_html__('Style 6', 'tourmaster'),
									),
									'condition' => array('single-related-tour-style' => array('grid', 'grid-with-frame', 'grid-no-space'))
								),
								'single-related-tour-column-size' => array(
									'title' => esc_html__('Column Size', 'tourmaster'),
									'type' => 'combobox',
									'options' => array( 60=>1, 30=>2, 20=>3, 15=>4, 12=>5 ),
									'default' => 30,
									'condition' => array( 'enable-single-related-tour' => 'enable', 'single-related-tour-style' => array('modern', 'modern-no-space', 'grid', 'grid-with-frame', 'grid-no-space') )
								),
								'single-related-tour-num-fetch' => array(
									'title' => esc_html__('Num Fetch', 'tourmaster'),
									'type' => 'text',
									'data-input-type' => 'number',
									'default' => 2,
									'condition' => array( 'enable-single-related-tour' => 'enable' ), 
									'description' => esc_html__('The number of posts showing on the blog item', 'tourmaster')
								),
								'single-related-tour-thumbnail-size' => array(
									'title' => esc_html__('Thumbnail Size', 'tourmaster'),
									'type' => 'combobox',
									'options' => 'thumbnail-size',
									'default' => 'large',
									'condition' => array( 'enable-single-related-tour' => 'enable' )
								),
								'single-related-tour-price-position' => array(
									'title' => esc_html__('Price Display Position', 'tourmaster'),
									'type' => 'combobox', 
									'options' => array(
										'right-title' => esc_html__('Default', 'tourmaster'),
										'bottom-title' => esc_html__('Bottom Of The Title', 'tourmaster'),
										'bottom-bar' => esc_html__('As Bottom Bar', 'tourmaster'),
									),
									'condition' => array( 'enable-single-related-tour' => 'enable', 'single-related-tour-style' => array('grid', 'grid-with-frame', 'grid-no-space') ),
									'default' => 'right-title'
								),
								'single-related-tour-info' => array(
									'title' => esc_html__('Tour Info', 'tourmaster'),
									'type' => 'multi-combobox',
									'options' => array(
										'duration-text' => esc_html__('Duration', 'tourmaster'),
										'availability' => esc_html__('Availability', 'tourmaster'),
										'departure-location' => esc_html__('Departure Location', 'tourmaster'),
										'return-location' => esc_html__('Return Location', 'tourmaster'),
										'minimum-age' => esc_html__('Minimum Age', 'tourmaster'),
										'maximum-people' => esc_html__('Maximum People', 'tourmaster'),
										'custom-excerpt' => esc_html__('Custom Excerpt ( In Tour Option )', 'tourmaster'),
									),
									'condition' => array( 'enable-single-related-tour' => 'enable', 'single-related-tour-style' => array('modern', 'modern-no-space', 'grid', 'grid-with-frame', 'grid-no-space') ),
									'description' => esc_html__('You can use Ctrl/Command button to select multiple items or remove the selected item. Leave this field blank to select all items in the list.', 'tourmaster'),
								),
								'single-related-tour-excerpt' => array(
									'title' => esc_html__('Excerpt Type', 'tourmaster'),
									'type' => 'combobox',
									'options' => array(
										'specify-number' => esc_html__('Specify Number', 'tourmaster'),
										'show-all' => esc_html__('Show All ( use <!--more--> tag to cut the content )', 'tourmaster'),
										'none' => esc_html__('Disable Exceprt', 'tourmaster'),
									),
									'condition' => array( 'enable-single-related-tour' => 'enable', 'single-related-tour-style' => array('full', 'full-with-frame', 'medium', 'medium-with-frame', 'grid', 'grid-with-frame', 'grid-no-space') ),
									'default' => 'none',
								),
								'single-related-tour-excerpt-number' => array(
									'title' => esc_html__('Excerpt Number', 'tourmaster'),
									'type' => 'text',
									'default' => 20,
									'condition' => array( 'enable-single-related-tour' => 'enable', 'single-related-tour-style' => array('grid', 'grid-with-frame', 'grid-no-space'), 'single-related-tour-excerpt' => 'specify-number' )
								),
								'single-related-tour-rating' => array(
									'title' => esc_html__('Tour Rating', 'tourmaster'),
									'type' => 'checkbox',
									'condition' => array( 'enable-single-related-tour' => 'enable', 'single-related-tour-style' => array('grid', 'grid-with-frame', 'grid-no-space') ),
									'default' => 'enable'
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
									'default' => "<strong>Dear Admin,</strong>\n\nA new booking form {customer-name} has been made.\n\n{tour-name}\n{order-number}\n{travel-date}\n\nCustomer\'s Note: {customer-note}\n{spaces}\n\nYou can view <a href=\"{admin-transaction-link}\">the transaction here</a>",
									'condition' => array('enable-admin-booking-made-mail' => 'enable')
								),
								'enable-admin-booking-made-approval-mail' => array(
									'title' => esc_html__('Enable Admin Booking Made ( Need Approval ) E-Mail', 'tourmaster'),
									'type' => 'checkbox'
								),
								'admin-booking-made-approval-mail-title' => array(
									'title' => esc_html__('Admin Booking Made ( Need Approval ) E-Mail Title', 'tourmaster'),
									'type' => 'text',
									'default' => esc_html__('A new booking has been made. Please approve the booking so customer can pay.', 'tourmaster'),
									'condition' => array('enable-admin-booking-made-approval-mail' => 'enable')
								),
								'admin-booking-made-approval-mail' => array(
									'title' => esc_html__('Admin Booking Made ( Need Approval ) E-Mail', 'tourmaster'),
									'type' => 'textarea',
									'default' => "<strong>Dear Admin,</strong>\nA new booking form {customer-name} has been made.\n\n{tour-name}\n{order-number}\n{travel-date}\n\nCustomer's Note: {customer-note}\n{spaces}\n\nYou can view <a href=\"{admin-transaction-link}\">the transaction here</a>\n\nPlease note that this customer can't process the payment untill you approvde thier booking.",
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
									'default' => "<strong>Dear Admin,</strong> \nA new booking form {customer-name} has been made.\n\n{tour-name}\n{order-number}\n{travel-date}\n{spaces}\n\nYou can view <a href=\"{admin-transaction-link}\">the transaction here</a>\n{spaces}\n\nCustomer’s email : {customer-email}\nPlease contact to your customer back for further details."
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
									'default' => "<strong>Dear Admin,</strong>\nA new payment receipt has been submitted\n\n{tour-name}\n{order-number}\n{travel-date}\n{spaces}\n\nYou can view <a href=\"{admin-transaction-link}\">the transaction here</a>",
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
									'default' => "<strong>Dear Admin,</strong>\nA new booking has been made and sucessfully paid.\n\n{payment-method}\n{payment-date}\n{transaction-id}\n{spaces}\n\n{tour-name}\n{order-number}\n{travel-date}\n\nCustomer's Note: {customer-note}\n{spaces}\n\nYou can view <a href=\"{admin-transaction-link}\">the transaction here</a>",
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
									'default' => esc_html__('New deposit has been successfully paid', 'tourmaster'),
									'condition' => array('enable-admin-deposit-payment-made-mail' => 'enable')
								),
								'admin-deposit-payment-made-mail' => array(
									'title' => esc_html__('Online Deposit Payment Made E-Mail', 'tourmaster'),
									'type' => 'textarea',
									'default' => "<strong>Dear Admin,</strong>\nNew deposit has been successfully paid.\n\n{payment-method}\n{submission-date}\n{transaction-id}\n{submission-amount}\n{spaces}\n\n{tour-name}\n{order-number}\n{travel-date}\n\nCustomer's Note: {customer-note}\n{spaces}\n\nYou can view <a href=\"{admin-transaction-link}\">the transaction here</a>",
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
									'defualt' => 'You have made a new booking',
									'condition' => array( 'enable-booking-made-mail' => 'enable' )
								),
								'booking-made-mail' => array(
									'title' => esc_html__('Booking Made E-Mail', 'tourmaster'),
									'type' => 'textarea',
									'wrapper-class' => 'tourmaster-fullsize',
									'default' => "<strong>Dear {customer-name}</strong>,\nYou have made a booking on\n\n{tour-name}\n{order-number}\n{travel-date}\n{total-price}\nCustomer's Note: {customer-note}\n\n<a href=\"{payment-link}\" >Make a payment</a>\n<a href=\"{invoice-link}\" >View Invoice</a>\n{divider}\nIf you wish to do the bank transfer. Please use the information below.\n\n<strong>Bank name: Center London Bank</strong>\n<strong>Account Number: 4455-4445-333</strong>\n<strong>Swift Code: XXCCVV</strong>\n\nAfter transferring, please submit payment receipt from your dashboard. We'll get back to you when the submission verified.",
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
									'default' => esc_html__('You have made a new booking. Please wait for approval before processing payment', 'tourmaster'),
									'condition' => array( 'enable-booking-made-approval-mail' => 'enable' )
								),
								'booking-made-approval-mail' => array(
									'title' => esc_html__('Booking Made ( Need Approval ) E-Mail', 'tourmaster'),
									'type' => 'textarea',
									'default' => "<strong>Dear {customer-name}</strong>,\nYou have made a booking on\n\n{tour-name}\n{order-number}\n{travel-date}\n{total-price}\nCustomer's Note: {customer-note}\n\nAt this point, please do nothing yet. \nAfter admin approve your booking, you will get email notification and then you can process payment later.",
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
									'default' => esc_html__('Your booking has been approved to process the payment', 'tourmaster'),
									'condition' => array( 'enable-booking-approve-mail' => 'enable' )
								),
								'booking-approve-mail' => array(
									'title' => esc_html__('Booking Approve E-Mail', 'tourmaster'),
									'type' => 'textarea',
									'default' => "<strong>Dear {customer-name}</strong>,\nYou have made a booking on\n\n{tour-name}\n{order-number}\n{travel-date}\n{total-price}\nCustomer's Note: {customer-note}\n\nAdmin has now approved your booking so you can process the payment. \nPlease note that this is not the final approve until you finish the payment.",
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
									'default' => "<strong>Dear {customer-name}</strong>,\nYou have made a booking on\n\n{tour-name}\n{order-number}\n{travel-date}\n{total-price}\n{divider}\nOur team will contact you back via the email you provided,\n{customer-email}"
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
									'default' => "<strong>Dear {customer-name}</strong>,\nCongratulations! Your payment has been sucessfully processed.\n\n{tour-name}\n{order-number}\n{travel-date}\n{total-price}\nCustomer's Note: {customer-note}\n\n{payment-method}\n{payment-date}\n{transaction-id}\n{spaces}\n\nYou can view <a href=\"{invoice-link}\">the receipt here</a>",
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
									'default' => esc_html__('Your deposit has been successfully processed', 'tourmaster'),
									'condition' => array( 'enable-deposit-payment-made-mail' => 'enable' )
								),
								'deposit-payment-made-mail' => array(
									'title' => esc_html__('Deposit Payment E-Made Mail', 'tourmaster'),
									'type' => 'textarea',
									'default' => "<strong>Dear {customer-name}</strong>,\nCongratulations! Your deposit has been sucessfully processed.\n\n{tour-name}\n{order-number}\n{travel-date}\n{submission-amount}\n\nCustomer's Note: {customer-note}\n\n{payment-method}\n{submission-date}\n{transaction-id}\n{spaces}\n\nYou can view <a href=\"{invoice-link}\">the receipt here</a>",
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
									'default' => "<strong>Dear {customer-name}</strong>,\nWe are here to inform that your booking has been cancelled.\n\n{tour-name}\n{order-number}\n{travel-date}",
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
									'default' => "<strong>Dear {customer-name}</strong>,\nWe are sorry to inform that your booking has been rejected. Your booking was rejected because of your payment was not successfully processed or your booking might be in the pending status for too long.\n\n{tour-name}\n{order-number}\n{travel-date}",
									'condition' => array( 'enable-booking-reject-mail' => 'enable' )
								),
								'enable-receipt-submission-mail' => array(
									'title' => esc_html__('Enable Receipt Submission E-Mail', 'tourmaster'),
									'type' => 'checkbox'
								),
								'receipt-submission-mail-title' => array(
									'title' => esc_html__('Receipt Submission E-Mail Title', 'tourmaster'),
									'type' => 'text',
									'default' => esc_html__('Thank you for payment submission.', 'tourmaster'),
									'condition' => array( 'enable-receipt-submission-mail' => 'enable' )
								),
								'receipt-submission-mail' => array(
									'title' => esc_html__('Receipt Submission E-Mail', 'tourmaster'),
									'type' => 'textarea',
									'default' => "<strong>Dear {customer-name}</strong>,\nThank you for payment submission. After reveiwing, we will get back to you soon. \n\n{tour-name}\n{order-number}\n\n{submission-date}\n{payment-method}\n{submission-amount}\n{transaction-id}\n\n<a href=\"{payment-link}\" >Make a payment</a>\n<a href=\"{invoice-link}\" >View Invoice</a>\n{divider}\nIf you wish to do the bank transfer. Please use the information below.\n\n<strong>Bank name: Center London Bank</strong>\n<strong>Account Number: 4455-4445-333</strong>\n<strong>Swift Code: XXCCVV</strong>\n\nAfter transferring, please submit payment receipt from your dashboard. We'll get back to you when the submission verified.",
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
									'default' => esc_html__('Your payment submission has been approved.', 'tourmaster'),
									'condition' => array( 'enable-receipt-submission-mail' => 'enable' )
								),
								'receipt-approve-mail' => array(
									'title' => esc_html__('Receipt Approve E-Mail', 'tourmaster'),
									'type' => 'textarea',
									'default' => "<strong>Dear {customer-name}</strong>,\nYour payment submission has been approved. You can make another deposit or the final payment from your dashboard.\n\n{tour-name}\n{order-number}\n\n{submission-date}\n{payment-method}\n{submission-amount}\n{transaction-id}\n\n<a href=\"{payment-link}\" >Make a payment</a>\n<a href=\"{invoice-link}\" >View Invoice</a>\n{divider}\nIf you wish to do the bank transfer. Please use the information below.\n\n<strong>Bank name: Center London Bank</strong>\n<strong>Account Number: 4455-4445-333</strong>\n<strong>Swift Code: XXCCVV</strong>\n\nAfter transferring, please submit payment receipt from your dashboard. We'll get back to you when the submission verified.",
									'wrapper-class' => 'tourmaster-fullsize',
									'condition' => array( 'enable-receipt-submission-mail' => 'enable' )
								),
								'enable-receipt-reject-mail' => array(
									'title' => esc_html__('Enable Receipt Reject E-Mail', 'tourmaster'),
									'type' => 'checkbox'
								),
								'receipt-reject-mail-title' => array(
									'title' => esc_html__('Receipt Reject E-Mail Title', 'tourmaster'),
									'type' => 'text',
									'default' => esc_html__('Your payment submission has been rejected.', 'tourmaster'),
									'condition' => array( 'enable-receipt-submission-mail' => 'enable' )
								),
								'receipt-reject-mail' => array(
									'title' => esc_html__('Receipt Reject E-Mail', 'tourmaster'),
									'type' => 'textarea',
									'default' => "<strong>Dear {customer-name}</strong>,\nUnfortunately, your payment submission is not valid. Please review your payment receipt and submit again. \n\n{tour-name}\n{order-number}\n\n{submission-date}\n{payment-method}\n{submission-amount}\n{transaction-id}\n\n<a href=\"{payment-link}\" >Make a payment</a>\n<a href=\"{invoice-link}\" >View Invoice</a>\n{divider}\nIf you wish to do the bank transfer. Please use the information below.\n\n<strong>Bank name: Center London Bank</strong>\n<strong>Account Number: 4455-4445-333</strong>\n<strong>Swift Code: XXCCVV</strong>\n\nAfter transferring, please submit payment receipt from your dashboard. We'll get back to you when the submission verified.",
									'wrapper-class' => 'tourmaster-fullsize',
									'condition' => array( 'enable-receipt-submission-mail' => 'enable' )
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
				$tourmaster_option->add_element(array(
					'title' => esc_html__('Payment', 'tourmaster'),
					'slug' => 'tourmaster_payment',
					'icon' => TOURMASTER_URL . '/images/plugin-options/general.png',
					'options' => apply_filters('goodlayers_plugin_payment_option', array(
						'payment-settings' => array(
							'title' => esc_html__('Payment Settings', 'tourmaster'),
							'options' => array(
								'enable-payment' => array(
									'title' => esc_html__('Enable Payment', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable'
								),
								'payment-admin-approval' => array(
									'title' => esc_html__('Needs Admin Approval Before Payment', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'disable',
									'condition' => array( 'enable-payment' => 'enable' ),
									'description' => esc_html__('Booking payment method needs to be enable to use this feature.', 'tourmaster')
								),
								'enable-woocommerce-payment' => array(
									'title' => esc_html__('Enable Woocommerce Payment', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'disable',
									'description' => esc_html__('All others option will be omitted after enabling this option.', 'tourmaster'),
									'condition' => array( 'enable-payment' => 'enable' ),
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
									'condition' => array( 'enable-payment' => 'enable', 'enable-woocommerce-payment' => 'disable' ),
									'description' => esc_html__('You can use Ctrl/Command button to select multiple items or remove the selected item.', 'tourmaster'),
								),
								'enable-full-payment' => array(
									'title' => esc_html__('Enable Full Payment', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable',
									'condition' => array( 'enable-payment' => 'enable', 'enable-woocommerce-payment' => 'disable' )
								),
								'enable-deposit-payment' => array(
									'title' => esc_html__('Enable Deposit Payment', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'disable',
									'condition' => array( 'enable-payment' => 'enable', 'enable-woocommerce-payment' => 'disable' )
								),
								'deposit-payment-amount' => array(
									'title' => esc_html__('Deposit 1 Payment Amount (%)', 'tourmaster'),
									'type' => 'text',
									'default' => 0,
									'condition' => array( 'enable-payment' => 'enable', 'enable-deposit-payment' => 'enable', 'enable-woocommerce-payment' => 'disable' ),
									'description' => esc_html__('Only fill number here', 'tourmaster')
								),
								'deposit2-payment-amount' => array(
									'title' => esc_html__('Deposit 2 Payment Amount (%)', 'tourmaster'),
									'type' => 'text',
									'default' => 0,
									'condition' => array( 'enable-payment' => 'enable', 'enable-deposit-payment' => 'enable', 'enable-woocommerce-payment' => 'disable' ),
									'description' => esc_html__('Only fill number here', 'tourmaster')
								),
								'deposit3-payment-amount' => array(
									'title' => esc_html__('Deposit 3 Payment Amount (%)', 'tourmaster'),
									'type' => 'text',
									'default' => 0,
									'condition' => array( 'enable-payment' => 'enable', 'enable-deposit-payment' => 'enable', 'enable-woocommerce-payment' => 'disable' ),
									'description' => esc_html__('Only fill number here', 'tourmaster')
								),
								'deposit4-payment-amount' => array(
									'title' => esc_html__('Deposit 4 Payment Amount (%)', 'tourmaster'),
									'type' => 'text',
									'default' => 0,
									'condition' => array( 'enable-payment' => 'enable', 'enable-deposit-payment' => 'enable', 'enable-woocommerce-payment' => 'disable' ),
									'description' => esc_html__('Only fill number here', 'tourmaster')
								),
								'deposit5-payment-amount' => array(
									'title' => esc_html__('Deposit 5 Payment Amount (%)', 'tourmaster'),
									'type' => 'text',
									'default' => 0,
									'condition' => array( 'enable-payment' => 'enable', 'enable-deposit-payment' => 'enable', 'enable-woocommerce-payment' => 'disable' ),
									'description' => esc_html__('Only fill number here', 'tourmaster')
								),
								'display-deposit-payment-day' => array(
									'title' => esc_html__('Disable Deposit Payment # Days Before The Start Date', 'tourmaster'),
									'type' => 'text',
									'default' => 0,
									'condition' => array( 'enable-payment' => 'enable', 'enable-deposit-payment' => 'enable', 'enable-woocommerce-payment' => 'disable' ),
									'description' => esc_html__('Only fill number here', 'tourmaster')
								),
								'credit-card-payment-gateway' => array(
									'title' => esc_html__('Credit Card Payment Gateway', 'tourmaster'),
									'type' => 'combobox',
									'options' => apply_filters('goodlayers_credit_card_payment_gateway_options', array('' => esc_html__('None', 'tourmaster'))),
									'condition' => array( 'enable-payment' => 'enable', 'enable-woocommerce-payment' => 'disable' )
								),
								'credit-card-service-fee' => array(
									'title' => esc_html__('Credit Card Service Fee (%)', 'tourmaster'),
									'type' => 'text',
									'default' => '',
									'description' => esc_html__('Fill only number here', 'tourmaster'),
									'condition' => array( 'enable-payment' => 'enable', 'enable-woocommerce-payment' => 'disable' )
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
									'condition' => array( 'enable-payment' => 'enable', 'enable-woocommerce-payment' => 'disable' ),
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
					))
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
								'tourmaster-single-price-head-background' => array(
									'title' => esc_html__('Single Price Head Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector-extra' => true,
									'selector' => 
										'body .tourmaster-tour-booking-submit input[type="submit"], ' .
										'body .tourmaster-tour-booking-submit input[type="submit"]:hover,' .
										'body .tourmaster-tour-booking-submit .tourmaster-button, ' .
										'body .tourmaster-enquiry-form .tourmaster-button,' .
										'.tourmaster-header-price .tourmaster-header-price-overlay{ background: #gdlr#; ' .
										' background: -webkit-linear-gradient(left, #gdlr# , <tourmaster-single-price-head-background-right>); ' .
										' background: -o-linear-gradient(right, #gdlr#, <tourmaster-single-price-head-background-right>); ' .
										' background: -moz-linear-gradient(right, #gdlr#, <tourmaster-single-price-head-background-right>); ' .
										' background: linear-gradient(to right, #gdlr# , <tourmaster-single-price-head-background-right>); }',
									'default' => '#4674e7',
								),
								'tourmaster-single-price-head-background-right' => array(
									'title' => esc_html__('Single Price Head Background Right Gradient', 'tourmaster'),
									'type' => 'colorpicker',
									'default' => '#4692e7',
								),
								'tourmaster-single-price-head-featured-background' => array(
									'title' => esc_html__('Single (Style 1) Price Head Featured Background', 'tourmaster'),
									'type' => 'colorpicker',
									'data-type' => 'rgba', 
									'selector' => '.tourmaster-tour-style-1 .tourmaster-header-price .tourmaster-header-price-ribbon, ' . 
										'.tourmaster-tour-style-1 .tourmaster-header-price .tourmaster-header-enquiry-ribbon{ background: #gdlr#; background: rgba(#gdlra#, 0.9); }' . 
										'.tourmaster-tour-style-1 .tourmaster-tour-booking-bar-wrap.tourmaster-top .tourmaster-header-price .tourmaster-header-price-ribbon,' . 
										'.tourmaster-tour-style-1 .tourmaster-tour-booking-bar-wrap.tourmaster-bottom .tourmaster-header-price .tourmaster-header-price-ribbon,' . 
										'.tourmaster-tour-style-1 .tourmaster-tour-booking-bar-wrap.tourmaster-lock .tourmaster-header-price .tourmaster-header-price-ribbon,' . 
										'.tourmaster-tour-style-1 .tourmaster-tour-booking-bar-wrap.tourmaster-fixed .tourmaster-header-price .tourmaster-header-price-ribbon{ background: #gdlr#; }',
									'default' => '#2c487a',
								),
								'tourmaster-single-price-head-text' => array(
									'title' => esc_html__('Single Price Head Text Color', 'tourmaster'),
									'type' => 'colorpicker',
									'data-type' => 'rgba', 
									'selector' => '.tourmaster-header-price .tourmaster-header-price-ribbon, ' . 
										'.tourmaster-header-price .tourmaster-tour-price-wrap, ' .
										'.tourmaster-header-price .tourmaster-header-enquiry{ color: #gdlr#; }',
									'default' => '#ffffff',
								),
								'tourmaster-single-price-head-discount-text' => array(
									'title' => esc_html__('Single Price Head Discount Text', 'tourmaster'),
									'type' => 'colorpicker',
									'data-type' => 'rgba', 
									'selector' => '.tourmaster-header-price .tourmaster-tour-price-wrap.tourmaster-discount .tourmaster-tour-price, ' . 
										'.tourmaster-header-price .tourmaster-tour-price-info{ color: #gdlr#; }',
									'default' => '#b9daff',
								),
								'tourmaster-remove-color' => array(
									'title' => esc_html__('Remove/Error Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-wish-list-remove-item{ color: #gdlr#; }' . 
										'.tourmaster-notification-box.tourmaster-failure, .tourmaster-user-update-notification.tourmaster-failure{ background: #gdlr#; }' . 
										'.tourmaster-tour-booking-submit-error, .tourmaster-tour-booking-error-max-people{ background: #gdlr#; }' . 
										'.tourmaster-tour-booking-bar-coupon-wrap .tourmaster-tour-booking-coupon-message.tourmaster-failed{ background-color: #gdlr#; }',
									'default' => '#ba4a4a',
								),
								'tourmaster-rating-color' => array(
									'title' => esc_html__('Rating Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-rating i, .tourmaster-review-form .tourmaster-review-form-rating, ' .
										'.tourmaster-single-review-content .tourmaster-single-review-detail-rating i, ' . 
										'.tourmaster-tour-review-item .tourmaster-tour-review-item-rating i, ' . 
										'.tourmaster-tour-search-field-rating .tourmaster-rating-select{ color: #gdlr#; }',
									'default' => '#ffa127',
								),
							)
						), // tourmaster-general
						
						'tourmaster-booking-bar' => array(
							'title' => esc_html__('Tourmaster Booking Bar', 'tourmaster'),
							'options' => array(
								'tourmaster-booking-bar-background' => array(
									'title' => esc_html__('Booking Bar Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-style-1 .tourmaster-tour-booking-bar-inner, ' . 
										'.tourmaster-tour-style-2 .tourmaster-tour-booking-bar-outer, ' . 
										'.tourmaster-form-field .tourmaster-combobox-list-wrap ul, ' . 
										'.tourmaster-template-payment .tourmaster-tour-booking-bar-wrap{ background-color: #gdlr#; }',
									'default' => '#ffffff',
								),
								'tourmaster-booking-bar-text' => array(
									'title' => esc_html__('Booking Bar Text', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-booking-date .tourmaster-tour-booking-date-display, ' .
										'.tourmaster-tour-booking-bar-wrap .tourmaster-view-count{ color: #gdlr#; }' . 
										'.tourmaster-tour-booking-bar-wrap .tourmaster-save-wish-list{ color: #gdlr#; }' . 
										'.tourmaster-tour-booking-people-container .tourmaster-tour-booking-room-text{ color: #gdlr#; }',
									'default' => '#333333',
								),
								'tourmaster-booking-bar-wishlist-background' => array(
									'title' => esc_html__('Single Booking Bar Wishlist Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-booking-bar-wrap .tourmaster-save-wish-list{ background-color: #gdlr#; }',
									'default' => '#fbfbfb',
								),
								'tourmaster-booking-bar-wishlist-border' => array(
									'title' => esc_html__('Single Booking Bar Wishlist Border', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-booking-bar-wrap .tourmaster-booking-bottom, ' .
										'.tourmaster-tour-booking-bar-wrap .tourmaster-save-wish-list{ border-color: #gdlr#; }',
									'default' => '#ebebeb',
								),
								'tourmaster-booking-bar-summary-title' => array(
									'title' => esc_html__('Booking Bar Summary Title', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-booking-bar-wrap .tourmaster-tour-booking-bar-summary-title{ color: #gdlr#; }',
									'default' => '#000000',
								),
								'tourmaster-booking-bar-summary-text' => array(
									'title' => esc_html__('Booking Bar Summary Text', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-booking-bar-summary-info, .tourmaster-tour-booking-bar-summary-people-amount, ' .
										'.tourmaster-tour-booking-bar-summary-room-text{ color: #gdlr#; }',
									'default' => '#414141',
								),
								'tourmaster-price-breakdown-color' => array(
									'title' => esc_html__('Booking Bar Price Breakdown Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-price-breakdown, .tourmaster-tour-booking-bar-total-price-wrap{ color: #gdlr#; }',
									'default' => '#515151',
								),
								'tourmaster-price-breakdown-total-color' => array(
									'title' => esc_html__('Booking Bar Price Breakdown Total Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-booking-bar-total-price, .tourmaster-tour-booking-bar-deposit-text{ color: #gdlr#; }',
									'default' => '#242424',
								),
								'tourmaster-price-breakdown-total-color-deposit' => array(
									'title' => esc_html__('Booking Bar Price Breakdown Total Color ( Deposit )', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-payment-receipt-deposit-option label input + span, ' .
										'.tourmaster-tour-booking-bar-deposit-option label input + span, ' .
										'.tourmaster-tour-booking-bar-total-price-wrap.tourmaster-deposit, ' . 
										'.tourmaster-tour-booking-bar-total-price-wrap.tourmaster-deposit .tourmaster-tour-booking-bar-total-price{ color: #gdlr#; }',
									'default' => '#a1a1a1',
								),
							)
						),
						'tourmaster-payment' => array(
							'title' => esc_html__('Tourmaster Payment', 'tourmaster'),
							'options' => array(
								'tourmaster-payment-title-Color' => array(
									'title' => esc_html__('Payment Title Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-payment-head .tourmaster-payment-title{ color: #gdlr#; }',
									'default' => '#ffffff',
								),
								'tourmaster-payment-title-overlay' => array(
									'title' => esc_html__('Payment Title Overlay Color', 'tourmaster'),
									'type' => 'colorpicker',
									'data-type' => 'rgba',
									'selector-extra' => true,
									'selector' => '.tourmaster-payment-head .tourmaster-payment-head-overlay-opacity{ background-color: rgba(#gdlra#, <tourmaster-payment-title-overlay-opacity>t); }',
									'default' => '#000000',
								),
								'tourmaster-payment-title-overlay-opacity' => array(
									'title' => esc_html__('Payment Title Overlay Opacity', 'tourmaster'),
									'type' => 'text',
									'default' => '0.5',
								),
								'tourmaster-payment-step-title-Color' => array(
									'title' => esc_html__('Payment Step Title Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-payment-step-item .tourmaster-payment-step-item-title{ color: #gdlr#; }' . 
										'.tourmaster-page-wrapper.tourmaster-payment-style-2 .tourmaster-payment-step-item .tourmaster-payment-step-item-title{ color: #gdlr#; }' . 
										'.tourmaster-page-wrapper.tourmaster-payment-style-2 .tourmaster-payment-step-item .tourmaster-payment-step-item-title span{ border-color: #gdlr#; }',
									'default' => '#ffffff',
								),
								'tourmaster-payment-step-title-active-color' => array(
									'title' => esc_html__('Payment Step Title (Style 2) Active Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-page-wrapper.tourmaster-payment-style-2 .tourmaster-payment-step-item.tourmaster-checked .tourmaster-payment-step-item-title, ' .
										'.tourmaster-page-wrapper.tourmaster-payment-style-2 .tourmaster-payment-step-item.tourmaster-enable .tourmaster-payment-step-item-title, ' .
										'.tourmaster-page-wrapper.tourmaster-payment-style-2 .tourmaster-payment-step-item.tourmaster-current .tourmaster-payment-step-item-title{ color: #gdlr#; }' .
										'.tourmaster-page-wrapper.tourmaster-payment-style-2 .tourmaster-payment-step-item.tourmaster-checked .tourmaster-payment-step-item-title span, ' .
										'.tourmaster-page-wrapper.tourmaster-payment-style-2 .tourmaster-payment-step-item.tourmaster-enable .tourmaster-payment-step-item-title span, ' .
										'.tourmaster-page-wrapper.tourmaster-payment-style-2 .tourmaster-payment-step-item.tourmaster-current .tourmaster-payment-step-item-title span{ border-color: #gdlr#; }',
									'default' => '#3d3d3d',
								),
								'tourmaster-payment-complete-background' => array(
									'title' => esc_html__('Payment Complete Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-payment-complete-wrap,.tourmaster-payment-method-wrap{ background-color: #gdlr#; }',
									'default' => '#f5f5f5',
								),
								'tourmaster-payment-complete-title' => array(
									'title' => esc_html__('Payment Complete Title Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-payment-complete-wrap .tourmaster-payment-complete-head, ' .
										'.tourmaster-payment-method-wrap .tourmaster-payment-method-title, ' .
										'.tourmaster-payment-method-wrap .tourmaster-payment-method-or{ color: #gdlr#; }',
									'default' => '#262626',
								),
								'tourmaster-payment-complete-border' => array(
									'title' => esc_html__('Payment Complete Border Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-payment-complete-wrap .tourmaster-payment-complete-bottom-text, ' .
										'.tourmaster-payment-complete-wrap .tourmaster-payment-complete-head, ' .
										'.tourmaster-payment-method-wrap .tourmaster-payment-method-title{ border-color: #gdlr#; }',
									'default' => '#e3e3e3',
								),
								'payment-service-form-background' => array(
									'title' => esc_html__('Service Form Background ( Payment )', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-payment-service-form-wrap{ background-color: #gdlr#; }',
									'default' => '#f5f5f5',
								),
								'payment-service-form-title' => array(
									'title' => esc_html__('Service Form Title ( Payment )', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-payment-service-form-wrap .tourmaster-payment-service-form-title{ color: #gdlr#; }',
									'default' => '#1a1a1a',
								),
								'payment-service-form-title-border' => array(
									'title' => esc_html__('Service Form Title Border ( Payment )', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-payment-service-form-wrap .tourmaster-payment-service-form-title{ border-color: #gdlr#; }',
									'default' => '#e3e3e3',
								),
								'payment-service-form-label' => array(
									'title' => esc_html__('Service Form Label ( Payment )', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-payment-service-form-wrap .tourmaster-payment-service-form-item-title{ color: #gdlr#; }',
									'default' => '#6a6a6a',
								),
								'pagement2-button1-color' => array(
									'title' => esc_html__('Payment (Style 2) Button 1 Color', 'tourmaster'),
									'type' => 'colorpicker',
									'default' => '#0754b0',
									'selector' => '.tourmaster-page-wrapper.tourmaster-payment-style-2 .tourmaster-tour-booking-side-payment-wrap .tourmaster-button.tourmaster-blue{ background: #gdlr#; }'
								),
								'pagement2-button2-color' => array(
									'title' => esc_html__('Payment (Style 2) Button 2 Color', 'tourmaster'),
									'type' => 'colorpicker',
									'default' => '#101010',
									'selector' => '.tourmaster-page-wrapper.tourmaster-payment-style-2 .tourmaster-tour-booking-side-payment-wrap .tourmaster-button{ background: #gdlr#; }'
								),
							)
						),
						'tourmaster-single' => array(
							'title' => esc_html__('Tourmaster Template', 'tourmaster'),
							'options' => array(
								'search-page-background' => array(
									'title' => esc_html__('Search/Archive Page Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-template-search .tourmaster-template-wrapper, .tourmaster-template-archive .tourmaster-template-wrapper{ background-color: #gdlr#; }',
									'default' => '#f3f3f3',
								),
								'search-not-found-background' => array(
									'title' => esc_html__('Search Not Found Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-single-search-not-found-wrap .tourmaster-single-search-not-found-inner{ background-color: #gdlr#; }',
									'default' => '#f6f6f6',
								),
								'search-not-found-title' => array(
									'title' => esc_html__('Search Not Found Title', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-single-search-not-found-wrap .tourmaster-single-search-not-found-title{ color: #gdlr#; }',
									'default' => '#cccccc',
								),
								'search-not-found-caption' => array(
									'title' => esc_html__('Search Not Found Caption', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-single-search-not-found-wrap .tourmaster-single-search-not-found-caption{ color: #gdlr#; }',
									'default' => '#a8a8a8',
								),
								'single-tour-top-gradient' => array(
									'title' => esc_html__('Single Tour Header Gradient', 'tourmaster'),
									'type' => 'colorpicker',
									'data-type' => 'rgba',
									'selector-extra' => true,
									'selector' => '.tourmaster-single-header-top-overlay, .tourmaster-payment-head .tourmaster-payment-head-top-overlay{ ' .
										'background: -webkit-linear-gradient(to top, rgba(#gdlra#, 0), rgba(#gdlra#, <single-tour-top-gradient-max-opacity>t)); ' . 
										'background: -o-linear-gradient(to top, rgba(#gdlra#, 0), rgba(#gdlra#, <single-tour-top-gradient-max-opacity>t)); ' . 
										'background: -moz-linear-gradient(to top, rgba(#gdlra#, 0), rgba(#gdlra#, <single-tour-top-gradient-max-opacity>t)); ' . 
										'background: linear-gradient(to top, rgba(#gdlra#, 0), rgba(#gdlra#, <single-tour-top-gradient-max-opacity>t)); }' .
										'.tourmaster-single-header-overlay, .tourmaster-payment-head .tourmaster-payment-head-overlay{ ' .
										'background: -webkit-linear-gradient(to bottom, rgba(#gdlra#, 0), rgba(#gdlra#, <single-tour-bottom-gradient-max-opacity>t)); ' . 
										'background: -o-linear-gradient(to bottom, rgba(#gdlra#, 0), rgba(#gdlra#, <single-tour-bottom-gradient-max-opacity>t)); ' . 
										'background: -moz-linear-gradient(to bottom, rgba(#gdlra#, 0), rgba(#gdlra#, <single-tour-bottom-gradient-max-opacity>t)); ' . 
										'background: linear-gradient(to bottom, rgba(#gdlra#, 0), rgba(#gdlra#, <single-tour-bottom-gradient-max-opacity>t)); }',
									'default' => '#000',
								),
								'single-tour-top-gradient-max-opacity' => array(
									'title' => esc_html__('Single Tour Header Top Gradient Max Opacity', 'kingster'),
									'type' => 'text',
									'default' => '1',
									'description' => esc_html__('Fill the number between 0.01 to 1', 'kingster')
								),
								'single-tour-bottom-gradient-max-opacity' => array(
									'title' => esc_html__('Single Tour Header Bottom Gradient Max Opacity', 'kingster'),
									'type' => 'text',
									'default' => '1',
									'description' => esc_html__('Fill the number between 0.01 to 1', 'kingster')
								),
								'single-tour-info-background' => array(
									'title' => esc_html__('Single Tour Info Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.single-tour .tourmaster-tour-info-outer{ background-color: #gdlr#; }',
									'default' => '#f5f5f5',
								),
								'single-tour-info-text' => array(
									'title' => esc_html__('Single Tour Info Text', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.single-tour .tourmaster-tour-info-wrap .tourmaster-tour-info{ color: #gdlr#; }',
									'default' => '#414141',
								),
								'tourmaster-review-title-color' => array(
									'title' => esc_html__('Single Review Title Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-single-review-head .tourmaster-tour-rating-text, ' .
										'.tourmaster-single-review-sort-by .tourmaster-head, ' .
										'.tourmaster-single-review-content .tourmaster-single-review-user-name, ' .
										'.tourmaster-single-review-content .tourmaster-single-review-user-type{ color: #gdlr#; }',
									'default' => '#272727',
								),
								'tourmaster-review-date-color' => array(
									'title' => esc_html__('Single Review Date Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-single-review-content .tourmaster-single-review-detail-date{ color: #gdlr#; }',
									'default' => '#a3a3a3',
								),
								'tourmaster-pagination-background' => array(
									'title' => esc_html__('Pagination Text Color ( Review )', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-review-content-pagination span{ color: #gdlr#; }',
									'default' => '#696969',
								),
								'tourmaster-pagination-text' => array(
									'title' => esc_html__('Pagination Background Color ( Review )', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-review-content-pagination span{ background-color: #gdlr#; }',
									'default' => '#f3f3f3',
								),
								'tourmaster-payment-step-icon-background' => array(
									'title' => esc_html__('Payment Step Icon Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-payment-step-item .tourmaster-payment-step-item-icon{ background: #gdlr#; }',
									'default' => '#484541',
								),
							)
						),
						'tourmaster-single2' => array(
							'title' => esc_html__('Tourmaster Template 2', 'tourmaster'),
							'options' => array(
								'booking-bar-tab-title-background' => array(
									'title' => esc_html__('Booking Bar Tab Title Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-style-1 .tourmaster-booking-tab-title{ background-color: #gdlr#; }',
									'default' => '#f5f5f5',
								),
								'booking-bar-tab-title-text' => array(
									'title' => esc_html__('Booking Bar Tab Title Text', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-booking-tab-title-item{ color: #gdlr#; }',
									'default' => '#929292',
								),
								'booking-bar-tab-active-title-text' => array(
									'title' => esc_html__('Booking Bar Tab Active Title Text', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-booking-tab-title-item.tourmaster-active{ color: #gdlr#; }',
									'default' => '#242424',
								),
								'booking-bar-tab-title-divider' => array(
									'title' => esc_html__('Booking Bar Tab Title Divider ( Tour Style 2 )', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-style-2 .tourmaster-booking-tab-title-item{ border-color: #gdlr#; }',
									'default' => '#d6d6d6',
								),
								'booking-bar-tab-title-divider' => array(
									'title' => esc_html__('Booking Bar Tab Title Divider Active ( Tour Style 2 )', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-style-2 .tourmaster-booking-tab-title-item.tourmaster-active, .tourmaster-tour-style-blank .tourmaster-booking-tab-title-item.tourmaster-active{ border-color: #gdlr#; }',
									'default' => '#234076',
								),
								'enquery-success-message-background' => array(
									'title' => esc_html__('Enquery Form Success Message Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-enquiry-form .tourmaster-enquiry-form-message.tourmaster-success{ background-color: #gdlr#; }',
									'default' => '#f1f8ff',
								),
								'enquery-success-message-border' => array(
									'title' => esc_html__('Enquery Form Success Message Border', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-enquiry-form .tourmaster-enquiry-form-message.tourmaster-success{ border-color: #gdlr#; }',
									'default' => '#e1ebfe',
								),
								'enquery-success-message-text' => array(
									'title' => esc_html__('Enquery Form Success Message Text', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-enquiry-form .tourmaster-enquiry-form-message.tourmaster-success{ color: #gdlr#; }',
									'default' => '#758ea8',
								),
								'enquery-failed-message-background' => array(
									'title' => esc_html__('Enquery Form Failed Message Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-enquiry-form .tourmaster-enquiry-form-message.tourmaster-failed{ background-color: #gdlr#; }',
									'default' => '#fff1f1',
								),
								'enquery-failed-message-border' => array(
									'title' => esc_html__('Enquery Form Failed Message Border', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-enquiry-form .tourmaster-enquiry-form-message.tourmaster-failed{ border-color: #gdlr#; }',
									'default' => '#fee1e1',
								),
								'enquery-failed-message-text' => array(
									'title' => esc_html__('Enquery Form Failed Message Text', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-urgency-message{ color: #gdlr#; }',
									'default' => '#ffffff',
								),
								'urgency-message-background' => array(
									'title' => esc_html__('Urgency Message Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-urgency-message{ background-color: #gdlr#; }',
									'default' => '#343434',
								),
								'urgency-message-text' => array(
									'title' => esc_html__('Urgency Message Text', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-enquiry-form .tourmaster-enquiry-form-message.tourmaster-failed{ color: #gdlr#; }',
									'default' => '#a87575',
								),
							)
						),
						'tourmaster-input-color' => array(
							'title' => esc_html__('Tourmaster Input', 'tourmaster'),
							'options' => array(

								'tourmaster-datepicker-background' => array(
									'title' => esc_html__('Datepicker Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-body .ui-datepicker{ background-color: #gdlr#; }',
									'default' => '#f5f5f5',
								),
								'tourmaster-datepicker-border' => array(
									'title' => esc_html__('Datepicker Border', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-body .ui-datepicker{ border-color: #gdlr#; }',
									'default' => '#ebebeb',
								),
								'tourmaster-datepicker-head' => array(
									'title' => esc_html__('Datepicker Head', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-body .ui-datepicker table tr th{ color: #gdlr#; }',
									'default' => '#808080',
								),
								'tourmaster-datepicker-enable-background' => array(
									'title' => esc_html__('Datepicker Enable Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-body .ui-datepicker table tr td a, .tourmaster-body .ui-datepicker-prev, .tourmaster-body .ui-datepicker-next{ background-color: #gdlr#; }',
									'default' => '#ffffff',
								),
								'tourmaster-datepicker-enable-text' => array(
									'title' => esc_html__('Datepicker Enable Text', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-body .ui-datepicker table tr td a, .tourmaster-body .ui-datepicker-prev, .tourmaster-body .ui-datepicker-next{ color: #gdlr#; }',
									'default' => '#5b5b5b',
								),
								'tourmaster-datepicker-disable-text' => array(
									'title' => esc_html__('Datepicker Disable Text', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-body .ui-datepicker table tr td a.ui-state-disable{ color: #gdlr#; }' .
										'.tourmaster-body .ui-datepicker-prev.ui-state-disabled, .tourmaster-body .ui-datepicker-prev.ui-state-disabled:hover, .tourmaster-body .ui-datepicker-next.ui-state-disabled, ' .
										'.tourmaster-body .ui-datepicker table tr td{ color: #gdlr#; }',
									'default' => '#c0c0c0',
								),
								
							)
						),
						'tourmaster-tour-item' => array(
							'title' => esc_html__('Tour Item', 'tourmaster'),
							'options' => array(
								'tour-item-title-color' => array(
									'title' => esc_html__('Tour Item Title Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-item .tourmaster-tour-title a{ color: #gdlr#; }',
									'default' => '#333333',
								),
								'tour-item-title-hover-color' => array(
									'title' => esc_html__('Tour Item Title Hover Color', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-item .tourmaster-tour-title a:hover{ color: #gdlr#; }',
									'default' => '#333333',
								),
								'tour-item-order-filterer-background' => array(
									'title' => esc_html__('Tour Item Order Filterer Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-order-filterer-wrap{ background-color: #gdlr#; }',
									'default' => '#ffffff',
								),
								'tour-item-order-filterer-combobox-background' => array(
									'title' => esc_html__('Tour Item Order Filterer Combobox Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-order-filterer-wrap .tourmaster-combobox-wrap select{ background-color: #gdlr#; }',
									'default' => '#ffffff',
								),
								'tour-item-order-filterer-icon' => array(
									'title' => esc_html__('Tour Item Order Filterer Icon', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-body .tourmaster-tour-order-filterer-style a{ color: #gdlr#; }',
									'default' => '#adadad',
								),
								'tour-item-frame-background' => array(
									'title' => esc_html__('Tour Item Frame Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-medium.tourmaster-tour-frame .tourmaster-tour-medium-inner, .tourmaster-tour-full.tourmaster-tour-frame .tourmaster-tour-content-wrap, ' .
										'.tourmaster-tour-grid.tourmaster-tour-frame .tourmaster-tour-content-wrap{ background: #gdlr#; }',
									'default' => '#ffffff',
								),
								'tour-price' => array(
									'title' => esc_html__('Tour Price', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-item .tourmaster-tour-discount-price, .tourmaster-tour-item .tourmaster-tour-price .tourmaster-tail{ color: #gdlr#; }',
									'default' => '#1b1b1b',
								),
								'tour-discount-price' => array(
									'title' => esc_html__('Tour Discount Price', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-item .tourmaster-tour-price-wrap.tourmaster-discount .tourmaster-tour-price, ' . 
										'.tourmaster-tour-item .tourmaster-tour-price-wrap.tourmaster-discount .tourmaster-tour-price .tourmaster-tail{ color: #gdlr#; }',
									'default' => '#bababa',
								),
								'tour-grid-bottom-price-background' => array(
									'title' => esc_html__('Tour Grid Bottom Price Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-grid .tourmaster-tour-price-bottom-wrap{ background-color: #gdlr#; }',
									'default' => '#e7e7e7',
								),
								'tour-grid-bottom-price-head' => array(
									'title' => esc_html__('Tour Grid Bottom Price Head', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-grid .tourmaster-tour-price-bottom-wrap .tourmaster-tour-price-head{ color: #gdlr#; }',
									'default' => '#5c5c5c',
								),
								'tour-grid-bottom-discount-price' => array(
									'title' => esc_html__('Tour Grid Bottom Discount Price', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-grid .tourmaster-tour-price-bottom-wrap.tourmaster-with-discount .tourmaster-tour-price{ color: #gdlr#; }',
									'default' => '#989898',
								),

								'tourmaster-tour-review-title' => array(
									'title' => esc_html__('Tour Review Item Title', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-review-item .tourmaster-tour-review-item-title a, .tourmaster-tour-review-item .tourmaster-tour-review-item-title a:hover{ color: #gdlr#; }',
									'default' => '#313131',
								),
								'tourmaster-tour-review-user-name' => array(
									'title' => esc_html__('Tour Review Item User Name', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-review-item .tourmaster-tour-review-item-user{ color: #gdlr#; }',
									'default' => '#5f5f5f',
								),

								'tourmaster-background' => array(
									'title' => esc_html__('Contcontent-navigation-ent Navigation Item Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-content-navigation-item-outer{ background-color: #gdlr#; }',
									'default' => '#ebebeb',
								),
								'tourmaster-content-navigation-text' => array(
									'title' => esc_html__('Content Navigation Item Text', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-content-navigation-item .tourmaster-content-navigation-tab{ color: #gdlr#; }',
									'default' => '#9a9a9a',
								),
								'tourmaster-content-navigation-active-text' => array(
									'title' => esc_html__('Content Navigation Item Active Text', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-content-navigation-item .tourmaster-content-navigation-tab.tourmaster-active, ' .
										'.tourmaster-content-navigation-item .tourmaster-content-navigation-tab:hover{ color: #gdlr#; }',
									'default' => '#1b1b1b',
								),
							)
						),
						'tourmaster-tour-search-item' => array(
							'title' => esc_html__('Tour Search Item', 'tourmaster'),
							'options' => array(
								'tourmaster-search-item-title' => array(
									'title' => esc_html__('Search Item/Filter Title', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-search-item-head .tourmaster-tour-search-item-head-title, ' . 
										'.tourmaster-tour-search-item .tourmaster-type-filter-title, ' . 
										'.tourmaster-tour-search-item-style-2 .tourmaster-type-filter-title i.icon_plus{ color: #gdlr#; }',
									'default' => '#383838',
								),
								'tourmaster-search-item-icon' => array(
									'title' => esc_html__('Search Item/Filter Icon', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-search-item .tourmaster-type-filter-title i, ' . 
										'.tourmaster-tour-search-item-head .tourmaster-tour-search-item-head-title i{ color: #gdlr#; }',
									'default' => '#a69371',
								),
								'tourmaster-search-input-background' => array(
									'title' => esc_html__('Search Input Background', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-search-wrap .tourmaster-tour-search-field input[type="text"], .tourmaster-tour-search-wrap .tourmaster-tour-search-field input[type="text"]:focus, .tourmaster-tour-search-wrap .tourmaster-tour-search-field select{ background-color: #gdlr#; }',
									'default' => '#585d6b',
								),
								'tourmaster-search-input-border' => array(
									'title' => esc_html__('Search Input Border', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-search-wrap .tourmaster-tour-search-field input[type="text"], ' . 
										'.tourmaster-tour-search-item-style-2 .tourmaster-tour-search-wrap .tourmaster-datepicker-wrap:after, ' .
										'.tourmaster-tour-search-wrap .tourmaster-tour-search-field select{ border-color: #gdlr#; }',
									'default' => '#676e74',
								),
								'tourmaster-search-input-title' => array(
									'title' => esc_html__('Search Input Title', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-search-wrap .tourmaster-tour-search-title{ color: #gdlr#; }',
									'default' => '#4674e7',
								),
								'tourmaster-search-input-label' => array(
									'title' => esc_html__('Search Input Label', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-search-wrap .tourmaster-tour-search-field label{ color: #gdlr#; }',
									'default' => '#5c5c5c',
								),
								'tourmaster-search-input-text' => array(
									'title' => esc_html__('Search Input Text', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-search-wrap .tourmaster-tour-search-field input[type="text"], .tourmaster-tour-search-wrap .tourmaster-tour-search-field input[type="text"]:focus, .tourmaster-tour-search-wrap .tourmaster-tour-search-field select{ color: #gdlr#; }' . 
										'.tourmaster-tour-search-wrap input::-webkit-input-placeholder{  color: #gdlr#; }' .
										'.tourmaster-tour-search-wrap input::-moz-placeholder{  color: #gdlr#; }' .
										'.tourmaster-tour-search-wrap input:-ms-input-placeholder{  color: #gdlr#; }' .
										'.tourmaster-tour-search-wrap input:-moz-placeholder{  color: #gdlr#; }',
									'default' => '#b9c1d5',
								),
								'tourmaster-search-input-icon' => array(
									'title' => esc_html__('Search Input Icon', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-search-wrap .tourmaster-datepicker-wrap:after, .tourmaster-tour-search-wrap .tourmaster-tour-search-field-inner:after, .tourmaster-tour-search-wrap .tourmaster-combobox-wrap:after{ color: #gdlr#; }',
									'default' => '#99a9d1',
								),
								'tourmaster-search-frame-background' => array(
									'title' => esc_html__('Tour Search Item Frame Backrgound', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-tour-search-wrap.tourmaster-with-frame{ background-color: #gdlr#; }',
									'default' => '#f5f5f5',
								),
								'tourmaster-search-filter-text' => array(
									'title' => esc_html__('Tour Search Filter Text', 'tourmaster'),
									'type' => 'colorpicker',
									'selector' => '.tourmaster-type-filter-term .tourmaster-type-filter-display, ' . 
										'.tourmaster-search-style-2 .tourmaster-tour-order-filterer-wrap .tourmaster-combobox-wrap select, ' . 
										'.tourmaster-search-style-2 .tourmaster-tour-search-field-keywords .tourmaster-tour-search-field-inner:after, ' . 
										'.tourmaster-search-style-2 .tourmaster-tour-search-field .tourmaster-combobox-wrap:after{ color: #gdlr#; }',
									'default' => '#b0b0b0',
								),
							)
						) // tourmaster-tour-search-item
					)
				));

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

						'import-export' => array(
							'title' => esc_html__('Import / Export', 'tourmaster'),
							'options' => array(

								'export' => array(
									'title' => esc_html__('Export Option', 'tourmaster'),
									'type' => 'export',
									'action' => 'tourmaster_plugin_option_export',
									'options' => array(
										'all' => esc_html__('All Options (general/color/miscellaneous)', 'tourmaster'),
										'tourmaster_general' => esc_html__('General', 'tourmaster'),
										'tourmaster_color' => esc_html__('Color', 'tourmaster'),
										'tourmaster_plugin' => esc_html__('Miscellaneous', 'tourmaster'),
									),
									'wrapper-class' => 'tourmaster-fullsize'
								),
								'import' => array(
									'title' => esc_html__('Import Option', 'tourmaster'),
									'type' => 'import',
									'wrapper-class' => 'tourmaster-fullsize'
								),

							) // import-options
						), // import-export

					)
				));
			}
		} // tourmaster_init_admin_option
	} // function_exists

