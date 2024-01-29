<?php
	/*	
	*	Tourmaster Plugin
	*	---------------------------------------------------------------------
	*	for payment page
	*	---------------------------------------------------------------------
	*/

	if( !function_exists('tourmaster_get_payment_page') ){
		function tourmaster_get_payment_page($booking_detail, $is_single = false){

			$payment_style = tourmaster_get_option('general', 'payment-page-style', 'style-1');

			// initiate the variable
			if( !empty($booking_detail['tour-id']) && !empty($booking_detail['tour-date']) ){
				$tour_option = tourmaster_get_post_meta($booking_detail['tour-id'], 'tourmaster-tour-option');
				
				if( !empty($booking_detail['step']) && $booking_detail['step'] == '3' ){
					$booking_detail = tourmaster_set_mandatory_service( $tour_option, $booking_detail );
				}

				$date_price = tourmaster_get_tour_date_price($tour_option, $booking_detail['tour-id'], $booking_detail['tour-date']);
				$date_price = tourmaster_get_tour_date_price_package($date_price, $booking_detail);
			}

			// if booking data is invalid
			if( empty($date_price) ){
				$ret  = '<div class="tourmaster-tour-booking-error" >';
				$ret .= esc_html__('An error occurred while processing your request.','tourmaster');
				if( !empty($booking_detail['tour-id']) ){
					$ret .= '<br><br><a href="' . get_permalink($booking_detail['tour-id']) . '" >' . esc_html__('Back to Tour Page', 'tourmaster') . '</a>';
				}else{
					$ret .= '<br><br><a href="' . home_url('/') . '" >' . esc_html__('Back to Home Page', 'tourmaster') . '</a>';
				}
				$ret .= '</div>';

				return array( 'content' => $ret, 'sidebar' => '' );
			}

			// booking step 2
			if( empty($booking_detail['step']) || $booking_detail['step'] == '2' ){

				unset($booking_detail['payment_method']);

				return array(
					'content' => tourmaster_payment_traveller_form( $tour_option, $date_price, $booking_detail ) . 
								 tourmaster_payment_contact_form( $booking_detail ),
					'sidebar' => tourmaster_get_booking_bar_summary( $tour_option, $date_price, $booking_detail, true)
				);

			// booking step 3
			}else if( $booking_detail['step'] == '3' ){

				unset($booking_detail['payment_method']);

				return array(
					'content' => tourmaster_payment_service_form( $tour_option, $booking_detail ) . 
								 '<div class="tourmaster-summary-info-outer" >' . 
								 tourmaster_payment_contact_detail( $booking_detail ) . 
								 tourmaster_payment_traveller_detail( $tour_option, $booking_detail ) . 
								 '</div>' . 
								 ($payment_style == 'style-2'? '': tourmaster_payment_method($booking_detail)),
					'sidebar' => tourmaster_get_booking_bar_summary( $tour_option, $date_price, $booking_detail )
				);

			// booking step 4
			}else if( $booking_detail['step'] == '4' ){
				
				if( $is_single ){
					return array(
						'content' => tourmaster_payment_complete_delay(),
						'sidebar' => tourmaster_get_booking_bar_summary( $tour_option, $date_price, $booking_detail ),
						'cookie' => '' 
					);
				}else if( $booking_detail['payment-method'] == 'booking' ){
					$tour_price = tourmaster_get_tour_price($tour_option, $date_price, $booking_detail);
					if( $date_price['pricing-method'] == 'group' ){
						$traveller_amount = 1;
					}else{
						$traveller_amount = tourmaster_get_tour_people_amount($tour_option, $date_price, $booking_detail, 'all');
					}
					$package_group_slug = empty($date_price['group-slug'])? '': $date_price['group-slug'];
					
					if( empty($tour_option['payment-admin-approval']) ){
						$payment_admin_approval = tourmaster_get_option('payment', 'payment-admin-approval', 'disable');
					}else{
						$payment_admin_approval = $tour_option['payment-admin-approval'];
					}
					if( empty($booking_detail['tid']) && $payment_admin_approval == 'enable' ){
						$order_status = 'wait-for-approval';
					}else{
						$order_status = 'pending';
					}

					if( $tid = tourmaster_insert_booking_data($booking_detail, $tour_price, $traveller_amount, $package_group_slug, $order_status) ){
						
						$booking_detail['tid'] = $tid;

						if( is_user_logged_in() ){
							if( $order_status == 'wait-for-approval' ){
								tourmaster_mail_notification('booking-made-approval-mail', $tid);
								tourmaster_mail_notification('admin-booking-made-approval-mail', $tid);
							}else{
								tourmaster_mail_notification('booking-made-mail', $tid);
								tourmaster_mail_notification('admin-booking-made-mail', $tid);
							}
						}else{
							tourmaster_mail_notification('guest-booking-made-mail', $tid);
							tourmaster_mail_notification('admin-guest-booking-made-mail', $tid);
						}

						return array(
							'content' => tourmaster_payment_complete(),
							'sidebar' => tourmaster_get_booking_bar_summary($tour_option, $date_price, $booking_detail),
							'cookie' => '' 
						);
					}else{
						// cannot insert to database
					}

				}

			}

			return array();

		} // tourmaster_get_payment_page
	}

	// get booking bar summary
	if( !function_exists('tourmaster_get_booking_bar_summary') ){
		function tourmaster_get_booking_bar_summary( $tour_option, $date_price, $booking_detail, $editable = false ){

			$payment_style = tourmaster_get_option('general', 'payment-page-style', 'style-1');

			$ret  = '<div class="tourmaster-tour-booking-bar-summary" >';
			$ret .= '<h3 class="tourmaster-tour-booking-bar-summary-title" >' . get_the_title($booking_detail['tour-id']) . '</h3>';
			
			$ret .= '<div class="tourmaster-tour-booking-bar-summary-info tourmaster-summary-travel-date" >';
			$ret .= '<span class="tourmaster-head" >' . esc_html__('Travel Date', 'tourmaster') . ' : </span>';
			$ret .= '<span class="tourmaster-tail" >';
			$ret .= tourmaster_date_format($booking_detail['tour-date']);
			if( $editable ){
				$ret .= ' ( <span class="tourmaster-tour-booking-bar-date-edit" >' . esc_html__('edit', 'tourmaster') . '</span> )';
				$ret .= '<form class="tourmaster-tour-booking-temp" action="' . get_permalink($booking_detail['tour-id']) . '" method="post" ></form>';
			}
			$ret .= '</span>';
			$ret .= '</div>';		

			if( !empty($booking_detail['package']) ){
				$ret .= '<div class="tourmaster-tour-booking-bar-summary-info tourmaster-summary-package" >';
				$ret .= '<span class="tourmaster-head" >' . esc_html__('Package', 'tourmaster') . ' : </span>';
				$ret .= '<span class="tourmaster-tail" >' . $booking_detail['package'] . '</span>';
				$ret .= '</div>';				
			}

			if( $tour_option['tour-type'] == 'multiple' && !empty($tour_option['multiple-duration']) ){
				$tour_duration = intval($tour_option['multiple-duration']);
				$end_date = strtotime('+ ' . ($tour_duration - 1)  . ' day', strtotime($booking_detail['tour-date']));

				$ret .= '<div class="tourmaster-tour-booking-bar-summary-info tourmaster-summary-end-date" >';
				$ret .= '<span class="tourmaster-head" >' . esc_html__('End Date', 'tourmaster') . ' : </span>';
				$ret .= '<span class="tourmaster-tail" >' . tourmaster_date_format($end_date) . '</span>';
				$ret .= '</div>';

				$ret .= '<div class="tourmaster-tour-booking-bar-summary-info tourmaster-summary-period" >';
				$ret .= '<span class="tourmaster-head" >' . esc_html__('Period', 'tourmaster') . ' : </span>';
				$ret .= '<span class="tourmaster-tail" >' . $tour_duration . ' ';
				$ret .= ($tour_option['multiple-duration'] > 1)? esc_html__('Days', 'tourmaster'): esc_html__('Day', 'tourmaster');
				$ret .= '</span>';
				$ret .= '</div>';
			}

			// group price
			if( $date_price['pricing-method'] == 'group' ){

			// no room based
			}else if( $tour_option['tour-type'] == 'single' || $date_price['pricing-room-base'] == 'disable' ){

				$ret .= '<div class="tourmaster-tour-booking-bar-summary-people-wrap" >';

				// fixed price
				if( $date_price['pricing-method'] == 'fixed' ){
					$ret .= '<div class="tourmaster-tour-booking-bar-summary-people-amount" >';
					$ret .= '<span class="tourmaster-head" >' . esc_html__('Traveller', 'tourmaster') . ' : </span>';
					$ret .= '<span class="tourmaster-tail" >' . $booking_detail['tour-people'] . '</span>';
					$ret .= '</div>';

				// variable price
				}else{
					$ret .= '<div class="tourmaster-tour-booking-bar-summary-people tourmaster-variable clearfix" >';
					if( !empty($date_price['adult-price']) || $date_price['adult-price'] === '0' ){
						$ret .= '<div class="tourmaster-tour-booking-bar-summary-people-amount tourmaster-adult" >';
						$ret .= '<span class="tourmaster-head" >' . esc_html__('Adult', 'tourmaster') . ' : </span>';
						$ret .= '<span class="tourmaster-tail" >' . (empty($booking_detail['tour-adult'])? '0': $booking_detail['tour-adult']) . '</span>';
						$ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people-amount
					}
					if( !empty($date_price['male-price']) || $date_price['male-price'] === '0' ){
						$ret .= '<div class="tourmaster-tour-booking-bar-summary-people-amount tourmaster-male" >';
						$ret .= '<span class="tourmaster-head" >' . esc_html__('Male', 'tourmaster') . ' : </span>';
						$ret .= '<span class="tourmaster-tail" >' . (empty($booking_detail['tour-male'])? '0': $booking_detail['tour-male']) . '</span>';
						$ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people-amount
					}
					if( !empty($date_price['female-price']) || $date_price['female-price'] === '0' ){
						$ret .= '<div class="tourmaster-tour-booking-bar-summary-people-amount tourmaster-female" >';
						$ret .= '<span class="tourmaster-head" >' . esc_html__('Female', 'tourmaster') . ' : </span>';
						$ret .= '<span class="tourmaster-tail" >' . (empty($booking_detail['tour-female'])? '0': $booking_detail['tour-female']) . '</span>';
						$ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people-amount
					}
					if( !empty($date_price['children-price']) || $date_price['children-price'] === '0' ){
						$ret .= '<div class="tourmaster-tour-booking-bar-summary-people-amount tourmaster-children" >';
						$ret .= '<span class="tourmaster-head" >' . esc_html__('Children', 'tourmaster') . ' : </span>';
						$ret .= '<span class="tourmaster-tail" >' . (empty($booking_detail['tour-children'])? '0': $booking_detail['tour-children']) . '</span>';
						$ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people-amount
					}
					if( !empty($date_price['student-price']) || $date_price['student-price'] === '0' ){
						$ret .= '<div class="tourmaster-tour-booking-bar-summary-people-amount tourmaster-student" >';
						$ret .= '<span class="tourmaster-head" >' . esc_html__('Student', 'tourmaster') . ' : </span>';
						$ret .= '<span class="tourmaster-tail" >' . (empty($booking_detail['tour-student'])? '0': $booking_detail['tour-student']) . '</span>';
						$ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people-amount
					}
					if( !empty($date_price['infant-price']) || $date_price['infant-price'] === '0' ){
						$ret .= '<div class="tourmaster-tour-booking-bar-summary-people-amount tourmaster-infant" >';
						$ret .= '<span class="tourmaster-head" >' . esc_html__('Infant', 'tourmaster') . ' : </span>';
						$ret .= '<span class="tourmaster-tail" >' . (empty($booking_detail['tour-infant'])? '0': $booking_detail['tour-infant']) . '</span>';
						$ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people-amount
					}
					$ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people
				}
				$ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people-wrap

			// room based	
			}else{	

				$ret .= '<div class="tourmaster-tour-booking-bar-summary-room-wrap clearfix" >';

				for( $i = 0; $i < $booking_detail['tour-room']; $i++ ){
					$ret .= '<div class="tourmaster-tour-booking-bar-summary-room" >';
					$ret .= '<div class="tourmaster-tour-booking-bar-summary-room-text" >' . esc_html__('Room', 'tourmaster') . ' ' . ($i + 1) . '</div>';
					// fixed price
					if( $date_price['pricing-method'] == 'fixed' ){
						$ret .= '<div class="tourmaster-tour-booking-bar-summary-people-amount" >';
						$ret .= '<span class="tourmaster-head" >' . esc_html__('Traveller', 'tourmaster') . ' : </span>';
						$ret .= '<span class="tourmaster-tail" >' . $booking_detail['tour-people'][$i] . '</span>';
						$ret .= '</div>';

					// variable price
					}else{
						$ret .= '<div class="tourmaster-tour-booking-bar-summary-people tourmaster-variable clearfix" >';
						if( !empty($date_price['adult-price']) || $date_price['adult-price'] === '0' ){
							$ret .= '<div class="tourmaster-tour-booking-bar-summary-people-amount tourmaster-adult" >';
							$ret .= '<span class="tourmaster-head" >' . esc_html__('Adult', 'tourmaster') . ' : </span>';
							$ret .= '<span class="tourmaster-tail" >' . (empty($booking_detail['tour-adult'][$i])? '0': $booking_detail['tour-adult'][$i]) . '</span>';
							$ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people-amount
						}
						if( !empty($date_price['male-price']) || $date_price['male-price'] === '0' ){
							$ret .= '<div class="tourmaster-tour-booking-bar-summary-people-amount tourmaster-male" >';
							$ret .= '<span class="tourmaster-head" >' . esc_html__('Male', 'tourmaster') . ' : </span>';
							$ret .= '<span class="tourmaster-tail" >' . (empty($booking_detail['tour-male'][$i])? '0': $booking_detail['tour-male'][$i]) . '</span>';
							$ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people-amount
						}
						if( !empty($date_price['female-price']) || $date_price['female-price'] === '0' ){
							$ret .= '<div class="tourmaster-tour-booking-bar-summary-people-amount tourmaster-female" >';
							$ret .= '<span class="tourmaster-head" >' . esc_html__('Female', 'tourmaster') . ' : </span>';
							$ret .= '<span class="tourmaster-tail" >' . (empty($booking_detail['tour-female'][$i])? '0': $booking_detail['tour-female'][$i]) . '</span>';
							$ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people-amount
						}
						if( !empty($date_price['children-price']) || $date_price['children-price'] === '0' ){
							$ret .= '<div class="tourmaster-tour-booking-bar-summary-people-amount tourmaster-children" >';
							$ret .= '<span class="tourmaster-head" >' . esc_html__('Children', 'tourmaster') . ' : </span>';
							$ret .= '<span class="tourmaster-tail" >' . (empty($booking_detail['tour-children'][$i])? '0': $booking_detail['tour-children'][$i]) . '</span>';
							$ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people-amount
						}
						if( !empty($date_price['student-price']) || $date_price['student-price'] === '0' ){
							$ret .= '<div class="tourmaster-tour-booking-bar-summary-people-amount tourmaster-student" >';
							$ret .= '<span class="tourmaster-head" >' . esc_html__('Student', 'tourmaster') . ' : </span>';
							$ret .= '<span class="tourmaster-tail" >' . (empty($booking_detail['tour-student'][$i])? '0': $booking_detail['tour-student'][$i]) . '</span>';
							$ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people-amount
						}
						if( !empty($date_price['infant-price']) || $date_price['infant-price'] === '0' ){
							$ret .= '<div class="tourmaster-tour-booking-bar-summary-people-amount tourmaster-infant" >';
							$ret .= '<span class="tourmaster-head" >' . esc_html__('Infant', 'tourmaster') . ' : </span>';
							$ret .= '<span class="tourmaster-tail" >' . (empty($booking_detail['tour-infant'][$i])? '0': $booking_detail['tour-infant'][$i]) . '</span>';
							$ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people-amount
						}
						$ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people
					}
					$ret .= '</div>'; // tourmaster-tour-booking-bar-summary-room
				}
				$ret .= '</div>'; // tourmaster-tour-booking-bar-summary-room-wrap
			}		

			if( $editable && $payment_style == 'style-1' ){
				$ret .= '<div class="tourmaster-tour-booking-bar-coupon-wrap" >';
				$ret .= '<input type="text" class="tourmaster-tour-booking-bar-coupon" name="coupon-code" placeholder="' . esc_html__('Coupon Code', 'tourmaster') . '" ';
				$ret .= ' value="' . (empty($booking_detail['coupon-code'])? '': esc_attr($booking_detail['coupon-code'])) . '" ';
				$ret .= ' />';
				$ret .= '<a class="tourmaster-tour-booking-bar-coupon-validate" ';
				$ret .= ' data-ajax-url="' . esc_url(TOURMASTER_AJAX_URL) . '" ';
				$ret .= ' data-tour-id="' . esc_attr($booking_detail['tour-id']) . '" ';
				$ret .= ' data-tid="' . (empty($booking_detail['tid'])? '': esc_attr($booking_detail['tid'])) . '" ';
				$ret .= ' >' . esc_html__('Apply', 'tourmaster') . '</a>';
				$ret .= '<div class="tourmaster-tour-booking-coupon-message" ></div>';
				$ret .= '</div>';
			}

			$tour_price = tourmaster_get_tour_price($tour_option, $date_price, $booking_detail);
			$ret .= '<div class="tourmaster-tour-booking-bar-price-breakdown-wrap" >';
			$ret .= '<span class="tourmaster-tour-booking-bar-price-breakdown-link" id="tourmaster-tour-booking-bar-price-breakdown-link" >' . esc_html__('View Price Breakdown', 'tourmaster') . '</span>';
			$ret .= tourmaster_get_tour_price_breakdown($tour_price['price-breakdown']);
			$ret .= '</div>'; // tourmaster-tour-booking-bar-price-breakdown-wrap
		
			$ret .= '</div>'; // tourmaster-tour-booking-bar-summary

			// payment option
			$payment_infos = array();
			if( !empty($booking_detail['tid']) ){
				$result = tourmaster_get_booking_data(array('id' => $booking_detail['tid']), array('single' => true));
				$payment_infos = json_decode($result->payment_info, true);
				$payment_infos = tourmaster_payment_info_format($payment_infos, $result->order_status);
			}
			$price_settings = tourmaster_get_price_settings($booking_detail['tour-id'], $payment_infos, $tour_price['total-price'], $booking_detail['tour-date']);
			
			// set the payment type option
			$payment_type = empty($booking_detail['payment-type'])? 'full': $booking_detail['payment-type'];
			if( $payment_type == 'partial' && !empty($price_settings['full-payment']) && empty($price_settings['deposit-payment']) ){
				$payment_type = 'full';
			}	
			if( $payment_type == 'full' && empty($price_settings['full-payment']) && !empty($price_settings['deposit-payment']) ){
				$payment_type = 'partial';
			}	
			
			// woocommerce
			$enable_woocommerce_payment = tourmaster_get_option('payment', 'enable-woocommerce-payment', 'disable');
			if( $enable_woocommerce_payment == 'enable' ){
				$payment_type = 'full';
				unset($price_settings['deposit-payment']);
			} 

			$ret .= '<div class="tourmaster-tour-booking-bar-total-price-wrap ' . ($payment_type == 'partial'? 'tourmaster-deposit': '') . '" >';
			if( $enable_woocommerce_payment == 'disable' && empty($booking_detail['payment_method']) && !empty($price_settings['full-payment']) && !empty($price_settings['deposit-payment']) ){
				$ret .= '<div class="tourmaster-tour-booking-bar-deposit-option" >';
				$ret .= '<label class="tourmaster-deposit-payment-full" >';
				$ret .= '<input type="radio" name="payment-type" value="full" ' . ($payment_type == 'full'? 'checked': '') . ' />';
				$ret .= '<span class="tourmaster-content" >';
				$ret .= '<i class="icon_check_alt2" ></i>';
				$ret .= esc_html__('Pay Full Amount', 'tourmaster');
				$ret .= '</span>';
				$ret .= '</label>'; 

				$ret .= '<label class="tourmaster-deposit-payment-partial" >';
				$ret .= '<input type="radio" name="payment-type" value="partial" ' . ($payment_type == 'partial'? 'checked': '') . ' />';
				$ret .= '<span class="tourmaster-content" >';
				$ret .= '<i class="icon_check_alt2" ></i>';
				$ret .= sprintf(esc_html__('Pay %d%% Deposit', 'tourmaster'), $price_settings['next-deposit-percent']);
				$ret .= '</span>';
				$ret .= '</label>';
				$ret .= '</div>';
			}else{
				$ret .= '<input type="hidden" name="payment-type" value="' . esc_attr($payment_type) . '" />';
			}
			
			$ret .= '<div class="tourmaster-tour-booking-bar-total-price-container" >';
			if( $editable && $payment_style == 'style-2' ){
				$ret .= '<div class="tourmaster-tour-booking-bar-coupon-wrap" >';
				$ret .= '<span class="tourmaster-head" >' . esc_html__('Coupon Code :', 'tourmaster') . '</span>';
				$ret .= '<input type="text" class="tourmaster-tour-booking-bar-coupon" name="coupon-code" ';
				$ret .= ' value="' . (empty($booking_detail['coupon-code'])? '': esc_attr($booking_detail['coupon-code'])) . '" ';
				$ret .= ' />';
				$ret .= '<a class="tourmaster-tour-booking-bar-coupon-validate" ';
				$ret .= ' data-ajax-url="' . esc_url(TOURMASTER_AJAX_URL) . '" ';
				$ret .= ' data-tour-id="' . esc_attr($booking_detail['tour-id']) . '" ';
				$ret .= ' data-tid="' . (empty($booking_detail['tid'])? '': esc_attr($booking_detail['tid'])) . '" ';
				$ret .= ' >' . esc_html__('Apply', 'tourmaster') . '</a>';
				$ret .= '<div class="tourmaster-tour-booking-coupon-message" ></div>';
				$ret .= '</div>';
			}

			if( !empty($tour_price['paid-amount']) ){
				$ret .= '<span class="tourmaster-tour-booking-bar-total-price-info-title" >' . esc_html__('Total Amount', 'tourmaster') . '</span>';
				$ret .= '<span class="tourmaster-tour-booking-bar-total-price-info-content" >' . tourmaster_money_format($tour_price['total-price']) . '</span>';
				$ret .= '<div class="clear"></div>';
				$ret .= '<span class="tourmaster-tour-booking-bar-total-price-info-title" >' . esc_html__('Paid Amount', 'tourmaster') . '</span>';
				$ret .= '<span class="tourmaster-tour-booking-bar-total-price-info-content" >' . tourmaster_money_format($tour_price['paid-amount']) . '</span>';
				$ret .= '<div class="clear"></div>';
				$ret .= '<span class="tourmaster-tour-booking-bar-total-price-info-title" >' . esc_html__('Remaining Amount', 'tourmaster') . '</span>';
				$ret .= '<span class="tourmaster-tour-booking-bar-total-price-info-content" >' . tourmaster_money_format($tour_price['total-price'] - $tour_price['paid-amount']) . '</span>';
				if( !empty($tour_price['pay-amount-paypal-service-rate']) ){
					$ret .= '<div class="clear"></div>';
					$ret .= '<span class="tourmaster-tour-booking-bar-total-price-info-title" >' . sprintf(esc_html__('Paypal Fee (%s%%)', 'tourmaster'), $tour_price['pay-amount-paypal-service-rate']) . '</span>';
					$ret .= '<span class="tourmaster-tour-booking-bar-total-price-info-content" >' . tourmaster_money_format($tour_price['pay-amount-paypal-service-fee']) . '</span>';
				}
				if( !empty($tour_price['pay-amount-credit-card-service-rate']) ){
					$ret .= '<div class="clear"></div>';
					$ret .= '<span class="tourmaster-tour-booking-bar-total-price-info-title" >' . sprintf(esc_html__('Credit Card Fee (%s%%)', 'tourmaster'), $tour_price['pay-amount-credit-card-service-rate']) . '</span>';
					$ret .= '<span class="tourmaster-tour-booking-bar-total-price-info-content" >' . tourmaster_money_format($tour_price['pay-amount-credit-card-service-fee']) . '</span>';
				}
				$ret .= '<div class="clear" style="margin-bottom: 5px;" ></div>';

				$ret .= '<i class="icon_tag_alt" ></i>';
				$ret .= '<span class="tourmaster-tour-booking-bar-total-price-title" >' . esc_html__('Total Price', 'tourmaster') . '</span>';
				$ret .= '<span class="tourmaster-tour-booking-bar-total-price" >' . tourmaster_money_format($tour_price['pay-amount']) . '</span>';
			}else{
				if( !empty($tour_price['pay-amount-paypal-service-rate']) ){
					$divider = true;
					$ret .= '<div class="clear"></div>';
					$ret .= '<span class="tourmaster-tour-booking-bar-total-price-info-title" >' . sprintf(esc_html__('Paypal Fee (%s%%)', 'tourmaster'), $tour_price['pay-amount-paypal-service-rate']) . '</span>';
					$ret .= '<span class="tourmaster-tour-booking-bar-total-price-info-content" >' . tourmaster_money_format($tour_price['pay-amount-paypal-service-fee']) . '</span>';
				}
				if( !empty($tour_price['pay-amount-credit-card-service-rate']) ){
					$divider = true;
					$ret .= '<div class="clear"></div>';
					$ret .= '<span class="tourmaster-tour-booking-bar-total-price-info-title" >' . sprintf(esc_html__('Credit Card Fee (%s%%)', 'tourmaster'), $tour_price['pay-amount-credit-card-service-rate']) . '</span>';
					$ret .= '<span class="tourmaster-tour-booking-bar-total-price-info-content" >' . tourmaster_money_format($tour_price['pay-amount-credit-card-service-fee']) . '</span>';
				}
				if( !empty($divider) ){
					$ret .= '<div class="clear" style="margin-bottom: 5px;" ></div>';
				}	
				$ret .= '<i class="icon_tag_alt" ></i>';
				$ret .= '<span class="tourmaster-tour-booking-bar-total-price-title" >' . esc_html__('Total Price', 'tourmaster') . '</span>';
				$ret .= '<span class="tourmaster-tour-booking-bar-total-price" >' . tourmaster_money_format($tour_price['pay-amount']) . '</span>';
			}
			$ret .= '</div>';

			if( $payment_style == 'style-1' ){
				$ret .= '</div>';
			}

			// deposit display
			if( !empty($price_settings['deposit-payment']) ){

				// for price with paypal service fee
				$display_rate = true;
				$ret .= '<div class="tourmaster-tour-booking-bar-deposit-text ' . ($payment_type == 'partial'? 'tourmaster-active': '') . '" >';
				
				if( !empty($tour_price['deposit-price-raw']) ){
					$ret .= '<div class="tourmaster-tour-booking-bar-deposit-info clearfix" >';
					$ret .= '<span class="tourmaster-head" >' . sprintf(esc_html__('Deposit Amount (%s%%)', 'tourmaster'), $tour_price['deposit-rate']) . '</span>';
					$ret .= '<span class="tourmaster-tail" >' . tourmaster_money_format($tour_price['deposit-price-raw']) . '</span>';
					$ret .= '</div>';
					$display_rate = false;
				}

				if( !empty($tour_price['deposit-paypal-service-rate']) && !empty($tour_price['deposit-paypal-service-fee']) ){
					$ret .= '<div class="tourmaster-tour-booking-bar-deposit-info clearfix" >';
					$ret .= '<span class="tourmaster-head" >' . sprintf(esc_html__('%d%% Paypal Service Fee', 'tourmaster'), $tour_price['deposit-paypal-service-rate']) . '</span>';
					$ret .= '<span class="tourmaster-tail" >' . tourmaster_money_format($tour_price['deposit-paypal-service-fee']) . '</span>';
					$ret .= '</div>';
				}else if( !empty($tour_price['deposit-credit-card-service-rate']) && !empty($tour_price['deposit-credit-card-service-fee']) ){
					$ret .= '<div class="tourmaster-tour-booking-bar-deposit-info clearfix" >';
					$ret .= '<span class="tourmaster-head" >' . sprintf(esc_html__('%s%% Credit Card Service Fee', 'tourmaster'), $tour_price['deposit-credit-card-service-rate']) . '</span>';
					$ret .= '<span class="tourmaster-tail" >' . tourmaster_money_format($tour_price['deposit-credit-card-service-fee']) . '</span>';
					$ret .= '</div>';
				}

				if( $display_rate ){
					$ret .= '<span class="tourmaster-tour-booking-bar-deposit-title" >' . sprintf(esc_html__('%s%% Deposit ', 'tourmaster'), $tour_price['deposit-rate']) . '</span>';
				}else{
					$ret .= '<span class="tourmaster-tour-booking-bar-deposit-title" >' . esc_html__('Deposit Price', 'tourmaster') . '</span>';
				}
				$ret .= '<span class="tourmaster-tour-booking-bar-deposit-price" >' . tourmaster_money_format($tour_price['deposit-price']) . '</span>';
				// $ret .= '<span class="tourmaster-tour-booking-bar-deposit-caption" >' . esc_html__('*Pay the rest later', 'tourmaster') . '</span>';
				$ret .= '</div>';
			}

			if( $payment_style == 'style-2' ){
				$ret .= '</div>';
			}

			if( $editable ){
				$ret .= '<a class="tourmaster-tour-booking-continue tourmaster-button tourmaster-payment-step" data-step="3" >' . esc_html__('Next Step', 'tourmaster') . '</a>';
			}

			if( !empty($booking_detail['tid']) ){
				$ret .= '<script>window.tourmaster_transaction_id = "' . $booking_detail['tid'] . '"</script>';
			}
			if( !empty($tour_price['total-price']) ){
				$ret .= '<script>window.tourmaster_total_price = "' . $tour_price['total-price'] . '"</script>';
			}
			
			if( $payment_style == 'style-2' && !empty($booking_detail['step']) && $booking_detail['step'] == '3' ){
				$ret .= tourmaster_side_payment_method($booking_detail);
			}

			return $ret;
		}
	}

	// service form
	if( !function_exists('tourmaster_set_mandatory_service') ){
		function tourmaster_set_mandatory_service( $tour_option, $booking_detail ){
			if( !empty($tour_option['tour-service']) ){

				$booking_detail['service'] = empty($booking_detail['service'])? array(): $booking_detail['service']; 
				$booking_detail['service-amount'] = empty($booking_detail['service-amount'])? array(): $booking_detail['service-amount']; 

				foreach($tour_option['tour-service'] as $service_id){
					$pos = array_search($service_id, $booking_detail['service']);
					if( $pos !== false && !empty($booking_detail['service-amount'][$pos]) ){
						continue;
					}


					$service_option = get_post_meta($service_id, 'tourmaster-service-option', true);
					if( !empty($service_option['mandatory']) && $service_option['mandatory'] == 'enable' ){
						$booking_detail['service'][] = $service_id;
						$booking_detail['service-amount'][] = 1;
					}
				}
			}

			return $booking_detail;
		} // tourmaster_set_mandatory_service
	}
	if( !function_exists('tourmaster_payment_service_form') ){
		function tourmaster_payment_service_form( $tour_option, $booking_detail ){

			$ret = '';

			if( !empty($tour_option['tour-service']) ){
				if( !empty($booking_detail['service']) && !empty($booking_detail['service-amount']) ){
					$services = tourmaster_process_service_data($booking_detail['service'], $booking_detail['service-amount']);
				}

				$ret .= '<div class="tourmaster-payment-service-form-wrap" >';
				$ret .= '<h3 class="tourmaster-payment-service-form-title" >' . esc_html__('Please select your preferred additional services.', 'tourmaster') . '</h3>';
				
				$ret .= '<div class="tourmaster-payment-service-form-item-wrap" >';
				foreach($tour_option['tour-service'] as $service_id){
					$service_option = get_post_meta($service_id, 'tourmaster-service-option', true);
					if( empty($service_option) ) continue;

					$ret .= '<div class="tourmaster-payment-service-form-item" >';
					$ret .= '<input type="checkbox" name="service[]" value="' . esc_attr($service_id) . '" ';
					if( !empty($service_option['mandatory']) && $service_option['mandatory'] == 'enable' ){
						$ret .= 'checked onclick="return false;" ';
					}else{
						$ret .= (empty($services[$service_id]))? '': 'checked';	
					}
					$ret .= ' />';
					$ret .= '<span class="tourmaster-payment-service-form-item-title" >' . get_the_title($service_id) . '</span>';
				
					$ret .= '<span class="tourmaster-payment-service-form-price-wrap" >';
					$ret .= '<span class="tourmaster-head" >' . tourmaster_money_format($service_option['price'], -2) . '</span>';
					$ret .= '<span class="tourmaster-tail tourmaster-type-' . esc_attr($service_option['per']) . '" >';
					if( $service_option['per'] == 'person' ){
						$ret .= '<span class="tourmaster-sep" >/</span>' . esc_html__('Person', 'tourmaster');
						$ret .= '<input type="hidden" name="service-amount[]" value="1" />';
					}else if( $service_option['per'] == 'group' ){
						$ret .= '<span class="tourmaster-sep" >/</span>' . esc_html__('Group', 'tourmaster');
						$ret .= '<input type="hidden" name="service-amount[]" value="1" />';
					}else if( $service_option['per'] == 'room' ){
						$ret .= '<span class="tourmaster-sep" >/</span>' . esc_html__('Room', 'tourmaster');
						$ret .= '<input type="hidden" name="service-amount[]" value="1" />';
					}else if( $service_option['per'] == 'unit' ){
						$ret .= '<span class="tourmaster-sep" >x</span>' . '<input type="text" name="service-amount[]" '; 
						$ret .= ' value="' . (empty($services[$service_id])? '1': esc_attr($services[$service_id])) . '" ';
						if( !empty($service_option['max-unit']) ){
							$ret .= ' data-max-unit="' . esc_attr($service_option['max-unit']) . '" ';
						}
						$ret .= ' />';
					}
					$ret .= '</span>';
					$ret .= '</span>';
					$ret .= '</div>';
				}
				$ret .= '</div>';
				
				$ret .= '</div>';
			}

			return $ret;
		}	
	}
	if( !function_exists('tourmaster_process_service_data') ){
		function tourmaster_process_service_data( $services, $services_amount ){
			$ret = array();

			if( !empty($services) ){
				foreach( $services as $service_key => $service ){
					if( !empty($service) && !empty($services_amount[$service_key]) ){
						$ret[$service] = $services_amount[$service_key];
					}
				}
			}

			return $ret;
		}
	}

	// traveller form
	if( !function_exists('tourmaster_payment_traveller_title') ){
		function tourmaster_payment_traveller_title(){
			return apply_filters('tourmaster_traveller_title_types', array(
				'mr' => esc_html__('Mr', 'tourmaster'),
				'mrs' => esc_html__('Mrs', 'tourmaster'),
				'ms' => esc_html__('Ms', 'tourmaster'),
				'miss' => esc_html__('Miss', 'tourmaster'),
				'master' => esc_html__('Master', 'tourmaster'),
			));
		}
	}
	if( !function_exists('tourmaster_payment_traveller_input') ){
		function tourmaster_payment_traveller_input($tour_option, $booking_detail, $i, $required = true){

			$extra_class = '';
			
			$title = empty($booking_detail['traveller_title'][$i])? '': $booking_detail['traveller_title'][$i];
			$title_html = '';
			if( $tour_option['require-traveller-info-title'] == 'enable' ){
				$extra_class .= ' tourmaster-with-info-title';

				$title_html .= '<div class="tourmaster-combobox-wrap tourmaster-traveller-info-title" >';
				$title_html .= '<select name="traveller_title[]" >';
				$title_types = tourmaster_payment_traveller_title();
				foreach( $title_types as $title_slug => $title_type ){
					$title_html .= '<option value="' . esc_attr($title_slug) . '" ' . ($title_slug == $title? 'selected': '') . ' >' . $title_type . '</option>';
				}
				$title_html .= '</select>';
				$title_html .= '</div>';
			}
			$first_name = empty($booking_detail['traveller_first_name'][$i])? '': $booking_detail['traveller_first_name'][$i];
			$last_name = empty($booking_detail['traveller_last_name'][$i])? '': $booking_detail['traveller_last_name'][$i];
			$passport = empty($booking_detail['traveller_passport'][$i])? '': $booking_detail['traveller_passport'][$i];
			$data_required = $required? 'data-required': '';

			$ret  = '<div class="tourmaster-traveller-info-field clearfix ' . esc_attr($extra_class) . '">';
			$ret .= '<span class="tourmaster-head">' . esc_html__('Traveller', 'tourmaster') . ' ' . ($i + 1) . '</span>';
			$ret .= '<span class="tourmaster-tail clearfix">';
			$ret .= $title_html;
			$ret .= '<input type="text" class="tourmaster-traveller-info-input" name="traveller_first_name[]" value="' . esc_attr($first_name) . '" placeholder="' . esc_html__('First Name', 'tourmaster') . ($required? ' *': '') . '" ' . $data_required . ' />';
			$ret .= '<input type="text" class="tourmaster-traveller-info-input" name="traveller_last_name[]" value="' . esc_attr($last_name) . '" placeholder="' . esc_html__('Last Name', 'tourmaster') . ($required? ' *': '') . '" ' . $data_required . ' />';
			if( !empty($tour_option['require-traveller-passport']) && $tour_option['require-traveller-passport'] == 'enable' ){
				$ret .= '<input type="text" class="tourmaster-traveller-info-passport" name="traveller_passport[]" value="' . esc_attr($passport) . '" placeholder="' . esc_html__('Passport Number', 'tourmaster') . ($required? ' *': '') . '" ' . $data_required . ' />';
			}

			// additional traveller fields
			if( !empty($tour_option['additional-traveller-fields']) ){
				foreach( $tour_option['additional-traveller-fields'] as $field ){
					$field_value = empty($booking_detail['traveller_' . $field['slug']][$i])? '': $booking_detail['traveller_' . $field['slug']][$i];

					if( !empty($field['width']) ){
						$ret .= '<div style="float: left; width: ' . esc_attr($field['width']) . '" >';
					}
					$ret .= '<div class="tourmaster-traveller-info-custom" >';
					if( $field['type'] == 'combobox' ){	
						$count = 0;
						$ret .= '<div class="tourmaster-combobox-wrap" >';
						$ret .= '<select name="traveller_' . esc_attr($field['slug']) . '[]" ';
						$ret .= (!empty($field['required']) && $field['required'] == 'true')? 'data-required ': '';
						$ret .= ' >';
						foreach( $field['options'] as $option_val => $option_title ){ $count++;
							if( $count == 1 ){
								$ret .= '<option value="" >' . $option_title . ((!empty($field['required']) && $field['required'] == 'true')? ' *': '') . '</option>';
							}else{
								$ret .= '<option value="' . esc_attr($option_val) . '" ' . ($field_value == $option_val? 'selected': '') . ' >' . $option_title . '</option>';
							}
						}
						$ret .= '</select>';
						$ret .= '</div>';
					}else{
						$ret .= '<input type="text" ';
						$ret .= 'name="traveller_' . esc_attr($field['slug']) . '[]" ';
						$ret .= 'value="' . esc_attr($field_value) . '" ';
						$ret .= 'placeholder="' . esc_attr($field['title']) . ((!empty($field['required']) && $field['required'] == 'true')? ' *': '') . '" ';
						$ret .= (!empty($field['required']) && $field['required'] == 'true')? 'data-required ': '';
						$ret .= ' />';
					}
					$ret .= '</div>';
					if( !empty($field['width']) ){
						$ret .= '</div>';
					}
				}
			}

			$ret .= '</span>';
			$ret .= '</div>';

			return $ret;
		}
	}
	if( !function_exists('tourmaster_payment_traveller_form') ){
		function tourmaster_payment_traveller_form( $tour_option, $date_price, $booking_detail ){
			
			$ret  = '';

			// get additonal traveller fields
			if( empty($tour_option['additional-traveller-fields']) ){
				$tour_option['additional-traveller-fields'] = tourmaster_get_option('general', 'additional-traveller-fields', '');
			}
			if( !empty($tour_option['additional-traveller-fields']) ){
				$tour_option['additional-traveller-fields'] = tourmaster_read_custom_fields($tour_option['additional-traveller-fields']);
			}

			// traveller detail
			if( !empty($tour_option['require-each-traveller-info']) && $tour_option['require-each-traveller-info'] == 'enable' ){
				$tour_option['require-traveller-info-title'] = empty($tour_option['require-traveller-info-title'])? 'enable': $tour_option['require-traveller-info-title'];

				$ret .= '<div class="tourmaster-payment-traveller-info-wrap tourmaster-form-field tourmaster-with-border" >';
				// group 
				if( $date_price['pricing-method'] == 'group' ){
					$traveller_amount = $date_price['max-group-people'];
					
					if( $traveller_amount > 0 ){
						$ret .= '<h3 class="tourmaster-payment-traveller-info-title" ><i class="fa fa-suitcase" ></i>';
						$ret .= esc_html__('Traveller Details', 'tourmaster');
						$ret .= '</h3>';

						$required = true;
						for( $i = 0; $i < $traveller_amount; $i++ ){
							$ret .= tourmaster_payment_traveller_input($tour_option, $booking_detail, $i, $required);
							$required = false;
						}
					}

				// normal
				}else{

					$ret .= '<h3 class="tourmaster-payment-traveller-info-title" ><i class="fa fa-suitcase" ></i>';
					$ret .= esc_html__('Traveller Details', 'tourmaster');
					$ret .= '</h3>';

					$traveller_amount = tourmaster_get_tour_people_amount($tour_option, $date_price, $booking_detail);
					for( $i = 0; $i < $traveller_amount; $i++ ){
						$ret .= tourmaster_payment_traveller_input($tour_option, $booking_detail, $i);
					}
				}
				$ret .= '</div>';
			}

			return $ret;

		} // tourmaster_payment_traveller_form
	}
	if( !function_exists('tourmaster_payment_traveller_detail') ){
		function tourmaster_payment_traveller_detail( $tour_option, $booking_detail ){
			$tour_option['require-traveller-info-title'] = empty($tour_option['require-traveller-info-title'])? 'enable': $tour_option['require-traveller-info-title'];
			if( $tour_option['require-traveller-info-title'] == 'enable' ){
				$title_types = tourmaster_payment_traveller_title();
			}

			// get additonal traveller fields
			if( empty($tour_option['additional-traveller-fields']) ){
				$tour_option['additional-traveller-fields'] = tourmaster_get_option('general', 'additional-traveller-fields', '');
			}
			if( !empty($tour_option['additional-traveller-fields']) ){
				$tour_option['additional-traveller-fields'] = tourmaster_read_custom_fields($tour_option['additional-traveller-fields']);
			}

			$ret = '';

			if( !empty($tour_option['require-each-traveller-info']) && $tour_option['require-each-traveller-info'] == 'enable' && !empty($booking_detail['traveller_first_name']) ){
				$ret  = '<div class="tourmaster-payment-traveller-detail" >';
				$ret .= '<h3 class="tourmaster-payment-detail-title" ><i class="fa fa-file-text-o" ></i>';
				$ret .= esc_html__('Traveller Details', 'tourmaster');
				$ret .= '</h3>';
				for( $i = 0; $i < sizeof($booking_detail['traveller_first_name']); $i++ ){
					if( !empty($booking_detail['traveller_first_name'][$i]) || !empty($booking_detail['traveller_last_name'][$i]) ){
						$ret .= '<div class="tourmaster-payment-detail clearfix" >';
						$ret .= '<span class="tourmaster-head" >' . esc_html__('Traveller', 'tourmaster') . ' ' . ($i + 1) . ' :</span>';
						$ret .= '<span class="tourmaster-tail" >';
						if( $tour_option['require-traveller-info-title'] == 'enable' ){
							if( !empty($title_types[$booking_detail['traveller_title'][$i]]) ){
								$ret .= $title_types[$booking_detail['traveller_title'][$i]] . ' ';
							}	
						}
						$ret .= ($booking_detail['traveller_first_name'][$i] . ' ' . $booking_detail['traveller_last_name'][$i]);
						if( !empty($booking_detail['traveller_passport'][$i]) ){
							$ret .= '<br>' . esc_html__('Passport ID :', 'tourmaster') . ' ' . $booking_detail['traveller_passport'][$i];
						}

						if( !empty($tour_option['additional-traveller-fields']) ){
							foreach($tour_option['additional-traveller-fields'] as $field){
								if( !empty($booking_detail['traveller_' . $field['slug']][$i]) ){
									$ret .= '<br>' . $field['title'] . ' ' . $booking_detail['traveller_' . $field['slug']][$i];
								}
							}
						} 
						
						$ret .= '</span>';
						$ret .= '</div>';
					}
				}
				$ret .= '</div>'; // tourmaster-payment-traveller- detail-wrap
			}
			
			return $ret;
		} // tourmaster_payment_traveller_detail
	}

	// contact form
	if( !function_exists('tourmaster_get_payment_contact_form_fields') ){
		function tourmaster_get_payment_contact_form_fields( $post_id = '' ){

			if( !empty($post_id) ){
				$custom_fields = get_post_meta($post_id, 'tourmaster-contact-detail-fields', true);
			}
			if( empty($custom_fields) ){
				$custom_fields = tourmaster_get_option('general', 'contact-detail-fields', '');
			}

			if( empty($custom_fields) ){
				return array(
					'first_name' => array(
						'title' => esc_html__('First Name', 'tourmaster'),
						'type' => 'text',
						'required' => true
					),
					'last_name' => array(
						'title' => esc_html__('Last Name', 'tourmaster'),
						'type' => 'text',
						'required' => true
					),
					'email' => array(
						'title' => esc_html__('Email', 'tourmaster'),
						'type' => 'email',
						'required' => true
					),
					'phone' => array(
						'title' => esc_html__('Phone', 'tourmaster'),
						'type' => 'text',
						'required' => true
					),
					'country' => array(
						'title' => esc_html__('Country', 'tourmaster'),
						'type' => 'combobox',
						'required' => true, 
						'options' => tourmaster_get_country_list(),
						'default' => tourmaster_get_option('general', 'user-default-country', '')
					),
					'contact_address' => array(
						'title' => esc_html__('Address', 'tourmaster'),
						'type' => 'textarea'
					),
				);			
			}else{
				return tourmaster_read_custom_fields($custom_fields);
			}


		} // tourmaster_get_payment_contact_form_fields
	}
	if( !function_exists('tourmaster_set_contact_form_data') ){
		function tourmaster_set_contact_form_data( $content, $data, $prefix = '' ){
			
			foreach( $data as $slug => $value ){

				if( is_array($value) ){ continue; }

				if( !empty($prefix) ){
					if( strpos($slug, $prefix) === false ){
						continue;
					}
					$slug = str_replace($prefix, '', $slug);
				}

				$content = str_replace('{' . $slug . '}', $value, $content);
			}

			return $content;
		}
	}
	if( !function_exists('tourmaster_payment_contact_form') ){
		function tourmaster_payment_contact_form( $booking_detail ){

			// form field
			$contact_fields = tourmaster_get_payment_contact_form_fields($booking_detail['tour-id']);

			$ret  = '<div class="tourmaster-payment-contact-wrap tourmaster-form-field tourmaster-with-border" >';
			$ret .= '<h3 class="tourmaster-payment-contact-title" ><i class="fa fa-file-text-o" ></i>';
			$ret .= esc_html__('Contact Details', 'tourmaster');
			$ret .= '</h3>';
			foreach( $contact_fields as $field_slug => $contact_field ){
				$contact_field['echo'] = false;
				$contact_field['slug'] = $field_slug;

				$value = empty($booking_detail[$field_slug])? '': $booking_detail[$field_slug];

				$ret .= tourmaster_get_form_field($contact_field, 'contact', $value);
			}
			$ret .= '</div>';

			// billing address
			$ret .= '<div class="tourmaster-payment-billing-wrap tourmaster-form-field tourmaster-with-border" >';
			$ret .= '<h3 class="tourmaster-payment-billing-title" ><i class="fa fa-file-text-o" ></i>';
			$ret .= esc_html__('Billing Details', 'tourmaster');
			$ret .= '</h3>';

			$ret .= '<div class="tourmaster-payment-billing-copy-wrap" ><label>';
			$ret .= '<input type="checkbox" class="tourmaster-payment-billing-copy" id="tourmaster-payment-billing-copy" ></i>';
			$ret .= '<span class="tourmaster-payment-billing-copy-text" >' . esc_html__('The same as contact details', 'tourmaster') . '</span>';
			$ret .= '</label></div>'; // tourmaster-payment-billing-copy-wrap

			foreach( $contact_fields as $field_slug => $contact_field ){

				$contact_field['echo'] = false;
				$contact_field['slug'] = 'billing_' . $field_slug;
				$contact_field['data'] = array(
					'slug' => 'contact-detail',
					'value' => $field_slug
				);

				$value = empty($booking_detail['billing_' . $field_slug])? '': $booking_detail['billing_' . $field_slug];

		 		$ret .= tourmaster_get_form_field($contact_field, 'billing', $value);
			}
			$ret .= '</div>'; // tourmaster-payment-billing-wrap

			// additional notes
			$additional_notes = empty($booking_detail['additional_notes'])? '': $booking_detail['additional_notes'];
			$ret .= '<div class="tourmaster-payment-additional-note-wrap tourmaster-form-field tourmaster-with-border" >';
			$ret .= '<h3 class="tourmaster-payment-additional-note-title" ><i class="fa fa-file-text-o" ></i>';
			$ret .= esc_html__('Notes', 'tourmaster');
			$ret .= '</h3>';
			$ret .= '<div class="tourmaster-additional-note-field clearfix">';
			$ret .= '<span class="tourmaster-head">' . esc_html__('Additional Notes', 'tourmaster') . '</span>';
			$ret .= '<span class="tourmaster-tail clearfix">';
			$ret .= '<textarea name="additional_notes" >' . esc_textarea($additional_notes) . '</textarea>';
			$ret .= '</span>';
			$ret .= '</div>'; // additional-note-field
			$ret .= '</div>'; // tourmasster-payment-additional-note-wrap

			$ret .= '<div class="tourmaster-tour-booking-required-error tourmaster-notification-box tourmaster-failure" ';
			$ret .= 'data-default="' . esc_html__('Please fill all required fields.', 'tourmaster') . '" ';
			$ret .= 'data-email="' . esc_html__('Invalid E-Mail, please try again.', 'tourmaster') . '" ';
			$ret .= 'data-phone="' . esc_html__('Invalid phone number, please try again.', 'tourmaster') . '" ';
			$ret .= '></div>';
			$ret .= '<a class="tourmaster-tour-booking-continue tourmaster-button tourmaster-payment-step" data-step="3" >' . esc_html__('Next Step', 'tourmaster') . '</a>';

			return $ret;

		} // tourmaster_payment_contact_form
	}

	if( !function_exists('tourmaster_payment_contact_detail') ){
		function tourmaster_payment_contact_detail( $booking_detail ){

			// form field
			$contact_fields = tourmaster_get_payment_contact_form_fields($booking_detail['tour-id']);

			// contact detail
			$ret  = '<div class="tourmaster-payment-contact-detail-wrap clearfix tourmaster-item-rvpdlr" >';
			$ret .= '<div class="tourmaster-payment-detail-wrap tourmaster-payment-contact-detail tourmaster-item-pdlr" >';
			$ret .= '<h3 class="tourmaster-payment-detail-title" ><i class="fa fa-file-text-o" ></i>';
			$ret .= esc_html__('Contact Details', 'tourmaster');
			$ret .= '</h3>';
			foreach( $contact_fields as $slug => $contact_field ){
				$ret .= '<div class="tourmaster-payment-detail" >';
				$ret .= '<span class="tourmaster-head" >' . $contact_field['title'] . ' :</span>';
				$ret .= '<span class="tourmaster-tail" >' . (empty($booking_detail[$slug])? '-': $booking_detail[$slug]) . '</span>';
				$ret .= '</div>';
			}
			$ret .= '</div>'; // tourmaster-payment-detail-wrap

			// billing detail
			$ret .= '<div class="tourmaster-payment-detail-wrap tourmaster-payment-billing-detail tourmaster-item-pdlr" >';
			$ret .= '<h3 class="tourmaster-payment-detail-title" ><i class="fa fa-file-text-o" ></i>';
			$ret .= esc_html__('Billing Details', 'tourmaster');
			$ret .= '</h3>';
			foreach( $contact_fields as $slug => $contact_field ){
				$ret .= '<div class="tourmaster-payment-detail" >';
				$ret .= '<span class="tourmaster-head" >' . $contact_field['title'] . ' :</span>';
				$ret .= '<span class="tourmaster-tail" >' . (empty($booking_detail['billing_' . $slug])? '-': $booking_detail['billing_' . $slug]) . '</span>';
				$ret .= '</div>';
			}
			$ret .= '</div>'; // tourmaster-payment-detail-wrap
			$ret .= '</div>'; // tourmaster-payment-contact-detail-wrap

			// additional note
			if( !empty($booking_detail['additional_notes']) ){
				$ret .= '<div class="tourmaster-payment-detail-notes-wrap" >';
				$ret .= '<h3 class="tourmaster-payment-detail-title" ><i class="fa fa-file-text-o" ></i>';
				$ret .= esc_html__('Notes', 'tourmaster');
				$ret .= '</h3>';
				$ret .= '<div class="tourmaster-payment-detail" >';
				$ret .= '<span class="tourmaster-head" >' . esc_html__('Additional Notes', 'tourmaster') . ' :</span>';
				$ret .= '<span class="tourmaster-tail" >' . esc_html($booking_detail['additional_notes']) . '</span>';
				$ret .= '</div>'; // tourmaster-payment-detail
				$ret .= '</div>'; // tourmaster-payment-detail-wrap
				$ret .= '<div class="clear" ></div>';
			}

			return $ret;

		} // tourmaster_payment_contact_detail
	}

	if( !function_exists('tourmaster_side_payment_method') ){
		function tourmaster_side_payment_method($booking_detail){

			$ret  = '<div class="tourmaster-tour-booking-side-payment-wrap tourmaster-form-field" >';
			$ret .= '<h3 class="tourmaster-tour-booking-side-payment-title" >' . esc_html__('Payment Method', 'tourmaster') . '</h3>';
			
			// image display
			$credit_card_types = tourmaster_get_option('payment', 'accepted-credit-card-type', array());
			if( !empty($credit_card_types) ){
				$ret .= '<div class="tourmaster-payment-credit-card-type" >';
				foreach( $credit_card_types as $type ){
					$ret .= '<img src="' . esc_attr(TOURMASTER_URL) . '/images/' . esc_attr($type) . '.png" alt="' . esc_attr($type) . '" />';
										
				}
				$ret .= '</div>';	
			}

			// payment terms
			$our_term = tourmaster_get_option('payment', 'term-of-service-page', '#');
			$our_term = is_numeric($our_term)? get_permalink($our_term): $our_term; 
			$privacy = tourmaster_get_option('payment', 'privacy-statement-page', '#');
			$privacy = is_numeric($privacy)? get_permalink($privacy): $privacy; 
			$ret .= '<div class="tourmaster-payment-terms" >';
			$ret .= '<input type="checkbox" name="term-and-service" />';
			$ret .= sprintf(wp_kses(
				__('* I agree with <a href="%s" target="_blank">Terms of Service</a> and <a href="%s" target="_blank">Privacy Statement</a>.', 'tourmaster'), 
				array('a' => array( 'href'=>array(), 'target'=>array() ))
			), $our_term, $privacy);
			$ret .= '<div class="tourmaster-tour-booking-required-error tourmaster-notification-box tourmaster-failure" ';
			$ret .= 'data-default="' . esc_attr(esc_html__('Please agree to all the terms and conditions before proceeding to the next step.', 'tourmaster')) . '" ';
			$ret .= '></div>';
			$ret .= '</div>'; // tourmaster-payment-terms
			
			// payment type
			$tour_option = tourmaster_get_post_meta($booking_detail['tour-id'], 'tourmaster-tour-option');
			if( empty($tour_option['enable-payment']) ){
				$enable_payment = tourmaster_get_option('payment', 'enable-payment', 'enable');
			}else{
				$enable_payment = $tour_option['enable-payment'];
			}

			$payment_method = array();
			if( $enable_payment == 'enable' ){
				$enable_woocommerce_payment = tourmaster_get_option('payment', 'enable-woocommerce-payment', 'disable');
				if( $enable_woocommerce_payment == 'enable' ){
					$payment_method[] = 'woocommerce';

					$payment_methods = tourmaster_get_option('payment', 'payment-method', array('booking', 'paypal', 'credit-card'));
					if( in_array('booking', $payment_methods) ){
						$payment_method[] = 'booking';
                    }
				}else{
					$payment_method = tourmaster_get_option('payment', 'payment-method', array('booking', 'paypal', 'credit-card'));
				}
			}

			$woocommerce_enable = in_array('woocommerce', $payment_method);
			$paypal_enable = in_array('paypal', $payment_method);
			$credit_card_enable = in_array('credit-card', $payment_method);
			$hipayprofessional_enable = in_array('hipayprofessional', $payment_method);
			$custom_payment_enable = apply_filters('tourmaster_custom_payment_enable', false, $payment_method);

			if( $woocommerce_enable ){
				$ret .= '<div class="tourmaster-online-payment-method tourmaster-payment-woocommerce tourmaster-center-align" >';
				$ret .= '<a class="tourmaster-button tourmaster-pay-woocommerce" >';
				$ret .= esc_html__('Pay Now', 'tourmaster');
				$ret .= '</a>';
				$ret .= '</div>';
			}else{
				if( $paypal_enable || $credit_card_enable || $hipayprofessional_enable || $custom_payment_enable ){
					$ret .= '<div class="tourmaster-combobox-wrap">';
					$ret .= '<select class="tourmaster-payment-selection">';
					$ret .= $paypal_enable? '<option value="paypal">' . esc_html__('Paypal', 'tourmaster') . '</option>': '';

					if( $credit_card_enable ){
						$payment_attr = apply_filters('goodlayers_plugin_payment_attribute', array());
						$ret .= '<option value="' . esc_attr($payment_attr['type']) . '">' . esc_html__('Credit Card', 'tourmaster') . '</option>';
					}

					if( $hipayprofessional_enable ){
						$hipayprofessional_button_atts = apply_filters('tourmaster_hipayprofessional_button_atts', array());
						$ret .= '<option value="' . esc_attr($hipayprofessional_button_atts['type']) . '">' . esc_html__('Hipayprofessional', 'tourmaster') . '</option>';
					
					}
					$ret .= '</select>';
					$ret .= '</div>';
				}

				$ret .= '<a class="tourmaster-button tourmaster-blue tourmaster-pay-now">' . esc_html__('Pay Now', 'tourmaster') . '</a>';
				$ret .= '<div class="tourmaster-or">' . esc_html__('OR', 'tourmaster') . '</div>';
				$ret .= '<a class="tourmaster-button tourmaster-pay-later tourmaster-payment-step" data-name="payment-method" data-value="booking" data-step="4" >' . esc_html__('Book And Pay Later', 'tourmaster') . '</a>';
			}
			$ret .= '</div>';

			return $ret;
		}
	}

	if( !function_exists('tourmaster_payment_method') ){
		function tourmaster_payment_method( $booking_detail = array() ){
			$tour_option = tourmaster_get_post_meta($booking_detail['tour-id'], 'tourmaster-tour-option');
			
			if( empty($tour_option['enable-payment']) ){
				$enable_payment = tourmaster_get_option('payment', 'enable-payment', 'enable');
			}else{
				$enable_payment = $tour_option['enable-payment'];
			}

			$payment_method = array();
			if( $enable_payment == 'enable' ){
				$enable_woocommerce_payment = tourmaster_get_option('payment', 'enable-woocommerce-payment', 'disable');
				if( $enable_woocommerce_payment == 'enable' ){
					$payment_method[] = 'woocommerce';

					$payment_methods = tourmaster_get_option('payment', 'payment-method', array('booking', 'paypal', 'credit-card'));
					if( in_array('booking', $payment_methods) ){
						$payment_method[] = 'booking';
                    }
				}else{
					$payment_method = tourmaster_get_option('payment', 'payment-method', array('booking', 'paypal', 'credit-card'));
				}
			}
			
			$woocommerce_enable = in_array('woocommerce', $payment_method);
			$paypal_enable = in_array('paypal', $payment_method);
			$credit_card_enable = in_array('credit-card', $payment_method);
			$hipayprofessional_enable = in_array('hipayprofessional', $payment_method);
			$custom_payment_enable = apply_filters('tourmaster_custom_payment_enable', false, $payment_method);

			$extra_class = '';
			if( $paypal_enable && $credit_card_enable ){
				$extra_class .= ' tourmaster-both-online-payment';
			}elseif( $paypal_enable && $hipayprofessional_enable ){
				$extra_class .= ' tourmaster-both-online-payment';
			}elseif( $credit_card_enable && $hipayprofessional_enable ){
				$extra_class .= ' tourmaster-both-online-payment';
			}elseif( !$paypal_enable && !$credit_card_enable && !$hipayprofessional_enable){
				$extra_class .= ' tourmaster-none-online-payment';
			}
			$ret  = '<div class="tourmaster-payment-method-wrap ' . esc_attr($extra_class) . '" >';
			$ret .= '<h3 class="tourmaster-payment-method-title" >';
			if( empty($payment_method) ){
				$ret .= esc_html__('Book now and we will contact you back', 'tourmaster');
			}else{
				$ret .= esc_html__('Please select a payment method', 'tourmaster');
			}
			$ret .= '</h3>';
			
			if( in_array('booking', $payment_method) ){
				if( is_user_logged_in() ){
					$ret .= '<div class="tourmaster-payment-method-description" >';
					$ret .= esc_html__('* If you wish to do a bank transfer, please select "Book and pay later" button.', 'tourmaster'); 
					$ret .= '<br>' . esc_html__('You will have an option to submit payment receipt on your dashboard page.', 'tourmaster');
					$ret .= '</div>';
				}
			}

			$our_term = tourmaster_get_option('payment', 'term-of-service-page', '#');
			$our_term = is_numeric($our_term)? get_permalink($our_term): $our_term; 
			$privacy = tourmaster_get_option('payment', 'privacy-statement-page', '#');
			$privacy = is_numeric($privacy)? get_permalink($privacy): $privacy; 
			$ret .= '<div class="tourmaster-payment-terms" >';
			$ret .= '<input type="checkbox" name="term-and-service" />';
			$ret .= sprintf(wp_kses(
				__('* I agree with <a href="%s" target="_blank">Terms of Service</a> and <a href="%s" target="_blank">Privacy Statement</a>.', 'tourmaster'), 
				array('a' => array( 'href'=>array(), 'target'=>array() ))
			), $our_term, $privacy);
			$ret .= '<div class="tourmaster-tour-booking-required-error tourmaster-notification-box tourmaster-failure" ';
			$ret .= 'data-default="' . esc_attr(esc_html__('Please agree to all the terms and conditions before proceeding to the next step.', 'tourmaster')) . '" ';
			$ret .= '></div>';
			$ret .= '</div>'; // tourmaster-payment-terms

			$admin_approval = true;
			if( empty($tour_option['payment-admin-approval']) ){
				$payment_admin_approval = tourmaster_get_option('payment', 'payment-admin-approval', 'disable');
			}else{
				$payment_admin_approval = $tour_option['payment-admin-approval'];
			}
			if( $payment_admin_approval == 'enable' ){
				if( empty($booking_detail['tid']) ){
					$admin_approval = false;
				}else{
					$result = tourmaster_get_booking_data(array('id'=>$booking_detail['tid']), array('single'=>true));
					if( $result->order_status == 'wait-for-approval' ){
						$admin_approval = false;
					}
				}
			}
			
			if( $admin_approval && ($woocommerce_enable || $paypal_enable || $credit_card_enable || $hipayprofessional_enable || $custom_payment_enable) ){
				$ret .= '<div class="tourmaster-payment-gateway clearfix" >';
				if( $woocommerce_enable ){
					$ret .= '<div class="tourmaster-online-payment-method tourmaster-payment-woocommerce tourmaster-center-align" >';
					$ret .= '<a class="tourmaster-button " data-method="ajax" data-action="tourmaster_payment_selected" data-ajax="' . esc_url(TOURMASTER_AJAX_URL) . '" data-action-type="woocommerce" >';
					$ret .= esc_html__('Pay Now', 'tourmaster');
					$ret .= '</a>';
					$ret .= '</div>';
				}

				if( $paypal_enable ){
					$paypal_button_atts = apply_filters('tourmaster_paypal_button_atts', array());
					$ret .= '<div class="tourmaster-online-payment-method tourmaster-payment-paypal" >';
					$ret .= '<img src="' . esc_attr(TOURMASTER_URL) . '/images/paypal.png" alt="paypal" width="170" height="76" ';
					if( !empty($paypal_button_atts['method']) && $paypal_button_atts['method'] == 'ajax' ){
						$ret .= 'data-method="ajax" data-action="tourmaster_payment_selected" data-ajax="' . esc_url(TOURMASTER_AJAX_URL) . '" ';
						if( !empty($paypal_button_atts['type']) ){
							$ret .= 'data-action-type="' . esc_attr($paypal_button_atts['type']) . '" ';
						} 
					}
					$ret .= ' />';

					if( !empty($paypal_button_atts['service-fee']) ){
						$ret .= '<div class="tourmaster-payment-paypal-service-fee-text" >';
						$ret .= sprintf(esc_html__('Additional %s%% is charged for PayPal payment.', 'tourmaster'), $paypal_button_atts['service-fee']);
						$ret .= '</div>';
					}
					$ret .= '</div>';
				}

				if( $credit_card_enable ){
					$payment_attr = apply_filters('goodlayers_plugin_payment_attribute', array());
					$ret .= '<div class="tourmaster-online-payment-method tourmaster-payment-credit-card" >';
					$ret .= '<img src="' . esc_attr(TOURMASTER_URL) . '/images/credit-card.png" alt="credit-card" width="170" height="76" ';
					if( !empty($payment_attr['method']) && $payment_attr['method'] == 'ajax' ){
						$ret .= 'data-method="ajax" data-action="tourmaster_payment_selected" data-ajax="' . esc_url(TOURMASTER_AJAX_URL) . '" ';
						if( !empty($payment_attr['type']) ){
							$ret .= 'data-action-type="' . esc_attr($payment_attr['type']) . '" ';
						} 
					}
					$ret .= ' />';

					// service fee
					$credit_card_service_fee = tourmaster_get_option('payment', 'credit-card-service-fee', '');
					if( !empty($credit_card_service_fee) ){
						$ret .= '<div class="tourmaster-payment-credit-card-service-fee-text" >';
						$ret .= sprintf(esc_html__('Additional %s%% is charged for payment via credit card.', 'tourmaster'), $credit_card_service_fee);
						$ret .= '</div>';
					}

					// image display
					$credit_card_types = tourmaster_get_option('payment', 'accepted-credit-card-type', array());
					if( !empty($credit_card_types) ){
						$ret .= '<div class="tourmaster-payment-credit-card-type" >';
						foreach( $credit_card_types as $type ){
							$ret .= '<img src="' . esc_attr(TOURMASTER_URL) . '/images/' . esc_attr($type) . '.png" alt="' . esc_attr($type) . '" />';
												
						}
						$ret .= '</div>';	
					}

					$ret .= '</div>';
				}

				if( $hipayprofessional_enable ){
			        $hipayprofessional_button_atts = apply_filters('tourmaster_hipayprofessional_button_atts', array());

			        $ret .= '<div class="tourmaster-online-payment-method tourmaster-payment-hipayprofessional" >';
			        $ret .= '<img src="' . esc_attr(TOURMASTER_URL) . '/images/hipay.png" alt="hipayprofessional" ';
			        if( !empty($hipayprofessional_button_atts['method']) && $hipayprofessional_button_atts['method'] == 'ajax' ){
			                $ret .= 'data-method="ajax" data-action="tourmaster_payment_selected" data-ajax="' . esc_url(TOURMASTER_AJAX_URL) . '" ';
			                if( !empty($hipayprofessional_button_atts['type']) ){
			                        $ret .= 'data-action-type="' . esc_attr($hipayprofessional_button_atts['type']) . '" ';
			                } 
			        }
			        $ret .= ' />';
			        $ret .= '</div>';
				}

				$ret .= apply_filters('tourmaster_additional_payment_method', '');

				$ret .= '</div>'; // tourmaster-payment-gateway
			}

			if( empty($payment_method) || in_array('booking', $payment_method) ){

				if( $admin_approval && sizeof($payment_method) > 1 ){
					$ret .= '<div class="tourmaster-payment-method-or" id="tourmaster-payment-method-or" >';
					$ret .= '<span class="tourmaster-left" ></span>';
					$ret .= '<span class="tourmaster-middle" >' . esc_html__('OR', 'tourmaster') . '</span>';
					$ret .= '<span class="tourmaster-right" ></span>';
					$ret .= '</div>'; // tourmaster-payment-method-or
				}

				$ret .= '<div class="tourmaster-payment-method-booking" >';
				if( is_user_logged_in() ){
					$ret .= '<a class="tourmaster-button tourmaster-payment-method-booking-button tourmaster-payment-step" data-name="payment-method" data-value="booking" data-step="4" >';
					if( empty($payment_method) ){
						$ret .= esc_html__('Book Now', 'tourmaster');
					}else{
						$ret .= esc_html__('Book and pay later', 'tourmaster');
					}
					$ret .= '</a>';
				}else{
					$book_by_email = tourmaster_get_option('general', 'enable-booking-via-email', 'enable');

					if( $book_by_email == 'enable' ){
						$ret .= '<a class="tourmaster-button tourmaster-payment-method-booking-button tourmaster-payment-step" data-name="payment-method" data-value="booking" data-step="4" >';
						$ret .= esc_html__('Book now via email', 'tourmaster');
						$ret .= '</a>';
					}else{
						$ret .= '<a class="tourmaster-button tourmaster-payment-method-booking-button" data-tmlb="book-and-pay-later-login" >';
						if( empty($payment_method) ){
							$ret .= esc_html__('Book Now', 'tourmaster');

							$lightbox_title = esc_html__('Book and pay later requires an account', 'tourmaster');
						}else{
							$ret .= esc_html__('Book and pay later', 'tourmaster');

							$lightbox_title = esc_html__('Book now requires an account', 'tourmaster');
						}
						$ret .= '</a>';

						$ret .= tourmaster_lightbox_content(array(
							'id' => 'book-and-pay-later-login',
							'title' => $lightbox_title,
							'content' => tourmaster_get_login_form2(false, array(
								'redirect'=>'payment'
							))
						));	
					}
				}
				$ret .= '</div>'; // tourmaster-payment-method-booking
			}

			$ret .= '</div>'; // tourmaster-payment-method-wrap

			return $ret;
		}
	}	

	if( !function_exists('tourmaster_payment_complete') ){
		function tourmaster_payment_complete(){

			$is_user_logged_in = is_user_logged_in();
			$enable_payment = tourmaster_get_option('general', 'enable-payment', 'enable');
			$enable_membership = tourmaster_get_option('general', 'enable-membership', 'enable');

			$ret  = '<div class="tourmaster-payment-complete-wrap" >';
			$ret .= '<div class="tourmaster-payment-complete-head" >' . esc_html__('Booking Completed!', 'tourmaster') . '</div>';
			$ret .= '<div class="tourmaster-payment-complete-content-wrap" >';
			$ret .= '<i class=" icon_check_alt2 tourmaster-payment-complete-icon" ></i>';
			$ret .= '<div class="tourmaster-payment-complete-thank-you" >' . esc_html__('Thank you!', 'tourmaster') . '</div>';
			$ret .= '<div class="tourmaster-payment-complete-content" >';
			if( $enable_membership == 'enable' && $is_user_logged_in && $enable_payment ){
				$ret .= wp_kses(__('Your booking detail has been sent to your email. <br> You can check the payment status from your dashboard.', 'tourmaster'), array('br'=>array()));
			}else{
				$ret .= esc_html__('Your booking detail has been sent to your email.', 'tourmaster');
			}
			$ret .= '</div>'; // tourmaster-payment-complete-content

			if( $is_user_logged_in ){
				$ret .= '<a class="tourmaster-payment-complete-button tourmaster-button" href="' . tourmaster_get_template_url('user') . '" >' . esc_html__('Go to my dashboard', 'tourmaster') . '</a>';
			}else{
				$ret .= '<a class="tourmaster-payment-complete-button tourmaster-button" href="' . esc_url(home_url("/")) . '" >' . esc_html__('Go to homepage', 'tourmaster') . '</a>';
			}

			$bottom_text = tourmaster_get_option('general', 'payment-complete-bottom-text', '');
			if( !empty($bottom_text) ){
				$ret .= '<div class="tourmaster-payment-complete-bottom-text" >';
				$ret .= tourmaster_content_filter($bottom_text);
				$ret .= '</div>';
			}
			$ret .= '</div>'; // tourmaster-payment-complete-content-wrap
			$ret .= '</div>'; // tourmaster-payment-complete-wrap

			return $ret;
		}
	}
	if( !function_exists('tourmaster_payment_complete_delay') ){
		function tourmaster_payment_complete_delay(){

			$is_user_logged_in = is_user_logged_in();
			$enable_payment = tourmaster_get_option('general', 'enable-payment', 'enable');
			$enable_membership = tourmaster_get_option('general', 'enable-membership', 'enable');

			$ret  = '<div class="tourmaster-payment-complete-wrap" >';
			$ret .= '<div class="tourmaster-payment-complete-head" >' . esc_html__('Booking Completed!', 'tourmaster') . '</div>';
			$ret .= '<div class="tourmaster-payment-complete-content-wrap" >';
			$ret .= '<i class=" icon_check_alt2 tourmaster-payment-complete-icon" ></i>';
			$ret .= '<div class="tourmaster-payment-complete-thank-you" >' . esc_html__('Thank you!', 'tourmaster') . '</div>';
			$ret .= '<div class="tourmaster-payment-complete-content" >';
			if( $enable_membership == 'enable' && $is_user_logged_in && $enable_payment == 'enable' ){
				$ret .= wp_kses(__('Your booking detail will be sent to your email shortly. <br> You can check the payment status from your dashboard.<br> ( There might be some delay processing the paypal payment )', 'tourmaster'), array('br'=>array()));
			}else{
				$ret .= esc_html__('Your booking detail will be sent to your email shortly.', 'tourmaster');
			}
			$ret .= '</div>'; // tourmaster-payment-complete-content

			if( $is_user_logged_in ){
				$ret .= '<a class="tourmaster-payment-complete-button tourmaster-button" href="' . tourmaster_get_template_url('user') . '" >' . esc_html__('Go to my dashboard', 'tourmaster') . '</a>';
			}else{
				$ret .= '<a class="tourmaster-payment-complete-button tourmaster-button" href="' . esc_url(home_url("/")) . '" >' . esc_html__('Go to homepage', 'tourmaster') . '</a>';
			}

			$bottom_text = tourmaster_get_option('general', 'payment-complete-bottom-text', '');
			if( !empty($bottom_text) ){
				$ret .= '<div class="tourmaster-payment-complete-bottom-text" >';
				$ret .= tourmaster_content_filter($bottom_text);
				$ret .= '</div>';
			}
			$ret .= '</div>'; // tourmaster-payment-complete-content-wrap
			$ret .= '</div>'; // tourmaster-payment-complete-wrap

			return $ret;
		}
	}

	//////////////////////////////////////////////////////////////////
	/////////////////            lightbox             ////////////////
	//////////////////////////////////////////////////////////////////
	if( !function_exists('tourmaster_lb_payment_receipt') ){
		function tourmaster_lb_payment_receipt( $transaction_id, $price_settings, $post_type = 'tour' ){
			
			$form_fields = array(
				'receipt' => array(
					'title' => esc_html__('Select Image', 'tourmaster'),
					'type' => 'file',
				),
				'transaction-id' => array(
					'title' => esc_html__('Transaction ID ( from the receipt )', 'tourmaster'),
					'type' => 'text',
					'required' => true
				)
			);

			$ret  = '<form class="tourmaster-payment-receipt-form tourmaster-form-field tourmaster-with-border" ';
			$ret .= 'method="post" enctype="multipart/form-data" ';
			$ret .= 'action="' . remove_query_arg(array('error_code')) . '" ';
			$ret .= '>';
			
			$payment_type_checked = false;
			$ret .= '<div class="tourmaster-payment-receipt-field tourmaster-payment-receipt-field-payment-type clearfix" >';
			$ret .= '<div class="tourmaster-head" >' . esc_html__('Select Payment Type', 'tourmaster') . '</div>';
			$ret .= '<div class="tourmaster-tail clearfix" >';
			$ret .= '<div class="tourmaster-payment-receipt-deposit-option" >';
			if( !empty($price_settings['full-payment']) && $price_settings['full-payment'] == 'enable' ){
				$ret .= '<label class="tourmaster-deposit-payment-full" >';
				$ret .= '<input type="radio" name="payment-type" value="full" ' . ($payment_type_checked? '': 'checked') . ' />';
				$ret .= '<span class="tourmaster-content" >';
				$ret .= '<i class="icon_check_alt2" ></i>';
				$ret .= sprintf(esc_html__('Pay Full Amount : %s', 'tourmaster'), tourmaster_money_format($price_settings['full-payment-amount']));
				$ret .= '</span>';
				$ret .= '</label>'; 	

				$payment_type_checked = true;
			}

			if( !empty($price_settings['deposit-payment']) && !empty($price_settings['next-deposit-percent']) && !empty($price_settings['next-deposit-amount']) ){
				$ret .= '<label class="tourmaster-deposit-payment-partial" >';
				$ret .= '<input type="radio" name="payment-type" value="partial" ' . ($payment_type_checked? '': 'checked') . ' />';
				$ret .= '<span class="tourmaster-content" >';
				$ret .= '<i class="icon_check_alt2" ></i>';
				$ret .= sprintf(esc_html__('Pay %d%% Deposit : %s', 'tourmaster'), $price_settings['next-deposit-percent'], tourmaster_money_format($price_settings['next-deposit-amount']));
				// $ret .= '<input type="hidden" name="deposit-rate" value="' . esc_attr($price_settings['next-deposit-percent']) . '" />';
				// $ret .= '<input type="hidden" name="deposit-price" value="' . esc_attr($price_settings['next-deposit-amount']) . '" />';
				$ret .= '</span>';
				$ret .= '</label>';
			}

			$ret .= '</div>';
			$ret .= '</div>';
			$ret .= '</div>';


			foreach( $form_fields as $field_slug => $form_field ){
				$form_field['echo'] = false;
				$form_field['slug'] = $field_slug;
				$ret .= tourmaster_get_form_field($form_field, 'payment-receipt');
			}

			$ret .= '<div class="tourmaster-lb-submit-error tourmaster-notification-box tourmaster-failure" >';
			$ret .= esc_html__('Please fill all required fields', 'tourmaster');
			$ret .= '</div>';

			$ret .= '<div class="tourmaster-payment-receipt-field-submit" >';
			$ret .= '<input class="tourmaster-payment-receipt-field-submit-button tourmaster-button" type="submit" value="' . esc_html__('Submit', 'tourmaster') . '" />';
			$ret .= '</div>';

			$ret .= '<div class="tourmaster-payment-receipt-description" >';
			$ret .= esc_html__('* Please wait for the verification process after submitting the receipt. This could take up to couple days. You can check the status of submission from your "Dashboard" or "My Booking" page.', 'tourmaster');
			$ret .= '</div>';

			$ret .= '<input type="hidden" name="action" value="' . ($post_type == 'tour'? '': $post_type . '-') . 'payment-receipt" />';
			$ret .= '<input type="hidden" name="id" value="'. esc_attr($transaction_id) . '" />';

			$ret .= '</form>';

			return $ret;
		}
	}

	//////////////////////////////////////////////////////////////////
	/////////////////            ajax action          ////////////////
	//////////////////////////////////////////////////////////////////

	add_action('wp_ajax_tourmaster_payment_template', 'tourmaster_ajax_payment_template');
	add_action('wp_ajax_nopriv_tourmaster_payment_template', 'tourmaster_ajax_payment_template');
	if( !function_exists('tourmaster_ajax_payment_template') ){
		function tourmaster_ajax_payment_template(){

			$booking_detail = empty($_POST['booking_detail'])? array(): tourmaster_process_post_data($_POST['booking_detail']);
			
			$ret = tourmaster_get_payment_page($booking_detail);
			
			if( !empty($_POST['sub_action']) && $_POST['sub_action'] == 'update_sidebar' ){
				unset($ret['content']);
			} 

			if( empty($booking_detail['step']) || $booking_detail['step'] != 4 ){ 
				$ret['cookie'] = $booking_detail;
			}

			die(json_encode($ret));

		} // tourmaster_ajax_payment_template
	}

	add_action('wp_ajax_tourmaster_validate_coupon_code', 'tourmaster_ajax_validate_coupon_code');
	add_action('wp_ajax_nopriv_tourmaster_validate_coupon_code', 'tourmaster_ajax_validate_coupon_code');
	if( !function_exists('tourmaster_validate_coupon_code') ){
		function tourmaster_validate_coupon_code( $coupon_code, $tour_id, $tid = '' ){
			global $wpdb;

			$coupons = get_posts(array(
				'post_type' => 'tour_coupon', 
				'posts_per_page' => 1, 
				'meta_key' => 'tourmaster-coupon-code', 
				'meta_value' => $coupon_code
			));

			if( !empty($coupons) ){

				$coupon_status = true;
				$coupon_option = get_post_meta($coupons[0]->ID, 'tourmaster-coupon-option', true);

				// check if already apply coupon
				$applied_coupon = false;
				if( !empty($tid) ){
					$condition = array('coupon_code'=>$coupon_code, 'id'=>$tid);
					$applied_coupon = tourmaster_get_booking_data($condition, array(), 'COUNT(*)');
				}

				if( empty($applied_coupon) ){

					// check expiry
					if( !empty($coupon_option['coupon-expiry']) ){
						if( strtotime(date("Y-m-d")) > strtotime($coupon_option['coupon-expiry']) ){
							return array(
								'status' => 'failed',
								'message' => esc_html__('This coupon has been expired, please try again with different coupon', 'tourmaster')
							);
						}
					}

					// check specific tour
					if( !empty($coupon_option['apply-to-specific-tour']) ){
						$allow_tours = array_map('trim', explode(',', $coupon_option['apply-to-specific-tour']));
						if( !in_array($tour_id, $allow_tours) ){
							return array(
								'status' => 'failed',
								'message' => esc_html__('This coupon is not available for this tour, please try again with different coupon', 'tourmaster')
							);
						}
					}

					// check the available number
					if( !empty($coupon_option['coupon-amount']) ){

						$condition = array('coupon_code'=>$coupon_code,);
						$used_coupon = tourmaster_get_booking_data($condition, array(), 'COUNT(*)');

						if( $used_coupon >= $coupon_option['coupon-amount'] ){
							return array(
								'status' => 'failed',
								'message' => esc_html__('This coupon has been used up, please try again with different coupon', 'tourmaster')
							);
						}
					}
				}
				
				// coupon is valid
				$discount_amount = 0;
				if( !empty($coupon_option['coupon-discount-type']) ){
					if( $coupon_option['coupon-discount-type'] == 'percent' ){
						$discount_amount = $coupon_option['coupon-discount-amount'] . '%';
					}else if( $coupon_option['coupon-discount-type'] == 'amount' ){
						$discount_amount = tourmaster_money_format($coupon_option['coupon-discount-amount']);
					} 		
				}
				$message = sprintf(__('You got %s discount', 'tourmaster'), $discount_amount);
				return array(
					'status' => 'success',
					'message' => $message, 
					'data' => $coupon_option
				);

			}else{
				return array(
					'status' => 'failed',
					'message' => esc_html__('Invalid coupon code, please try again with different coupon', 'tourmaster')
				);
			}
		}
	}
	if( !function_exists('tourmaster_ajax_validate_coupon_code') ){
		function tourmaster_ajax_validate_coupon_code(){

			$ret = array();

			if( empty($_POST['coupon_code']) ){
				die(json_encode(array(
					'status' => 'failed',
					'message' => esc_html__('Please fill in the coupon code', 'tourmaster')
				)));
			}else{

				$status = tourmaster_validate_coupon_code($_POST['coupon_code'], $_POST['tour_id']);
				unset($status['data']);

				die(json_encode($status));
			}

		} // tourmaster_ajax_payment_template
	}

	//////////////////////////////////////////////////////////////////
	/////////////////     payment plugin supported    ////////////////
	//////////////////////////////////////////////////////////////////
	add_filter('goodlayers_payment_get_transaction_data', 'tourmaster_goodlayers_payment_get_transaction_data', 10, 3);
	if( !function_exists('tourmaster_goodlayers_payment_get_transaction_data') ){
		function tourmaster_goodlayers_payment_get_transaction_data( $ret, $tid, $types ){
			$result = tourmaster_get_booking_data(array('id'=>$tid), array('single'=>true));
			if( !empty($result) ){
				$ret = array();

				foreach( $types as $type ){
					if( $type == 'price' ){
						$pricing_info = json_decode($result->pricing_info, true);
						$booking_detail = json_decode($result->booking_detail, true);
						if( empty($booking_detail['payment-type']) || $booking_detail['payment-type'] != 'partial' ){
							unset($pricing_info['deposit-price']);
						}
						$ret[$type] = $pricing_info;
					}else if( in_array($type, array('email', 'contact_address', 'first_name', 'last_name', 'country')) ){
						$contact_info = json_decode($result->contact_info, true);
						$ret[$type] = empty($contact_info[$type])? '': $contact_info[$type];
					}else if( $type == 'tour_id' ){
						$ret[$type] = $result->tour_id;
					}else if( $type == 'currency' ){
						if( !empty($result->currency) ){
							$ret[$type] = json_decode($result->currency, true);
						}else{
							$ret[$type] = array();
						}
					}
				}
			}

			return $ret;
		}
	}

	add_filter('goodlayers_payment_get_option', 'tourmaster_goodlayers_payment_get_option', 10, 2);
	if( !function_exists('tourmaster_goodlayers_payment_get_option') ){
		function tourmaster_goodlayers_payment_get_option($value, $key){
			return tourmaster_get_option('payment', $key, $value);
		}
	}

	add_action('goodlayers_set_payment_complete', 'tourmaster_goodlayers_set_payment_complete', 10, 2);
	if( !function_exists('tourmaster_goodlayers_set_payment_complete') ){
		function tourmaster_goodlayers_set_payment_complete($tid, $payment_info){

			$result = tourmaster_get_booking_data(array('id'=>$tid), array('single'=>true));
			
			$payment_infos = json_decode($result->payment_info, true);
			$payment_infos = tourmaster_payment_info_format($payment_infos, $result->order_status);
			$payment_infos[] = $payment_info;

			$paid_amount = 0;
			if( !empty($payment_infos) ){
				foreach( $payment_infos as $payment_info ){
					if( !empty($payment_info['deposit_amount']) ){
						$paid_amount += floatval($payment_info['deposit_amount']);
					}else if( !empty($payment_info['pay_amount']) ){
						$paid_amount += floatval($payment_info['pay_amount']);
					}else if( !empty($payment_info['amount']) ){
						$paid_amount += floatval($payment_info['amount']);

					// receipt
					}else if( !empty($payment_info['deposit_price']) ){
						$paid_amount += $payment_info['deposit_price'];
					}
				}
			}
			

			if( tourmaster_compare_price($result->total_price, $paid_amount) || $paid_amount >= $result->total_price ){
				$order_status = 'online-paid';
				$mail_type = 'payment-made-mail';
		        $admin_mail_type = 'admin-online-payment-made-mail';
			}else{
				$order_status = 'deposit-paid';
				$mail_type = 'deposit-payment-made-mail';
		        $admin_mail_type = 'admin-deposit-payment-made-mail';
			}

			tourmaster_update_booking_data( 
				array(
					'payment_info' => json_encode($payment_infos),
					'payment_date' => current_time('mysql'),
					'order_status' => $order_status
				),
				array('id' => $tid),
				array('%s', '%s', '%s'),
				array('%d')
			);

			tourmaster_mail_notification($mail_type, $tid, '', array(
				'custom' => array(
					'payment-method' => $payment_info['payment_method'],
					'payment-date' => tourmaster_time_format($payment_info['submission_date']) . ' ' . tourmaster_date_format($payment_info['submission_date']),
					'submission-date' => tourmaster_time_format($payment_info['submission_date']) . ' ' . tourmaster_date_format($payment_info['submission_date']),
					'submission-amount' => $payment_info,
					'transaction-id' => $payment_info['transaction_id']
				)
			));
			tourmaster_mail_notification($admin_mail_type, $tid, '', array(
				'custom' => array(
					'payment-method' => $payment_info['payment_method'],
					'payment-date' => tourmaster_time_format($payment_info['submission_date']) . ' ' . tourmaster_date_format($payment_info['submission_date']),
					'submission-date' => tourmaster_time_format($payment_info['submission_date']) . ' ' . tourmaster_date_format($payment_info['submission_date']),
					'submission-amount' => $payment_info,
					'transaction-id' => $payment_info['transaction_id']
				)
			));
			tourmaster_send_email_invoice($tid);
		}
	}

	add_action('wp_ajax_tourmaster_payment_plugin_complete', 'tourmaster_payment_plugin_complete');
	add_action('wp_ajax_nopriv_tourmaster_payment_plugin_complete', 'tourmaster_payment_plugin_complete');
	if( !function_exists('tourmaster_payment_plugin_complete') ){
		function tourmaster_payment_plugin_complete(){
			die(json_encode(array(
				'cookie' => '',
				'content' => tourmaster_payment_complete()
			)));
		}
	}

	add_action('wp_ajax_tourmaster_payment_selected', 'tourmaster_ajax_payment_selected');
	add_action('wp_ajax_nopriv_tourmaster_payment_selected', 'tourmaster_ajax_payment_selected');
	if( !function_exists('tourmaster_ajax_payment_selected') ){
		function tourmaster_ajax_payment_selected(){

			$ret = array();

			if( !empty($_POST['booking_detail']) ){
				$booking_detail = tourmaster_process_post_data($_POST['booking_detail']);
				
				if( !empty($booking_detail['tid']) ){
					
					$tid = $booking_detail['tid'];
					$payment_type = $booking_detail['payment-type'];
					$payment_method = $_POST['type'];
					$result = tourmaster_get_booking_data(array('id' => $tid), array('single' => true));

					$pricing_info = json_decode($result->pricing_info, true);
					$pricing_info = tourmaster_update_booked_tour_price($pricing_info, $payment_method, $booking_detail['payment-type']);
					
					$booking_detail = json_decode($result->booking_detail, true);
					$booking_detail['payment_method'] = $payment_method;
					$booking_detail['payment-type'] = $payment_type;

					global $wpdb;
                    $wpdb->update("{$wpdb->prefix}tourmaster_order", 
                        array('pricing_info' => json_encode($pricing_info), 'booking_detail' => json_encode($booking_detail)),
                        array('id' => $tid),
                        array('%s', '%s'),
                        array('%d')
                    );

					$tour_option = tourmaster_get_post_meta($booking_detail['tour-id'], 'tourmaster-tour-option');
					$date_price = tourmaster_get_tour_date_price($tour_option, $booking_detail['tour-id'], $booking_detail['tour-date']);
					$date_price = tourmaster_get_tour_date_price_package($date_price, $booking_detail);

					$booking_detail['step'] = 4;
					$ret['content'] = apply_filters('goodlayers_' . $_POST['type'] . '_payment_form', '', $tid);
					$ret['sidebar'] = tourmaster_get_booked_booking_bar_summary( $tour_option, $date_price, $booking_detail, $pricing_info );

				}else if( !empty($booking_detail['tour-id']) && !empty($booking_detail['tour-date']) ){
					$tour_option = tourmaster_get_post_meta($booking_detail['tour-id'], 'tourmaster-tour-option');
					$date_price = tourmaster_get_tour_date_price($tour_option, $booking_detail['tour-id'], $booking_detail['tour-date']);
					$date_price = tourmaster_get_tour_date_price_package($date_price, $booking_detail);
						
					$booking_detail['payment_method'] = $_POST['type'];
					$tour_price = tourmaster_get_tour_price($tour_option, $date_price, $booking_detail);
					
					if( $date_price['pricing-method'] == 'group' ){
						$traveller_amount = 1;
					}else{
						$traveller_amount = tourmaster_get_tour_people_amount($tour_option, $date_price, $booking_detail, 'all');
					}
					
					$package_group_slug = empty($date_price['group-slug'])? '': $date_price['group-slug'];
					$tid = tourmaster_insert_booking_data($booking_detail, $tour_price, $traveller_amount, $package_group_slug);

					if( $tour_price['total-price'] <= 0 ){
						$ret['content'] = tourmaster_payment_complete();
						$ret['cookie'] = '';
						
					}else{
						$booking_detail['tid'] = $tid;

						$ret['content'] = apply_filters('goodlayers_' . $_POST['type'] . '_payment_form', '', $tid);
						$ret['cookie'] = $booking_detail;
						
						// recalculate the fee
						$booking_detail['step'] = 4;
						$ret['sidebar'] = tourmaster_get_booking_bar_summary( $tour_option, $date_price, $booking_detail );
					}
				}
			}

			die(json_encode($ret));
		} // tourmaster_ajax_payment_selected
	}

    if( !function_exists('tourmaster_get_tour_product_order') ){
        function tourmaster_get_tour_product_order(){

            $product_id = get_option('tourmaster_tour_product_order_id', '');

            if( !empty($product_id) ){
                $product = wc_get_product($product_id);
                if( !empty($product) ){
                    return $product;
                }
            }
            
            $product = new WC_Product();
            $product->set_name(esc_html__('Tour Booking', 'tourmaster'));
            $product->set_catalog_visibility('hidden');
            $product->save();

			wp_update_post(array('ID'=>$product->get_id(), 'post_status' => 'private'));

            update_option('tourmaster_tour_product_order_id', $product->get_id());

            return $product;

        }
    }	

	add_filter('goodlayers_woocommerce_payment_form', 'tourmaster_goodlayers_woocommerce_payment_form', 10, 2);
	if( !function_exists('tourmaster_goodlayers_woocommerce_payment_form') ){
		function tourmaster_goodlayers_woocommerce_payment_form($ret, $tid){

			$result = tourmaster_get_booking_data(array('id' => $tid), array('single' => true));

			if( !empty($result->woocommerce_order_id) ){
                $wc_order = wc_get_order($result->woocommerce_order_id);
            }

            if( empty($wc_order) ){
				
				global $wpdb;
                $currency = json_decode($result->currency, true);
				$user_id = get_current_user_id();

                // create woocommerce object
                $product = tourmaster_get_tour_product_order();
                if( !empty($currency) ){
                    $product->set_price($result->total_price * floatval($currency['exchange-rate']));
                }else{
                    $product->set_price($result->total_price);
                }

				$billing_info = json_decode($result->billing_info, true);
                $billing_address = array(
                    'first_name' => empty($billing_info['first_name'])? '': $billing_info['first_name'],
                    'last_name'  => empty($billing_info['last_name'])? '': $billing_info['last_name'],
                    'email'      => empty($billing_info['email'])? '': $billing_info['email'],
                    'address_1'  => empty($billing_info['address'])? '': $billing_info['address'],
                    'address_2'  => '',
                    'city'       => '',
                    'state'      => '',
                    'postcode'   => '',
                    'country'    => empty($billing_info['country'])? '': tourmaster_woocommerce_country_code($billing_info['country']),
                );

                $wc_order = wc_create_order();
				$wc_order->add_order_note(sprintf(esc_html__('Tour order #%d : %s', 'tourmaster'), $tid, get_the_title($result->tour_id)));
                $wc_order->add_product($product, 1);
                if( !empty($user_id) ){
                    $wc_order->set_customer_id($user_id);
                }
                if( !empty($currency) ){
                    $wc_order->set_currency(strtoupper($currency['currency-code']));
                }
                $wc_order->set_address($billing_address,'billing');
                $wc_order->calculate_totals();
                $wc_order->update_status('wc-pending');
                $wc_order->save();

                // update woocommerce order id
                $wpdb->update("{$wpdb->prefix}tourmaster_order", array(
                    'woocommerce_order_id' => $wc_order->id
                ), array(
                    'id' => $tid
                ), array('%d'), array('%d'));
            }

			$order_url = $wc_order->get_checkout_payment_url();

			$ret  = '<script>';
			$ret .= 'window.location.replace(\'' . $order_url . '\')';
			$ret .= '</script>';

			return $ret;
		}
	}

	add_action('woocommerce_order_status_completed', 'tourmaster_woocommerce_order_complete');
    if( !function_exists('tourmaster_woocommerce_order_complete') ){
        function tourmaster_woocommerce_order_complete( $order_id ){
            
            global $wpdb;
            $sql  = "SELECT * FROM {$wpdb->prefix}tourmaster_order ";
            $sql .= $wpdb->prepare("WHERE woocommerce_order_id = %d", $order_id);
            $result = $wpdb->get_row($sql);

			if( !empty($result) ) {
				$wpdb->update(
					"{$wpdb->prefix}tourmaster_order", 
					array('order_status' => 'approved'), 
					array('id' => $result->id),
					array('%s'),
					array('%d')
				);
	
				tourmaster_mail_notification('payment-made-mail', $result->id);
				tourmaster_mail_notification('admin-online-payment-made-mail', $result->id);
			}


        }
    }