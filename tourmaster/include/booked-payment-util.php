<?php
	/*	
	*	Tourmaster Plugin
	*	---------------------------------------------------------------------
	*	for payment page
	*	---------------------------------------------------------------------
	*/
	
	if( !function_exists('tourmaster_get_booked_payment_page') ){
		function tourmaster_get_booked_payment_page($tour_option, $date_price, $booking_detail, $pricing_info){

			$payment_style = tourmaster_get_option('general', 'payment-page-style', 'style-1');
            $services_price = empty($pricing_info['price-breakdown']['additional-service'])? array(): $pricing_info['price-breakdown']['additional-service'];

			return array(
				 // tourmaster_booked_payment_service_form( $tour_option, $booking_detail, $services_price ) . 
                 'content' => '<div class="tourmaster-summary-info-outer" >' . 
				 				tourmaster_payment_contact_detail( $booking_detail ) . 
								tourmaster_payment_traveller_detail( $tour_option, $booking_detail ) . 
								'</div>' . 
								($payment_style == 'style-2'? '': tourmaster_payment_method($booking_detail)),
				'sidebar' => tourmaster_get_booked_booking_bar_summary( $tour_option, $date_price, $booking_detail, $pricing_info )
			);

		}
	}

    /*
	if( !function_exists('tourmaster_booked_payment_service_form') ){
		function tourmaster_booked_payment_service_form($tour_option, $booking_detail, $services_price){

			$ret = '';

			if( !empty($tour_option['tour-service']) ){
				if( !empty($booking_detail['service']) && !empty($booking_detail['service-amount']) ){
					$services = tourmaster_process_service_data($booking_detail['service'], $booking_detail['service-amount']);
				}

				$ret .= '<div class="tourmaster-payment-service-form-wrap" >';
				$ret .= '<h3 class="tourmaster-payment-service-form-title" >' . esc_html__('Additional services.', 'tourmaster') . '</h3>';
				
				$ret .= '<div class="tourmaster-payment-service-form-item-wrap" >';
				foreach($tour_option['tour-service'] as $service_id){
					$service_option = get_post_meta($service_id, 'tourmaster-service-option', true);
					if( empty($service_option) ) continue;

					$ret .= '<div class="tourmaster-payment-service-form-item" >';
					$ret .= '<input type="checkbox" disabled name="service[]" value="' . esc_attr($service_id) . '" ';
					if( !empty($service_option['mandatory']) && $service_option['mandatory'] == 'enable' ){
						$ret .= 'checked onclick="return false;" ';
					}else{
						$ret .= (empty($services[$service_id]))? '': 'checked';	
					}
					$ret .= ' />';
					$ret .= '<span class="tourmaster-payment-service-form-item-title" >' . get_the_title($service_id) . '</span>';
				
					$ret .= '<span class="tourmaster-payment-service-form-price-wrap" >';
                    if( !empty($services_price[$service_id]['price-one']) ){
                        $ret .= '<span class="tourmaster-head" >' . tourmaster_money_format($services_price[$service_id]['price-one'], -2) . '</span>';
                    }else{
                        $ret .= '<span class="tourmaster-head" >' . tourmaster_money_format($service_option['price'], -2) . '</span>';
                    }
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
						$ret .= '<span class="tourmaster-sep" >x</span>' . '<input disabled type="text" name="service-amount[]" '; 
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
    */

    // get booking bar summary
	if( !function_exists('tourmaster_get_booked_booking_bar_summary') ){
		function tourmaster_get_booked_booking_bar_summary( $tour_option, $date_price, $booking_detail, $tour_price ){

			$payment_style = tourmaster_get_option('general', 'payment-page-style', 'style-1');

			$ret  = '<div class="tourmaster-tour-booking-bar-summary" >';
			$ret .= '<h3 class="tourmaster-tour-booking-bar-summary-title" >' . get_the_title($booking_detail['tour-id']) . '</h3>';
			
			$ret .= '<div class="tourmaster-tour-booking-bar-summary-info tourmaster-summary-travel-date" >';
			$ret .= '<span class="tourmaster-head" >' . esc_html__('Travel Date', 'tourmaster') . ' : </span>';
			$ret .= '<span class="tourmaster-tail" >';
			$ret .= tourmaster_date_format($booking_detail['tour-date']);
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
			$ret .= '</div>';

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

	if( !function_exists('tourmaster_update_booked_tour_price') ){
		function tourmaster_update_booked_tour_price( $pricing_info, $payment_method, $payment_type ){
            
			// check service rate
			// only for displaying, will not be stored until paypal payment is made 
            if( $payment_method == 'paypal' ){
                $service_fee = tourmaster_get_option('payment', 'paypal-service-fee', '');
                if( !empty($service_fee) ){
                    if( $payment_type == 'partial' ){
                        $pricing_info['deposit-price-raw'] = $pricing_info['deposit-price'];
                        $pricing_info['deposit-paypal-service-rate'] = $service_fee;
                        $pricing_info['deposit-paypal-service-fee'] = $pricing_info['deposit-price'] * (floatval($service_fee) / 100);	
                        $pricing_info['deposit-price'] += $pricing_info['deposit-paypal-service-fee'];
                    }else{
                        $pricing_info['pay-amount-paypal-service-rate'] = $service_fee;
                        $pricing_info['pay-amount-paypal-service-fee'] = $pricing_info['pay-amount'] * (floatval($service_fee) / 100);
                        $pricing_info['pay-amount-raw'] = $pricing_info['pay-amount'];
                        $pricing_info['pay-amount'] += $pricing_info['pay-amount-paypal-service-fee'];
                    }
                }
            }else if( in_array($payment_method, array('stripe', 'authorize', 'paymill')) ){
                $service_fee = tourmaster_get_option('payment', 'credit-card-service-fee', '');
                if( !empty($service_fee) ){
                    if( $payment_type == 'partial' ){
                        $pricing_info['deposit-price-raw'] = $pricing_info['deposit-price'];
                        $pricing_info['deposit-credit-card-service-rate'] = $service_fee;
                        $pricing_info['deposit-credit-card-service-fee'] = $pricing_info['deposit-price'] * (floatval($service_fee) / 100);	
                        $pricing_info['deposit-price'] += $pricing_info['deposit-credit-card-service-fee'];
                    }else{
                        $pricing_info['pay-amount-credit-card-service-rate'] = $service_fee;
                        $pricing_info['pay-amount-credit-card-service-fee'] = $pricing_info['pay-amount'] * (floatval($service_fee) / 100);
                        $pricing_info['pay-amount-raw'] = $pricing_info['pay-amount'];
                        $pricing_info['pay-amount'] += $pricing_info['pay-amount-credit-card-service-fee'];
                    }
                }
            }

			return $pricing_info;

		} // tourmaster_get_tour_price
	}