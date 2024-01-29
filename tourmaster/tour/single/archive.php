<?php
get_header();

	$search_sidebar = tourmaster_get_option('general', 'archive-search-sidebar', 'disable');
	$search_style = tourmaster_get_option('general', 'search-page-style', 'style-1');
	
	$shadow_size = tourmaster_get_option('general', 'search-page-tour-frame-shadow-size', '');
	$shadow_size_x = tourmaster_get_option('general', 'search-page-tour-frame-shadow-x', 0);
	$shadow_size_x = empty($shadow_size_x)? 0: $shadow_size_x;
	$shadow_size_y = tourmaster_get_option('general', 'search-page-tour-frame-shadow-y', 0);
	$shadow_size_y = empty($shadow_size_y)? 0: $shadow_size_y;

	$settings = array(
		'pagination' => 'page',
		'tour-style' => tourmaster_get_option('general', 'search-page-tour-style', 'full'),
		'grid-style' => tourmaster_get_option('general', 'search-page-tour-grid-style', 'style-1'),
		'column-size' => tourmaster_get_option('general', 'search-page-column-size', '20'),
		'thumbnail-size' => tourmaster_get_option('general', 'search-page-thumbnail-size', 'full'),
		'tour-info' => tourmaster_get_option('general', 'search-page-tour-info', array()),
		'excerpt' => tourmaster_get_option('general', 'search-page-excerpt', 'specify-number'),
		'excerpt-number' => tourmaster_get_option('general', 'search-page-excerpt-number', '55'),
		'tour-rating' => tourmaster_get_option('general', 'search-page-tour-rating', 'enable'),
		'tour-border-radius' => tourmaster_get_option('general', 'search-page-tour-frame-border-radius', ''),
		'custom-pagination' => true,
		'frame-shadow-size' => empty($shadow_size)? '': array('x' => $shadow_size_x, 'y' => $shadow_size_y, 'size' => $shadow_size),
		'frame-shadow-color' => tourmaster_get_option('general', 'search-page-tour-frame-shadow-color', ''),
		'frame-shadow-opacity' => tourmaster_get_option('general', 'search-page-tour-frame-shadow-opacity', ''),
		'tour-title-font-size' => tourmaster_get_option('general', 'search-page-tour-title-font-size', ''),
		'tour-title-font-weight' => tourmaster_get_option('general', 'search-page-tour-title-font-weight', ''),
		'tour-title-letter-spacing' => tourmaster_get_option('general', 'search-page-tour-title-letter-spacing', ''),
		'tour-title-text-transform' => tourmaster_get_option('general', 'search-page-tour-title-text-transform', ''),
	);
	$settings['paged'] = (get_query_var('paged'))? get_query_var('paged') : get_query_var('page');
	$settings['paged'] = empty($settings['paged'])? 1: $settings['paged'];

	if( $settings['grid-style'] == 'style-2' ){
		$settings['tour-border-radius'] = '3px';
	} 

	// archive query
	global $wp_query;
	$settings['query'] = $wp_query;

	// start the content
	$search_style = tourmaster_get_option('general', 'search-page-style', 'style-1');
	echo '<div class="tourmaster-template-wrapper tourmaster-search-' . esc_attr($search_style) . '" >';
	echo '<div class="tourmaster-container" >';
	
	// sidebar content
	$sidebar_type = tourmaster_get_option('general', 'search-sidebar', 'none');
	echo '<div class="' . tourmaster_get_sidebar_wrap_class($sidebar_type) . '" >';
	echo '<div class="' . tourmaster_get_sidebar_class(array('sidebar-type'=>$sidebar_type, 'section'=>'center')) . '" >';
	echo '<div class="tourmaster-page-content" >';
	
	$term_description = term_description();
	$archive_description = tourmaster_get_option('general', 'archive-description', 'enable');
	if( $archive_description == 'enable' && !empty($term_description) ){
		echo '<div class="tourmaster-taxonomy-description tourmaster-item-pdlr" >' . tourmaster_text_filter($term_description) . '</div>';
	}

	echo tourmaster_pb_element_tour::get_content($settings);

	echo '</div>'; // tourmaster-page-content
	echo '</div>'; // tourmaster-get-sidebar-class

	// sidebar left
	if( $sidebar_type == 'left' || $sidebar_type == 'both' ){
		$search_content = '';
		if( $search_sidebar != 'disable' ){
			$search_settings = array(
				'fields' => tourmaster_get_option('general', 'tour-search-fields', ''),
				'enable-rating-field' => tourmaster_get_option('general', 'tour-search-rating-field', ''),
				'filters' => tourmaster_get_option('general', 'tour-search-filters', ''),
				'filter-state' => tourmaster_get_option('general', 'tour-search-filter-state', 'disable'),
				'style' => 'full',
				'item-style' => $search_style,
				'with-frame' => 'enable',
				'no-pdlr' => true
			);
			$search_content .= '<div class="tourmaster-tour-search-item-wrap" >';
			$search_content .= tourmaster_pb_element_tour_search::get_content($search_settings);
			$search_content .= '</div>';
		}

		$sidebar_left = tourmaster_get_option('general', 'search-sidebar-left');
		echo tourmaster_get_sidebar($sidebar_type, 'left', $sidebar_left, $search_content);
	}

	// sidebar right
	if( $sidebar_type == 'right' || $sidebar_type == 'both' ){
		$search_content = '';
		if( $search_sidebar != 'disable' ){
			$search_settings = array(
				'fields' => tourmaster_get_option('general', 'tour-search-fields', ''),
				'enable-rating-field' => tourmaster_get_option('general', 'tour-search-rating-field', ''),
				'filters' => tourmaster_get_option('general', 'tour-search-filters', ''),
				'filter-state' => tourmaster_get_option('general', 'tour-search-filter-state', 'disable'),
				'style' => 'full',
				'item-style' => $search_style,
				'with-frame' => 'enable',
				'no-pdlr' => true
			);
			$search_content .= '<div class="tourmaster-tour-search-item-wrap" >';
			$search_content .= tourmaster_pb_element_tour_search::get_content($search_settings);
			$search_content .= '</div>';
		}

		$sidebar_right = tourmaster_get_option('general', 'search-sidebar-right');
		echo tourmaster_get_sidebar($sidebar_type, 'right', $sidebar_right, $search_content);
	}

	echo '</div>'; // tourmaster-get-sidebar-wrap-class	

	echo '</div>'; // tourmaster-container
	echo '</div>'; // tourmaster-template-wrapper

get_footer(); 

?>