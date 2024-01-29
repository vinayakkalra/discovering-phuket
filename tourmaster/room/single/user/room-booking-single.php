<?php
	echo '<div class="tourmaster-user-content-inner tourmaster-user-content-inner-my-booking-single" >';
	tourmaster_get_user_breadcrumb();
	
	if( !empty($_GET['error_code']) && $_GET['error_code'] == 'cannot_upload_file' ){ 
		echo '<div class="tourmaster-notification-box tourmaster-failure" >';
		echo esc_html__('Cannot upload a media file, please try uploading it again.', 'tourmaster');
		echo '</div>';
	}

	// booking table block
	tourmaster_user_content_block_start();

	global $wpdb, $current_user;
	$sql  = "SELECT * FROM {$wpdb->prefix}tourmaster_room_order ";
	$sql .= $wpdb->prepare("WHERE user_id = %d ", $current_user->data->ID);
	$sql .= $wpdb->prepare("AND id = %d ", $_GET['id']);
	$result = $wpdb->get_row($sql);

	tourmaster_set_currency($result->currency);

	$contact_fields = tourmaster_room_payment_contact_form_fields();
	$contact_detail = empty($result->contact_info)? array(): json_decode($result->contact_info, true);

	// sidebar
	echo '<div class="tourmaster-my-booking-single-content-wrap" >';
	echo '<div class="tourmaster-my-booking-single-sidebar" >';
	$statuses = array(
		'all' => esc_html__('All', 'tourmaster'),
		'pending' => esc_html__('Pending', 'tourmaster'),
		'approved' => esc_html__('Approved', 'tourmaster'),
		'receipt-submitted' => esc_html__('Receipt Submitted', 'tourmaster'),
		'online-paid' => esc_html__('Online Paid', 'tourmaster'),
		'deposit-paid' => esc_html__('Deposit Paid', 'tourmaster'),
		// 'departed' => esc_html__('Departed', 'tourmaster'),
		'rejected' => esc_html__('Rejected', 'tourmaster'),
		'wait-for-approval' => esc_html__('Wait For Approval', 'tourmaster'),
	);
	echo '<h3 class="tourmaster-my-booking-single-title">' . esc_html__('Order Status', 'tourmaster') . '</h3>';
	echo '<div class="tourmaster-booking-status tourmaster-status-' . esc_attr($result->order_status) . '" >' . $statuses[$result->order_status] . '</div>';
	
	// payment info
	echo '<h3 class="tourmaster-my-booking-single-sub-title">' . esc_html__('Bank Payment Receipt', 'tourmaster') . '</h3>';
	$payment_infos = empty($result->payment_info)? array(): json_decode($result->payment_info, true);
	if( !empty($payment_infos) ){
		$paid_amount = 0;
		$paid_times = 0;
		foreach( $payment_infos as $payment_info ){
			if( !empty($payment_info['amount']) ){
				$paid_times++;
				$paid_amount += floatval($payment_info['amount']);

				echo '<div class="tourmaster-deposit-item ' . ($paid_times == sizeof($payment_infos)? 'tourmaster-active': '') . '" >';
				echo '<div class="tourmaster-deposit-item-head" ><i class="icon_plus" ></i>';
				if( tourmaster_compare_price($paid_amount, $result->total_price) || $paid_amount > $result->total_price ){
					echo sprintf(esc_html__('Final Payment : %s', 'tourmaster'), tourmaster_money_format($paid_amount));
				}else{
					echo sprintf(esc_html__('Deposit %d : %s', 'tourmaster'), $paid_times, tourmaster_money_format($paid_amount));
				}
				echo '</div>';

				echo '<div class="tourmaster-deposit-item-content" >';
				tourmaster_room_deposit_item_content($result, $payment_info);
				echo '</div>';
				echo '</div>';
			}
		}
	}

	if( $result->order_status != 'wait-for-approval' ){
		$price_settings = tourmaster_room_get_submit_receipt_settings($result->total_price, $payment_infos);
		
		if( in_array($result->order_status, array('pending', 'rejected', 'deposit-paid')) || 
			($result->order_status == 'deposit-paid' && $price_settings['more-payment'] == true) ){
	
			echo '<a data-tmlb="payment-receipt" class="tourmaster-my-booking-single-receipt-button tourmaster-button" >' . esc_html__('Submit Payment Receipt', 'tourmaster') . '</a>';
			echo tourmaster_lightbox_content(array(
				'id' => 'payment-receipt',
				'title' => esc_html__('Submit Bank Payment Receipt', 'tourmaster'),
				'content' => tourmaster_lb_payment_receipt($result->id, $price_settings, 'room')
			));

			$payment_method = tourmaster_get_option('payment', 'payment-method', array('booking', 'paypal', 'credit-card'));
			$paypal_enable = in_array('paypal', $payment_method);
			$credit_card_enable = in_array('credit-card', $payment_method);
			$hipayprofessional_enable = in_array('hipayprofessional', $payment_method);
			$custom_payment_enable = apply_filters('tourmaster_custom_payment_enable', false, $payment_method);

			if( empty($pricing_info['admin-edit']) ){
				if( $paypal_enable || $credit_card_enable || $hipayprofessional_enable || $custom_payment_enable ){
					echo '<a href="';
					echo esc_url(add_query_arg(array('tid'=>$result->id, 'pt'=>'room'), tourmaster_get_template_url('payment')));
					echo '" class="tourmaster-my-booking-single-payment-button tourmaster-button" >' . esc_html__('Make an Online Payment', 'tourmaster') . '</a>';
				}
			}
			
		}
	} // enable payment

	echo '</div>'; // tourmaster-my-booking-single-sidebar

	// content
	$detail_column = 20;
	if( empty($contact_detail['required-billing']) || $contact_detail['required-billing'] == 'false' ){
		$detail_column = 30;
	}

	echo '<div class="tourmaster-my-booking-single-content" >';
	echo '<div class="tourmaster-item-rvpdlr clearfix" >';
	echo '<div class="tourmaster-my-booking-single-order-summary-column tourmaster-column-' . esc_attr($detail_column) . ' tourmaster-item-pdlr" >';
	echo '<h3 class="tourmaster-my-booking-single-title">' . esc_html__('Order Summary', 'tourmaster') . '</h3>';

	echo '<div class="tourmaster-my-booking-single-field clearfix" >';
	echo '<span class="tourmaster-head">' . esc_html__('Order Number', 'tourmaster') . ' :</span> ';
	echo '<span class="tourmaster-tail">#' . $result->id . '</span>';
	echo '</div>';

	echo '<div class="tourmaster-my-booking-single-field clearfix" >';
	echo '<span class="tourmaster-head">' . esc_html__('Booking Date', 'tourmaster') . ' :</span> ';
	echo '<span class="tourmaster-tail">' . tourmaster_date_format($result->booking_date) . '</span>';
	echo '</div>';

	$booking_details = empty($result->booking_data)? array(): json_decode($result->booking_data, true);
	for( $i = 0; $i < sizeof($booking_details); $i++ ){
		$booking_detail = $booking_details[$i];
		echo '<div class="tourmaster-my-booking-single-field clearfix" >';
		echo '<div class="tourmaster-head tourmaster-full">' . esc_html__('Room :', 'tourmaster') . ' ' . get_the_title($booking_detail['room_id']) . '</div> ';
		echo '<div class="tourmaster-tail tourmaster-indent">' . sprintf(_n('%d Room', '%d Rooms', $booking_detail['room_amount'], 'tourmaster'), $booking_detail['room_amount']) . '</div>';
		echo '<div class="tourmaster-tail tourmaster-indent">' . tourmaster_room_booking_duration_info($booking_detail['start_date'], $booking_detail['end_date'], false) . '</div>';
		echo '</div>';
	}

	if( !empty($contact_detail['additional_notes']) ){
		echo '<div class="tourmaster-my-booking-single-field tourmaster-additional-note clearfix" >';
		echo '<div class="tourmaster-head">' . esc_html__('Customer\'s Note', 'tourmaster') . ' :</div> ';
		echo '<div class="tourmaster-tail">' . $contact_detail['additional_notes'] . '</div>';
		echo '</div>';
	}
	echo '</div>'; // tourmaster-my-booking-single-order-summary-column

	echo '<div class="tourmaster-my-booking-single-contact-detail-column tourmaster-column-' . esc_attr($detail_column) . ' tourmaster-item-pdlr" >';
	echo '<h3 class="tourmaster-my-booking-single-title">' . esc_html__('Contact Detail', 'tourmaster') . '</h3>';
	foreach( $contact_fields as $field_slug => $contact_field ){
		if( !empty($contact_detail[$field_slug]) ){
			echo '<div class="tourmaster-my-booking-single-field clearfix" >';
			echo '<span class="tourmaster-head">' . $contact_field['title'] . ' :</span> ';
			if( $field_slug == 'country' ){
				echo '<span class="tourmaster-tail">' . tourmaster_get_country_list('', $contact_detail[$field_slug]) . '</span>';
			}else{
				echo '<span class="tourmaster-tail">' . $contact_detail[$field_slug] . '</span>';
			}
			echo '</div>';
		}
	}
	echo '</div>'; // tourmaster-my-booking-single-contact-detail-column

	if( !empty($contact_detail['required-billing']) && $contact_detail['required-billing'] != 'false' ){
		echo '<div class="tourmaster-my-booking-single-billing-detail-column tourmaster-column-' . esc_attr($detail_column) . ' tourmaster-item-pdlr" >';
		echo '<h3 class="tourmaster-my-booking-single-title">' . esc_html__('Billing Detail', 'tourmaster') . '</h3>';
		foreach( $contact_fields as $field_slug => $contact_field ){
			if( !empty($billing_detail[$field_slug]) ){
				echo '<div class="tourmaster-my-booking-single-field clearfix" >';
				echo '<span class="tourmaster-head">' . $contact_field['title'] . ' :</span> ';
				if( $field_slug == 'country' ){
					echo '<span class="tourmaster-tail">' . tourmaster_get_country_list('', $billing_detail[$field_slug]) . '</span>';
				}else{
					echo '<span class="tourmaster-tail">' . $billing_detail[$field_slug] . '</span>';
				}
				echo '</div>';
			}
		}
		echo '</div>'; // tourmaster-my-booking-single-billing-detail-column
	}

	echo '</div>'; // tourmaster-item-rvpdl

	// traveller info
	$guest_fields = tourmaster_get_option('room_general', 'additional-guest-fields', array());
	if( !empty($guest_fields) ){
		$guest_fields = tourmaster_read_custom_fields($guest_fields);
	}
	if( !empty($contact_detail['guest_first_name']) ){
		echo '<div class="tourmaster-my-booking-single-traveller-info" >';
		echo '<h3 class="tourmaster-my-booking-single-title">' . esc_html__('Traveller Info', 'tourmaster') . '</h3>';
		for( $i = 0; $i < sizeof($booking_details); $i++ ){
			$booking_detail = $booking_details[$i]; 
			for( $j = 0; $j < intval($booking_detail['room_amount']); $j++ ){
				echo '<div class="tourmaster-my-booking-single-field clearfix" >';
				if( intval($booking_detail['room_amount']) > 1 ){
					echo '<span class="tourmaster-head">' . sprintf(esc_html__('%s : Room %d', 'tourmaster'), get_the_title($booking_detail['room_id']), $j + 1) . '</span> ';
				}else{					
					echo '<span class="tourmaster-head">' . get_the_title($booking_detail['room_id']) . '</span> ';
				}
				echo '</div>';
				for( $k = 0; $k < sizeof($contact_detail['guest_first_name'][$i][$j]); $k++ ){
					if( !empty($contact_detail['guest_first_name'][$i][$j][$k]) || !empty($contact_detail['guest_first_name'][$i][$j][$k]) ){
						echo '<div class="tourmaster-my-booking-single-field clearfix" >';
						echo '<span class="tourmaster-head">' . sprintf(esc_html__('Guest %d:', 'tourmaster'), ($k+1)) . '</span> ';
						echo '<span class="tourmaster-tail">';
						echo $contact_detail['guest_first_name'][$i][$j][$k] . ' ' . $contact_detail['guest_last_name'][$i][$j][$k];
						foreach( $guest_fields as $field ){
							if( !empty($contact_detail['traveller_' . $field['slug']][$i][$j][$k]) ){
								echo '<br>' . $field['title'] . ' ' . $contact_detail['traveller_' . $field['slug']][$i][$j][$k];
							}
						}
						echo '</span>';
						echo '</div>';				
					}
				}

			}
		}

		for( $i=0; $i<sizeof($contact_detail['guest_first_name']); $i++ ){
			
		}
		
		echo '</div>'; // tourmaster-my-booking-single-traveller-info
	}

	// price breakdown
	$price_breakdowns = empty($result->price_breakdown)? array(): json_decode($result->price_breakdown, true);
	if( !empty($price_breakdowns) ){
		echo '<div class="tourmaster-my-booking-single-price-breakdown" >';
		echo '<h3 class="tourmaster-my-booking-single-title">' . esc_html__('Price Breakdown', 'tourmaster') . '</h3>';
		echo tourmaster_get_room_booking_price_breakdown($booking_details, $price_breakdowns);
		echo '</div>'; // tourmaster-my-booking-single-traveller-info
	}

	tourmaster_reset_currency();

	echo '</div>'; // tourmaster-my-booking-single-content
	echo '</div>'; // tourmaster-my-booking-single-content-wrap

	tourmaster_user_content_block_end();

	echo '</div>'; // tourmaster-user-content-inner