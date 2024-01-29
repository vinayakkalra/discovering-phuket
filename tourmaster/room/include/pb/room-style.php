<?php
	/*	
	*	Goodlayers Blog Item Style
	*/

	if( !class_exists('tourmaster_room_style') ){
		class tourmaster_room_style{

			// get the content of the tour item
			function get_content( $args ){

				$ret = apply_filters('tourmaster_room_style_content', '', $args, $this);
				if( !empty($ret) ) return $ret;

				switch( $args['room-style'] ){
					case 'grid':
						return $this->room_grid( $args ); 
						break;	
					case 'grid2':
						return $this->room_grid2( $args ); 
						break;		
					case 'grid3':
						return $this->room_grid3( $args ); 
						break;
					case 'grid4':
						return $this->room_grid4( $args ); 
						break;
					case 'grid5':
						return $this->room_grid5( $args );
						break;
					case 'modern':
						return $this->room_modern( $args ); 
						break;
					case 'modern2':
						return $this->room_modern2( $args ); 
						break;
					case 'side-thumbnail': 
						return $this->room_side_thumbnail( $args ); 
						break;
				}
				
			}

			// get blog excerpt
			function get_excerpt( $excerpt_length, $excerpt_more = ' [&hellip;]' ) {

				$post = get_post();
				if( empty($post) || post_password_required() ){ return ''; }
			
				$excerpt = $post->post_excerpt;
				if( empty($excerpt) ){
					$excerpt = get_the_content('');
					$excerpt = strip_shortcodes($excerpt);
					
					$excerpt = apply_filters('the_content', $excerpt);
					$excerpt = str_replace(']]>', ']]&gt;', $excerpt);
				}
				
				$excerpt_more = apply_filters('excerpt_more', $excerpt_more);
				$excerpt = wp_trim_words($excerpt, $excerpt_length, $excerpt_more);

				$excerpt = apply_filters('wp_trim_excerpt', $excerpt, $post->post_excerpt);		
				$excerpt = apply_filters('get_the_excerpt', $excerpt);
				
				return $excerpt;
			}
			function room_excerpt( $args ){

				$ret = '';

				if( $args['excerpt'] == 'specify-number' ){
					if( !empty($args['excerpt-number']) ){
						$excerpt = $this->get_excerpt($args['excerpt-number']);
						if( !empty($excerpt) ){
							$ret = '<div class="tourmaster-room-content" >' . $excerpt . '</div>';
						}
					}
				}else if( $args['excerpt'] != 'none' ){
					$content = tourmaster_content_filter(get_the_content(), true);
					if( !empty($content) ){
						$ret = '<div class="tourmaster-room-content" >' . $content . '</div>';
					}
				}	

				return $ret;
			}			

			// get the title
			function room_title( $args, $title_front = '', $title_back = '', $extra_css = array() ){

				$extra_css = $extra_css + array(
					'font-size' => empty($args['room-title-font-size'])? '': $args['room-title-font-size'],
					'font-weight' => empty($args['room-title-font-weight'])? '': $args['room-title-font-weight'],
					'letter-spacing' => empty($args['room-title-letter-spacing'])? '': $args['room-title-letter-spacing'],
					'text-transform' => empty($args['room-title-text-transform'])? '': $args['room-title-text-transform'],
					'margin-bottom' => empty($args['room-title-bottom-margin'])? '': $args['room-title-bottom-margin']
				);

				$ret  = '<h3 class="tourmaster-room-title gdlr-core-skin-title" ' . tourmaster_esc_style($extra_css) . ' >';
				$ret .= '<a href="' . get_permalink() . '" >' . $title_front . get_the_title() . $title_back . '</a>';
				$ret .= '</h3>';


				return $ret;
			}

			// get tour thumbnail
			function get_thumbnail( $args, $with_price = false, $with_ribbon = false, $with_category = false ){
				
				$ret = '';

				$feature_image = get_post_thumbnail_id();
				if( !empty($feature_image) ){

					$price_html = '';
					$ribbon_html = '';

					if( $with_price ){
						$price_html = $this->get_price($args, true);
					}
					if( $with_ribbon ){
						$ribbon_html = $this->get_ribbon($args);
					}

					$ret .= '<div class="tourmaster-room-thumbnail tourmaster-media-image ';
					if( !empty($args['enable-thumbnail-zoom-on-hover']) && $args['enable-thumbnail-zoom-on-hover'] == 'enable' ){
						$ret .= ' tourmaster-zoom-on-hover';
					}
					if( !empty($args['frame-shadow-size']['size']) && !empty($args['frame-shadow-color']) && !empty($args['frame-shadow-opacity']) ){
						$ret .= ' gdlr-core-outer-frame-element';	
					}
					$ret .= empty($price_html)? '': ' tourmaster-with-price';
					$ret .= empty($ribbon_html)? '': ' tourmaster-with-ribbon';
					$ret .= '" ';
					if( empty($args['with-frame']) || $args['with-frame'] == 'disable' ){
						if( !empty($args['frame-border-radius']) ){
							$css_atts['border-radius'] = $args['frame-border-radius'];
						}
						if( !empty($args['frame-shadow-size']['size']) && !empty($args['frame-shadow-color']) && !empty($args['frame-shadow-opacity']) ){
							$css_atts['background-shadow-size'] = $args['frame-shadow-size'];
							$css_atts['background-shadow-color'] = $args['frame-shadow-color'];
							$css_atts['background-shadow-opacity'] = $args['frame-shadow-opacity'];
						}
						if( !empty($css_atts) ){
							$ret .= tourmaster_esc_style($css_atts);
						}
					}
					
					$ret .= ' >';
					$ret .= '<a href="' . get_permalink() . '" >';
					$ret .= tourmaster_get_image($feature_image, $args['thumbnail-size']);
					$ret .= '</a>';
					$ret .= $price_html . $ribbon_html;

					if( !empty($with_category) ){
						$ret .= get_the_term_list(get_the_ID(), 'room_category', '<span class="tourmaster-thumbnail-category" >', '<span class="gdlr-core-sep">,</span> ' , '</span>' );
					}
					$ret .= '</div>';

				}

				return $ret;
			}

			// get ribbon
			function get_ribbon( $args = array() ){
				

				if( !empty($args['enable-ribbon']) && $args['enable-ribbon'] == 'disable' ) return '';

				$ribbon_text = get_post_meta(get_the_ID(), 'tourmaster-room-ribbon-text', true);
				$ribbon_color = get_post_meta(get_the_ID(), 'tourmaster-room-ribbon-color', true);

				$ret = '';
				if( !empty($ribbon_text) ){
					$ret  = '<div class="tourmaster-ribbon" ' . tourmaster_esc_style(array(
						'background-color' => $ribbon_color,
					)) .' >' . $ribbon_text . '</div>';
				}

				return $ret;
			}

			// tour rating
			function get_rating( $args, $with_text = true ){

				if( !empty($args['enable-rating']) && $args['enable-rating'] == 'disable' ) return '';

				$rating = get_post_meta(get_the_ID(), 'tourmaster-room-rating', true);
				if( empty($rating['reviewer']) ){ return ''; }
				
				if( empty($rating['score']) ){
					$ret  = '<div class="tourmaster-room-rating tourmaster-tour-rating-empty" ><span>0</span></div>';
				}else{
					$score = intval($rating['score']) / intval($rating['reviewer']);

					$ret  = '<div class="tourmaster-room-rating" >';
					$ret .= tourmaster_get_rating($score);

					if( $with_text === true ){
						$ret .= '<span class="tourmaster-room-rating-text" >';
						$ret .= $rating['reviewer'] . ' ';
						$ret .= (intval($rating['reviewer']) > 1)? esc_html__('Reviews', 'tourmaster'): esc_html__('Review', 'tourmaster');
						$ret .= '</span>';
					}
					$ret .= '</div>';
				}

				return $ret;

			}

			// tour price
			function get_price( $settings = array(), $with_bg = false ){

				if( !empty($settings['display-price']) && $settings['display-price'] == 'disable' ) return;

				$price_text = get_post_meta(get_the_ID(), 'tourmaster-room-price-text', true);

				$ret = '';
				
				if( !empty($price_text) ){
					$extra_class = '';
					$price_wrap_att = array();

					if( $with_bg ){
						$extra_class .= 'tourmaster-with-bg ';
						$price_wrap_att['border-radius'] = empty($settings['price-background-radius'])? '': $settings['price-background-radius'];

						$price_wrap_att['background-color'] = empty($settings['price-background-color'])? '': $settings['price-background-color'];
						if( !empty($settings['price-background-text-color']) ){
							$extra_class .= 'tourmaster-with-text-color ';
							$price_wrap_att['color'] = empty($settings['price-background-text-color'])? '': $settings['price-background-text-color'];
						}

					}else{
						$extra_class .= 'tourmaster-no-bg ';
					}

					if( empty($settings['enable-price-prefix']) || $settings['enable-price-prefix'] == 'enable' ){
						$price_prefix = get_post_meta(get_the_ID(), 'tourmaster-room-price-prefix', true);
					}
					if( empty($settings['enable-price-suffix']) || $settings['enable-price-suffix'] == 'enable' ){
						$price_suffix = get_post_meta(get_the_ID(), 'tourmaster-room-price-suffix', true);
					}
					$price_discount_text = get_post_meta(get_the_ID(), 'tourmaster-room-price-discount-text', true);
					$decimal_digit = empty($settings['price-decimal-digit'])? 0: $settings['price-decimal-digit'];

					$price_wrap_att['font-size'] = empty($settings['room-price-font-size'])? '': $settings['room-price-font-size'];
					$price_wrap_att['font-weight'] = empty($settings['room-price-font-weight'])? '': $settings['room-price-font-weight'];

					$ret .= '<div class="tourmaster-price-wrap ' . esc_attr($extra_class) . '" ' . gdlr_core_esc_style($price_wrap_att) . ' >';
					if( !empty($price_prefix) ){
						$ret .= '<span class="tourmaster-head" >' . esc_html__('From', 'tourmaster') . '</span>';
					}
					if( !empty($price_discount_text) ){
						$ret .= '<span class="tourmaster-price-discount" >';
						$ret .= tourmaster_money_format($price_discount_text, $decimal_digit);
						$ret .= '</span>';
					}
					if( !empty($price_text) ){
						$ret .= '<span class="tourmaster-price" >';
						$ret .= tourmaster_money_format($price_text, $decimal_digit);
						$ret .= '</span>';
					}
					if( !empty($price_suffix) ){
						$ret .= '<span class="tourmaster-tail" >' . $price_suffix . '</span>';
					}
					$ret .= '</div>';
				}
				
				return $ret;
			}

			// tour info
			function get_info($args = array() ){

				$ret = '';
				
				if( !empty($args['room-info']) ){
					foreach( $args['room-info'] as $type ){
						switch( $type ){
							case 'bed-type':
								$info = get_post_meta(get_the_ID(), 'tourmaster-room-bed-type', true);
								if( !empty($info) ){
									$ret .= '<div class="tourmaster-info tourmaster-info-' . esc_attr($type) . '" >';
									$ret .= '<i class="gdlr-icon-double-bed2" ></i>';
									$ret .= '<span class="tourmaster-tail" >' . $info . '</span>';
									$ret .= '</div>';
								} 
								break;
							case 'guest-amount':
								$info = get_post_meta(get_the_ID(), 'tourmaster-room-guest-amount', true);
								if( !empty($info) ){
									$ret .= '<div class="tourmaster-info tourmaster-info-' . esc_attr($type) . '" >';
									$ret .= '<i class="gdlr-icon-group" ></i>';
									$ret .= '<span class="tourmaster-tail" >' . $info . '</span>';
									$ret .= '</div>';
								} 
								break; 
							case 'room-size':
								$info = get_post_meta(get_the_ID(), 'tourmaster-room-size-text', true);
								if( !empty($info) ){
									$ret .= '<div class="tourmaster-info tourmaster-info-' . esc_attr($type) . '" >';
									$ret .= '<i class="gdlr-icon-resize" ></i>';
									$ret .= '<span class="tourmaster-tail" >' . $info . '</span>';
									$ret .= '</div>';
								} 
								break; 
							case 'custom-excerpt': 
								
						}
					}

					if( !empty($ret) ){
						$ret = '<div class="tourmaster-info-wrap clearfix" >' . $ret . '</div>';
					}

					if( in_array('custom-excerpt', $args['room-info']) ){
						$info = get_post_meta(get_the_ID(), 'tourmaster-room-custom-excerpt', true);
						if( !empty($info) ){
							$ret .= '<div class="tourmaster-custom-excerpt" >';
							$ret .= tourmaster_content_filter($info);
							$ret .= ' </div>';
						} 
					}
				}

				return $ret;
			}
			function get_info_location( $args ){
				$ret = '';

				if( !empty($args['room-info']) && in_array('location', $args['room-info']) ){
					$info = get_post_meta(get_the_ID(), 'tourmaster-room-location', true);
					if( !empty($info) ){
						$ret .= '<div class="tourmaster-location" >';
						$ret .= '<i class="icon-location-pin" ></i>';
						$ret .= tourmaster_text_filter($info);
						$ret .= ' </div>';
					} 
				}

				return $ret;
			} 

			// get button
			function get_button( $args, $button_text = '' ){
				
				if( empty($button_text) ){
					$button_text = esc_html__('Book Now', 'tourmaster');
				}

				$ret = '';
				if( empty($args['read-more-button']) || $args['read-more-button'] == 'text' ){
					$ret .= '<a class="tourmaster-read-more tourmaster-type-text" href="' . esc_attr(get_permalink()) . '" ' . gdlr_core_esc_style(array(
						'color' => empty($args['read-more-text-color'])? '': $args['read-more-text-color']
					)) . ' >' . $button_text . '<i class="icon-arrow-right" ></i></a>';
				}else if( $args['read-more-button'] == 'button' ){
					$ret .= '<a class="tourmaster-read-more tourmaster-type-button" href="' . esc_attr(get_permalink()) . '" ' . gdlr_core_esc_style(array(
						'color' => empty($args['read-more-text-color'])? '': $args['read-more-text-color'],
						'background-color' => empty($args['read-more-background-color'])? '': $args['read-more-background-color']
					)) . ' >' . $button_text . '</a>';
				}else if( $args['read-more-button'] == 'border-button' ){
					$ret .= '<a class="tourmaster-read-more tourmaster-type-border-button" href="' . esc_attr(get_permalink()) . '" ' . gdlr_core_esc_style(array(
						'color' => empty($args['read-more-text-color'])? '': $args['read-more-text-color'],
						'border-color' => empty($args['read-more-background-color'])? '': $args['read-more-background-color']
					)) . ' >' . $button_text . '</a>';
				}

				return $ret;
			} 
			
			// tour grid
			function room_grid( $args ){
				
				$extra_class  = '';

				$inner_class  = '';
				$inner_atts = array();

				$content_class = '';
				$content_atts = array();
				$content_sync_height = '';

				if( !empty($args['with-frame']) && $args['with-frame'] == 'enable' ){
					$extra_class = 'tourmaster-grid-frame ';

					// inner section
					if( !empty($args['enable-move-up-shadow-effect']) && $args['enable-move-up-shadow-effect'] == 'enable' ){
						$inner_class .= 'gdlr-core-move-up-with-shadow gdlr-core-outer-frame-element ';
					}
					$inner_atts = array(
						'border-width' => ( empty($args['frame-border-size']) || $args['frame-border-size'] == array('top'=>'', 'right'=>'', 'bottom'=>'', 'left'=>'', 'settings'=>'link') )? '': $args['frame-border-size'],
						'border-color' => empty($args['frame-border-color'])? '': $args['frame-border-color'],
					);
					if( !empty($args['frame-border-radius']) ){
						$inner_atts['border-radius'] = $args['frame-border-radius'];
					}
					if( !empty($args['frame-shadow-size']['size']) && !empty($args['frame-shadow-color']) && !empty($args['frame-shadow-opacity']) ){
						$inner_atts['background-shadow-size'] = $args['frame-shadow-size'];
						$inner_atts['background-shadow-color'] = $args['frame-shadow-color'];
						$inner_atts['background-shadow-opacity'] = $args['frame-shadow-opacity'];
					}

					// content section
					$content_class = 'gdlr-core-skin-e-background ';
					$content_atts['padding'] = empty($args['frame-padding'])? '': $args['frame-padding']; 
					if( !empty($args['layout']) && $args['layout'] != 'masonry' ){
						global $tourmaster_room_item_id;
						$content_class .= 'gdlr-core-js ';
						$content_sync_height = 'room-item-' . esc_attr($tourmaster_room_item_id);
					}
				}
				
				$ret  = '<div class="tourmaster-room-grid ' . esc_attr($extra_class) . '"  ' . tourmaster_esc_style(array(
					'margin-bottom' => empty($args['room-list-bottom-margin'])? '': $args['room-list-bottom-margin']
				)) . ' >';
				$ret .= '<div class="tourmaster-room-grid-inner ' . esc_attr($inner_class) . '" ' . tourmaster_esc_style($inner_atts) . ' >';
				$ret .= $this->get_thumbnail($args, true, true);
				$ret .= '<div class="tourmaster-room-content-wrap ' . esc_attr($content_class) . '" ';
				$ret .= empty($content_sync_height)? '': 'data-sync-height="' . esc_attr($content_sync_height) . '" ';
				$ret .= tourmaster_esc_style($content_atts) . ' >';

				$ret .= $this->room_title($args);
				$ret .= $this->get_info($args);
				$ret .= $this->room_excerpt($args);
				$ret .= $this->get_rating($args);
				$ret .= $this->get_info_location($args);
				$ret .= $this->get_button($args);

				$ret .= '</div>'; // tourmaster-room-content-wrap
				$ret .= '</div>'; // tourmaster-room-grid-inner
				$ret .= '</div>'; // tourmaster-room-grid
				
				return $ret;
			} 	
			
			function room_grid2( $args ){
				
				$extra_class  = '';

				$inner_class  = '';
				$inner_atts = array();

				$content_class = '';
				$content_atts = array();
				$content_sync_height = '';

				if( !empty($args['with-frame']) && $args['with-frame'] == 'enable' ){
					$extra_class = 'tourmaster-grid-frame ';

					// inner section
					if( !empty($args['enable-move-up-shadow-effect']) && $args['enable-move-up-shadow-effect'] == 'enable' ){
						$inner_class .= 'gdlr-core-move-up-with-shadow gdlr-core-outer-frame-element ';
					}
					$inner_atts = array(
						'border-width' => ( empty($args['frame-border-size']) || $args['frame-border-size'] == array('top'=>'', 'right'=>'', 'bottom'=>'', 'left'=>'', 'settings'=>'link') )? '': $args['frame-border-size'],
						'border-color' => empty($args['frame-border-color'])? '': $args['frame-border-color'],
					);
					if( !empty($args['frame-border-radius']) ){
						$inner_atts['border-radius'] = $args['frame-border-radius'];
					}
					if( !empty($args['frame-shadow-size']['size']) && !empty($args['frame-shadow-color']) && !empty($args['frame-shadow-opacity']) ){
						$inner_atts['background-shadow-size'] = $args['frame-shadow-size'];
						$inner_atts['background-shadow-color'] = $args['frame-shadow-color'];
						$inner_atts['background-shadow-opacity'] = $args['frame-shadow-opacity'];
					}

					// content section
					$content_class = 'gdlr-core-skin-e-background ';
					$content_atts['padding'] = empty($args['frame-padding'])? '': $args['frame-padding']; 
					if( !empty($args['layout']) && $args['layout'] != 'masonry' ){
						global $tourmaster_room_item_id;
						$content_class .= 'gdlr-core-js ';
						$content_sync_height = 'room-item-' . esc_attr($tourmaster_room_item_id);
					}
				}
				
				$ret  = '<div class="tourmaster-room-grid2 ' . esc_attr($extra_class) . '" ' . tourmaster_esc_style(array(
					'margin-bottom' => empty($args['room-list-bottom-margin'])? '': $args['room-list-bottom-margin']
				)) . ' >';
				$ret .= '<div class="tourmaster-room-grid-inner ' . esc_attr($inner_class) . '" ' . tourmaster_esc_style($inner_atts) . ' >';
				$ret .= $this->get_thumbnail($args, false, true);
				$ret .= '<div class="tourmaster-room-content-wrap ' . esc_attr($content_class) . '" ';
				$ret .= empty($content_sync_height)? '': 'data-sync-height="' . esc_attr($content_sync_height) . '" ';
				$ret .= tourmaster_esc_style($content_atts) . ' >';

				$ret .= $this->room_title($args);
				$ret .= $this->get_info($args);
				$ret .= $this->room_excerpt($args);
				$ret .= $this->get_rating($args);
				$ret .= $this->get_info_location($args);

				$ret .= '<div class="tourmaster-bottom clearfix" >';
				$ret .= $this->get_price($args);
				$ret .= $this->get_button($args);
				$ret .= '</div>';
				$ret .= '</div>'; // tourmaster-room-content-wrap
				$ret .= '</div>'; // tourmaster-room-grid-inner
				$ret .= '</div>'; // tourmaster-room-grid
				
				return $ret;
			} 	
			
			function room_grid3( $args ){
				
				$extra_class  = '';

				$inner_class  = '';
				$inner_atts = array();

				$content_class = '';
				$content_atts = array();
				$content_sync_height = '';

				if( !empty($args['with-frame']) && $args['with-frame'] == 'enable' ){
					$extra_class = 'tourmaster-grid-frame ';

					// inner section
					if( !empty($args['enable-move-up-shadow-effect']) && $args['enable-move-up-shadow-effect'] == 'enable' ){
						$inner_class .= 'gdlr-core-move-up-with-shadow gdlr-core-outer-frame-element ';
					}
					$inner_atts = array(
						'border-width' => ( empty($args['frame-border-size']) || $args['frame-border-size'] == array('top'=>'', 'right'=>'', 'bottom'=>'', 'left'=>'', 'settings'=>'link') )? '': $args['frame-border-size'],
						'border-color' => empty($args['frame-border-color'])? '': $args['frame-border-color'],
					);
					if( !empty($args['frame-border-radius']) ){
						$inner_atts['border-radius'] = $args['frame-border-radius'];
					}
					if( !empty($args['frame-shadow-size']['size']) && !empty($args['frame-shadow-color']) && !empty($args['frame-shadow-opacity']) ){
						$inner_atts['background-shadow-size'] = $args['frame-shadow-size'];
						$inner_atts['background-shadow-color'] = $args['frame-shadow-color'];
						$inner_atts['background-shadow-opacity'] = $args['frame-shadow-opacity'];
					}

					// content section
					$content_class = 'gdlr-core-skin-e-background ';
					$content_atts['padding'] = empty($args['frame-padding'])? '': $args['frame-padding']; 
					if( !empty($args['layout']) && $args['layout'] != 'masonry' ){
						global $tourmaster_room_item_id;
						$content_class .= 'gdlr-core-js ';
						$content_sync_height = 'room-item-' . esc_attr($tourmaster_room_item_id);
					}
				}
				
				$ret  = '<div class="tourmaster-room-grid3 ' . esc_attr($extra_class) . '" ' . tourmaster_esc_style(array(
					'margin-bottom' => empty($args['room-list-bottom-margin'])? '': $args['room-list-bottom-margin']
				)) . ' >';
				$ret .= '<div class="tourmaster-room-grid-inner ' . esc_attr($inner_class) . '" ' . tourmaster_esc_style($inner_atts) . ' >';
				$ret .= $this->get_thumbnail($args, false, true);
				$ret .= '<div class="tourmaster-room-content-wrap ' . esc_attr($content_class) . '" ';
				$ret .= empty($content_sync_height)? '': 'data-sync-height="' . esc_attr($content_sync_height) . '" ';
				$ret .= tourmaster_esc_style($content_atts) . ' >';
				
				$ret .= $this->get_price($args, true);
				$ret .= $this->room_title($args);
				$ret .= $this->get_info($args);
				$ret .= $this->room_excerpt($args);
				$ret .= $this->get_rating($args);
				$ret .= $this->get_info_location($args);
				$ret .= $this->get_button($args);

				$ret .= '</div>'; // tourmaster-room-content-wrap
				$ret .= '</div>'; // tourmaster-room-grid-inner
				$ret .= '</div>'; // tourmaster-room-grid
				
				return $ret;
			} 	

			function room_grid4( $args ){
				
				$extra_class  = '';

				$inner_class  = '';
				$inner_atts = array();

				$content_class = '';
				$content_atts = array();
				$content_sync_height = '';

				if( !empty($args['with-frame']) && $args['with-frame'] == 'enable' ){
					$extra_class = 'tourmaster-grid-frame ';

					// inner section
					if( !empty($args['enable-move-up-shadow-effect']) && $args['enable-move-up-shadow-effect'] == 'enable' ){
						$inner_class .= 'gdlr-core-move-up-with-shadow gdlr-core-outer-frame-element ';
					}
					$inner_atts = array(
						'border-width' => ( empty($args['frame-border-size']) || $args['frame-border-size'] == array('top'=>'', 'right'=>'', 'bottom'=>'', 'left'=>'', 'settings'=>'link') )? '': $args['frame-border-size'],
						'border-color' => empty($args['frame-border-color'])? '': $args['frame-border-color'],
					);
					if( !empty($args['frame-border-radius']) ){
						$inner_atts['border-radius'] = $args['frame-border-radius'];
					}
					if( !empty($args['frame-shadow-size']['size']) && !empty($args['frame-shadow-color']) && !empty($args['frame-shadow-opacity']) ){
						$inner_atts['background-shadow-size'] = $args['frame-shadow-size'];
						$inner_atts['background-shadow-color'] = $args['frame-shadow-color'];
						$inner_atts['background-shadow-opacity'] = $args['frame-shadow-opacity'];
					}

					// content section
					$content_class = 'gdlr-core-skin-e-background ';
					$content_atts['padding'] = empty($args['frame-padding'])? '': $args['frame-padding']; 
					if( !empty($args['layout']) && $args['layout'] != 'masonry' ){
						global $tourmaster_room_item_id;
						$content_class .= 'gdlr-core-js ';
						$content_sync_height = 'room-item-' . esc_attr($tourmaster_room_item_id);
					}
				}
				
				$ret  = '<div class="tourmaster-room-grid4 ' . esc_attr($extra_class) . '" ' . tourmaster_esc_style(array(
					'margin-bottom' => empty($args['room-list-bottom-margin'])? '': $args['room-list-bottom-margin']
				)) . ' >';
				$ret .= '<div class="tourmaster-room-grid-inner ' . esc_attr($inner_class) . '" ' . tourmaster_esc_style($inner_atts) . ' >';
				$ret .= $this->get_thumbnail($args, true, true);
				$ret .= '<div class="tourmaster-room-content-wrap ' . esc_attr($content_class) . '" ';
				$ret .= empty($content_sync_height)? '': 'data-sync-height="' . esc_attr($content_sync_height) . '" ';
				$ret .= tourmaster_esc_style($content_atts) . ' >';
				
				$ret .= $this->room_title($args);
				$ret .= $this->get_rating($args);
				$ret .= $this->get_info($args);
				$ret .= $this->room_excerpt($args);
				$ret .= $this->get_info_location($args);
				$ret .= $this->get_button($args, esc_html__('Check Details', 'tourmaster'));

				$ret .= '</div>'; // tourmaster-room-content-wrap
				$ret .= '</div>'; // tourmaster-room-grid-inner
				$ret .= '</div>'; // tourmaster-room-grid
				
				return $ret;
			} 	

			function room_grid5( $args ){
				
				$extra_class  = '';

				$inner_class  = '';
				$inner_atts = array();

				$content_class = '';
				$content_atts = array();
				$content_sync_height = '';

				if( !empty($args['with-frame']) && $args['with-frame'] == 'enable' ){
					$extra_class = 'tourmaster-grid-frame ';

					// inner section
					if( !empty($args['enable-move-up-shadow-effect']) && $args['enable-move-up-shadow-effect'] == 'enable' ){
						$inner_class .= 'gdlr-core-move-up-with-shadow gdlr-core-outer-frame-element ';
					}
					$inner_atts = array(
						'border-width' => ( empty($args['frame-border-size']) || $args['frame-border-size'] == array('top'=>'', 'right'=>'', 'bottom'=>'', 'left'=>'', 'settings'=>'link') )? '': $args['frame-border-size'],
						'border-color' => empty($args['frame-border-color'])? '': $args['frame-border-color'],
					);
					if( !empty($args['frame-border-radius']) ){
						$inner_atts['border-radius'] = $args['frame-border-radius'];
					}
					if( !empty($args['frame-shadow-size']['size']) && !empty($args['frame-shadow-color']) && !empty($args['frame-shadow-opacity']) ){
						$inner_atts['background-shadow-size'] = $args['frame-shadow-size'];
						$inner_atts['background-shadow-color'] = $args['frame-shadow-color'];
						$inner_atts['background-shadow-opacity'] = $args['frame-shadow-opacity'];
					}

					// content section
					$content_class = 'gdlr-core-skin-e-background ';
					$content_atts['padding'] = empty($args['frame-padding'])? '': $args['frame-padding']; 
					if( !empty($args['layout']) && $args['layout'] != 'masonry' ){
						global $tourmaster_room_item_id;
						$content_class .= 'gdlr-core-js ';
						$content_sync_height = 'room-item-' . esc_attr($tourmaster_room_item_id);
					}
				}
				
				$ret  = '<div class="tourmaster-room-grid5 ' . esc_attr($extra_class) . '" ' . tourmaster_esc_style(array(
					'margin-bottom' => empty($args['room-list-bottom-margin'])? '': $args['room-list-bottom-margin']
				)) . ' >';
				$ret .= '<div class="tourmaster-room-grid-inner ' . esc_attr($inner_class) . '" ' . tourmaster_esc_style($inner_atts) . ' >';
				$ret .= $this->get_thumbnail($args, true, true, true);
				$ret .= '<div class="tourmaster-room-content-wrap ' . esc_attr($content_class) . '" ';
				$ret .= empty($content_sync_height)? '': 'data-sync-height="' . esc_attr($content_sync_height) . '" ';
				$ret .= tourmaster_esc_style($content_atts) . ' >';
				
				$ret .= $this->room_title($args);
				$ret .= $this->get_rating($args);
				$ret .= $this->get_info($args);
				$ret .= $this->room_excerpt($args);
				$ret .= $this->get_info_location($args);
				$ret .= $this->get_button($args, esc_html__('Check Details', 'tourmaster'));

				$ret .= '</div>'; // tourmaster-room-content-wrap
				$ret .= '</div>'; // tourmaster-room-grid-inner
				$ret .= '</div>'; // tourmaster-room-grid
				
				return $ret;
			} 

			function room_modern( $args ){
				
				$extra_class = empty(get_post_thumbnail_id())? 'tourmaster-without-thumbnail': 'tourmaster-with-thumbnail ';

				// content section
				$content_class = 'gdlr-core-skin-e-background ';
				$content_atts = array(
					'border-width' => ( empty($args['frame-border-size']) || $args['frame-border-size'] == array('top'=>'', 'right'=>'', 'bottom'=>'', 'left'=>'', 'settings'=>'link') )? '': $args['frame-border-size'],
					'border-color' => empty($args['frame-border-color'])? '': $args['frame-border-color'],
					'padding' => empty($args['frame-padding'])? '': $args['frame-padding']
				);
				if( !empty($args['frame-border-radius']) ){
					$content_atts['border-radius'] = $args['frame-border-radius'];
				}
				if( !empty($args['frame-shadow-size']['size']) && !empty($args['frame-shadow-color']) && !empty($args['frame-shadow-opacity']) ){
					$content_atts['background-shadow-size'] = $args['frame-shadow-size'];
					$content_atts['background-shadow-color'] = $args['frame-shadow-color'];
					$content_atts['background-shadow-opacity'] = $args['frame-shadow-opacity'];
				}
				
				$ret  = '<div class="tourmaster-room-modern ' . esc_attr($extra_class) . '" ' . tourmaster_esc_style(array(
					'margin-bottom' => empty($args['room-list-bottom-margin'])? '': $args['room-list-bottom-margin']
				)) . ' >';
				$ret .= $this->get_thumbnail($args, false, false);
				
				$ret .= '<div class="tourmaster-room-content-wrap ' . esc_attr($content_class) . '" ';
				$ret .= tourmaster_esc_style($content_atts) . ' >';
				
				$ret .= $this->room_title($args);
				$ret .= $this->get_info($args);
				$ret .= $this->room_excerpt($args);
				$ret .= $this->get_rating($args);
				$ret .= $this->get_info_location($args);
				$ret .= $this->get_button($args);

				$ret .= '</div>'; // tourmaster-room-content-wrap
				$ret .= '</div>'; // tourmaster-room-grid
				
				return $ret;
			} 	

			function room_modern2( $args ){
				
				$title_css = array();
				if( !empty($args['overlay-content-padding']['bottom']) ){
					$title_css['transform'] = 'translateY(calc(-100% - ' . $args['overlay-content-padding']['bottom'] . '))';
					$title_css['-webkit-transform'] = $title_css['transform'];
				}


				$ret  = '<div class="tourmaster-room-modern2" ' . tourmaster_esc_style(array(
					'margin-bottom' => empty($args['room-list-bottom-margin'])? '': $args['room-list-bottom-margin'],
					'margin-left' => empty($args['room-modern2-side-margin'])? '': $args['room-modern2-side-margin'],
					'margin-right' => empty($args['room-modern2-side-margin'])? '': $args['room-modern2-side-margin']
				)) . ' >';
				$ret .= $this->get_thumbnail($args, false, true);
				if( !empty($args['overlay-hover-opacity']) ){
					$ret .= '<div class="tourmaster-room-overlay" ><div ' . gdlr_core_esc_style(array(
						'opacity' => $args['overlay-hover-opacity']
					)) . '></div></div>';
				}
				$ret .= '<div class="tourmaster-room-content-wrap" ' . gdlr_core_esc_style(array(
					'padding' => empty($args['overlay-content-padding'])? '': $args['overlay-content-padding']
				)) . ' >';
				$ret .= $this->room_title($args, '', '', $title_css);

				$ret .= '<div class="tourmaster-bottom" >';
				$ret .= $this->get_rating($args);
				$ret .= $this->get_price($args);
				$ret .= '</div>';
				$ret .= '</div>'; // tourmaster-room-content-wrap
				$ret .= '</div>'; // tourmaster-room-modern2
				
				return $ret;
			} 	

			function room_side_thumbnail( $args ){
				
				$extra_class  = '';

				$inner_class  = '';
				$inner_atts = array();

				$content_class = '';
				$content_atts = array();
				$content_sync_height = '';

				// inner section
				$inner_atts = array(
					'border-width' => ( empty($args['frame-border-size']) || $args['frame-border-size'] == array('top'=>'', 'right'=>'', 'bottom'=>'', 'left'=>'', 'settings'=>'link') )? '': $args['frame-border-size'],
					'border-color' => empty($args['frame-border-color'])? '': $args['frame-border-color'],
				);
				if( !empty($args['frame-border-radius']) ){
					$inner_atts['border-radius'] = $args['frame-border-radius'];
				}
				if( !empty($args['frame-shadow-size']['size']) && !empty($args['frame-shadow-color']) && !empty($args['frame-shadow-opacity']) ){
					$inner_atts['background-shadow-size'] = $args['frame-shadow-size'];
					$inner_atts['background-shadow-color'] = $args['frame-shadow-color'];
					$inner_atts['background-shadow-opacity'] = $args['frame-shadow-opacity'];
				}

				// content section
				$content_class = 'gdlr-core-skin-e-background ';
				$content_atts['padding'] = empty($args['frame-padding'])? '': $args['frame-padding']; 

				$ret  = '<div class="tourmaster-room-side-thumbnail ' . esc_attr($extra_class) . '" ' . tourmaster_esc_style(array(
					'margin-bottom' => empty($args['room-list-bottom-margin'])? '': $args['room-list-bottom-margin']
				)) . ' >';
				$ret .= '<div class="tourmaster-room-side-thumbnail-inner ' . esc_attr($inner_class) . '" ' . tourmaster_esc_style($inner_atts) . ' >';
				$ret .= $this->get_thumbnail($args, false, true);
				$ret .= '<div class="tourmaster-room-content-wrap ' . esc_attr($content_class) . '" ';
				$ret .= tourmaster_esc_style($content_atts) . ' >';
				
				$ret .= $this->room_title($args);
				$ret .= $this->get_info($args);
				$ret .= $this->room_excerpt($args);
				$ret .= $this->get_rating($args);
				$ret .= $this->get_info_location($args);

				$ret .= '<div class="tourmaster-bottom" >';
				$ret .= $this->get_button($args);
				$ret .= $this->get_price($args);
				$ret .= '</div>';
				$ret .= '</div>'; // tourmaster-room-content-wrap
				$ret .= '</div>'; // tourmaster-room-grid-inner
				$ret .= '</div>'; // tourmaster-room-grid
				
				return $ret;
			} 	

		} // tourmaster_tour_style
	} // class_exists
	