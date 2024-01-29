<?php 

$skip_page = false;
if( !empty($_GET['step']) && $_GET['step'] == 4 ){
	if( !empty($_GET['payment_method']) && $_GET['payment_method'] == 'paypal' ){
		$skip_page = true;
		include 'payment-paypal-complete.php';
	}
}else if( !empty($_GET['tid']) || !empty($_COOKIE['tourmaster-room-current-id']) ){
	$skip_page = true;
	include 'payment-online.php';
}
	
if( !$skip_page ){
	
	header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

	/**
	 * The template for displaying single room payment
	 */

	$type = 'booking';
	$booking_details = array();
	if( true || (!empty($_GET['type']) && $_GET['type'] == 'cart') ){
		$type = 'cart';
		$booking_detail = json_decode(wp_unslash($_COOKIE['tourmaster-room-cart']), true);
		$booking_details = stripslashes_deep($booking_detail); 
	}else if( !empty($_COOKIE['tourmaster-room-booking-detail']) ){
		$booking_detail = json_decode(wp_unslash($_COOKIE['tourmaster-room-booking-detail']), true);
		$booking_details = array(stripslashes_deep($booking_detail)); 
	}

	// check if booking is still available
	$unavail_error = '';

	$room_date_avails = array();
	if( !empty($booking_details) ){
		foreach($booking_details as $key => $booking_detail ){
			$is_avail = true;

			// check ical date
            $ical_date_list = get_post_meta($booking_detail['room_id'], 'tourmaster_ical_sync_date_list', true);
            if( !empty($ical_date_list) ){
                $ical_date_list = explode(',', $ical_date_list);
                $date_list = tourmaster_split_date($booking_detail['start_date'], $booking_detail['end_date']);
                foreach( $date_list as $date ){
                    if( in_array($date, $ical_date_list) ){
                        $is_avail = false;
                        break;
                    } 
                }
            }

			$room_amount = get_post_meta($booking_detail['room_id'], 'tourmaster-room-amount', true);
			$avail_dates = tourmaster_room_check_single_available($booking_detail['room_id'], $booking_detail['start_date'], $booking_detail['end_date']);
			$avail_dates = array_intersect_key($room_date_avails, $avail_dates) + $avail_dates;
			
			// check if available
			foreach($avail_dates as $date => $occupy){
				if( intval($occupy) + $booking_detail['room_amount'] > $room_amount ){
					$is_avail = false;
				}
				$avail_dates[$date] = intval($avail_dates[$date]) + intval($booking_detail['room_amount']);
			}

			// prevent multiple room booking within same date
			if( $is_avail ){
				if( empty($room_date_avails[$booking_detail['room_id']]) ){
					$room_date_avails[$booking_detail['room_id']] = $avail_dates;
				}else{
					$room_date_avails[$booking_detail['room_id']] += $avail_dates;
				}
			}else{
				if( empty($unavail_error) ){
					if( sizeof($booking_details) > 1 ){
						$unavail_error = esc_html__('Some booking is not available and will be filtered out.', 'tourmaster');
					}else{
						$unavail_error = esc_html__('The room and date you selected is not available anymore.', 'tourmaster');
					}
				}
				unset($booking_details[$key]);
			}
		}
	}
	if( !empty($unavail_error) ){
		$booking_details = array_values($booking_details);
	}

	// initiate service array
	if( !empty($booking_details) ){
		for( $i = 0; $i < sizeof($booking_details); $i++ ){
			if( empty($booking_details[$i]['services']) ){
				$services = array();
				$room_option = tourmaster_get_post_meta($booking_details[$i]['room_id'], 'tourmaster-room-option');

				if( !empty($room_option['room-service']) ){
					foreach( $room_option['room-service'] as $service_id ){
						$service_option = get_post_meta($service_id, 'tourmaster-service-option', true);
						if( !empty($service_option['mandatory']) && $service_option['mandatory'] == 'enable' ){
							$services[$service_id] = 1;
						}else{
							$services[$service_id] = 0;
						}
					}
				}

				$booking_details[$i]['services'] = array();
				for( $j = 0; $j < $booking_details[$i]['room_amount']; $j++ ){
					$booking_details[$i]['services'][$j] = $services;
				}
			}
		}

		// get price data
		$price_breakdowns = tourmaster_room_price_breakdowns($booking_details);
	}

	get_header();


	echo '<div class="tourmaster-page-wrapper" id="tourmaster-room-payment-page" ';
	echo 'data-ajax-url="' . esc_attr(TOURMASTER_AJAX_URL) . '" ';
	echo 'data-type="' . esc_attr($type) . '" ';
	echo 'data-booking-details=' . esc_attr(json_encode($booking_details)) . ' >';
	echo '<div class="tourmaster-container clearfix" >';
	
	if( !empty($unavail_error) ){
		echo '<div class="tourmaster-room-payment-error tourmaster-item-mglr" >' . $unavail_error . '</div>';
	}

	if( !empty($booking_details) ){
		tourmaster_room_payment_step();

		echo '<div class="tourmaster-payment-step tourmaster-step2 clearfix" id="tourmaster-step2-wrap" >';
		echo '<div class="tourmaster-column-40" >';
		tourmaster_room_price_summary($booking_details, $price_breakdowns);
		echo '</div>'; // tourmaster-column-40
		echo '<div class="tourmaster-column-20" >';
		echo '<div class="tourmaster-room-price-sidebar-wrap tourmaster-item-pdlr" >';
		tourmaster_room_price_sidebar($price_breakdowns);
		echo '</div>'; // tourmaster-room-price-sidebar-wrap
		echo '</div>'; // tourmaster-column-20
		echo '</div>'; // tourmaster-step2

		echo '<div class="tourmaster-payment-step tourmaster-step3 clearfix" id="tourmaster-step3-wrap" ';
		echo ' data-required-error="' . esc_attr(esc_html__('Please fill all required fields', 'tourmaster')) . '" ';
		echo ' >';
		echo '<div class="tourmaster-column-40" >';
		echo '<div class="tourmaster-room-contact-detail-wrap" >';
		echo tourmaster_room_contact_detail($booking_details);
		echo '</div>';
		echo '</div>'; // tourmaster-column-40
		echo '<div class="tourmaster-column-20" >';
		echo '<div class="tourmaster-room-sidebar-summary-wrap tourmaster-item-pdlr" >';
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

	get_footer(); 

	do_action('include_goodlayers_payment_script');
}

?>