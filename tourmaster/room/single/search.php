<?php
	get_header();

	//$shadow_size = tourmaster_get_option('room_general', 'search-page-tour-frame-shadow-size', '');
	$settings = array(
		'pagination' => 'page',
		'room-style' => tourmaster_get_option('room_general', 'search-room-style', 'side-thumbnail'),
		'with-frame' => tourmaster_get_option('room_general', 'search-room-with-frame', 'disable'),
		'column-size' => tourmaster_get_option('room_general', 'search-room-column-size', '30'),
		'thumbnail-size' => tourmaster_get_option('room_general', 'search-room-thumbnail-size', 'full'),
		'display-price' => tourmaster_get_option('room_general', 'search-room-display-price', 'enable'),
		'enable-price-prefix' => tourmaster_get_option('room_general', 'search-room-enable-price-prefix', 'enable'),
		'enable-price-suffix' => tourmaster_get_option('room_general', 'search-room-enable-price-suffix', 'enable'),
		'price-decimal-digit' => tourmaster_get_option('room_general', 'search-room-price-decimal-digit', 0),
		'display-ribbon' => tourmaster_get_option('room_general', 'search-room-display-ribbon', 'enable'),
		'room-info' => tourmaster_get_option('room_general', 'search-room-info', array()),
		'excerpt' => tourmaster_get_option('room_general', 'search-room-excerpt', 'specify-number'),
		'excerpt-number' => tourmaster_get_option('room_general', 'search-room-excerpt-number', '55'),
		'enable-rating' => tourmaster_get_option('room_general', 'search-room-enable-rating', 'enable'),
		'custom-pagination' => true,

		'room-title-font-size' => tourmaster_get_option('room_general', 'search-room-title-font-size', ''),
		'room-title-font-weight' => tourmaster_get_option('room_general', 'search-room-title-font-weight', ''),
		'room-title-letter-spacing' => tourmaster_get_option('room_general', 'search-room-title-letter-spacing', ''),
		'room-title-text-transform' => tourmaster_get_option('room_general', 'search-room-title-text-transform', ''),
		// 'frame-shadow-size' => empty($shadow_size)? '': array('x' => 0, 'y' => 0, 'size' => $shadow_size),
		// 'frame-shadow-color' => tourmaster_get_option('room_general', 'search-page-tour-frame-shadow-color', ''),
		// 'frame-shadow-opacity' => tourmaster_get_option('room_general', 'search-page-tour-frame-shadow-opacity', ''),
		'num-fetch' => tourmaster_get_option('room_general', 'search-room-num-fetch', '10'),
	);
	$settings['paged'] = (get_query_var('paged'))? get_query_var('paged') : get_query_var('page');
	$settings['paged'] = empty($settings['paged'])? 1: $settings['paged'];

	// search query
	$args = array(
		'post_status' => 'publish',
		'post_type' => 'room',
		'posts_per_page' => $settings['num-fetch'],
		'paged' => $settings['paged'],
	);

	// category
	$args['tax_query'] = array(
		'relation' => 'AND'
	);

	// taxonomy
	$tax_fields = array(
		'room_category' => esc_html__('Category', 'tourmaster'),
		'room_tag' => esc_html__('Tag', 'tourmaster')
	);
	$tax_fields = $tax_fields + tourmaster_get_custom_tax_list('room');
	foreach( $tax_fields as $tax_slug => $tax_name ){
		if( !empty($_GET['tax-' . $tax_slug]) ){
			$args['tax_query'][] = array(
				array('terms'=>$_GET['tax-' . $tax_slug], 'taxonomy'=>$tax_slug, 'field'=>'slug')
			);
		}
	}

	$meta_query = array(
		'relation' => 'AND'
	);

	// guest amount
	if( !empty($_GET['adult']) || !empty($_GET['children']) ){
		$guest_amount = intval($_GET['adult']) + intval($_GET['children']);
	
		if( !empty($guest_amount) ){

			// max guest
			$meta_query[] = array( 'relation' => 'OR',
				array(
					'key'     => 'tourmaster-room-max-guest',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => 'tourmaster-room-max-guest',
					'value'   => $guest_amount,
					'compare' => '>=',
					'type'    => 'NUMERIC'
				)
			);

			// min guest
			$meta_query[] = array( 'relation' => 'OR',
				array(
					'key'     => 'tourmaster-room-min-guest',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => 'tourmaster-room-min-guest',
					'value'   => $guest_amount,
					'compare' => '<=',
					'type'    => 'NUMERIC'
				)
			);
		}
	}

	// date
	if( !empty($_GET['start_date']) && !empty($_GET['end_date']) ){
		$date_list = tourmaster_split_date($_GET['start_date'], $_GET['end_date']);

		foreach( $date_list as $date ){
			$meta_query[] = array(
				'key'     => 'tourmaster-room-date-display',
				'value'   => $date,
				'compare' => 'LIKE',
			);
		}
	}

	if( !empty($meta_query) ){
		$args['meta_query'] = $meta_query;
	}

	$settings['query'] = new WP_Query($args);

	// start the content
	echo '<div class="tourmaster-template-wrapper" >';
	echo '<div class="tourmaster-container" >';

	// sidebar content
	$sidebar_type = 'none';
	echo '<div class="' . tourmaster_get_sidebar_wrap_class($sidebar_type) . '" >';
	echo '<div class="' . tourmaster_get_sidebar_class(array('sidebar-type'=>$sidebar_type, 'section'=>'center')) . '" >';
	
	
	echo '<div class="tourmaster-page-content" >';
	
	// search filter
	$search_settings = array(
		'style' => 'box',
		'align' => 'vertical',
		'form-radius' => 'round',
		'button-style' => 'border',		
		'search-filters' => tourmaster_get_option('room_general', 'search-filters', array()),
		'search-filter-content' => tourmaster_get_option('room_general', 'search-filters-content', 'enable'),
	);
	echo '<div class="tourmaster-room-search-item-wrap tourmaster-column-15" >';
	echo '<h3 class="tourmaster-item-pdlr" >' . esc_html__('Check Availability', 'tourmaster') . '</h3>';
	echo tourmaster_pb_element_room_search::get_content($search_settings);
	echo '</div>';

	// content
	if( $settings['query']->have_posts() ){	

		/*
		$settings['enable-order-filterer'] = 'enable'; 
		$settings['order-filterer-grid-style'] = tourmaster_get_option('general', 'tour-search-order-filterer-grid-style', ''); 
		$settings['order-filterer-grid-style-thumbnail'] = tourmaster_get_option('general', 'tour-search-order-filterer-grid-style-thumbnail', ''); 
		$settings['order-filterer-grid-style-column'] = $settings['column-size']; 
		
		$settings['order-filterer-list-style'] = tourmaster_get_option('general', 'tour-search-item-style', '');
		$settings['order-filterer-list-style-thumbnail'] = tourmaster_get_option('general', 'tour-search-item-thumbnail', '');

		$default_style = tourmaster_get_option('general', 'tour-search-default-style', 'list');
		if( $default_style == 'list' ){
			$settings['tour-style'] = $settings['order-filterer-list-style'];
			$settings['thumbnail-size'] = $settings['order-filterer-list-style-thumbnail'];
		}else if( $default_style == 'grid' ){
			$settings['tour-style'] = $settings['order-filterer-grid-style'];
			$settings['thumbnail-size'] =$settings['order-filterer-grid-style-thumbnail'];
		}

		$settings['s'] = empty($args['s'])? '': $args['s'];
		$settings['tax_query'] = $args['tax_query'];
		$settings['meta_query'] = $meta_query;
		*/

		echo '<div class="tourmaster-tour-search-content-wrap tourmaster-column-45" >';
		echo tourmaster_pb_element_room::get_content($settings);
		echo '</div>';
	}else{
		echo '<div class="tourmaster-single-search-not-found-wrap tourmaster-column-45 tourmaster-item-pdlr" >';
		echo '<div class="tourmaster-single-search-not-found-inner" >';
		echo '<div class="tourmaster-single-search-not-found" >';
		echo '<h3 class="tourmaster-single-search-not-found-title" >' . esc_html__('Not Found', 'tourmaster') . '</h3>';
		echo '<div class="tourmaster-single-search-not-found-caption" >' . esc_html__('Nothing matched your search criteria. Please try again with different keywords', 'tourmaster') . '</div>';
		echo '</div>'; // tourmaster-single-search-not-found
		echo '</div>'; // tourmaster-single-search-not-found-inner
		echo '</div>'; // tourmaster-single-search-not-found-wrap
	}

	echo '</div>'; // tourmaster-page-content
	
	echo '</div>'; // tourmaster-get-sidebar-class
	echo '</div>'; // tourmaster-get-sidebar-wrap-class	
	
	echo '</div>'; // tourmaster-container
	echo '</div>'; // tourmaster-template-wrapper

get_footer(); 

?>