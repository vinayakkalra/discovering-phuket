<?php
	/*	
	*	Ordering Page
	*/

	if( !function_exists('tourmaster_order_edit_text') ){
		function tourmaster_order_edit_text($tmlb = ''){
			return '<a class="tourmaster-order-edit-text" href="#" data-tmlb="' . esc_attr($tmlb) . '" >' . esc_html__('Edit', 'tourmaster') . '<i class="fa fa-edit" ></i></a>';
		}
	}
	
	add_action('wp_ajax_tourmaster_room_admin_remove_order', 'tourmaster_room_admin_remove_order');
	if( !function_exists('tourmaster_room_admin_remove_order') ){
		function tourmaster_room_admin_remove_order(){
			
			$data = tourmaster_process_post_data($_POST['data']);

			if( !isset($_POST['index']) || empty($data['tid']) || empty($data['booking_details']) ){
				die(json_encode(array('status' => 'failed', 'debug' => $_POST)));
			} 

			global $wpdb;
			$order_id = $data['tid'];
			$booking_details = empty($data['booking_details'])? array(): json_decode($data['booking_details'], true);
			$d_booking_detail = $booking_details[$_POST['index']];

			// delete room booking table
			$dsql  = "DELETE FROM {$wpdb->prefix}tourmaster_room_booking ";
			$dsql .= $wpdb->prepare('WHERE order_id = %d ', $order_id);
			$dsql .= $wpdb->prepare('AND room_id = %d ', $d_booking_detail['room_id']);
			$dsql .= $wpdb->prepare('AND start_date = %s ', $d_booking_detail['start_date']);
			$dsql .= $wpdb->prepare('AND end_date = %s ', $d_booking_detail['end_date']);
			$dsql .= $wpdb->prepare('LIMIT %d ', $d_booking_detail['room_amount']);
			$wpdb->query($dsql);

			// update booking detail
			unset($booking_details[$_POST['index']]);
			$booking_details = array_values($booking_details);

			$sql  = "SELECT * FROM {$wpdb->prefix}tourmaster_room_order ";
			$sql .= $wpdb->prepare('WHERE id = %d', $order_id);
			$result = $wpdb->get_row($sql);

			$price_breakdowns = tourmaster_room_price_breakdowns($booking_details, $result->coupon_code);
			$wpdb->update("{$wpdb->prefix}tourmaster_room_order", array(
				'booking_data' => json_encode($booking_details),
				'price_breakdown' => json_encode($price_breakdowns),
				'total_price' => $price_breakdowns['grand-total-price'],
			), array(
				'id' => $order_id
			), array(), array('%d'));

			die(json_encode(array(
				'dsql' => $dsql,
				'status' => 'success',
				'booking_details' => $booking_details,
				'booking_details_data' => tourmaster_room_admin_add_order_details($booking_details)
			)));
		}
	}
	
	add_action('wp_ajax_tourmaster_room_admin_add_order', 'tourmaster_room_admin_add_order');
	if( !function_exists('tourmaster_room_admin_add_order') ){
		function tourmaster_room_admin_add_order(){
		
			$ret = array();

			if( !empty($_POST['data']) ){
				$data = tourmaster_process_post_data($_POST['data']);

				if( empty($data['room_id']) ){
					die(json_encode(array(
						'status' => 'failed',
						'message' => esc_html__('Please select the room you want to book.', 'tourmaster')
					)));
				}

				if( empty($data['start_date']) || empty($data['end_date']) || $data['end_date'] <= $data['start_date'] ){
					die(json_encode(array(
						'status' => 'failed',
						'message' => esc_html__('Please select start date and end date.', 'tourmaster')
					)));
				}

				// check if room is available
				tourmaster_room_booking_is_available($data);

				$booking_details = empty($data['booking_details'])? array(): json_decode($data['booking_details'], true);
				
				// create new booking
				$booking_detail = array(
					'post_type' => 'room',
					'room_id' => $data['room_id'],
					'start_date' => $data['start_date'],
					'end_date' => $data['end_date'],
					'room_amount' => $data['room_amount'],
					'adult' => $data['adult'],
					'children' => $data['children'],
				);

				// apply service array
				$services = array();
				$room_option = tourmaster_get_post_meta($booking_detail['room_id'], 'tourmaster-room-option');
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
				$booking_detail['services'] = array();
				for( $j = 0; $j < $booking_detail['room_amount']; $j++ ){
					$booking_detail['services'][$j] = $services;
				}

				// add new order to database
				global $wpdb;
				$booking_details[] = $booking_detail;

				if( empty($data['tid']) ){
					$price_breakdowns = tourmaster_room_price_breakdowns($booking_details, '');
					$user_id = get_current_user_id();

					$data = array(
						'user_id' => $user_id,
						'booking_date' => current_time('mysql'),
						'booking_data' => json_encode($booking_details),
						'contact_info' => '',
						'coupon_code' => '',
						'order_status' => 'pending',
						'price_breakdown' => json_encode($price_breakdowns),
						'total_price' => $price_breakdowns['grand-total-price'],
					);
					$format = array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%f');
					$wpdb->insert("{$wpdb->prefix}tourmaster_room_order", $data, $format);
					$order_id = $wpdb->insert_id;
				}else{
					$order_id = $data['tid'];

					$sql  = "SELECT * FROM {$wpdb->prefix}tourmaster_room_order ";
					$sql .= $wpdb->prepare('WHERE id = %d', $order_id);
					$result = $wpdb->get_row($sql);

					$price_breakdowns = tourmaster_room_price_breakdowns($booking_details, $result->coupon_code);
					$wpdb->update("{$wpdb->prefix}tourmaster_room_order", array(
						'booking_data' => json_encode($booking_details),
						'price_breakdown' => json_encode($price_breakdowns),
						'total_price' => $price_breakdowns['grand-total-price'],
					), array(
						'id' => $order_id
					), array(), array('%d'));
					
				}

				$data = array(
					'order_id' => $order_id,
					'room_id' => $booking_detail['room_id'],
					'start_date' => $booking_detail['start_date'],
					'end_date' => $booking_detail['end_date'],
				);
				$format = array('%d', '%d', '%s', '%s');
				for( $j = 0; $j < intval($booking_detail['room_amount']); $j++ ){
					$wpdb->insert("{$wpdb->prefix}tourmaster_room_booking", $data, $format);
				}

				// update available date
				tourmaster_room_check_occupied($booking_detail['room_id']);

				$ret['status'] = 'success';
				$ret['tid'] = $order_id;
				$ret['booking_details'] = $booking_details;
				$ret['booking_details_data'] = tourmaster_room_admin_add_order_details($booking_details);
			}

			die(json_encode($ret));
		}
	}

	if( !function_exists('tourmaster_room_order_new_form') ){
		function tourmaster_room_order_new_form($tid = '', $result = array()){

			$ret  = '';
			$ret .= tourmaster_get_form_field(array(
				'title' => esc_html__('Select Room :', 'tourmaster'),
				'echo' => false,
				'slug' => 'room_id',
				'type' => 'combobox',
				'options' => tourmaster_get_post_list('room', true)
			), 'order-edit');
			
			$ret .= tourmaster_get_form_field(array(
				'title' => esc_html__('Start Date :', 'tourmaster'),
				'echo' => false,
				'slug' => 'start_date',
				'type' => 'datepicker',
			), 'order-edit');

			$ret .= tourmaster_get_form_field(array(
				'title' => esc_html__('End Date :', 'tourmaster'),
				'echo' => false,
				'slug' => 'end_date',
				'type' => 'datepicker',
			), 'order-edit');

			$ret .= tourmaster_get_form_field(array(
				'title' => esc_html__('Room Amount', 'tourmaster'),
				'echo' => false,
				'slug' => 'room_amount',
				'type' => 'combobox',
				'options' => array(
					'1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5',
					'6' => '6', '7' => '7', '8' => '8', '9' => '9', '10' => '10' )
			), 'order-edit');


			$ret .= '<div class="tourmaster-room-order-add-guests" >';
			$ret .= '<div class="tourmaster-room-order-add-guests-label" >' . esc_html__('Room', 'tourmaster') . '<span>1</span></div>';
			$ret .= tourmaster_get_form_field(array(
				'title' => esc_html__('Adult', 'tourmaster'),
				'echo' => false,
				'slug' => 'adult[]',
				'type' => 'combobox',
				'options' => array( '0' => '0',
					'1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5',
					'6' => '6', '7' => '7', '8' => '8', '9' => '9', '10' => '10' )
			), 'order-edit');

			$ret .= tourmaster_get_form_field(array(
				'title' => esc_html__('Children', 'tourmaster'),
				'echo' => false,
				'slug' => 'children[]',
				'type' => 'combobox',
				'options' => array( '0' => '0',
					'1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5',
					'6' => '6', '7' => '7', '8' => '8', '9' => '9', '10' => '10' )
			), 'order-edit');
			$ret .= '</div>';

			$ret .= '<div class="tourmaster-new-order-button tourmaster-room-add-to-order" >Add to Order</div>';


			$booking_details = empty($result->booking_data)? array(): json_decode($result->booking_data, true);
			$ret .= '<div class="tourmaster-new-order-booking-detail" >';
			$ret .= '<h3>' . esc_html__('Booking Details', 'tourmaster') . '</h3>';
			$ret .= '<div class="tourmaster-new-order-booking-detail-data" >';
			$ret .= tourmaster_room_admin_add_order_details($booking_details);
			$ret .= '</div>';
			
			$ret .= '<div style="display: none" >';
			$ret .= '<input type="text" name="tid" value="' . (empty($tid)? '': esc_attr($tid)) . '"  />';
			$ret .= '<textarea name="booking_details" >' . (empty($result->booking_data)? '': esc_textarea($result->booking_data)) . '</textarea>';
			$ret .= '</div>';
			$ret .= '</div>';

			return $ret;
		}
	}

	if( !function_exists('tourmaster_room_admin_add_order_details') ){
		function tourmaster_room_admin_add_order_details($booking_details){
			$ret = '';
			
			for( $i = 0; $i < sizeof($booking_details); $i++ ){
				$booking_detail = $booking_details[$i];
				$ret .= '<div class="tourmaster-my-booking-single-field clearfix" >';
				$ret .= '<div class="tourmaster-head tourmaster-full">' . esc_html__('Room :', 'tourmaster') . ' ' . get_the_title($booking_detail['room_id']);
				$ret .= '<i class="tourmaster-room-admin-remove-order fa fa-trash" data-index="' . esc_attr($i) . '" data-confirm-message="' . esc_attr__('Are you sure you want to do this ?', 'tourmaster') . '" ></i></div> ';
				$ret .= '<div class="tourmaster-tail tourmaster-indent">' . sprintf(_n('%d Room', '%d Rooms', $booking_detail['room_amount'], 'tourmaster'), $booking_detail['room_amount']) . '</div>';
				$ret .= '<div class="tourmaster-tail tourmaster-indent">' . tourmaster_room_booking_duration_info($booking_detail['start_date'], $booking_detail['end_date'], false) . '</div>';
				$ret .= '</div>';
			}

			$ret .= '<a class="tourmaster-new-order-button" href="" >' . esc_html__('Refresh', 'tourmaster') . '</a>';

			return $ret;
		}
	}

	if( !function_exists('tourmaster_room_order_edit_form') ){
		function tourmaster_room_order_edit_form($tid, $type = '', $result = '' ){
			$ret  = '<form class="tourmaster-order-edit-form tourmaster-type-' . esc_attr($type) . '" action="" method="post" data-ajax-url="' . esc_attr(TOURMASTER_AJAX_URL) . '" >';

			if( $type == 'new_order' ){

				$ret .= tourmaster_room_order_new_form($tid, $result);

			}else if( $type == 'traveller' ){	

				// traveller detail
				$guest_fields = tourmaster_get_option('room_general', 'additional-guest-fields', '');
				if( !empty($guest_fields) ){
					$guest_fields = tourmaster_read_custom_fields($guest_fields);
				}

				$booking_details = empty($result->booking_data)? array(): json_decode($result->booking_data, true);
				$contact_info = empty($result->contact_info)? array(): json_decode($result->contact_info, true);

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
							$ret .= tourmaster_room_payment_guest_input($guest_fields, $i, $j, $k, $contact_info);
						}
					}
					$ret .= '</div>';
				}
				$ret .= '</div>';

			}else if( $type == 'additional_notes' ){

				$contact_detail = empty($result->contact_info)? array(): json_decode($result->contact_info, true);
				$value = empty($contact_detail['additional_notes'])? '': $contact_detail['additional_notes'];
				$ret .= tourmaster_get_form_field(array(
					'title' => esc_html__('Additional Notes :', 'tourmaster'),
					'echo' => false,
					'slug' => 'additional_notes',
					'type' => 'textarea'
				), 'order-edit', $value);

			}else if( $type == 'contact_details' ){

				$values = empty($result->contact_info)? array(): json_decode($result->contact_info, true);

				$form_fields = tourmaster_room_payment_contact_form_fields();
				foreach( $form_fields as $field_slug => $field ){
					$value = empty($values[$field_slug])? '': $values[$field_slug];
					$ret .= tourmaster_get_form_field(array(
						'title' => $field['title'],
						'echo' => false,
						'slug' => $field_slug,
						'type' => $field['type'],
						'options' => empty($field['options'])? array(): $field['options'],
						'required' => empty($field['required'])? false: true,
					), 'order-edit', $value);
				}

				$ret .= '<div class="tourmaster-payment-billing-separate-wrap" style="margin-left: 120px; margin-bottom: 20px;" ><label>';
				$ret .= '<input type="checkbox" name="required-billing" class="tourmaster-payment-billing-separate" value="1" ';
				$ret .= (!empty($values['required-billing']) && $values['required-billing'] != 'false')? 'checked ': '';
				$ret .= ' />';
				$ret .= '<span class="tourmaster-text" >' . esc_html__('Use different detail for billing', 'tourmaster') . '</span>';
				$ret .= '</label></div>';

				$ret .= '<div class="tourmaster-room-payment-billing-wrap" style="display: none;" >';
				foreach( $form_fields as $field_slug => $field ){
					$value = empty($values['billing_' . $field_slug])? '': $values['billing_' . $field_slug];
					$ret .= tourmaster_get_form_field(array(
						'title' => $field['title'],
						'echo' => false,
						'slug' => 'billing_' . $field_slug,
						'type' => $field['type'],
						'options' => empty($field['options'])? array(): $field['options'],
						'required' => empty($field['required'])? false: true,
					), 'order-edit', $value);
				}
				$ret .= '</div>';

			}else if( $type == 'price' ){

				$booking_details = empty($result->booking_data)? array(): json_decode($result->booking_data, true);
				$price_breakdowns = empty($result->price_breakdown)? array(): json_decode($result->price_breakdown, true);

				for( $i = 0; $i < sizeof($booking_details); $i++ ){
					$booking_detail = $booking_details[$i];
					$price_breakdown = $price_breakdowns[$i];

					for( $j = 0; $j < intval($booking_detail['room_amount']); $j++ ){
						$ret .= '<div class="tourmaster-room-price-edit-item" >';
						$ret .= '<h3 class="tourmaster-room-price-edit-title" >' . sprintf(esc_html__('%s : Room %d', 'tourmaster'), get_the_title($booking_detail['room_id']), $j+1) . '</h3>';
						
						// room date
						foreach( $price_breakdown['room-dates'][$j] as $date => $prices ){

							$ret .= '<div class="tourmaster-room-price-edit-date" >' . tourmaster_date_format($date) . '</div>';

							$ret .= tourmaster_get_form_field(array(
								'title' => esc_html__('Base Price', 'tourmaster'),
								'echo' => false,
								'slug' => 'base-price[' . $i . '][' . $j . '][' . $date . ']',
								'type' => 'price-edit'
							), 'order-edit', $prices['base-price']);	

							$ret .= tourmaster_get_form_field(array(
								'title' => esc_html__('Additional Adult', 'tourmaster'),
								'echo' => false,
								'slug' => 'additional-adult-price[' . $i . '][' . $j . '][' . $date . ']',
								'type' => 'price-edit',
								'description' => 'x' . $prices['additional-adult']
							), 'order-edit', $prices['additional-adult-price']);	

							$ret .= tourmaster_get_form_field(array(
								'title' => esc_html__('Additional Child', 'tourmaster'),
								'echo' => false,
								'slug' => 'additional-child-price[' . $i . '][' . $j . '][' . $date . ']',
								'type' => 'price-edit',
								'description' => 'x' . $prices['additional-child']
							), 'order-edit', $prices['additional-child-price']);	

						}
						
						// services

						$service_price = empty($price_breakdown['room-service-prices'][$j])? 0: $price_breakdown['room-service-prices'][$j];

						if( !empty($booking_detail['services'][$j]) ){
							$ret .= '<div class="tourmaster-room-price-edit-date" >' . esc_html__('Services', 'tourmaster') . '</div>';
						
							foreach( $booking_detail['services'][$j] as $service_id => $service_amount ){
								
								$service_amount = empty($service_amount)? 0: $service_amount;

								$ret .= tourmaster_get_form_field(array(
									'title' => sprintf(esc_html__('%s (Amount)', 'tourmaster'), get_the_title($service_id)),
									'echo' => false,
									'slug' => 'services[' . $i . '][' . $j . '][' . $service_id . ']',
									'type' => 'price-edit',
									'pre-input' => '<div class="tourmaster-price-edit-cross" >' . 
										'<span> x </span>' . 
									'</div>',
								), 'order-edit', $service_amount);

							}

							$ret .= tourmaster_get_form_field(array(
								'title' => esc_html__('Total Services Price', 'tourmaster'),
								'echo' => false,
								'slug' => 'room-service-prices[' . $i . '][' . $j . ']',
								'type' => 'price-edit'
							), 'order-edit', $service_price);	

						}
						
						$ret .= '</div>';

					}
				}

				// coupon
				$coupon_code = empty($price_breakdowns['coupon']['coupon-code'])? '': $price_breakdowns['coupon']['coupon-code'];
				$discount_text = '';

				if( !empty($price_breakdowns['coupon']['discount-type']) ){
					if( $price_breakdowns['coupon']['discount-type'] == 'percent' ){
						$discount_text = empty($price_breakdowns['coupon']['discount-amount'])? '': $price_breakdowns['coupon']['discount-amount'] . '%';
					}else if( $price_breakdowns['coupon']['discount-type'] == 'amount' ){
						$discount_text = empty($price_breakdowns['coupon']['discount-amount'])? '': $price_breakdowns['coupon']['discount-amount'];
					}
				}

				$ret .= '<h3 class="tourmaster-order-edit-title" >' . esc_html__('Discount', 'tourmaster'). '</h3>';
				$ret .= tourmaster_get_form_field(array(
					'title' => esc_html__('Coupon Code', 'tourmaster'),
					'echo' => false,
					'slug' => 'coupon-code',
					'type' => 'text'
				), 'order-edit', $coupon_code);
				$ret .= tourmaster_get_form_field(array(
					'title' => esc_html__('Discount Text', 'tourmaster'),
					'echo' => false,
					'slug' => 'discount-text',
					'type' => 'price-edit',
					'description' => esc_html__('With % or just number for fixed amount.', 'tourmaster')
				), 'order-edit', $discount_text);

			} // price
			
			$ret .= '<div class="tourmaster-order-edit-form-load" >' . esc_html__('Now loading', 'tourmaster') . '</div>';
			$ret .= '<div class="tourmaster-order-edit-form-error" >' . esc_html__('An error occurs, please check console for more information', 'tourmaster') . '</div>';
			$ret .= '<input type="hidden" name="tid" value="' . esc_attr($tid) . '" />';
			$ret .= '<input type="hidden" name="type" value="' . esc_attr($type) . '" />';
			$ret .= '<input type="hidden" name="action" value="tourmaster_room_order_edit" />';
			
			if( $type != 'new_order' ){
				$ret .= '<input type="submit" class="tourmaster-order-edit-submit" value="' . esc_attr__('Submit', 'tourmaster') . '" />';
			}
			$ret .= '</form>';

			return $ret;
		}
	}
	
	add_action('wp_ajax_tourmaster_room_order_edit', 'tourmaster_room_order_edit');
	if( !function_exists('tourmaster_room_order_edit') ){
		function tourmaster_room_order_edit(){

			global $wpdb;
			
			$data = tourmaster_process_post_data($_POST);

			/*
			// add - edit order
			if( $data['type'] == 'new_order' ){

				$result = tourmaster_get_booking_data(array('id' => $data['tid']), array('single' => true));
				$booking_detail = empty($result->booking_detail)? array(): json_decode($result->booking_detail, true);
				
				// get tour option
				$tour_option = tourmaster_get_post_meta($data['tour-id'], 'tourmaster-tour-option');
				$date_price = tourmaster_get_tour_date_price($tour_option, $data['tour-id'], $data['tour-date']);
				$date_price = tourmaster_get_tour_date_price_package($date_price, $data);
		
				// traveller amount
				if( $date_price['pricing-method'] == 'group' ){
					$traveller_amount = 1;
				}else{
					$traveller_amount = tourmaster_get_tour_people_amount($tour_option, $date_price, $data, 'all');
				}

				$fields = array( 
					'tid', 'tour-id', 'tour-date', 'package', 
					'group',
					'tour-people', 'tour-adult', 'tour-children', 'tour-student', 'tour-infant', 
					'tour-male', 'tour-female',
					'tour-room' 
				);
				foreach( $fields as $field ){
					if ( !empty($data[$field]) ){
						$booking_detail[$field] = $data[$field];
					}else{
						if( !empty($booking_detail[$field]) ){
							unset($booking_detail[$field]);
						}
					}
				}

				// check the service
				if( empty($booking_detail['service']) ){
					$booking_detail = tourmaster_set_mandatory_service($tour_option, $booking_detail);
				}

				// calculate the price
				$tour_price = tourmaster_get_tour_price($tour_option, $date_price, $booking_detail);
				$package_group_slug = empty($date_price['group-slug'])? '': $date_price['group-slug'];

				// built old traveller amount / contact / billing for booking_detail
				$tid = tourmaster_insert_booking_data($booking_detail, $tour_price, $traveller_amount, $package_group_slug, null, true);

				$ret = array('status' => 'success');
				if( empty($booking_detail['tid']) && !empty($data['current_url']) ){
					$ret['redirect'] = add_query_arg(array('single'=>$tid), $data['current_url']);
				}

				die(json_encode($ret));

			// traveller
			}else 
			*/

			if( $data['type'] == 'traveller' ){
				
				if( !empty($_POST['guest_first_name']) ){

					$sql  = "SELECT * FROM {$wpdb->prefix}tourmaster_room_order ";
					$sql .= $wpdb->prepare('WHERE id = %d', $data['tid']);
					$result = $wpdb->get_row($sql);

					$contact_detail = empty($result->contact_info)? array(): json_decode($result->contact_info, true);

					// guest first & last name
					if( !empty($data['guest_first_name']) ){
						$contact_detail['guest_first_name'] = $data['guest_first_name'];
					}
					if( !empty($data['guest_last_name']) ){
						$contact_detail['guest_last_name'] = $data['guest_last_name'];
					}

					// guest fields
					$guest_fields = tourmaster_get_option('room_general', 'additional-guest-fields', '');
					if( !empty($guest_fields) ){
						$guest_fields = tourmaster_read_custom_fields($guest_fields);
					}

					foreach( $guest_fields as $field_slug => $field_options ){
						if( !empty($data['traveller_' . $field_slug]) ){
							$contact_detail['traveller_' . $field_slug] = $data['traveller_' . $field_slug];
						}
					}	

					$wpdb->update("{$wpdb->prefix}tourmaster_room_order", array(
						'contact_info' => json_encode($contact_detail)
					), array(
						'id' => $data['tid']
					), array('%s'), array('%d'));
	
					die(json_encode(array('status' => 'success')));

				}

			// additional notes
			}else if( $data['type'] == 'additional_notes' ){
				
				if( !empty($data['additional_notes']) ){
					
					$sql  = "SELECT * FROM {$wpdb->prefix}tourmaster_room_order ";
					$sql .= $wpdb->prepare('WHERE id = %d', $data['tid']);
					$result = $wpdb->get_row($sql);

					$contact_detail = empty($result->contact_info)? array(): json_decode($result->contact_info, true);
					$contact_detail['additional_notes'] = $data['additional_notes'];

					$wpdb->update("{$wpdb->prefix}tourmaster_room_order", array(
						'contact_info' => json_encode($contact_detail)
					), array(
						'id' => $data['tid']
					), array('%s'), array('%d'));

					die(json_encode(array('status' => 'success')));
				}

			// contact details
			}else if( $data['type'] == 'contact_details' ){

				// retrieve old values
				$sql  = "SELECT * FROM {$wpdb->prefix}tourmaster_room_order ";
				$sql .= $wpdb->prepare('WHERE id = %d', $data['tid']);
				$result = $wpdb->get_row($sql);
				$values = empty($result->contact_info)? array(): json_decode($result->contact_info, true);

				$form_fields = tourmaster_room_payment_contact_form_fields();
				
				// contact field
				foreach( $form_fields as $field_slug => $field ){
					$values[$field_slug] = empty($data[$field_slug])? '': $data[$field_slug];

					// validate
					if( !empty($field['required']) && empty($data[$field_slug]) ){
						die(json_encode(array('status' => 'failed', 'message' => esc_html__('Please fill all required fields.', 'tourmaster'))));
					}
					if( $field['type'] == 'email' && !empty($data[$field_slug]) ){
						if( !is_email($data[$field_slug]) ){
							die(json_encode(array('status' => 'failed', 'message' => esc_html__('An E-mail is incorrect.', 'tourmaster'))));
						}
					}
				}

				$values['required-billing'] = empty($data['required-billing'])? '': $data['required-billing'];

				// billing field
				foreach( $form_fields as $field_slug => $field ){
					$values['billing_' . $field_slug] = empty($data['billing_' . $field_slug])? '': $data['billing_' . $field_slug];

					// validate
					if( !empty($values['required-billing']) && $values['required-billing'] != 'false' ){
						if( !empty($field['required']) && empty($data['billing_' . $field_slug]) ){
							die(json_encode(array('status' => 'failed', 'message' => esc_html__('Please fill all required fields.', 'tourmaster'))));
						}
						if( $field['type'] == 'email' && !empty($data['billing_' . $field_slug]) ){
							if( !is_email($data['billing_' . $field_slug]) ){
								die(json_encode(array('status' => 'failed', 'message' => esc_html__('An E-mail is incorrect.', 'tourmaster'))));
							}
						}
					}
				}
				
				$wpdb->update("{$wpdb->prefix}tourmaster_room_order", array(
					'contact_info' => json_encode($values)
				), array(
					'id' => $data['tid']
				), array('%s'), array('%d'));

				die(json_encode(array('status' => 'success')));

			// price
			}else if( $data['type'] == 'price' ){

				// retrieve old values
				$sql  = "SELECT * FROM {$wpdb->prefix}tourmaster_room_order ";
				$sql .= $wpdb->prepare('WHERE id = %d', $data['tid']);
				$result = $wpdb->get_row($sql);

				$booking_details = empty($result->booking_data)? array(): json_decode($result->booking_data, true);
				$price_breakdowns = empty($result->price_breakdown)? array(): json_decode($result->price_breakdown, true);
	
				// start calculating the price
				$price_breakdowns['sub-total-price'] = 0;
				$price_breakdowns['services-price'] = 0;

				for( $i = 0; $i < sizeof($booking_details); $i++ ){

					$price_breakdowns[$i]['room-prices'] = array();
					$price_breakdowns[$i]['sub-total-price'] = 0;

					for( $j = 0; $j < intval($booking_details[$i]['room_amount']); $j++ ){

						$price_breakdowns[$i]['room-prices'][$j] = 0;

						// room price
						foreach( $price_breakdowns[$i]['room-dates'][$j] as $date => $prices ){

							// apply room price
							if( isset($data['base-price'][$i][$j][$date]) ){
								$price_breakdowns[$i]['room-dates'][$j][$date]['base-price'] = $data['base-price'][$i][$j][$date];
							}
							if( isset($data['additional-adult-price'][$i][$j][$date]) ){
								$price_breakdowns[$i]['room-dates'][$j][$date]['additional-adult-price'] = $data['additional-adult-price'][$i][$j][$date];
							}
							if( isset($data['additional-child-price'][$i][$j][$date]) ){
								$price_breakdowns[$i]['room-dates'][$j][$date]['additional-child-price'] = $data['additional-child-price'][$i][$j][$date];
							}

							// calculate room price
							$price_breakdowns[$i]['room-dates'][$j][$date]['total-price'] = $price_breakdowns[$i]['room-dates'][$j][$date]['base-price'];
							if( !empty($price_breakdowns[$i]['room-dates'][$j][$date]['additional-adult']) && !empty($price_breakdowns[$i]['room-dates'][$j][$date]['additional-adult-price']) ){
								$price_breakdowns[$i]['room-dates'][$j][$date]['total-price'] += floatval($price_breakdowns[$i]['room-dates'][$j][$date]['additional-adult']) * floatval($price_breakdowns[$i]['room-dates'][$j][$date]['additional-adult-price']);
							}
							if( !empty($price_breakdowns[$i]['room-dates'][$j][$date]['additional-child']) && !empty($price_breakdowns[$i]['room-dates'][$j][$date]['additional-child-price']) ){
								$price_breakdowns[$i]['room-dates'][$j][$date]['total-price'] += floatval($price_breakdowns[$i]['room-dates'][$j][$date]['additional-child']) * floatval($price_breakdowns[$i]['room-dates'][$j][$date]['additional-child-price']);
							}

							// add to price summary
							$price_breakdowns[$i]['room-prices'][$j] += $price_breakdowns[$i]['room-dates'][$j][$date]['total-price'];
						}

						$price_breakdowns[$i]['sub-total-price'] += $price_breakdowns[$i]['room-prices'][$j];

						// service amount
						foreach( $booking_details[$i]['services'][$j] as $service_id => $service_amount ){
							if( isset($data['services'][$i][$j][$service_id]) ){
								$booking_details[$i]['services'][$j][$service_id] = $data['services'][$i][$j][$service_id];
							}
						}

						// service price
						if( isset($data['room-service-prices'][$i][$j]) ){
							$price_breakdowns[$i]['room-service-prices'][$j] = $data['room-service-prices'][$i][$j];
							$price_breakdowns['services-price'] += floatval($data['room-service-prices'][$i][$j]);
						}

					}

					$price_breakdowns['sub-total-price'] += $price_breakdowns[$i]['sub-total-price'];
				}

				// coupon code
				if( isset($data['coupon-code']) ){
					$price_breakdowns['coupon']['coupon-code'] = $data['coupon-code'];
				}
				if( isset($data['discount-text']) ){
					if( strpos($data['discount-text'], '%') !== false ){
						$price_breakdowns['coupon']['discount-type'] = 'percent';
						$price_breakdowns['coupon']['discount-amount'] = str_replace('%', '', $data['discount-text']);
					}else{
						$price_breakdowns['coupon']['discount-type'] = 'amount';
						$price_breakdowns['coupon']['discount-amount'] = $data['discount-text'];
					}

					if( empty($price_breakdowns['coupon']['type']) ){
						$coupon_after_tax = tourmaster_get_option('room_general', 'apply-coupon-after-tax', 'disable');
						$price_breakdowns['coupon']['type'] = ($coupon_after_tax == 'enable')? 'after-tax': 'before-tax';
					}
				}

				// finalize the price
				$price_breakdowns['total-price'] = $price_breakdowns['sub-total-price'] + $price_breakdowns['services-price'];
				if( !empty($price_breakdowns['coupon']) && $price_breakdowns['coupon']['type'] == 'before-tax' ){
					if( $price_breakdowns['coupon']['discount-type'] == 'percent' ){
						$price_breakdowns['coupon']['discount-price'] = (floatval($price_breakdowns['coupon']['discount-amount']) * $price_breakdowns['total-price']) / 100;
					}else if( $price_breakdowns['coupon']['discount-type'] == 'amount' ){
						$price_breakdowns['coupon']['discount-price'] = floatval($price_breakdowns['coupon']['discount-amount']);
					}
					$price_breakdowns['total-price'] -= $price_breakdowns['coupon']['discount-price'];
				}

				$price_breakdowns['tax-rate'] = empty($price_breakdowns['tax-rate'])? 0: floatval($price_breakdowns['tax-rate']);
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

				$wpdb->update("{$wpdb->prefix}tourmaster_room_order", array(
					'booking_data' => json_encode($booking_details),
					'price_breakdown' => json_encode($price_breakdowns)
				), array(
					'id' => $data['tid']
				), array('%s', '%s'), array('%d'));
				
				die(json_encode(array('status' => 'success')));

			} // end if
			
		}
	}