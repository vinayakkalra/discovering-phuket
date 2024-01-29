<?php 
	if( !empty($_GET['tid']) ){
		$tid = trim($_GET['tid']);
	}else if( !empty($_COOKIE['tourmaster-room-current-id']) ){
		$tid = trim($_COOKIE['tourmaster-room-current-id']);
	}

	global $wpdb;
	$sql  = "SELECT * FROM {$wpdb->prefix}tourmaster_room_order ";
	$sql .= $wpdb->prepare("WHERE id = %d ", $tid);
	$sql .= $wpdb->prepare("AND user_id = %d ", get_current_user_id());
	$result = $wpdb->get_row($sql);	

	// get price data
	$booking_details = json_decode($result->booking_data, true);
	$contact_info = json_decode($result->contact_info, true);
	$price_breakdowns = json_decode($result->price_breakdown, true);
	$payment_info = json_decode($result->payment_info, true);

	get_header();

	tourmaster_set_currency($result->currency);

	echo '<div class="tourmaster-page-wrapper" id="tourmaster-room-payment-display-page" ';
	echo 'data-tid="' . esc_attr($result->id) . '" ';
	echo 'data-ajax-url="' . esc_attr(TOURMASTER_AJAX_URL) . '" >';
	echo '<div class="tourmaster-container clearfix" >';

	if( in_array($result->order_status, array('approved', 'online-paid')) ){

		echo '<div class="tourmaster-room-paid-order-message clearfix" style="padding: 60px 0px;" >';
		echo '<p>' . esc_html__('This order has already been paid, please check your order on dashboard page', 'tourmaster') . '</p>';
		echo '<a class="tourmaster-button" href="' . tourmaster_get_template_url('dashboard') . '" >' . esc_html__('Go to Dashboard', 'tourmaster') . '</a>';
		echo '</div>';

	}else{
		tourmaster_room_payment_step(3);

		echo '<div class="tourmaster-payment-step tourmaster-step3 clearfix" id="tourmaster-step3-wrap" style="display: block;" >';
		echo '<div class="tourmaster-column-40" >';
		echo '<div class="tourmaster-room-contact-detail-wrap" >';
		echo tourmaster_room_display_contact_detail($booking_details, $contact_info);
		echo '</div>';
		echo '</div>'; // tourmaster-column-40
		echo '<div class="tourmaster-column-20" >';
		echo '<div class="tourmaster-room-sidebar-summary-wrap tourmaster-item-pdlr" >';
		tourmaster_room_price_sidebar($price_breakdowns, 3, $payment_info, $result->order_status);
		echo '</div>'; // tourmaster-room-sidebar-summary-wrap
		echo '</div>'; // tourmaster-column-20
		echo '</div>'; // tourmaster-step3
	
		echo '<div class="tourmaster-payment-step tourmaster-step4 clearfix" id="tourmaster-step4-wrap" >';
		echo '<div class="tourmaster-room-complete-booking-wrap tourmaster-item-pdlr" >';
		echo tourmaster_room_booking_complete();
		echo '</div>';
		echo '</div>';
	}

	
	echo '</div>'; // tourmaster-container
	echo '</div>'; // tourmaster-page-wrapper

	tourmaster_reset_currency();

	get_footer(); 

	do_action('include_goodlayers_payment_script');

?>