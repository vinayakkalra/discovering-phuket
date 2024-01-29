<?php
	/*	
	*	Goodlayers Item For Page Builder
	*/

	add_action('plugins_loaded', 'tourmaster_add_pb_element_room_search');
	if( !function_exists('tourmaster_add_pb_element_room_search') ){
		function tourmaster_add_pb_element_room_search(){

			if( class_exists('gdlr_core_page_builder_element') ){
				gdlr_core_page_builder_element::add_element('room_search', 'tourmaster_pb_element_room_search'); 
			}
			
		}
	}
	
	if( !class_exists('tourmaster_pb_element_room_search') ){
		class tourmaster_pb_element_room_search{
			
			// get the element settings
			static function get_settings(){
				return array(
					'icon' => 'fa-address-book',
					'title' => esc_html__('Room Search', 'tourmaster')
				);
			}
			
			// return the element options
			static function get_options(){
				return apply_filters('tourmaster_room_search_item_options', array(		
					'general' => array(
						'title' => esc_html__('General', 'tourmaster'),
						'options' => array(
							'style' => array(
								'title' => esc_html__('Style', 'tourmaster'),
								'type' => 'combobox',
								'options' => array(
									'box' => esc_html__('Box', 'tourmaster'),
									'text-top' => esc_html__('Text Top', 'tourmaster'),
									'placeholder' => esc_html__('Placeholder', 'tourmaster'),
									'full-background' => esc_html__('Full Background', 'tourmaster')
								)
							),
							'align' => array(
								'title' => esc_html__('Align', 'tourmaster'),
								'type' => 'combobox',
								'options' => array(
									'vertical' => esc_html__('Vertical', 'tourmaster'),
									'horizontal' => esc_html__('Horizontal', 'tourmaster')
								)
							),
							'form-radius' => array(
								'title' => esc_html__('Form Radius', 'tourmaster'),
								'type' => 'combobox',
								'options' => array(
									'normal' => esc_html__('Normal', 'tourmaster'),
									'round' => esc_html__('Round', 'tourmaster')
								)
							),
							'button-style' => array(
								'title' => esc_html__('Button Style', 'tourmaster'),
								'type' => 'combobox',
								'options' => array(
									'solid' => esc_html__('Solid', 'tourmaster'),
									'border' => esc_html__('Border', 'tourmaster')
								)
							),
							'search-text' => array(
								'title' => esc_html__('Search Text', 'tourmaster'),
								'type' => 'text'
							),
							'background-shadow-size' => array(
								'title' => esc_html__('Background Shadow Size', 'tourmaster'),
								'type' => 'custom',
								'item-type' => 'padding',
								'options' => array('x', 'y', 'size'),
								'data-input-type' => 'pixel',
								'condition' => array( 'style' => 'full-background' )
							),
							'background-shadow-color' => array(
								'title' => esc_html__('Background Shadow Color', 'tourmaster'),
								'type' => 'colorpicker',
								'condition' => array( 'style' => 'full-background' )
							),
							'background-shadow-opacity' => array(
								'title' => esc_html__('Background Shadow Opacity', 'tourmaster'),
								'type' => 'text',
								'default' => '0.2',
								'description' => esc_html__('Fill the number between 0.01 to 1', 'tourmaster'),
								'condition' => array( 'style' => 'full-background' )
							),
						)
					),			
					'color' => array(
						'title' => esc_html('Color', 'tourmaster'),
						'options' => array(
							'submit-button-background' => array(
								'title' => esc_html__('Submit Button Background', 'tourmaster'),
								'type' => 'colorpicker'
							),
							'submit-button-text' => array(
								'title' => esc_html__('Submit Button Text', 'tourmaster'),
								'type' => 'colorpicker'
							),
							'submit-button-border' => array(
								'title' => esc_html__('Submit Button Border', 'tourmaster'),
								'type' => 'colorpicker'
							),
						)
					),			
					'spacing' => array(
						'title' => esc_html('Spacing', 'tourmaster'),
						'options' => array(
							'full-bg-top-padding' => array(
								'title' => esc_html__('Full BG Top Padding', 'tourmaster'),
								'type' => 'text',
								'data-input-type' => 'pixel'
							), 
							'full-bg-bottom-padding' => array(
								'title' => esc_html__('Full BG Bottom Padding', 'tourmaster'),
								'type' => 'text',
								'data-input-type' => 'pixel'
							), 
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
				$settings['style'] = empty($settings['style'])? 'box': $settings['style'];
				$settings['search-text'] = empty($settings['search-text'])? esc_html__('Search Room', 'tourmaster'): $settings['search-text'];
				$settings['button-style'] = empty($settings['button-style'])? 'solid': $settings['button-style'];
				$settings['form-radius'] = empty($settings['form-radius'])? 'normal': $settings['form-radius'];
				$settings['align'] = empty($settings['align'])? 'horizontal': $settings['align'];

				$form_class  = 'tourmaster-radius-' . esc_attr($settings['form-radius']) . ' ';
				$form_class .= 'tourmaster-style-' . esc_attr($settings['style']) . ' ';
				$form_class .= 'tourmaster-align-' . esc_attr($settings['align']) . ' ';

				if( $settings['style'] == 'full-background' ){
					$form_class .= 'gdlr-core-skin-e-background ';
				}

				$ret  = '<div class="tourmaster-room-search-item tourmaster-item-pdlr clearfix" ';
				if( !empty($settings['padding-bottom']) && $settings['padding-bottom'] != '30px' ){
					$ret .= tourmaster_esc_style(array('padding-bottom'=>$settings['padding-bottom']));
				}
				if( !empty($settings['padding-bottom']) && $settings['padding-bottom'] != '30px' ){
					$ret .= tourmaster_esc_style(array('padding-bottom'=>$settings['padding-bottom']));
				}
				if( !empty($settings['id']) ){
					$ret .= ' id="' . esc_attr($settings['id']) . '" ';
				}
				$ret .= ' >';
				$ret .= '<' . ($preview? 'div': 'form') . ' class="tourmaster-room-search-form ' . esc_attr($form_class) . '" ';
				if( $settings['style'] == 'full-background' ){
					$ret .= gdlr_core_esc_style(array(
						'background-shadow-size' => empty($settings['background-shadow-size'])? '': $settings['background-shadow-size'],
						'background-shadow-color' => empty($settings['background-shadow-color'])? '': $settings['background-shadow-color'],
						'background-shadow-opacity' => empty($settings['background-shadow-opacity'])? '': $settings['background-shadow-opacity'],
					));
				}
				$ret .= 'action="' . esc_attr(tourmaster_get_template_url('room-search')) . '" method="get" ';
				$ret .= ' >';
				
				if( $settings['style'] == 'placeholder' ){
					$ret .= self::get_content_placeholder($settings);
				}else{
					$ret .= self::get_content_normal($settings);
				}
				
				$ret .= '<input type="hidden" name="room-search" value="" />';
				
				$ret .= '</' . ($preview? 'div': 'form') . '>';
				$ret .= '</div>'; // tourmaster-tour-search-item
				
				return $ret;
			}

			static function get_search_filter( $settings = array() ){

				if( empty($settings['search-filters']) ) return;
				$show_filter_content = (empty($settings['show-filter-content']) || $settings['show-filter-content'] = 'enable')? true: false;


				$tax_fields = array(
					'room_category' => esc_html__('Category', 'tourmaster'),
					'room_tag' => esc_html__('Tag', 'tourmaster')
				);
				$tax_fields = $tax_fields + tourmaster_get_custom_tax_list('room');

				$ret = '';

				if( !empty($tax_fields) ){
					foreach( $tax_fields as $tax_slug => $tax_name ){
						if( !in_array($tax_slug, $settings['search-filters']) ) continue;
						
						$term_options = tourmaster_get_term_list($tax_slug);

						if( !empty($term_options) ){
							$ret .= '<div class="tourmaster-room-search-tax-item" >';
							$ret .= '<h4 class="tourmaster-label" >';
							$ret .= $tax_name;
							$ret .= '<span class="tourmaster-close-filter ' . ($show_filter_content? '': 'tourmaster-active') . '"></span>';
							$ret .= '</h4>';
							
							$ret .= '<div class="tourmaster-filter" ' . ($show_filter_content? '': 'style="display: none;"') . ' >';
							foreach( $term_options as $term_slug => $term_name ){
								$ret .= '<div class="tourmaster-filter-term" >';
								$ret .= '<input type="checkbox" name="tax-' . esc_attr($tax_slug) . '[]" value="' . esc_attr($term_slug) . '" ';
								if( !empty($_GET['tax-' . $tax_slug]) && in_array($term_slug, $_GET['tax-' . $tax_slug]) ){
									$ret .= 'checked';
								}
								$ret .= ' />';
								$ret .= '<span class="tourmaster-checkbox-input" ><i class="fa fa-check" ></i></span>';
								$ret .= $term_name;
								$ret .= '</div>';
							}
							$ret .= '</div>';
							$ret .= '</div>';
						}
						
					}

				}

				if( !empty($ret) ){
					$ret = '<div class="tourmaster-room-search-tax-wrap" >' . $ret . '</div>';
				}

				return $ret;

			}
			
			static function get_content_normal( $settings = array() ){
				$bg_type = ($settings['style'] == 'text-top')? 'inner': 'outer';
				$column_css = array();

				if( $settings['style'] == 'full-background' ){
					$column_css['padding-top'] = empty($settings['full-bg-top-padding'])? '': $settings['full-bg-top-padding'];
					$column_css['padding-bottom'] = empty($settings['full-bg-bottom-padding'])? '': $settings['full-bg-bottom-padding'];
				}

				$pb_atts = array('align' => $settings['align']);
				if( !empty($_GET['start_date']) ){
					$pb_atts['start_date'] = $_GET['start_date'];
				}
				if( !empty($_GET['end_date']) ){
					$pb_atts['end_date'] = $_GET['end_date'];
				}

				$ret  = '<div class="tourmaster-room-search-size10" ' . gdlr_core_esc_style($column_css) . ' >';
				$ret .= tourmaster_room_datepicker_range($pb_atts, $bg_type);
				$ret .= '</div>';

				$ret .= '<div class="tourmaster-room-size2" ' . gdlr_core_esc_style($column_css) . ' >';
				$ret .= tourmaster_room_amount_selection(array(
					'room_amount' => array(
						'label' => esc_html__('Room', 'tourmaster'),
						'value' => 1
					)
				), array(
					'title' => esc_html__('Room', 'tourmaster'),
					'hide-label' => true
				), $bg_type);
				$ret .= '</div>';

				$ret .= '<div class="tourmaster-room-search-size5" ' . gdlr_core_esc_style($column_css) . ' >';
				$ret .= tourmaster_room_amount_selection(array(
					'adult' => array(
						'label' => esc_html__('Adults', 'tourmaster'),
						'value' => 2
					),
					'children' => array(
						'label' => esc_html__('Children', 'tourmaster'),
						'value' => 0
					)
				), array(), $bg_type);
				$ret .= '</div>';

				$ret .= self::get_search_filter($settings);

				$ret .= '<div class="tourmaster-room-search-size4 tourmaster-room-search-submit-wrap" >';
				$ret .= '<input class="tourmaster-room-search-submit tourmaster-style-' . esc_attr($settings['button-style']) . '" type="submit" ';
				$ret .= 'value="' . esc_attr($settings['search-text']) .'" ' . tourmaster_esc_style(array(
					'background' => empty($settings['submit-button-background'])? '': $settings['submit-button-background'],
					'color' => empty($settings['submit-button-text'])? '': $settings['submit-button-text'],
					'border-color' => empty($settings['submit-button-border'])? '': $settings['submit-button-border'],
				)) . ' />';

				if( $settings['style'] == 'full-background' ){
					$ret .= '<div class="tourmaster-content"><span><i class="fa fa-search"></i></span><span>' . esc_html__('Search Now', 'tourmaster') . '</span></div>';
				}
				$ret .= '</div>';

				return $ret;
			}
			static function get_content_placeholder( $settings = array() ){

				$pb_atts = array('init_date' => 'disable', 'align' => $settings['align']);
				if( !empty($_GET['start_date']) ){
					$pb_atts['start_date'] = $_GET['start_date'];
					unset($pb_atts['init_date']);
				}
				if( !empty($_GET['end_date']) ){
					$pb_atts['end_date'] = $_GET['end_date'];
					unset($pb_atts['init_date']);
				}

				$ret  = '<div class="tourmaster-room-search-size10" >';
				$ret .= tourmaster_room_datepicker_range(array('init_date' => 'disable', 'align' => $settings['align']));
				$ret .= '</div>';

				$ret .= '<div class="tourmaster-room-size2" >';
				$ret .= tourmaster_room_amount_selection(array(
					'room_amount' => array(
						'label' => esc_html__('Room', 'tourmaster'),
						'value' => 1
					)
				), array(
					'title' => esc_html__('Room', 'tourmaster'),
				));
				$ret .= '</div>';

				$ret .= '<div class="tourmaster-room-search-size5" >';
				$ret .= tourmaster_room_amount_selection(array(
					'adult' => array(
						'label' => esc_html__('Adults', 'tourmaster'),
						'value' => 2
					),
					'children' => array(
						'label' => esc_html__('Children', 'tourmaster'),
						'value' => 0
					)
				));
				$ret .= '</div>';

				$ret .= self::get_search_filter($settings);

				$ret .= '<div class="tourmaster-room-search-size4 tourmaster-room-search-submit-wrap" >';
				$ret .= '<input class="tourmaster-room-search-submit tourmaster-style-' . esc_attr($settings['button-style']) . '" type="submit" ';
				$ret .= 'value="' . esc_attr($settings['search-text']) .'" ' . tourmaster_esc_style(array(
					'background' => empty($settings['submit-button-background'])? '': $settings['submit-button-background'],
					'color' => empty($settings['submit-button-text'])? '': $settings['submit-button-text'],
					'border-color' => empty($settings['submit-button-border'])? '': $settings['submit-button-border'],
				)) . ' />';
				$ret .= '</div>';

				return $ret;
			}

		} // tourmaster_pb_element_tour
	} // class_exists	

	add_shortcode('tourmaster_room_search', 'tourmaster_room_search_shortcode');
	if( !function_exists('tourmaster_room_search_shortcode') ){
		function tourmaster_room_search_shortcode($atts){
			$atts = wp_parse_args($atts, array());
			
			$ret  = '<div class="tourmaster-room-search-shortcode clearfix tourmaster-item-rvpdlr" >';
			$ret .= tourmaster_pb_element_room_search::get_content($atts);
			$ret .= '</div>';

			return $ret;
		}
	}