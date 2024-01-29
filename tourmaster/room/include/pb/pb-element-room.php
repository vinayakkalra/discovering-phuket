<?php
	/*	
	*	Goodlayers Item For Page Builder
	*/

	add_action('plugins_loaded', 'tourmaster_add_pb_element_room');
	if( !function_exists('tourmaster_add_pb_element_room') ){
		function tourmaster_add_pb_element_room(){

			if( class_exists('gdlr_core_page_builder_element') ){
				gdlr_core_page_builder_element::add_element('room', 'tourmaster_pb_element_room'); 
			}
			
		}
	}
	
	if( !class_exists('tourmaster_pb_element_room') ){
		class tourmaster_pb_element_room{
			
			// get the element settings
			static function get_settings(){
				return array(
					'icon' => 'fa-plane',
					'title' => esc_html__('Room', 'tourmaster')
				);
			}

			// list all custom taxonomy
			static function get_tax_option_list(){
				
				$ret = array();

				$tax_fields = array();
				$tax_fields = $tax_fields + tourmaster_get_custom_tax_list('room');
				foreach( $tax_fields as $tax_field => $tax_title ){
					$ret[$tax_field] = array(
						'title' => $tax_title,
						'type' => 'multi-combobox',
						'options' => tourmaster_get_term_list($tax_field),
					);
				}

				return $ret;
			}
			
			// return the element options
			static function get_options(){
				return apply_filters('tourmaster_room_item_options', array(					
					'general' => array(
						'title' => esc_html__('General', 'tourmaster'),
						'options' => array(
							'relation' => array(
								'title' => esc_html__('Relation (Category & Tag & Tax)', 'tourmaster'),
								'type' => 'combobox',
								'options' => array(
									'or' => esc_html__('OR', 'tourmaster'),
									'and' => esc_html__('AND', 'tourmaster')
								)
							),
							'category' => array(
								'title' => esc_html__('Category', 'tourmaster'),
								'type' => 'multi-combobox',
								'options' => tourmaster_get_term_list('room_category'),
								'description' => esc_html__('You can use Ctrl/Command button to select multiple items or remove the selected item. Leave this field blank to select all items in the list.', 'tourmaster'),
							),
							'tag' => array(
								'title' => esc_html__('Tag', 'tourmaster'),
								'type' => 'multi-combobox',
								'options' => tourmaster_get_term_list('room_tag')
							),
						) + self::get_tax_option_list() + array(
							'num-fetch' => array(
								'title' => esc_html__('Num Fetch', 'tourmaster'),
								'type' => 'text',
								'data-input-type' => 'number',
								'default' => 9,
								'description' => esc_html__('The number of posts showing on the item', 'tourmaster')
							),
							'orderby' => array(
								'title' => esc_html__('Order By', 'tourmaster'),
								'type' => 'combobox',
								'options' => array(
									'date' => esc_html__('Publish Date', 'tourmaster'), 
									'title' => esc_html__('Title', 'tourmaster'), 
									'rand' => esc_html__('Random', 'tourmaster'), 
									'menu_order' => esc_html__('Menu Order', 'tourmaster'), 
									'rating' => esc_html__('Rating ( Score )', 'tourmaster'), 
								)
							),
							'order' => array(
								'title' => esc_html__('Order', 'tourmaster'),
								'type' => 'combobox',
								'options' => array(
									'desc'=>esc_html__('Descending Order', 'tourmaster'), 
									'asc'=> esc_html__('Ascending Order', 'tourmaster'), 
								)
							),
							'pagination' => array(
								'title' => esc_html__('Pagination', 'tourmaster'),
								'type' => 'combobox',
								'options' => array(
									'none'=>esc_html__('None', 'tourmaster'), 
									'page'=>esc_html__('Page', 'tourmaster'), 
									'load-more'=>esc_html__('Load More', 'tourmaster'), 
								),
								'description' => esc_html__('Pagination is not supported and will be automatically disabled on carousel layout.', 'tourmaster'),
							),
							'pagination-style' => array(
								'title' => esc_html__('Pagination Style', 'tourmaster'),
								'type' => 'combobox',
								'options' => array(
									'default' => esc_html__('Default', 'tourmaster'),
									'plain' => esc_html__('Plain', 'tourmaster'),
									'rectangle' => esc_html__('Rectangle', 'tourmaster'),
									'rectangle-border' => esc_html__('Rectangle Border', 'tourmaster'),
									'round' => esc_html__('Round', 'tourmaster'),
									'round-border' => esc_html__('Round Border', 'tourmaster'),
									'circle' => esc_html__('Circle', 'tourmaster'),
									'circle-border' => esc_html__('Circle Border', 'tourmaster'),
								),
								'default' => 'default',
								'condition' => array( 'pagination' => 'page' )
							),
							'pagination-align' => array(
								'title' => esc_html__('Pagination Alignment', 'tourmaster'),
								'type' => 'radioimage',
								'options' => 'text-align',
								'with-default' => true,
								'default' => 'default',
								'condition' => array( 'pagination' => 'page' )
							),
							'exclude-self' => array(
								'title' => esc_html__('Exclude Self', 'tourmaster'),
								'type' => 'checkbox',
								'default' => 'enable'
							)
							
						),
					),
					'settings' => array(
						'title' => esc_html('Room Style', 'tourmaster'),
						'options' => array(
							'room-style' => array(
								'title' => esc_html__('Room Style', 'tourmaster'),
								'type' => 'combobox',
								'options' => array(
									'grid' => esc_html__('Grid', 'tourmaster'),
									'grid2' => esc_html__('Grid 2', 'tourmaster'),
									'grid3' => esc_html__('Grid 3', 'tourmaster'),
									'grid4' => esc_html__('Grid 4', 'tourmaster'),
									'grid5' => esc_html__('Grid 5', 'tourmaster'),
									'modern' => esc_html__('Modern', 'tourmaster'),
									'modern2' => esc_html__('Modern 2', 'tourmaster'),
									'side-thumbnail' => esc_html__('Side Thumbnail', 'tourmaster')
								),
								'default' => 'grid'
							),
							'overlay-hover-opacity' => array(
								'title' => esc_html__('Overlay Hover Opacity', 'tourmaster'),
								'type' => 'text',
								'description' => esc_html__('Fill the number between 0.01 to 1', 'tourmaster'),
								'condition' => array('room-style' => 'modern2')
							),
							'overlay-content-padding' => array(
								'title' => esc_html__('Content Padding', 'tourmaster'),
								'type' => 'custom',
								'item-type' => 'padding',
								'data-input-type' => 'pixel',
								'default' => array( 'top'=>'', 'right'=>'', 'bottom'=>'', 'left'=>'', 'settings'=>'unlink' ),
								'condition' => array('room-style' => 'modern2')
							),
							'with-frame' => array(
								'title' => esc_html__('With Frame', 'tourmaster'),
								'type' => 'checkbox',
								'default' => 'disable',
								'condition' => array( 'room-style' => array('grid', 'grid2', 'grid3', 'grid4', 'grid5') )
							),
							'column-size' => array(
								'title' => esc_html__('Column Size', 'tourmaster'),
								'type' => 'combobox',
								'options' => array( 60=>1, 30=>2, 20=>3, 15=>4, 12=>5 ),
								'default' => 20,
								'condition' => array( 'room-style' => array('grid', 'grid2', 'grid3', 'grid4', 'grid5', 'modern', 'modern2') )
							),
							'thumbnail-size' => array(
								'title' => esc_html__('Thumbnail Size', 'tourmaster'),
								'type' => 'combobox',
								'options' => 'thumbnail-size',
							),
							'enable-thumbnail-zoom-on-hover' => array(
								'title' => esc_html__('Thumbnail Zoom on Hover', 'tourmaster'),
								'type' => 'checkbox',
								'default' => 'disable'
							),
							'read-more-button' => array(
								'title' => esc_html__('Read More Button', 'tourmaster'),
								'type' => 'combobox',
								'options' => array(
									'none' => esc_html__('None', 'tourmaster'),
									'text' => esc_html__('Text', 'tourmaster'),
									'button' => esc_html__('Button', 'tourmaster'),
									'border-button' => esc_html__('Border Button', 'tourmaster'),
								),
								'default' => 'text'
							),
							'layout' => array(
								'title' => esc_html__('Layout', 'tourmaster'),
								'type' => 'combobox',
								'options' => array( 
									'fitrows' => esc_html__('Fit Rows', 'tourmaster'),
									'carousel' => esc_html__('Carousel', 'tourmaster'),
									'masonry' => esc_html__('Masonry', 'tourmaster'),
								),
								'default' => 'fitrows',
							),
							'carousel-item-margin' => array(
								'title' => esc_html__('Carousel Item Margin', 'tourmaster'),
								'type' => 'text',
								'data-input-type' => 'pixel',
								'condition' => array( 'layout' => 'carousel' )
							),
							'carousel-overflow' => array(
								'title' => esc_html__('Carousel Overflow', 'tourmaster'),
								'type' => 'combobox',
								'options' => array(
									'' => esc_html__('Hidden', 'tourmaster'),
									'visible' => esc_html__('Visible', 'tourmaster')
								),
							),
							'carousel-scrolling-item-amount' => array(
								'title' => esc_html__('Carousel Scrolling Item Amount', 'tourmaster'),
								'type' => 'text',
								'default' => '1',
							),
							'carousel-autoslide' => array(
								'title' => esc_html__('Autoslide Carousel', 'tourmaster'),
								'type' => 'checkbox',
								'default' => 'enable',
							),
							'carousel-start-at' => array(
								'title' => esc_html__('Carousel Start At (Number)', 'tourmaster'),
								'type' => 'text',
								'default' => '',
							),
							'carousel-navigation' => array(
								'title' => esc_html__('Carousel Navigation', 'tourmaster'),
								'type' => 'combobox',
								'options' => (function_exists('gdlr_core_get_flexslider_navigation_types')? gdlr_core_get_flexslider_navigation_types(): array()),
								'default' => 'navigation',
							),
							'carousel-navigation-show-on-hover' => array(
								'title' => esc_html__('Carousel Navigation Display On Hover', 'tourmaster'),
								'type' => 'checkbox',
								'default' => 'disable',
								'condition' => array( 'carousel-navigation' => array('navigation-outer', 'navigation-inner') )
							),
							'carousel-navigation-align' => (function_exists('gdlr_core_get_flexslider_navigation_align')? gdlr_core_get_flexslider_navigation_align(): array()),
							'carousel-navigation-left-icon' => (function_exists('gdlr_core_get_flexslider_navigation_left_icon')? gdlr_core_get_flexslider_navigation_left_icon(): array()),
							'carousel-navigation-right-icon' => (function_exists('gdlr_core_get_flexslider_navigation_right_icon')? gdlr_core_get_flexslider_navigation_right_icon(): array()),
							'carousel-navigation-icon-color' => (function_exists('gdlr_core_get_flexslider_navigation_icon_color')? gdlr_core_get_flexslider_navigation_icon_color(): array()),
							'carousel-navigation-icon-hover-color' => (function_exists('gdlr_core_get_flexslider_navigation_icon_hover_color')? gdlr_core_get_flexslider_navigation_icon_hover_color(): array()),
							'carousel-navigation-icon-bg' => (function_exists('gdlr_core_get_flexslider_navigation_icon_background')? gdlr_core_get_flexslider_navigation_icon_background(): array()),
							'carousel-navigation-icon-padding' => (function_exists('gdlr_core_get_flexslider_navigation_icon_padding')? gdlr_core_get_flexslider_navigation_icon_padding(): array()),
							'carousel-navigation-icon-radius' => (function_exists('gdlr_core_get_flexslider_navigation_icon_radius')? gdlr_core_get_flexslider_navigation_icon_radius(): array()),
							'carousel-navigation-size' => (function_exists('gdlr_core_get_flexslider_navigation_icon_size')? gdlr_core_get_flexslider_navigation_icon_size(): array()),
							'carousel-navigation-margin' => (function_exists('gdlr_core_get_flexslider_navigation_margin')? gdlr_core_get_flexslider_navigation_margin(): array()),
							'carousel-navigation-side-margin' => (function_exists('gdlr_core_get_flexslider_navigation_side_margin')? gdlr_core_get_flexslider_navigation_side_margin(): array()),
							'carousel-navigation-icon-margin' => (function_exists('gdlr_core_get_flexslider_navigation_icon_margin')? gdlr_core_get_flexslider_navigation_icon_margin(): array()),
							'carousel-bullet-style' => array(
								'title' => esc_html__('Carousel Bullet Style', 'tourmaster'),
								'type' => 'radioimage',
								'options' => (function_exists('gdlr_core_get_flexslider_bullet_itypes')? gdlr_core_get_flexslider_bullet_itypes(): array()),
								'condition' => array( 'layout' => 'carousel', 'carousel-navigation' => array('bullet','both') ),
								'wrapper-class' => 'gdlr-core-fullsize'
							),
							'carousel-bullet-top-margin' => array(
								'title' => esc_html__('Carousel Bullet Top Margin', 'tourmaster'),
								'type' => 'text',
								'data-input-type' => 'pixel',
								'condition' => array( 'layout' => 'carousel', 'carousel-navigation' => array('bullet','both') )
							),
							'display-price' => array(
								'title' => esc_html__('Display Price', 'tourmaster'),
								'type' => 'checkbox',
								'default' => 'enable',
								'condition' => array( 'room-style' => array('grid', 'grid2', 'grid3', 'grid4', 'grid5', 'side-thumbnail') )
							),
							'enable-price-prefix' => array(
								'title' => esc_html__('Enable Price Prefix', 'tourmaster'),
								'type' => 'checkbox',
								'default' => 'enable',
								'condition' => array( 'display-price' => 'enable' )
							),
							'enable-price-suffix' => array(
								'title' => esc_html__('Enable Price Suffix', 'tourmaster'),
								'type' => 'checkbox',
								'default' => 'enable',
								'condition' => array( 'display-price' => 'enable' )
							),
							'price-decimal-digit' => array(
								'title' => esc_html__('Price Decimal Digit', 'tourmaster'),
								'type' => 'text',
								'condition' => array( 'display-price' => 'enable', 'room-style' => array('grid', 'grid2', 'grid3', 'grid4', 'grid5', 'side-thumbnail') )
							),
							'display-ribbon' => array(
								'title' => esc_html__('Display Ribbon', 'tourmaster'),
								'type' => 'checkbox',
								'default' => 'enable',
								'condition' => array( 'room-style' => array('grid', 'grid2', 'grid3', 'grid4', 'grid5', 'side-thumbnail') )
							),
							'room-info' => array(
								'title' => esc_html__('Room Info', 'tourmaster'),
								'type' => 'multi-combobox',
								'options' => array(
									'bed-type' => esc_html__('Bed Type', 'tourmaster'),
									'guest-amount' => esc_html__('Guest Amount', 'tourmaster'),
									'room-size' => esc_html__('Room Size', 'tourmaster'),
									'custom-excerpt' => esc_html__('Custom Excerpt', 'tourmaster'),
									'location' => esc_html__('Location', 'tourmaster')
								),
								'description' => esc_html__('You can use Ctrl/Command button to select multiple items or remove the selected item. Leave this field blank to select all items in the list.', 'tourmaster'),
							),
							'excerpt' => array(
								'title' => esc_html__('Excerpt Type', 'tourmaster'),
								'type' => 'combobox',
								'options' => array(
									'specify-number' => esc_html__('Specify Number', 'tourmaster'),
									'show-all' => esc_html__('Show All ( use <!--more--> tag to cut the content )', 'tourmaster'),
									'none' => esc_html__('Disable Exceprt', 'tourmaster'),
								),
								'default' => 'specify-number',
							),
							'excerpt-number' => array(
								'title' => esc_html__('Excerpt Number', 'tourmaster'),
								'type' => 'text',
								'default' => 55,
							),
							'enable-rating' => array(
								'title' => esc_html__('Enable Rating', 'tourmaster'),
								'type' => 'checkbox',
								'default' => 'enable'
							),
						),
					),
					'typography' => array(
						'title' => esc_html('Typography', 'tourmaster'),
						'options' => array(
							'room-title-font-size' => array(
								'title' => esc_html__('Room Title Font Size', 'tourmaster'),
								'type' => 'text',
								'data-input-type' => 'pixel',
							),
							'room-title-font-weight' => array(
								'title' => esc_html__('Room Title Font Weight', 'tourmaster'),
								'type' => 'text',
								'description' => esc_html__('Eg. lighter, bold, normal, 300, 400, 600, 700, 800', 'tourmaster')
							),
							'room-title-letter-spacing' => array(
								'title' => esc_html__('Room Title Letter Spacing', 'tourmaster'),
								'type' => 'text',
								'data-input-type' => 'pixel',
							),
							'room-title-text-transform' => array(
								'title' => esc_html__('Room Title Text Transform', 'tourmaster'),
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
							'room-price-font-size' => array(
								'title' => esc_html__('Room Price Font Size', 'tourmaster'),
								'type' => 'text',
								'data-input-type' => 'pixel',
							),
							'room-price-font-weight' => array(
								'title' => esc_html__('Room Price Font Weight', 'tourmaster'),
								'type' => 'text',
								'description' => esc_html__('Eg. lighter, bold, normal, 300, 400, 600, 700, 800', 'tourmaster')
							),
						)
					),
					'shadow' => array(
						'title' => esc_html__('Color/Shadow', 'tourmaster'),
						'options' => array(
							'frame-padding' => array(
								'title' => esc_html__('Frame Padding', 'tourmaster'),
								'type' => 'custom',
								'item-type' => 'padding',
								'data-input-type' => 'pixel',
								'default' => array( 'top'=>'', 'right'=>'', 'bottom'=>'', 'left'=>'', 'settings'=>'unlink' ),
							),
							'frame-border-size' => array(
								'title' => esc_html__('Frame Border Size', 'tourmaster'),
								'type' => 'custom',
								'item-type' => 'padding',
								'data-input-type' => 'pixel',
								'default' => array( 'top'=>'', 'right'=>'', 'bottom'=>'', 'left'=>'', 'settings'=>'link' )
							),
							'frame-border-color' => array(
								'title' => esc_html__('Frame Border Color', 'tourmaster'),
								'type' => 'colorpicker',
								'descripiton' => esc_html__('Only effects the "Column With Frame" style', 'tourmaster')
							),
							'frame-hover-border-color' => array(
								'title' => esc_html__('Frame Hover Border Color', 'tourmaster'),
								'type' => 'colorpicker',
								'data-input-type' => 'pixel',
								'description' => esc_html__('Only For Blog Column With Frame Style', 'tourmaster')
							),
							'frame-shadow-size' => array(
								'title' => esc_html__('Shadow Size ( for image/frame )', 'tourmaster'),
								'type' => 'custom',
								'item-type' => 'padding',
								'options' => array('x', 'y', 'size'),
								'data-input-type' => 'pixel',
							),
							'frame-shadow-color' => array(
								'title' => esc_html__('Shadow Color ( for image/frame )', 'tourmaster'),
								'type' => 'colorpicker'
							),
							'frame-shadow-opacity' => array(
								'title' => esc_html__('Shadow Opacity ( for image/frame )', 'tourmaster'),
								'type' => 'text',
								'default' => '0.2',
								'description' => esc_html__('Fill the number between 0.01 to 1', 'tourmaster')
							),
							'enable-move-up-shadow-effect' => array(
								'title' => esc_html__('Move Up Shadow Hover Effect', 'tourmaster'),
								'type' => 'checkbox',
								'default' => 'disable',
								'description' => esc_html__('Only effects the "Column With Frame" style', 'tourmaster')
							),
							'move-up-effect-length' => array(
								'title' => esc_html__('Move Up Hover Effect Length', 'tourmaster'),
								'type' => 'text',
								'data-input-type' => 'pixel',
								'condition' => array( 'enable-move-up-shadow-effect' => 'enable' )
							),
							'frame-hover-shadow-size' => array(
								'title' => esc_html__('Shadow Hover Size', 'tourmaster'),
								'type' => 'custom',
								'item-type' => 'padding',
								'options' => array('x', 'y', 'size'),
								'data-input-type' => 'pixel',
								'condition' => array( 'enable-move-up-shadow-effect' => 'enable' )
							),
							'frame-hover-shadow-color' => array(
								'title' => esc_html__('Shadow Hover Color', 'tourmaster'),
								'type' => 'colorpicker',
								'condition' => array( 'enable-move-up-shadow-effect' => 'enable' )
							),
							'frame-hover-shadow-opacity' => array(
								'title' => esc_html__('Shadow Hover Opacity', 'tourmaster'),
								'type' => 'text',
								'default' => '0.2',
								'description' => esc_html__('Fill the number between 0.01 to 1', 'tourmaster'),
								'condition' => array( 'enable-move-up-shadow-effect' => 'enable' )
							),
						),
					),
					'color' => array(
						'title' => esc_html('Color', 'tourmaster'),
						'options' => array(
							'price-background-color' => array(
								'title' => esc_html__('Price (With Background) Background Color', 'tourmaster'),
								'type' => 'colorpicker'
							),
							'price-background-text-color' => array(
								'title' => esc_html__('Price (With Background) Text Color', 'tourmaster'),
								'type' => 'colorpicker'
							),
							'read-more-background-color' => array(
								'title' => esc_html__('Read More Background Color', 'tourmaster'),
								'type' => 'colorpicker'
							),
							'read-more-text-color' => array(
								'title' => esc_html__('Read More Text Color', 'tourmaster'),
								'type' => 'colorpicker'
							)
						),
					),
					'spacing' => array(
						'title' => esc_html('Spacing', 'tourmaster'),
						'options' => array(
							'price-background-radius' => array(
								'title' => esc_html__('Price (With Background) Radius', 'tourmaster'),
								'type' => 'text',
								'data-input-type' => 'pixel',
							),
							'frame-border-radius' => array(
								'title' => esc_html__('Frame/Thumbnail Border Radius', 'tourmaster'),
								'type' => 'text',
								'data-input-type' => 'pixel',
							),
							'room-modern2-side-margin' => array(
								'title' => esc_html__('Room Modern 2 Side Margin', 'tourmaster'),
								'type' => 'text',
								'data-input-type' => 'pixel',
							),
							'room-list-bottom-margin' => array(
								'title' => esc_html__('Room List Bottom Margin', 'tourmaster'),
								'type' => 'text',
								'data-input-type' => 'pixel',
							),
							'padding-bottom' => array(
								'title' => esc_html__('Padding Bottom ( Item )', 'tourmaster'),
								'type' => 'text',
								'data-input-type' => 'pixel',
								'default' => '30px'
							),
						)
					)
				));
			}

			// get the preview for page builder
			static function get_preview( $settings = array() ){
				$content  = self::get_content($settings, true);
				$id = mt_rand(0, 9999);
				
				ob_start();
?><script type="text/javascript" id="tourmaster-preview-room-<?php echo esc_attr($id); ?>" >
if( document.readyState == 'complete' ){
	jQuery(document).ready(function(){
		var room_preview = jQuery('#tourmaster-preview-room-<?php echo esc_attr($id); ?>').parent();
		room_preview.gdlr_core_lightbox().gdlr_core_flexslider().gdlr_core_isotope().gdlr_core_fluid_video();
	});
}else{
	jQuery(window).load(function(){
		setTimeout(function(){
			var room_preview = jQuery('#tourmaster-preview-room-<?php echo esc_attr($id); ?>').parent();
			room_preview.gdlr_core_lightbox().gdlr_core_flexslider().gdlr_core_isotope().gdlr_core_fluid_video();
		}, 300);
	});
}
</script><?php	
				$content .= ob_get_contents();
				ob_end_clean();
				
				return $content;
			}			
			
			// get the content from settings
			static function get_content( $settings = array(), $preview = false ){

				global $tourmaster_room_item_id;
				$tourmaster_room_item_id = empty($tourmaster_room_item_id)? intval(rand(1,100)): $tourmaster_room_item_id + 1;

				// default variable
				if( empty($settings) ){
					$settings = array(
						'category' => '', 'tag' => '', 'room-style' => 'grid', 'column-size' => 20,
					);
				}
				
				$settings['room-style'] = empty($settings['room-style'])? 'grid': $settings['room-style'];
				$settings['with-frame'] = empty($settings['with-frame'])? 'disable': $settings['with-frame'];
				$settings['layout'] = empty($settings['layout'])? 'fitrows': $settings['layout'];
				
				if( in_array($settings['room-style'], array('modern', 'modern2')) ){
					$settings['has-column'] = 'enable';
					$settings['with-frame'] = 'disable';
				}else if( $settings['room-style'] == 'side-thumbnail' ){
					$settings['has-column'] = 'disable';
					$settings['with-frame'] = 'enable';
					$settings['column-size'] = 60;
					$settings['layout'] = 'fitrows';
				}else if( in_array($settings['room-style'], array('grid', 'grid2', 'grid3', 'grid4', 'grid5')) ){
					$settings['has-column'] = 'enable';
				}
				
				$custom_style  = '';
				if( in_array($settings['room-style'], array('grid', 'grid2', 'grid3', 'grid4', 'grid5')) && $settings['with-frame'] == 'enable' ){
					if( !empty($settings['enable-move-up-shadow-effect']) && $settings['enable-move-up-shadow-effect'] == 'enable' ){
						$custom_style_temp = gdlr_core_esc_style(array(
							'background-shadow-size' => empty($settings['frame-hover-shadow-size'])? '': $settings['frame-hover-shadow-size'],
							'background-shadow-color' => empty($settings['frame-hover-shadow-color'])? '': $settings['frame-hover-shadow-color'],
							'background-shadow-opacity' => empty($settings['frame-hover-shadow-opacity'])? '': $settings['frame-hover-shadow-opacity'],
						), false, true);

						if( !empty($settings['move-up-effect-length']) ){
							$custom_style_temp .= 'transform: translate3d(0, -' . $settings['move-up-effect-length'] . ', 0); ';
						}

						if( !empty($custom_style_temp) ){
							$custom_style .= '#custom_style_id .gdlr-core-move-up-with-shadow:hover{ ' . $custom_style_temp . ' }';
						}
					}
				}
				if( !empty($settings['frame-hover-border-color']) ){
					$custom_style .= '#custom_style_id .tourmaster-room-grid:hover .tourmaster-room-frame{ border-color: ' . $settings['frame-hover-border-color'] . ' !important; }';
				}
				if( !empty($settings['carousel-navigation-icon-hover-color']) ){
					$custom_style .= '#custom_style_id .gdlr-core-flexslider-custom-nav i:hover{ color: ' . $settings['carousel-navigation-icon-hover-color'] . ' !important; }';
				}
				if( !empty($custom_style) ){
					if( empty($settings['id']) ){
						global $gdlr_core_room_id;

						if( $preview ){
							$gdlr_core_room_id = empty($gdlr_core_room_id)? array(): $gdlr_core_room_id;

							// generate unique id so it does not get overwritten in admin area
							$rnd_room_id = mt_rand(0, 99999);
							while( in_array($rnd_room_id, $gdlr_core_room_id) ){
								$rnd_room_id = mt_rand(0, 99999);
							}
							$gdlr_core_room_id[] = $rnd_room_id;
							$settings['id'] = 'gdlr-core-room-' . $rnd_room_id;
						}else{
							$gdlr_core_room_id = empty($gdlr_core_room_id)? 1: $gdlr_core_room_id + 1;
							$settings['id'] = 'gdlr-core-room-' . $gdlr_core_room_id;
						}
					}

					$custom_style = str_replace('custom_style_id', $settings['id'], $custom_style); 
					if( $preview ){
						$custom_style = '<style>' . $custom_style . '</style>';
					}else{
						gdlr_core_add_inline_style($custom_style);
						$custom_style = '';
					}
				}

				// start printing item
				$extra_class = ' tourmaster-room-item-style-' . $settings['room-style'];
				$ret  = '<div class="tourmaster-room-item clearfix ' . esc_attr($extra_class) . '" ';
				if( !empty($settings['padding-bottom']) && $settings['padding-bottom'] != '30px' ){
					$ret .= tourmaster_esc_style(array('padding-bottom'=>$settings['padding-bottom']));
				}
				if( !empty($settings['id']) ){
					$ret .= ' id="' . esc_attr($settings['id']) . '" ';
				}
				$ret .= ' >';

				
				// pring tour item
				$room_item = new tourmaster_room_item($settings);

				$ret .= $room_item->get_content();
				
				$ret .= '</div>' . $custom_style; // tourmaster-room-item
				
				return $ret;
			}			
			
		} // tourmaster_pb_element_tour
	} // class_exists	

	add_shortcode('tourmaster_room', 'tourmaster_room_shortcode');
	if( !function_exists('tourmaster_room_shortcode') ){
		function tourmaster_room_shortcode($atts){
			$atts = wp_parse_args($atts, array());
			$atts['column-size'] = empty($atts['column-size'])? 60: 60 / intval($atts['column-size']); 
			$atts['room-info'] = empty($atts['room-info'])? array(): array_map('trim', explode(',', $atts['room-info']));
			
			$ret  = '<div class="tourmaster-room-shortcode clearfix tourmaster-item-rvpdlr" >';
			$ret .= tourmaster_pb_element_room::get_content($atts);
			$ret .= '</div>';

			return $ret;
		}
	}