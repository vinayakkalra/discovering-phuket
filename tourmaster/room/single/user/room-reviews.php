<?php
	// print the page content
	echo '<div class="tourmaster-user-content-inner tourmaster-user-content-inner-review" >';
	tourmaster_get_user_breadcrumb();

	// booking table block
	tourmaster_user_content_block_start();

	// query 
	global $wpdb, $current_user;
	$sql  = "SELECT * FROM {$wpdb->prefix}tourmaster_room_order AS t1 ";
	$sql .= "LEFT JOIN {$wpdb->prefix}tourmaster_room_review AS t2 ";
	$sql .= "ON t1.id = t2.order_id ";
	$sql .= $wpdb->prepare("WHERE user_id = %d ", $current_user->data->ID);
	$sql .= "AND order_status IN ('online-paid', 'approved', 'departed') ";
	$sql .= "GROUP BY id ";
	$sql .= "ORDER BY id DESC ";
	$results = $wpdb->get_results($sql);
	

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

	echo '</div>'; // tourmaster-user-content-inner