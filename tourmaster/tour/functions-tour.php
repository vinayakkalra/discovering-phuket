<?php

	include_once(TOURMASTER_LOCAL . '/tour/include/plugin-option.php');
    include_once(TOURMASTER_LOCAL . '/tour/include/review-util.php');
	include_once(TOURMASTER_LOCAL . '/tour/include/user-page.php');

    include_once(TOURMASTER_LOCAL . '/tour/include/tour-option.php');
    include_once(TOURMASTER_LOCAL . '/tour/include/tour-filter.php');
    include_once(TOURMASTER_LOCAL . '/tour/include/tour-coupon.php');
    include_once(TOURMASTER_LOCAL . '/tour/include/tour-service.php');
    include_once(TOURMASTER_LOCAL . '/tour/include/tour-util.php');

	include_once(TOURMASTER_LOCAL . '/tour/include/order.php');
	include_once(TOURMASTER_LOCAL . '/tour/include/order-util.php');

	include_once(TOURMASTER_LOCAL . '/tour/include/pb/tour-style.php');
	include_once(TOURMASTER_LOCAL . '/tour/include/pb/tour-item.php');
	include_once(TOURMASTER_LOCAL . '/tour/include/pb/pb-element-content-navigation.php');
	include_once(TOURMASTER_LOCAL . '/tour/include/pb/pb-element-tour.php');
	include_once(TOURMASTER_LOCAL . '/tour/include/pb/pb-element-tour-title.php');
	include_once(TOURMASTER_LOCAL . '/tour/include/pb/pb-element-tour-review.php');
	include_once(TOURMASTER_LOCAL . '/tour/include/pb/pb-element-tour-search.php');
	include_once(TOURMASTER_LOCAL . '/tour/include/pb/pb-element-tour-category.php');

	include_once(TOURMASTER_LOCAL . '/tour/include/widget/tour-widget.php');
	include_once(TOURMASTER_LOCAL . '/tour/include/widget/tour-category-widget.php');
	include_once(TOURMASTER_LOCAL . '/tour/include/widget/tour-search-widget.php');

	// enqueue necessay style/script
	if( !is_admin() ){ 
		add_action('wp_enqueue_scripts', 'tourmaster_tour_enqueue_script', 11); 
	}else{
		add_action('gdlr_core_front_script', 'tourmaster_tour_enqueue_script', 11);
	}
	if( !function_exists('tourmaster_tour_enqueue_script') ){
		function tourmaster_tour_enqueue_script(){
			wp_enqueue_style('tourmaster-custom-style', tourmaster_get_style_custom());

			wp_enqueue_script('tourmaster-tour-script', TOURMASTER_URL . '/tour/tourmaster-tour.js', array('jquery'), false, true);
		}
	}

	// archive template
	add_filter('template_include', 'tourmaster_tour_archive_template_registration', 9998);
	if( !function_exists('tourmaster_tour_archive_template_registration') ){
		function tourmaster_tour_archive_template_registration( $template ){
			global $tourmaster_template;

			// archive template
			if( is_tax('tour_category') || is_tax('tour_tag') || tourmaster_is_custom_tour_tax() ){
				$tourmaster_template = 'archive';
				$template = TOURMASTER_LOCAL . '/tour/single/archive.php';
			}

			return $template;
		} // tourmaster_template_registration
	} // function_exists

	// add tourmaster to body class
	add_filter('body_class', 'tourmaster_tour_body_class');
	if( !function_exists('tourmaster_tour_body_class') ){
		function tourmaster_tour_body_class( $classes ){

			if( is_single() && get_post_type() == 'tour' ){
				$booking_bar_position = tourmaster_get_option('general', 'mobile-booking-bar-position', 'bottom');
				if( $booking_bar_position == 'bottom' ){
					$classes[] = 'tourmaster-bottom-booking-bar';
				}

				$mobile_read_more = tourmaster_get_option('general', 'mobile-content-read-more', 'enable');
				if( $mobile_read_more == 'enable' ){
					$classes[] = 'tourmaster-mobile-read-more';
				}
			}

			return $classes;
		}
	}

    // add_action('init', 'tourmaster_list_category_thumbnail');
	if( !function_exists('tourmaster_list_category_thumbnail') ){
		function tourmaster_list_category_thumbnail(){
			$categories = get_categories(array(
				'taxonomy'=>'tour_category', 
				'hide_empty'=>0,
				'number'=>999
			));

			$tour_tax = array();
			foreach( $categories as $category ){
				$term_meta = get_term_meta($category->term_id, 'thumbnail', true);
				$tour_tax[$category->slug] = $term_meta;
			}

			print_r(json_encode($tour_tax));

		}
	}