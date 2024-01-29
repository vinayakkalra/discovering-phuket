<?php
	if( !function_exists('tourmaster_room_mail') ){
		function tourmaster_room_mail( $settings = array() ){
			global $tourmaster_debug;
			if( !empty($tourmaster_debug) ){
				print_r($settings); return;
			}

			$sender_name = tourmaster_get_option('room_general', 'system-email-name', 'WORDPRESS');
			$sender = tourmaster_get_option('room_general', 'system-email-address');

			if( !empty($sender) ){ 
				$headers  = "From: {$sender_name} <{$sender}>\r\n";
				if( !empty($settings['reply-to']) ){
					$headers .= "Reply-To: {$settings['reply-to']}\r\n";
				}
				if( !empty($settings['cc']) ){
					$headers .= "CC: {$settings['cc']}\r\n"; 
				}
				$headers .= "MIME-Version: 1.0\r\n";
				$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

				wp_mail($settings['recipient'], $settings['title'], $settings['message'], $headers);
			}

		} // tourmaster_room_mail
	}

    if( !function_exists('tourmaster_room_mail_content') ){
		function tourmaster_room_mail_content( $content = '', $header = true, $footer = true, $settings = array() ){

			$settings['width'] = empty($settings['width'])? '600': $settings['width'];
			$settings['padding'] = empty($settings['padding'])? '60px 60px 40px': $settings['padding'];

			ob_start();

			echo '<html><body>';
			echo '<div class="tourmaster-mail-template" style="line-height: 1.7; background: #f5f5f5; margin: 40px auto 40px; min-width: ' . $settings['width'] . 'px; width: ' . $settings['width'] . 'px; font-size: 14px; font-family: Arial, Helvetica, sans-serif; color: #838383;" >';
			if( !empty($header) ){
				$header_logo = tourmaster_get_option('room_general', 'mail-header-logo', TOURMASTER_URL . '/images/logo.png');

				echo '<div class="tourmaster-mail-header" style="background: #353d46; padding: 25px 35px;" >';
				echo tourmaster_get_image($header_logo);
				echo '<div style="display: block; clear: both; visibility: hidden; line-height: 0; height: 0; zoom: 1;" ></div>'; // clear
				echo '</div>';
			}

			if( empty($settings['no-filter']) ){
				$content = tourmaster_content_filter($content);
			}

			//apply css to link and p tag
			$pointer = 0;
			while( ($new_pointer = strpos($content, '<a', $pointer)) !== false ){
				$pointer = $new_pointer + 2;

				$style_tag = strpos($content, 'style=', $pointer);
				$close_tag = strpos($content, '>', $pointer);

				if( $style_tag === false || $close_tag < $style_tag ){
					$first_section = substr($content, 0, $pointer);
					$last_section = substr($content, $pointer);
					$content  = $first_section . ' style="color: #4290de; text-decoration: none;" ' . $last_section;
				}
			}
			echo '<div class="tourmaster-mail-content" style="padding: ' . $settings['padding'] . ';" >' . $content . '</div>';

			if( !empty($footer) ){
				$footer_left = tourmaster_get_option('room_general', 'mail-footer-left', '');
				$footer_right = tourmaster_get_option('room_general', 'mail-footer-right', '');

				echo '<div class="tourmaster-mail-footer" style="background: #ebedef; font-size: 13px; padding: 25px 30px 5px;" >';
				if( !empty($footer_left) ){
					echo '<div class="tourmaster-mail-footer-left" style="float: left; text-align: left;" >' . tourmaster_content_filter($footer_left) . '</div>';
				}
				if( !empty($footer_right) ){
					echo '<div class="tourmaster-mail-footer-right" style="float: right; text-align: right;" >' . tourmaster_content_filter($footer_right) . '</div>';
				}
				echo '<div style="display: block; clear: both; visibility: hidden; line-height: 0; height: 0; zoom: 1;" ></div>'; // clear
				echo '</div>';
			}
			echo '</div>';
			echo '</body></html>';

			$message = ob_get_contents();
			ob_end_clean();

			return $message;

		} // tourmaster_room_mail_content
	}

	if( !function_exists('tourmaster_room_mail_notification') ){
		function tourmaster_room_mail_notification( $type, $tid = '', $user_id = '', $settings = array() ){

			if( $type == 'custom' || $type == 'admin-custom' ){
				$option_enable = 'enable';
				$mail_title = empty($settings['title'])? '': $settings['title'];
				$raw_message = empty($settings['message'])? '': $settings['message'];
			}else{
				$option_enable = tourmaster_get_option('room_general', 'enable-' . $type, 'enable');
				$mail_title = tourmaster_get_option('room_general', $type . '-title');
				$raw_message = tourmaster_get_option('room_general', $type);
			}

			if( $option_enable == 'enable' ){

				if( !empty($tid) ){
					global $wpdb, $current_user;
					$sql  = "SELECT * FROM {$wpdb->prefix}tourmaster_room_order ";
					$sql .= $wpdb->prepare("WHERE id = %d ", $tid);
					$result = $wpdb->get_row($sql);
					$contact_info = empty($result->contact_info)? array(): json_decode($result->contact_info, true);
				}else if( !empty($settings['result']) ){
					$result = $settings['result'];
					$contact_info = json_decode($result->contact_info, true);
				}

				if( !empty($result) ){

					$mail_title = str_replace('{id}', $result->id, $mail_title);
					$raw_message = str_replace('{id}', $result->id, $raw_message);

					// customer mail
					$user_email = $contact_info['email'];
					$raw_message = str_replace('{customer-email}', $user_email, $raw_message);
					
					$booking_details = empty($result->booking_data)? array(): json_decode($result->booking_data, true);
					
					// room_info
					$booking_info = '';
					for( $i = 0; $i < sizeof($booking_details); $i++ ){
						$booking_detail = $booking_details[$i]; 

						$booking_info .= '<div class="tourmaster-mail-booking-info" style="margin-bottom: 10px;" >';
						$booking_info .= '<div class="tourmaster-head" style="font-weight: bold;" >' . esc_html__('Room :', 'tourmaster') . ' ' . get_the_title($booking_detail['room_id']) . '</div> ';
						$booking_info .= '<div class="tourmaster-tail">' . sprintf(_n('%d Room', '%d Rooms', $booking_detail['room_amount'], 'tourmaster'), $booking_detail['room_amount']) . '</div>';
						$booking_info .= '<div class="tourmaster-tail">' . tourmaster_room_booking_duration_info($booking_detail['start_date'], $booking_detail['end_date'], false) . '</div>';
						$booking_info .= '</div>';
					}
				
					$raw_message = str_replace('{booking-info}', $booking_info, $raw_message);

					// customer name
					$customer_name  = '<strong>' . $contact_info['first_name'] . ' ' . $contact_info['last_name'] . '</strong>';
					$raw_message = str_replace('{customer-name}', $customer_name, $raw_message);

					// additional notes
					if( !empty($contact_info['additional_notes']) ){
						$raw_message = str_replace('{customer-note}', $contact_info['additional_notes'], $raw_message);
					}else{
						$raw_message = str_replace('{customer-note}', '', $raw_message);
					}

					// custom contact info
					$contact_fields = tourmaster_room_payment_contact_form_fields();
					foreach( $contact_fields as $cfield_slug => $cfield_settigns ){
						if( !empty($contact_info[$cfield_slug]) ){
							$raw_message = str_replace('{' . $cfield_slug . '}', $contact_info[$cfield_slug], $raw_message);
						}else{
							$raw_message = str_replace('{' . $cfield_slug . '}', '', $raw_message);
						}
					}

					if( !empty($result->total_price) ){
						$total_price  = '<div class="tourmaster-mail-payment-price" style="font-size: 16px; font-weight: 600; margin: 20px 0px 25px;" >';
						$total_price .= '<span class="tourmaster-head" >' . esc_html__('Total Price :', 'tourmaster') . '</span> ';
						$total_price .= '<span class="payment-method" >' . tourmaster_money_format($result->total_price) . '</span>';
						$total_price .= '</div>';
						$raw_message = str_replace('{total-price}', $total_price, $raw_message);
					}else{
						$raw_message = str_replace('{total-price}', '', $raw_message);
					}

					// order number
					$order_number  = '<div class="tourmaster-mail-order-info" style="font-style: italic; margin-bottom: 5px;" >';
					$order_number .= '<span class="tourmaster-head" >' . esc_html__('Order Number :', 'tourmaster') . '</span> ';
					$order_number .= '<span class="tourmaster-tail" >#' . $result->id . '</span>';
					$order_number .= '</div>';
					$raw_message = str_replace('{order-number}', $order_number, $raw_message);

					// admin transaction url
					$raw_message = str_replace('{admin-transaction-link}', admin_url('admin.php?page=tourmaster_room_order&single=' . $result->id), $raw_message);
					
					// invoice url
					$user_url = tourmaster_get_template_url('user');
					$invoice_url = add_query_arg(array(
						'page_type' => 'room-invoices',
						'sub_page' => 'single',
						'id' => $result->id
					), $user_url);
					$raw_message = str_replace('{invoice-link}', $invoice_url, $raw_message);				

					// payment url
					$user_url = tourmaster_get_template_url('user');
					$invoice_url = add_query_arg(array(
						'page_type' => 'room-booking',
						'sub_page' => 'single',
						'id' => $result->id
					), $user_url);
					$raw_message = str_replace('{payment-link}', $invoice_url, $raw_message);

				}else if( !empty($user_id) ){

					$customer_name  = '<strong>' . tourmaster_get_user_meta($user_id) . '</strong>';
					$raw_message = str_replace('{customer-name}', $customer_name, $raw_message);

					$user_email = tourmaster_get_user_meta($user_id, 'email');
					$raw_message = str_replace('{customer-email}', $user_email, $raw_message);

					$user_phone = tourmaster_get_user_meta($user_id, 'phone');
					$user_phone = empty($user_phone)? ' -': $user_phone; 
					$raw_message = str_replace('{customer-phone}', $user_phone, $raw_message);
				}

				// for extra settings
				if( !empty($settings['custom']) ){
					$field_slugs = array(
						'payment_method' => 'payment-method',
						'transaction_id' => 'transaction-id',
						'submission_date' => 'payment-date'
					);
					foreach( $settings['custom'] as $field_key => $field_value ){
						$temp_title = '';
						$field_slug = empty($field_slugs[$field_key])? $field_key: $field_slugs[$field_key];
						if( $field_key == 'payment_method' ){
							$temp_title = esc_html__('Payment Method :', 'tourmaster');
							if( $field_value == 'paypal' ){
								$field_value = esc_html__('Paypal', 'tourmaster');
							}else if( $field_value == 'hipayprofessional' ){
								$field_value = esc_html__('Hipay Professional', 'tourmaster');
							}else if( $field_value == 'receipt' ){
								$field_value = esc_html__('Receipt Submission', 'tourmaster');
							}else{
								$field_value = esc_html__('Credit Card', 'tourmaster');
							}
						}else if( $field_key == 'transaction_id' ){
							$temp_title = esc_html__('Transaction ID :', 'tourmaster');
						}else if( $field_key == 'submission_date' ){
							$temp_title = esc_html__('Payment Date :', 'tourmaster');
						}else if( $field_key == 'amount' ){
							$temp_title = esc_html__('Amount :', 'tourmaster');
						}

						if( !empty($field_value) ){
							if( $field_key == 'amount' ){
								$temp_content = '';
								
								if( !empty($field_value) ){
									$temp_content .= '<div class="tourmaster-mail-payment-info" style="font-weight: 600; margin-bottom: 5px;" >';
									$temp_content .= '<span class="tourmaster-head" >' . esc_html__('Amount :', 'tourmaster') . ' </span>';
									$temp_content .= '<span class="payment-method" >' . tourmaster_money_format($field_value) . '</span>';	
									$temp_content .= '</div>';
								}

								if( !empty($settings['custom']['service_fee_rate']) && !empty($settings['custom']['service_fee']) ){
									$temp_content .= '<div class="tourmaster-mail-payment-info" style="font-weight: 600; margin-bottom: 5px;" >';
									$temp_content .= '<span class="tourmaster-head" >' . sprintf(esc_html__('Service Fee (%s%%)', 'tourmaster'), $settings['custom']['service_fee_rate']) . '</span> ';
									$temp_content .= '<span class="payment-method" >' . tourmaster_money_format($settings['custom']['service_fee']) . '</span>';	
									$temp_content .= '</div>';
									
									if( !empty($settings['custom']['paid_amount']) ){
										$temp_content .= '<div class="tourmaster-mail-payment-info" style="font-weight: 600; margin-bottom: 5px;" >';
										$temp_content .= '<span class="tourmaster-head" >' . esc_html__('Paid Amount :', 'tourmaster') . '</span> ';
										$temp_content .= '<span class="payment-method" >' . tourmaster_money_format($settings['custom']['paid_amount']) . '</span>';	
										$temp_content .= '</div>';
									}
								}
								
							}else{
								$temp_content  = '<div class="tourmaster-mail-payment-info" style="font-weight: 600; margin-bottom: 5px;" >';
								if( !empty($temp_title) ){
									$temp_content .= '<span class="tourmaster-head" >' . $temp_title . '</span> ';
								}
								$temp_content .= '<span class="payment-method" >' . $field_value . '</span>';
								$temp_content .= '</div>';
								$field_value = $temp_content;
							}
							
						}
						$raw_message = str_replace('{' . $field_slug . '}', $temp_content, $raw_message);
					}
				}else{
					$raw_message = str_replace('{payment-method}', '', $raw_message);
					$raw_message = str_replace('{transaction-id}', '', $raw_message);
					$raw_message = str_replace('{payment-date}', '', $raw_message);
					$raw_message = str_replace('{submission-date}', '', $raw_message);
					$raw_message = str_replace('{submission-amount}', '', $raw_message);
				}

				// profile page url
				$raw_message = str_replace('{profile-page-link}', tourmaster_get_template_url('user'), $raw_message);
				
				// html
				$raw_message = str_replace('{header}', '<h3 style="font-size: 17px; margin-bottom: 25px; font-weight: 600; margin-top: 0px; color: #515355" >', $raw_message);
				$raw_message = str_replace('{/header}', '</h3>', $raw_message);
				$raw_message = str_replace('{spaces}', '<div class="tourmaster-mail-spaces" style="margin-bottom: 25px;" ></div>', $raw_message);
				$raw_message = str_replace('{divider}', '<div class="tourmaster-mail-divider" style="border-bottom-width: 1px; border-bottom-style: solid; margin-bottom: 30px; margin-top: 30px; border-color: #d7d7d7;" ></div>', $raw_message);

				$message = tourmaster_room_mail_content($raw_message);

				// send the mail
				$mail_settings = array(
					'title' => $mail_title,
					'message' => $message
				);
				
				if( strpos($type, 'admin') === 0 ){
					$mail_settings['recipient'] = tourmaster_get_option('room_general', 'admin-email-address');
					$mail_settings['reply-to'] = $user_email;
				}else if( !empty($user_email) ){
					$mail_settings['recipient'] = $user_email;
				}

				if( !empty($mail_settings['recipient']) ){
					tourmaster_room_mail($mail_settings);
				}
			}

		} // tourmaster_mail_notification
	}

	/*
	if( !is_admin() ){
		add_action('init', 'test');
	}
	
	function test(){
		global $tourmaster_debug;
		$tourmaster_debug = true;

		$tid = 20;
		tourmaster_room_send_email_invoice($tid); return;

		global $wpdb;
		$sql  = "SELECT * FROM {$wpdb->prefix}tourmaster_room_order ";
		$sql .= $wpdb->prepare("WHERE id = %d ", $tid);
		$result = $wpdb->get_row($sql);

		$payment_infos = empty($result->payment_info)? array(): json_decode($result->payment_info, true);
		$settings = array('custom' => $payment_infos[0]);

		tourmaster_room_mail_notification('deposit-payment-made-mail', $tid, '', $settings);
	}
	*/

	if( !function_exists('tourmaster_room_send_email_invoice') ){
		function tourmaster_room_send_email_invoice( $tid ){

			$enable_email_invoice = tourmaster_get_option('room_general', 'enable-customer-invoice', 'enable');
			if( $enable_email_invoice == 'disable' ) return;

			global $wpdb;
			$sql  = "SELECT * FROM {$wpdb->prefix}tourmaster_room_order ";
			$sql .= $wpdb->prepare("WHERE id = %d ", $tid);
			$result = $wpdb->get_row($sql);
			if( empty($result) ) return;

			ob_start();
			echo '<div style="background: #fff; padding: 50px 50px; font-size: 14px; " >'; // tourmaster-invoice-wrap

			$invoice_logo = tourmaster_get_option('room_general', 'invoice-logo');
			$contact_detail = empty($result->contact_info)? array(): json_decode($result->contact_info, true);
			$billing_prefix = (empty($contact_detail['required-billing']) || $contact_detail['required-billing'] == 'false')? '': 'billing_';

			echo '<div style="margin-bottom: 60px; color: #121212;" >'; // tourmaster-invoice-head
			echo '<div style="float: left;" >'; // tourmaster-invoice-head-left
			echo '<div style="margin-bottom: 35px;" >'; // tourmaster-invoice-logo
			if( empty($invoice_logo) ){
				echo tourmaster_get_image(TOURMASTER_URL . '/images/invoice-logo.png');
			}else{
				echo tourmaster_get_image($invoice_logo);
			}
			echo '</div>'; // tourmaster-invoice-logo
			echo '<div style="font-size: 16px; font-weight: bold; margin-bottom: 5px; text-transform: uppercase;" >' . esc_html__('Invoice ID :', 'tourmaster') . ' #' . $result->id . '</div>'; // tourmaster-invoice-id
			echo '<div>' . esc_html__('Invoice date :', 'tourmaster') . ' ' . tourmaster_date_format($result->booking_date) . '</div>'; // tourmaster-invoice-date
			echo '<div style="margin-top: 34px;" >'; // tourmaster-invoice-receiver
			echo '<div style="font-size: 16px; font-weight: bold; text-transform: uppercase; margin-bottom: 5px;" >' . esc_html__('Invoice To', 'tourmaster') . '</div>'; // tourmaster-invoice-receiver-head
			echo '<div>'; // tourmaster-invoice-receiver-info
			$customer_address = tourmaster_get_option('room_general', 'invoice-customer-address');
			if( empty($customer_address) ){
				echo '<span style="display: block; margin-bottom: 4px;" >' . $contact_detail[$billing_prefix . 'first_name'] . ' ' . $contact_detail[$billing_prefix . 'last_name'] . '</span>'; // tourmaster-invoice-receiver-name
				echo '<span style="display: block; max-width: 250px;" >' . (empty($contact_detail[$billing_prefix . 'contact_address'])? '': $contact_detail[$billing_prefix . 'contact_address']) . '</span>'; // tourmaster-invoice-receiver-address
			}else{
				echo tourmaster_content_filter(tourmaster_set_contact_form_data($customer_address, $contact_detail, $billing_prefix));
			}
			echo '</div>';
			echo '</div>';
			echo '</div>'; // tourmaster-invoice-head-left
			
			$company_name = tourmaster_get_option('room_general', 'invoice-company-name', '');
			$company_info = tourmaster_get_option('room_general', 'invoice-company-info', '');
			echo '<div style="float: right; padding-top: 10px; width: 180px;" >'; // tourmaster-invoice-head-right
			echo '<div>'; // tourmaster-invoice-company-info
			echo '<div style="font-size: 16px; font-weight: bold; margin-bottom: 20px;" >' . $company_name . '</div>'; // tourmaster-invoice-company-name
			echo '<div>' . tourmaster_content_filter($company_info) . '</div>'; // tourmaster-invoice-company-info
			echo '</div>';
			echo '</div>'; // tourmaster-invoice-head-right
			echo '<div style="clear: both" ></div>';
			echo '</div>'; // tourmaster-invoice-head

			// price breakdown
			$booking_details = empty($result->booking_data)? array(): json_decode($result->booking_data, true);
			$price_breakdowns = empty($result->price_breakdown)? array(): json_decode($result->price_breakdown, true);
			echo '<div>'; // tourmaster-invoice-price-breakdown
			echo '<div style="padding: 18px 25px; font-size: 14px; font-weight: 700; text-transform: uppercase; color: #454545; background-color: #f3f3f3" >'; // tourmaster-invoice-price-head
			echo '<span style="width: 80%; float: left;" >' . esc_html__('Description', 'tourmaster') . '</span>'; // tourmaster-head
			echo '<span style="overflow: hidden;" >' . esc_html__('Total', 'tourmaster') . '</span>'; // tourmaster-tail
			echo '</div>'; // tourmaster-invoice-price-head
			echo tourmaster_get_room_invoice_price_email($booking_details, $price_breakdowns);
			echo '</div>'; // tourmaster-invoice-price-breakdown

			if( !empty($result->payment_info) ){
				$payment_infos = json_decode($result->payment_info, true);

				if( !empty($payment_infos) ){
					echo '<div style="padding: 22px 35px; margin-top: 40px; background: #f3f3f3; color: #454545" >'; // tourmaster-invoice-payment-info
					foreach( $payment_infos as $payment_info ){
						echo '<div style="margin-bottom: 15px;" >';
						echo '<div style="float: left; margin-right: 60px; text-transform: uppercase;" >'; // tourmaster-invoice-payment-info-item
						echo '<div style="font-weight: 800; margin-bottom: 5px;" >' . esc_html__('Payment Method', 'tourmaster') . '</div>'; // tourmaster-head
						echo '<div>'; // tourmaster-tail
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
							echo '<div style="float: left; margin-right: 60px; text-transform: uppercase;" >';
							echo '<div style="font-weight: 800; margin-bottom: 5px;" >' . esc_html__('Amount', 'tourmaster') . '</div>';
							echo '<div>' . tourmaster_money_format($payment_info['amount']) . '</div>';	
							echo '</div>'; // tourmaster-invoice-payment-info-item
						}
						if( !empty($payment_info['service_fee']) ){
							echo '<div style="float: left; margin-right: 60px; text-transform: uppercase;" >';
							echo '<div style="font-weight: 800; margin-bottom: 5px;" >' . esc_html__('Service Fee', 'tourmaster') . '</div>';
							echo '<div>' . tourmaster_money_format($payment_info['service_fee']) . '</div>';	
							echo '</div>'; // tourmaster-invoice-payment-info-item
			
							if( !empty($payment_info['paid_amount']) ){
								echo '<div style="float: left; margin-right: 60px; text-transform: uppercase;" >';
								echo '<div style="font-weight: 800; margin-bottom: 5px;" >' . esc_html__('Paid Amount', 'tourmaster') . '</div>';
								echo '<div>' . tourmaster_money_format($payment_info['paid_amount']) . '</div>';	
								echo '</div>'; // tourmaster-invoice-payment-info-item
							}
						}

						echo '<div style="float: left; margin-right: 60px; text-transform: uppercase;" >'; // tourmaster-invoice-payment-info-item
						echo '<div style="font-weight: 800; margin-bottom: 5px;" >' . esc_html__('Date', 'tourmaster') . '</div>'; // tourmaster-head
						echo '<div>' . tourmaster_date_format($payment_info['submission_date']) . '</div>'; // tourmaster-tail
						echo '</div>'; // tourmaster-invoice-payment-info-item

						if( !empty($payment_info['transaction_id']) ){
							echo '<div style="float: left; margin-right: 60px; text-transform: uppercase;" >'; // tourmaster-invoice-payment-info-item
							echo '<div style="font-weight: 800; margin-bottom: 5px;" >' . esc_html__('Transaction ID', 'tourmaster') . '</div>'; // tourmaster-head
							echo '<div>' . $payment_info['transaction_id'] . '</div>'; // tourmaster-tail
							echo '</div>'; // tourmaster-invoice-payment-info-item
						}

						echo '<div style="clear: both" ></div>';
						echo '</div>';
					}
					echo '</div>';
				}
			}

			echo '</div>'; // tourmaster-invoice-wrap

			$content = ob_get_contents();
			ob_end_clean();
			
			// send the mail
			$mail_settings = array(
				'title' => sprintf(esc_html__('Invoice From %s', 'tourmaster'), tourmaster_get_option('room_general', 'system-email-name', 'WORDPRESS')), 
				'message' => tourmaster_room_mail_content($content, true, true, array('width' => '1210', 'padding' => '0px 1px', 'no-filter' => true)),
				'recipient' => $contact_detail[$billing_prefix . 'email']
			);
			
			if( !empty($mail_settings['recipient']) ){
				tourmaster_room_mail($mail_settings);
			}

		} // tourmaster_send_email_invoice
	}

	if( !function_exists('tourmaster_get_room_invoice_price_email') ){
		function tourmaster_get_room_invoice_price_email( $booking_details, $price_breakdowns ){
			$ret  = '<div>'; // tourmaster-invoice-price clearfix

			for( $i = 0; $i < sizeof($booking_details); $i++ ){
                $booking_detail = $booking_details[$i];
                $price_breakdown = $price_breakdowns[$i];

                for( $j = 0; $j < $booking_detail['room_amount']; $j++ ){
					$ret .= '<div style="padding: 18px 25px; border-bottom-width: 1px; border-bottom-style: solid; border-color: #e1e1e1;" >'; // tourmaster-invoice-price-item
					$ret .= '<span style="width: 80%; float: left; color: #7b7b7b;" >'; // tourmaster-head
					$ret .= '<span style="display: block; font-size: 15px; margin-bottom: 2px;" >'; // tourmaster-head-title
					if( $booking_detail['room_amount'] > 1 ){
                        $ret .= sprintf(esc_html__('%s : Room %d', 'tourmaster'), get_the_title($booking_detail['room_id']), ($j+1));
                    }else{
                        $ret .= get_the_title($booking_detail['room_id']);
                    }
					$ret .= ' (';
                    $ret .= sprintf(_n('%d Adult', '%d Adults', $booking_detail['adult'][$j], 'tourmaster'), $booking_detail['adult'][$j]) . ' '; 
                    $ret .= sprintf(_n('%d Children', '%d Childrens', $booking_detail['children'][$j], 'tourmaster'), $booking_detail['children'][$j]) . ' '; 
                    $ret .= ')';
					$ret .= '</span>';
					$ret .= '<span style="display: block; font-size: 13px;" >'; // tourmaster-head-caption
					$ret .= tourmaster_room_booking_duration_info($booking_detail['start_date'], $booking_detail['end_date'], false, false);
					$ret .= '</span>';
					$ret .= '</span>'; // tourmaster-head
					$ret .= '<span style="color: #1e1e1e; font-size: 16px;" >'; // tourmaster-tail
					$ret .= tourmaster_money_format($price_breakdown['room-prices'][$j]);
					$ret .= '</span>';

					if( !empty($price_breakdown['room-service-prices'][$j]) ){
						$ret .= '<div style="clear: both; margin-bottom: 10px;" ></div>'; // tourmaster-separator
						$ret .= '<span style="width: 80%; float: left; color: #7b7b7b;" >'; // tourmaster-head
						$ret .= '<span style="display: block; font-size: 15px; margin-bottom: 2px;" >' . esc_html__('Additional Services', 'tourmaster') . '</span>'; // tourmaster-head-title
						$ret .= '</span>';
						$ret .= '<span style="color: #1e1e1e; font-size: 16px;" >' . tourmaster_money_format($price_breakdown['room-service-prices'][$j]) . '</span>'; // tourmaster-tail
						$ret .= '<div style="clear: both;" ></div>'; // clearfix
					}
					$ret .= '</div>';
				}
			}

			// coupon
			if( !empty($price_breakdowns['coupon']) && $price_breakdowns['coupon']['type'] == 'before-tax' ){
				$ret .= '<div style="padding: 18px 25px; border-bottom-width: 1px; border-bottom-style: solid; border-color: #e1e1e1;" >'; // tourmaster-invoice-price-sub-total
				$ret .= '<span style="color: #7b7b7b; float: left; margin-left: 55%; width: 25%; font-size: 15px;" >' . esc_html__('Coupon Discount', 'tourmaster') . '</span>'; // tourmaster-head
				$ret .= '<span style="color: #1e1e1e; display: block; overflow: hidden; font-size: 16px;" >'; // tourmaster-tail
				$ret .= '- ' . tourmaster_money_format($price_breakdowns['coupon']['discount-price']);
				$ret .= '</span>';
				$ret .= '<div style="clear: both;" ></div>';
				$ret .= '</div>';
			}

			// total
			$ret .= '<div style="padding: 18px 25px; border-bottom-width: 1px; border-bottom-style: solid; border-color: #e1e1e1;" >'; // tourmaster-invoice-price-sub-total
			$ret .= '<span style="color: #7b7b7b; float: left; margin-left: 55%; width: 25%; font-size: 15px;" >' . esc_html__('Total Price', 'tourmaster') . '</span>'; // tourmaster-head
			$ret .= '<span style="color: #1e1e1e; display: block; overflow: hidden; font-size: 16px;" >'; // tourmaster-tail
			$ret .= tourmaster_money_format($price_breakdowns['total-price']);
			$ret .= '</span>';
			$ret .= '<div style="clear: both;" ></div>';
			$ret .= '</div>';

			if( !empty($price_breakdowns['tax-price']) ){
				$ret .= '<div style="padding: 18px 25px; border-bottom-width: 1px; border-bottom-style: solid; border-color: #e1e1e1;" >'; // tourmaster-invoice-price-tax
				$ret .= '<span style="color: #7b7b7b; float: left; margin-left: 55%; width: 25%; font-size: 15px;" >' . esc_html__('Tax', 'tourmaster') . '</span>'; // tourmaster-head
				$ret .= '<span style="color: #1e1e1e; display: block; overflow: hidden; font-size: 16px;" >'; // tourmaster-tail
				$ret .= tourmaster_money_format($price_breakdowns['tax-price']);
				$ret .= '</span>';
				$ret .= '<div style="clear: both;" ></div>';
				$ret .= '</div>';
			}

			// coupon
			if( !empty($price_breakdowns['coupon']) && $price_breakdowns['coupon']['type'] == 'after-tax' ){
				$ret .= '<div style="padding: 18px 25px; border-bottom-width: 1px; border-bottom-style: solid; border-color: #e1e1e1;" >'; // tourmaster-invoice-price-sub-total
				$ret .= '<span style="color: #7b7b7b; float: left; margin-left: 55%; width: 25%; font-size: 15px;" >' . esc_html__('Coupon Discount', 'tourmaster') . '</span>'; // tourmaster-head
				$ret .= '<span style="color: #1e1e1e; display: block; overflow: hidden; font-size: 16px;" >'; // tourmaster-tail
				$ret .= '- ' . tourmaster_money_format($price_breakdowns['coupon']['discount-price']);
				$ret .= '</span>';
				$ret .= '<div style="clear: both;" ></div>';
				$ret .= '</div>';
			}
			$ret .= '<div style="clear: both;" ></div>';
			$ret .= '</div>'; // tourmaster-invoice-price

			$ret .= '<div style="font-weight: bold; padding: 18px 25px; border-width: 1px 0px 2px; border-style: solid; border-color: #e1e1e1;" >'; // tourmaster-invoice-total-price
			$ret .= '<span style="float: left; margin-left: 55%; width: 25%; font-size: 15px;" >' . esc_html__('Grand Total Price', 'tourmaster') . '</span> '; // tourmaster-head
			$ret .= '<span style="display: block; overflow: hidden; font-size: 16px;" >' . tourmaster_money_format($price_breakdowns['grand-total-price']) . '</span>'; // tourmaster-tail
			$ret .= '</div>'; // tourmaster-invoice-total-price

			return $ret;
		} // tourmaster_get_tour_invoice_price
	}