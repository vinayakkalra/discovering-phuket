<?php

    add_action('wp_footer', 'tourmaster_room_ask_login');
    if( !function_exists('tourmaster_room_ask_login') ){
        function tourmaster_room_ask_login(){

            if( !is_user_logged_in() ){
                $guest_booking = tourmaster_get_option('general', 'enable-guest-booking', 'enable');
				$guest_booking = ($guest_booking == 'enable')? true: false;

				echo tourmaster_lightbox_content(array(
					'id' => 'room-proceed-without-login',
					'title' => esc_html__('Proceed Booking', 'tourmaster'),
					'content' => tourmaster_get_login_form2(false, array(
						'continue-as-guest' => $guest_booking,
						'redirect' => 'room-payment'
					))
				));
            }

        }
    }

    if( !function_exists('tourmaster_room_amount_selection') ){
        function tourmaster_room_amount_selection( $options = array(), $settings = array(), $bg_class = 'outer' ){

            if( $bg_class == 'outer' ){
                $outer_class = 'gdlr-core-skin-e-background';
                $inner_head_class = 'gdlr-core-skin-e-content';
                $inner_class = 'gdlr-core-skin-e-content';
            }else if( $bg_class == 'inner' ){
                $outer_class = '';
                $inner_head_class = 'gdlr-core-skin-content';
                $inner_class = 'gdlr-core-skin-e-background gdlr-core-skin-e-content';
            }
            
            $ret  = '<div class="tourmaster-room-amount-selection ' . (empty($settings['class'])? '': esc_attr($settings['class'])) . '" >';
            $ret .= '<div class="tourmaster-custom-amount-display ' . esc_attr($outer_class) . '" >';
            $ret .= '<div class="tourmaster-head ' . esc_attr($inner_head_class) . '" >' . (empty($settings['title'])? esc_html__('Guests', 'tourmaster'): $settings['title']) . '</div>';
            $ret .= '<div class="tourmaster-tail ' . esc_attr($inner_class) . '" >';
            foreach( $options as $option ){
                $ret .= '<span class="tourmaster-space" ></span>';
                $ret .= (empty($settings['hide-label'])? $option['label'] . ' ': '') . $option['value'];
            }
            $ret .= '</div>';
            $ret .= '</div>'; // tourmaster-custom-amount-display

            $ret .= '<div class="tourmaster-custom-amount-selection-wrap" >';
            foreach( $options as $slug => $option ){
                $ret .= '<div class="tourmaster-custom-amount-selection-item clearfix" ';
                $ret .= empty($settings['hide-label'])? 'data-label="' . esc_attr($option['label']) . '" ': '';
                $ret .= ' >';
                $ret .= '<div class="tourmaster-head" >' . $option['label'] . '</div>';
                $ret .= '<div class="tourmaster-tail" >';
                $ret .= '<span class="tourmaster-minus" ><i class="icon_minus-06" ></i></span>';
                $ret .= '<span class="tourmaster-amount" >' . $option['value'] . '</span>';
                $ret .= '<span class="tourmaster-plus" ><i class="icon_plus" ></i></span>';
                $ret .= '</div>';
                $ret .= '<input type="hidden" name="' . esc_attr($slug) . '" value="' . esc_attr($option['value']) . '" />';
                $ret .= '</div>';
            }
            $ret .= '</div>'; // tourmaster-custom-amount-selection-wrap

            $ret .= '</div>';
            
            return $ret;
        }
    }

    if( !function_exists('tourmaster_room_datepicker_range') ){
        function tourmaster_room_datepicker_range( $settings = array(), $bg_class = 'outer' ){

            $settings['align'] = empty($settings['align'])? 'horizontal': $settings['align'];
            
            if( !empty($settings['start_date']) && !empty($settings['end_date']) ){

            }else if( !empty($settings['init_date']) && $settings['init_date'] == 'disable' ){
                $settings['start_date'] = '';
                $settings['end_date'] = '';
                $settings['current_date'] = current_time('Y-m-d');
            }else if( !empty($settings['avail-date']) ){
                $avail_dates = explode(',', $settings['avail-date']);
                $settings['start_date'] = array_shift($avail_dates);
                $settings['end_date'] = date_i18n('Y-m-d', strtotime($settings['start_date']) + 86400);
            }else{
                $settings['start_date'] = current_time('Y-m-d');
                $settings['end_date'] = date_i18n('Y-m-d', strtotime($settings['start_date']) + 86400);
            }

            if( $bg_class == 'outer' ){
                $outer_class = 'gdlr-core-skin-e-background';
                $inner_head_class = 'gdlr-core-skin-e-content';
                $inner_class = 'gdlr-core-skin-e-content';
            }else if( $bg_class == 'inner' ){
                $outer_class = '';
                $inner_head_class = 'gdlr-core-skin-content';
                $inner_class = 'gdlr-core-skin-e-background gdlr-core-skin-e-content';
            }
            
            $min_night_stay = tourmaster_get_option('room_general', 'min-night-stay', 1);
            $min_night_stay = empty($min_night_stay)? 1: intval($min_night_stay);
            
            $ret  = '<div class="tourmaster-room-date-selection tourmaster-' . esc_attr($settings['align']) . '" ';
            $ret .= 'data-avail-date="' . (empty($settings['avail-date'])? '': esc_attr($settings['avail-date'])) . '" ';
            $ret .= empty($settings['current_date'])? '': 'data-current-date="' . esc_attr($settings['current_date']) . '" ';
            $ret .= 'data-min-night-stay="' . esc_attr($min_night_stay) . '" ';
            $ret .= ' >';

            $ret .= '<div class="tourmaster-custom-start-date ' . esc_attr($outer_class) . '" >';
            $ret .= '<div class="tourmaster-head ' . esc_attr($inner_head_class) . '" >' . esc_html__('Check In', 'tourmaster') . '</div>';
            $ret .= '<div class="tourmaster-tail ' . esc_attr($inner_class) . '" >' . (empty($settings['start_date'])? '': tourmaster_date_format($settings['start_date'])) . '</div>';
            $ret .= '<input type="hidden" name="start_date" value="' . esc_attr($settings['start_date']) . '" />';
            $ret .= '</div>';

            $ret .= '<div class="tourmaster-custom-end-date ' . esc_attr($outer_class) . '" >';
            $ret .= '<div class="tourmaster-head ' . esc_attr($inner_head_class) . '" >' . esc_html__('Check Out', 'tourmaster') . '</div>';
            $ret .= '<div class="tourmaster-tail ' . esc_attr($inner_class) . '" >' . (empty($settings['end_date'])? '': tourmaster_date_format($settings['end_date'])) . '</div>';
            $ret .= '<input type="hidden" name="end_date" value="' . esc_attr($settings['end_date']) . '" />';
            $ret .= '</div>';

            $js_date_format = tourmaster_get_option('general', 'datepicker-date-format', 'd M yy');
            $ret .= '<div class="tourmaster-custom-datepicker-wrap" ';
            $ret .= 'data-date-format="' . esc_attr($js_date_format) . '" ';
            $ret .= ' >';
            $ret .= '<i class="tourmaster-custom-datepicker-close icon_close" ></i>';
            $ret .= '<div class="tourmaster-custom-datepicker-title" ></div>';
            $ret .= '<div class="tourmaster-custom-datepicker-calendar" ></div>';
            $ret .= '</div>'; // tourmaster-custom-datepicker-wrap

            $ret .= '</div>'; // tourmaster-room-date-selection

            return $ret;
        }
    }

    // add single booking sidebar
    add_action('gdlr_core_pb_wrapper_sidebar_right_content', 'tourmaster_gdlr_core_pb_wrapper_sidebar_right_content');
    if( !function_exists('tourmaster_gdlr_core_pb_wrapper_sidebar_right_content') ){
		function tourmaster_gdlr_core_pb_wrapper_sidebar_right_content( $settings ){
            if( !empty($settings['enable-booking-bar']) && $settings['enable-booking-bar'] == 'right' ){
                if( is_single() && get_post_type() == 'room' ){
                    tourmaster_room_get_booking_bar();
                }
            }
        }
    }
    add_action('gdlr_core_pb_wrapper_sidebar_left_content', 'tourmaster_gdlr_core_pb_wrapper_sidebar_left_content');
    if( !function_exists('tourmaster_gdlr_core_pb_wrapper_sidebar_left_content') ){
		function tourmaster_gdlr_core_pb_wrapper_sidebar_left_content( $settings ){
            if( !empty($settings['enable-booking-bar']) && $settings['enable-booking-bar'] == 'left' ){
                if( is_single() && get_post_type() == 'room' ){
                    tourmaster_room_get_booking_bar();
                }
            }
        }
    }

    if( !function_exists('tourmaster_room_get_booking_bar') ){
		function tourmaster_room_get_booking_bar(){

            $room_option = tourmaster_get_post_meta(get_the_ID(), 'tourmaster-room-option');
            $room_option['form-settings'] = empty($room_option['form-settings'])? 'booking': $room_option['form-settings'];

            if( $room_option['form-settings'] == 'none' ){

            }else if( $room_option['form-settings'] == 'custom' ){
                echo '<div class="tourmaster-room-booking-custom-code" >';
                echo tourmaster_text_filter($room_option['form-custom-code']);
                echo '</div>';
            }else{

                echo '<div class="tourmaster-room-booking-bar-wrap" >';
                echo '<div class="tourmaster-room-booking-bar-title" >';
                if( $room_option['form-settings'] == 'both' ){
                    echo '<span class="tourmaster-active" data-room-tab="booking" >' . esc_html__('Book Your Room', 'tourmaster') . '</span>';
                    echo '<span data-room-tab="enquiry" >' . esc_html__('Enquiry', 'tourmaster') . '</span>';
                }else if( $room_option['form-settings'] == 'booking' ){
                    echo '<span class="tourmaster-active" data-room-tab="booking" >' . esc_html__('Book Your Room', 'tourmaster') . '</span>';
                }else if( $room_option['form-settings'] == 'enquiry' ){
                    echo '<span class="tourmaster-active" data-room-tab="enquiry" >' . esc_html__('Enquiry', 'tourmaster') . '</span>';
                }
                
                echo '</div>';
                echo '<div class="tourmaster-room-booking-bar-content" >';

                if( $room_option['form-settings'] == 'booking' || $room_option['form-settings'] == 'both' ){
                    echo '<div class="tourmaster-room-booking-wrap" class="tourmaster-active" data-room-tab="booking" >';
                    echo '<form class="tourmaster-room-booking-form clearfix" ';
                    echo ' id="tourmaster-room-booking-form" ';
			        echo ' action="' . esc_attr(add_query_arg(array('pt' => 'room'), tourmaster_get_template_url('payment'))) . '" ';
                    echo ' data-ajax-url="' . esc_url(TOURMASTER_AJAX_URL) . '" ';
                    echo ' data-action="tourmaster_room_booking_form" ';
                    echo ' method="POST" ';
                    echo ' >';
                    echo tourmaster_room_datepicker_range(array(
                        'align' => 'vertical',
                        'avail-date' => get_post_meta(get_the_ID(), 'tourmaster-room-date-display', true)
                    ));
                    echo tourmaster_room_amount_selection(array(
                        'room_amount' => array(
                            'label' => esc_html__('Room', 'tourmaster'),
                            'value' => 1
                        )
                    ), array(
                        'title' => esc_html__('Room', 'tourmaster'),
                        'hide-label' => true
                    ));

                    echo '<div class="tourmaster-room-booking-guest-selection tourmaster-one" >';
                    echo tourmaster_room_amount_selection(array(
                        'adult[]' => array(
                            'label' => esc_html__('Adults', 'tourmaster'),
                            'value' => 2
                        ),
                        'children[]' => array(
                            'label' => esc_html__('Children', 'tourmaster'),
                            'value' => 0
                        )
                    ), array(
                        'title' => '<span>' . esc_html__('Room', 'tourmaster') . '<span class="tourmaster-number" >1</span> : </span>' . esc_html__('Guests', 'tourmaster')
                    ));
                    echo '</div>';
                    echo '<input type="hidden" name="room_id" value="' . esc_attr(get_the_ID()) . '" />';
                    echo '<input type="hidden" name="post_type" value="room" />';
                    echo '<input type="submit" class="tourmaster-room-button tourmaster-full" value="' . esc_html__('Book Now', 'tourmaster') . '" />';
                    echo '</form>';
                    echo '</div>';
                }
                if( $room_option['form-settings'] == 'enquiry' || $room_option['form-settings'] == 'both' ){
                    echo '<div class="tourmaster-room-enquiry-wrap" data-room-tab="enquiry" >';
                    echo tourmaster_room_get_enquiry_form(get_the_ID());
                    echo '</div>';
                }
                
                
                echo '</div>'; // tourmaster-room-booking-bar-content
                echo '</div>'; // tourmaster-room-booking-bar-wrap
            }

            
        } // tourmaster_room_get_booking_bar
    }

    if( !function_exists('tourmaster_room_booking_is_available') ){
		function tourmaster_room_booking_is_available($data){

            // min night stay
            $min_night_stay = tourmaster_get_option('room_general', 'min-night-stay', 1);
            $min_night_stay = empty($min_night_stay)? 1: intval($min_night_stay);
            if( $min_night_stay > 1 ){
                $date_list = tourmaster_split_date($data['start_date'], $data['end_date']);

                if( $min_night_stay > sizeof($date_list) ){
                    die(json_encode(array(
                        'status' => 'failed', 
                        'message' => sprintf(esc_html__('At least %d days is required to book the room.', 'tourmaster'), $min_night_stay)
                    )));
                }
            }

            // check min max amount
            $max_guest = get_post_meta($data['room_id'], 'tourmaster-room-max-guest', true);
            $min_guest = get_post_meta($data['room_id'], 'tourmaster-room-min-guest', true);
            if( !empty($max_guest) || !empty($min_guest) ){
                for($i = 0; $i < $data['room_amount']; $i++ ){
                    if( !empty($max_guest) && $data['adult'][$i] + $data['children'][$i] > $max_guest ){
                        die(json_encode(array(
                            'status' => 'failed', 
                            'message' => sprintf(esc_html__('You can book up to %d guests per room', 'tourmaster'), $max_guest)
                        )));
                    } 
                    if( !empty($min_guest) && $data['adult'][$i] + $data['children'][$i] < $min_guest ){
                        die(json_encode(array(
                            'status' => 'failed', 
                            'message' => sprintf(esc_html__('At least %d guests is required to book the room', 'tourmaster'), $min_guest)
                        )));
                    } 
                }
            }

            
            $is_avail = true;

            // check ical date
            $ical_date_list = get_post_meta($data['room_id'], 'tourmaster_ical_sync_date_list', true);
            if( !empty($ical_date_list) ){
                $ical_date_list = explode(',', $ical_date_list);
                $date_list = tourmaster_split_date($data['start_date'], $data['end_date']);
                foreach( $date_list as $date ){
                    if( in_array($date, $ical_date_list) ){
                        $is_avail = false;
                        break;
                    } 
                }
            }

            // check available date
            if( $is_avail ){
                $room_amount = get_post_meta($data['room_id'], 'tourmaster-room-amount', true);
                $avail_dates = tourmaster_room_check_single_available($data['room_id'], $data['start_date'], $data['end_date']);
                foreach($avail_dates as $date => $occupy){
                    if( intval($occupy) + $data['room_amount'] > $room_amount ){
                        $is_avail = false;
                        break;
                    }
                }
            }
            
            if( !$is_avail ){
                die(json_encode(array(
                    'status' => 'failed', 
                    'message' => esc_html__('The room is not available on the selected date.', 'tourmaster')
                )));
            }

        }
    }
    
    add_action('wp_ajax_tourmaster_room_booking_form', 'tourmaster_room_booking_form_ajax');
    add_action('wp_ajax_nopriv_tourmaster_room_booking_form', 'tourmaster_room_booking_form_ajax');
    if( !function_exists('tourmaster_room_booking_form_ajax') ){
		function tourmaster_room_booking_form_ajax(){

            $data = empty($_POST['data'])? array(): tourmaster_process_post_data($_POST['data']);

            ob_start();
            tourmaster_room_booking_bar_summary($data);
            $content = ob_get_contents();
            ob_end_clean();

            tourmaster_room_booking_is_available($data);

            die(json_encode(array(
                'status' => 'success', 
                'content' => $content,
                'cart_row' => tourmaster_room_cart_row_item($data)
            )));
        }
    }

    if( !function_exists('tourmaster_room_booking_duration_info') ){
		function tourmaster_room_booking_duration_info($start_date, $end_date, $echo = true, $night_number = true){

            $start_date = strtotime($start_date);
            $end_date = strtotime($end_date);
            $duration = round($end_date - $start_date) / 86400;

            $ret  = tourmaster_date_format($start_date) . ' - ' . tourmaster_date_format($end_date);
            if( $night_number ){
                $ret .= ' ' . sprintf(_n('(%d Night)', '(%d Nights)', $duration, 'tourmaster'), $duration); 
            } 

            if( $echo ){
                echo $ret;
            }else{
                return $ret;
            }
            
        }
    }

    if( !function_exists('tourmaster_room_booking_bar_summary') ){
		function tourmaster_room_booking_bar_summary($data){

            $price_breakdown = tourmaster_room_price_breakdown($data, true);

            echo '<div class="tourmaster-room-booking-bar-summary" >';

            echo '<h3 class="tourmaster-room-title" >' . get_the_title($data['room_id']) . '</h3>';
            echo '<div class="tourmaster-room-booking-duration" >' . tourmaster_room_booking_duration_info($data['start_date'], $data['end_date']) . '</div>';
            
            for( $i = 0; $i < $data['room_amount']; $i++ ){
                echo '<div class="tourmaster-room-price" >';
                echo '<div class="tourmaster-head" >';
                if( $data['room_amount'] > 1 ){
                    echo sprintf(esc_html__('Room %d :', 'tourmaster'), ($i + 1)) . ' ' ;
                }

                echo sprintf(_n('%d Adult', '%d Adults', $data['adult'][$i], 'tourmaster'), $data['adult'][$i]) . ' '; 
                echo sprintf(_n('%d Children', '%d Childrens', $data['children'][$i], 'tourmaster'), $data['children'][$i]) . ' '; 

                echo '</div>';
                echo '<div class="tourmaster-tail" >' . tourmaster_money_format($price_breakdown['room-prices'][$i]) . '</div>';
                echo '</div>';
            }

            echo '<div class="tourmaster-divider" ></div>';

            echo '<div class="tourmaster-price clearfix" >';
            echo '<div class="tourmaster-head" >' . esc_html__('Sub Total', 'tourmaster') . '</div>';
            echo '<div class="tourmaster-tail" >' . tourmaster_money_format($price_breakdown['sub-total-price']) . '</div>';
            echo '</div>';

            if( !empty($price_breakdown['tax-rate']) ){
                echo '<div class="tourmaster-price clearfix" >';
                echo '<div class="tourmaster-head" >' . sprintf(esc_html__('Tax %d%%', 'tourmaster'), $price_breakdown['tax-rate']) . '</div>';
                echo '<div class="tourmaster-tail" >' . tourmaster_money_format($price_breakdown['tax-price']) . '</div>';
                echo '</div>';
            }

            echo '<div class="tourmaster-divider" ></div>';

            echo '<div class="tourmaster-price tourmaster-grand-total clearfix" >';
            echo '<div class="tourmaster-head" >' . esc_html__('Grand Total', 'tourmaster') . '</div>';
            echo '<div class="tourmaster-tail tourmaster-em" >' . tourmaster_money_format($price_breakdown['total-price']) . '</div>';
            echo '</div>';

            echo '<div class="tourmaster-room-button tourmaster-add-to-cart tourmaster-grey" ';
            echo 'data-complete="' . esc_attr(esc_html__('Your order is added to cart', 'tourmaster')) . '" ';
            echo '>' . esc_html__('Browse More Room', 'tourmaster') . '</div>';
            echo '<div class="tourmaster-or" >' . esc_html__('OR', 'tourmaster') . '</div>';
            echo '<div class="tourmaster-room-button tourmaster-checkout" >' . esc_html__('Check Out Now', 'tourmaster') . '</div>';

            echo '<div class="tourmaster-go-back" ><i class="icon-arrow-left"></i>' . esc_html__('Go Back', 'tourmaster') . '</div>';
            echo '</div>';

        }
    }

    if( !function_exists('tourmaster_split_date') ){
		function tourmaster_split_date($from, $to){
			$ret = array();
			
			$from = new DateTime($from);
			$to = new DateTime($to);
			$interval = new DateInterval('P1D');
			$periods = new DatePeriod($from, $interval, $to);
			
			foreach($periods as $period){
				$ret[] = $period->format('Y-m-d');
			}
			
			return $ret;
		} // tourmaster_split_date
	}
    if( !function_exists('tourmaster_check_package_date') ){
		function tourmaster_check_package_date($package_date, $date){
            if( sizeof($package_date) == 1 ) return 0;

            for($i = 0; $i < sizeof($package_date); $i++ ){
                if( strpos($package_date[$i], $date) !== false ){
                    return $i;
                }
            }
        } // tourmaster_check_package_date
    }

    if( !function_exists('tourmaster_room_price_breakdowns') ){
		function tourmaster_room_price_breakdowns($booking_details, $coupon_code = ''){
            $price_breakdowns = array(
                'sub-total-price' => 0,
                'services-price' => 0,
                'tax-rate' => 0,
                'tax-price' => 0,
                'total-price' => 0
            );
            for( $i = 0; $i < sizeof($booking_details); $i++ ){
                $price_breakdown = tourmaster_room_price_breakdown($booking_details[$i]);
                $price_breakdowns[] = $price_breakdown;
                
                $price_breakdowns['sub-total-price'] += $price_breakdown['sub-total-price'];
                if( !empty($price_breakdowns[$i]['room-service-prices']) ){
                    foreach( $price_breakdowns[$i]['room-service-prices'] as $room_service_price ){
                        if( !empty($room_service_price) ){
                            $price_breakdowns['services-price'] += $room_service_price;
                        }
                        
                    }
                }
            }

            if( !empty($coupon_code) ){
                $coupons = get_posts(array(
                    'post_type' => 'room_coupon', 
                    'posts_per_page' => 1, 
                    'meta_key' => 'tourmaster-coupon-code', 
                    'meta_value' => $coupon_code
                ));

                if( !empty($coupons) ){
                    $coupon_after_tax = tourmaster_get_option('room_general', 'apply-coupon-after-tax', 'disable');
                    $coupon_option = get_post_meta($coupons[0]->ID, 'tourmaster-coupon-option', true);
                    $price_breakdowns['coupon'] = array(
                        'type' => ($coupon_after_tax == 'enable')? 'after-tax': 'before-tax',
                        'coupon-code' => $coupon_code,
                        'discount-type' => $coupon_option['coupon-discount-type'],
                        'discount-amount' => $coupon_option['coupon-discount-amount'],
                    );
                }
            }
            $price_breakdowns['total-price'] = $price_breakdowns['sub-total-price'] + $price_breakdowns['services-price'];
            if( !empty($price_breakdowns['coupon']) && $price_breakdowns['coupon']['type'] == 'before-tax' ){
                if( $price_breakdowns['coupon']['discount-type'] == 'percent' ){
                    $price_breakdowns['coupon']['discount-price'] = (floatval($price_breakdowns['coupon']['discount-amount']) * $price_breakdowns['total-price']) / 100;
                }else if( $price_breakdowns['coupon']['discount-type'] == 'amount' ){
                    $price_breakdowns['coupon']['discount-price'] = floatval($price_breakdowns['coupon']['discount-amount']);
                }
                $price_breakdowns['total-price'] -= $price_breakdowns['coupon']['discount-price'];
            }

            $price_breakdowns['tax-rate'] = tourmaster_get_option('room_general', 'tax-rate', 0);
            $price_breakdowns['tax-price'] = ($price_breakdowns['total-price'] * $price_breakdowns['tax-rate']) / 100;
            $price_breakdowns['grand-total-price'] = $price_breakdowns['total-price'] + $price_breakdowns['tax-price'];
            if( !empty($price_breakdowns['coupon']) && $price_breakdowns['coupon']['type'] == 'after-tax' ){
                if( $price_breakdowns['coupon']['discount-type'] == 'percent' ){
                    $price_breakdowns['coupon']['discount-price'] = (floatval($price_breakdowns['coupon']['discount-amount']) * $price_breakdowns['grand-total-price']) / 100;
                }else if( $price_breakdowns['coupon']['discount-type'] == 'amount' ){
                    $price_breakdowns['coupon']['discount-price'] = floatval($price_breakdowns['coupon']['discount-amount']);
                }
                $price_breakdowns['grand-total-price'] -= $price_breakdowns['coupon']['discount-price'];
            }

            return $price_breakdowns;
        }
    }

    if( !function_exists('tourmaster_room_price_breakdown') ){
		function tourmaster_room_price_breakdown($data, $with_tax = false){

            $included_tax = tourmaster_get_option('room_general', 'included-tax-in-price', 'disable');

            $tax_deducted = 1;
            $tax_rate = tourmaster_get_option('room_general', 'tax-rate', 0);
            if( $included_tax == 'enable' ){
                $tax_deducted += ($tax_rate / 100);
            }

            $room_option = tourmaster_get_post_meta($data['room_id'], 'tourmaster-room-option');
            $package_date = get_post_meta($data['room_id'], 'tourmaster-room-package-date', true);
            
            $date_price = $room_option['date-price'];
            $date_list = tourmaster_split_date($data['start_date'], $data['end_date']);
            $no_nights = sizeof($date_list);

            $price_breakdown = array( 
                'sub-total-price' => 0,
                'room-dates' => array(),
                'room-prices' => array(),
                'room-service-prices' => array()
            );
            for( $i = 0; $i < $data['room_amount']; $i++ ){
                
                $room_dates = array(); 
                $adult = intval($data['adult'][$i]);
                $children = intval($data['children'][$i]);
                $price_breakdown['room-prices'][$i] = 0;

                // price for each day
                foreach( $date_list as $date ){
                    
                    $room_date = array();
                    $index = tourmaster_check_package_date($package_date, $date);

                    // check additional amount
                    $base_price_guest = empty($date_price[$index]['base-price-guests'])? 2: intval($date_price[$index]['base-price-guests']);
                    if( $adult > $base_price_guest ){
                        $room_date['additional-adult'] = $adult - $base_price_guest;
                        $base_price_guest = 0;
                    }else{
                        $base_price_guest -= $adult;
                        $room_date['additional-adult'] = 0;
                    }
                    if( $children > $base_price_guest ){
                        $room_date['additional-child'] = $children - $base_price_guest;
                    }else{
                        $room_date['additional-child'] = 0;
                    }

                    // calculate the price
                    $room_date['base-price'] = (floatval($date_price[$index]['base-price']) / $tax_deducted);
                    $room_date['additional-adult-price'] = $room_date['additional-adult'] * (floatval($date_price[$index]['additional-adult-price']) / $tax_deducted);
                    $room_date['additional-child-price'] = $room_date['additional-child'] * (floatval($date_price[$index]['additional-child-price']) / $tax_deducted);
                    $room_date['total-price'] = $room_date['base-price'] + $room_date['additional-adult-price'] + $room_date['additional-child-price'];

                    $room_dates[$date] = $room_date;
                    $price_breakdown['room-prices'][$i] += $room_date['total-price'];
                    $price_breakdown['sub-total-price'] += $room_date['total-price'];
                }

                // service price
                $price_breakdown['room-service-prices'][$i] = 0;
                if( !empty($data['services'][$i]) ){
                    foreach( $data['services'][$i] as $service_id => $amount ){
                        if( empty($amount) ) continue;
                        
                        // condition
                        $service_option = get_post_meta($service_id, 'tourmaster-service-option', true);
                        if( !empty($service_option['mandatory']) && $service_option['mandatory'] == 'enable' ){
                            if( $amount < 0 ) $amount = 1;
                        }
                        if( $service_option['per'] == 'unit' && !empty($service_option['max-unit']) && intval($service_option['max-unit']) < intval($amount) ){
                            $amount = $service_option['max-unit'];
                        }

                        // calculate the price
                        if( $service_option['per'] == 'room' ){
                            $price_breakdown['room-service-prices'][$i] += floatval($service_option['price']) * 1;
                        }else if( $service_option['per'] == 'night' ){
                            $price_breakdown['room-service-prices'][$i] += floatval($service_option['price']) * $no_nights;
                        }else if( $service_option['per'] == 'unit' ){
                            $price_breakdown['room-service-prices'][$i] += floatval($service_option['price']) * intval($amount);
                        }else if( $service_option['per'] == 'person' ){
                            $price_breakdown['room-service-prices'][$i] += floatval($service_option['price']) * ($adult + $children);
                        }

                    }
                }

                $price_breakdown['room-dates'][] = $room_dates;
            }

            // calculate tax
            if( $with_tax ){
                $price_breakdown['tax-rate'] = $tax_rate;
                $price_breakdown['tax-price'] = ($price_breakdown['sub-total-price'] * $tax_rate) / 100;
                $price_breakdown['total-price'] = $price_breakdown['sub-total-price'] + $price_breakdown['tax-price'];
            }

            return $price_breakdown;

        } // tourmaster_room_price_breakdown
    }

    add_filter('zurf_custom_main_menu_right', 'tourmaster_room_navigation_checkout');
    add_filter('traveltour_custom_main_menu_right', 'tourmaster_room_navigation_checkout');
    add_filter('hotale_custom_main_menu_right', 'tourmaster_room_navigation_checkout');
    if( !function_exists('tourmaster_room_navigation_checkout') ){
		function tourmaster_room_navigation_checkout($ret){
            $enable_checkout_button = tourmaster_get_option('room_general', 'enable-navigation-checkout-button', 'disable');
            $checkout_button_link = tourmaster_get_option('room_general', 'navigation-checkout-button-link', '');

            $button = '';
            if( $enable_checkout_button == 'enable' ){
                $cart_cookie = empty($_COOKIE['tourmaster-room-cart'])? array(): json_decode(wp_unslash($_COOKIE['tourmaster-room-cart']), true);
                $cart_cookie = stripslashes_deep($cart_cookie);

                $button .= '<div class="tourmaster-room-navigation-checkout-wrap ' . (sizeof($cart_cookie) > 0? 'tourmaster-active': '') . '" >';
                $button .= '<a id="tourmaster-room-navigation-checkout-button" ';
                $button .= 'class="tourmaster-room-navigation-checkout-button" ';
                $button .= 'href="' . esc_url($checkout_button_link) . '" ';
                $button .= 'data-checkout-label="' . esc_attr(esc_html__('Check Out', 'tourmaster')) . '" ';
                $button .= 'data-label="' . esc_attr(esc_html__('Book Now', 'tourmaster')) . '" >';
                $button .= (sizeof($cart_cookie) > 0)? esc_html__('Check Out', 'tourmaster'): esc_html__('Book Now', 'tourmaster');
                $button .= '<span class="tourmaster-count" >' . sizeof($cart_cookie) . '</span></a>';
                $button .= '<div class="tourmaster-room-cart-item-wrap" >';
                $button .= '<div class="tourmaster-room-cart-items" >';
                $button .= '<ul>';
                foreach( $cart_cookie as $cart_item ){
                    $button .= tourmaster_room_cart_row_item($cart_item);
                }
                $button .= '</ul>';
                $button .= '<a class="tourmaster-checkout-button" href="' . esc_attr(add_query_arg(array('pt' => 'room', 'type' => 'cart'), tourmaster_get_template_url('payment'))) . '" >Check Out</a>';
                $button .= '</div>'; // tourmaster-room-cart-items
                $button .= '</div>'; // tourmaster-room-cart-item-wrap
                $button .= '</div>';
            }

            return $ret . $button;
        }
    }

    if( !function_exists('tourmaster_room_cart_row_item') ){
		function tourmaster_room_cart_row_item($cart_item){
            $button  = '<li>';
            $button .= get_the_title($cart_item['room_id']);
            $button .= '<span class="tourmaster-amount" >x' . $cart_item['room_amount'] . '</span>';
            $button .= '<i class="fa5r fa5-trash-alt tourmaster-remove" ></i>'; 
            $button .= '</li>';
            return $button;
        }
    }

    /*
    add_action('init', 'test2');
    function test2(){
        // tourmaster_room_check_occupied(9950);
        // tourmaster_room_check_single_available(9950, '2022-03-24', '2022-03-28');
        // tourmaster_room_check_available('2022-03-24', '2022-03-28');
    }
    */
    
    if( !function_exists('tourmaster_room_check_occupied') ){
        function tourmaster_room_check_occupied($room_id){
            
            global $wpdb;

            $date_avails = get_post_meta($room_id, 'tourmaster-room-date-avail', true);
            if( empty($date_avails) ) return;

            $count = 0;
            $date_list = explode(',', $date_avails);
            $sql  = "SELECT ";
            foreach( $date_list as $date ){ $count ++;
                $sql .= ($count > 1)? ', ': '';
                $sql .= "COUNT(CASE WHEN ";
                $sql .= $wpdb->prepare("start_date <= CONVERT(%s, DATETIME) ", $date);
                $sql .= $wpdb->prepare("AND end_date > CONVERT(%s, DATETIME) ", $date);
                $sql .= "THEN 1 END) AS d{$count} ";
            } 
            $sql .= "FROM {$wpdb->prefix}tourmaster_room_booking ";
            $sql .= $wpdb->prepare("WHERE room_id = %d", $room_id);
            $result = $wpdb->get_row($sql);

            $count = 0;
            $room_amount = get_post_meta($room_id, 'tourmaster-room-amount', true);
            $room_amount = empty($room_amount)? 1: intval($room_amount);
            $date_occupied = array();
            foreach( $date_list as $date ){ $count ++;
                $slug = 'd' . $count;
                if( $room_amount <= $result->$slug ){
                    $date_occupied[] = $date;
                }
            }
            if( !empty($date_occupied) ){
                $date_display = array_diff($date_list, $date_occupied);
                update_post_meta($room_id, 'tourmaster-room-date-occupied', implode(',', $date_occupied));
            }else{
                $date_display = $date_list;
                delete_post_meta($room_id, 'tourmaster-room-date-occupied');
            }   

            tourmaster_room_calculate_date_display($room_id, array(
                'date-avail' => $date_list,
                'date-occupied' => $date_occupied
            ));

        }
    }

    if( !function_exists('tourmaster_room_check_single_available') ){
        function tourmaster_room_check_single_available($room_id, $start_date, $end_date){

            global $wpdb;

            $count = 0;
            $date_list = tourmaster_split_date($start_date, $end_date);

            $sql  = "SELECT ";
            foreach( $date_list as $date ){ $count ++;
                $sql .= ($count > 1)? ', ': '';
                $sql .= "COUNT(CASE WHEN ";
                $sql .= $wpdb->prepare("start_date <= CONVERT(%s, DATETIME) ", $date);
                $sql .= $wpdb->prepare("AND end_date > CONVERT(%s, DATETIME) ", $date);
                $sql .= "THEN 1 END) AS d{$count} ";
            } 
            $sql .= "FROM {$wpdb->prefix}tourmaster_room_booking ";
            $sql .= $wpdb->prepare("WHERE room_id = %d", $room_id);
            $result = $wpdb->get_row($sql);

            $count = 0;
            $ret = array();
            foreach( $date_list as $date ){ $count ++;
                $slug = 'd' . $count;
                $ret[$date] = $result->$slug;
            }

            return $ret;
        }
    }
    if( !function_exists('tourmaster_room_check_available') ){
        function tourmaster_room_check_available($start_date, $end_date){

            global $wpdb;

            $count = 0;
            $date_list = tourmaster_split_date($start_date, $end_date);

            $sql  = "SELECT room_id, ";
            foreach( $date_list as $date ){ $count ++;
                $sql .= ($count > 1)? ', ': '';
                $sql .= "COUNT(CASE WHEN ";
                $sql .= $wpdb->prepare("start_date <= CONVERT(%s, DATETIME) ", $date);
                $sql .= $wpdb->prepare("AND end_date > CONVERT(%s, DATETIME) ", $date);
                $sql .= "THEN 1 END) AS d{$count} ";
            } 
            $sql .= "FROM {$wpdb->prefix}tourmaster_room_booking GROUP BY room_id";

            $results = $wpdb->get_results($sql);
        }
    }