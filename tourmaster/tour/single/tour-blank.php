<?php
	/**
	 * The template for displaying single tour posttype
	 */

	// calculate view count before printing the content
	$view_count = get_post_meta(get_the_ID(), 'tourmaster-view-count', true);
	$view_count = empty($view_count)? 0: intval($view_count);
	if( empty($_COOKIE['tourmaster-tour-' . get_the_ID()]) ){
		$view_count = $view_count + 1;
		update_post_meta(get_the_ID(), 'tourmaster-view-count', $view_count);
		setcookie('tourmaster-tour-' . get_the_ID(), 1, time() + 86400);
	}

	if( !empty($_POST['tour_temp']) ){
		$temp_data = tourmaster_process_post_data($_POST['tour_temp']);
		$temp_data = json_decode($temp_data, true);
		unset($temp_data['tour-id']);
	}

get_header();

	$tour_style = new tourmaster_tour_style();
	$tour_option = tourmaster_get_post_meta(get_the_ID(), 'tourmaster-tour-option');
	$tour_option['form-settings'] = empty($tour_option['form-settings'])? 'booking': $tour_option['form-settings'];

	echo '<div class="tourmaster-page-wrapper tourmaster-tour-style-blank" id="tourmaster-page-wrapper" >';
	
	// tour schema / structure data
	$enable_schema = tourmaster_get_option('general', 'enable-tour-schema', 'enable');
	if( $enable_schema == 'enable' ){
		$schema = array(
			'@context' => 'http://schema.org',
			'@type' => 'Product',
			'name' => get_the_title(),
			'productID' => 'tour-' . get_the_ID(),
			'brand' => get_bloginfo('name'),
			'sku' => '1',
			'url' => get_permalink(),
			'description' => get_the_excerpt(),
		);

		$tour_price = get_post_meta(get_the_ID(), 'tourmaster-tour-price', true);
		if( !empty($tour_price) ){
			$schema['offers'] = array(
				'@type' => 'Offer',
				'url' => get_permalink(),
				'price' => $tour_price,
				'priceValidUntil' => date('Y-01-01', strtotime('+365 day')),
				'availability' => 'http://schema.org/InStock'
			);

			$currency = tourmaster_get_option('general', 'tour-schema-price-currency', '');
			if( !empty($currency) ){
				$schema['offers']['priceCurrency'] = $currency;
			}

			$price_range = get_post_meta(get_the_ID(), 'tourmaster-tour-price-range', true);
			if( !empty($price_range) ){
				$schema['offers']['priceRange'] = $price_range;
			}
		}		

		$feature_image = get_post_thumbnail_id();
		if( !empty($feature_image) ){
			$schema['image'] = tourmaster_get_image_url($feature_image, 'full');
		}

		$tour_rating = get_post_meta(get_the_ID(), 'tourmaster-tour-rating', true);
		if( !empty($tour_rating['reviewer']) ){
			$rating_value = intval($tour_rating['score']) / (2 * intval($tour_rating['reviewer']));
			$schema['AggregateRating'] = array(
				array(
					'@type' => 'AggregateRating',
					'ratingValue' => number_format($rating_value, 2),
					'reviewCount' => $tour_rating['reviewer']
				),
			);
		}

		// review
		$review_args = array(
			'review_tour_id' => get_the_ID(), 
			'review_score' => 'IS NOT NULL',
			'order_status' => array(
				'hide-prefix' => true,
				'custom' => ' (order_status IS NULL OR order_status != \'cancel\') '
			)
		);
		$review = tourmaster_get_booking_data($review_args, array(
			'only-review' => true,
			'num-fetch' => 1,
			'paged' => 1,
			'orderby' => 'review_date',
			'order' => 'desc',
			'single' => true
		));
		if( !empty($review) ){
			$reviewer_name = '';
			if( !empty($review->user_id) ){
				$reviewer_name = tourmaster_get_user_meta($review->user_id);
			}else if( !empty($review->reviewer_name) ){
				$reviewer_name = $review->reviewer_name;
			}

			$schema['review'] = array(
				'@type' => 'Review',
				'reviewRating' => array(
					'@type' => 'Rating',
					'ratingValue' => number_format(($review->review_score / 2), 2)
				),
				'name' => $reviewer_name,
				'author' => array(
					'@type' => 'Person',
					'name' => $reviewer_name
				),
				'datePublished' => $review->review_date,
				'reviewBody' => $review->review_description
			);
		}

		echo '<script type="application/ld+json">';
		echo json_encode($schema);
		echo '</script>';
	}


	////////////////////////////////////////////////////////////////////
	// content section
	////////////////////////////////////////////////////////////////////
	echo '<div class="tourmaster-template-wrapper" >';

	echo '<div class="tourmaster-single-tour-content-wrap" >';
	global $post;
	while( have_posts() ){ the_post();

		if( empty($tour_option['show-wordpress-editor-content']) || $tour_option['show-wordpress-editor-content'] == 'enable' ){
			ob_start();
			the_content();
			$content = ob_get_contents();
			ob_end_clean();

			if( !empty($content) ){
				echo '<div class="tourmaster-container" >';
				echo '<div class="tourmaster-page-content tourmaster-item-pdlr" >';
				echo '<div class="tourmaster-single-main-content" >' . $content . '</div>'; // tourmaster-single-main-content
				echo '</div>'; // tourmaster-page-content
				echo '</div>'; // tourmaster-container
			}
		}
	}

	if( !post_password_required() ){
		do_action('gdlr_core_print_page_builder');
	}

	$mobile_read_more = tourmaster_get_option('general', 'mobile-content-read-more', 'enable');
	if( $mobile_read_more == 'enable' ){
		echo '<div class="tourmaster-single-tour-read-more-gradient" ></div>';
		echo '<div class="tourmaster-single-tour-read-more-wrap" >';
		echo '<div class="tourmaster-container" >';
		echo '<a class="tourmaster-button tourmaster-item-mglr" href="#" >' . esc_html__('Read More', 'tourmaster') . '</a>';
		echo '</div>';
		echo '</div>';
	}
	echo '</div>'; // tourmaster-single-tour-content-wrap

	////////////////////////////////////////////////////////////////////
	// related tour section
	////////////////////////////////////////////////////////////////////
	$related_tour = tourmaster_get_option('general', 'enable-single-related-tour', 'enable');

	if( $related_tour == 'enable' ){

		$related_tour_args = apply_filters('tourmaster_single_related_tour_args', array(
			'tour-style' => tourmaster_get_option('general', 'single-related-tour-style', 'grid'),
			'grid-style' => tourmaster_get_option('general', 'single-related-tour-grid-style', 'style-2'),
			'thumbnail-size' => tourmaster_get_option('general', 'single-related-tour-thumbnail-size', 'large'),
			'excerpt' => tourmaster_get_option('general', 'single-related-tour-excerpt', 'none'),
			'excerpt-number' => tourmaster_get_option('general', 'single-related-tour-excerpt-number', '20'),
			'column-size' => tourmaster_get_option('general', 'single-related-tour-column-size', '30'),
			'price-position' => tourmaster_get_option('general', 'single-related-tour-price-position', 'right-title'),
			'tour-rating' => tourmaster_get_option('general', 'single-related-tour-rating', 'enable'),
			'tour-info' => tourmaster_get_option('general', 'single-related-tour-info', ''),
		));

		// query related portfolio
		$args = array('post_type' => 'tour', 'suppress_filters' => false);
		$args['posts_per_page'] = tourmaster_get_option('general', 'single-related-tour-num-fetch', '2');
		$args['post__not_in'] = array(get_the_ID());

		$related_terms = get_the_terms(get_the_ID(), 'tour_tag');
		$related_tags = array();
		if( !empty($related_terms) ){
			foreach( $related_terms as $term ){
				$related_tags[] = $term->term_id;
			}
			$args['tax_query'] = array(array('terms'=>$related_tags, 'taxonomy'=>'tour_tag', 'field'=>'id'));
		} 
		$query = new WP_Query($args);

		// print item
		if( $query->have_posts() ){

			echo '<div class="tourmaster-single-related-tour tourmaster-tour-item tourmaster-style-' . esc_attr($related_tour_args['tour-style']) . '">';
			echo '<div class="tourmaster-single-related-tour-container tourmaster-container">';
			echo '<h3 class="tourmaster-single-related-tour-title tourmaster-item-pdlr">' . esc_html__('Related Tours', 'tourmaster') . '</h3>';

			$column_sum = 0;
			$no_space = in_array($related_tour_args['tour-style'], array('grid-no-space', 'modern-no-space'))? 'yes': 'no';
			if( strpos($related_tour_args['tour-style'], 'with-frame') !== false ){
				$related_tour_args['with-frame'] = 'enable';
				$related_tour_args['tour-style'] = str_replace('-with-frame', '', $related_tour_args['tour-style']);
			}else{
				$related_tour_args['with-frame'] = 'disable';
			}

			echo '<div class="tourmaster-tour-item-holder clearfix ' . ($no_space == 'yes'? ' tourmaster-item-pdlr': '') . '" >';
			while( $query->have_posts() ){ $query->the_post();

				$additional_class  = ' tourmaster-column-' . $related_tour_args['column-size'];
				$additional_class .= ($no_space == 'yes')? '': ' tourmaster-item-pdlr';
				$additional_class .= in_array($related_tour_args['tour-style'], array('modern'))? ' tourmaster-item-mgb': '';

				if( $column_sum == 0 || $column_sum + intval($related_tour_args['column-size']) > 60 ){
					$column_sum = intval($related_tour_args['column-size']);
					$additional_class .= ' tourmaster-column-first';
				}else{
					$column_sum += intval($related_tour_args['column-size']);
				}
				echo '<div class="gdlr-core-item-list ' . esc_attr($additional_class) . '" >';
				echo $tour_style->get_content($related_tour_args);
				echo '</div>';
			}
			wp_reset_postdata();

			echo '</div>'; // tourmaster-tour-item-holder

			echo '</div>'; // tourmaster-container 
			echo '</div>'; // tourmaster-single-related-tour
		}
	}

	////////////////////////////////////////////////////////////////////
	// review section
	////////////////////////////////////////////////////////////////////
	if( empty($tour_option['enable-review']) || $tour_option['enable-review'] == 'enable' ){
		$review_num_fetch = apply_filters('tourmaster_review_num_fetch', 5);
		$review_args = array(
			'review_tour_id' => get_the_ID(), 
			'review_score' => 'IS NOT NULL',
			'order_status' => array(
				'hide-prefix' => true,
				'custom' => ' (order_status IS NULL OR order_status != \'cancel\') '
			)
		);
		$results = tourmaster_get_booking_data($review_args, array(
			'only-review' => true,
			'num-fetch' => $review_num_fetch,
			'paged' => 1,
			'orderby' => 'review_date',
			'order' => 'desc'
		));
		
		if( !empty($results) ){
			$max_num_page = intval(tourmaster_get_booking_data($review_args, array('only-review' => true), 'COUNT(*)')) / $review_num_fetch;

			echo '<div class="tourmaster-single-review-container tourmaster-container" >';
			echo '<div class="tourmaster-single-review-item tourmaster-item-pdlr" >';
			echo '<div class="tourmaster-single-review" id="tourmaster-single-review" >';

			echo '<div class="tourmaster-single-review-head clearfix" >';
			echo '<div class="tourmaster-single-review-head-info clearfix" >';
			echo $tour_style->get_rating('plain');

			echo '<div class="tourmaster-single-review-filter" id="tourmaster-single-review-filter" >';
			echo '<div class="tourmaster-single-review-sort-by" >';
			echo '<span class="tourmaster-head" >' . esc_html__('Sort By:', 'tourmaster') . '</span>';
			echo '<span class="tourmaster-sort-by-field" data-sort-by="rating" >' . esc_html__('Rating', 'tourmaster') . '</span>';
			echo '<span class="tourmaster-sort-by-field tourmaster-active" data-sort-by="date" >' . esc_html__('Date', 'tourmaster') . '</span>';
			echo '</div>'; // tourmaster-single-review-sort-by
			echo '<div class="tourmaster-single-review-filter-by tourmaster-form-field tourmaster-with-border" >';
			echo '<div class="tourmaster-combobox-wrap" >';
			echo '<select id="tourmaster-filter-by" >';
			echo '<option value="" >' . esc_html__('Filter By', 'tourmaster'). '</option>';
			echo '<option value="solo" >' . esc_html__('Solo', 'tourmaster'). '</option>';
			echo '<option value="couple" >' . esc_html__('Couple', 'tourmaster'). '</option>';
			echo '<option value="family" >' . esc_html__('Family', 'tourmaster'). '</option>';
			echo '<option value="group" >' . esc_html__('Group', 'tourmaster'). '</option>';
			echo '</select>';
			echo '</div>'; // tourmaster-combobox-wrap
			echo '</div>'; // tourmaster-single-review-filter-by
			echo '</div>'; // tourmaster-single-review-filter
			echo '</div>'; // tourmaster-single-review-head-info
			echo '</div>'; // tourmaster-single-review-head

			echo '<div class="tourmaster-single-review-content" id="tourmaster-single-review-content" ';
			echo 'data-tour-id="' . esc_attr(get_the_ID()) . '" ';
			echo 'data-ajax-url="' . esc_attr(TOURMASTER_AJAX_URL) . '" >';
			echo tourmaster_get_review_content_list($results);

			echo tourmaster_get_review_content_pagination($max_num_page);
			echo '</div>'; // tourmaster-single-review-content
			echo '</div>'; // tourmaster-single-review
			echo '</div>'; // tourmaster-single-review-item
			echo '</div>'; // tourmaster-single-review-container
		} 
	}

	echo '</div>'; // tourmaster-template-wrapper

	echo '</div>'; // tourmaster-page-wrapper

	// urgent message
	if( empty($_COOKIE['tourmaster-urgency-message']) && !empty($tour_option['enable-urgency-message']) && $tour_option['enable-urgency-message'] == 'enable' ){
		$urgency_message_number = 0;
		if( !empty($tour_option['real-urgency-message']) && $tour_option['real-urgency-message'] == 'disable' ){
			$urgency_message_number = rand(intval($tour_option['urgency-message-number-from']), intval($tour_option['urgency-message-number-to']));
		}else{
			$ip_list = get_post_meta(get_the_ID(), 'tourmaster-tour-ip-list', true);
			$ip_list = empty($ip_list)? array(): $ip_list;

			$client_ip = tourmaster_get_client_ip();
			$ip_list[$client_ip] = strtotime('now');

			// remove the user which longer than 1 hour
			$current_time = strtotime('now');
			foreach( $ip_list as $client_ip => $ttl ){
				if( $current_time > $ttl + 3600 ){
					unset($ip_list[$client_ip]);
				}
			}

			$urgency_message_number = sizeof($ip_list);
			update_post_meta(get_the_ID(), 'tourmaster-tour-ip-list', $ip_list);
		}

		echo '<div class="tourmaster-urgency-message" id="tourmaster-urgency-message" data-expire="86400" >';
		echo '<i class="tourmaster-urgency-message-icon fa fa-users" ></i>';
		echo '<div class="tourmaster-urgency-message-text" >';
		echo sprintf(esc_html__('%d travellers are considering this tour right now!', 'tourmaster'), $urgency_message_number);
		echo '</div>';
		echo '</div>';
	}

get_footer(); 

?>