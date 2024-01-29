<?php
	/*	
	*	Utility function for uses
	*/
	
	if( !function_exists('tourmaster_get_currency_rate') ){
		function tourmaster_get_currency_rate($main_currency){
			$main_currency = strtolower($main_currency);

			if( !empty($main_currency) ){
				$currencies = get_transient('tourmaster_currency_rate_' . $main_currency);
				if( empty($currencies) ){
					$currencies = tourmaster_update_currency_rate($main_currency);
				}
				if( empty($currencies) ){
					$currencies = get_option('tourmaster_currency_rate_' . $main_currency, array());
				}

				return $currencies;
			}else{
				return array();
			}
			
		}
	}

	if( !function_exists('tourmaster_update_currency_rate') ){
		function tourmaster_update_currency_rate($main_currency){

			$ret = array();
			$feed_url = 'https://www.floatrates.com/daily/' . trim($main_currency) . '.json';

			$response = wp_remote_get($feed_url);
			if ( is_array($response) && !is_wp_error($response) ){
				$currencies = json_decode($response['body'], true);
				foreach( $currencies as $code => $currency ){
					if( !empty($currency['rate']) ){
						$ret[$code] = $currency['rate'];
					}
				}

				if( !empty($ret) ){
					set_transient('tourmaster_currency_rate_' . $main_currency, $ret, 86400); // 1day
					update_option('tourmaster_currency_rate_' . $main_currency, $ret);
				}
			}

			return $ret;

		}
	}
	
	if( !function_exists('tourmaster_set_currency') ){
		function tourmaster_set_currency($data){
			global $tourmaster_currency;

			if( !is_array($data) ){
				$currency_data = json_decode($data, true);
				$tourmaster_currency = $currency_data;
			}else{
				$currency_data = $data;
			}

		}
	}
	if( !function_exists('tourmaster_reset_currency') ){
		function tourmaster_reset_currency(){ 
			global $tourmaster_main_currency, $tourmaster_currency;
			$tourmaster_currency = $tourmaster_main_currency;
		}
	}

	// init currency object
	add_action('init', 'tourmaster_init_currency');
	if( !function_exists('tourmaster_init_currency') ){
		function tourmaster_init_currency(){ 
			global $tourmaster_main_currency, $tourmaster_currency;

			$currencies = tourmaster_get_option('general', 'currencies', array());
			$main_currency = tourmaster_get_option('general', 'currency-code', 'usd');

			// conversion fee
			$conversion_fee = floatval(tourmaster_get_option('general', 'currency-conversion-fee', ''));
			if( !empty($conversion_fee) ){
				$conversion_fee = 1 + ($conversion_fee / 100);
			}else{
				$conversion_fee = 1;
			}
			
			// set cookie
			$current_currency = '';
			if( !empty($_GET['currency']) ){
				$current_currency = strtolower($_GET['currency']);
				setcookie('tourmaster-currency', $current_currency, 0, '/', COOKIE_DOMAIN, is_ssl(), false);

			}else if( !empty($_COOKIE['tourmaster-currency']) ){
				if( strtolower($_COOKIE['tourmaster-currency']) == strtolower($main_currency) ){
					setcookie('tourmaster-currency', '', time() - 86400);
				}else{
					$current_currency = strtolower($_COOKIE['tourmaster-currency']);
				}
			}

			// init currency object
			if( !empty($current_currency) && !empty($currencies) ){
				foreach( $currencies as $currency ){
					if( strtolower($currency['title']) == $current_currency ){
						if( !empty($currency['exchange-rate']) ){
							$tourmaster_currency = array(
								'currency-code' => $current_currency,
								'number-format' => $currency['money-format'],
								'exchange-rate' => floatval($currency['exchange-rate']) * $conversion_fee
							);
						}else{
							$currency_rates = tourmaster_get_currency_rate($main_currency);
							$tourmaster_currency = array(
								'currency-code' => $current_currency,
								'number-format' => $currency['money-format'],
								'exchange-rate' => floatval($currency_rates[$current_currency]) * $conversion_fee
							);
						}
						break;
					}
				}
			} 

			$tourmaster_main_currency = $tourmaster_currency;
		}
	}

	add_shortcode('tourmaster_currency_switcher', 'tourmaster_currency_switcher_shortcode');
	if( !function_exists('tourmaster_currency_switcher_shortcode') ){
		function tourmaster_currency_switcher_shortcode($atts){
			$atts = wp_parse_args($atts, array());

			$ret  = '<div class="tourmaster-currency-switcher-shortcode clearfix" >';
			$ret .= tourmaster_navigation_currency('', $atts);
			$ret .= '</div>';

			return $ret;
		}
	}

    add_filter('zurf_custom_main_menu_right', 'tourmaster_navigation_currency', 9);
    add_filter('hotale_custom_main_menu_right', 'tourmaster_navigation_currency', 9);
    add_filter('traveltour_custom_main_menu_right', 'tourmaster_navigation_currency', 9);
    if( !function_exists('tourmaster_navigation_currency') ){
		function tourmaster_navigation_currency($ret = '', $settings = array()){ 

			$currencies = tourmaster_get_option('general', 'currencies', array());
			$main_currency = tourmaster_get_option('general', 'currency-code', 'usd');
			$button = '';
			if( !empty($currencies) ){
				global $tourmaster_currency;
				if( !empty($tourmaster_currency) ){
					$current_currency = $tourmaster_currency['currency-code'];
				}else{
					$current_currency = $main_currency;
				}

				$button  = '<div class="tourmaster-currency-switcher" ' . tourmaster_esc_style(array(
					'background' => empty($settings['background-color'])? '': $settings['background-color']
				)) . ' >';
				$button .= '<span class="tourmaster-head" ' . tourmaster_esc_style(array(
					'color' => empty($settings['text-color'])? '': $settings['text-color']
				)) . ' ><span>' . strtoupper($current_currency) . '</span><i class="fa fa-sort-down" ></i></span>';
				$button .= '<div class="tourmaster-currency-switcher-inner" >';
				$button .= '<div class="tourmaster-currency-switcher-content" >';
				$button .= '<ul>';
				if( !empty($tourmaster_currency) ){
					$button .= '<li><a href="' . add_query_arg(array('currency' => strtolower($main_currency))) . '" >' . strtoupper($main_currency) . '</a></li>';
				}
				foreach( $currencies as $currency ){
					if( $current_currency == strtolower($currency['title']) ) continue;
					$button .= '<li><a href="' . add_query_arg(array('currency' => strtolower($currency['title']))) . '" >' . strtoupper($currency['title']) . '</a></li>';
				}
				$button .= '</ul>';
				$button .= '</div>'; // tourmaster-currency-switcher-content
				$button .= '</div>'; // tourmaster-currency-switcher-inner
				$button .= '</div>'; // tourmaster-currency-switcher
			}

            
            return $ret . $button;
        }
    }

	if( !function_exists('tourmaster_money_format') ){
		function tourmaster_money_format( $amount, $digit = -1 ){
			if( $digit == -1 ){
				$digit = tourmaster_get_option('general', 'price-breakdown-decimal-digit', '2');
			
			// custom
			}else if( $digit == -2 ){
				if( $amount == intval($amount) ){
					$digit = 0;
				}else{
					$digit = tourmaster_get_option('general', 'price-breakdown-decimal-digit', '2');
				}
			
			}

			// round every number down
			// $amount = intval($amount * pow(10, 2)) / pow(10, 2);
			$amount = empty($amount)? 0: floatval($amount);
			$amount = round($amount, $digit);

			// format number
			$thousand = tourmaster_get_option('general', 'price-thousand-separator', ',');
			$decimal_sign = tourmaster_get_option('general', 'price-decimal-separator', '.');

			global $tourmaster_currency;
			if( !empty($tourmaster_currency) ){
				$format = $tourmaster_currency['number-format'];
				$amount = floatval($tourmaster_currency['exchange-rate']) * floatval($amount);
				$amount = number_format($amount, $digit, $decimal_sign, $thousand);
			}else{
				$format = tourmaster_get_option('general', 'money-format', '$NUMBER');
				$amount = number_format($amount, $digit, $decimal_sign, $thousand);
			}

			
			
			return str_replace('NUMBER', $amount, $format);
		}
	}

	if( !function_exists('tourmaster_set_locale') ){
		function tourmaster_set_locale($post_id){
			// global $sitepress;
			// 
			// if( function_exists('pll_get_post_language') ){
			// 	$new_locale = pll_get_post_language($post_id, 'locale');
			// }else if( !empty($sitepress) ){
			// 	$post_info = apply_filters('wpml_post_language_details', NULL, get_the_ID());
			// 	if( !empty($post_info['locale']) ){
			// 		$new_locale = $post_info['locale'];
			// 	}
			// }
			// 
			// if( !empty($new_locale) ){
			// 	switch_to_locale($new_locale);
			// 	unset($GLOBALS['tourmaster_general']);
			// }

		}
	}
	if( !function_exists('tourmaster_return_locale') ){
		function tourmaster_return_locale(){	
			// global $sitepress;
			// 	
			// if( function_exists('pll_get_post_language') || !empty($sitepress) ){
			// 	restore_previous_locale();
			// 	unset($GLOBALS['tourmaster_general']);
			// }
		}
	}
	if( !function_exists('tourmaster_wpml_posts') ){
		function tourmaster_wpml_posts($tour_id){	
			global $sitepress;

			$ret = array();

			// polylang is enable
			if( function_exists('pll_get_post_translations') ){
				$pll_translations = pll_get_post_translations($tour_id);
				if( !empty($pll_translations) ){
					foreach( $pll_translations as $translation ){
						$ret[] = $translation; 
					}
				}

			// wpml is enable
			}else if( !empty($sitepress) ){

				$trid = $sitepress->get_element_trid($tour_id, 'post_tour');
				$translations = $sitepress->get_element_translations($trid,'post_tour');
				if( !empty($translations) ){
					foreach( $translations as $translation ){
						$ret[] = $translation->element_id; 
					}
				}
			}

			return $ret;
		}
	}

	if( !function_exists('tourmaster_get_sql_page_part') ){
		function tourmaster_get_sql_page_part($paged = 1, $num_fetch = 5){
			global $wpdb;

			$ret = '';

			if( $paged <= 1 ){
				$ret .= $wpdb->prepare('LIMIT %d ', $num_fetch);
			}else{
				$ret .= $wpdb->prepare('LIMIT %d, %d', (($paged - 1) * $num_fetch), $num_fetch);
			}

			return $ret;
		}
	}


	add_action('wp_enqueue_scripts', 'tourmaster_include_lightbox');
	if( !function_exists('tourmaster_include_lightbox') ){
		function tourmaster_include_lightbox($atts){	
			if( !function_exists('gdlr_core_get_lightbox_atts') ){
				wp_enqueue_style('lightgallery', TOURMASTER_URL . '/plugins/lightgallery/lightgallery.css');
				wp_enqueue_script('lightgallery', TOURMASTER_URL . '/plugins/lightgallery/lightgallery.js', array('jquery'), false, true);
			}
		}
	}

	// lightbox 
	if( !function_exists('tourmaster_get_lightbox_atts') ){
		function tourmaster_get_lightbox_atts($atts){
			if( function_exists('gdlr_core_get_lightbox_atts') ){
				return gdlr_core_get_lightbox_atts($atts);
			}else{
				$ret  = ' class="tourmaster-lightgallery ' . (empty($atts['class'])? '': $atts['class']) . '" ';
				$ret .= empty($atts['url'])? '': ' href="' . esc_url($atts['url']) . '"';
				$ret .= empty($atts['caption'])? '': ' data-sub-html="' . esc_attr($atts['caption']) . '"';
				$ret .= empty($atts['group'])? '': ' data-lightbox-group="' . esc_attr($atts['group']) . '"';

				return $ret;
			}
		}
	}

	// price comparing function
	if( !function_exists('tourmaster_compare_price') ){
		function tourmaster_compare_price( $price1, $price2 ){

			if( abs(floatval($price1) - floatval($price2)) <= 0.01 ){
				return true;
			}else{
				return false;
			}

		}
	}

	// Function to get the client ip address
	if( !function_exists('tourmaster_get_client_ip') ){
		function tourmaster_get_client_ip(){
		    $ipaddress = '';
		    if( !empty($_SERVER['HTTP_CLIENT_IP']) ){
		        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		    }else if( !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ){
		        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		    }else if( !empty($_SERVER['HTTP_X_FORWARDED']) ){
		        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		    }else if( !empty($_SERVER['HTTP_FORWARDED_FOR']) ){
		        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		    }else if( !empty($_SERVER['HTTP_FORWARDED']) ){
		        $ipaddress = $_SERVER['HTTP_FORWARDED'];
		    }else if( !empty($_SERVER['REMOTE_ADDR']) ){
		        $ipaddress = $_SERVER['REMOTE_ADDR'];
		    }else{
		        $ipaddress = 'UNKNOWN';
		    }
		 
		    return $ipaddress;
		}
	}
	
	if( !function_exists('tourmaster_date_format') ){
		function tourmaster_date_format( $date, $format = '' ){
			$format = empty($format)? get_option('date_format'): $format;
			$date = is_numeric($date)? $date: strtotime($date);
			return date_i18n($format, $date);
		}
	}
	if( !function_exists('tourmaster_time_format') ){
		function tourmaster_time_format( $date, $format = '' ){
			$format = empty($format)? get_option('time_format'): $format;
			$date = is_numeric($date)? $date: strtotime($date);
			return date_i18n($format, $date);
		}
	}
	if( !function_exists('tourmaster_time_offset') ){
		function tourmaster_time_offset( $time, $offset ){
			$time_offset = 0;

			// change hh:mm time to second
			if( !empty($time) ){
				$start_time = explode(':', $time);
				if( !empty($start_time[0]) ){
					$time_offset += intval($start_time[0]) * 60 * 60;
				}
				if( !empty($start_time[1]) ){
					$time_offset += intval($start_time[1]) * 60;
				}				
			}

			// last minute booking in hours
			if( !empty($offset) ){
				$time_offset -= intval($offset) * 60 * 60;
			}

			return $time_offset;
		}
	}

	if( !function_exists('tourmaster_lightbox_content') ){
		function tourmaster_lightbox_content( $settings = array() ){
			$style = tourmaster_get_option('general', 'login-lightbox-style', 'style-1');

			$ret  = '<div class="tourmaster-lightbox-content-wrap tourmaster-' . esc_attr($style) . '" data-tmlb-id="' . $settings['id'] . '" >';
			if( !empty($settings['title']) ){
				$ret .= '<div class="tourmaster-lightbox-head" >';
				$ret .= '<h3 class="tourmaster-lightbox-title" >' . $settings['title'] . '</h3>';
				$ret .= '<i class="tourmaster-lightbox-close icon_close" ></i>';
				$ret .= '</div>';
			}

			if( !empty($settings['content']) ){
				$ret .= '<div class="tourmaster-lightbox-content" >' . $settings['content'] . '</div>';
			}
			$ret .= '</div>';

			return $ret;
		} // tourmaster_lightbox_content
	}

	if( !function_exists('tourmaster_match_curly_braces') ){
		function tourmaster_match_curly_braces( $string ){
			$ret = array();

			$start = strpos($string, '{');
			$end = 0;
			while( $start !== false && $end !== false ){
				$string = substr($string, $start);
				$start = 0;
				$end = strpos($string, '}');

				$ret[] = substr($string, $start, $end + 1);
				$start = strpos($string, '{', $end);
			}

			return $ret;
		}
	}
	if( !function_exists('tourmaster_read_custom_fields') ){
		function tourmaster_read_custom_fields( $string ){

			$custom_fields = array();

			// match every { }
			$matches = tourmaster_match_curly_braces($string);
			if( !empty($matches) ){
				foreach( $matches as $match ){

					$field = array();

					// remove unnecessary string out and separate each attribute
					$match = str_replace(array("{", "}", "\r\n", "\n"), "", $match);
					$match = explode(',', $match);
					if( !empty($match) ){
						foreach( $match as $att ){
							$att = explode('=', $att);
							if( !empty($att) && sizeof($att) == 2 ){
								if( $att[0] == 'options' ){
									$field[$att[0]] = array();
									$options = explode('/', $att[1]);
									if( !empty($field['title']) ){
										$field[$att[0]][''] = $field['title'];
									}

									foreach($options as $option){
										$field[$att[0]][$option] = $option;
									}
								}else if( $att[0] == 'type' && $att[1] == 'country' ){
									$field['type'] = 'combobox';
									$field['options'] = tourmaster_get_country_list();
									$field['default'] = tourmaster_get_option('general', 'user-default-country', '');
								}else{
									$field[$att[0]] = $att[1];
								}
								
							}  
						}
					}

					$custom_fields[$field['slug']] = $field;	
				}
			}

			return $custom_fields;
		}
	}
	if( !function_exists('tourmaster_get_form_field') ){
		function tourmaster_get_form_field( $settings, $slug, $value = '' ){

			if( isset($settings['echo']) && $settings['echo'] === false ){
				ob_start();
			}

			$user_id = get_current_user_id();
			$extra_class = 'tourmaster-' . $slug . '-field-' . $settings['slug'];
			$field_value = '';
			if( !empty($value) || $value === 0 || $value === '0' ){
				$field_value = $value;
			}else if( !empty($_POST[$settings['slug']]) ){
				$field_value = tourmaster_process_post_data($_POST[$settings['slug']]);
			}else if( !empty($user_id) ){
				$field_value = tourmaster_get_user_meta($user_id, $settings['slug']);
			}

			if( empty($field_value) && !empty($settings['default']) ){
				$field_value = $settings['default'];
			}

			$data = '';
			if( !empty($settings['data']) && !empty($settings['data']['slug']) && !empty($settings['data']['value']) ){
				$data = ' data-' . esc_attr($settings['data']['slug']) . '="' . esc_attr($settings['data']['value']) . '" ';
			}
			if( !empty($settings['type']) ){
				$extra_class .= ' tourmaster-type-' . $settings['type'];
			}

			echo '<div class="tourmaster-' . esc_attr($slug) . '-field ' . esc_attr($extra_class) . ' clearfix" >';
			echo '<div class="tourmaster-head" >';
			if( !empty($settings['title']) ){
				echo $settings['title'];
			}
			if( !empty($settings['required']) && ($settings['required'] === true || $settings['required'] == "true") ){
				echo '<span class="tourmaster-req" >*</span>';
				$data .= ' data-required ';
			}
			echo '</div>';

			echo '<div class="tourmaster-tail clearfix" >';
			if( !empty($settings['pre-input']) ){
				echo $settings['pre-input'];
			}
			
			switch($settings['type']){
				case 'plain-text':
					echo empty($field_value)? '-': $field_value;
					break;
				case 'textarea':
					echo '<textarea name="' . esc_attr($settings['slug']) . '" ' . $data . ' >' . esc_textarea($field_value) . '</textarea>';
					break;
				case 'email':
					echo '<input type="email" name="' . esc_attr($settings['slug']) . '" value="' . esc_attr($field_value) . '" ' . $data . ' />';
					break;
				case 'text':
					echo '<input type="text" name="' . esc_attr($settings['slug']) . '" value="' . esc_attr($field_value) . '" ' . $data . ' />';
					break;
				case 'price-edit':
					echo '<div class="tourmaster-price-edit-head" >';
					echo '<input type="text" name="' . esc_attr($settings['slug']) . ((!empty($settings['data-type']) && $settings['data-type'] == 'array')? '[]': '') . '" value="' . esc_attr($field_value) . '" ' . $data . ' />';
					echo '</div>';
					if( !empty($settings['description']) ){
						echo '<div class="tourmaster-price-edit-tail">' . $settings['description'] . '</div>';
					}
					break;
				case 'file':
					echo '<label class="tourmaster-file-label" >';
					echo '<span class="tourmaster-file-label-text" data-default="' . esc_attr__('Click to select a file', 'tourmaster') . '" >' . esc_html__('Click to select a file', 'tourmaster') . '</span>';
					echo '<input type="file" name="' . esc_attr($settings['slug']) . '" ' . $data . ' />';
					echo '</label>';
					break;
				case 'password':
					echo '<input type="password" name="' . esc_attr($settings['slug']) . '" value="' . esc_attr($field_value) . '" ' . $data . ' />';
					break;
				case 'combobox':
					echo '<div class="tourmaster-combobox-wrap" >';
					echo '<select name="' . esc_attr($settings['slug']) . '" ' . $data . ' >';
					foreach( $settings['options'] as $option_val => $option_title ){
						echo '<option value="' . esc_attr($option_val) . '" ' . ($field_value == $option_val? 'selected': '') . ' >' . $option_title . '</option>';
					}
					echo '</select>';
					echo '</div>';
					break;

				case 'datepicker':
						echo '<input type="text" class="tourmaster-datepicker" name="' . esc_attr($settings['slug']) . '" value="' . esc_attr($field_value) . '" />';
						echo '<i class="fa fa-calendar" ></i>';
					break;

				case 'date':
					echo '<div class="tourmaster-date-select" >';
					$selected_date = explode('-', $field_value);

					$date = empty($selected_date[2])? '': intval($selected_date[2]);
					echo '<div class="tourmaster-combobox-wrap tourmaster-form-field-alt-date" >';
					echo '<select data-type="date" >';
					echo '<option value="" ' . (empty($date)? 'selected': '' ) . ' >' . esc_html__('Date', 'tourmaster') . '</option>';
					for( $i = 1; $i <= 31; $i++ ){
						echo '<option value="' . esc_attr($i) . '" ' . (($i == $date)? 'selected': '' ) . ' >' . $i . '</option>';
					}
					echo '</select>';
					echo '</div>'; // tourmaster-combobox-wrap

					$month = empty($selected_date[1])? '': intval($selected_date[1]);
					echo '<div class="tourmaster-combobox-wrap tourmaster-form-field-alt-month" >';
					echo '<select data-type="month" >';
					echo '<option value="" ' . (empty($month)? 'selected': '' ) . ' >' . esc_html__('Month', 'tourmaster') . '</option>';
					for( $i = 1; $i <= 12; $i++ ){
						echo '<option value="' . esc_attr($i) . '" ' . (($i == $month)? 'selected': '' ) . ' >' . date_i18n('F', strtotime('2016-' . $i . '-1')) . '</option>';
					}
					echo '</select>';
					echo '</div>'; // tourmaster-combobox-wrap

					$current_year = date('Y');
					$start_year = $current_year - 120;
					$year = empty($selected_date[0])? '': intval($selected_date[0]);
					echo '<div class="tourmaster-combobox-wrap tourmaster-form-field-alt-year" >';
					echo '<select data-type="year" >';
					echo '<option value="" ' . (empty($year)? 'selected': '' ) . ' >' . esc_html__('Year', 'tourmaster') . '</option>';
					for( $i = $current_year; $i >= $start_year; $i-- ){
						echo '<option value="' . esc_attr($i) . '" ' . (($i == $year)? 'selected': '' ) . ' >' . $i . '</option>';
					}
					echo '</select>';
					echo '</div>'; // tourmaster-combobox-wrap

					echo '</div>'; // tourmaster date select
					echo '<input type="hidden" name="' . esc_attr($settings['slug']) . '" value="' . esc_attr($field_value) . '" />';
					break;
			}
			echo '</div>';
			echo '</div>'; // tourmaster-edit-profile-field	

			if( isset($settings['echo']) && $settings['echo'] === false ){
				$ret = ob_get_contents();
				ob_end_clean();

				return $ret;
			}
		} // tourmaster_get_form_field
	}	

	// retrieve all categories from each post type
	if( !function_exists('tourmaster_get_term_list') ){	
		function tourmaster_get_term_list( $taxonomy, $cat = '', $with_all = false ){
			$term_atts = array(
				'taxonomy'=>$taxonomy, 
				'hide_empty'=>0,
				'number'=>999
			);
			if( !empty($cat) ){
				if( is_array($cat) ){
					$term_atts['slug'] = $cat;
				}else{
					$term_atts['parent'] = $cat;
				}
			}
			$term_list = get_categories($term_atts);
			
			$ret = array();
			if( !empty($with_all) ){
				$ret[$cat] = esc_html__('All', 'tourmaster'); 
			}

			if( !empty($term_list) ){
				foreach( $term_list as $term ){
					if( !empty($term->slug) && !empty($term->name) ){
						$ret[$term->slug] = $term->name;
					}
				}
			}

			return $ret;
		}	
	}

	// get rating
	if( !function_exists('tourmaster_get_rating') ){	
		function tourmaster_get_rating( $score ){

			$ret  = '';
			for( $i = 2; $i <= 10; $i += 2 ){
				if( $score - $i >= - 0.5 ){
					$ret .= '<i class="fa fa-star" ></i>';
				}else if( $score - $i <= -1.5 ){
					$ret .= '<i class="fa fa-star-o" ></i>';
				}else{
					$ret .= '<i class="fa fa-star-half-o" ></i>';
				}
			}

			return $ret;
		}
	}

	// get the sidebar
	if( !function_exists('tourmaster_get_sidebar_wrap_class') ){
		function tourmaster_get_sidebar_wrap_class($sidebar_type){
			return ' tourmaster-sidebar-wrap clearfix tourmaster-sidebar-style-' . $sidebar_type;
		}
	}
	if( !function_exists('tourmaster_get_sidebar_class') ){
		function tourmaster_get_sidebar_class($args){

			// set default column
			if( empty($args['column']) ){
				if( $args['sidebar-type'] == 'both' ){
					if( function_exists('traveltour_get_option') ){
						$args['column'] = traveltour_get_option('general', 'both-sidebar-width', 15);
					}else{
						$args['column'] = 15;
					}
				}else if( $args['sidebar-type'] == 'left' || $args['sidebar-type'] == 'right' ){
					if( function_exists('traveltour_get_option') ){
						$args['column'] = traveltour_get_option('general', 'sidebar-width', 20);
					}else{
						$args['column'] = 15;
					}
				}else{
					$args['column'] = 60;
				}
			}

			// if center section
			if( $args['section'] == 'center' ){
				if( $args['sidebar-type'] == 'both' ){
					$args['column'] = 60 - (2 * intval($args['column']));
				}else if( $args['sidebar-type'] == 'left' || $args['sidebar-type'] == 'right' ){
					$args['column'] = 60 - intval($args['column']);
				}
			}

			$sidebar_class  = ' tourmaster-sidebar-' . $args['section'];
			$sidebar_class .= ' tourmaster-column-' . $args['column'];

			return $sidebar_class; 
		}
	}
	if( !function_exists('tourmaster_get_sidebar') ){
		function tourmaster_get_sidebar($sidebar_type = '', $section = '', $sidebar_id = '', $content = ''){
			$sidebar_class = apply_filters('gdlr_core_sidebar_class', '');

			echo '<div class="' . tourmaster_get_sidebar_class(array('sidebar-type'=>$sidebar_type, 'section'=>$section)) . '" >';
			echo '<div class="tourmaster-sidebar-area ' . esc_attr($sidebar_class) . ' tourmaster-item-pdlr" >';
			if( !empty($content) ){
				echo $content;
			}
			if( is_active_sidebar($sidebar_id) ){ 
				dynamic_sidebar($sidebar_id); 
			}
			echo '</div>';
			echo '</div>';

		}
	}	

	// get tour date from option
	// timing : single/recurring
	if( !function_exists('tourmaster_get_tour_dates') ){	
		function tourmaster_get_tour_dates( $settings = array(), $timing = 'single' ){
			
			$dates = array();

			// single date
			if( $timing == 'single' ){
				if( !empty($settings['date'])){
					$dates[] = $settings['date'];
				}

			// recurring date
			}else{
				if( !empty($settings['year']) && !empty($settings['month']) && !empty($settings['day']) ){
					foreach( $settings['year'] as $year ){
						foreach( $settings['month'] as $month ){
							foreach( $settings['day'] as $day ){

								$timestamp = strtotime("{$year}-{$month}-1");

								// if day matched the selected day
								if( $day == strtolower(date('l', $timestamp)) ){
								 	$dates[] = date('Y-m-d', $timestamp);
								}

								$timestamp = strtotime("next {$day}", $timestamp);
								while( date('n', $timestamp) == $month ){
									$dates[] = date('Y-m-d', $timestamp);
									$timestamp = strtotime("next {$day}", $timestamp);
								}
							}
						}
					}

				} // not empty date month year

				// include extra date
				if( !empty($settings['extra-date']) ){
					$extra_dates = array();
					$extra_dates = explode(',', $settings['extra-date']);
					$extra_dates = array_map('trim', $extra_dates);
					
					if( !empty($extra_dates) ){
						foreach( $extra_dates as $date ){
							// ref : http://stackoverflow.com/questions/22061723/regex-date-validation-for-yyyy-mm-dd
							if( preg_match('/^\d{4}\-(0?[1-9]|1[012])\-(0?[1-9]|[12][0-9]|3[01])$/', $date) ){
								if( !in_array($date, $dates) ){
									$dates[] = $date;
								}
							}
						}

						sort($dates);
					}
					// check if it's valid date
				}

				// exclude extra date
				if( !empty($settings['exclude-extra-date']) ){
					$extra_dates = array();
					$extra_dates = explode(',', $settings['exclude-extra-date']);
					$extra_dates = array_map('trim', $extra_dates);
					$extra_dates = apply_filters('tourmaster_tour_exclude_extra_date', $extra_dates);
					
					$dates = array_diff($dates, $extra_dates);
				}
			}

			return $dates;
		} // tourmaster_get_tour_dates
	}	

	// filter date 
	// time_offset is 60 * 60 * 24 = 86400
	if( !function_exists('tourmaster_filter_tour_date') ){
		function tourmaster_filter_tour_date( $dates, $month = '', $time_offset = 86400 ){
			
			if( !empty($month) ){
				$tmp = strtotime(current_time('Y-m-1'));
				$end_time = strtotime('+ ' . (intval($month) + 1) . ' month', $tmp);
			}

			$current_time = strtotime(current_time('Y-m-d H:i'));
			foreach( $dates as $key => $date ){

				$date_time = strtotime($date);

				// if the date is already pass
				if( $current_time > $date_time + $time_offset ){
					unset($dates[$key]);
				}

				// if exceed the available time
				if( !empty($end_time) && $end_time < $date_time ){
					unset($dates[$key]);
				}
			}

			return $dates;
		}
	}	