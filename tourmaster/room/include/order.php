<?php
	/*	
	*	Ordering Page
	*/

	add_action('admin_menu', 'tourmaster_room_init_order_page', 99);
	if( !function_exists('tourmaster_room_init_order_page') ){
		function tourmaster_room_init_order_page(){
			add_submenu_page(
				'tourmaster_room_admin_option',
				esc_html__('Room Order', 'tourmaster'), 
				esc_html__('Room Order', 'tourmaster'),
				'manage_room_order', 
				'tourmaster_room_order', 
				'tourmaster_room_create_order_page',
				120
			);
		}
	}

	// add the script when opening the theme option page
	add_action('admin_enqueue_scripts', 'tourmaster_room_order_page_script');
	if( !function_exists('tourmaster_room_order_page_script') ){
		function tourmaster_room_order_page_script($hook){
			if( strpos($hook, 'page_tourmaster_room_order') !== false ){
				tourmaster_include_utility_script(array(
					'font-family' => 'Open Sans'
				));

				wp_enqueue_style('tourmaster-order', TOURMASTER_URL . '/include/css/order.css');
				wp_enqueue_script('tourmaster-order', TOURMASTER_URL . '/include/js/order.js', array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'), false, true);
			
			
				// single order action
				if( isset($_GET['single']) ){
					
					// payment action
					tourmaster_room_single_order_payment_action();

					// change status
					if( !empty($_GET['single']) && !empty($_GET['status']) ){

						global $wpdb;
						$sql  = "SELECT * FROM {$wpdb->prefix}tourmaster_room_order ";
						$sql .= $wpdb->prepare("WHERE id = %d", $_GET['single']);
						$result = $wpdb->get_row($sql);	

						$updated = $wpdb->update("{$wpdb->prefix}tourmaster_room_order",
							array('order_status' => $_GET['status']),
							array('id' => $_GET['single']),
							array('%s'),
							array('%d')
						);

						wp_redirect(remove_query_arg(array('status')));

						// send the mail
						if( !empty($updated) ){
							if( in_array($_GET['status'], array('approved', 'online-paid')) ){
								tourmaster_room_mail_notification('payment-made-mail', $_GET['single']);
								tourmaster_room_send_email_invoice($_GET['single']);
							}else if( $_GET['status'] == 'deposit-paid' ){
								tourmaster_room_mail_notification('deposit-payment-made-mail', $_GET['single']);
								tourmaster_room_send_email_invoice($_GET['single']);
							}else if( $_GET['status'] == 'cancel' ){
								tourmaster_room_mail_notification('booking-cancelled-mail', $_GET['single']);
							}else if( $_GET['status'] == 'rejected' ){
								tourmaster_room_mail_notification('booking-reject-mail', $_GET['single']);
							}else if( $_GET['status'] == 'pending' && $result->order_status == 'wait-for-approval' ){
								tourmaster_room_mail_notification('booking-approve-mail', $_GET['single']);
							} 
						}
					}else if( !empty($_GET['single']) && !empty($_GET['action']) && $_GET['action'] == 'send-invoice' ){
						tourmaster_room_send_email_invoice($_GET['single']);
					}

				// order list action
				}else{
					
					if( !empty($_GET['action']) && !empty($_GET['id']) ){

						
						if( $_GET['action'] == 'remove' ){
	
							tourmaster_room_mail_notification('booking-reject-mail', $_GET['id']);
							 
							global $wpdb;
							$sql  = "SELECT DISTINCT room_id FROM {$wpdb->prefix}tourmaster_room_booking ";
							$sql .= $wpdb->prepare("WHERE order_id = %d", $_GET['id']);
							$results = $wpdb->get_results($sql);

							$wpdb->delete("{$wpdb->prefix}tourmaster_room_order", 
								array('id' => $_GET['id']),
								array('%d') 
							);
							$wpdb->delete("{$wpdb->prefix}tourmaster_room_booking", 
								array('order_id' => $_GET['id']),
								array('%d') 
							);
							
							// update occupancy
							foreach( $results as $result ){
								tourmaster_room_check_occupied($result->room_id);
							}

							wp_redirect(remove_query_arg(array('action', 'id')));
	   
	   
						}else if( in_array($_GET['action'], array('approved', 'rejected')) ){
							
							global $wpdb;
							
							// check old status first
							$sql  = "SELECT * FROM {$wpdb->prefix}tourmaster_room_order ";
							$sql .= $wpdb->prepare("WHERE id = %d", $_GET['id']);
							$result = $wpdb->get_row($sql);	

							if( $_GET['action'] == 'approved' ){
								if( $result->order_status == 'wait-for-approval' ){
									$order_status = 'pending';
								}else{
									$order_status = 'approved';
								}
							}else{
								$order_status = 'rejected';
							}
	   
							$updated = $wpdb->update("{$wpdb->prefix}tourmaster_room_order",
								array('order_status' => $order_status),
								array('id' => $_GET['id']),
								array('%s'),
								array('%d')
							);
							
							// send the mail
							if( !empty($updated) ){
								if( in_array($order_status, array('approved', 'online-paid', 'deposit-paid')) ){
									tourmaster_room_mail_notification('payment-made-mail', $_GET['id']);
									tourmaster_room_send_email_invoice($_GET['id']);
								}else if( $order_status == 'rejected' ){
									tourmaster_room_mail_notification('booking-reject-mail', $_GET['id']);
								}else if( $order_status == 'pending' ){
									tourmaster_room_mail_notification('booking-approve-mail', $_GET['id']);
								}
							}
							
							wp_redirect(remove_query_arg(array('action', 'id')));
						}
					}

				}

			}
		}
	}

	if( !function_exists('tourmaster_room_order_csv_export') ){
		function tourmaster_room_order_csv_export( $results ){

			// define constant
			$current_url = (is_ssl()? "https": "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$statuses = array(
				'all' => __('All', 'tourmaster'),
				'pending' => __('Pending', 'tourmaster'),
				'approved' => __('Approved', 'tourmaster'),
				'receipt-submitted' => __('Receipt Submitted', 'tourmaster'),
				'online-paid' => __('Online Paid', 'tourmaster'),
				'deposit-paid' => __('Deposit Paid', 'tourmaster'),
				// 'departed' => __('Departed', 'tourmaster'),
				'rejected' => __('Rejected', 'tourmaster'),
				'cancel' => __('Cancel', 'tourmaster'),
				'wait-for-approval' => __('Wait For Approval', 'tourmaster'),
			);

			// print it as file
			$fp = fopen(TOURMASTER_LOCAL . '/include/js/order.csv', 'w');
			fputcsv($fp, array(
				__('Order ID', 'tourmaster'),
				__('Room', 'tourmaster'),
				__('Contact Name', 'tourmaster'),
				__('Contact Email', 'tourmaster'),
				__('Contact Number', 'tourmaster'),
				__('Customer\'s Note', 'tourmaster'),
				__('Booking Date', 'tourmaster'),
				__('Total Price', 'tourmaster'),
				__('Payment Status', 'tourmaster'),
				__('Link To Transaction', 'tourmaster'),
			));
			foreach( $results as $result ){

				$room_data = '';
				$booking_details = empty($result->booking_data)? array(): json_decode($result->booking_data, true);
				for( $i = 0; $i < sizeof($booking_details); $i++ ){
					$booking_detail = $booking_details[$i];

					$room_data .= esc_html__('Room :', 'tourmaster') . ' ' . get_the_title($booking_detail['room_id']) . "\n";
					$room_data .= sprintf(_n('%d Room', '%d Rooms', $booking_detail['room_amount'], 'tourmaster'), $booking_detail['room_amount']) . "\n";
					$room_data .= tourmaster_room_booking_duration_info($booking_detail['start_date'], $booking_detail['end_date'], false) . "\n";
				}

				$contact_info = empty($result->contact_info)? array(): json_decode($result->contact_info, true);

				fputcsv($fp, array(
					'#' . $result->id,
					html_entity_decode($room_data),
					$contact_info['first_name'] . ' ' . $contact_info['last_name'],
					$contact_info['email'],
					$contact_info['phone'],
					empty($contact_info['additional_notes'])? ' ': $contact_info['additional_notes'],
					tourmaster_date_format($result->booking_date),
					tourmaster_money_format($result->total_price),
					$statuses[$result->order_status],
					add_query_arg(
						array('single'=>$result->id), 
						remove_query_arg(array('order_id', 'from_date', 'to_date', 'action', 'id', 'export'), $current_url)
					)
				));
			}
			fclose($fp);

// script for user to download
?><script>
	jQuery(document).ready(function(){
		var element = document.createElement('a');
		element.setAttribute('href', '<?php echo esc_js(TOURMASTER_URL . '/include/js/order.csv'); ?>');
		element.setAttribute('download', 'transaction.csv');
		
		element.style.display = 'none';
		document.body.appendChild(element);

		element.click();
		document.body.removeChild(element);
	});
</script><?php

		} 
	}

	if( !function_exists('tourmaster_room_create_order_page') ){
		function tourmaster_room_create_order_page(){
			
			// new order
			echo '<a class="tourmaster-new-order-text" href="#" data-tmlb="new-order">';
			echo esc_html__('Add New Booking', 'tourmaster');
			echo '</a>';

			echo tourmaster_lightbox_content(array(
				'id' => 'new-order',
				'title' => esc_html__('Edit Order', 'tourmaster'),
				'content' => tourmaster_room_order_edit_form('', 'new_order')
			));	

			// print order
			if( !isset($_GET['single']) ){
				$action_url = remove_query_arg(array('order_id', 'from_date', 'to_date', 'action', 'id', 'export'));

				$statuses = array(
					'all' => esc_html__('All', 'tourmaster'),
					'pending' => esc_html__('Pending', 'tourmaster'),
					'approved' => esc_html__('Approved', 'tourmaster'),
					'receipt-submitted' => esc_html__('Receipt Submitted', 'tourmaster'),
					'online-paid' => esc_html__('Online Paid', 'tourmaster'),
					'deposit-paid' => esc_html__('Deposit Paid', 'tourmaster'),
					// 'departed' => esc_html__('Departed', 'tourmaster'),
					'rejected' => esc_html__('Rejected', 'tourmaster'),
					'cancel' => esc_html__('Cancel', 'tourmaster'),
					'wait-for-approval' => __('Wait For Approval', 'tourmaster'),
				);
?>
<div class="tourmaster-order-filter-wrap" >
	<form class="tourmaster-order-search-form" method="get" action="<?php echo esc_url($action_url); ?>" >
		<label><?php esc_html_e('Search by order id :', 'tourmaster'); ?></label>
		<input type="text" name="order_id" value="<?php echo empty($_GET['order_id'])? '': esc_attr($_GET['order_id']); ?>" />
		<input type="hidden" name="page" value="tourmaster_room_order" />
		<input type="submit" value="<?php esc_html_e('Search', 'tourmaster'); ?>" />
	</form>
	<form class="tourmaster-order-search-form" method="get" action="<?php echo esc_url($action_url); ?>" >
		<div style="margin-bottom: 10px;" >
			<label><?php esc_html_e('Select Room :', 'tourmaster'); ?></label>
			<select name="room_id" ><?php
				$tour_list = tourmaster_get_post_list('room');
				echo '<option value="" >' . esc_html__('All', 'tourmaster') . '</option>';
				foreach( $tour_list as $tour_id => $tour_name ){
					echo '<option value="' . esc_attr($tour_id) . '" ' . ((!empty($_GET['tour_id']) && $_GET['tour_id'] == $tour_id)? 'selected': '') . ' >' . esc_html($tour_name) . '</option>';
				}	
			?></select>
			<br>
		</div>
		<label><?php esc_html_e('Date Filter :', 'tourmaster'); ?></label>
		<span class="tourmaster-separater" ><?php esc_html_e('From', 'tourmaster') ?></span>
		<input class="tourmaster-datepicker" type="text" name="from_date" value="<?php echo empty($_GET['from_date'])? '': esc_attr($_GET['from_date']); ?>" />
		<span class="tourmaster-separater" ><?php esc_html_e('To', 'tourmaster') ?></span>
		<input class="tourmaster-datepicker" type="text" name="to_date" value="<?php echo empty($_GET['to_date'])? '': esc_attr($_GET['to_date']); ?>" />
		<input type="hidden" name="page" value="tourmaster_room_order" />
		<input type="hidden" name="export" value="0" />
		<input type="submit" value="<?php esc_html_e('Filter', 'tourmaster'); ?>" />
		<input id="tourmaster-csv-export" type="button" value="<?php esc_html_e('Export To CSV', 'tourmaster'); ?>" />
	</form>
	<div class="tourmaster-order-filter" >
	<?php
		$order_status = empty($_GET['order_status'])? 'all': $_GET['order_status'];
		foreach( $statuses as $status_slug => $status ){
			echo '<span class="tourmaster-separator" >|</span>';
			echo '<a href="' . esc_url(add_query_arg(array('order_status'=>$status_slug), $action_url)) . '" ';
			echo 'class="tourmaster-order-filter-status ' . ($status_slug == $order_status? 'tourmaster-active': '') . '" >';
			echo $status;
			echo '</a>';
		}
	?>
	</div>
</div>
<?php				
			}

			echo '<div class="tourmaster-order-page-wrap" >';
			echo '<div class="tourmaster-order-page-head" >';
			echo '<i class="fa fa-check-circle-o" ></i>';
			echo esc_html__('Transaction Order', 'tourmaster');
			echo '</div>'; // tourmaster-order-page-head

			echo '<div class="tourmaster-order-page-content clearfix" >';
			if( isset($_GET['single']) ){
				tourmaster_room_get_single_order();
			}else{
				tourmaster_room_get_order_list();
			}

			echo '</div>'; // tourmaster-order-page-content
			echo '</div>'; // tourmaster-order-page-wrap
		}
	}

	if( !function_exists('tourmaster_room_get_order_list') ){
		function tourmaster_room_get_order_list(){

			global $wpdb;

			// print the order
			$paged = empty($_GET['paged'])? 1: $_GET['paged'];
			$num_fetch = 10;

			$where = 'WHERE ';
			$where_sql = '';
			if( !empty($_GET['order_status']) && $_GET['order_status'] != 'all' ){
				$where_sql .= $where . $wpdb->prepare("order_status = %s ", $_GET['order_status']);
				$where = 'AND ';
			}
			if( !empty($_GET['order_id']) ){
				$where_sql .= $where . $wpdb->prepare("id = %d ", $_GET['order_id']);
				$where = 'AND ';
			}
			if( !empty($_GET['room_id']) ){
				$sub_sql  = "(SELECT DISTINCT order_id FROM {$wpdb->prefix}tourmaster_room_booking ";
				$sub_sql .= $wpdb->prepare("WHERE room_id = %d) ", $_GET['room_id']);
				$where_sql .= $where . "id IN {$sub_sql}";
				$where = 'AND ';
			}
			if( !empty($_GET['from_date']) && !empty($_GET['to_date']) ){
				$sub_sql  = "(SELECT DISTINCT order_id FROM {$wpdb->prefix}tourmaster_room_booking ";
				$sub_sql .= $wpdb->prepare("WHERE end_date >= %s ", $_GET['from_date']);
				$sub_sql .= $wpdb->prepare("AND start_date <= %s ", $_GET['to_date']);
				$sub_sql .= ")";
				$where_sql .= $where . "id IN {$sub_sql}";
				$where = 'AND ';
			}
			if( !empty($_GET['export']) ){
				$export_sql  = "SELECT * FROM {$wpdb->prefix}tourmaster_room_order " . $where_sql;
				$export_sql .= 'ORDER BY id DESC ';
				$export_results = $wpdb->get_results($export_sql);

				tourmaster_room_order_csv_export($export_results);
			}

			$sql  = "SELECT * FROM {$wpdb->prefix}tourmaster_room_order " . $where_sql;
			$sql .= 'ORDER BY id DESC ';
			$sql .= tourmaster_get_sql_page_part($paged, $num_fetch);
			$results = $wpdb->get_results($sql);

			$sql  = "SELECT COUNT(*) FROM {$wpdb->prefix}tourmaster_room_order " . $where_sql;
			$max_num_page = ceil($wpdb->get_var($sql) / $num_fetch);

			echo '<table>';
			echo tourmaster_get_table_head(array(
				esc_html__('Order', 'tourmaster'),
				esc_html__('Travel Date', 'tourmaster'),
				esc_html__('Contact Detail', 'tourmaster'),
				esc_html__('Customer\'s Note', 'tourmaster'),
				esc_html__('Booking Date', 'tourmaster'),
				esc_html__('Total', 'tourmaster'),
				esc_html__('Payment Status', 'tourmaster'),
				esc_html__('Action', 'tourmaster'),
			));
			$statuses = array(
				'all' => esc_html__('All', 'tourmaster'),
				'pending' => esc_html__('Pending', 'tourmaster'),
				'approved' => esc_html__('Approved', 'tourmaster'),
				'receipt-submitted' => esc_html__('Receipt Submitted', 'tourmaster'),
				'online-paid' => esc_html__('Online Paid', 'tourmaster'),
				'deposit-paid' => esc_html__('Deposit Paid', 'tourmaster'),
				// 'departed' => esc_html__('Departed', 'tourmaster'),
				'rejected' => esc_html__('Rejected', 'tourmaster'),
				'cancel' => esc_html__('Cancel', 'tourmaster'),
				'wait-for-approval' => __('Wait For Approval', 'tourmaster'),
			);

			foreach( $results as $result ){

				tourmaster_set_currency($result->currency);

				$order_title  = '<div class="tourmaster-content" ><a href="' . add_query_arg(array('single'=>$result->id), remove_query_arg(array('order_id', 'from_date', 'to_date', 'action', 'id', 'export'))) . '" >';
				$order_title .= '#' . $result->id;
				$order_title .= '</a></div>';

				$booking_data = json_decode($result->booking_data, true);
				$travel_date = sprintf(esc_html__('%s to %s', 'tourmaster'), tourmaster_date_format($booking_data[0]['start_date']), tourmaster_date_format($booking_data[0]['end_date']));
				
				$contact_detail = empty($result->contact_info)? array(): json_decode($result->contact_info, true);
				$buyer_info  = '<div class="tourmaster-head" >';
				$buyer_info .= empty($contact_detail['first_name'])? '': $contact_detail['first_name'] . ' ';
				$buyer_info .= empty($contact_detail['last_name'])? '': $contact_detail['last_name'] . ' ';
				$buyer_info .= '</div>';
				$buyer_info .= '<div class="tourmaster-content" >';
				$buyer_info .= empty($contact_detail['phone'])? '': $contact_detail['phone'] . ' ';
				$buyer_info .= empty($contact_detail['email'])? '': '<a href="mailto:' . esc_attr($contact_detail['email']) . '" ><i class="fa fa-envelope-o" ></i></a>';
				$buyer_info .= '</div>';

				$additional_note = '';
				if( !empty($contact_detail['additional_notes']) ){
					$additional_note  = wp_trim_words($contact_detail['additional_notes'], 15);
				}

				$booking_date = tourmaster_date_format($result->booking_date);

				$tour_price = tourmaster_money_format($result->total_price);

				$order_status  = '<span class="tourmaster-order-status tourmaster-status-' . esc_attr($result->order_status) . '" >';
				if( $result->order_status == 'approved' ){
					$order_status .= '<i class="fa fa-check" ></i>';
				}else if( $result->order_status == 'departed' ){
					$order_status .= '<i class="fa fa-check-circle-o" ></i>';
				}else if( $result->order_status == 'rejected' || $result->order_status == 'cancel' ){
					$order_status .= '<i class="fa fa-remove" ></i>';
				}	
				$order_status .= $statuses[$result->order_status];
				if( $result->order_status == 'pending' && empty($result->user_id) ){
					$order_status .= ' <br>' . esc_html__('(Via E-mail)', 'tourmaster');
				}
				$order_status .= '</span>';

				$action  = '<a href="' . add_query_arg(array('single'=>$result->id), remove_query_arg(array('id','action'))) . '" class="tourmaster-order-action" title="' . esc_html__('View', 'tourmaster') . '" >';
				$action .= '<i class="fa fa-eye" ></i>';
				$action .= '</a>';
				$action .= '<a href="' . add_query_arg(array('id'=>$result->id, 'action'=>'approved')) . '" class="tourmaster-order-action" title="' . esc_html__('Approve', 'tourmaster') . '" ';
				$action .= 'data-confirm="' . esc_html__('After approving the transaction, invoice and payment receipt will be sent to customer\'s billing email.', 'tourmaster') . '" ';
				$action .= '>';
				$action .= '<i class="fa fa-check" ></i>';
				$action .= '</a>';
				$action .= '<a href="' . add_query_arg(array('id'=>$result->id, 'action'=>'rejected')) . '" class="tourmaster-order-action" title="' . esc_html__('Reject', 'tourmaster') . '" ';
				$action .= 'data-confirm="' . esc_html__('After rejected the transaction, the rejection message will be sent to customer\'s contact email.', 'tourmaster') . '" ';
				$action .= '>';
				$action .= '<i class="fa fa-remove" ></i>';
				$action .= '</a>';
				$action .= '<a href="' . add_query_arg(array('id'=>$result->id, 'action'=>'remove')) . '" class="tourmaster-order-action" title="' . esc_html__('Remove', 'tourmaster') . '" ';
				$action .= 'data-confirm="' . esc_html__('The transaction you selected will be permanently removed from the system.', 'tourmaster') . '" ';
				$action .= '>';
				$action .= '<i class="fa fa-trash-o" ></i>';
				$action .= '</a>';

				tourmaster_get_table_content(array($order_title, $travel_date, $buyer_info, $additional_note, $booking_date, $tour_price, $order_status, $action));
			}

			tourmaster_reset_currency();

			echo '</table>';

			if( !empty($max_num_page) && $max_num_page > 1 ){
				echo '<div class="tourmaster-transaction-pagination" >';
				$dot = false;
			for($i=1; $i<=$max_num_page; $i++){
				if( $i == $paged ){
					$dot = true;
					echo '<span class="tourmaster-transaction-pagination-item tourmaster-active" >' . $i . '</span>';
				}else if( ($i <= $paged + 2 && $i >= $paged -2) || $i == 1 || $i == $max_num_page ){
					$dot = true;
					echo '<a href="' . add_query_arg(array('paged'=>$i), remove_query_arg(array('action'))) . '" class="tourmaster-transaction-pagination-item" >' . $i . '</a>';
				}else if( $dot ){
					$dot = false;
					echo '<span class="page-numbers dots">…</span>';
				}
			}

				echo '</div>';
			}

		}
	}

	if( !function_exists('tourmaster_room_single_order_payment_action') ){
		function tourmaster_room_single_order_payment_action(){

			// for payment status
			if( isset($_GET['payment_info']) && $_GET['action'] ){
				
				global $wpdb;
				$sql  = "SELECT * FROM {$wpdb->prefix}tourmaster_room_order ";
				$sql .= $wpdb->prepare('WHERE id = %d', $_GET['single']);
				$result = $wpdb->get_row($sql);

				$order_status = $result->order_status;
				$payment_infos = empty($result->payment_info)? array(): json_decode($result->payment_info, true);
				$i = intval($_GET['payment_info']);

				// email info
				$submission_date = '';
				$submission_amount = 0;
				$submission_transaction_id = '';
				if( !empty($payment_infos[$i]) ){
					if( !empty($payment_infos[$i]['submission_date']) ){
						$submission_date = $payment_infos[$i]['submission_date'];
					}		
					if( !empty($payment_infos[$i]['amount']) ){
						$submission_amount = $payment_infos[$i]['amount'];
					}
					if( !empty($payment_infos[$i]['transaction_id']) ){
						$submission_transaction_id = $payment_infos[$i]['transaction_id'];
					}
				}
				
				// do an action
				if( $_GET['action'] == 'approve' ){
					$payment_infos[$i]['payment_status'] = 'paid';
				}else if( $_GET['action'] == 'remove' ){
					unset($payment_infos[$i]);
				}

				$order_status = tourmaster_room_payment_order_status($result->total_price, $payment_infos, false);
				
				$wpdb->update("{$wpdb->prefix}tourmaster_room_order",
					array(
						'payment_info' => json_encode(array_values($payment_infos)),
						'order_status' => $order_status
					),
					array('id' => $_GET['single']),
					array('%s'),
					array('%d')
				);

				if( $_GET['action'] == 'approve' ){
					tourmaster_room_mail_notification('receipt-approve-mail', $_GET['single'], '', array('custom' => $payment_infos[$i]));
					tourmaster_room_send_email_invoice($_GET['single']);
				}else if( $_GET['action'] == 'remove' ){
					tourmaster_room_mail_notification('receipt-reject-mail', $_GET['single'], '', array('custom' => $payment_infos[$i]));
				}

				wp_redirect(remove_query_arg(array('payment_info', 'action')));
			}
		}
	}

	if( !function_exists('tourmaster_room_get_single_order') ){
		function tourmaster_room_get_single_order(){

			global $wpdb;
			$sql  = "SELECT * FROM {$wpdb->prefix}tourmaster_room_order ";
			$sql .= $wpdb->prepare('WHERE id = %d', $_GET['single']);
			$result = $wpdb->get_row($sql);

			tourmaster_set_currency($result->currency);

			// $tour_option = tourmaster_get_post_meta($result->tour_id, 'tourmaster-tour-option');

			// from my-booking-single.php
			/*
			$contact_fields = tourmaster_get_payment_contact_form_fields($result->tour_id);
			$contact_detail = empty($result->contact_info)? array(): json_decode($result->contact_info, true);
			$billing_detail = empty($result->billing_info)? array(): json_decode($result->billing_info, true);
			$booking_detail = empty($result->booking_detail)? array(): json_decode($result->booking_detail, true);
			*/
			$contact_fields = tourmaster_room_payment_contact_form_fields();
			$contact_detail = empty($result->contact_info)? array(): json_decode($result->contact_info, true);

			// sidebar
			echo '<div class="tourmaster-my-booking-single-sidebar" >';
			$statuses = array(
				'all' => esc_html__('All', 'tourmaster'),
				'pending' => esc_html__('Pending', 'tourmaster'),
				'approved' => esc_html__('Approved', 'tourmaster'),
				'receipt-submitted' => esc_html__('Receipt Submitted', 'tourmaster'),
				'online-paid' => esc_html__('Online Paid', 'tourmaster'),
				'deposit-paid' => esc_html__('Deposit Paid', 'tourmaster'),
				// 'departed' => esc_html__('Departed', 'tourmaster'),
				'rejected' => esc_html__('Rejected', 'tourmaster'),
				'cancel' => esc_html__('Cancel', 'tourmaster'),
				'wait-for-approval' => __('Wait For Approval', 'tourmaster'),
			);
			echo '<h3 class="tourmaster-my-booking-single-title">' . esc_html__('Order Status', 'tourmaster') . '</h3>';
			echo '<div class="tourmaster-booking-status tourmaster-status-' . esc_attr($result->order_status) . '" >';
			echo '<form action="' . add_query_arg(array('action' => 'update-status')) . '" method="GET" >';
			echo '<div class="tourmaster-custom-combobox" >';
			echo '<select name="status" >';
			foreach( $statuses as $status_slug => $status_title ){
				if( $status_slug == 'all' ) continue;
				echo '<option value="' . esc_attr($status_slug) . '" ' . ($status_slug == $result->order_status? 'selected': '') . '>';
				echo esc_html($status_title);
				if( $status_slug == 'pending' && empty($result->user_id) ){
					echo ' ' . esc_html__('(Via E-mail)', 'tourmaster');
				}
				echo '</option>';
			}
			echo '</select>';
			echo '</div>'; // tourmaster-combobox
			echo '<input class="tourmaster-button" id="tourmaster-update-booking-status" type="submit" value="' . esc_html__('Update Status', 'tourmaster') . '" />';
			if( !empty($_GET['page']) ){
				echo '<input name="page" type="hidden" value="' . esc_attr($_GET['page']) . '" />';
			}
			if( !empty($_GET['single']) ){
				echo '<input name="single" type="hidden" value="' . esc_attr($_GET['single']) . '" />';
			}
			echo '</form>';
			echo '</div>'; // tourmaster-booking-status
			
			if( !empty($result->woocommerce_order_id) ){

				$wc_order = wc_get_order($result->woocommerce_order_id);
				$wc_edit_order_url = $wc_order->get_edit_order_url();
				if( !empty($wc_edit_order_url) ){
					echo '<h3 class="tourmaster-my-booking-single-sub-title">' . esc_html__('Bank Payment Receipt', 'tourmaster') . '</h3>';
					echo '<a href="' . esc_attr($wc_edit_order_url) . '" >';
					echo sprintf(esc_html__('Edit Woocommerce Order (#%d)', 'tourmaster'), $result->woocommerce_order_id);
					echo '</a>';
				}

			}

			$payment_infos = empty($result->payment_info)? array(): json_decode($result->payment_info, true);
			if( !empty($payment_infos) ){
				
				echo '<h3 class="tourmaster-my-booking-single-sub-title">' . esc_html__('Bank Payment Receipt', 'tourmaster') . '</h3>';
				
				$paid_amount = 0;
				$paid_times = 0;
				$count = 0;
				foreach( $payment_infos as $payment_info ){ $count++;

					if( !empty($payment_info['amount']) ){
						$paid_times++;
						$paid_amount += floatval($payment_info['amount']);
					}

					echo '<div class="tourmaster-deposit-item ' . ($paid_times == sizeof($payment_infos)? 'tourmaster-active': '') . '" >';
					echo '<div class="tourmaster-deposit-item-head" ><i class="icon_plus" ></i>';
					if( tourmaster_compare_price($paid_amount, $result->total_price) || $paid_amount > $result->total_price ){
						echo sprintf(esc_html__('Final Payment : %s', 'tourmaster'), tourmaster_money_format($paid_amount));
					}else{
						echo sprintf(esc_html__('Deposit %d : %s', 'tourmaster'), $paid_times, tourmaster_money_format($paid_amount));
					}
					echo '</div>';
	
					echo '<div class="tourmaster-deposit-item-content" >';
					if( $payment_info['payment_status'] == 'pending' ){
						echo '<a href="' . add_query_arg(array('payment_info'=>($count-1), 'action'=>'approve')) . '" >';
						echo '<i class="fa fa-check-circle-o" ></i>' . esc_html__('Approve', 'tourmaster');
						echo '</a><br>';
					}
					echo '<a class="tourmaster-remove" href="' . esc_url(add_query_arg(array('payment_info'=>($count-1), 'action'=>'remove'))) . '" data-confirm >';
					echo '<i class="fa fa-times-circle-o" ></i>' . esc_html__('Reject / Remove', 'tourmaster');
					echo '</a><br><br>';

					tourmaster_room_deposit_item_content($result, $payment_info);
					echo '</div>';
					echo '</div>';
				}
			}
			echo '</div>'; // tourmaster-my-booking-single-sidebar

			// content
			$detail_column = 20;
			if( empty($contact_detail['required-billing']) || $contact_detail['required-billing'] == 'false' ){
				$detail_column = 30;
			}

			echo '<div class="tourmaster-my-booking-single-content clearfix" >';
			echo '<div class="tourmaster-item-rvpdlr clearfix" >';
			echo '<div class="tourmaster-my-booking-single-order-summary-column tourmaster-column-' . esc_attr($detail_column) . ' tourmaster-item-pdlr" >';
			echo '<h3 class="tourmaster-my-booking-single-title">';
			echo esc_html__('Order Summary', 'tourmaster');
			echo tourmaster_order_edit_text('new-order');
			echo tourmaster_lightbox_content(array(
				'id' => 'new-order',
				'title' => esc_html__('Edit Order', 'tourmaster'),
				'content' => tourmaster_room_order_edit_form($_GET['single'], 'new_order', $result)
			));	
			echo '</h3>';

			if( $result->order_status == 'pending' && empty($result->user_id) ){
				echo '<div class="tourmaster-my-booking-pending-via-email" >';
				echo esc_html__('This booking has been made manually via email. Customer won\'t see from their dashboard. You should contact back to customer manually.', 'tourmaster');
				echo '</div>';
			}

			echo '<div class="tourmaster-my-booking-single-field clearfix" >';
			echo '<span class="tourmaster-head">' . esc_html__('Order Number', 'tourmaster') . ' :</span> ';
			echo '<span class="tourmaster-tail">#' . $result->id . '</span>';
			echo '</div>';

			echo '<div class="tourmaster-my-booking-single-field clearfix" >';
			echo '<span class="tourmaster-head">' . esc_html__('Booking Date', 'tourmaster') . ' :</span> ';
			echo '<span class="tourmaster-tail">' . tourmaster_date_format($result->booking_date) . '</span>';
			echo '</div>';

			$booking_details = empty($result->booking_data)? array(): json_decode($result->booking_data, true);
			for( $i = 0; $i < sizeof($booking_details); $i++ ){
				$booking_detail = $booking_details[$i];
				echo '<div class="tourmaster-my-booking-single-field clearfix" >';
				echo '<div class="tourmaster-head tourmaster-full">' . esc_html__('Room :', 'tourmaster') . ' ' . get_the_title($booking_detail['room_id']) . '</div> ';
				echo '<div class="tourmaster-tail tourmaster-indent">' . sprintf(_n('%d Room', '%d Rooms', $booking_detail['room_amount'], 'tourmaster'), $booking_detail['room_amount']) . '</div>';
				echo '<div class="tourmaster-tail tourmaster-indent">' . tourmaster_room_booking_duration_info($booking_detail['start_date'], $booking_detail['end_date'], false) . '</div>';
				echo '</div>';
			}

			// customer note
			echo '<div class="tourmaster-my-booking-single-field tourmaster-additional-note clearfix" >';
			echo '<div class="tourmaster-head">';
			echo esc_html__('Customer\'s Note', 'tourmaster') . ' :';
			echo tourmaster_order_edit_text('edit-additional-notes');
			echo tourmaster_lightbox_content(array(
				'id' => 'edit-additional-notes',
				'title' => esc_html__('Customer\'s Note', 'tourmaster'),
				'content' => tourmaster_room_order_edit_form($_GET['single'], 'additional_notes', $result)
			));	
			echo '</div> ';
			echo '<div class="tourmaster-tail">';
			if( !empty($contact_detail['additional_notes']) ){
				echo $contact_detail['additional_notes'];
			}
			echo '</div>';
			echo '</div>';

			echo '</div>'; // tourmaster-my-booking-single-order-summary-column

			echo '<div class="tourmaster-my-booking-single-contact-detail-column tourmaster-column-' . esc_attr($detail_column) . ' tourmaster-item-pdlr" >';
			echo '<h3 class="tourmaster-my-booking-single-title">';
			echo esc_html__('Contact Detail', 'tourmaster');
			echo tourmaster_order_edit_text('edit-contact-details');
			echo tourmaster_lightbox_content(array(
				'id' => 'edit-contact-details',
				'title' => esc_html__('Contact Details', 'tourmaster'),
				'content' => tourmaster_room_order_edit_form($_GET['single'], 'contact_details', $result)
			));	
			echo '</h3>';
			foreach( $contact_fields as $field_slug => $contact_field ){
				if( !empty($contact_detail[$field_slug]) ){
					echo '<div class="tourmaster-my-booking-single-field clearfix" >';
					echo '<span class="tourmaster-head">' . $contact_field['title'] . ' :</span> ';
					if( $field_slug == 'country' ){
						echo '<span class="tourmaster-tail">' . tourmaster_get_country_list('', $contact_detail[$field_slug]) . '</span>';
					}else{
						echo '<span class="tourmaster-tail">' . $contact_detail[$field_slug] . '</span>';
					}
					echo '</div>';
				}
			}
			echo '</div>'; // tourmaster-my-booking-single-contact-detail-column

			if( !empty($contact_detail['required-billing']) && $contact_detail['required-billing'] != 'false' ){
				echo '<div class="tourmaster-my-booking-single-billing-detail-column tourmaster-column-' . esc_attr($detail_column) . ' tourmaster-item-pdlr" >';
				echo '<h3 class="tourmaster-my-booking-single-title">';
				echo esc_html__('Billing Detail', 'tourmaster');
				/*
				echo tourmaster_order_edit_text('edit-billing-details');
				echo tourmaster_lightbox_content(array(
					'id' => 'edit-billing-details',
					'title' => esc_html__('Billing Details', 'tourmaster'),
					'content' => tourmaster_order_edit_form($_GET['single'], 'billing_details', $result)
				));	
				*/
				echo '</h3>';
				foreach( $contact_fields as $field_slug => $contact_field ){
					if( !empty($contact_detail['billing_' . $field_slug]) ){
						echo '<div class="tourmaster-my-booking-single-field clearfix" >';
						echo '<span class="tourmaster-head">' . $contact_field['title'] . ' :</span> ';
						if( $field_slug == 'country' ){
							echo '<span class="tourmaster-tail">' . tourmaster_get_country_list('', $contact_detail['billing_' . $field_slug]) . '</span>';
						}else{
							echo '<span class="tourmaster-tail">' . $contact_detail['billing_' . $field_slug] . '</span>';
						}
						echo '</div>';
					}
				}
				echo '</div>'; // tourmaster-my-booking-single-billing-detail-column
			}
			echo '</div>'; // tourmaster-item-rvpdl
			
			// traveller info
			$enable_traveller_info = tourmaster_get_option('room_general', 'required-guest-info', 'enable');

			if( $enable_traveller_info == 'enable' || !empty($contact_detail['guest_first_name']) ){
				echo '<div class="tourmaster-my-booking-single-traveller-info" >';
				echo '<h3 class="tourmaster-my-booking-single-title">';
				echo esc_html__('Traveller Info', 'tourmaster');
				echo tourmaster_order_edit_text('edit-traveller');
				echo tourmaster_lightbox_content(array(
					'id' => 'edit-traveller',
					'title' => esc_html__('Traveller', 'tourmaster'),
					'content' => tourmaster_room_order_edit_form($_GET['single'], 'traveller', $result)
				));	
				echo '</h3>';
				
				if( !empty($contact_detail['guest_first_name']) ){
					$guest_fields = tourmaster_get_option('room_general', 'additional-guest-fields', array());
					if( !empty($guest_fields) ){
						$guest_fields = tourmaster_read_custom_fields($guest_fields);
					}

					for( $i = 0; $i < sizeof($booking_details); $i++ ){
						$booking_detail = $booking_details[$i]; 
						for( $j = 0; $j < intval($booking_detail['room_amount']); $j++ ){
							echo '<div class="tourmaster-my-booking-single-field clearfix" >';
							if( intval($booking_detail['room_amount']) > 1 ){
								echo '<span class="tourmaster-head">' . sprintf(esc_html__('%s : Room %d', 'tourmaster'), get_the_title($booking_detail['room_id']), $j + 1) . '</span> ';
							}else{					
								echo '<span class="tourmaster-head">' . get_the_title($booking_detail['room_id']) . '</span> ';
							}
							echo '</div>';
							for( $k = 0; $k < sizeof($contact_detail['guest_first_name'][$i][$j]); $k++ ){
								if( !empty($contact_detail['guest_first_name'][$i][$j][$k]) || !empty($contact_detail['guest_first_name'][$i][$j][$k]) ){
									echo '<div class="tourmaster-my-booking-single-field clearfix" >';
									echo '<span class="tourmaster-head">' . sprintf(esc_html__('Guest %d:', 'tourmaster'), ($k+1)) . '</span> ';
									echo '<span class="tourmaster-tail">';
									echo $contact_detail['guest_first_name'][$i][$j][$k] . ' ' . $contact_detail['guest_last_name'][$i][$j][$k];
									foreach( $guest_fields as $field ){
										if( !empty($contact_detail['traveller_' . $field['slug']][$i][$j][$k]) ){
											echo '<br>' . $field['title'] . ' ' . $contact_detail['traveller_' . $field['slug']][$i][$j][$k];
										}
									}
									echo '</span>';
									echo '</div>';				
								}
							}
			
						}
					}
				}

				echo '</div>'; // tourmaster-my-booking-single-traveller-info
			}
			
			// price breakdown
			$price_breakdowns = empty($result->price_breakdown)? array(): json_decode($result->price_breakdown, true);
			if( !empty($price_breakdowns) ){
				echo '<div class="tourmaster-my-booking-single-price-breakdown" >';
				echo '<h3 class="tourmaster-my-booking-single-title">';
				echo esc_html__('Price Breakdown', 'tourmaster');
				echo tourmaster_order_edit_text('edit-price');
				echo tourmaster_lightbox_content(array(
					'id' => 'edit-price',
					'title' => esc_html__('Price', 'tourmaster'),
					'content' => tourmaster_room_order_edit_form($_GET['single'], 'price', $result)
				));	
				echo '</h3>';
				echo tourmaster_get_room_booking_price_breakdown($booking_details, $price_breakdowns);
				echo '</div>'; // tourmaster-my-booking-single-traveller-info
			}

			// echo '<a class="tourmaster-button tourmaster-resend-invoice" href="' . esc_url(add_query_arg(array('action'=>'send-invoice'))) . '" >' . esc_html__('Resend Invoice', 'tourmaster') . '</a>';
			
			echo '</div>'; // tourmaster-my-booking-single-content

			tourmaster_reset_currency();

		}
	}