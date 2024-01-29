<?php

	$nav_list = tourmaster_get_user_nav_list();
	$nav_active = empty($_GET['page_type'])? '': $_GET['page_type'];
	
	$icons = array();
	$page_style = tourmaster_get_option('general', 'user-page-style', 'style-1');
	if( $page_style == 'style-2' ){
		$icons = array(
			'fa fa-dashboard' => 'fa5s fa5-laptop',
			'fa fa-edit' => 'fa5s fa5-edit',
			'fa fa-unlock-alt' => 'fa5s fa5-user-lock',
			'icon_document_alt' => 'fa5r fa5-file-alt',
			'icon_wallet' => 'fa5s fa5-file-invoice-dollar',
			'fa fa-star' => 'fa5r fa5-star',
			'fa fa-heart-o' => 'fa5r fa5-heart',
			'icon_lock-open_alt' => 'fa5s fa5-lock-open'
		);
	}

	foreach( $nav_list as $nav_slug => $nav ){
		if( !empty($nav['type']) && $nav['type'] == 'title' ){
			echo '<h3 class="tourmaster-user-navigation-head" >' . $nav['title'] . '</h3>';
		}else if( empty($nav['hide-main-nav']) ){

			// assign active class
			$nav_class = 'tourmaster-user-navigation-item-' . $nav_slug;

			if( empty($nav_active) || $nav_active == $nav_slug ){
				$nav_active = $nav_slug;
				$nav_class = ' tourmaster-active'; 
			}

			// get the navigation link
			if( !empty($nav['link']) ){
				$nav_link = $nav['link'];
			}else{
				$nav_link = tourmaster_get_template_url('user', array('page_type'=>$nav_slug));
			}

			echo '<div class="tourmaster-user-navigation-item ' . esc_attr($nav_class) . '" >';
			echo '<a href="' . esc_url($nav_link) . '" >';
			if( !empty($nav['icon']) ){
				if( !empty($icons[$nav['icon']]) ){
					echo '<i class="tourmaster-user-navigation-item-icon ' . esc_attr($icons[$nav['icon']]) . '" ></i>';
				}else{
					echo '<i class="tourmaster-user-navigation-item-icon ' . esc_attr($nav['icon']) . '" ></i>';
				}
			}
			echo $nav['title'];
			echo '</a>';
			echo '</div>';		
		}
		
		
	}




?>