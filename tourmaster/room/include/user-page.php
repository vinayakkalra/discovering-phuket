<?php

    add_filter('tourmaster_user_nav_list', 'tourmaster_room_user_nav_list');
    if( !function_exists('tourmaster_room_user_nav_list') ){
		function tourmaster_room_user_nav_list($nav_list){

            $nav_list = $nav_list + array(
				'room-booking-title' => array(
					'type' => 'title',
					'title' => esc_html__('Room Booking', 'tourmaster')
				),
				'room-booking' => array(
					'title' => esc_html__('My Bookings', 'tourmaster'),
					'icon' => 'icon_document_alt'
				),
				'room-invoices' => array(
					'title' => esc_html__('Invoices', 'tourmaster'),
					'icon' => 'icon_wallet'
				),
				'room-reviews' => array(
					'title' => esc_html__('Reviews', 'tourmaster'),
					'icon' => 'fa fa-star'
				),
				// 'room-wish-list' => array(
				// 	'title' => esc_html__('Wish List', 'tourmaster'),
				// 	'icon' => 'fa fa-heart-o',
				// 	'top-bar' => true,
				// )
            );

			$cart_page = tourmaster_get_option('room_general', 'top-bar-cart-page', '');

			if( !empty($cart_page) ){
				$nav_list['cart-page'] = array(
					'title' => esc_html__('Room Cart', 'tourmaster'),
					'icon' => 'fa fa-cart',
					'top-bar' => true,
					'hide-main-nav' => true,
					'link' => get_permalink($cart_page),
				);
			}

            return $nav_list;
        } // tourmaster_tour_user_nav_list
    }
	
	add_filter('tourmaster_user_content_template', 'tourmaster_room_user_content_template', 10, 2);
	if( !function_exists('tourmaster_room_user_content_template') ){
		function tourmaster_room_user_content_template($template, $page_type){
			
			if( in_array($page_type, array('room-invoices', 'room-invoices-paid', 'room-invoices-single', 'room-booking', 'room-booking-single', 'room-reviews', 'room-wish-list')) ){
				$template = TOURMASTER_LOCAL . '/room/single/user/' . $page_type . '.php';
			}
			
			return $template;

		} // tourmaster_tour_dashboard_block
	}

	add_action('tourmaster_dashboard_block', 'tourmaster_room_dashboard_block');
	if( !function_exists('tourmaster_room_dashboard_block') ){
		function tourmaster_room_dashboard_block(){
			
			/* dashboard page content */

			///////////////////////
			// my booking section
			///////////////////////
			
			// filter
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

			// query 
			global $wpdb, $current_user;
			$sql  = "SELECT * FROM {$wpdb->prefix}tourmaster_room_order ";
			$sql .= $wpdb->prepare("WHERE user_id = %d ", $current_user->data->ID);
			$sql .= "AND order_status != 'cancel' ";
			$sql .= 'ORDER BY id DESC LIMIT 5';
			$results = $wpdb->get_results($sql);

			if( !empty($results) ){	

				tourmaster_user_content_block_start(array(
					'title' => esc_html__('Current Room Booking', 'tourmaster'),
					'title-link-text' => esc_html__('View All Bookings', 'tourmaster'),
					'title-link' => tourmaster_get_template_url('user', array('page_type'=>'room-booking'))
				));

				echo '<table class="tourmaster-my-booking-table tourmaster-table tourmaster-room-table" >';
				tourmaster_get_table_head(array(
					esc_html__('Order ID', 'tourmaster'),
					esc_html__('Travel Date', 'tourmaster'),
					esc_html__('Total', 'tourmaster'),
					esc_html__('Payment Status', 'tourmaster'),
				));
				foreach( $results as $result ){

					tourmaster_set_currency($result->currency);

					$single_booking_url = add_query_arg(array(
						'page_type' => 'room-booking',
						'sub_page' => 'single',
						'id' => $result->id
					));
					$title = '<a class="tourmaster-my-booking-title" href="' . esc_url($single_booking_url) . '" >' . $result->id . '</a>';

					$booking_data = json_decode($result->booking_data, true);
					$travel_date  = '<a href="' . esc_url($single_booking_url) . '" >';
					$travel_date .= sprintf(esc_html__('%s to %s', 'tourmaster'), tourmaster_date_format($booking_data[0]['start_date']), tourmaster_date_format($booking_data[0]['end_date']));
					$travel_date .= '</a>';
					
					$status  = '<span class="tourmaster-my-booking-status tourmaster-booking-status tourmaster-status-' . esc_attr($result->order_status) . '" >';
					if( $result->order_status == 'approved' ){
						$status .= '<i class="fa fa-check" ></i>';
					}else if( $result->order_status == 'departed' ){
						$status .= '<i class="fa fa-check-circle-o" ></i>';
					}else if( $result->order_status == 'rejected' ){
						$status .= '<i class="fa fa-remove" ></i>';
					}		
					$status .= $statuses[$result->order_status];
					$status .= '</span>';
					if( in_array($result->order_status, array('pending', 'receipt-submitted', 'rejected', 'deposit-paid')) ){
						$status .= '<a class="tourmaster-my-booking-action fa fa-dollar" title="' . esc_html__('Make Payment', 'tourmaster') . '" href="' . esc_url($single_booking_url) . '" ></a>';
					}
					if( in_array($result->order_status, array('pending', 'receipt-submitted', 'rejected')) ){
						$status .= '<a class="tourmaster-my-booking-action fa fa-remove" title="' . esc_html__('Cancel', 'tourmaster') . '" href="' . add_query_arg(array('action'=>'remove', 'id'=>$result->id)) . '" ';
						$status .= ' data-confirm="' . esc_html__('Just To Confirm', 'tourmaster') . '" ';
						$status .= ' data-confirm-yes="' . esc_html__('Yes', 'tourmaster') . '" ';
						$status .= ' data-confirm-no="' . esc_html__('No', 'tourmaster') . '" ';
						$status .= ' data-confirm-text="' . esc_html__('Are you sure you want to do this ?', 'tourmaster') . '" ';
						$status .= ' data-confirm-sub="' . esc_html__('The transaction you selected will be permanently removed from the system.', 'tourmaster') . '" ';
						$status .= ' ></a>';
					}

					tourmaster_get_table_content(array(
						$title,
						$travel_date, 
						'<span class="tourmaster-my-booking-price" >' . tourmaster_money_format($result->total_price) . '</span>',
						$status
					));
				}

				tourmaster_reset_currency();

				echo '</table>';

				tourmaster_user_content_block_end();
			}

			///////////////////////
			// my review section
			///////////////////////

			$sql  = "SELECT * FROM {$wpdb->prefix}tourmaster_room_order AS t1 ";
			$sql .= "LEFT JOIN {$wpdb->prefix}tourmaster_room_review AS t2 ";
			$sql .= "ON t1.id = t2.order_id ";
			$sql .= $wpdb->prepare("WHERE user_id = %d ", $current_user->data->ID);
			$sql .= "AND order_status IN ('online-paid', 'approved', 'departed') ";
			$sql .= "GROUP BY id ";
			$sql .= "ORDER BY id DESC LIMIT 5";
			$results = $wpdb->get_results($sql);

			tourmaster_user_content_block_start(array(
				'title' => esc_html__('Room Reviews', 'tourmaster'),
				'title-link-text' => esc_html__('View All Reviews', 'tourmaster'),
				'title-link' => tourmaster_get_template_url('user', array('page_type'=>'room-reviews'))
			));

			if( !empty($results) ){

				echo '<table class="tourmaster-user-review-table tourmaster-table tourmaster-room-table" >';
				tourmaster_get_table_head(array(
					esc_html__('Order ID', 'tourmaster'),
					esc_html__('Travel Date', 'tourmaster'),
					esc_html__('Status', 'tourmaster'),
					esc_html__('Action', 'tourmaster'),
				));		
				foreach( $results as $result ){
					$title = $result->id;
		
					$booking_data = json_decode($result->booking_data, true);
					$travel_date  = '';
					$travel_date .= sprintf(esc_html__('%s to %s', 'tourmaster'), tourmaster_date_format($booking_data[0]['start_date']), tourmaster_date_format($booking_data[0]['end_date']));
		
					if( $result->review_score == '' ){
						$status  = '<span class="tourmaster-user-review-status tourmaster-status-pending" >';	
						$status .= esc_html__('Pending', 'tourmaster');
						$status .= '</span>';
		
						$action  = '<span class="tourmaster-user-review-action" data-tmlb="submit-review" >' . esc_html__('Submit Review', 'tourmaster') . '</span>';
						$action .= tourmaster_lightbox_content(array(
							'id' => 'submit-review',
							'title' => esc_html__('Submit Your Review', 'tourmaster'),
							'content' => tourmaster_room_get_review_form( $result )
						));
					}else{
						$status  = '<span class="tourmaster-user-review-status tourmaster-status-submitted" >';	
						$status .= esc_html__('Submitted', 'tourmaster');
						$status .= '</span>';
		
						$action  = '<span class="tourmaster-user-review-action" data-tmlb="view-review" >' . esc_html__('View Review', 'tourmaster') . '</span>';		
						$action .= tourmaster_lightbox_content(array(
							'id' => 'view-review',
							'title' => esc_html__('Your Review', 'tourmaster'),
							'content' => tourmaster_room_get_submitted_review($result)
						));
					}
		
					tourmaster_get_table_content(array($title, $travel_date, $status, $action));
				}
				echo '</table>';
		
			}
			
			tourmaster_user_content_block_end();

		} // tourmaster_tour_dashboard_block
	}

	// display deposit content
	if( !function_exists('tourmaster_room_deposit_item_content') ){
		function tourmaster_room_deposit_item_content( $result, $payment_info ){
			
			// file
			if( !empty($payment_info['file_url']) ){
				echo '<div class="tourmaster-my-booking-single-payment-receipt" >';
				if( strpos($payment_info['file_url'], '.pdf') ){
					echo '<a href="' . esc_url($payment_info['file_url']) . '" target="_blank" >';
					echo '<i class="fa fa-file" style="margin-right: 10px;" ></i>' . esc_html__('Download', 'tourmaster');
					echo '</a>';
				}else{
					echo '<a href="' . esc_url($payment_info['file_url']) . '" >';
					echo '<img src="' . esc_url($payment_info['file_url']) . '" alt="receipt" />';
					echo '</a>';
				}
				echo '</div>';			
			}

			// date
			if( !empty($payment_info['submission_date']) ){
				echo '<div class="tourmaster-my-booking-single-field clearfix" >';
				echo '<span class="tourmaster-head">' . esc_html__('Submission Date', 'tourmaster') . ' :</span> ';
				echo '<span class="tourmaster-tail">' . tourmaster_date_format($payment_info['submission_date']) . ' ' . tourmaster_time_format($payment_info['submission_date']) . '</span>';
				echo '</div>';			
			}
			
			// payment method
			if( !empty($payment_info['payment_method']) ){
				echo '<div class="tourmaster-my-booking-single-field clearfix" >';
				echo '<span class="tourmaster-head">' . esc_html__('Payment Method', 'tourmaster') . ' :</span> ';
				echo '<span class="tourmaster-tail">';
				if( $payment_info['payment_method'] == 'receipt' ){
					echo esc_html__('Receipt Submission', 'tourmaster');
				}else if( in_array($payment_info['payment_method'], array('stripe', 'authorize','paymill')) ){
					echo esc_html__('Credit Card', 'tourmaster');
				}else{
					echo $payment_info['payment_method'];
				}
				echo '</span>';
				echo '</div>';			
			}
			
			// transaction id
			if( !empty($payment_info['transaction_id']) ){
				echo '<div class="tourmaster-my-booking-single-field clearfix" >';
				echo '<span class="tourmaster-head">' . esc_html__('Transaction ID', 'tourmaster') . ' :</span> ';
				echo '<span class="tourmaster-tail">' . $payment_info['transaction_id'] . '</span>';
				echo '</div>';			
			}

			// deposit price
			if( !empty($payment_info['deposit_rate']) && !empty($payment_info['deposit_price']) ){
				echo '<div class="tourmaster-my-booking-single-field clearfix" >';
				echo '<span class="tourmaster-head">' . esc_html__('Deposit Rate', 'tourmaster') . ' :</span> ';
				echo '<span class="tourmaster-tail">' . $payment_info['deposit_rate'] . '%</span>';
				echo '</div>';			

				echo '<div class="tourmaster-my-booking-single-field clearfix" >';
				echo '<span class="tourmaster-head">' . esc_html__('Deposit Price', 'tourmaster') . ' :</span> ';
				echo '<span class="tourmaster-tail">' . tourmaster_money_format($payment_info['deposit_price']) . '</span>';
				echo '</div>';			
			}

			if( !empty($payment_info['amount']) ){
				echo '<div class="tourmaster-my-booking-single-field clearfix" >';
				echo '<span class="tourmaster-head">' . esc_html__('Amount', 'tourmaster') . ' :</span> ';
				echo '<span class="tourmaster-tail">' . tourmaster_money_format($payment_info['amount']) . '</span>';
				echo '</div>';
			}
			if( !empty($payment_info['service_fee_rate']) && !empty($payment_info['service_fee']) ){
				echo '<div class="tourmaster-my-booking-single-field clearfix" >';
				echo '<span class="tourmaster-head">' . sprintf(esc_html__('Service Fee (%s%%)', 'tourmaster'), $payment_info['service_fee_rate']) . ' :</span> ';
				echo '<span class="tourmaster-tail">' . tourmaster_money_format($payment_info['service_fee']) . '</span>';
				echo '</div>';

				if( !empty($payment_info['paid_amount']) ){
					echo '<div class="tourmaster-my-booking-single-field clearfix" >';
					echo '<span class="tourmaster-head">' . esc_html__('Paid Amount', 'tourmaster') . ' :</span> ';
					echo '<span class="tourmaster-tail">' . tourmaster_money_format($payment_info['paid_amount']) . '</span>';
					echo '</div>';
				}
			}

			// payment status
			if( !empty($payment_info['payment_status']) ){
				echo '<div class="tourmaster-my-booking-single-field clearfix" >';
				echo '<span class="tourmaster-head">' . esc_html__('Payment Status', 'tourmaster') . ' :</span> ';
				echo '<span class="tourmaster-tail">';
				if( $payment_info['payment_status'] == 'paid' ){
					esc_html_e('Paid', 'tourmaster');
				}else if( $payment_info['payment_status'] == 'pending' ){
					esc_html_e('Pending', 'tourmaster');
				}else{
					echo $payment_info['payment_status'];
				}
				echo '</span>';
				echo '</div>';
			}

		}
	}


	if( !function_exists('tourmaster_get_room_invoice_price') ){
		function tourmaster_get_room_invoice_price( $booking_details, $price_breakdowns ){

			$ret  = '<div class="tourmaster-invoice-price clearfix" >';

			for( $i = 0; $i < sizeof($booking_details); $i++ ){
                $booking_detail = $booking_details[$i];
                $price_breakdown = $price_breakdowns[$i];

                for( $j = 0; $j < $booking_detail['room_amount']; $j++ ){
					$ret .= '<div class="tourmaster-invoice-price-item clearfix" >';
					$ret .= '<span class="tourmaster-head" >';
					$ret .= '<span class="tourmaster-head-title" >';
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
					$ret .= '<span class="tourmaster-head-caption" >';
					$ret .= tourmaster_room_booking_duration_info($booking_detail['start_date'], $booking_detail['end_date'], false, false);
					$ret .= '</span>';
					$ret .= '</span>';
					$ret .= '<span class="tourmaster-tail tourmaster-right" >';
					$ret .= tourmaster_money_format($price_breakdown['room-prices'][$j]);
					$ret .= '</span>';
					
					if( !empty($price_breakdown['room-service-prices'][$j]) ){
						$ret .= '<div class="tourmaster-separator" ></div>';	
						$ret .= '<span class="tourmaster-head" >';
						$ret .= '<span class="tourmaster-head-title" >' . esc_html__('Additional Services', 'tourmaster') . '</span>';
						$ret .= '</span>';
						$ret .= '<span class="tourmaster-tail tourmaster-right" >' . tourmaster_money_format($price_breakdown['room-service-prices'][$j]) . '</span>';
					}
					$ret .= '</div>';
				}
			}

			// coupon
			if( !empty($price_breakdowns['coupon']) && $price_breakdowns['coupon']['type'] == 'before-tax' ){
				$ret .= '<div class="tourmaster-invoice-price-sub-total clearfix" >';
				$ret .= '<span class="tourmaster-head" >' . esc_html__('Coupon Discount', 'tourmaster') . '</span>';
				$ret .= '<span class="tourmaster-tail tourmaster-right" >';
				$ret .= '- ' . tourmaster_money_format($price_breakdowns['coupon']['discount-price']);
				$ret .= '</span>';
				$ret .= '</div>';
			}

			// total
			$ret .= '<div class="tourmaster-invoice-price-sub-total clearfix" >';
			$ret .= '<span class="tourmaster-head" >' . esc_html__('Total Price', 'tourmaster') . '</span>';
			$ret .= '<span class="tourmaster-tail tourmaster-right" >';
			$ret .= tourmaster_money_format($price_breakdowns['total-price']);
			$ret .= '</span>';
			$ret .= '</div>';

			if( !empty($price_breakdowns['tax-price']) ){
				$ret .= '<div class="tourmaster-invoice-price-tax clearfix" >';
				$ret .= '<span class="tourmaster-head" >' . esc_html__('Tax', 'tourmaster') . '</span>';
				$ret .= '<span class="tourmaster-tail tourmaster-right" >';
				$ret .= tourmaster_money_format($price_breakdowns['tax-price']);
				$ret .= '</span>';
				$ret .= '</div>';
			}

			// coupon
			if( !empty($price_breakdowns['coupon']) && $price_breakdowns['coupon']['type'] == 'after-tax' ){
				$ret .= '<div class="tourmaster-invoice-price-sub-total clearfix" >';
				$ret .= '<span class="tourmaster-head" >' . esc_html__('Coupon Discount', 'tourmaster') . '</span>';
				$ret .= '<span class="tourmaster-tail tourmaster-right" >';
				$ret .= '- ' . tourmaster_money_format($price_breakdowns['coupon']['discount-price']);
				$ret .= '</span>';
				$ret .= '</div>';
			}

			$ret .= '</div>'; // tourmaster-invoice-price

			$ret .= '<div class="tourmaster-invoice-total-price clearfix" >';
            $ret .= '<span class="tourmaster-head">' . esc_html__('Grand Total Pirce', 'tourmaster') . '</span> ';
            $ret .= '<span class="tourmaster-tail">' . tourmaster_money_format($price_breakdowns['grand-total-price']) . '</span>';
            $ret .= '</div>'; // tourmaster-invoice-total-price

			return $ret;
		} // tourmaster_get_tour_invoice_price
	}