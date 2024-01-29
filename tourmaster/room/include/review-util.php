<?php
	/*	
	*	Utility function for uses
	*/

	// review form
	if( !function_exists('tourmaster_room_get_review_form') ){
		function tourmaster_room_get_review_form( $result, $is_admin = false, $value = array() ){
			ob_start();
?>

<?php if( $is_admin ){ ?>
<div class="tourmaster-review-form tourmaster-form-field tourmaster-with-border" >
	<?php if( $is_admin === true ){ ?>
		<div class="tourmaster-review-form-name" >
			<span class="tourmaster-head" ><?php echo esc_html__('Reviewer Name', 'tourmaster'); ?></span>
			<input type="text" name="review-name" />
		</div>
		<div class="tourmaster-review-form-email" >	
			<span class="tourmaster-head" ><?php echo esc_html__('Reviewer Email (For Gravatar Profile Picture)', 'tourmaster'); ?></span>
			<input type="text" name="review-email" />
		</div>
	<?php } ?>
<?php }else{ ?> 
<form class="tourmaster-review-form tourmaster-form-field tourmaster-with-border" method="POST" >
<?php } ?>
	<div class="tourmaster-review-form-description" >
		<div class="tourmaster-head" ><?php echo esc_html__('What do you say about this room? *', 'tourmaster'); ?></div>
		<textarea name="description" ><?php echo empty($value['description'])? '': esc_textarea($value['description']); ?></textarea>
	</div>
	<div class="tourmaster-review-form-rating-wrap" >
		<div class="tourmaster-head" ><?php echo esc_html__('Rate this room *', 'tourmaster'); ?></div>
		<div class="tourmaster-review-form-rating clearfix" >
		<?php
			$rating_value = empty($value['rating'])? 10: intval($value['rating']);

			for( $i = 1; $i <= 10; $i++ ){
				if( $i % 2 == 0 ){
					echo '<span class="tourmaster-rating-select" data-rating-score="' . esc_attr($i) . '" ></span>';
				}else{
					echo '<i class="tourmaster-rating-select ';
					if( $rating_value == $i ){
						echo 'fa fa-star-half-empty';
					}else if( $rating_value > $i ){
						echo 'fa fa-star';
					}else{
						echo 'fa fa-star-o';
					}
					echo '" data-rating-score="' . esc_attr($i) . '" ></i>';
				}
			}

			echo '<input type="hidden" name="rating" value="' . esc_attr($rating_value) . '" />';
		?>
		</div>
	</div>
<?php if( $is_admin ){ ?>	
	<div class="tourmaster-review-form-date" >	
		<span class="tourmaster-head" ><?php echo esc_html__('Published Date', 'tourmaster'); ?></span>
		<input type="text" class="tourmaster-html-option-datepicker" name="review-published-date" value="<?php
			if( !empty($value['published-date']) ){
				echo esc_attr(date('Y-m-d', strtotime($value['published-date'])));
			}
		?>" />
		<input type="hidden" name="room_id" value="<?php 
			$room_id = get_the_ID();
			if( empty($room_id) && !empty($value['room_id']) ){
				$room_id = $value['room_id'];
			} 
			echo esc_attr($room_id); 
		?>" />
		<?php if( $is_admin === true ){ ?>
			<input type="hidden" name="review_action" value="tourmaster_admin_add_review" />
		<?php }else{ ?>
			<input type="hidden" name="review_action" value="tourmaster_admin_edit_review" />
			<input type="hidden" name="review_id" value="<?php echo esc_attr($value['review-id']); ?>" />
		<?php } ?>
	</div>
	<input class="tourmaster-button tourmaster-submit-review" data-ajax-url="<?php echo esc_attr(TOURMASTER_AJAX_URL); ?>" type="button" value="<?php echo esc_html__('Submit Review', 'tourmaster'); ?>" />
</div>
<?php }else{ ?>
	<input type="hidden" name="review_id" value="<?php echo esc_attr($result->id); ?>" />
	<input type="hidden" name="order_id" value="<?php echo esc_attr($result->order_id); ?>" />
	<input class="tourmaster-button" type="submit" value="<?php echo esc_html__('Submit Review', 'tourmaster'); ?>" />
</form>
<?php }
			$ret = ob_get_contents();
			ob_end_clean();

			return $ret;
		}
	}

	////////////////////////////// admin review ////////////////////////////
	
	if( !function_exists('tourmaster_room_admin_add_review') ){
		function tourmaster_room_admin_add_review( $data = array() ){

			if( !empty($data['review-name']) && !empty($data['review-email']) && !empty($data['room_id']) && 
				!empty($data['review-published-date']) && !empty($data['description']) && !empty($data['rating']) ){

				if( is_email($data['review-email']) ){
					global $wpdb;

					$wpdb->insert("{$wpdb->prefix}tourmaster_room_review" ,array(
						'review_room_id' => $data['room_id'],
						'reviewer_name' => $data['review-name'],
						'reviewer_email' => $data['review-email'],
						'review_date' => $data['review-published-date'],
						'review_description' => $data['description'],
						'review_score' => $data['rating']
					), array(
						'%d', '%s', '%s', '%s', '%s', '%d'
					));

					tourmaster_room_update_review_score($data['room_id']);

					$ret = json_encode(array(
						'status' => 'success',
						'message' => esc_html__('A review is successfully added.', 'tourmaster')
					));
				}else{
					$ret = json_encode(array(
						'status' => 'failed',
						'message' => esc_html__('Invalid Email, please try again.', 'tourmaster')
					));
				}
			}else{
				$ret = json_encode(array(
					'status' => 'failed',
					'message' => esc_html__('Please fill all required fields.', 'tourmaster')
				));
			}

			return $ret;
		}
	}

	// review content
	if( !function_exists('tourmaster_room_get_edit_admin_review_item') ){
		function tourmaster_room_get_edit_admin_review_item( $review_id ){

			global $wpdb;
			$sql  = "SELECT * FROM {$wpdb->prefix}tourmaster_room_review ";
			$sql .= $wpdb->prepare("WHERE review_id = %d ", $review_id);
			$result = $wpdb->get_row($sql);

			$value = array(
				'room_id' => $result->review_room_id,
				'review-id' => $result->review_id,
				'description' => $result->review_description,
				'rating' => $result->review_score,
				'published-date' => $result->review_date,
			);

			return array(
				'status' => 'success',
				'content' => tourmaster_admin_lightbox_content(array(
					'title' => esc_html__('Edit Review', 'tourmaster'),
					'content' => '<div class="tourmaster-html-option-admin-review" >' . tourmaster_room_get_review_form(null, 'edit', $value) . '</div>'
				))
			);
			
		}
	}
			
	if( !function_exists('tourmaster_room_admin_edit_review') ){
		function tourmaster_room_admin_edit_review( $post_data ){
			if( !empty($post_data['review-published-date']) && !empty($post_data['description']) && !empty($post_data['rating']) ){
				global $wpdb;

				$updated = $wpdb->update("{$wpdb->prefix}tourmaster_room_review", array(
					'review_score' => $post_data['rating'],
					'review_description' => $post_data['description'],
					'review_date' => $post_data['review-published-date'] . ' 00:00:00'
				), array('review_id' => $post_data['review_id']), array('%d', '%s', '%s'), array('%d'));

				tourmaster_room_update_review_score($data['room_id']);
				
				if( $updated !== false ){
					$ret = array(
						'status' => 'success',
					);
				}else{
					$ret = array(
						'status' => 'failed',
						'message' => esc_html__('Cannot update review data, please refresh the page and try this again.', 'tourmaster')
					);
				}
			}else{
				$ret = array(
					'status' => 'failed',
					'message' => esc_html__('Please fill all required fields.', 'tourmaster')
				);
			}

			return $ret;
		}
	}

	// review content
	if( !function_exists('tourmaster_room_get_admin_review_item') ){
		function tourmaster_room_get_admin_review_item( $room_id, $paged = 1 ){

			global $wpdb;

			$review_num_fetch = apply_filters('tourmaster_review_num_fetch', 5);

			$sql  = "SELECT * FROM {$wpdb->prefix}tourmaster_room_review ";
			$sql .= $wpdb->prepare("WHERE review_room_id = %d ", $room_id);
			$sql .= "AND order_id IS NULL ";
			$sql .= tourmaster_get_sql_page_part($paged, $review_num_fetch);
			$results = $wpdb->get_results($sql);

			if( !empty($results) ){

				$sql  = "SELECT COUNT(*) FROM {$wpdb->prefix}tourmaster_room_review ";
				$sql .= $wpdb->prepare("WHERE review_room_id = %d ", $room_id);
				$sql .= "AND order_id IS NULL ";
				$max_num_page = $wpdb->get_var($sql) / $review_num_fetch;

				$ret = array(
					'status' => 'success', 
					'content' => tourmaster_room_get_review_content_list($results, true) .
						tourmaster_room_get_review_content_pagination($max_num_page, $paged)
				);
			}else{
				$ret = array(
					'status' => 'failed',
					'message' => esc_html__('No result found, please refresh the page to try again.', 'tourmaster')
				);
			}

			return $ret;

		}
	}

	// review content
	if( !function_exists('tourmaster_room_remove_review_data') ){
		function tourmaster_room_remove_review_data( $review_id, $room_id ){
			
			global $wpdb;

			$wpdb->delete("{$wpdb->prefix}tourmaster_room_review", array('review_id' => $review_id), array('%d'));

			tourmaster_room_update_review_score($room_id);
		}
	}

	// review content
	if( !function_exists('tourmaster_room_get_review_content_list') ){
		function tourmaster_room_get_review_content_list( $query, $editable = false ){
			
			$ret  = '';
			foreach( $query as $result ){
				
				$user_id = '';
				$avatar = '';
				if( !empty($result->user_id) ){
					$user_id = $result->user_id;
					$avatar = get_the_author_meta('tourmaster-user-avatar', $user_id);
				}else if( !empty($result->reviewer_email) ){
					$user_id = $result->reviewer_email;
				}

				$reviewer_name = '';
				if( !empty($result->user_id) ){
					$reviewer_name = tourmaster_get_user_meta($result->user_id);
				}else if( !empty($result->reviewer_name) ){
					$reviewer_name = $result->reviewer_name;
				}

				$ret .= '<div class="tourmaster-single-review-content-item clearfix" >';
				$ret .= '<div class="tourmaster-single-review-user clearfix" >';
				if( !empty($user_id) ){
					$ret .= '<div class="tourmaster-single-review-avatar tourmaster-media-image" >';
					if( !empty($avatar['thumbnail']) ){
						$ret .= '<img src="' . esc_url($avatar['thumbnail']) . '" alt="profile-image" />';
					}else if( !empty($avatar['file_url']) ){
						$ret .= '<img src="' . esc_url($avatar['file_url']) . '" alt="profile-image" />';
					}else{
						$ret .= get_avatar($user_id, 90);
					}
					$ret .= '</div>'; 
				}
				$ret .= '<h4 class="tourmaster-single-review-user-name" >' . $reviewer_name . '</h4>';
				$ret .= '</div>'; // tourmaster-single-review-user

				$ret .= '<div class="tourmaster-single-review-detail" >';
				if( !empty($result->review_description) ){
					$ret .= '<div class="tourmaster-single-review-detail-description" >' . tourmaster_content_filter($result->review_description) . '</div>';
				}
				$ret .= '<div class="tourmaster-single-review-detail-rating" >' . tourmaster_get_rating($result->review_score) . '</div>';
				$ret .= '<div class="tourmaster-single-review-detail-date" >' . tourmaster_date_format($result->review_date) . '</div>';
				
				if( $editable ){
					$ret .= '<div class="tourmaster-single-review-editable" >';
					$ret .= '<div class="tourmaster-single-review-edit" data-id="' . esc_attr($result->review_id) . '" ><i class="fa fa-edit" ></i>' . esc_html__('Edit', 'tourmaster') . '</div>';
					$ret .= '<div class="tourmaster-single-review-remove" data-id="' . esc_attr($result->review_id) . '" ><i class="fa fa-remove" ></i>' . esc_html__('Remove', 'tourmaster') . '</div>';
					$ret .= '</div>';
				}
				$ret .= '</div>'; // tourmaster-single-review-detail
				$ret .= '</div>'; // tourmaster-single-review-content-item
			}

			return $ret;
		} // tourmaster_get_review_content_list
	}
	if( !function_exists('tourmaster_room_get_review_content_pagination') ){
		function tourmaster_room_get_review_content_pagination( $max_num_page, $current_page = 1 ){

			$ret = '';
			if( !empty($max_num_page) && $max_num_page > 1 ){
				$ret .= '<div class="tourmaster-review-content-pagination" >';
				if( $current_page > 1 ){
					$ret .= '<span data-paged="' . esc_attr($current_page-1) . '" ><i class="fa fa-angle-left" ></i></span>';
				}
				for( $i = 1; $i <= $max_num_page; $i++ ){
					if( $i == $current_page ){
						$ret .= '<span class="tourmaster-active" >' . $i . '</span>';
					}else{
						$ret .= '<span data-paged="' . esc_attr($i) . '" >' . $i . '</span>';
					}
				}
				if( $current_page < $max_num_page ){
					$ret .= '<span data-paged="' . esc_attr($current_page+1) . '" ><i class="fa fa-angle-right" ></i></span>';
				}
				$ret .= '</div>';
			}

			return $ret;
		} // tourmaster_get_review_content_pagination
	}

	////////////////////////////// customer review ////////////////////////////

	// ajax review list
	add_action('wp_ajax_get_single_room_review', 'tourmaster_get_single_room_review');
	add_action('wp_ajax_nopriv_get_single_room_review', 'tourmaster_get_single_room_review');
	if( !function_exists('tourmaster_get_single_room_review') ){
		function tourmaster_get_single_room_review(){

			// sort_by
			// filter_by
			if( !empty($_POST['room_id']) ){

				$review_num_fetch = apply_filters('tourmaster_review_num_fetch', 5);
				$paged = (empty($_POST['paged'])? '1': $_POST['paged']);

				global $wpdb;
				$sql  = "SELECT * from {$wpdb->prefix}tourmaster_room_review ";
				$sql .= $wpdb->prepare("WHERE review_room_id = %d ", $_POST['room_id']);
				if( empty($_POST['sort_by']) || $_POST['sort_by'] == 'date' ){
					$sql .= "ORDER BY review_date DESC ";
				}else if( $_POST['sort_by'] == 'rating' ){
					$sql .= "ORDER BY review_score DESC ";
				}
				$sql .= tourmaster_get_sql_page_part($paged, $review_num_fetch);
				$results = $wpdb->get_results($sql);

				$sql  = "SELECT COUNT(*) from {$wpdb->prefix}tourmaster_room_review ";
				$sql .= $wpdb->prepare("WHERE review_room_id = %d ", $_POST['room_id']);
				$max_num_page = intval($wpdb->get_var($sql)) / $review_num_fetch;

				die(json_encode(array(
					'content' => '<div>' . $temp .'</div>' . tourmaster_room_get_review_content_list($results) .
						tourmaster_room_get_review_content_list($max_num_page, $paged)
				)));
			}

			die(json_encode(array()));

		} // tourmaster_get_single_tour_review
	}

	if( !function_exists('tourmaster_room_get_submitted_review') ){
		function tourmaster_room_get_submitted_review( $result ){
			ob_start();
?>
<div class="tourmaster-review-form" >
	<?php if( !empty($result->review_description) ){ ?>
		<div class="tourmaster-review-form-description" >
			<div class="tourmaster-head" ><?php echo esc_html__('What do you say about this tour? *', 'tourmaster'); ?></div>
			<div class="tourmaster-tail"><?php echo tourmaster_content_filter($result->review_description); ?></div>
		</div>
	<?php } ?>
	<?php if( !empty($result->review_score) ){ ?>
		<div class="tourmaster-review-form-rating-wrap" >
			<div class="tourmaster-head" ><?php echo esc_html__('Rate this tour *', 'tourmaster'); ?></div>
			<div class="tourmaster-review-form-rating clearfix" >	
			<?php
				$score = intval($result->review_score);
				echo tourmaster_get_rating($score);
			?>
			</div>
		</div>
	<?php } ?>
</div>
<?php			
			$ret = ob_get_contents();
			ob_end_clean();

			return $ret;
		}
	} // tourmaster_get_submitted_review

	if( !function_exists('tourmaster_room_update_review_score') ){
		function tourmaster_room_update_review_score( $room_id ){

			global $wpdb;

			$sql  = "SELECT * from {$wpdb->prefix}tourmaster_room_review ";
			$sql .= $wpdb->prepare("WHERE review_room_id = %d ", $room_id);
			$results = $wpdb->get_results($sql);

		    $review_score = 0;
		    $review_number = 0;
		    foreach( $results as $result ){
		    	if( $result->review_score != '' ){
		    		$review_score += $result->review_score;
		    		$review_number++;
		    	}
		    }

		    update_post_meta($room_id, 'tourmaster-room-rating', array(
		    	'score' => $review_score,
		    	'reviewer' => $review_number
		    ));

		    if( $review_number > 0 ){
		    	update_post_meta($room_id, 'tourmaster-room-rating-score', $review_score / $review_number);
		    }else{
		    	delete_post_meta($room_id, 'tourmaster-room-rating-score');
		    }

		} // tourmaster_update_review_score
	}