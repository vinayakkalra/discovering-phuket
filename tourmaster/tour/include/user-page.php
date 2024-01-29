<?php

    add_filter('tourmaster_user_nav_list', 'tourmaster_tour_user_nav_list');
    if( !function_exists('tourmaster_tour_user_nav_list') ){
		function tourmaster_tour_user_nav_list($nav_list){

            $nav_list = $nav_list + array(
				'tour-booking-title' => array(
					'type' => 'title',
					'title' => esc_html__('Tour Booking', 'tourmaster')
				),
				'my-booking' => array(
					'title' => esc_html__('My Bookings', 'tourmaster'),
					'icon' => 'icon_document_alt'
				),
				'invoices' => array(
					'title' => esc_html__('Invoices', 'tourmaster'),
					'icon' => 'icon_wallet'
				),
				'reviews' => array(
					'title' => esc_html__('Reviews', 'tourmaster'),
					'icon' => 'fa fa-star'
				),
				'wish-list' => array(
					'title' => esc_html__('Wish List', 'tourmaster'),
					'icon' => 'fa fa-heart-o',
					'top-bar' => true,
				)
            );

            return $nav_list;
        } // tourmaster_tour_user_nav_list
    }

	add_filter('tourmaster_user_content_template', 'tourmaster_tour_user_content_template', 10, 2);
	if( !function_exists('tourmaster_tour_user_content_template') ){
		function tourmaster_tour_user_content_template($template, $page_type){

			if( in_array($page_type, array('invoices', 'invoices-paid', 'invoices-single', 'my-booking', 'my-booking-single', 'reviews', 'wish-list')) ){
				$template = TOURMASTER_LOCAL . '/tour/single/user/' . $page_type . '.php';
			}
			
			return $template;

		} // tourmaster_tour_dashboard_block
	}

	add_action('tourmaster_dashboard_block', 'tourmaster_tour_dashboard_block');
	if( !function_exists('tourmaster_tour_dashboard_block') ){
		function tourmaster_tour_dashboard_block(){
			
			/* dashboard page content */
			global $current_user;

			///////////////////////
			// my booking section
			///////////////////////

			// query 
			$conditions = array('user_id' => $current_user->data->ID, 'order_status'=> array('condition'=>'!=', 'value'=>'cancel'));
			$results = tourmaster_get_booking_data($conditions, array('paged'=>1, 'num-fetch'=>5));

			if( !empty($results) ){	
				$statuses = array(
					'all' => esc_html__('All', 'tourmaster'),
					'pending' => esc_html__('Pending', 'tourmaster'),
					'approved' => esc_html__('Approved', 'tourmaster'),
					'receipt-submitted' => esc_html__('Receipt Submitted', 'tourmaster'),
					'online-paid' => esc_html__('Online Paid', 'tourmaster'),
					'deposit-paid' => esc_html__('Deposit Paid', 'tourmaster'),
					'departed' => esc_html__('Departed', 'tourmaster'),
					'rejected' => esc_html__('Rejected', 'tourmaster'),
					'wait-for-approval' => esc_html__('Wait For Approval', 'tourmaster'),
				);

				tourmaster_user_content_block_start(array(
					'title' => esc_html__('Current Booking', 'tourmaster'),
					'title-link-text' => esc_html__('View All Bookings', 'tourmaster'),
					'title-link' => tourmaster_get_template_url('user', array('page_type'=>'my-booking'))
				));

				echo '<table class="tourmaster-my-booking-table tourmaster-table" >';
				tourmaster_get_table_head(array(
					esc_html__('Tour Name', 'tourmaster'),
					esc_html__('Travel Date', 'tourmaster'),
					esc_html__('Total', 'tourmaster'),
					esc_html__('Payment Status', 'tourmaster'),
				));
				foreach( $results as $result ){

					tourmaster_set_currency($result->currency);

					$single_booking_url = add_query_arg(array(
						'page_type' => 'my-booking',
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
					if( in_array($result->order_status, array('pending', 'receipt-submitted', 'rejected', 'deposit-paid')) ){
						$status .= '<a class="tourmaster-my-booking-action fa fa-dollar" href="' . esc_url($single_booking_url) . '" ></a>';
					}
					if( in_array($result->order_status, array('pending', 'receipt-submitted', 'rejected')) ){
						$status .= '<a class="tourmaster-my-booking-action fa fa-remove" href="' . add_query_arg(array('action'=>'remove', 'id'=>$result->id)) . '" ></a>';
					}

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
			}

			///////////////////////
			// review section
			///////////////////////
			$conditions = array(
				'user_id' => $current_user->data->ID,
				'order_status' => 'departed'
			);
			$results = tourmaster_get_booking_data($conditions, array('paged'=>1, 'num-fetch'=>5, 'with-review' => true));

			if( !empty($results) ){
				tourmaster_user_content_block_start(array(
					'title' => esc_html__('Tour Reviews', 'tourmaster'),
					'title-link-text' => esc_html__('View All Reviews', 'tourmaster'),
					'title-link' => tourmaster_get_template_url('user', array('page_type'=>'reviews'))
				));

				echo '<table class="tourmaster-user-review-table tourmaster-table" >';
				tourmaster_get_table_head(array(
					esc_html__('Tour Name', 'tourmaster'),
					esc_html__('Status', 'tourmaster'),
					esc_html__('Action', 'tourmaster'),
				));		
				foreach( $results as $result ){
					$title = get_the_title($result->tour_id);

					if( $result->review_score == '' ){
						$status  = '<span class="tourmaster-user-review-status tourmaster-status-pending" >';	
						$status .= esc_html__('Pending', 'tourmaster');
						$status .= '</span>';

						$action  = '<span class="tourmaster-user-review-action" data-tmlb="submit-review" >' . esc_html__('Submit Review', 'tourmaster') . '</span>';
						$action .= tourmaster_lightbox_content(array(
							'id' => 'submit-review',
							'title' => esc_html__('Submit Your Review', 'tourmaster'),
							'content' => tourmaster_get_review_form( $result )
						));
					}else{
						$status  = '<span class="tourmaster-user-review-status tourmaster-status-submitted" >';	
						$status .= esc_html__('Submitted', 'tourmaster');
						$status .= '</span>';

						$action  = '<span class="tourmaster-user-review-action" data-tmlb="view-review" >' . esc_html__('View Review', 'tourmaster') . '</span>';		
						$action .= tourmaster_lightbox_content(array(
							'id' => 'view-review',
							'title' => esc_html__('Your Review', 'tourmaster'),
							'content' => tourmaster_get_submitted_review( $result )
						));
					}

					tourmaster_get_table_content(array($title, $status, $action));
				}
				echo '</table>';

				tourmaster_user_content_block_end();
			}

		} // tourmaster_tour_dashboard_block
	}