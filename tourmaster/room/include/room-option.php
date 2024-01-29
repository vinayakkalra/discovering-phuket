<?php
	/*	
	*	Tourmaster Plugin
	*	---------------------------------------------------------------------
	*	for room post type
	*	---------------------------------------------------------------------
	*/

	// create post type
	add_action('init', 'tourmaster_room_init');
	if( !function_exists('tourmaster_room_init') ){
		function tourmaster_room_init() {
			
			// custom post type
			$slug = apply_filters('tourmaster_custom_post_slug', 'room', 'room');
			$supports = apply_filters('tourmaster_custom_post_support', array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'revisions'), 'room');

			$labels = array(
				'name'               => esc_html__('Room', 'tourmaster'),
				'singular_name'      => esc_html__('Room', 'tourmaster'),
				'menu_name'          => esc_html__('Room', 'tourmaster'),
				'name_admin_bar'     => esc_html__('Room', 'tourmaster'),
				'add_new'            => esc_html__('Add New', 'tourmaster'),
				'add_new_item'       => esc_html__('Add New Room', 'tourmaster'),
				'new_item'           => esc_html__('New Room', 'tourmaster'),
				'edit_item'          => esc_html__('Edit Room', 'tourmaster'),
				'view_item'          => esc_html__('View Room', 'tourmaster'),
				'all_items'          => esc_html__('All Room', 'tourmaster'),
				'search_items'       => esc_html__('Search Room', 'tourmaster'),
				'parent_item_colon'  => esc_html__('Parent Room:', 'tourmaster'),
				'not_found'          => esc_html__('No room found.', 'tourmaster'),
				'not_found_in_trash' => esc_html__('No room found in Trash.', 'tourmaster')
			);
			$args = array(
				'show_in_rest' 		 => true,
				'labels'             => $labels,
				'description'        => esc_html__('Description.', 'tourmaster'),
				'public'             => true,
				'publicly_queryable' => true,
				'exclude_from_search'=> false,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'rewrite'            => array('slug' => $slug),
				'map_meta_cap' 		 => true,
				'capabilities' => array(
					'edit_post'          => 'edit_room', 
					'read_post'          => 'read_room', 
					'delete_post'        => 'delete_room', 
					'delete_posts'       => 'delete_rooms', 
					'edit_posts'         => 'edit_rooms', 
					'create_posts'       => 'edit_rooms', 
					'edit_others_posts'  	=> 'edit_others_rooms', 
					'delete_others_posts '  => 'edit_others_rooms', 
					'publish_posts'      	=> 'publish_rooms',       
					'edit_published_posts' 	=> 'publish_rooms',       
					'read_private_posts' 	=> 'read_private_rooms', 
					'edit_private_posts' 	=> 'read_private_rooms', 
					'delete_private_posts' 	=> 'read_private_rooms', 
				),
				'has_archive'        => false,
				'hierarchical'       => false,
				'menu_position'      => null,
				'supports'           => $supports
			);
			register_post_type('room', $args);

			// custom taxonomy
			$slug = apply_filters('tourmaster_custom_post_slug', 'room-category', 'room_category');
			$args = array(
				'show_in_rest' 		=> true,
				'hierarchical'      => true,
				'label'             => esc_html__('Room Category', 'tourmaster'),
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array('slug' => $slug),
				'capabilities'		=> array(
					'manage_terms' => 'manage_room_category', 
					'edit_terms' => 'manage_room_category', 
					'delete_terms' => 'manage_room_category', 
					'assign_terms' => 'manage_room_category'
				)
			);
			register_taxonomy('room_category', array('room'), $args);
			register_taxonomy_for_object_type('room_category', 'room');

			$slug = apply_filters('tourmaster_custom_post_slug', 'room-tag', 'room_tag');
			$args = array(
				'show_in_rest' 		=> true,
				'hierarchical'      => false,
				'label'             => esc_html__('Room Tag', 'tourmaster'),
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array('slug' => $slug),
				'capabilities'		=> array(
					'manage_terms' => 'manage_room_tag', 
					'edit_terms' => 'manage_room_tag', 
					'delete_terms' => 'manage_room_tag', 
					'assign_terms' => 'manage_room_tag'
				)
			);
			register_taxonomy('room_tag', array('room'), $args);
			register_taxonomy_for_object_type('room_tag', 'room');

			// apply single template filter
			add_filter('single_template', 'tourmaster_room_template');

		}
	} // tourmaster_post_type_init

	if( !function_exists('tourmaster_room_template') ){
		function tourmaster_room_template( $template ){

			if( get_post_type() == 'room' ){
				$template = TOURMASTER_LOCAL . '/room/single.php';
			}

			return $template;
		}
	}

	// add page builder to room
	if( is_admin() ){ add_filter('gdlr_core_page_builder_post_type', 'tourmaster_gdlr_core_room_add_page_builder'); }
	if( !function_exists('tourmaster_gdlr_core_room_add_page_builder') ){
		function tourmaster_gdlr_core_room_add_page_builder( $post_type ){
			$post_type[] = 'room';
			return $post_type;
		}
	}	

	// init page builder value
	if( is_admin() ){ add_filter('gdlr_core_room_page_builder_val_init', 'tourmaster_room_page_builder_val_init'); }
	if( !function_exists('tourmaster_room_page_builder_val_init') ){
		function tourmaster_room_page_builder_val_init( $value ){
			$value = '';		
			return json_decode($value, true);
		}
	}

	// create an option
	if( is_admin() ){ add_action('after_setup_theme', 'tourmaster_room_option_init'); }
	if( !function_exists('tourmaster_room_option_init') ){
		function tourmaster_room_option_init(){

			if( class_exists('tourmaster_page_option') ){
				if( !empty($_GET['post']) ){
					$ical_url = add_query_arg(array('tourmaster_room_ical'=>'', 'room_id'=>$_GET['post']), home_url('/'));
				}else{
					$ical_url = '';
				}

				new tourmaster_page_option(array(
					'post_type' => array('room'),
					'title' => esc_html__('Room Settings', 'tourmaster'),
					'title-icon' => 'fa fa-plane',
					'slug' => 'tourmaster-room-option',
					'options' => apply_filters('tourmaster_room_options', array(

						'general' => array(
							'title' => esc_html__('General', 'tourmaster'),
							'options' => array(

								'enable-page-title' => array(
									'title' => esc_html__('Enable Page Title', 'tourmaster'),
									'type' => 'combobox',
									'options' => array(
										'' => esc_html__('Default', 'tourmaster'),
										'enable' => esc_html__('Enable', 'tourmaster'),
										'disable' => esc_html__('Disable', 'tourmaster'),
									),
									'default' => ''
								),
								'header-image' => array(
									'title' => esc_html__('Header Image', 'tourmaster'),
									'type' => 'upload'
								),
								'header-background-overlay-opacity' => array(
									'title' => esc_html__('Title Background Overlay Opacity', 'tourmaster'),
									'type' => 'text',
									'description' => esc_html__('Fill the number between 0 - 1 ( Leave Blank For Default Value )', 'tourmaster'),
								),
								'title-background-top-radius' => array(
									'title' => esc_html__('Page Title Background Top Radius', 'hotale'),
									'type' => 'text',
									'data-input-type' => 'pixel',
								),
								'title-background-bottom-radius' => array(
									'title' => esc_html__('Page Title Background Bottom Radius', 'hotale'),
									'type' => 'text',
									'data-input-type' => 'pixel',
								),
								'show-wordpress-editor-content' => array(
									'title' => esc_html__('Show Wordpress Editor Content', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable'
								),
								'enable-review' => array(
									'title' => esc_html__('Enable Review', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable'
								)

								/*
								'sidebar-widget' => array(
									'title' => esc_html__('Sidebar Widget', 'tourmaster'),
									'type' => 'combobox',
									'options' => 'sidebar-default',
									'default' => 'default'
								),
								*/

							)
						), // general

						'display-info' => array(
							'title' => esc_html__('Display Info', 'tourmaster'),
							'options' => array(
								'price-prefix' => array(
									'title' => esc_html__('Price Prefix', 'tourmaster'),
									'type' => 'text',
									'single' => 'tourmaster-room-price-prefix'
								),
								'price-text' => array(
									'title' => esc_html__('Price Text', 'tourmaster'),
									'type' => 'text',
									'single' => 'tourmaster-room-price-text'
								),
								'price-discount-text' => array(
									'title' => esc_html__('Price Discount Text', 'tourmaster'),
									'type' => 'text',
									'single' => 'tourmaster-room-price-discount-text'
								),
								'price-suffix' => array(
									'title' => esc_html__('Price Suffix', 'tourmaster'),
									'type' => 'text',
									'single' => 'tourmaster-room-price-suffix'
								),
								'ribbon-text' => array(
									'title' => esc_html__('Ribbon Text', 'tourmaster'),
									'type' => 'text',
									'single' => 'tourmaster-room-ribbon-text'
								),
								'ribbon-color' => array(
									'title' => esc_html__('Ribbon Color', 'tourmaster'),
									'type' => 'colorpicker',
									'single' => 'tourmaster-room-ribbon-color'
								),
								'bed-type' => array(
									'title' => esc_html__('Bed Type', 'tourmaster'),
									'type' => 'text',
									'single' => 'tourmaster-room-bed-type'
								),
								'guest-amount' => array(
									'title' => esc_html__('Guest Amount', 'tourmaster'),
									'type' => 'text',
									'single' => 'tourmaster-room-guest-amount'
								),
								'room-size' => array(
									'title' => esc_html__('Room Size', 'tourmaster'),
									'type' => 'text',
									'single' => 'tourmaster-room-size-text'
								),
								'custom-excerpt' => array(
									'title' =>  esc_html__('Custom Excerpt', 'tourmaster'),
									'type' => 'textarea',
									'single' => 'tourmaster-room-custom-excerpt'
								),
								'location' => array(
									'title' => esc_html__('Location', 'tourmaster'),
									'type' => 'text',
									'single' => 'tourmaster-room-location'
								)
							)
						),

						'room-settings' => array(
							'title' => esc_html__('Room Settings', 'tourmaster'),
							'options' => array(

								'room-amount' => array(
									'title' => esc_html__('Room Amount', 'tourmaster'),
									'type' => 'text',
									'default' => 1,
									'single' => 'tourmaster-room-amount'
								),
								'ical-sync-url' => array(
									'title' => esc_html__('ICAL Sync URL' , 'tourmaster'),
									'type' => 'textarea',
									'default' => 1,
									'single' => 'tourmaster_ical_sync_url',
									'description' => esc_html__('Ical only supports when room amount is set to 1', 'tourmaster') . '<br>' .
										esc_html__('You can fill multiple lines for each .ics url you want to sync.', 'tourmaster') . '<br> ' . 
										esc_html__('This is our room ical url.', 'tourmaster') . ' <a href="' . esc_attr($ical_url) . '" target="_blank" >' . esc_attr($ical_url) . '</a>'
										
								),
								'max-guest' => array(
									'title' => esc_html__('Max Guest / Room', 'tourmaster'),
									'type' => 'text',
									'single' => 'tourmaster-room-max-guest'
								),
								'min-guest' => array(
									'title' => esc_html__('Min Guest / Room', 'tourmaster'),
									'type' => 'text',
									'single' => 'tourmaster-room-min-guest'
								),
								/*
								'payment-admin-approval' => array(
									'title' => esc_html__('Needs Admin Approval Before Payment', 'tourmaster'),
									'type' => 'combobox',
									'options' => array(
										'' => esc_html__('Default', 'tourmaster'),
										'enable' => esc_html__('Enable', 'tourmaster'),
										'disable' => esc_html__('Disable', 'tourmaster'),
									),
								),
								*/
								'form-settings' => array(
									'title' =>  esc_html__('Reservation Bar', 'tourmaster'),
									'type' => 'combobox',
									'options' => array(
										'booking' => esc_html__('Only Booking Form', 'tourmaster'),
										'enquiry' => esc_html__('Only Enquiry Form', 'tourmaster'),
										'both' => esc_html__('Both Booking & Enquiry Form', 'tourmaster'),
										'custom' => esc_html__('Custom Code', 'tourmaster'),
										'none' => esc_html__('None ( Hide the right side out )', 'tourmaster'),
									),
									'default' => 'booking'
								),
								/*
								'extra-booking-info' => array(
									'title' => esc_html__('Custom Extra Booking Info', 'tourmaster'),
									'type' => 'textarea',
									'single' => 'tourmaster-extra-booking-info',
									'description' => wp_kses(__('You can see how to create the fields <a href="http://support.goodlayers.com/document/2017/10/06/tourmaster-modifying-the-enquiry-form/" target="_blank" >here</a>', 'tourmaster'), array('a'=>array( 'href'=> array(), 'target'=>array())) ) . '<br>' .
										esc_html__('Use for gathering plan data only. This custom booking info has nothing to do with system calculation such as booking date', 'tourmaster'),
									'condition' => array( 'form-settings' => array('booking', 'both') )
								),
								'contact-detail-fields' => array(
									'title' => esc_html__('Custom Contact Detail Fields', 'tourmaster'),
									'type' => 'textarea',
									'single' => 'tourmaster-contact-detail-fields',
									'description' => wp_kses(__('You can see how to create the fields <a href="https://support.goodlayers.com/document/2018/05/01/tourmaster-modifying-the-contact-detail-fields-since-v3-0-8/" target="_blank" >HERE</a>', 'tourmaster'), array( 'a' => array( 'href' => array(), 'target' => array() ) )) . '<br>' . 
										esc_html__('If left blank, the system will use default settings from Tour Master panel settings.', 'tourmaster'),
									'condition' => array( 'form-settings' => array('booking', 'both') )
								),
								*/
								'enquiry-form-fields' => array(
									'title' => esc_html__('Custom Enquiry Form Fields', 'tourmaster'),
									'type' => 'textarea',
									'single' => 'tourmaster-enquiry-form-fields',
									'description' => wp_kses(__('You can see how to create the fields <a href="https://support.goodlayers.com/document/2017/10/06/tourmaster-modifying-the-enquiry-form/" target="_blank" >HERE</a>', 'tourmaster'), array( 'a' => array( 'href' => array(), 'target' => array() ) )),
									'condition' => array( 'form-settings' => array('enquiry', 'both') )
								),
								'enquiry-form-mail-content-admin' => array(
									'title' => esc_html__('Custom Enquiry Form Mail Content (Admin)', 'tourmaster'),
									'type' => 'textarea',
									'single' => 'tourmaster-enquiry-form-mail-content-admin',
									'condition' => array( 'form-settings' => array('enquiry', 'both') )
								),
								'enquiry-form-mail-content-customer' => array(
									'title' => esc_html__('Custom Enquiry Form Mail Content (Customer)', 'tourmaster'),
									'type' => 'textarea',
									'single' => 'tourmaster-enquiry-form-mail-content-customer',
									'condition' => array( 'form-settings' => array('enquiry', 'both') )
								),
								'form-custom-code' => array(
									'title' => esc_html__('Custom Code', 'tourmaster'),
									'type' => 'textarea',
									'condition' => array( 'form-settings' => 'custom' )
								),
								/*
								'show-price' => array(
									'title' =>  esc_html__('Show Header Price', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable',
									'condition' => array( 'form-settings' => 'enquiry' )
								),
								'date-selection-type' => array(
									'title' =>  esc_html__('Date Selection Type', 'tourmaster'),
									'type' => 'combobox',
									'options' => array(
										'calendar' => esc_html__('Calendar', 'tourmaster'),
										'date-list' => esc_html__('Date List', 'tourmaster')
									),
									'condition' => array('form-settings' => array('booking', 'both') )
								),
								*/
								'last-minute-booking' => array(
									'title' =>  esc_html__('Last Minute Booking (Hour)', 'tourmaster'),
									'type' => 'text',
									'condition' => array('form-settings' => array('booking', 'both') ),
									'description' =>  esc_html__('Specify the number of hours prior to the travel time you want to close the booking system.', 'tourmaster'),
								),
								'book-in-advance' => array(
									'title' =>  esc_html__('Book In Advance (Month)', 'tourmaster'),
									'type' => 'text',
									'single' => 'tourmaster-book-in-advance',
									'condition' => array('form-settings' => array('booking', 'both') ),
									'description' =>  esc_html__('For example, If you fill the number "10" (for ten months) and today is in March 2019, customers will have an ability to book the room from today until Jan 2020 (ten months from current month). Leave this field blank for unlimited booking in advanced.', 'tourmaster'),
								),
								/*
								'deposit-booking' => array(
									'title' =>  esc_html__('Deposit Booking', 'tourmaster'),
									'type' => 'combobox',
									'options' => array(
										'default' => esc_html__('Default', 'tourmaster'),
										'enable' => esc_html__('Enable (Custom)', 'tourmaster'),
										'disable' => esc_html__('Disable', 'tourmaster')
									),
									'description' => esc_html__('Default value can be set at the "Tourmaster" plugin option.', 'tourmaster'),
									'condition' => array('form-settings' => array('booking', 'both') ),
								),
								'deposit-amount' => array(
									'title' =>  esc_html__('Deposit Amount (%)', 'tourmaster'),
									'type' => 'text',
									'description' => esc_html__('Only fill number here.', 'tourmaster'),
									'condition' => array('form-settings' => array('booking', 'both'), 'deposit-booking' => 'enable')
								),
								'deposit2-amount' => array(
									'title' =>  esc_html__('Deposit 2 Amount (%)', 'tourmaster'),
									'type' => 'text',
									'description' => esc_html__('Only fill number here.', 'tourmaster'),
									'condition' => array('form-settings' => array('booking', 'both'), 'deposit-booking' => 'enable')
								),
								'deposit3-amount' => array(
									'title' =>  esc_html__('Deposit 3 Amount (%)', 'tourmaster'),
									'type' => 'text',
									'description' => esc_html__('Only fill number here.', 'tourmaster'),
									'condition' => array('form-settings' => array('booking', 'both'), 'deposit-booking' => 'enable')
								),
								'deposit4-amount' => array(
									'title' =>  esc_html__('Deposit 4 Amount (%)', 'tourmaster'),
									'type' => 'text',
									'description' => esc_html__('Only fill number here.', 'tourmaster'),
									'condition' => array('form-settings' => array('booking', 'both'), 'deposit-booking' => 'enable')
								),
								'deposit5-amount' => array(
									'title' =>  esc_html__('Deposit 5 Amount (%)', 'tourmaster'),
									'type' => 'text',
									'description' => esc_html__('Only fill number here.', 'tourmaster'),
									'condition' => array('form-settings' => array('booking', 'both'), 'deposit-booking' => 'enable')
								),
								'tour-price-text' => array(
									'title' =>  esc_html__('Tour Price Text', 'tourmaster'),
									'type' => 'text',
									'description' => esc_html__('Use for search function and displaying as tour information. Only fill number here.', 'tourmaster'),
									'condition' => array( 'form-settings' => array('booking', 'enquiry', 'both', 'custom') )
								),
								'tour-price-discount-text' => array(
									'title' =>  esc_html__('Tour Price Discount Text', 'tourmaster'),
									'type' => 'text',
									'description' => esc_html__('Use for search function and displaying as tour information. Only fill number here.', 'tourmaster'),
									'condition' => array( 'form-settings' => array('booking', 'enquiry', 'both', 'custom') )
								),
								'tour-price-range' => array(
									'title' =>  esc_html__('Tour Price Range ( Only For Schema Data )', 'tourmaster'),
									'type' => 'text',
									'description' => esc_html__('This is an example of price renge format "$100 - $1000"', 'tourmaster'),
									'single' => 'tourmaster-tour-price-range',
									'condition' => array( 'form-settings' => array('booking', 'enquiry', 'both', 'custom') )
								),
							
								'require-each-traveller-info' => array(
									'title' =>  esc_html__('Require Each Traveller\'s Info', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable',
									'condition' => array( 'form-settings' => array('booking', 'both') ),
									'description' => esc_html__('This option requires customer to fill name and last name of each traveller.', 'tourmaster')
								),
								'additional-traveller-fields' => array(
									'title' => esc_html__('Custom Traveller Detail Fields', 'tourmaster'),
									'type' => 'textarea',
									'description' => wp_kses(__('You can see how to create the fields <a href="https://support.goodlayers.com/document/2018/05/03/tourmaster-modifying-the-traveller-detail-fields-since-v3-0-8/" target="_blank" >HERE</a>', 'tourmaster'), array( 'a' => array( 'href' => array(), 'target' => array() ) )),
									'condition' => array( 'form-settings' => array('booking', 'both'), 'require-each-traveller-info' => 'enable' )
								),
								'require-traveller-info-title' => array(
									'title' =>  esc_html__('Require Traveller\'s Title (Mr/Mrs)', 'tourmaster'),
									'type' => 'checkbox',
									'condition' => array( 'require-each-traveller-info' => 'enable', 'form-settings' => array('booking', 'both') ),
									'default' => 'enable'
								),
								'require-traveller-passport' => array(
									'title' =>  esc_html__('Require Traveller\'s Passport', 'tourmaster'),
									'type' => 'checkbox',
									'condition' => array( 'require-each-traveller-info' => 'enable', 'form-settings' => array('booking', 'both') ),
									'default' => 'disable'
								),
								*/
								'room-service' => array(
									'title' =>  esc_html__('Room Service', 'tourmaster'),
									'type' => 'multi-combobox',
									'options' => 'post_type',
									'options-data' => 'room_service',
									'condition' => array( 'form-settings' => array('booking', 'both') ),
								),
								/*
								'link-proceed-booking-to-external-url' => array(
									'title' =>  esc_html__('Link Proceed Booking Button To External URL', 'tourmaster'),
									'type' => 'text',
									'condition' => array('form-settings' => array('booking', 'both') ),
									'description' => esc_html__('This option will ignore all booking variables.', 'tourmaster')
								),
								'external-url-text' => array(
									'title' =>  esc_html__('External URL Text', 'tourmaster'),
									'type' => 'textarea',
									'condition' => array('form-settings' => array('booking', 'both') ),
									'description' => esc_html__('Only works with external url.', 'tourmaster')
								),
								*/
								'enable-review' => array(
									'title' => esc_html__('Enable Review', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable'
								)
							)
						), // 'tour-settings'

						'date-price' => array(
							'title' => esc_html__('Date & Price', 'tourmaster'),
							'options' => array(

								'date-price' => array(
									'title' => esc_html__('Add Date & Price', 'tourmaster'),
									'type' => 'custom',
									'item-type' => 'tabs',
									'options' => array(
										'day' => array(
											'title' => esc_html__('Day', 'tourmaster'),
											'type' => 'checkboxes',
											'options' => array(
												'monday' => esc_html__('Mon', 'tourmaster'),
												'tuesday' => esc_html__('Tue', 'tourmaster'),
												'wednesday' => esc_html__('Wed', 'tourmaster'),
												'thursday' => esc_html__('Thu', 'tourmaster'),
												'friday' => esc_html__('Fri', 'tourmaster'),
												'saturday' => esc_html__('Sat', 'tourmaster'),
												'sunday' => esc_html__('Sun', 'tourmaster'),
												'select-all' => esc_html__('Select All', 'tourmaster'),
												'deselect-all' => esc_html__('Deselect All', 'tourmaster'),
											)
										),
										'month' => array(
											'title' => esc_html__('Month', 'tourmaster'),
											'type' => 'checkboxes',
											'options' => array(
												'1' => esc_html__('Jan', 'tourmaster'),
												'2' => esc_html__('Feb', 'tourmaster'),
												'3' => esc_html__('Mar', 'tourmaster'),
												'4' => esc_html__('Apr', 'tourmaster'),
												'5' => esc_html__('May', 'tourmaster'),
												'6' => esc_html__('Jun', 'tourmaster'),
												'7' => esc_html__('Jul', 'tourmaster'),
												'8' => esc_html__('Aug', 'tourmaster'),
												'9' => esc_html__('Sep', 'tourmaster'),
												'10' => esc_html__('Oct', 'tourmaster'),
												'11' => esc_html__('Nov', 'tourmaster'),
												'12' => esc_html__('Dec', 'tourmaster'),
												'select-all' => esc_html__('Select All', 'tourmaster'),
												'deselect-all' => esc_html__('Deselect All', 'tourmaster'),
											)
										),
										'year' => array(
											'title' => esc_html__('Year', 'tourmaster'),
											'type' => 'checkboxes',
											'options' => array(
												'2021' => '2021',
												'2022' => '2022',
												'2023' => '2023',
												'2024' => '2024',
												'2025' => '2025',
												'2026' => '2026',
												'2027' => '2027',
												'2028' => '2028',
											)
										),

										'extra-date-description' => array(
											'description' => esc_html__('Fill the date in yyyy-mm-dd format and separated the date using comma. Eg. 2020-12-25,2020-12-26,2020-12-27', 'tourmaster'),
											'type' => 'description'
										),
										'extra-date' => array(
											'title' => esc_html__('INCLUDE EXTRA DATES USING DATE FORMAT', 'tourmaster'),
											'type' => 'textarea',
											'wrapper_class' => 'tourmaster-full-size',
											'title_color' => '#67b1a1'
										),
										'exclude-extra-date' => array(
											'title' => esc_html__('EXCLUDE EXTRA DATES USING DATE FORMAT', 'tourmaster'),
											'type' => 'textarea',
											'wrapper_class' => 'tourmaster-full-size',
											'title_color' => '#be7272'
										),

										'pricing-title' => array(
											'title' => esc_html__('PRICING', 'tourmaster'),
											'type' => 'title',
											'wrapper_class' => 'tourmaster-middle-with-divider'
										),
										'base-price' => array(
											'title' => esc_html__('Base Price', 'tourmaster'),
											'type' => 'text',
										),
										'base-price-guests' => array(
											'title' => esc_html__('Base Price Guests', 'tourmaster'),
											'type' => 'text',
											'default' => 2,
											'description' => esc_html__('*Base Price Guests is for the maximum guests amount that will be charged with Base Price. Additional guests will be charged by the below pricing option. The Base Price field accepts only numbers. Don’t fill currency sign nor commas.', 'tourmaster')
										),
										'additional-adult-price' => array(
											'title' => esc_html__('Additional Adult Price', 'tourmaster'),
											'type' => 'text',
										),
										'additional-child-price' => array(
											'title' => esc_html__('Additional Child Price', 'tourmaster'),
											'type' => 'text',
										),
									),
									'settings' => array(
										'tab-title' => esc_html__('Date', 'tourmaster') . '<i class="fa fa-edit" ></i>',
										'allow-duplicate' => '<i class="fa fa-copy" ></i>' . esc_html__('Duplicate', 'tourmaster'),
									),
									'wrapper-class' => 'tourmaster-with-bottom-divider'
								),

								/*
								'group-discount-title' => array(
									'title' => esc_html__('Group Discount', 'tourmaster'),
									'type' => 'title',
									'wrapper-class' => 'tourmaster-main-title'
								),
								'group-discount-category' => array(
									'title' => esc_html__('Group Discount Category Counting', 'tourmaster'),
									'type' => 'multi-combobox',
									'options' => array(
										'adult' => esc_html__('Adult', 'tourmaster'),
										'male' => esc_html__('Male', 'tourmaster'),
										'female' => esc_html__('Female', 'tourmaster'),
										'children' => esc_html__('Children', 'tourmaster'),
										'student' => esc_html__('Student', 'tourmaster'),
										'infant' => esc_html__('Infant', 'tourmaster'),
									),
									'description' => esc_html__('Leave this field blank to select all traveller types. Use "ctrl" to select multiple or deselect the option.', 'tourmaster') . 
										'<br><br>' . esc_html__('This option will let you choose which group to be counted for discount. Ex. if you choose to use only Adult to be counted and choose 3 Travellers Number to get discount. When select 2 adult + 1 child, this condition will not be met. However, if select 3 adults + 1 child, this condition met. Note that this option apply to Variable Price only.', 'tourmaster')
								),
								'group-discount-apply' => array(
									'title' => esc_html__('Group Discount Apply To', 'tourmaster'),
									'type' => 'multi-combobox',
									'options' => array(
										'adult' => esc_html__('Adult', 'tourmaster'),
										'male' => esc_html__('Male', 'tourmaster'),
										'female' => esc_html__('Female', 'tourmaster'),
										'children' => esc_html__('Children', 'tourmaster'),
										'student' => esc_html__('Student', 'tourmaster'),
										'infant' => esc_html__('Infant', 'tourmaster'),
									),
									'description' => esc_html__('You can choose  which category to get discount when discount condition met.', 'tourmaster')
								),
								'group-discount-per-person' => array(
									'title' => esc_html__('Group Discount Based On Person', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'disable',
									'description' => esc_html__('This option will be automatically set to "Enable" if the "Group Discount Apply To" option is selected.', 'tourmaster') . 
										'<br><br>' . esc_html__('If you turn this option on, the discount will apply on per person basis and if you\'re using \'Room Base\' pricing, it will only apply to \'Base Price\' and won\'t apply to \'Room Based Price\'. Please also note that with this option, the discount won\'t be applied to "Tour Service" as well. However, if you turn this option off, the discount will be applied to everything and will be shown as discount at the end of price breakdown.', 'tourmaster')
								),
								'group-discount' => array(
									'title' => esc_html__('Add Group Discount', 'tourmaster'),
									'type' => 'custom',
									'item-type' => 'group-discount',
									'options' => array(
										'traveller-number' => array(
											'title' => esc_html__('Travellers number', 'tourmaster'),
											'type' => 'text'
										),
										'discount' => array(
											'title' => esc_html__('Discount', 'tourmaster'),
											'type' => 'text'
										),
										'description' => array(
											'type' => 'description',
											'description' => esc_html__('* Fill only number for fixed amount, ex. \'10\' for $10. Fill % at the end if using as percentage, ex. \'10%\'' , 'tourmaster')
										)
									),
									'description' => esc_html__('For example, if you create 2 discount boxes, and for the first box, you set up 5 travellers with 15% discount and for another box, 10 travellers with 25% discount. When customers book for 5,6,7,8,9 travellers, they will get 15% off. However, if they book for 10, 11, 12 ( and so on ) travellers, they will get 25% off.', 'tourmaster')
								)
								*/
							),
						), // 'date-price'

						/*
						'urgency-message' => array(
							'title' => esc_html__('Urgency Message', 'tourmaster'),
							'options' => array(
								'enable-urgency-message' => array(
									'title' =>  esc_html__('Enable Urgency Message :', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'disable',
									'description' => esc_html__('By enabling this option, the urgent message will be shown in the front-end of the single tour. Ex. "20 travellers are considering this tour right now!"', 'tourmaster') . '<br>' . 
										esc_html__('** Urgency message will be disappeared for 1 day after you close it.', 'tourmaster')
								),
								'real-urgency-message' => array(
									'title' =>  esc_html__('Use Real Data :', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable',
									'description' => esc_html__('Real data will record each user for 1 hour.', 'tourmaster')
								),
								'urgency-message-number-from' => array(
									'title' =>  esc_html__('Number From :', 'tourmaster'),
									'type' => 'text',
									'default' => '5',
									'condition' => array( 'real-urgency-message' => 'disable' ),
									'description' => esc_html__('The system will randomly pick the number between "from" and "to" fields.', 'tourmaster')
								),
								'urgency-message-number-to' => array(
									'title' =>  esc_html__('Number To :', 'tourmaster'),
									'type' => 'text',
									'default' => '10',
									'condition' => array( 'real-urgency-message' => 'disable' )
								),
							)
						), // urgency message

						'group-message' => array(
							'title' => esc_html__('Reminder & Message', 'tourmaster'),
							'options' => array(
								'carbon-copy-mail' => array(
									'title' =>  esc_html__('Carbon Copy Email (CC)', 'tourmaster'),
									'type' => 'text',
									'single' => 'tourmaster-tour-cc-mail',
									'description' => esc_html__('Fill the email here to send a copy of an Admin Email for transaction related to this tour.', 'tourmaster')
								),
								'payment-notification-title' => array(
									'title' =>  esc_html__('Payment Notification', 'tourmaster'),
									'type' => 'title',
								),
								'enable-payment-notification' => array(
									'title' =>  esc_html__('Enable Payment Notification', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'disable',
									'single' => 'tourmaster-payment-notification',
									'description' => esc_html__('By, enabling this option, the system will automatically send a payment notification to customer\'s email.', 'tourmaster')
								),
								'payment-notification-days-before-travel' => array(
									'title' =>  esc_html__('Days Before Travel (Haven\'t Paid)', 'tourmaster'),
									'type' => 'text',
									'condition' => array( 'enable-payment-notification' => 'enable' ),
									'description' => esc_html__('Send reminder message XX days before the travel date. This will remind customers if customers haven\'t paid for anything yet.', 'tourmaster')
								),
								'deposit-payment-notification-days-before-travel' => array(
									'title' =>  esc_html__('Days Before Travel (Deposit Paid)', 'tourmaster'),
									'type' => 'text',
									'condition' => array( 'enable-payment-notification' => 'enable' ),
									'description' => esc_html__('Send reminder message XX days before the travel date. This will remind customers if customers have paid the deposit but haven\' paid the rest amount yet. It will remind customers to pay the rest. If you allow to pay at arrival, you may skip this feature so ones who paid the deposit won\'t get the reminder message.', 'tourmaster')
								),
								'payment-notification-mail-subject' => array(
									'title' =>  esc_html__('Email Subject', 'tourmaster'),
									'type' => 'text',
									'condition' => array( 'enable-payment-notification' => 'enable' ),
								),
								'payment-notification-mail-message' => array(
									'title' =>  esc_html__('Email Message', 'tourmaster'),
									'type' => 'textarea',
									'condition' => array( 'enable-payment-notification' => 'enable' ),
								),
								'enable-payment-notification-message-admin-copy' => array(
									'title' =>  esc_html__('Send a copy to admin', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'disable',
									'condition' => array( 'enable-payment-notification' => 'enable' ),
								),

								'reminder-message-title' => array(
									'title' =>  esc_html__('Reminder Message', 'tourmaster'),
									'type' => 'title',
								),
								'enable-reminder-message' => array(
									'title' =>  esc_html__('Enable Reminder Message', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'disable',
									'single' => 'tourmaster-reminder-message',
									'description' => esc_html__('By, enabling this option, the system will automatically send a reminder message to customer\'s email.', 'tourmaster')
								),
								'reminder-message-days-before-travel' => array(
									'title' =>  esc_html__('Reminder Message Days Before Travel', 'tourmaster'),
									'type' => 'text',
									'condition' => array( 'enable-reminder-message' => 'enable' ),
									'description' => esc_html__('Only number is allowed here.', 'tourmaster')
								),
								'reminder-message-mail-subject' => array(
									'title' =>  esc_html__('Email Subject', 'tourmaster'),
									'type' => 'text',
									'condition' => array( 'enable-reminder-message' => 'enable' ),
								),
								'reminder-message-mail-message' => array(
									'title' =>  esc_html__('Email Message', 'tourmaster'),
									'type' => 'textarea',
									'condition' => array( 'enable-reminder-message' => 'enable' ),
								),
								'enable-reminder-message-admin-copy' => array(
									'title' =>  esc_html__('Send a copy to admin', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'disable',
									'condition' => array( 'enable-reminder-message' => 'enable' ),
								),

								'group-message-title' => array(
									'title' =>  esc_html__('Group Message', 'tourmaster'),
									'type' => 'title',
									'wrapper-class' => 'tourmaster-top-margin-wrapper'
								),
								'group-message-date' => array(
									'title' =>  esc_html__('Group Message Date', 'tourmaster'),
									'type' => 'datepicker',
									'description' => esc_html__('* To specify the exact group of customer that you want to send the message to.', 'tourmaster')
								),
								'group-message-mail-subject' => array(
									'title' =>  esc_html__('Email Subject', 'tourmaster'),
									'type' => 'text',
								),
								'group-message-mail-message' => array(
									'title' =>  esc_html__('Email Message', 'tourmaster'),
									'type' => 'textarea',
								),
								'enable-group-message-admin-copy' => array(
									'title' =>  esc_html__('Send a copy to admin', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'disable',
									'description' => esc_html__('Enable this to send the copy of the mail which cusmoter receieve to admin e-mail.', 'tourmaster')
								),
								'group-message-submit' => array(
									'button-title' =>  esc_html__('Send Email', 'tourmaster'),
									'type' => 'button',
									'data-type' => 'ajax',
									'data-action' => 'tourmaster_submit_group_message',
									'data-fields' => array( 'group-message-date', 'group-message-mail-subject', 'group-message-mail-message', 'enable-group-message-admin-copy', 'group-message-tour-id' ) 
								),
							)
						),

						*/

					)) // tourmaster_tour_options
				)); // tourmaster_page_option

				new tourmaster_page_option(array(
					'post_type' => array('room'),
					'title' => esc_html__('Review Manger', 'tourmaster'),
					'title-icon' => 'fa fa-plane',
					'slug' => 'tourmaster-review-option',
					'options' => apply_filters('tourmaster_review_options', array(

						'manage-review' => array(
							'title' => esc_html__('Manage Review', 'tourmaster'),
							'options' => array(

								'manage-review' => array(
									'type' => 'manage-review-room'
								)

							)
						), // manage review

						'add-a-review' => array(
							'title' => esc_html__('Add A Review', 'tourmaster'),
							'options' => array(

								'add-review' => array(
									'type' => 'add-review-room'
								)

							)
						), // add a review

					))
				));

			} // function_exits

		} // tourmaster_tour_option_init
	}	

	// save tour meta option hook
	if( is_admin() ){ 
		add_action('save_post_room', 'tourmaster_save_post_room_meta', 11); 
		add_action('tourmaster_after_ajax_save_page_option', 'tourmaster_save_room_meta');
	}
	if( !function_exists('tourmaster_save_post_room_meta') ){
		function tourmaster_save_post_room_meta( $post_id ){

			// check if nonce is available
			if( !isset($_POST['plugin_page_option_security']) ){
				return;
			}

			// vertify that the nonce is vaild
			if( !wp_verify_nonce($_POST['plugin_page_option_security'], 'tourmaster_page_option') ) {
				return;
			}

			// ignore the auto save
			if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
				return;
			}

			// check the user's permissions.
			if( isset($_POST['post_type']) && 'room' == $_POST['post_type'] ) {
				if( !current_user_can('edit_post', $post_id) ){
					return;
				}
			}

			tourmaster_save_room_meta($post_id);

		} // tourmaster_save_tour_meta
	}
	if( !function_exists('tourmaster_save_room_meta') ){
		function tourmaster_save_room_meta( $post_id ){

			$post_type = get_post_type($post_id);
			if( $post_type != 'room' ) return;

			// additional meta field
			if( !empty($post_id) ){
				if( empty($_POST['tourmaster-room-option']) ){
					$room_option = get_post_meta($post_id, 'tourmaster-room-option', true);
				}else{
					$room_option = json_decode(tourmaster_process_post_data($_POST['tourmaster-room-option']), true);
				}
				
				// determine all available dates
				if( !empty($room_option['date-price']) ){
					$date_list = array();
					$package_date_list = array();
					foreach( $room_option['date-price'] as $settings ){
						$dates = tourmaster_get_tour_dates($settings, 'multiple');
						sort($dates);
						
						$date_list = array_merge($date_list, $dates);
						$package_date_list[] = implode(',', $dates);
					}

					if( !empty($date_list) ){
						$date_list = array_unique($date_list);
						sort($date_list);
						update_post_meta($post_id, 'tourmaster-room-date', implode(',', $date_list));
						update_post_meta($post_id, 'tourmaster-room-package-date', $package_date_list);

						$book_in_advance = get_post_meta($post_id, 'tourmaster-book-in-advance', true);
						$date_avail = tourmaster_filter_tour_date($date_list, $book_in_advance);
						if( !empty($date_avail) ){
							update_post_meta($post_id, 'tourmaster-room-date-avail', implode(',', $date_avail));

							tourmaster_room_calculate_date_display($result->post_id, array(
								'date-avail' => $date_avail
							));
						}else{
							delete_post_meta($post_id, 'tourmaster-room-date-avail');
							delete_post_meta($post_id, 'tourmaster-room-date-display');
						}

						tourmaster_room_check_occupied($post_id);
					}else{
						delete_post_meta($post_id, 'tourmaster-room-date');
						delete_post_meta($post_id, 'tourmaster-room-date-avail');
					}
				}else{
					delete_post_meta($post_id, 'tourmaster-room-date');
					delete_post_meta($post_id, 'tourmaster-room-date-avail');
				}

				tourmaster_room_update_review_score($post_id);
			}

		}
	}

	// trigger the date available date every day
	add_action('tourmaster_schedule_daily', 'tourmaster_daily_filter_room_date');
	if( !function_exists('tourmaster_daily_filter_room_date') ){
		function tourmaster_daily_filter_room_date(){
			global $wpdb;

			// filter available date
			$sql  = "SELECT post_id, meta_value FROM {$wpdb->postmeta} ";
		    $sql .= "WHERE meta_key = 'tourmaster-room-date' ";
		    $results = $wpdb->get_results($sql);
		    if( !empty($results) ){
		    	foreach( $results as $result ){
		    		$date_list = explode(',', $result->meta_value);
		    		$book_in_advance = get_post_meta($result->post_id, 'tourmaster-book-in-advance', true);
					$date_avail = tourmaster_filter_tour_date($date_list, $book_in_advance);
					if( !empty($date_avail) ){
						update_post_meta($result->post_id, 'tourmaster-room-date-avail', implode(',', $date_avail));

						tourmaster_room_calculate_date_display($result->post_id, array(
							'date-avail' => $date_avail
						));
					}else{
						delete_post_meta($result->post_id, 'tourmaster-room-date-avail');
						delete_post_meta($result->post_id, 'tourmaster-room-date-display');
					}
		    	}
		    }

		} // tourmaster_hourly_filter_tour_date
	}

	if( !function_exists('tourmaster_room_calculate_date_display') ){
		function tourmaster_room_calculate_date_display( $post_id, $settings = array() ){

			// date avail
			if( !empty($settings['date-avail']) ){
				$date_avail = $settings['date-avail'];
			}else{
				$date_avail = get_post_meta($post_id, 'tourmaster-room-date-avail', true);
				$date_avail = empty($date_avail)? array(): explode(',', $date_avail);
			}

			if( !empty($date_avail) ){

				// date occupy
				if( !empty($settings['date-occupied']) ){
					$date_occupied = $settings['date-occupied'];
				}else{
					$date_occupied = get_post_meta($post_id, 'tourmaster-room-date-occupied', true);
					$date_occupied = empty($date_occupied)? array(): explode(',', $date_occupied);
				}
				$date_display = array_diff($date_avail, $date_occupied);

				// date ical
				if( !empty($settings['date-ical']) ){
					$date_ical = $settings['date-ical'];
				}else{
					$ical_date_list = get_post_meta($post_id, 'tourmaster_ical_sync_date_list', true);
					$ical_date_list = empty($ical_date_list)? array(): explode(',', $ical_date_list);
				}
				$date_display = array_diff($date_display, $ical_date_list);

				// block date
				$block_date = tourmaster_get_option('room_general', 'block-date', '');
				if( !empty($block_date) ){
					$block_date = explode(',', $block_date);
					$date_display = array_diff($date_display, $block_date);
				}

				update_post_meta($post_id, 'tourmaster-room-date-display', implode(',', $date_display));

			}

		}
	}

	add_action('tourmaster_after_save_plugin_option', 'tourmaster_room_set_block_date');
	if( !function_exists('tourmaster_room_set_block_date') ){
		function tourmaster_room_set_block_date(){

			unset($GLOBALS['tourmaster_room_general']);

			$block_date = tourmaster_get_option('room_general', 'block-date', '');
			$old_block_date = get_option('tourmaster-room-block-date', '');
			if( $block_date = $old_block_date ){
				return;
			}else{
				update_option('tourmaster-room-block-date', $block_date);
			}

			global $wpdb;

			$sql  = "SELECT post_id, meta_value FROM {$wpdb->postmeta} ";
		    $sql .= "WHERE meta_key = 'tourmaster-room-date-avail' ";
		    $results = $wpdb->get_results($sql);

			if( !empty($results) ){
		    	foreach( $results as $result ){
					$date_avail = explode(',', $result->meta_value);
					
					tourmaster_room_calculate_date_display($result->post_id, array(
						'date-avail' => $date_avail
					));
				}
			}

		}
	}

	// cancel booking
	add_action('tourmaster_schedule_daily', 'tourmaster_room_cancel_booking');
	if( !function_exists('tourmaster_room_cancel_booking') ){
		function tourmaster_room_cancel_booking(){

			$day_num = tourmaster_get_option('room_general', 'cancel-booking-day', '');
			if( empty($day_num) ){ return; }

			global $wpdb;

			$current_date = current_time('mysql');
			$cancel_date = date('Y-m-d H:i:s', (strtotime($current_date) - (intval($day_num) * 86400)));

			$sql  = "SELECT id FROM {$wpdb->prefix}tourmaster_room_order ";
			$sql .= "WHERE booking_date <= '{$cancel_date}' ";
			$sql .= "AND order_status IN ('pending','rejected')";
			$results = $wpdb->get_results($sql);
				
				if( !empty($results) ){

					// update status
					$sql  = "UPDATE {$wpdb->prefix}tourmaster_room_order ";
					$sql .= "SET order_status = 'cancel' ";
					$sql .= "WHERE id IN (";
					$count = 0;
					foreach( $results as $result ){ $count++;
						$sql .= ($count <= 1? '': ',') . $result->id;
					}
					$sql .= ")";
					$wpdb->query($sql);

					// send email
					$cancel_booking_email = tourmaster_get_option('room_general', 'enable-cancel-booking-mail', 'enable');
					if( $cancel_booking_email == 'enable' ){
						foreach( $results as $result ){
							tourmaster_room_mail_notification('booking-cancelled-mail', $result->id);
						}
					}
					
				}
		} // tourmaster_cancel_booking
	}