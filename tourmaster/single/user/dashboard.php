<?php
	/* dashboard page content */
	global $current_user;

	///////////////////////
	// my profile section
	///////////////////////
	tourmaster_user_content_block_start(array(
		'title' => esc_html__('My Profile', 'tourmaster'),
		'title-link-text' => esc_html__('Edit Profile', 'tourmaster'),
		'title-link' => tourmaster_get_template_url('user', array('page_type'=>'edit-profile')),
		'wrapper-class' => 'tourmaster-dashboard-profile-wrapper'
	));

	$profile_list = array(
		'full_name' => esc_html__('Name', 'tourmaster'),
		'gender' => esc_html__('Gender', 'tourmaster'),
		'birth_date' => esc_html__('Birth Date', 'tourmaster'),
		'country' => esc_html__('Country', 'tourmaster'),
		'email' => esc_html__('Email', 'tourmaster'),
		'phone' => esc_html__('Phone', 'tourmaster'),
		'contact_address' => esc_html__('Contact Address', 'tourmaster'),
	);
	echo '<div class="tourmaster-my-profile-wrapper" >';
	echo '<div class="tourmaster-my-profile-avatar" >';
	$avatar = get_the_author_meta('tourmaster-user-avatar', $current_user->data->ID);
	if( !empty($avatar['thumbnail']) ){
		echo '<img src="' . esc_url($avatar['thumbnail']) . '" alt="profile-image" />';
	}else if( !empty($avatar['file_url']) ){
		echo '<img src="' . esc_url($avatar['file_url']) . '" alt="profile-image" />';
	}else{
		echo get_avatar($current_user->data->ID, 85);
	}
	echo '</div>';

	$even_column = true;
	echo '<div class="tourmaster-my-profile-info-wrap clearfix" >';
	foreach( $profile_list as $meta_field => $field_title ){
		$extra_class  = 'tourmaster-my-profile-info-' . $meta_field;
		$extra_class .= ($even_column)? ' tourmaster-even': ' tourmaster-odd';
		

		echo '<div class="tourmaster-my-profile-info ' . esc_attr($extra_class) . ' clearfix" >';
		echo '<span class="tourmaster-head" >' . $field_title . '</span>';
		echo '<span class="tourmaster-tail" >';
		if( $meta_field == 'birth_date' ){
			$user_meta = tourmaster_get_user_meta($current_user->data->ID, $meta_field, '-');
			if( $user_meta == '-' ){
				echo $user_meta;
			}else{
				echo tourmaster_date_format($user_meta);
			}
		}else if( $meta_field == 'gender' ){
			$user_meta = tourmaster_get_user_meta($current_user->data->ID, $meta_field, '-');
			if( $user_meta == 'male' ){
				echo esc_html__('Male', 'tourmaster');
			}else if( $user_meta == 'female' ){
				echo esc_html__('Female', 'tourmaster');
			}
		}else{
			echo tourmaster_get_user_meta($current_user->data->ID, $meta_field, '-');
		}

		echo '</span>';
		echo '</div>';

		$even_column = !$even_column;
	}
	echo '</div>'; // tourmaster-my-profile-info-wrap

	echo '</div>'; // tourmaster-my-profile-wrapper
	tourmaster_user_content_block_end();

	do_action('tourmaster_dashboard_block');
?>