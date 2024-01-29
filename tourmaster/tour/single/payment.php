<?php

$skip_page = false;
if( !empty($_GET['tid']) ){
	if( !empty($_GET['step']) && $_GET['step'] == '4' && !empty($_GET['payment_method']) && $_GET['payment_method'] == 'paypal' ){
		$paypal_complete = true;
	}else{
		$skip_page = true;
		include 'payment-online.php';
	}
	
}

if( !$skip_page ){
	header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

		/**
		 * The template for displaying single tour posttype
		 */

	if( !empty($_POST) ){
		$booking_cookie = json_encode($_POST);
		setcookie('tourmaster-booking-detail', $booking_cookie, 0, '/', COOKIE_DOMAIN, is_ssl(), false);
		wp_redirect(add_query_arg(array()));
	}

	get_header();

	$payment_style = tourmaster_get_option('general', 'payment-page-style', 'style-1');

	echo '<div class="tourmaster-page-wrapper tourmaster-payment-' . esc_attr($payment_style) . '" id="tourmaster-page-wrapper" >';

	if( empty($booking_detail) ){
		if( !empty($_COOKIE['tourmaster-booking-detail']) ){
			$booking_detail = json_decode(wp_unslash($_COOKIE['tourmaster-booking-detail']), true);
			$booking_detail = stripslashes_deep($booking_detail); 
		}else{
			$booking_detail = array();
		}
	}
	if( !empty($paypal_complete) ){
		$result = tourmaster_get_booking_data(array('id' => $_GET['tid']), array('single' => true));
		$booking_detail = json_decode($result->booking_detail, true);
		
		$booking_step = 4;
		$booking_detail['step'] = 4;
	}else{
		$booking_step = empty($booking_detail['step'])? 2: intval($booking_detail['step']);
	}

	if( !empty($booking_detail['tour-id']) && !empty($booking_detail['tour-date']) ){
		$tour_option = tourmaster_get_post_meta($booking_detail['tour-id'], 'tourmaster-tour-option');
		$date_price = tourmaster_get_tour_date_price($tour_option, $booking_detail['tour-id'], $booking_detail['tour-date']);
		$date_price = tourmaster_get_tour_date_price_package($date_price, $booking_detail);
	}else{
		$tour_option = '';
		$date_price = '';
	}
	
	// payment head
	if( !empty($booking_detail['tour-id']) ){

		if( $payment_style == 'style-1' ){
			$feature_image = get_post_thumbnail_id($booking_detail['tour-id']);
			echo '<div class="tourmaster-payment-head ' . (empty($feature_image)? 'tourmaster-wihtout-background': 'tourmaster-with-background') . '" ';
			if( !empty($feature_image) ){
				echo tourmaster_esc_style(array('background-image'=>$feature_image));
			}
			echo ' >';
			echo '<div class="traveltour-header-transparent-substitute" ></div>';
			echo '<div class="tourmaster-payment-head-overlay-opacity" ></div>';
			echo '<div class="tourmaster-payment-head-overlay" ></div>';
			echo '<div class="tourmaster-payment-head-top-overlay" ></div>';
			echo '<div class="tourmaster-payment-title-container tourmaster-container" >';
			echo '<h1 class="tourmaster-payment-title tourmaster-item-pdlr">' . get_the_title($booking_detail['tour-id']) . '</h1>';
			echo '</div>'; // tourmaster-payment-title-container
		}

		$step_count = 1;
		$payment_steps = array(
			esc_html__('Select Tour', 'tourmaster'),
			esc_html__('Contact Details', 'tourmaster'),
			esc_html__('Payment', 'tourmaster'),
			esc_html__('Complete', 'tourmaster'),
		);
		echo '<div class="tourmaster-payment-step-wrap" id="tourmaster-payment-step-wrap" >';
		echo '<div class="tourmaster-payment-step-overlay" ></div>';
		echo '<div class="tourmaster-payment-step-container tourmaster-container" >';
		echo '<div class="tourmaster-payment-step-inner tourmaster-item-mglr clearfix" >';
		foreach( $payment_steps as $payment_step ){
			echo '<div class="tourmaster-payment-step-item ';
			if( $step_count == 1 ){
				echo 'tourmaster-checked ';
			}else if( $booking_step == $step_count ){
				echo 'tourmaster-current ';
			}else if( $booking_step > $step_count ){
				echo 'tourmaster-enable ';
			}
			echo '" data-step="' . esc_attr($step_count) . '" >';
			echo '<span class="tourmaster-payment-step-item-icon" >';
			echo '<i class="fa fa-check" ></i>';
			echo '<span class="tourmaster-text" >' . $step_count . '</span>';
			echo '</span>';
			echo '<span class="tourmaster-payment-step-item-title" >' . $payment_step . ($payment_style == 'style-2'? '<span></span>': '') . '</span>'; 
			echo '</div>';

			$step_count++;
		}
		echo '</div>'; // tourmaster-payment-step-inner
		echo '</div>'; // tourmaster-payment-step-container
		echo '</div>'; // tourmaster-payment-step-wrap
		if( $payment_style == 'style-1' ){
			echo '</div>'; // tourmaster-payment-head
		}
	}else{
		echo '<div class="traveltour-header-transparent-substitute" ></div>';
	}

	echo '<div class="tourmaster-template-wrapper" id="tourmaster-payment-template-wrapper" ';
	echo 'data-ajax-url="' . esc_url(TOURMASTER_AJAX_URL) . '" ';
	echo 'data-booking-detail="' . esc_attr(json_encode($booking_detail)) . '" >';
	echo '<div class="tourmaster-container" >';
	echo '<div class="tourmaster-page-content tourmaster-item-pdlr clearfix" >';

	$content = tourmaster_get_payment_page($booking_detail, true);

	/* tourmaster booking bar */
	echo '<div class="tourmaster-tour-booking-bar-wrap" id="tourmaster-tour-booking-bar-wrap" >';
	echo '<div class="tourmaster-tour-booking-bar-outer" >';
	echo '<div class="tourmaster-tour-booking-bar-inner" id="tourmaster-tour-booking-bar-inner" >';
	echo $content['sidebar'];
	echo '</div>'; // tourmaster-tour-booking-bar-inner
	echo '</div>'; // tourmaster-tour-booking-bar-outer

	// sidebar widget
	$sidebar_name = tourmaster_get_option('general', 'payment-page-sidebar', 'none');
	if( $sidebar_name != 'none' && is_active_sidebar($sidebar_name) ){
		$sidebar_class = apply_filters('gdlr_core_sidebar_class', '');

		echo '<div class="tourmaster-tour-booking-bar-widget ' . esc_attr($sidebar_class) . '" >';
		dynamic_sidebar($sidebar_name); 
		echo '</div>';
	}
	echo '</div>'; // tourmaster-tour-booking-bar-wrap

	echo '<div class="tourmaster-tour-payment-content" id="tourmaster-tour-payment-content" >';
	echo $content['content'];
	echo '</div>'; // tourmaster-tour-payment-content

	echo '</div>'; // tourmaster-page-content
	echo '</div>'; // tourmaster-container
	echo '</div>'; // tourmaster-template-wrapper	

	echo '</div>'; // tourmaster-page-wrapper
	get_footer(); 

	do_action('include_goodlayers_payment_script');
}

?>