<?php
	/*	
	*	Tourmaster Plugin
	*	---------------------------------------------------------------------
	*	for tour post type
	*	---------------------------------------------------------------------
	*/

	add_action('init', 'tourmaster_add_custom_tour_tax', 99);
	if( !function_exists('tourmaster_add_custom_tour_tax') ){
		function tourmaster_add_custom_tour_tax(){
			$custom_taxs = get_option('tourmaster_custom_tour_taxs', array());

			foreach( $custom_taxs as $custom_tax_slug => $custom_tax ){

				$args = array(
					'show_in_rest' 		=> true,
					'hierarchical'      => $custom_tax['hierarchical'],
					'label'             => $custom_tax['label'],
					'show_ui'           => true,
					'show_admin_column' => true,
					'query_var'         => true,
					'capabilities'		=> array(
						'manage_terms' => 'manage_tour_category', 
						'edit_terms'   => 'manage_tour_category', 
						'delete_terms' => 'manage_tour_category', 
						'assign_terms' => 'manage_tour_category'
					)
				);
				register_taxonomy($custom_tax_slug, array('tour'), $args);
				register_taxonomy_for_object_type($custom_tax_slug, 'tour');

				new tourmaster_taxonomy_option(array(
					'taxonomy' => $custom_tax_slug,
					'options' => array(
						'thumbnail' => array(
							'title' => esc_html__('Thumbnail', 'tourmaster'),
							'type' => 'upload'
						),
						'archive-title-background' => array(
							'title' => esc_html__('Archive Title Background ( For Traveltour Theme )', 'tourmaster'),
							'type' => 'upload'
						)
					)
				));

			}
		}
	}

	add_action('admin_menu', 'tourmaster_init_tour_filter_page', 99);
	if( !function_exists('tourmaster_init_tour_filter_page') ){
		function tourmaster_init_tour_filter_page(){
			add_submenu_page(
				'edit.php?post_type=tour', 
				esc_html__('Add New Filter', 'tourmaster'), 
				esc_html__('Add New Filter', 'tourmaster'),
				'manage_tour_filter', 
				'tourmaster_add_tour_filter_page', 
				'tourmaster_create_add_tour_filter_page',
				2
			);
		}
	}

	if( !function_exists('tourmaster_create_add_tour_filter_page') ){
		function tourmaster_create_add_tour_filter_page(){
			tourmaster_create_add_filter_page();
		}
	}

	if( !function_exists('tourmaster_is_custom_tour_tax') ){
		function tourmaster_is_custom_tour_tax(){

			$taxs = tourmaster_get_custom_tax_list();
			foreach( $taxs as $tax_slug => $tax_label ){
				if( is_tax($tax_slug) ){
					return true;
				}
			}

			return false;
		}
	}

	// get tour search fields
	if( !function_exists('tourmaster_get_tour_search_fields') ){
		function tourmaster_get_tour_search_fields( $type = 'all'){

			if( $type == 'all' ){
				$ret = array(
					'keywords' => esc_html__('Keywords', 'tourmaster'),
					'tour_category' => esc_html__('Category', 'tourmaster'),
					'tour_tag' => esc_html__('Tag', 'tourmaster'),
				) + tourmaster_get_custom_tax_list() + array(
					'duration' => esc_html__('Duration', 'tourmaster'),
					'date' => esc_html__('Date', 'tourmaster'),
					'month' => esc_html__('Month', 'tourmaster'),
					// 'start_date' => esc_html__('Start Date', 'tourmaster'),
					// 'end_date' => esc_html__('End Date', 'tourmaster'),
					'min-price' => esc_html__('Min Price', 'tourmaster'),
					'max-price' => esc_html__('Max Price', 'tourmaster'),
				);			
			}else if( $type == 'default' ){
				$ret = array(
					'keywords' => esc_html__('Keywords', 'tourmaster'),
					'tour_category' => esc_html__('Category', 'tourmaster'),
					'tour_tag' => esc_html__('Tag', 'tourmaster'),
					'duration' => esc_html__('Duration', 'tourmaster'),
					'date' => esc_html__('Date', 'tourmaster'),
					'month' => esc_html__('Month', 'tourmaster'),
					'min-price' => esc_html__('Min Price', 'tourmaster'),
					'max-price' => esc_html__('Max Price', 'tourmaster'),
				);	
			}else if( $type == 'custom' ){
				$ret = tourmaster_get_custom_tax_list();
			}


			return $ret;
		}
	}