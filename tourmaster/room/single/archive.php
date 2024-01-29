<?php
get_header();
	
	//$shadow_size = tourmaster_get_option('room_general', 'search-page-tour-frame-shadow-size', '');
	$settings = array(
		'pagination' => 'page',
		'room-style' => tourmaster_get_option('room_general', 'archive-room-style', 'grid'),
		'with-frame' => tourmaster_get_option('room_general', 'archive-room-with-frame', 'disable'),
		'column-size' => tourmaster_get_option('room_general', 'archive-room-column-size', '20'),
		'thumbnail-size' => tourmaster_get_option('room_general', 'archive-room-thumbnail-size', 'full'),
		'display-price' => tourmaster_get_option('room_general', 'archive-room-display-price', 'enable'),
		'enable-price-prefix' => tourmaster_get_option('room_general', 'archive-room-enable-price-prefix', 'enable'),
		'enable-price-suffix' => tourmaster_get_option('room_general', 'archive-room-enable-price-suffix', 'enable'),
		'price-decimal-digit' => tourmaster_get_option('room_general', 'archive-room-price-decimal-digit', 0),
		'display-ribbon' => tourmaster_get_option('room_general', 'archive-room-display-ribbon', 'enable'),
		'room-info' => tourmaster_get_option('room_general', 'archive-room-info', array()),
		'excerpt' => tourmaster_get_option('room_general', 'archive-room-excerpt', 'specify-number'),
		'excerpt-number' => tourmaster_get_option('room_general', 'archive-room-excerpt-number', '55'),
		'enable-rating' => tourmaster_get_option('room_general', 'archive-room-enable-rating', 'enable'),
		'custom-pagination' => true,
		
		'room-title-font-size' => tourmaster_get_option('room_general', 'archive-room-title-font-size', ''),
		'room-title-font-weight' => tourmaster_get_option('room_general', 'archive-room-title-font-weight', ''),
		'room-title-letter-spacing' => tourmaster_get_option('room_general', 'archive-room-title-letter-spacing', ''),
		'room-title-text-transform' => tourmaster_get_option('room_general', 'archive-room-title-text-transform', ''),
		// 'frame-shadow-size' => empty($shadow_size)? '': array('x' => 0, 'y' => 0, 'size' => $shadow_size),
		// 'frame-shadow-color' => tourmaster_get_option('room_general', 'search-page-tour-frame-shadow-color', ''),
		// 'frame-shadow-opacity' => tourmaster_get_option('room_general', 'search-page-tour-frame-shadow-opacity', ''),
	);
	$settings['paged'] = (get_query_var('paged'))? get_query_var('paged') : get_query_var('page');
	$settings['paged'] = empty($settings['paged'])? 1: $settings['paged'];

	// archive query
	global $wp_query;
	$settings['query'] = $wp_query;

	// start the content
	echo '<div class="tourmaster-template-wrapper" >';
	echo '<div class="tourmaster-container" >';
	
	// sidebar content
	$sidebar_type = tourmaster_get_option('room_general', 'archive-sidebar', 'none');
	echo '<div class="' . tourmaster_get_sidebar_wrap_class($sidebar_type) . '" >';
	echo '<div class="' . tourmaster_get_sidebar_class(array('sidebar-type'=>$sidebar_type, 'section'=>'center')) . '" >';
	echo '<div class="tourmaster-page-content" >';
	
	$term_description = term_description();
	$archive_description = tourmaster_get_option('room_general', 'archive-description', 'enable');
	if( $archive_description == 'enable' && !empty($term_description) ){
		echo '<div class="tourmaster-taxonomy-description tourmaster-item-pdlr" >' . tourmaster_text_filter($term_description) . '</div>';
	}

	echo tourmaster_pb_element_room::get_content($settings);

	echo '</div>'; // tourmaster-page-content
	echo '</div>'; // tourmaster-get-sidebar-class

	// sidebar left
	if( $sidebar_type == 'left' || $sidebar_type == 'both' ){
		$sidebar_left = tourmaster_get_option('room_general', 'archive-sidebar-left');
		echo tourmaster_get_sidebar($sidebar_type, 'left', $sidebar_left);
	}

	// sidebar right
	if( $sidebar_type == 'right' || $sidebar_type == 'both' ){
		$sidebar_right = tourmaster_get_option('room_general', 'archive-sidebar-right');
		echo tourmaster_get_sidebar($sidebar_type, 'right', $sidebar_right);
	}

	echo '</div>'; // tourmaster-get-sidebar-wrap-class	

	echo '</div>'; // tourmaster-container
	echo '</div>'; // tourmaster-template-wrapper

get_footer(); 

?>