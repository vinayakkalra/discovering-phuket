<?php
/**
 * The template for displaying single room
 */

	get_header();

	while( have_posts() ){ the_post();
		
		$room_option = tourmaster_get_post_meta(get_the_ID(), 'tourmaster-room-option');

		// header
		$enable_title = empty($room_option['enable-page-title'])? tourmaster_get_option('room_general', 'enable-room-title', 'enable'): $room_option['enable-page-title'];
		if( $enable_title == 'enable' ){

			$background_radius = '';
			$top_radius = empty($room_option['title-background-top-radius'])? '': $room_option['title-background-top-radius'];
			$bottom_radius = empty($room_option['title-background-bottom-radius'])? '': $room_option['title-background-bottom-radius'];
			if( empty($top_radius) && empty($bottom_radius) ){
				$top_radius = tourmaster_get_option('room_general', 'room-title-background-top-radius', '');
				$bottom_radius = tourmaster_get_option('room_general', 'room-title-background-bottom-radius', '');
			}
			if( !empty($top_radius) || !empty($bottom_radius) ){
				$background_radius  = empty($top_radius)? '0 0 ': "{$top_radius} {$top_radius} ";
				$background_radius .= empty($bottom_radius)? '0 0': "{$bottom_radius} {$bottom_radius} ";
			}

			echo '<div class="tourmaster-room-single-header-title-wrap" ' . tourmaster_esc_style(array(
				'background-image' => empty($room_option['header-image'])? '': $room_option['header-image'],
				'border-radius' => $background_radius
			)) . ' >';
			echo '<div class="tourmaster-room-single-header-background-overlay" ' . tourmaster_esc_style(array(
				'opacity' => empty($room_option['header-background-overlay-opacity'])? '': $room_option['header-background-overlay-opacity']
			)) . '></div>';
			echo '<div class="tourmaster-container" >';
			echo '<h1 class="tourmaster-item-pdlr" >' . get_the_title() . '</h1>';
			echo '</div>';
			echo '</div>';
		}

		// editor content
		if( empty($room_option['show-wordpress-editor-content']) || $room_option['show-wordpress-editor-content'] == 'enable' ){
			ob_start();
			the_content();
			$content = ob_get_contents();
			ob_end_clean();

			if( !empty($content) ){
				echo '<div class="tourmaster-container" >';
				echo '<div class="tourmaster-page-content tourmaster-item-pdlr" >';
				echo '<div class="tourmaster-single-main-content" >' . $content . '</div>'; // tourmaster-single-main-content
				echo '</div>'; // tourmaster-page-content
				echo '</div>'; // tourmaster-container
			}
		}
		
	} // while

	if( !post_password_required() ){
		do_action('gdlr_core_print_page_builder');
	}

	////////////////////////////////////////////////////////////////////
	// review section
	////////////////////////////////////////////////////////////////////
	if( empty($room_option['enable-review']) || $room_option['enable-review'] == 'enable' ){
		
		$review_num_fetch = apply_filters('tourmaster_review_num_fetch', 5);

		global $wpdb;
		$sql  = "SELECT * from {$wpdb->prefix}tourmaster_room_review ";
		$sql .= $wpdb->prepare("WHERE review_room_id = %d ", get_the_ID());
		$sql .= "ORDER BY review_date DESC ";
		$sql .= tourmaster_get_sql_page_part(1, $review_num_fetch);
		$results = $wpdb->get_results($sql);
		
		if( !empty($results) ){
			$room_style = new tourmaster_room_style();

			$sql  = "SELECT COUNT(*) from {$wpdb->prefix}tourmaster_room_review ";
			$sql .= $wpdb->prepare("WHERE review_room_id = %d ", get_the_ID());
			$max_num_page = intval($wpdb->get_var($sql)) / $review_num_fetch;

			echo '<div class="tourmaster-single-review-container tourmaster-container" >';
			echo '<div class="tourmaster-single-review-item tourmaster-item-pdlr" >';
			echo '<div class="tourmaster-single-review" id="tourmaster-room-single-review" >';

			echo '<div class="tourmaster-single-review-head clearfix" >';
			echo '<div class="tourmaster-single-review-head-info" >';
			echo $room_style->get_rating(array());

			echo '<div class="tourmaster-single-review-filter" id="tourmaster-single-review-filter" >';
			echo '<div class="tourmaster-single-review-sort-by" >';
			echo '<span class="tourmaster-head" >' . esc_html__('Sort By:', 'tourmaster') . '</span>';
			echo '<span class="tourmaster-sort-by-field" data-sort-by="rating" >' . esc_html__('Rating', 'tourmaster') . '</span>';
			echo '<span class="tourmaster-sort-by-field tourmaster-active" data-sort-by="date" >' . esc_html__('Date', 'tourmaster') . '</span>';
			echo '</div>'; // tourmaster-single-review-sort-by
			echo '</div>'; // tourmaster-single-review-filter
			echo '</div>'; // tourmaster-single-review-head-info
			echo '</div>'; // tourmaster-single-review-head

			echo '<div class="tourmaster-single-review-content" id="tourmaster-single-review-content" ';
			echo 'data-room-id="' . esc_attr(get_the_ID()) . '" ';
			echo 'data-ajax-url="' . esc_attr(TOURMASTER_AJAX_URL) . '" >';
			echo tourmaster_room_get_review_content_list($results);

			echo tourmaster_room_get_review_content_pagination($max_num_page);
			echo '</div>'; // tourmaster-single-review-content
			echo '</div>'; // tourmaster-single-review
			echo '</div>'; // tourmaster-single-review-item
			echo '</div>'; // tourmaster-single-review-container
		} 
	} 

	get_footer(); 
?>