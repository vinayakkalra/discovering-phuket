<?php

    if( !function_exists('tourmaster_room_payment_step') ){
        function tourmaster_room_payment_step( $step = 2 ){

            echo '<div class="tourmaster-room-payment-step tourmaster-item-mglr clearfix" data-step="' . esc_attr($step) . '" >';
            echo '<div class="tourmaster-step tourmaster-column-15 tourmaster-active" >';
            echo '<div class="tourmaster-head" >' . esc_html__('Select Rooms', 'tourmaster') . '<div class="tourmaster-bullet" ></div></div>';
            echo '</div>';

            echo '<div class="tourmaster-step tourmaster-column-15 ' . ($step >= 2? 'tourmaster-active': '') . '" >';
            echo '<div class="tourmaster-head" >' . esc_html__('Summary And Services', 'tourmaster') . '<div class="tourmaster-bullet" ></div></div>';
            echo '</div>';
            
            echo '<div class="tourmaster-step tourmaster-column-15 ' . ($step >= 3? 'tourmaster-active': '') . '" >';
            echo '<div class="tourmaster-head" >' . esc_html__('Contact Detail', 'tourmaster') . '<div class="tourmaster-bullet" ></div></div>';
            echo '</div>';
            
            echo '<div class="tourmaster-step tourmaster-column-15 ' . ($step >= 4? 'tourmaster-active': '') . '" >';
            echo '<div class="tourmaster-head" >' . esc_html__('Complete', 'tourmaster') . '<div class="tourmaster-bullet" ></div></div>';
            echo '</div>';
            echo '</div>';

        }
    }

    if( !function_exists('tourmaster_room_price_sidebar') ){
        function tourmaster_room_price_sidebar( $price_breakdowns = array(), $step = 2, $payment_info = array(), $order_status = '' ){

            echo '<div class="tourmaster-room-price-sidebar " >';
            echo '<div class="tourmaster-price tourmaster-bold clearfix" >';
            echo '<div class="tourmaster-head" >' . esc_html__('Sub Total', 'tourmaster') . '</div>';
            echo '<div class="tourmaster-tail" >' . tourmaster_money_format($price_breakdowns['sub-total-price']) . '</div>';
            echo '</div>';

            if( !empty($price_breakdowns['services-price']) ){
                echo '<div class="tourmaster-price clearfix" >';
                echo '<div class="tourmaster-head" >' . esc_html__('Additional Services', 'tourmaster') . '</div>';
                echo '<div class="tourmaster-tail" >' . tourmaster_money_format($price_breakdowns['services-price']) . '</div>';
                echo '</div>';
            }
            
            if( !empty($price_breakdowns['coupon']) && $price_breakdowns['coupon']['type'] == 'before-tax' ){
                echo '<div class="tourmaster-price clearfix" >';
                echo '<div class="tourmaster-head" >' . esc_html__('Coupon Code', 'tourmaster') . '</div>';
                echo '<div class="tourmaster-tail" >' . $price_breakdowns['coupon']['coupon-code'] . '</div>';
                echo '</div>';

                echo '<div class="tourmaster-price clearfix" >';
                echo '<div class="tourmaster-head" >' . esc_html__('Coupon Discount', 'tourmaster') . '</div>';
                echo '<div class="tourmaster-tail" >- ' . tourmaster_money_format($price_breakdowns['coupon']['discount-price']) . '</div>';
                echo '</div>';
            }

            echo '<div class="tourmaster-divider" ></div>';

            echo '<div class="tourmaster-price tourmaster-bold clearfix" >';
            echo '<div class="tourmaster-head" >' . esc_html__('Total', 'tourmaster') . '</div>';
            echo '<div class="tourmaster-tail" >' . tourmaster_money_format($price_breakdowns['total-price']) . '</div>';
            echo '</div>';

            echo '<div class="tourmaster-price clearfix" >';
            echo '<div class="tourmaster-head" >' . sprintf(esc_html__('Tax %d%%', 'tourmaster'), $price_breakdowns['tax-rate']) . '</div>';
            echo '<div class="tourmaster-tail" >' . tourmaster_money_format($price_breakdowns['tax-price']) . '</div>';
            echo '</div>';

            if( !empty($price_breakdowns['coupon']) && $price_breakdowns['coupon']['type'] == 'after-tax' ){
                echo '<div class="tourmaster-price clearfix" >';
                echo '<div class="tourmaster-head" >' . esc_html__('Coupon Code', 'tourmaster') . '</div>';
                echo '<div class="tourmaster-tail" >' . $price_breakdowns['coupon']['coupon-code'] . '</div>';
                echo '</div>';

                echo '<div class="tourmaster-price clearfix" >';
                echo '<div class="tourmaster-head" >' . esc_html__('Coupon Discount', 'tourmaster') . '</div>';
                echo '<div class="tourmaster-tail" >- ' . tourmaster_money_format($price_breakdowns['coupon']['discount-price']) . '</div>';
                echo '</div>';
            }

            echo '<div class="tourmaster-divider" ></div>';

            echo '<div class="tourmaster-price tourmaster-bold clearfix" >';
            echo '<div class="tourmaster-head" >' . esc_html__('Grand Total', 'tourmaster') . '</div>';
            echo '<div class="tourmaster-tail tourmaster-em" >' . tourmaster_money_format($price_breakdowns['grand-total-price']) . '</div>';
            echo '</div>';

            if( !empty($payment_info) ){
                $paid_amount = 0;
                foreach( $payment_info as $payment ){
                    $paid_amount += empty($payment['amount'])? 0: floatval($payment['amount']);
                }

                if( !empty($paid_amount) ){
                    echo '<div class="tourmaster-price clearfix" >';
                    echo '<div class="tourmaster-head" >' . esc_html__('Paid Amount', 'tourmaster') . '</div>';
                    echo '<div class="tourmaster-tail tourmaster-em" >' . tourmaster_money_format($paid_amount) . '</div>';
                    echo '</div>';
                }
            }

            if( $step == 2 ){
                echo '<div class="tourmaster-price clearfix tourmaster-coupon-input-wrap" >';
                echo '<div class="tourmaster-head" >' . esc_html__('Coupon Code :', 'tourmaster') . '</div>';
                echo '<div class="tourmaster-tail" ><input class="tourmaster-room-coupon-code" type="text" /></div>';
                echo '</div>';

                echo '<div class="tourmaster-room-button tourmaster-step2-checkout" >' . esc_html__('Check Out Now', 'tourmaster') . '</div>';
            }else if( $step == 3 ){

                $enable_woocommerce_payment = tourmaster_get_option('room_payment', 'enable-woocommerce-payment', 'disable');
                $deposit_info = tourmaster_room_get_deposit_info($price_breakdowns['grand-total-price'], $payment_info);

                if( $enable_woocommerce_payment == 'disable' && $deposit_info['deposit_rate'] != 100 ){
                    echo '<div class="tourmaster-price tourmaster-bold clearfix tourmaster-deposit-amount" >';
                    echo '<div class="tourmaster-head" >' . sprintf(esc_html__('%d%% Deposit', 'tourmaster'), $deposit_info['deposit_rate']) . '</div>';
                    echo '<div class="tourmaster-tail tourmaster-em" >' . tourmaster_money_format($deposit_info['deposit_amount']) . '</div>';
                    echo '</div>';

                    echo '<div class="tourmaster-divider" ></div>';

                    echo '<div class="tourmaster-room-pay-type" >';
                    echo '<div class="tourmaster-room-pay-type-item tourmaster-full tourmaster-active" >';
                    echo '<i class="icon_check" ></i>';
                    echo esc_html__('Pay Full Amount', 'tourmaster');
                    echo '</div>';
                    echo '<div class="tourmaster-room-pay-type-item tourmaster-deposit" >';
                    echo '<i class="icon_check" ></i>';
                    echo sprintf(esc_html__('Pay %d%% Deposit', 'tourmaster'), $deposit_info['deposit_rate']);
                    echo '</div>';
                    echo '</div>';
                }

                echo '<div class="tourmaster-divider" ></div>';
                tourmaster_room_price_sidebar_payment($order_status);
            }

            
            echo '</div>'; // tourmaster-room-price-sidebar

        }
    }

    if( !function_exists('tourmaster_room_price_sidebar_payment') ){
        function tourmaster_room_price_sidebar_payment($order_status = ''){
            $enable_woocommerce_payment = tourmaster_get_option('room_payment', 'enable-woocommerce-payment', 'disable');
            $payment_method = tourmaster_get_option('room_payment', 'payment-method', array());
            
            echo '<div class="tourmaster-room-payment-method-wrap tourmaster-form-field" >';
            echo '<h3 class="tourmaster-payment-method-title" >' . esc_html__('Payment Method', 'tourmaster') . '</h3>';

            $accepted_payment_method = tourmaster_get_option('room_payment', 'accepted-credit-card-type', 'enable');
            echo '<div class="tourmater-room-accepted-payment-method" >';
            foreach( $accepted_payment_method as $method ){
                echo tourmaster_get_image(TOURMASTER_URL . '/images/' . $method . '.png');
            }
            echo '</div>';

            $our_term = tourmaster_get_option('room_payment', 'term-of-service-page', '#');
			$our_term = is_numeric($our_term)? get_permalink($our_term): $our_term; 
			$privacy = tourmaster_get_option('room_payment', 'privacy-statement-page', '#');
			$privacy = is_numeric($privacy)? get_permalink($privacy): $privacy; 
			echo '<div class="tourmaster-payment-terms" >';
			echo '<input type="checkbox" name="term-and-service" ';
            echo 'data-error="' . esc_attr(esc_html__('Please agree to all the terms and conditions before proceeding to the next step.', 'tourmaster')) . '" ';
            echo '/>';
			echo sprintf(wp_kses(
				__('* I agree with <a href="%s" target="_blank">Terms of Service</a> and <a href="%s" target="_blank">Privacy Statement</a>.', 'tourmaster'), 
				array('a' => array( 'href'=>array(), 'target'=>array() ))
			), $our_term, $privacy);
			echo '</div>'; // tourmaster-payment-terms

            $need_admin_approval = (tourmaster_get_option('room_payment', 'payment-admin-approval', 'disable') == 'enable');
            if( (empty($order_status) && $need_admin_approval) || 
                    (!empty($order_status) && $order_status == 'wait-for-approval') ){

            }else{
                if( $enable_woocommerce_payment == 'enable' ){
                    echo '<div class="tourmaster-room-button tourmaster-blue tourmaster-pay-woocommerce" >' . esc_html__('Pay Now', 'tourmaster') . '</div>';
                
                    if( in_array('booking', $payment_method) ){
                        echo '<div class="tourmaster-or" >' . esc_html__('OR', 'tourmaster') . '</div>';
                    }
                }else{
                    
                    $payments_title = array(
                        'paypal' => esc_html__('Paypal', 'tourmaster'),
                        'credit-card' => esc_html__('Credit Card', 'tourmaster'),
                        'hipayprofessional' => esc_html__('Hipay Professional', 'tourmaster')
                    );
    
                    echo '<div class="tourmaster-combobox-wrap" >';
                    echo '<select class="tourmaster-payment-selection" >';
                    foreach( $payment_method as $slug ){
                        if( $slug == 'booking' ) continue;
                        echo '<option value="' . esc_attr($slug) . '" >' . $payments_title[$slug] . '</option>';
                    }
                    echo '</select>';
                    echo '</div>';
                    echo '<div class="tourmaster-room-button tourmaster-blue tourmaster-pay-now" >' . esc_html__('Pay Now', 'tourmaster') . '</div>';
                
                    if( in_array('booking', $payment_method) ){
                        echo '<div class="tourmaster-or" >' . esc_html__('OR', 'tourmaster') . '</div>';
                    }
                }
            }

            if( empty($order_status) ){
                echo '<div class="tourmaster-room-button tourmaster-pay-later" >' . esc_html__('Book And Pay Later', 'tourmaster') . '</div>';
            }
            echo '</div>';
        }
    }

    if( !function_exists('tourmaster_room_payment_contact_form_fields') ){
		function tourmaster_room_payment_contact_form_fields(){

			if( empty($custom_fields) ){
				$custom_fields = tourmaster_get_option('room_general', 'contact-detail-fields', '');
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
					'address' => array(
						'title' => esc_html__('Address', 'tourmaster'),
						'type' => 'textarea'
					),
				);			
			}else{
				return tourmaster_read_custom_fields($custom_fields);
			}


		} // tourmaster_room_payment_contact_form_fields
	}
    
    if( !function_exists('tourmaster_room_payment_guest_input') ){
		function tourmaster_room_payment_guest_input($guest_fields, $i, $j, $k, $values = array()){

			$ret  = '<div class="tourmaster-guest-info-field clearfix">';
			$ret .= '<div class="tourmaster-head">' . sprintf(esc_html__('Guest %d :', 'tourmaster'), ($k + 1)) . '</div>';
			$ret .= '<div class="tourmaster-tail clearfix">';
            $ret .= '<div class="tourmaster-guest-info-input-wrap" >';
			$ret .= '<input type="text" class="tourmaster-guest-info-input" name="guest_first_name[' . $i . '][' . $j . '][' . $k . ']" value="';
            $ret .= empty($values['guest_first_name'][$i][$j][$k])? '': esc_attr($values['guest_first_name'][$i][$j][$k]);
            $ret .= '" placeholder="' . esc_html__('First Name', 'tourmaster') . '*" data-required />';
			$ret .= '</div>';
            $ret .= '<div class="tourmaster-guest-info-input-wrap" >';
            $ret .= '<input type="text" class="tourmaster-guest-info-input" name="guest_last_name[' . $i . '][' . $j . '][' . $k . ']" value="';
            $ret .= empty($values['guest_last_name'][$i][$j][$k])? '': esc_attr($values['guest_last_name'][$i][$j][$k]);
            $ret .= '" placeholder="' . esc_html__('Last Name', 'tourmaster') . '*" data-required />';
            $ret .= '</div>';

			// additional traveller fields
			if( !empty($guest_fields) ){
				foreach( $guest_fields as $field ){
                    $field_value = empty($values['traveller_' . $field['slug']][$i][$j][$k])? '': esc_attr($values['traveller_' . $field['slug']][$i][$j][$k]);

					if( !empty($field['width']) ){
						$ret .= '<div style="float: left; width: ' . esc_attr($field['width']) . '" >';
					}

					$ret .= '<div class="tourmaster-guest-info-custom" >';
					if( $field['type'] == 'combobox' ){	
						$count = 0;
						$ret .= '<div class="tourmaster-combobox-wrap" >';
						$ret .= '<select name="traveller_' . esc_attr($field['slug']) . '[' . $i . '][' . $j . '][' . $k . ']" ';
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
						$ret .= 'name="traveller_' . esc_attr($field['slug']) . '[' . $i . '][' . $j . '][' . $k . ']" ';
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

			$ret .= '</div>';
			$ret .= '</div>';

			return $ret;
		}
	}

    if( !function_exists('tourmaster_room_payment_display_guest_input') ){
		function tourmaster_room_payment_display_guest_input($guest_fields, $i, $j, $k, $guest_data){

			$ret  = '<div class="tourmaster-guest-info-field tourmaster-display clearfix">';
			$ret .= '<div class="tourmaster-head">' . sprintf(esc_html__('Guest %d :', 'tourmaster'), ($k + 1)) . '</div>';
			$ret .= '<div class="tourmaster-tail clearfix">';

            $ret .= '<div class="tourmaster-guest-info-input-wrap" >';
            $ret .= '<div class="tourmaster-sub-head" >' . esc_html__('First Name', 'tourmaster') . '</div>';
            $ret .= '<div class="tourmaster-sub-tail" >' . (empty($guest_data['guest_first_name'][$i][$j][$k])? '-': $guest_data['guest_first_name'][$i][$j][$k]) . '</div>';
			$ret .= '</div>';
            $ret .= '<div class="tourmaster-guest-info-input-wrap" >';
            $ret .= '<div class="tourmaster-sub-head" >' . esc_html__('Last Name', 'tourmaster') . '</div>';
            $ret .= '<div class="tourmaster-sub-tail" >' . (empty($guest_data['guest_last_name'][$i][$j][$k])? '-': $guest_data['guest_last_name'][$i][$j][$k]) . '</div>';
			$ret .= '</div>';

			// additional traveller fields
			if( !empty($guest_fields) ){
				foreach( $guest_fields as $field ){
                    $field_value = empty($guest_data['traveller_' . $field['slug']][$i][$j][$k])? '': $guest_data['traveller_' . $field['slug']][$i][$j][$k];

					if( !empty($field['width']) ){
						$ret .= '<div style="float: left; width: ' . esc_attr($field['width']) . '" >';
					}

					$ret .= '<div class="tourmaster-guest-info-custom" >';
					if( $field['type'] == 'combobox' ){	

						$count = 0;
						foreach( $field['options'] as $option_val => $option_title ){ $count++;
							if( $count == 1 ){
								$ret .= '<div class="tourmaster-sub-head" >' . $option_title . '</div>';
							}else if( empty($field_value) ){
                                $ret .=  '<div class="tourmaster-sub-tail" >-</div>';
                            }else if( $field_value == $option_val ){
                                $ret .=  '<div class="tourmaster-sub-tail" >' . $option_title . '</div>';
							}
						}
					}else{
                        $ret .= '<div class="tourmaster-sub-head" >' . $field['title'] . '</div>';
                        $ret .= '<div class="tourmaster-sub-tail" >' . (empty($field_value)? '-': $field_value) . '</div>';
					}
					$ret .= '</div>';

					if( !empty($field['width']) ){
						$ret .= '</div>';
					}
				}
			}

			$ret .= '</div>';
			$ret .= '</div>';

			return $ret;
		}
	}

    if( !function_exists('tourmaster_room_contact_detail') ){
        function tourmaster_room_contact_detail( $booking_details = array() ){

            $ret = '<div class="tourmaster-room-payment-contact-form tourmaster-item-pdlr" >';

            // traveller detail
            $required_guest_info = tourmaster_get_option('room_general', 'required-guest-info', 'enable');

            if( $required_guest_info == 'enable' ){
                $guest_fields = tourmaster_get_option('room_general', 'additional-guest-fields', '');
                if( !empty($guest_fields) ){
                    $guest_fields = tourmaster_read_custom_fields($guest_fields);
                }

                $ret .= '<div class="tourmaster-room-payment-guest-info-wrap tourmaster-form-field tourmaster-with-border" >';
                for( $i = 0; $i < sizeof($booking_details); $i++ ){
                    $booking_detail = $booking_details[$i];

                    $ret .= '<h3 class="tourmaster-payment-contact-title" >';
                    $ret .= sprintf(esc_html__('Guest Detail : %s', 'tourmaster'), get_the_title($booking_detail['room_id']));
                    $ret .= '</h3>';

                    $ret .= '<div class="tourmaster-room-payment-guest-info-inner" >';
                    for( $j = 0; $j < intval($booking_detail['room_amount']); $j++ ){
                        $ret .= ($booking_detail['room_amount'] > 1)? '<h4>' . sprintf(esc_html__('Room %d', 'tourmaster'), ($j + 1)) . '</h4>': '';
                        
                        $guest_amount = intval($booking_detail['adult'][$j]) + intval($booking_detail['children'][$j]);
                        for( $k = 0; $k < $guest_amount; $k++ ){
                            $ret .= tourmaster_room_payment_guest_input($guest_fields, $i, $j, $k);
                        }
                    }
                    $ret .= '</div>';
                }
                $ret .= '</div>';
                $ret .= '<div class="tourmaster-divider"></div>';
            }
            
            // form field
			$contact_fields = tourmaster_room_payment_contact_form_fields();
			$ret .= '<div class="tourmaster-room-payment-contact-wrap tourmaster-form-field tourmaster-with-border" >';
			$ret .= '<h3 class="tourmaster-payment-contact-title" >';
			$ret .= esc_html__('Contact and Billing Details', 'tourmaster');
			$ret .= '</h3>';
			foreach( $contact_fields as $field_slug => $contact_field ){
				$contact_field['echo'] = false;
				$contact_field['slug'] = $field_slug;

				$value = empty($booking_detail[$field_slug])? '': $booking_detail[$field_slug];
                
				$ret .= tourmaster_get_form_field($contact_field, 'contact', $value);
			}
			$ret .= '</div>';

			// billing address
            $ret .= '<div class="tourmaster-payment-billing-separate-wrap" ><label>';
			$ret .= '<input type="checkbox" class="tourmaster-payment-billing-separate" />';
			$ret .= '<span class="tourmaster-text" >' . esc_html__('Use different detail for billing', 'tourmaster') . '</span>';
			$ret .= '</label></div>';

			$ret .= '<div class="tourmaster-room-payment-billing-wrap tourmaster-form-field tourmaster-with-border" >';
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
            $ret .= '<div class="tourmaster-divider" ></div>';

			// additional notes
			$additional_notes = empty($booking_detail['additional_notes'])? '': $booking_detail['additional_notes'];
			$ret .= '<div class="tourmaster-room-payment-additional-note-wrap tourmaster-form-field tourmaster-with-border" >';
			$ret .= '<h3 class="tourmaster-payment-additional-note-title" >';
			$ret .= esc_html__('Notes', 'tourmaster');
			$ret .= '</h3>';
			$ret .= '<div class="tourmaster-additional-note-field clearfix">';
			$ret .= '<div class="tourmaster-head">' . esc_html__('Additional Notes', 'tourmaster') . '</div>';
			$ret .= '<div class="tourmaster-tail clearfix">';
			$ret .= '<textarea name="additional_notes" >' . esc_textarea($additional_notes) . '</textarea>';
			$ret .= '</div>';
			$ret .= '</div>'; // additional-note-field
			$ret .= '</div>'; // tourmasster-payment-additional-note-wrap
            $ret .= '</div>';
            
			return $ret;

        }
    }
    
    if( !function_exists('tourmaster_room_display_contact_detail') ){
        function tourmaster_room_display_contact_detail( $booking_details = array(), $contact_info = array() ){

            $ret = '<div class="tourmaster-room-payment-contact-form tourmaster-item-pdlr" >';

            // traveller detail
            $required_guest_info = tourmaster_get_option('room_general', 'required-guest-info', 'enable');

            if( $required_guest_info == 'enable' ){
                $guest_fields = tourmaster_get_option('room_general', 'additional-guest-fields', '');
                if( !empty($guest_fields) ){
                    $guest_fields = tourmaster_read_custom_fields($guest_fields);
                }

                $ret .= '<div class="tourmaster-room-payment-guest-info-wrap tourmaster-form-field tourmaster-with-border" >';
                for( $i = 0; $i < sizeof($booking_details); $i++ ){
                    $booking_detail = $booking_details[$i];

                    $ret .= '<h3 class="tourmaster-payment-contact-title" >';
                    $ret .= sprintf(esc_html__('Guest Detail : %s', 'tourmaster'), get_the_title($booking_detail['room_id']));
                    $ret .= '</h3>';

                    $ret .= '<div class="tourmaster-room-payment-guest-info-inner" >';
                    for( $j = 0; $j < intval($booking_detail['room_amount']); $j++ ){
                        $ret .= ($booking_detail['room_amount'] > 1)? '<h4>' . sprintf(esc_html__('Room %d', 'tourmaster'), ($j + 1)) . '</h4>': '';
                        
                        $guest_amount = intval($booking_detail['adult'][$j]) + intval($booking_detail['children'][$j]);
                        for( $k = 0; $k < $guest_amount; $k++ ){
                            $ret .= tourmaster_room_payment_display_guest_input($guest_fields, $i, $j, $k, $contact_info);
                        }
                    }
                    $ret .= '</div>';
                }
                $ret .= '</div>';
                $ret .= '<div class="tourmaster-divider"></div>';
            }
            
            // form field
			$contact_fields = tourmaster_room_payment_contact_form_fields();
			$ret .= '<div class="tourmaster-room-payment-contact-wrap tourmaster-form-field tourmaster-with-border" >';
			$ret .= '<h3 class="tourmaster-payment-contact-title" >';
			$ret .= esc_html__('Contact Details', 'tourmaster');
			$ret .= '</h3>';
			foreach( $contact_fields as $field_slug => $contact_field ){
                $contact_field['type'] = 'plain-text';
				$contact_field['echo'] = false;
				$contact_field['slug'] = $field_slug;

				$value = empty($contact_info[$field_slug])? '': $contact_info[$field_slug];

				$ret .= tourmaster_get_form_field($contact_field, 'contact', $value);
			}
			$ret .= '</div>';

			// billing address
            if( !empty($contact_info['required-billing']) || $contact_info['required-billing'] == 'true' ){
                $ret .= '<div class="tourmaster-divider" ></div>';
                $ret .= '<h3 class="tourmaster-payment-contact-title" >';
                $ret .= esc_html__('Billing Details', 'tourmaster');
                $ret .= '</h3>';
                $ret .= '<div class="tourmaster-room-payment-contact-wrap tourmaster-form-field tourmaster-with-border" >';
                foreach( $contact_fields as $field_slug => $contact_field ){
                    $contact_field['type'] = 'plain-text'; 
                    $contact_field['echo'] = false;
                    $contact_field['slug'] = 'billing_' . $field_slug;

                    $value = empty($contact_info['billing_' . $field_slug])? '': $contact_info['billing_' . $field_slug];
    
                    $ret .= tourmaster_get_form_field($contact_field, 'contact', $value);
                }
                $ret .= '</div>'; // tourmaster-payment-billing-wrap
            }
           
            $ret .= '<div class="tourmaster-divider" ></div>';

			// additional notes
			$additional_notes = empty($booking_detail['additional_notes'])? '': $booking_detail['additional_notes'];
			$ret .= '<div class="tourmaster-room-payment-additional-note-wrap tourmater-type-plain-text tourmaster-form-field tourmaster-with-border" >';
			$ret .= '<h3 class="tourmaster-payment-additional-note-title" >';
			$ret .= esc_html__('Notes', 'tourmaster');
			$ret .= '</h3>';
			$ret .= '<div class="tourmaster-additional-note-field clearfix">';
			$ret .= '<div class="tourmaster-head">' . esc_html__('Additional Notes', 'tourmaster') . '</div>';
			$ret .= '<div class="tourmaster-tail clearfix">';
			$ret .= tourmaster_content_filter(111 . $additional_notes);
			$ret .= '</div>';
			$ret .= '</div>'; // additional-note-field
			$ret .= '</div>'; // tourmasster-payment-additional-note-wrap
            $ret .= '</div>';
            
			return $ret;

        }
    }
    
    if( !function_exists('tourmaster_room_single_price_breakdown') ){
        function tourmaster_room_single_price_breakdown( $booking_detail = array(), $price_breakdown = array(), $room_number = 0 ){
            echo '<div class="tourmaster-room-single-price-breakdown" >';
            echo '<i class="tourmaster-lightbox-close icon_close"></i>';
            echo '<h4 class="tourmaster-title">' . esc_html__('Pice Breakdown', 'tourmaster') . '</h4>';
            echo '<h4 class="tourmaster-room-title" >' . get_the_title($booking_detail['room_id']) . '</h4>';
            echo '<div class="tourmaster-room-duration" >' . tourmaster_room_booking_duration_info($booking_detail['start_date'], $booking_detail['end_date'], false) . '</div>';
            echo '<div class="tourmaster-divider" ></div>';
            echo '<h5 class="tourmaster-amount-title" >';
            echo ($booking_detail['room_amount'] > 1 )? sprintf(esc_html__('Room %d :', 'tourmaster'), ($room_number+1)) . ' ':  '';
            echo sprintf(_n('%d Adult', '%d Adults', $booking_detail['adult'][$room_number], 'tourmaster'), $booking_detail['adult'][$room_number]) . ' '; 
            echo sprintf(_n('%d Children', '%d Childrens', $booking_detail['children'][$room_number], 'tourmaster'), $booking_detail['children'][$room_number]) . ' '; 
            echo '</h5>';

            echo '<ul>';
            foreach($price_breakdown['room-dates'][$room_number] as $date => $prices ){
                echo '<li class="clearfix" ><span class="tourmaster-head" >';
                echo tourmaster_date_format($date) . ' - ';
                echo sprintf(esc_html__('Room price : %s', 'tourmaster'), tourmaster_money_format($prices['base-price'])) . ' ';
                if( !empty($prices['additional-adult-price']) ){
                    echo sprintf(esc_html__('Additional adult : %s', 'tourmaster'), tourmaster_money_format($prices['additional-adult-price'])) . ' ';
                }
                if( !empty($prices['additional-child-price']) ){
                    echo sprintf(esc_html__('Additional child : %s', 'tourmaster'), tourmaster_money_format($prices['additional-child-price'])) . ' ';
                }
                echo '</span><span class="tourmaster-tail" >' . tourmaster_money_format($prices['total-price']) . '</span></li>';
            }
            echo '</ul>';

            echo '<div class="tourmaster-divider" ></div>';

            echo '<div class="tourmaster-room-total-price clearfix" >';
            echo '<div class="tourmaster-head" >' . esc_html__('Total', 'tourmaster') . '</div>';
            echo '<div class="tourmaster-tail" >' . tourmaster_money_format($price_breakdown['room-prices'][$room_number]) . '</div>';
            echo '</div>';
            
            echo '</div>';
        }
    }
    if( !function_exists('tourmaster_room_price_summary') ){
        function tourmaster_room_price_summary( $booking_details = array(), $price_breakdowns = array() ){

            echo '<div class="tourmaster-room-price-summary-wrap tourmaster-room-service-form tourmaster-item-mglr" ';
            echo ' data-remove-head="' . esc_html__('Just To Confirm', 'tourmaster') . '" ';
			echo ' data-remove-yes="' . esc_html__('Yes', 'tourmaster') . '" ';
			echo ' data-remove-no="' . esc_html__('No', 'tourmaster') . '" ';
			echo ' data-remove-text="' . esc_html__('Are you sure you want to do this ?', 'tourmaster') . '" ';
			echo ' data-remove-sub="" ';
            echo ' >';
            echo '<h3 class="tourmaster-room-price-summary-title" >' . esc_html__('Price Summary and Additional Services', 'tourmaster') . '</h3>';
            
            for( $i = 0; $i < sizeof($booking_details); $i++ ){
                $booking_detail = $booking_details[$i];
                $price_breakdown = $price_breakdowns[$i];
                $room_option = tourmaster_get_post_meta($booking_detail['room_id'], 'tourmaster-room-option');
                
                echo '<div class="tourmaster-room-price-summary-block" >';
                echo '<div class="tourmaster-room-price-summary-room-title" >' . get_the_title($booking_detail['room_id']) . '</div>';
                echo '<div class="tourmaster-room-price-summary-room-duration" >' . tourmaster_room_booking_duration_info($booking_detail['start_date'], $booking_detail['end_date'], false) . '</div>';
            
                for( $j = 0; $j < $booking_detail['room_amount']; $j++ ){
                    echo '<div class="tourmaster-room-price-summary-item" >';
                    echo '<h3 class="tourmaster-title" >';

                    echo ($booking_detail['room_amount'] > 1 )? sprintf(esc_html__('Room %d :', 'tourmaster'), ($j+1)) . ' ':  '';
                    echo sprintf(_n('%d Adult', '%d Adults', $booking_detail['adult'][$j], 'tourmaster'), $booking_detail['adult'][$j]) . ' '; 
                    echo sprintf(_n('%d Children', '%d Childrens', $booking_detail['children'][$j], 'tourmaster'), $booking_detail['children'][$j]) . ' '; 
                    
                    echo '<span class="tourmaster-price-breakdown-title">( price breakdown )';
                    echo tourmaster_room_single_price_breakdown($booking_detail, $price_breakdown, $j);
                    echo '</span>';
                    echo '<span class="tourmaster-price" >';
                    echo tourmaster_money_format($price_breakdown['room-prices'][$j]);
                    echo '<i class="tourmaster-room-remove-room fa fa-trash-o" data-i="' . esc_attr($i) . '" data-j="' . esc_attr($j) . '" ></i>';
                    echo '</span>';
                    echo '</h3>';
                    if( !empty($room_option['room-service']) ){
                        echo '<div class="tourmaster-service-wrap" >';
                        foreach( $room_option['room-service'] as $service_id ){
                            $service_option = get_post_meta($service_id, 'tourmaster-service-option', true);

                            echo '<div class="tourmaster-service" >';
                            echo '<label class="tourmaster-label-checkbox" ><input type="checkbox" ';
                            echo (!empty($service_option['mandatory']) && $service_option['mandatory'] == 'enable')? 'disabled ': '';
                            echo !empty($booking_detail['services'][$j][$service_id])? 'checked ': '';
                            echo ' /><span></span></label>';
                            echo '<span class="tourmaster-service-label" >';
                            echo '<span class="tourmaster-head" >';
                            echo get_the_title($service_id) . ' - ';
                            
                            if( $service_option['per'] == 'person' ){
                                echo tourmaster_money_format($service_option['price']) . ' / ' . esc_html__('Person', 'tourmaster');
                                echo '</span>';
                                echo '<input type="hidden" name="service[' . $i . '][' . $j . '][' . $service_id . ']" value="' . esc_attr($booking_detail['services'][$j][$service_id]) . '" />';
                            }else if( $service_option['per'] == 'night' ){
                                echo tourmaster_money_format($service_option['price']) . ' / ' . esc_html__('Night', 'tourmaster');
                                echo '</span>';
                                echo '<input type="hidden" name="service[' . $i . '][' . $j . '][' . $service_id . ']" value="' . esc_attr($booking_detail['services'][$j][$service_id]) . '" />';
                            }else if( $service_option['per'] == 'room' ){
                                echo tourmaster_money_format($service_option['price']) . ' / ' . esc_html__('Room', 'tourmaster');
                                echo '</span>';
                                echo '<input type="hidden" name="service[' . $i . '][' . $j . '][' . $service_id . ']" value="' . esc_attr($booking_detail['services'][$j][$service_id]) . '" />';
                            }else{
                                $unit_text = (empty($service_option['unit-text'])? esc_html__('Unit', 'tourmaster'): $service_option['unit-text']);
                                echo tourmaster_money_format($service_option['price']) . ' / ' . $unit_text;
                                echo '</span>';
                                echo '<input type="text" class="tourmaster-service-amount" name="service[' . $i . '][' . $j . '][' . $service_id . ']" value="' . esc_attr($booking_detail['services'][$j][$service_id]) . '" />';
                                echo '<span>' . $unit_text . '</span>';
                            }
                            echo '</span>';
                            echo '</div>';
                        }

                        echo '<div class="tourmaster-service-total clearfix" >';
                        echo '<div class="tourmaster-head" >' . esc_html__('Additional Services Total', 'tourmaster') . '</div>';
                        echo '<div class="tourmaster-tail" >' . tourmaster_money_format($price_breakdown['room-service-prices'][$j]) . '</div>';
                        echo '</div>';
                        echo '</div>'; // tourmaster-service-wrap
                    }
                    
                    echo '</div>'; // tourmaster-room-price-summary-item
                }
    
                echo '</div>'; // tourmaster-room-price-summary-block
            } 
            
            echo '</div>'; // tourmaster-room-price-summary-wrap 
        }
    }
    if( !function_exists('tourmaster_get_room_booking_price_breakdown') ){
		function tourmaster_get_room_booking_price_breakdown($booking_details, $price_breakdowns){

			$ret  = '<div class="tourmaster-price-breakdown" >';
            
            for( $i = 0; $i < sizeof($booking_details); $i++ ){
                $booking_detail = $booking_details[$i];
                $price_breakdown = $price_breakdowns[$i];

                for( $j = 0; $j < $booking_detail['room_amount']; $j++ ){

                    // title
                    $ret .= '<div class="tourmaster-price-breakdown-room" >';
                    $ret .= '<div class="tourmaster-price-breakdown-room-head" >';
                    $ret .= '<span class="tourmaster-head" >';
                    if( $booking_detail['room_amount'] > 1 ){
                        $ret .= sprintf(esc_html__('%s : Room %d', 'tourmaster'), get_the_title($booking_detail['room_id']), ($j+1));
                    }else{
                        $ret .= get_the_title($booking_detail['room_id']);
                    }
                    $ret .= '</span>';
                    $ret .= '<span class="tourmaster-tail" >(';
                    $ret .= sprintf(_n('%d Adult', '%d Adults', $booking_detail['adult'][$j], 'tourmaster'), $booking_detail['adult'][$j]) . ' '; 
                    $ret .= sprintf(_n('%d Children', '%d Childrens', $booking_detail['children'][$j], 'tourmaster'), $booking_detail['children'][$j]) . ' '; 
                    $ret .= ')</span>';
                    $ret .= '<div class="tourmaster-info" >';
                    $ret .= tourmaster_room_booking_duration_info($booking_detail['start_date'], $booking_detail['end_date'], false, false);
                    $ret .= '</div>';
                    $ret .= '</div>';

                    // price
                    $ret .= '<div class="tourmaster-price-breakdown-room-price" >';
                    $ret .= '<span class="tourmaster-head" >' . esc_html__('Room Price', 'tourmaster') . '</span>';
                    $ret .= '<span class="tourmaster-tail tourmaster-right" >' . tourmaster_money_format($price_breakdown['room-prices'][$j]) . '</span>';
                    $ret .= '</div>';

                    if( !empty($price_breakdown['room-service-prices'][$j]) ){
                        $ret .= '<div class="tourmaster-price-breakdown-room-price" >';
                        $ret .= '<span class="tourmaster-head" >' . esc_html__('Additional Services', 'tourmaster') . '</span>';
                        $ret .= '<span class="tourmaster-tail tourmaster-right" >' . tourmaster_money_format($price_breakdown['room-service-prices'][$j]) . '</span>';
                        foreach( $booking_detail['services'][$j] as $service_id => $amount ){
                            if( !empty($amount) ){
                                $service_option = get_post_meta($service_id, 'tourmaster-service-option', true);
                                if( $service_option['per'] == 'unit' ){
                                    $ret .= '<div class="tourmaster-sub-text" >' . get_the_title($service_id) . ' x ' . $amount . '</div>';
                                }else{
                                    $ret .= '<div class="tourmaster-sub-text" >' . get_the_title($service_id) . '</div>';
                                }
                            }
                        }
                        $ret .= '</div>';
                    }
                    $ret .= '</div>';
                    
                }
            }
			
			$ret .= '<div class="tourmaster-price-breakdown-summary" >';

            // coupon

			if( !empty($price_breakdowns['coupon']) && $price_breakdowns['coupon']['type'] == 'before-tax' ){
                
                if( !empty($price_breakdowns['coupon']['coupon-code']) ){
                    $ret .= '<div class="tourmaster-price-breakdown-coupon-code" >';
                    $ret .= '<span class="tourmaster-head" >' . esc_html__('Coupon Code :', 'tourmaster') . '</span>';
                    $ret .= '<span class="tourmaster-tail" > ';
                    $ret .= '<span class="tourmaster-coupon-code" >' . $price_breakdowns['coupon']['coupon-code'] . '</span>';
                    $ret .= '</span>';
                    $ret .= '</div>';
                }
               
                if( !empty($price_breakdowns['coupon']['discount-price']) ){
                    $ret .= '<div class="tourmaster-price-breakdown-coupon-amount" >';
                    $ret .= '<span class="tourmaster-head" >' . esc_html__('Coupon Discount Price', 'tourmaster') . '</span>';
                    $ret .= '<span class="tourmaster-tail tourmaster-right" >- ' . tourmaster_money_format($price_breakdowns['coupon']['discount-price']) . '</span>';
                    $ret .= '</div>';
                }
            }

            // total price
			$ret .= '<div class="tourmaster-price-breakdown-sub-total " >';
			$ret .= '<span class="tourmaster-head" >' . esc_html__('Total Price', 'tourmaster') . '</span>';
			$ret .= '<span class="tourmaster-tail tourmaster-right" >';
			$ret .= tourmaster_money_format($price_breakdowns['total-price']);
			$ret .= '</span>';
			$ret .= '</div>';

			// tax
			if( !empty($price_breakdowns['tax-rate']) && !empty($price_breakdowns['tax-price']) ){
				$ret .= '<div class="tourmaster-price-breakdown-tax-due" >';
				$ret .= '<span class="tourmaster-head" >' . sprintf(esc_html__('Tax %d%%', 'tourmaster'), $price_breakdowns['tax-rate']) . '</span>';
				$ret .= '<span class="tourmaster-tail tourmaster-right" >';
				$ret .= tourmaster_money_format($price_breakdowns['tax-price']);
				$ret .= '</span>';
				$ret .= '</div>';
			}

			// coupon
			if( !empty($price_breakdowns['coupon']) && $price_breakdowns['coupon']['type'] == 'after-tax' ){
                if( !empty($price_breakdowns['coupon']['coupon-code']) ){
                    $ret .= '<div class="tourmaster-price-breakdown-coupon-code" >';
                    $ret .= '<span class="tourmaster-head" >' . esc_html__('Coupon Code :', 'tourmaster') . '</span>';
                    $ret .= '<span class="tourmaster-tail" > ';
                    $ret .= '<span class="tourmaster-coupon-code" >' . $price_breakdowns['coupon']['coupon-code'] . '</span>';
                    $ret .= '</span>';
                    $ret .= '</div>';
                }

                if( !empty($price_breakdowns['coupon']['discount-price']) ){
                    $ret .= '<div class="tourmaster-price-breakdown-coupon-amount" >';
                    $ret .= '<span class="tourmaster-head" >' . esc_html__('Coupon Discount Price', 'tourmaster') . '</span>';
                    $ret .= '<span class="tourmaster-tail tourmaster-right" >- ' . tourmaster_money_format($price_breakdowns['coupon']['discount-price']) . '</span>';
                    $ret .= '</div>';
                }
            }

			$ret .= '</div>'; // tourmaster-price-breakdown-summary
			$ret .= '<div class="clear"></div>';
			$ret .= '</div>'; // tourmaster-price-breakdown
            
            $ret .= '<div class="tourmaster-my-booking-single-total-price clearfix" >';
            $ret .= '<div class="tourmaster-my-booking-single-field clearfix" >';
            $ret .= '<span class="tourmaster-head">' . esc_html__('Grand Total', 'tourmaster') . '</span> ';
            $ret .= '<span class="tourmaster-tail">' . tourmaster_money_format($price_breakdowns['grand-total-price']) . '</span>';
            $ret .= '</div>';
            $ret .= '</div>';

			return $ret;
		} // tourmaster_get_tour_price_breakdown
	}	    
    
    if( !function_exists('tourmaster_room_booking_complete') ){
        function tourmaster_room_booking_complete(){
            
            echo '<div class="tourmaster-room-booking-complete" >';
            echo '<h3 class="tourmaster-title" >' . esc_html__('Booking Completed!', 'tourmaster') . '</h3>';
            echo '<div class="tourmaster-caption" >' . esc_html__('Thank you!', 'tourmaster') . '</div>';
            echo '<div class="tourmaster-content" >';
            esc_html_e('Your booking detail has been sent to your email.', 'tourmaster');
            if( is_user_logged_in() ){
                echo '<br>';
                esc_html_e('You can check the payment status from your dashboard', 'tourmaster');
            }
            echo '</div>';
            if( is_user_logged_in() ){
                echo '<a class="tourmaster-room-button" href="' . esc_attr(tourmaster_get_template_url('user')) . '" >' . esc_html__('Go to my dashboard', 'tourmaster') . '</a>';
            }
            echo '</div>';
        }
    }
    if( !function_exists('tourmaster_room_booking_paypal_complete') ){
        function tourmaster_room_booking_paypal_complete(){
            echo '<div class="tourmaster-room-booking-complete" >';
            echo '<h3 class="tourmaster-title" >' . esc_html__('Booking Completed!', 'tourmaster') . '</h3>';
            echo '<div class="tourmaster-caption" >' . esc_html__('Thank you!', 'tourmaster') . '</div>';
            echo '<div class="tourmaster-content" >';
            esc_html_e('Your booking detail will be sent to your email shortly.', 'tourmaster');
            echo '<br>';
            esc_html_e('( There might be some delay processing the paypal payment )', 'tourmaster');
            if( is_user_logged_in() ){
                echo '<br>';
                esc_html_e('You can check the payment status from your dashboard', 'tourmaster');
            }
            echo '</div>';
            if( is_user_logged_in() ){
                echo '<a class="tourmaster-room-button" href="' . esc_attr(tourmaster_get_template_url('user')) . '" >' . esc_html__('Go to my dashboard', 'tourmaster') . '</a>';
            }
            echo '</div>';
        }
    }

    // ret_data : false to return coupon code if available
    if( !function_exists('tourmaster_room_check_coupon_code') ){
        function tourmaster_room_check_coupon_code($coupon_code, $ret_data = true){ 

            $coupons = get_posts(array(
				'post_type' => 'room_coupon', 
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
					// $condition = array('coupon_code'=>$coupon_code, 'id'=>$tid);
					// $applied_coupon = tourmaster_get_booking_data($condition, array(), 'COUNT(*)');
				}

				if( empty($applied_coupon) ){

					// check expiry
					if( !empty($coupon_option['coupon-expiry']) ){
						if( strtotime(current_time("Y-m-d")) > strtotime($coupon_option['coupon-expiry']) ){
                            if( $ret_data ){
                                return array(
                                    'status' => 'failed',
                                    'message' => esc_html__('The coupon has been expired', 'tourmaster')
                                );
                            }else{
                                return '';
                            }
						}
					}

					// check specific tour
					if( !empty($coupon_option['apply-to-specific-tour']) ){
						$allow_tours = array_map('trim', explode(',', $coupon_option['apply-to-specific-tour']));
						if( !in_array($tour_id, $allow_tours) ){
							if( $ret_data ){
                                return array(
                                    'status' => 'failed',
                                    'message' => esc_html__('The coupon is not available', 'tourmaster')
                                );
                            }else{
                                return '';
                            }
						}
					}

					// check the available number
                    /*
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
                    */
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
                if( $ret_data ){
                    return array(
                        'status' => 'success',
                        'message' => $message, 
                        'data' => $coupon_option
                    );
                }else{
                    return $coupon_code;
                }
			}else{
                if( $ret_data ){
                    return array(
                        'status' => 'failed',
                        'message' => esc_html__('Coupon not found', 'tourmaster')
                    );
                }else{
                    return '';
                }
			}

        }
    }

    if( !function_exists('tourmaster_room_format_booking_data') ){
        function tourmaster_room_format_booking_data($booking_details, $services = array()){

            for( $i = 0; $i < sizeof($booking_details); $i++ ){
                if( !empty($services[$i]) ){
                    $booking_details[$i]['services'] = $services[$i];
                }
            }

            return $booking_details;
        }
    }

    if( !function_exists('tourmaster_room_get_deposit_info') ){
        function tourmaster_room_get_deposit_info($total_price, $payment_info){

            $enable_deposit_payment = tourmaster_get_option('room_payment', 'enable-deposit-payment', 'enable');
            if( $enable_deposit_payment == 'disable' ){
                return array(
                    'deposit_rate' => 100,
                    'deposit_amount' => $total_price
                );
            }

            $paid_amount = 0;
            $paid_times = 0;
            $deposit_rate = 0;
            $deposit_amount = 0;

            if( !empty($payment_info) ){
                foreach( $payment_info as $payment ){
                    if( !empty($payment['amount']) ){
                        $paid_times++;
                        $paid_amount += floatval($payment['amount']);
                    }
                }
            }

            $deposit_rate = floatval(tourmaster_get_option('room_payment', 'deposit-payment-amount', ''));
            $deposit2_rate = floatval(tourmaster_get_option('room_payment', 'deposit2-payment-amount', ''));
            $deposit3_rate = floatval(tourmaster_get_option('room_payment', 'deposit3-payment-amount', ''));
            $deposit4_rate = floatval(tourmaster_get_option('room_payment', 'deposit4-payment-amount', ''));
            $deposit5_rate = floatval(tourmaster_get_option('room_payment', 'deposit5-payment-amount', ''));

            if( $paid_times == 0 && !empty($deposit_rate) ){
                $current_deposit_rate = $deposit_rate;
                $total_deposit_rate = $deposit_rate;
            }else if( $paid_times == 1 && !empty($deposit2_rate) ){
                $current_deposit_rate = $deposit2_rate;
                $total_deposit_rate = $deposit_rate + $deposit2_rate; 
            }else if( $paid_times == 2 && !empty($deposit3_rate) ){
                $current_deposit_rate = $deposit3_rate;
                $total_deposit_rate = $deposit_rate + $deposit2_rate + $deposit3_rate; 
            }else if( $paid_times == 3 && !empty($deposit4_rate) ){
                $current_deposit_rate = $deposit4_rate;
                $total_deposit_rate = $deposit_rate + $deposit2_rate + $deposit3_rate + $deposit4_rate; 
            }else if( $paid_times == 4 && !empty($deposit5_rate) ){
                $current_deposit_rate = $deposit5_rate;
                $total_deposit_rate = $deposit_rate + $deposit2_rate + $deposit3_rate + $deposit4_rate + $deposit5_rate; 
            }else{
                $current_deposit_rate = 100;
                $total_deposit_rate = 100;
            }

            return array(
                'paid_amount' => $paid_amount,
                'paid_times' => $paid_times,
                'deposit_rate' => $current_deposit_rate,
                'deposit_amount' => ($total_price * ($total_deposit_rate / 100)) - $paid_amount
            );
            
        }
    }

    // get price settings
	if( !function_exists('tourmaster_room_get_submit_receipt_settings') ){
		function tourmaster_room_get_submit_receipt_settings($total_price, $payment_infos){
			
            $deposit_info = tourmaster_room_get_deposit_info($total_price, $payment_infos);

			$ret = array();

            $ret['paid-amount'] = $deposit_info['paid_amount'];
			$ret['deposit-percent'] = '';
			$ret['total-deposit-percent'] = '';

            if( tourmaster_compare_price($total_price, $deposit_info['paid_amount']) || $deposit_info['paid_amount'] > $total_price ){
                $ret['more-payment'] = false;
                $ret['full-payment'] = false;
                $ret['deposit-payment'] = false;
            }else{
                $ret['more-payment'] = true;
			    $ret['full-payment'] = true;
                $ret['full-payment-amount'] = $total_price - $deposit_info['paid_amount'];

                if( $deposit_info['deposit_rate'] < 100 ){
                    $ret['deposit-payment'] = true;
                    $ret['next-deposit-percent'] = $deposit_info['deposit_rate'];
                    $ret['next-deposit-amount'] = $deposit_info['deposit_amount'];
                }else{
                    $ret['deposit-payment'] = false;
                }

            }

			return $ret;
		}
	}

    add_action('wp_ajax_tourmaster_room_service_selected', 'tourmaster_room_ajax_service_selected');
    add_action('wp_ajax_nopriv_tourmaster_room_service_selected', 'tourmaster_room_ajax_service_selected');
    if( !function_exists('tourmaster_room_ajax_service_selected') ){
        function tourmaster_room_ajax_service_selected(){
            
            $data = tourmaster_process_post_data($_POST['data']);
            $booking_details = tourmaster_room_format_booking_data($data['booking_details'], $data['services']);  
            $coupon_code = '';
            $price_breakdowns = tourmaster_room_price_breakdowns($booking_details, $coupon_code);

            // update service prices
            $service_prices = array();
            for( $i = 0; $i < sizeof($booking_details); $i++ ){
                $service_prices[$i] = array_map('tourmaster_money_format', $price_breakdowns[$i]['room-service-prices']);
            }

            // update price sidebar
            ob_start();
            tourmaster_room_price_sidebar($price_breakdowns);
            $price_sidebar = ob_get_contents();
            ob_end_clean();

            die(json_encode(array(
                'price_sidebar' => $price_sidebar,
                'service_prices' => $service_prices
            )));
        }
    }

    add_action('wp_ajax_tourmaster_room_check_coupon_code', 'tourmaster_room_ajax_check_coupon_code');
    add_action('wp_ajax_nopriv_tourmaster_room_check_coupon_code', 'tourmaster_room_ajax_check_coupon_code');
    if( !function_exists('tourmaster_room_ajax_check_coupon_code') ){
        function tourmaster_room_ajax_check_coupon_code(){

            $data = tourmaster_process_post_data($_POST['data']);
            $coupon_code = $data['coupon_code'];
            $coupon_status = tourmaster_room_check_coupon_code($coupon_code);

            die(json_encode(array(
                'message' => $coupon_status['message']
            )));
        }
    }

    add_action('wp_ajax_tourmaster_room_checkout_step', 'tourmaster_room_ajax_checkout_step');
    add_action('wp_ajax_nopriv_tourmaster_room_checkout_step', 'tourmaster_room_ajax_checkout_step');
    if( !function_exists('tourmaster_room_ajax_checkout_step') ){
        function tourmaster_room_ajax_checkout_step(){
            
            $data = tourmaster_process_post_data($_POST['data']);
            $booking_details = tourmaster_room_format_booking_data($data['booking_details'], $data['services']);          
            $coupon_code = tourmaster_room_check_coupon_code($data['coupon_code'], false); 
            $price_breakdowns = tourmaster_room_price_breakdowns($booking_details, $coupon_code);

            // update price sidebar
            ob_start();
            tourmaster_room_price_sidebar($price_breakdowns, 3);
            $price_sidebar = ob_get_contents();
            ob_end_clean();

            die(json_encode(array(
                'price_sidebar' => $price_sidebar
            )));
        }
    }

    add_action('wp_ajax_tourmaster_room_pay_now', 'tourmaster_room_ajax_pay_now');
    add_action('wp_ajax_nopriv_tourmaster_room_pay_now', 'tourmaster_room_ajax_pay_now');
    if( !function_exists('tourmaster_room_ajax_pay_now') ){
        function tourmaster_room_ajax_pay_now(){
            
            $data = tourmaster_process_post_data($_POST['data']);
            $payment_method = trim($_POST['payment_method']);
            $pay_full_amount = (empty($_POST['pay_full_amount']) || $_POST['pay_full_amount'] == 'false')? false: true;
            $booking_details = tourmaster_room_format_booking_data($data['booking_details'], $data['services']);     
            $coupon_code = tourmaster_room_check_coupon_code($data['coupon_code'], false);      
            $price_breakdowns = tourmaster_room_price_breakdowns($booking_details, $coupon_code);
            $user_id = get_current_user_id();

            global $wpdb;
            
            // room order table
            $data = array(
                'user_id' => $user_id,
                'booking_date' => current_time('mysql'),
                'booking_data' => json_encode($booking_details),
                'contact_info' => json_encode($data['contact_info']),
                'coupon_code' => $coupon_code,
                'order_status' => 'pending',
                'price_breakdown' => json_encode($price_breakdowns),
                'total_price' => $price_breakdowns['grand-total-price'],
            );
            $format = array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%f');

            global $tourmaster_currency;
            if( !empty($tourmaster_currency) ){
                $data['currency'] = json_encode($tourmaster_currency);
                $format[] = '%s';
            }

            $wpdb->insert("{$wpdb->prefix}tourmaster_room_order", $data, $format);
            $order_id = $wpdb->insert_id;

            // room date booking
            for( $i = 0; $i < sizeof($booking_details); $i++ ){          
                $data = array(
                    'order_id' => $order_id,
                    'room_id' => $booking_details[$i]['room_id'],
                    'start_date' => $booking_details[$i]['start_date'],
                    'end_date' => $booking_details[$i]['end_date'],
                );
                $format = array('%d', '%d', '%s', '%s');

                for( $j = 0; $j < intval($booking_details[$i]['room_amount']); $j++ ){
                    $wpdb->insert("{$wpdb->prefix}tourmaster_room_booking", $data, $format);
                }
            }

            // update available date
            $occupied_room = array();
            for( $i = 0; $i < sizeof($booking_details); $i++ ){
                if( !in_array($booking_details[$i]['room_id'], $occupied_room) ){
                    tourmaster_room_check_occupied($booking_details[$i]['room_id']);
                    $occupied_room[] = $booking_details[$i]['room_id'];
                }
            }

            if( $payment_method == 'credit-card' ){
                $payment_method = tourmaster_get_option('room_payment', 'credit-card-payment-gateway', '');
            }
            $payment_content = apply_filters('goodlayers_room_' . $payment_method . '_payment_form', '', $order_id, $pay_full_amount);

            die(json_encode(array(
                'order_id' => $order_id,
                'message' => 'success',
                'payment_content' => $payment_content
            )));
            
        }
    }

    add_action('wp_ajax_tourmaster_room_payd_now', 'tourmaster_room_ajax_payd_now');
    add_action('wp_ajax_nopriv_tourmaster_room_payd_now', 'tourmaster_room_ajax_payd_now');
    if( !function_exists('tourmaster_room_ajax_payd_now') ){
        function tourmaster_room_ajax_payd_now(){
            
            $order_id = trim($_POST['tid']);
            $payment_method = trim($_POST['payment_method']);
            $pay_full_amount = (empty($_POST['pay_full_amount']) || $_POST['pay_full_amount'] == 'false')? false: true;

            if( $payment_method == 'credit-card' ){
                $payment_method = tourmaster_get_option('room_payment', 'credit-card-payment-gateway', '');
            }
            $payment_content = apply_filters('goodlayers_room_' . $payment_method . '_payment_form', '', $order_id, $pay_full_amount);

            die(json_encode(array(
                'message' => 'success',
                'payment_content' => $payment_content
            )));
            
        }
    }

    if( !function_exists('tourmaster_get_room_product_order') ){
        function tourmaster_get_room_product_order(){

            $product_id = get_option('tourmaster_room_product_order_id', '');

            if( !empty($product_id) ){
                $product = wc_get_product($product_id);
                if( !empty($product) ){
                    return $product;
                }
            }
            
            $product = new WC_Product();
            $product->set_name(esc_html__('Room Booking', 'tourmaster'));
            $product->set_catalog_visibility('hidden');
            $product->save();

            wp_update_post(array('ID'=>$product->get_id(), 'post_status' => 'private'));

            update_option('tourmaster_room_product_order_id', $product->get_id());

            return $product;

        }
    }

    add_action('wp_ajax_tourmaster_room_pay_woocommerce', 'tourmaster_room_ajax_pay_woocommerce');
    add_action('wp_ajax_nopriv_tourmaster_room_pay_woocommerce', 'tourmaster_room_ajax_pay_woocommerce');
    if( !function_exists('tourmaster_room_ajax_pay_woocommerce') ){
        function tourmaster_room_ajax_pay_woocommerce(){

            $data = tourmaster_process_post_data($_POST['data']);
            $booking_details = tourmaster_room_format_booking_data($data['booking_details'], $data['services']);     
            $coupon_code = tourmaster_room_check_coupon_code($data['coupon_code'], false);      
            $price_breakdowns = tourmaster_room_price_breakdowns($booking_details, $coupon_code);
            $user_id = get_current_user_id();
            $contact_detail = $data['contact_info'];

            global $wpdb;

            // room order table
            $data = array(
                'user_id' => $user_id,
                'booking_date' => current_time('mysql'),
                'booking_data' => json_encode($booking_details),
                'contact_info' => json_encode($data['contact_info']),
                'coupon_code' => $coupon_code,
                'order_status' => 'pending',
                'price_breakdown' => json_encode($price_breakdowns),
                'total_price' => $price_breakdowns['grand-total-price']
            );
            $format = array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%f');

            global $tourmaster_currency;
            if( !empty($tourmaster_currency) ){
                $data['currency'] = json_encode($tourmaster_currency);
                $format[] = '%s';
            }

            $wpdb->insert("{$wpdb->prefix}tourmaster_room_order", $data, $format);
            $order_id = $wpdb->insert_id;

            for( $i = 0; $i < sizeof($booking_details); $i++ ){
                $data = array(
                    'order_id' => $order_id,
                    'room_id' => $booking_details[$i]['room_id'],
                    'start_date' => $booking_details[$i]['start_date'],
                    'end_date' => $booking_details[$i]['end_date'],
                );
                $format = array('%d', '%d', '%s', '%s');

                for( $j = 0; $j < intval($booking_details[$i]['room_amount']); $j++ ){
                    $wpdb->insert("{$wpdb->prefix}tourmaster_room_booking", $data, $format);
                }
            }

            // update available date
            $occupied_room = array();
            for( $i = 0; $i < sizeof($booking_details); $i++ ){
                if( !in_array($booking_details[$i]['room_id'], $occupied_room) ){
                    tourmaster_room_check_occupied($booking_details[$i]['room_id']);
                    $occupied_room[] = $booking_details[$i]['room_id'];
                }
            }

            // create woocommerce object
            $product = tourmaster_get_room_product_order();
            if( !empty($tourmaster_currency) ){
			    $product->set_price(($price_breakdowns['grand-total-price']) * floatval($tourmaster_currency['exchange-rate']));
            }else{
                $product->set_price($price_breakdowns['grand-total-price']);
            }

            if( empty($contact_detail['required-billing']) || $contact_detail['required-billing'] == 'false' ){
                $billing_address = array(
                    'first_name' => empty($contact_detail['first_name'])? '': $contact_detail['first_name'],
                    'last_name'  => empty($contact_detail['last_name'])? '': $contact_detail['last_name'],
                    'email'      => empty($contact_detail['email'])? '': $contact_detail['email'],
                    'address_1'  => empty($contact_detail['address'])? '': $contact_detail['address'],
                    'address_2'  => '',
                    'city'       => '',
                    'state'      => '',
                    'postcode'   => '',
                    'country'    => empty($contact_detail['country'])? '': tourmaster_woocommerce_country_code($contact_detail['country']),
                );
            }else{
                $billing_address = array(
                    'first_name' => empty($contact_detail['billing_first_name'])? '': $contact_detail['billing_first_name'],
                    'last_name'  => empty($contact_detail['billing_last_name'])? '': $contact_detail['billing_last_name'],
                    'email'      => empty($contact_detail['billing_email'])? '': $contact_detail['billing_email'],
                    'address_1'  => empty($contact_detail['billing_address'])? '': $contact_detail['billing_address'],
                    'address_2'  => '',
                    'city'       => '',
                    'state'      => '',
                    'postcode'   => '',
                    'country'    => empty($contact_detail['billing_country'])? '': tourmaster_woocommerce_country_code($contact_detail['billing_country']),
                );
            }

            $wc_order = wc_create_order();
            $wc_order->add_order_note(sprintf(esc_html__('Room order #%d', 'tourmaster'), $order_id));
			$wc_order->add_product($product, 1);
            if( !empty($user_id) ){
                $wc_order->set_customer_id($user_id);
            }
            if( !empty($tourmaster_currency) ){
                $wc_order->set_currency(strtoupper($tourmaster_currency['currency-code']));
            }
			$wc_order->set_address($billing_address,'billing');
            $wc_order->calculate_totals();
			$wc_order->update_status('wc-pending');
			$wc_order->save();

            // update woocommerce order id
            $wpdb->update("{$wpdb->prefix}tourmaster_room_order", array(
                'woocommerce_order_id' => $wc_order->id
            ), array(
                'id' => $order_id
            ), array('%d'), array('%d'));

            die(json_encode(array(
                'message' => 'success',
                'redirect_url' => $wc_order->get_checkout_payment_url()
            )));
        }
    }

    add_action('wp_ajax_tourmaster_room_payd_woocommerce', 'tourmaster_room_ajax_payd_woocommerce');
    add_action('wp_ajax_nopriv_tourmaster_room_payd_woocommerce', 'tourmaster_room_ajax_payd_woocommerce');
    if( !function_exists('tourmaster_room_ajax_payd_woocommerce') ){
        function tourmaster_room_ajax_payd_woocommerce(){
            
            $order_id = trim($_POST['tid']);
            $user_id = get_current_user_id();

            global $wpdb;
            $sql  = "SELECT * FROM {$wpdb->prefix}tourmaster_room_order ";
            $sql .= $wpdb->prepare("WHERE id = %d", $order_id);
            $result = $wpdb->get_row($sql);

            if( !empty($result->woocommerce_order_id) ){
                $wc_order = wc_get_order($result->woocommerce_order_id);
            }

            if( empty($wc_order) ){

                $currency = json_decode($result->currency, true);

                // create woocommerce object
                $product = tourmaster_get_room_product_order();
                if( !empty($currency) ){
                    $product->set_price($result->total_price * floatval($currency['exchange-rate']));
                }else{
                    $product->set_price($result->total_price);
                }

                $contact_detail = json_decode($result->contact_info, true);
                if( empty($contact_detail['required-billing']) || $contact_detail['required-billing'] == 'false' ){
                    $billing_address = array(
                        'first_name' => empty($contact_detail['first_name'])? '': $contact_detail['first_name'],
                        'last_name'  => empty($contact_detail['last_name'])? '': $contact_detail['last_name'],
                        'email'      => empty($contact_detail['email'])? '': $contact_detail['email'],
                        'address_1'  => empty($contact_detail['address'])? '': $contact_detail['address'],
                        'address_2'  => '',
                        'city'       => '',
                        'state'      => '',
                        'postcode'   => '',
                        'country'    => empty($contact_detail['country'])? '': tourmaster_woocommerce_country_code($contact_detail['country']),
                    );
                }else{
                    $billing_address = array(
                        'first_name' => empty($contact_detail['billing_first_name'])? '': $contact_detail['billing_first_name'],
                        'last_name'  => empty($contact_detail['billing_last_name'])? '': $contact_detail['billing_last_name'],
                        'email'      => empty($contact_detail['billing_email'])? '': $contact_detail['billing_email'],
                        'address_1'  => empty($contact_detail['billing_address'])? '': $contact_detail['billing_address'],
                        'address_2'  => '',
                        'city'       => '',
                        'state'      => '',
                        'postcode'   => '',
                        'country'    => empty($contact_detail['billing_country'])? '': tourmaster_woocommerce_country_code($contact_detail['billing_country']),
                    );
                }

                $wc_order = wc_create_order();
                $wc_order->add_order_note(sprintf(esc_html__('Room order #%d', 'tourmaster'), $order_id));
                $wc_order->add_product($product, 1);
                if( !empty($user_id) ){
                    $wc_order->set_customer_id($user_id);
                }
                if( !empty($currency) ){
                    $wc_order->set_currency(strtoupper($currency['currency-code']));
                }
                $order->set_address($billing_address,'billing');
                $wc_order->calculate_totals();
                $wc_order->update_status('wc-pending');
                $wc_order->save();

                // update woocommerce order id
                $wpdb->update("{$wpdb->prefix}tourmaster_room_order", array(
                    'woocommerce_order_id' => $wc_order->id
                ), array(
                    'id' => $order_id
                ), array('%d'), array('%d'));
            }

            die(json_encode(array(
                'message' => 'success',
                'redirect_url' => $wc_order->get_checkout_payment_url()
            )));
            
        }
    }

    add_action('woocommerce_order_status_completed', 'tourmaster_room_woocommerce_order_complete');
    if( !function_exists('tourmaster_room_woocommerce_order_complete') ){
        function tourmaster_room_woocommerce_order_complete( $order_id ){
            
            global $wpdb;
            $sql  = "SELECT * FROM {$wpdb->prefix}tourmaster_room_order ";
            $sql .= $wpdb->prepare("WHERE woocommerce_order_id = %d", $order_id);
            $result = $wpdb->get_row($sql);

            if( !empty($result) ){
                $wpdb->update(
                    "{$wpdb->prefix}tourmaster_room_order", 
                    array('order_status' => 'approved'), 
                    array('id' => $result->id),
                    array('%s'),
                    array('%d')
                );
    
                tourmaster_room_mail_notification('payment-made-mail', $result->id, '');
                tourmaster_room_mail_notification('admin-online-payment-made-mail', $result->id, '');
            }

        }
    }
    

    add_action('wp_ajax_tourmaster_room_pay_later', 'tourmaster_room_ajax_pay_later');
    add_action('wp_ajax_nopriv_tourmaster_room_pay_later', 'tourmaster_room_ajax_pay_later');
    if( !function_exists('tourmaster_room_ajax_pay_later') ){
        function tourmaster_room_ajax_pay_later(){

            $data = tourmaster_process_post_data($_POST['data']);
            $booking_details = tourmaster_room_format_booking_data($data['booking_details'], $data['services']);     
            $coupon_code = tourmaster_room_check_coupon_code($data['coupon_code'], false);      
            $price_breakdowns = tourmaster_room_price_breakdowns($booking_details, $coupon_code);
            $user_id = get_current_user_id();

            $need_admin_approval = (tourmaster_get_option('room_payment', 'payment-admin-approval', 'disable') == 'enable');
            $order_status = ($need_admin_approval)? 'wait-for-approval': 'pending';
            global $wpdb;

            // room order table
            $data = array(
                'user_id' => $user_id,
                'booking_date' => current_time('mysql'),
                'booking_data' => json_encode($booking_details),
                'contact_info' => json_encode($data['contact_info']),
                'coupon_code' => $coupon_code,
                'order_status' => $order_status,
                'price_breakdown' => json_encode($price_breakdowns),
                'total_price' => $price_breakdowns['grand-total-price'],
            );
            $format = array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%f');

            global $tourmaster_currency;
            if( !empty($tourmaster_currency) ){
                $data['currency'] = json_encode($tourmaster_currency);
                $format[] = '%s';
            }

            $wpdb->insert("{$wpdb->prefix}tourmaster_room_order", $data, $format);
            $order_id = $wpdb->insert_id;

            for( $i = 0; $i < sizeof($booking_details); $i++ ){
                $data = array(
                    'order_id' => $order_id,
                    'room_id' => $booking_details[$i]['room_id'],
                    'start_date' => $booking_details[$i]['start_date'],
                    'end_date' => $booking_details[$i]['end_date'],
                );
                $format = array('%d', '%d', '%s', '%s');
                
                for( $j = 0; $j < intval($booking_details[$i]['room_amount']); $j++ ){
                    $wpdb->insert("{$wpdb->prefix}tourmaster_room_booking", $data, $format);
                }
            }

            // update available date
            $occupied_room = array();
            for( $i = 0; $i < sizeof($booking_details); $i++ ){
                if( !in_array($booking_details[$i]['room_id'], $occupied_room) ){
                    tourmaster_room_check_occupied($booking_details[$i]['room_id']);
                    $occupied_room[] = $booking_details[$i]['room_id'];
                }
            }

            if( !empty($user_id) ){
                if( $order_status == 'wait-for-approval' ){
                    tourmaster_room_mail_notification('booking-made-approval-mail', $order_id);
                    tourmaster_room_mail_notification('admin-booking-made-approval-mail', $order_id);
                }else{
                    tourmaster_room_mail_notification('booking-made-mail', $order_id);
                    tourmaster_room_mail_notification('admin-booking-made-mail', $order_id);
                }
            }else{
                tourmaster_room_mail_notification('guest-booking-made-mail', $order_id);
                tourmaster_room_mail_notification('admin-guest-booking-made-mail', $order_id);
            }

            die(json_encode(array(
                'message' => 'success',
                'booking_details' => $booking_details
            )));
        }
    }

    if( !function_exists('tourmaster_room_payment_order_status') ){
        function tourmaster_room_payment_order_status($total_price, $payment_infos, $online = false){

            $paid_amount = 0;
            foreach( $payment_infos as $payment_info ){
                $paid_amount += floatval($payment_info['amount']);
            }

            if( $paid_amount == 0 ){
                return 'pending';
            }else if( tourmaster_compare_price($total_price, $paid_amount) || $paid_amount > $total_price ){
                if( $online ){
                    return 'online-paid';
                }else{
                    return 'approved';
                }
            }else{
                return 'deposit-paid';
            }

        }
    }