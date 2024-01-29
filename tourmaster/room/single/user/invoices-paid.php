<?php
	echo '<div class="tourmaster-user-content-inner tourmaster-user-content-inner-invoices" >';
	tourmaster_get_user_breadcrumb();

	// filter
	$statuses = array(
		'all' => esc_html__('All', 'tourmaster'),
		'approved' => esc_html__('Approved', 'tourmaster'),
		'online-paid' => esc_html__('Online Paid', 'tourmaster'),
		// 'departed' => esc_html__('Departed', 'tourmaster'),
	);
	echo '<div class="tourmaster-my-booking-filter" >';
	foreach( $statuses as $status_slug => $status ){
		echo '<span class="tourmaster-sep">|</span>';
		echo '<a ';
		if( $status_slug == 'all' && (empty($_GET['status']) || $_GET['status'] == 'all') ){
			echo ' class="tourmaster-active" ';
		}else if( !empty($_GET['status']) && $_GET['status'] == $status_slug ){
			echo ' class="tourmaster-active" ';
		}
		echo 'href="' . esc_url(add_query_arg(array('status'=>$status_slug))) . '" >' . $status . '</a>';
	}
	echo '</div>'; // tourmaster-my-booking-filter

	// booking table block
	tourmaster_user_content_block_start();

	echo '<table class="tourmaster-my-booking-table tourmaster-table" >';

	tourmaster_get_table_head(array(
		esc_html__('Tour Name', 'tourmaster'),
		esc_html__('Travel Date', 'tourmaster'),
		esc_html__('Total', 'tourmaster'),
		esc_html__('Payment Status', 'tourmaster'),
	));

	// query 
	global $current_user;
	$conditions = array(
		'user_id' => $current_user->data->ID,
	);
	if( !empty($_GET['status']) && $_GET['status'] != 'all' && !empty($statuses[$_GET['status']]) ){
		$conditions['order_status'] = $_GET['status'];
	}else{
		$conditions['order_status'] = array(
			'custom' => " IN('approved', 'online-paid', 'departed') "
		);
	}
	// $conditions['order_status'] = array( 'custom' => ' IN (\'approved\', \'online-paid\') ' );
	$results = tourmaster_get_booking_data($conditions);

	foreach( $results as $result ){

		tourmaster_set_currency($result->currency);

		$single_booking_url = add_query_arg(array(
			'sub_page' => 'single',
			'id' => $result->id,
			'tour_id' => $result->tour_id
		));
		$title = '<a class="tourmaster-my-booking-title" href="' . esc_url($single_booking_url) . '" >' . get_the_title($result->tour_id) . '</a>';
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

		tourmaster_get_table_content(array(
			$title,
			tourmaster_date_format($result->travel_date),
			'<span class="tourmaster-my-booking-price" >' . tourmaster_money_format($result->total_price) . '</span>',
			$status
		));
	}

	tourmaster_reset_currency();

	echo '</table>';
	tourmaster_user_content_block_end();

	echo '</div>'; // tourmaster-user-content-inner