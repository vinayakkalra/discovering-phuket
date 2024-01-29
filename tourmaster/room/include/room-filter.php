<?php
	/*	
	*	Tourmaster Plugin
	*	---------------------------------------------------------------------
	*	for tour post type
	*	---------------------------------------------------------------------
	*/

	add_action('init', 'tourmaster_add_custom_room_tax', 99);
	if( !function_exists('tourmaster_add_custom_room_tax') ){
		function tourmaster_add_custom_room_tax(){
			$custom_taxs = get_option('tourmaster_custom_room_taxs', array());

			foreach( $custom_taxs as $custom_tax_slug => $custom_tax ){

				$args = array(
					'show_in_rest' 		=> true,
					'hierarchical'      => $custom_tax['hierarchical'],
					'label'             => $custom_tax['label'],
					'show_ui'           => true,
					'show_admin_column' => true,
					'query_var'         => true,
					'capabilities'		=> array(
						'manage_terms' => 'manage_room_category', 
						'edit_terms'   => 'manage_room_category', 
						'delete_terms' => 'manage_room_category', 
						'assign_terms' => 'manage_room_category'
					)
				);
				register_taxonomy($custom_tax_slug, array('room'), $args);
				register_taxonomy_for_object_type($custom_tax_slug, 'room');

			}
		}
	}

	add_action('admin_menu', 'tourmaster_init_room_filter_page', 99);
	if( !function_exists('tourmaster_init_room_filter_page') ){
		function tourmaster_init_room_filter_page(){
			add_submenu_page(
				'edit.php?post_type=room', 
				esc_html__('Add New Filter', 'tourmaster'), 
				esc_html__('Add New Filter', 'tourmaster'),
				'manage_room_filter', 
				'tourmaster_add_room_filter_page', 
				'tourmaster_create_add_room_filter_page'
			);
		}
	}

	
	if( !function_exists('tourmaster_create_add_room_filter_page') ){
		function tourmaster_create_add_room_filter_page(){
			tourmaster_create_add_filter_page('room');
		}
	}

	if( !function_exists('tourmaster_is_custom_room_tax') ){
		function tourmaster_is_custom_room_tax(){

			$taxs = tourmaster_get_custom_tax_list('room');
			foreach( $taxs as $tax_slug => $tax_label ){
				if( is_tax($tax_slug) ){
					return true;
				}
			}

			return false;
		}
	}