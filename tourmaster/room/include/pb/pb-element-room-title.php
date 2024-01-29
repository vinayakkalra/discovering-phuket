<?php
	/*	
	*	Goodlayers Item For Page Builder
	*/

	add_action('plugins_loaded', 'tourmaster_add_pb_element_room_title');
	if( !function_exists('tourmaster_add_pb_element_room_title') ){
		function tourmaster_add_pb_element_room_title(){

			if( class_exists('gdlr_core_page_builder_element') ){
				gdlr_core_page_builder_element::add_element('room_title', 'tourmaster_pb_element_room_title'); 
			}
			
		}
	}
	
	if( !class_exists('tourmaster_pb_element_room_title') ){
		class tourmaster_pb_element_room_title{
			
			// get the element settings
			static function get_settings(){
				return array(
					'icon' => 'fa-address-book',
					'title' => esc_html__('Room Title', 'tourmaster')
				);
			}
			
			// return the element options
			static function get_options(){
				return apply_filters('tourmaster_room_search_item_options', array(		
					'general' => array(
						'title' => esc_html__('General', 'tourmaster'),
						'options' => array(
							'enable-title' => array(
								'title' => esc_html__('Enable Title', 'tourmaster'),
								'type' => 'checkbox',
								'default' => 'enable'
							),
							'enable-rating' => array(
								'title' => esc_html__('Enable Rating', 'tourmaster'),
								'type' => 'checkbox',
								'default' => 'disable'
							),
							'enable-price' => array(
								'title' => esc_html__('Enable Price', 'tourmaster'),
								'type' => 'checkbox',
								'default' => 'enable'
							),
							/*
							'room-info' => array(
								'title' => esc_html__('Room Info', 'tourmaster'),
								'type' => 'custom',
								'item-type' => 'tabs',
								'wrapper-class' => 'gdlr-core-fullsize',
								'options' => array(
									'icon' => array(
										'title' => esc_html__('Icon', 'goodlayers-core'),
										'type' => 'text'
									),
									'title' => array(
										'title' => esc_html__('Text', 'goodlayers-core'),
										'type' => 'text'
									),
								),
								'default' => array()

							),
							*/
							'caption' => array(
								'title' => esc_html__('Caption', 'tourmaster'),
								'type' => 'text'
							),
						)
					),
					'typography' => array(
						'title' => esc_html('Typography', 'tourmaster'),
						'options' => array(
							'title-font-size' => array(
								'title' => esc_html__('Title Font Size', 'tourmaster'),
								'type' => 'text',
								'data-input-type' => 'pixel',
							),
							'title-font-weight' => array(
								'title' => esc_html__('Title Font Weight', 'tourmaster'),
								'type' => 'text',
								'description' => esc_html__('Eg. lighter, bold, normal, 300, 400, 600, 700, 800', 'tourmaster')
							),
							'title-letter-spacing' => array(
								'title' => esc_html__('Title Letter Spacing', 'tourmaster'),
								'type' => 'text',
								'data-input-type' => 'pixel',
							),
							'title-text-transform' => array(
								'title' => esc_html__('Title Text Transform', 'tourmaster'),
								'type' => 'combobox',
								'data-type' => 'text',
								'options' => array(
									'uppercase' => esc_html__('Uppercase', 'tourmaster'),
									'lowercase' => esc_html__('Lowercase', 'tourmaster'),
									'capitalize' => esc_html__('Capitalize', 'tourmaster'),
									'none' => esc_html__('None', 'tourmaster'),
								),
								'default' => 'none'
							),
							'info-icon-font-size' => array(
								'title' => esc_html__('Info Icon Font Size', 'tourmaster'),
								'type' => 'text',
								'data-input-type' => 'pixel',
							),
							'info-font-size' => array(
								'title' => esc_html__('Info Font Size', 'tourmaster'),
								'type' => 'text',
								'data-input-type' => 'pixel',
							),
						)
					),
					'color' => array(
						'title' => esc_html('Color', 'tourmaster'),
						'options' => array(
							'info-icon-color' => array(
								'title' => esc_html__('Info Icon Color', 'tourmaster'),
								'type' => 'colorpicker'
							),
							'info-text-color' => array(
								'title' => esc_html__('Info Text Color', 'tourmaster'),
								'type' => 'colorpicker'
							)
						)
					),
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
				$content  = self::get_content($settings);
				return $content;
			}			

			// get the content from settings
			static function get_content( $settings = array() ){
				
				
				// default variable
				$settings = empty($settings)? array(): $settings;
				
				$settings['enable-title'] = empty($settings['enable-title'])? 'enable': $settings['enable-title'];
				$settings['enable-price'] = empty($settings['enable-price'])? 'enable': $settings['enable-price'];

				$ret  = '<div class="tourmaster-room-title-item tourmaster-item-mglr tourmaster-item-pdb clearfix" ';
				if( !empty($settings['padding-bottom']) && $settings['padding-bottom'] != '30px' ){
					$ret .= tourmaster_esc_style(array('padding-bottom'=>$settings['padding-bottom']));
				}
				if( !empty($settings['id']) ){
					$ret .= ' id="' . esc_attr($settings['id']) . '" ';
				}
				$ret .= ' >';

				if( empty(get_the_ID()) ){
					$title = esc_html__('Room Title', 'tourmaster');
					$price_text = 100;
				}else{
					$title = get_the_title();
					$price_text = get_post_meta(get_the_ID(), 'tourmaster-room-price-text', true);
				}

				if( $settings['enable-title'] == 'enable' ){
					$ret .= '<h3 class="tourmaster-room-title-item-title" ' . gdlr_core_esc_style(array(
						'font-size' => empty($settings['title-font-size'])? '': $settings['title-font-size'],
						'font-weight' => empty($settings['title-font-weight'])? '': $settings['title-font-weight'],
						'letter-spacing' => empty($settings['title-letter-spacing'])? '': $settings['title-letter-spacing'],
						'text-transform' => empty($settings['title-text-transform'])? '': $settings['title-text-transform'],
					)) . ' >' . $title . '</h3>';

					if( !empty($settings['enable-rating']) && $settings['enable-rating'] == 'enable' ){
						$room_style = new tourmaster_room_style();
						$ret .= $room_style->get_rating(array(
							'enable-rating' => 'enable'
						), true);
					}
				}

				if( !empty($price_text) && $settings['enable-price'] == 'enable' ){	
					$price_discount_text = get_post_meta(get_the_ID(), 'tourmaster-room-price-discount-text', true);

					$ret .= '<div class="tourmaster-room-title-price" >';
					$ret .= '<div class="tourmaster-head" >';
					$ret .= '<span class="tourmaster-label" >' . esc_html__('From', 'tourmaster') . '</span>';
					if( !empty($price_discount_text) ){
						$ret .= '<span class="tourmaster-price-discount" >' . tourmaster_money_format($price_discount_text) . '</span>';
					}
					$ret .= '<span class="tourmaster-price" >' . tourmaster_money_format($price_text) . '</span>';
					$ret .= '</div>';
					$ret .= '<div class="tourmaster-tail" >' . esc_html__('per night', 'tourmaster') . '</div>';
					$ret .= '</div>';
				}

				if( !empty($settings['room-info']) ){
					$ret .= '<div class="tourmaster-room-title-item-info-wrap" >';
					foreach( $settings['room-info'] as $room_info ){
						$ret .= '<div class="tourmaster-room-title-item-info" ' . tourmaster_esc_style(array(
							'color' => empty($settings['info-text-color'])? '': $settings['info-text-color'],
							'font-size' => empty($settings['info-font-size'])? '': $settings['info-font-size'],
						)) . ' >';
						$ret .= '<i class="' . esc_attr($room_info['icon']) . '" ' . tourmaster_esc_style(array(
							'color' => empty($settings['info-icon-color'])? '': $settings['info-icon-color'],
							'font-size' => empty($settings['info-icon-font-size'])? '': $settings['info-icon-font-size'],
						)) . ' ></i>';
						$ret .= '<span class="tourmaster-head">' . tourmaster_text_filter($room_info['title']) . '</span>';
						$ret .= '</div>';
					}
					$ret .= '</div>';
				}

				if( !empty($settings['caption']) ){
					$ret .= '<div class="tourmaster-room-title-item-caption" >' . tourmaster_text_filter($settings['caption']) . '</div>';
				}

				$ret .= '</div>'; // tourmaster-room-title-item
				
				return $ret;
			}		

		} // tourmaster_pb_element_tour
	} // class_exists	