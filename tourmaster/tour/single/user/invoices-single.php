<?php
	echo '<div class="tourmaster-user-content-inner tourmaster-user-content-inner-invoices-single" >';
	tourmaster_get_user_breadcrumb();

	// booking table block
	tourmaster_user_content_block_start();

	global $current_user;
	$result = tourmaster_get_booking_data(array(
		'id' => $_GET['id'],
		'user_id' => $current_user->data->ID,
		'order_status' => array(
			'condition' => '!=',
			'value' => 'cancel'
		)
	), array('single' => true));

	tourmaster_set_currency($result->currency);

	echo '<div class="tourmaster-invoice-wrap clearfix" id="tourmaster-invoice-wrap" >';

	$invoice_logo = tourmaster_get_option('general', 'invoice-logo');
	$billing_info = empty($result->billing_info)? array(): json_decode($result->billing_info, true);
	$booking_detail = empty($result->booking_detail)? array(): json_decode($result->booking_detail, true);

	echo '<div class="tourmaster-invoice-head clearfix" >';
	echo '<div class="tourmaster-invoice-head-left" >';
	echo '<div class="tourmaster-invoice-logo" >';
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
	$customer_address = tourmaster_get_option('general', 'invoice-customer-address');
	if( empty($customer_address) ){
		echo '<span class="tourmaster-invoice-receiver-name" >' . $billing_info['first_name'] . ' ' . $billing_info['last_name'] . '</span>';
		echo '<span class="tourmaster-invoice-receiver-address" >' . (empty($billing_info['contact_address'])? '': $billing_info['contact_address']) . '</span>';
	}else{
		echo tourmaster_content_filter(tourmaster_set_contact_form_data($customer_address, $billing_info));
	}
	echo '</div>';
	echo '</div>';
	echo '</div>'; // tourmaster-invoice-head-left
	
	$company_name = tourmaster_get_option('general', 'invoice-company-name', '');
	$company_info = tourmaster_get_option('general', 'invoice-company-info', '');
	echo '<div class="tourmaster-invoice-head-right" >';
	echo '<div class="tourmaster-invoice-company-info" >';
	echo '<div class="tourmaster-invoice-company-name" >' . $company_name . '</div>';
	echo '<div class="tourmaster-invoice-company-info" >' . tourmaster_content_filter($company_info) . '</div>';
	echo '</div>';
	echo '</div>'; // tourmaster-invoice-head-right
	echo '</div>'; // tourmaster-invoice-head

	// traveller info
	if( !empty($result->traveller_info) ){
		$title_types = tourmaster_payment_traveller_title();
		$traveller_info = json_decode($result->traveller_info, true);
		if( !empty($tour_option['additional-traveller-fields']) ){
			$additional_traveller_fields = $tour_option['additional-traveller-fields'];
		}else{
			$additional_traveller_fields = tourmaster_get_option('general', 'additional-traveller-fields', '');
		}
		if( !empty($additional_traveller_fields) ){
			$additional_traveller_fields = tourmaster_read_custom_fields($additional_traveller_fields);
		}

		if( !empty($traveller_info) ){
			echo '<div class="tourmaster-invoice-single-traveller-info" style="margin-bottom: 50px; " >';
			echo '<h3 class="tourmaster-invoice-single-title" style="font-size: 16px; text-transform: uppercase; margin-top: -20px; font-weight: 800" >' . esc_html__('Traveller Info', 'tourmaster') . '</h3>';
			for( $i=0; $i<sizeof($traveller_info['first_name']); $i++ ){
				if( !empty($traveller_info['first_name'][$i]) || !empty($traveller_info['last_name'][$i]) ){
					echo '<div class="tourmaster-invoice-single-field clearfix" >';
					echo '<span class="tourmaster-head">' . esc_html__('Traveller', 'tourmaster') . ' ' . ($i+1) . ' :</span> ';
					echo '<span class="tourmaster-tail">';
					if( !empty($traveller_info['title'][$i]) ){
						if( !empty($title_types[$traveller_info['title'][$i]]) ){
							echo $title_types[$traveller_info['title'][$i]] . ' ';
						}
					}
					echo $traveller_info['first_name'][$i] . ' ' . $traveller_info['last_name'][$i];
					if( !empty($traveller_info['passport'][$i]) ){
						echo '<br>' . esc_html__('Passport ID :', 'tourmaster') . ' ' . $traveller_info['passport'][$i];
					}
					if( !empty($additional_traveller_fields) ){
						foreach( $additional_traveller_fields as $field ){
							if( !empty($booking_detail['traveller_' . $field['slug']][$i]) ){
								echo '<br>' . $field['title'] . ' ' . $booking_detail['traveller_' . $field['slug']][$i];
							}
						}
					}
					echo '</span>';
					echo '</div>';				
				}
			}
			echo '</div>'; // tourmaster-my-booking-single-traveller-info
		}
	}

	// price breakdown
	if( !empty($result->pricing_info) ){
		$pricing_info = json_decode($result->pricing_info, true);

		echo '<div class="tourmaster-invoice-price-breakdown" >';
		echo '<div class="tourmaster-invoice-price-head" >';
		echo '<span class="tourmaster-head" >' . esc_html__('Description', 'tourmaster') . '</span>';
		echo '<span class="tourmaster-tail" >' . esc_html__('Total', 'tourmaster') . '</span>';
		echo '</div>'; // tourmaster-invoice-price-head

		echo tourmaster_get_tour_invoice_price($result->tour_id, $pricing_info['price-breakdown'], $booking_detail);

		echo '<div class="tourmaster-invoice-total-price clearfix" >';
		echo '<span class="tourmaster-head">' . esc_html__('Total', 'tourmaster') . '</span> ';
		echo '<span class="tourmaster-tail">' . tourmaster_money_format($result->total_price) . '</span>';
		echo '</div>'; // tourmaster-invoice-total-price
		echo '</div>'; // tourmaster-invoice-price-breakdown
	}

	if( !empty($result->payment_info) ){
	// if( !empty($result->order_status) && in_array($result->order_status, array('approve', 'online-paid', 'departed', 'deposit-paid')) ){
		$payment_infos = json_decode($result->payment_info, true);
		$payment_infos = tourmaster_payment_info_format($payment_infos, $result->order_status);

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

				// fee
				if( !empty($payment_info['deposit_amount']) ){
					echo '<div class="tourmaster-invoice-payment-info-item" >';
					echo '<div class="tourmaster-head" >' . esc_html__('Deposit Amount', 'tourmaster') . '</div>';
					echo '<div class="tourmaster-tail" >' . tourmaster_money_format($payment_info['deposit_amount']) . '</div>';	
					echo '</div>'; // tourmaster-invoice-payment-info-item
				}else if( !empty($payment_info['pay_amount']) ){
					echo '<div class="tourmaster-invoice-payment-info-item" >';
					echo '<div class="tourmaster-head" >' . esc_html__('Total Price', 'tourmaster') . '</div>';
					echo '<div class="tourmaster-tail" >' . tourmaster_money_format($payment_info['pay_amount']) . '</div>';	
					echo '</div>'; // tourmaster-invoice-payment-info-item
				}
				if( !empty($payment_info['deposit_paypal_service_rate']) && !empty($payment_info['deposit_paypal_service_fee']) ){
					echo '<div class="tourmaster-invoice-payment-info-item" >';
					echo '<div class="tourmaster-head" >' . sprintf(esc_html__('Paypal Fee (%s%%)', 'tourmaster'), $payment_info['deposit_paypal_service_rate']) . '</div>';
					echo '<div class="tourmaster-tail" >' . tourmaster_money_format($payment_info['deposit_paypal_service_fee']) . '</div>';	
					echo '</div>'; // tourmaster-invoice-payment-info-item
				}else if( !empty($payment_info['pay_paypal_service_rate']) && !empty($payment_info['pay_paypal_service_fee']) ){
					echo '<div class="tourmaster-invoice-payment-info-item" >';
					echo '<div class="tourmaster-head" >' . sprintf(esc_html__('Paypal Fee (%s%%)', 'tourmaster'), $payment_info['pay_paypal_service_rate']) . '</div>';
					echo '<div class="tourmaster-tail" >' . tourmaster_money_format($payment_info['pay_paypal_service_fee']) . '</div>';	
					echo '</div>'; // tourmaster-invoice-payment-info-item
				}else if( !empty($payment_info['deposit_credit_card_service_rate']) && !empty($payment_info['deposit_credit_card_service_fee']) ){
					echo '<div class="tourmaster-invoice-payment-info-item" >';
					echo '<div class="tourmaster-head" >' . sprintf(esc_html__('Credit Card Fee (%s%%)', 'tourmaster'), $payment_info['deposit_credit_card_service_rate']) . '</div>';
					echo '<div class="tourmaster-tail" >' . tourmaster_money_format($payment_info['deposit_credit_card_service_fee']) . '</div>';	
					echo '</div>'; // tourmaster-invoice-payment-info-item
				}else if( !empty($payment_info['pay_credit_card_service_rate']) && !empty($payment_info['pay_credit_card_service_fee']) ){
					echo '<div class="tourmaster-invoice-payment-info-item" >';
					echo '<div class="tourmaster-head" >' . sprintf(esc_html__('Credit Card Fee (%s%%)', 'tourmaster'), $payment_info['pay_credit_card_service_rate']) . '</div>';
					echo '<div class="tourmaster-tail" >' . tourmaster_money_format($payment_info['pay_credit_card_service_fee']) . '</div>';	
					echo '</div>'; // tourmaster-invoice-payment-info-item
				}

				// paid amount
				if( !empty($payment_info['amount']) || !empty($payment_info['deposit_price']) ){
					echo '<div class="tourmaster-invoice-payment-info-item" >';
					echo '<div class="tourmaster-head" >' . esc_html__('Paid Amount', 'tourmaster') . '</div>';
					if( !empty($payment_info['amount']) ){
						echo '<div class="tourmaster-tail" >' . tourmaster_money_format($payment_info['amount']) . '</div>';	
					}else{
						echo '<div class="tourmaster-tail" >' . tourmaster_money_format($payment_info['deposit_price']) . '</div>';
					}
					echo '</div>'; // tourmaster-invoice-payment-info-item
				}

				echo '<div class="tourmaster-invoice-payment-info-item" >';
				echo '<div class="tourmaster-head" >' . esc_html__('Date', 'tourmaster') . '</div>';
				echo '<div class="tourmaster-tail" >' . tourmaster_date_format($payment_info['submission_date']) . '</div>';
				echo '</div>'; // tourmaster-invoice-payment-info-item
				
				$transaction_id = '';
				if( !empty($payment_info['transaction_id']) ){
					$transaction_id = $payment_info['transaction_id'];
				}else if( !empty($payment_info['transaction-id']) ){
					$transaction_id = $payment_info['transaction-id'];
				}
				if( !empty($transaction_id) ){
					echo '<div class="tourmaster-invoice-payment-info-item" >';
					echo '<div class="tourmaster-head" >' . esc_html__('Transaction ID', 'tourmaster') . '</div>';
					echo '<div class="tourmaster-tail" >' . $transaction_id . '</div>';
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