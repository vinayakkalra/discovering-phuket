<?php
	echo '<div class="tourmaster-user-content-inner tourmaster-user-content-inner-invoices-single" >';
	tourmaster_get_user_breadcrumb();

	// booking table block
	tourmaster_user_content_block_start();

	global $wpdb, $current_user;
	$sql  = "SELECT * FROM {$wpdb->prefix}tourmaster_room_order ";
	$sql .= $wpdb->prepare("WHERE user_id = %d ", $current_user->data->ID);
	$sql .= $wpdb->prepare("AND id = %d ", $_GET['id']);
	$sql .= "AND order_status != 'cancel' ";
	$result = $wpdb->get_row($sql);

	tourmaster_set_currency($result->currency);

	echo '<div class="tourmaster-invoice-wrap clearfix" id="tourmaster-invoice-wrap" >';

	$invoice_logo = tourmaster_get_option('room_general', 'invoice-logo');
	$contact_detail = empty($result->contact_info)? array(): json_decode($result->contact_info, true);
	$billing_prefix = (empty($contact_detail['required-billing']) || $contact_detail['required-billing'] == 'false')? '': 'billing_';

	echo '<div class="tourmaster-invoice-head clearfix" >';
	echo '<div class="tourmaster-invoice-head-left" >';
	echo '<div class="tourmaster-room-invoice-logo" >';
	if( empty($invoice_logo) ){
		echo tourmaster_get_image(TOURMASTER_URL . '/images/invoice-logo.png');
	}else{
		echo tourmaster_get_image($invoice_logo);
	}
	echo '</div>'; // tourmaster-invoice-logo
	echo '<div class="tourmaster-invoice-id" >' . esc_html__('Invoice ID :', 'tourmaster') . ' #' . $result->id . '</div>';
	echo '<div class="tourmaster-invoice-date" >' . esc_html__('Invoice date :', 'tourmaster') . ' ' . tourmaster_date_format($result->booking_date) . '</div>';
	echo '<div class="tourmaster-invoice-receiver" >';
	echo '<div class="tourmaster-invoice-receiver-head" >' . esc_html__('Invoice To', 'tourmaster') . '</div>';
	echo '<div class="tourmaster-invoice-receiver-info" >';
	$customer_address = tourmaster_get_option('room_general', 'invoice-customer-address');
	if( empty($customer_address) ){
		echo '<span class="tourmaster-invoice-receiver-name" >' . $contact_detail[$billing_prefix . 'first_name'] . ' ' . $contact_detail[$billing_prefix . 'last_name'] . '</span>';
		echo '<span class="tourmaster-invoice-receiver-address" >' . (empty($contact_detail[$billing_prefix . 'contact_address'])? '': $contact_detail[$billing_prefix . 'contact_address']) . '</span>';
	}else{
		echo tourmaster_content_filter(tourmaster_set_contact_form_data($customer_address, $contact_detail, $billing_prefix));
	}
	echo '</div>';
	echo '</div>';
	echo '</div>'; // tourmaster-invoice-head-left
	
	$company_name = tourmaster_get_option('room_general', 'invoice-company-name', '');
	$company_info = tourmaster_get_option('room_general', 'invoice-company-info', '');
	echo '<div class="tourmaster-invoice-head-right" >';
	echo '<div class="tourmaster-invoice-company-info" >';
	echo '<div class="tourmaster-invoice-company-name" >' . $company_name . '</div>';
	echo '<div class="tourmaster-invoice-company-info" >' . tourmaster_content_filter($company_info) . '</div>';
	echo '</div>';
	echo '</div>'; // tourmaster-invoice-head-right
	echo '</div>'; // tourmaster-invoice-head

	// price breakdown
	$booking_details = empty($result->booking_data)? array(): json_decode($result->booking_data, true);
	$price_breakdowns = empty($result->price_breakdown)? array(): json_decode($result->price_breakdown, true);
	echo '<div class="tourmaster-invoice-price-breakdown" >';
	echo '<div class="tourmaster-invoice-price-head" >';
	echo '<span class="tourmaster-head" >' . esc_html__('Description', 'tourmaster') . '</span>';
	echo '<span class="tourmaster-tail" >' . esc_html__('Total', 'tourmaster') . '</span>';
	echo '</div>'; // tourmaster-invoice-price-head
	echo tourmaster_get_room_invoice_price($booking_details, $price_breakdowns);
	echo '</div>'; // tourmaster-invoice-price-breakdown

	if( !empty($result->payment_info) ){
		$payment_infos = json_decode($result->payment_info, true);

		if( !empty($payment_infos) ){
			echo '<div class="tourmaster-invoice-payment-info clearfix" >';
			foreach( $payment_infos as $payment_info ){
				echo '<div class="tourmaster-invoice-payment-info-item-wrap clearfix" >';
				echo '<div class="tourmaster-invoice-payment-info-item" >';
				echo '<div class="tourmaster-head" >' . esc_html__('Payment Method', 'tourmaster') . '</div>';
				echo '<div class="tourmaster-tail" >';
				if( !empty($payment_info['payment_method']) && $payment_info['payment_method'] == 'receipt' ){
					echo esc_html__('Bank Transfer', 'tourmaster');
				}else if( !empty($payment_info['payment_method']) ){
					if( $payment_info['payment_method'] == 'paypal' ){
						echo esc_html__('Paypal', 'tourmaster');
					}else{
						echo esc_html__('Credit Card', 'tourmaster');
					}
				}
				echo '</div>';
				echo '</div>'; // tourmaster-invoice-payment-info-item

				// paid amount
				if( !empty($payment_info['amount']) ){
					echo '<div class="tourmaster-invoice-payment-info-item" >';
					echo '<div class="tourmaster-head" >' . esc_html__('Amount', 'tourmaster') . '</div>';
					echo '<div class="tourmaster-tail" >' . tourmaster_money_format($payment_info['amount']) . '</div>';	
					echo '</div>'; // tourmaster-invoice-payment-info-item
				}
				if( !empty($payment_info['service_fee']) ){
					echo '<div class="tourmaster-invoice-payment-info-item" >';
					echo '<div class="tourmaster-head">' . esc_html__('Service Fee', 'tourmaster') . '</div> ';
					echo '<div class="tourmaster-tail">' . tourmaster_money_format($payment_info['service_fee']) . '</div>';
					echo '</div>'; // tourmaster-invoice-payment-info-item
	
					if( !empty($payment_info['paid_amount']) ){
						echo '<div class="tourmaster-invoice-payment-info-item" >';
						echo '<div class="tourmaster-head">' . esc_html__('Paid Amount', 'tourmaster') . '</div> ';
						echo '<div class="tourmaster-tail">' . tourmaster_money_format($payment_info['paid_amount']) . '</div>';
						echo '</div>';
					}
				}

				echo '<div class="tourmaster-invoice-payment-info-item" >';
				echo '<div class="tourmaster-head" >' . esc_html__('Date', 'tourmaster') . '</div>';
				echo '<div class="tourmaster-tail" >' . tourmaster_date_format($payment_info['submission_date']) . '</div>';
				echo '</div>'; // tourmaster-invoice-payment-info-item
				
				if( !empty($payment_info['transaction_id']) ){
					echo '<div class="tourmaster-invoice-payment-info-item" >';
					echo '<div class="tourmaster-head" >' . esc_html__('Transaction ID', 'tourmaster') . '</div>';
					echo '<div class="tourmaster-tail" >' . $payment_info['transaction_id'] . '</div>';
					echo '</div>'; // tourmaster-invoice-payment-info-item
				}
				echo '</div>';
			}
			echo '</div>'; // tourmaster-invoice-payment-info

		}
	}

	echo '</div>'; // tourmaster-invoice-wrap

	echo '<div class="tourmaster-invoice-button" >';
	if( empty($result->order_status) || !in_array($result->order_status, array('approved', 'online-paid', 'departed')) ){
		echo '<a href="' . esc_url(add_query_arg(array('page_type'=>'my-booking'))) . '" class="tourmaster-button" >' . esc_html__('Make a Payment', 'tourmaster') . '</a>';
	}
	echo '<a href="#" class="tourmaster-button tourmaster-print" data-id="tourmaster-invoice-wrap" ><i class="fa fa-print" ></i>' . esc_html__('Print', 'tourmaster') . '</a>';
	// echo '<a href="#" class="tourmaster-button tourmaster-pdf-download" data-id="tourmaster-invoice-wrap" ><i class="fa fa-file-pdf-o" ></i>' . esc_html__('Download Pdf', 'tourmaster') . '</a>';
	echo '</div>'; // tourmaster-invoice-button

	tourmaster_reset_currency();

	tourmaster_user_content_block_end();
	echo '</div>'; // tourmaster-user-content-inner