<?php

	if( !empty($_GET['tid']) ){
		$result = tourmaster_get_booking_data(array(
			'id' => $_GET['tid'],
			'user_id' => get_current_user_id(),
			'order_status' => array(
				'condition' => '!=',
				'value' => 'cancel'
			)
		), array('single' => true));

		if( !empty($result) ){
			$booking_detail = json_decode($result->booking_detail, true);
			$booking_detail['tid'] = $_GET['tid'];

			$booking_step = 3;
			$booking_detail['step'] = 3;
			
			$pricing_info = json_decode($result->pricing_info, true);
			$tour_option = tourmaster_get_post_meta($booking_detail['tour-id'], 'tourmaster-tour-option');
			$date_price = tourmaster_get_tour_date_price($tour_option, $booking_detail['tour-id'], $booking_detail['tour-date']);
			$date_price = tourmaster_get_tour_date_price_package($date_price, $booking_detail);

			// reset credit card service fee
			unset($booking_detail['payment_method']);
			
			$payment_infos = json_decode($result->payment_info, true);
			$payment_infos = tourmaster_payment_info_format($payment_infos, $result->order_status);
			$price_settings = tourmaster_get_price_settings($booking_detail['tour-id'], $payment_infos, $pricing_info['total-price'], $booking_detail['tour-date']);
            
			$pricing_info['paid-amount'] = $price_settings['paid-amount'];
			$pricing_info['pay-amount'] = $pricing_info['total-price'] - $price_settings['paid-amount'];
			if( !empty($price_settings['next-deposit-amount']) ){
				$pricing_info['deposit-rate'] = $price_settings['next-deposit-percent'];
				$pricing_info['deposit-price'] = $price_settings['next-deposit-amount'];
			}
			
			unset($pricing_info['deposit-price-raw']);
			unset($pricing_info['pay-amount-raw']);
			
			unset($pricing_info['deposit-paypal-service-rate']);
			unset($pricing_info['deposit-paypal-service-fee']);
			unset($pricing_info['pay-amount-paypal-service-rate']);
			unset($pricing_info['pay-amount-paypal-service-fee']);

			unset($pricing_info['deposit-credit-card-service-rate']);
			unset($pricing_info['deposit-credit-card-service-fee']);	
			unset($pricing_info['pay-amount-credit-card-service-rate']);
			unset($pricing_info['pay-amount-credit-card-service-fee']);

			global $wpdb;
			$wpdb->update("{$wpdb->prefix}tourmaster_order", 
				array('pricing_info' => json_encode($pricing_info)),
				array('id' => $_GET['tid']),
				array('%s'),
				array('%d')
			);
		}
	}

	get_header();
		
	tourmaster_set_currency($result->currency);

	$payment_style = tourmaster_get_option('general', 'payment-page-style', 'style-1');

	echo '<div class="tourmaster-page-wrapper tourmaster-payment-' . esc_attr($payment_style) . '" id="tourmaster-page-wrapper" >';

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
		echo '<div class="tourmaster-payment-step-wrap" >';
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
				echo 'tourmaster-checked ';
			}
			echo '" data-step="' . esc_attr($step_count) . '" >';
			echo '<span class="tourmaster-payment-step-item-icon" >';
			echo '<i class="fa fa-check" ></i>';
			echo '<span class="tourmaster-text" >' . $step_count . '</span>';
			echo '</span>';
			echo '<span class="tourmaster-payment-step-item-title" >' . $payment_step  . ($payment_style == 'style-2'? '<span></span>': '') . '</span>'; 
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
	echo 'data-booking-detail="' . esc_attr(json_encode(array('tid' => $_GET['tid']))) . '" >';
	echo '<div class="tourmaster-container" >';
	echo '<div class="tourmaster-page-content tourmaster-item-pdlr clearfix" >';

	$content = tourmaster_get_booked_payment_page($tour_option, $date_price, $booking_detail, $pricing_info);

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

	tourmaster_reset_currency();
	
	get_footer(); 

	do_action('include_goodlayers_payment_script');

?>