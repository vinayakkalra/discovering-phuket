<?php
	/*	
	*	Goodlayers Item For Page Builder
	*/

	add_action('plugins_loaded', 'tourmaster_add_pb_element_room_cart');
	if( !function_exists('tourmaster_add_pb_element_room_cart') ){
		function tourmaster_add_pb_element_room_cart(){

			if( class_exists('gdlr_core_page_builder_element') ){
				gdlr_core_page_builder_element::add_element('room_cart', 'tourmaster_pb_element_room_cart'); 
			}
			
		}
	}
	
	if( !class_exists('tourmaster_pb_element_room_cart') ){
		class tourmaster_pb_element_room_cart{
			
			// get the element settings
			static function get_settings(){
				return array(
					'icon' => 'fa-address-book',
					'title' => esc_html__('Room Cart', 'tourmaster')
				);
			}
			
			// return the element options
			static function get_options(){
				return apply_filters('tourmaster_room_search_item_options', array(		
					'spacing' => array(
						'title' => esc_html('Spacing', 'tourmaster'),
						'options' => array(
							'padding-bottom' => array(
								'title' => esc_html__('Padding Bottom ( Item )', 'tourmaster'),
								'type' => 'text',
								'data-input-type' => 'pixel',
								'default' => '30px'
							),
						)
					),
				));
			}

			// get the preview for page builder
			static function get_preview( $settings = array() ){
				$content  = self::get_content($settings, true);
				return $content;
			}			

			// get the content from settings
			static function get_content( $settings = array(), $preview = false ){
				
				
				// default variable
				$settings = empty($settings)? array(): $settings;
				
				$ret  = '<div class="tourmaster-room-cart-item tourmaster-item-mglr tourmaster-item-pdb clearfix" ';
				if( !empty($settings['padding-bottom']) && $settings['padding-bottom'] != '30px' ){
					$ret .= tourmaster_esc_style(array('padding-bottom'=>$settings['padding-bottom']));
				}
				if( !empty($settings['id']) ){
					$ret .= ' id="' . esc_attr($settings['id']) . '" ';
				}
				$ret .= ' >';

				if( $preview ){
					$ret .= '<div class="gdlr-core-external-plugin-message">' . esc_html__('This item will show cart list.', 'tourmaster') . '</div>';
				}else{
					$booking_detail = json_decode(wp_unslash($_COOKIE['tourmaster-room-cart']), true);
					$booking_details = stripslashes_deep($booking_detail); 

					if( !empty($booking_details) ){
						$price_breakdowns = tourmaster_room_price_breakdowns($booking_details);
						$ret .= self::get_cart_content($booking_details, $price_breakdowns);
						$ret .= '<div class="tourmaster-room-cart-submit" >';
						$ret .= '<a class="tourmaster-button" href="' . esc_attr(add_query_arg(array('pt' => 'room', 'type' => 'cart'), tourmaster_get_template_url('payment'))) . '" >' . esc_html__('Check out now', 'tourmaster') . '</a>';
						$ret .= '</div>';
					}else{
						$icon_color = tourmaster_get_option('room_color', 'room-cart-empty-icon', '#cccccc');

						$ret .= '<div class="tourmaster-room-price-summary-block tourmaster-room-cart-empty" >';
						ob_start();
?>
<svg width="75" height="75" viewBox="0 0 75 75" fill="none" xmlns="http://www.w3.org/2000/svg">
<g clip-path="url(#clip0_64_13388)">
<path d="M61.2755 48.6663C63.5305 46.9722 65.1638 44.6561 66.0005 41.9661L74.7857 15.9573H18.3111L17.688 9.04843C17.4737 6.6185 16.3648 4.37278 14.5658 2.72549C12.7666 1.07819 10.4323 0.170776 7.99281 0.170776H0.580566V5.96399H7.99281C10.0493 5.96399 11.7364 7.50882 11.9176 9.56323L16.4512 59.8324C16.4519 59.8414 16.4528 59.8502 16.4536 59.8592C16.8955 64.2973 20.2277 67.7641 24.485 68.4864C25.6203 71.8748 28.8224 74.3241 32.588 74.3241C36.2834 74.3241 39.4375 71.9658 40.6267 68.6757H47.4325C48.6217 71.9658 51.7758 74.3241 55.4712 74.3241C60.1829 74.3241 64.0162 70.4907 64.0162 65.7791C64.0162 61.0675 60.1829 57.2341 55.4712 57.2341C51.7758 57.2341 48.6217 59.5924 47.4325 62.8825H40.6267C39.4375 59.5924 36.2834 57.2341 32.588 57.2341C29.0163 57.2341 25.9513 59.4375 24.6762 62.5557C23.3488 62.0287 22.3738 60.8013 22.2196 59.297L21.498 51.2959H53.4436C56.277 51.2961 58.9852 50.3867 61.2755 48.6663ZM55.4715 63.0274C56.9889 63.0274 58.2233 64.2618 58.2233 65.7792C58.2233 67.2967 56.9889 68.5311 55.4715 68.5311C53.954 68.5311 52.7196 67.2967 52.7196 65.7792C52.7196 64.2618 53.954 63.0274 55.4715 63.0274ZM32.5882 63.0274C34.1056 63.0274 35.34 64.2618 35.34 65.7792C35.34 67.2967 34.1056 68.5311 32.5882 68.5311C31.0707 68.5311 29.8364 67.2967 29.8364 65.7792C29.8364 64.2618 31.0707 63.0274 32.5882 63.0274ZM20.9757 45.503V45.5028L18.8334 21.7505H66.714L60.5 40.147L60.4758 40.2222C59.5193 43.3315 56.6276 45.503 53.4439 45.503H20.9757Z" fill="<?php echo esc_attr($icon_color); ?>"/>
</g>
<defs>
<clipPath id="clip0_64_13388">
<rect width="74.2051" height="74.2051" fill="white" transform="translate(0.580566 0.144897)"/>
</clipPath>
</defs>
</svg>
<?php
						$ret .= ob_get_contents();
						ob_end_clean();

						$ret .= '<div class="tourmaster-title" >' . esc_html__('Your cart is empty', 'tourmaster') . '</div>';
						$ret .= '<div class="tourmaster-caption" >' . esc_html__('You don\'t have any item in the cart at this moment.', 'tourmaster') . '</div>';
						$ret .= '</div>';
					}
					
				}
				

				$ret .= '</div>'; // tourmaster-room-title-item
				
				return $ret;
			}		

			static function get_cart_content($booking_details, $price_breakdowns){

				ob_start();
				echo '<div class="tourmaster-room-price-summary-wrap tourmaster-room-service-form" ';
				echo ' data-remove-head="' . esc_html__('Just To Confirm', 'tourmaster') . '" ';
				echo ' data-remove-yes="' . esc_html__('Yes', 'tourmaster') . '" ';
				echo ' data-remove-no="' . esc_html__('No', 'tourmaster') . '" ';
				echo ' data-remove-text="' . esc_html__('Are you sure you want to do this ?', 'tourmaster') . '" ';
				echo ' data-remove-sub="" ';
				echo ' >';
				
				for( $i = 0; $i < sizeof($booking_details); $i++ ){
					$booking_detail = $booking_details[$i];
					$price_breakdown = $price_breakdowns[$i];
					$room_option = tourmaster_get_post_meta($booking_detail['room_id'], 'tourmaster-room-option');
					
					echo '<div class="tourmaster-room-price-summary-block" >';
					echo '<div class="tourmaster-room-price-summary-room-title" >' . get_the_title($booking_detail['room_id']) . '</div>';
					echo '<div class="tourmaster-room-price-summary-room-duration" >' . tourmaster_room_booking_duration_info($booking_detail['start_date'], $booking_detail['end_date'], false) . '</div>';
				
					for( $j = 0; $j < $booking_detail['room_amount']; $j++ ){
						echo '<div class="tourmaster-room-price-summary-item" >';
						echo '<h3 class="tourmaster-title" >';

						echo ($booking_detail['room_amount'] > 1 )? sprintf(esc_html__('Room %d :', 'tourmaster'), ($j+1)) . ' ':  '';
						echo sprintf(_n('%d Adult', '%d Adults', $booking_detail['adult'][$j], 'tourmaster'), $booking_detail['adult'][$j]) . ' '; 
						echo sprintf(_n('%d Children', '%d Childrens', $booking_detail['children'][$j], 'tourmaster'), $booking_detail['children'][$j]) . ' '; 
						
						echo '<span class="tourmaster-price-breakdown-title">( price breakdown )';
						echo tourmaster_room_single_price_breakdown($booking_detail, $price_breakdown, $j);
						echo '</span>';
						echo '<span class="tourmaster-price" >';
						echo tourmaster_money_format($price_breakdown['room-prices'][$j]);
						echo '<i class="tourmaster-room-remove-room fa fa-trash-o" data-i="' . esc_attr($i) . '" data-j="' . esc_attr($j) . '" ></i>';
						echo '</span>';
						echo '</h3>';
						echo '</div>'; // tourmaster-room-price-summary-item
					}
		
					echo '</div>'; // tourmaster-room-price-summary-block
				} 
				
				echo '</div>'; // tourmaster-room-price-summary-wrap 

				$ret = ob_get_contents();
				ob_end_clean();

				return $ret;
			}

		} // tourmaster_pb_element_tour
	} // class_exists	

	add_shortcode('tourmaster_room_cart', 'tourmaster_room_cart_shortcode');
	if( !function_exists('tourmaster_room_cart_shortcode') ){
		function tourmaster_room_cart_shortcode($atts){
			$atts = wp_parse_args($atts, array(
				
			));

			$ret  = '<div class="tourmaster-room-cart-shortcode clearfix tourmaster-item-rvpdlr" >';
			$ret .= tourmaster_pb_element_room_cart::get_content($atts);
			$ret .= '</div>';

			return $ret;
		}
	}