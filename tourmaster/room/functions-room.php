<?php


    include_once(TOURMASTER_LOCAL . '/room/include/order.php');
    include_once(TOURMASTER_LOCAL . '/room/include/order-util.php');

    include_once(TOURMASTER_LOCAL . '/room/include/mail-util.php');
    include_once(TOURMASTER_LOCAL . '/room/include/review-util.php');
    include_once(TOURMASTER_LOCAL . '/room/include/user-page.php');

    include_once(TOURMASTER_LOCAL . '/room/include/room-settings.php');
    include_once(TOURMASTER_LOCAL . '/room/include/room-option.php');
    include_once(TOURMASTER_LOCAL . '/room/include/room-service.php');
    include_once(TOURMASTER_LOCAL . '/room/include/room-coupon.php');
    include_once(TOURMASTER_LOCAL . '/room/include/room-filter.php');
    include_once(TOURMASTER_LOCAL . '/room/include/enquiry-form.php');
    include_once(TOURMASTER_LOCAL . '/room/include/ical-settings.php');
    
    include_once(TOURMASTER_LOCAL . '/room/include/booking-bar.php');
    include_once(TOURMASTER_LOCAL . '/room/include/payment-element.php');
    include_once(TOURMASTER_LOCAL . '/room/include/paypal.php');

    // include each payment
    $enable_woocommerce_payment = tourmaster_get_option('room_payment', 'enable-woocommerce-payment', 'disable');
    if( $enable_woocommerce_payment == 'disable' ){
        $payment_methods = tourmaster_get_option('room_payment', 'payment-method', array());
        $payment_gateway = tourmaster_get_option('room_payment', 'credit-card-payment-gateway', '');
        if( in_array('credit-card', $payment_methods) ){
            if( $payment_gateway == 'stripe' ){
                include_once(TOURMASTER_LOCAL . '/room/include/stripe.php');
            }else if( $payment_gateway == 'authorize' ){
                include_once(TOURMASTER_LOCAL . '/room/include/authorize.php');
            }
        }
    }

    include_once(TOURMASTER_LOCAL . '/room/include/pb/pb-element-room-search.php');
    include_once(TOURMASTER_LOCAL . '/room/include/pb/pb-element-room-title.php');
    include_once(TOURMASTER_LOCAL . '/room/include/pb/pb-element-room-cart.php');
    include_once(TOURMASTER_LOCAL . '/room/include/pb/pb-element-room.php');
    include_once(TOURMASTER_LOCAL . '/room/include/pb/room-item.php');
    include_once(TOURMASTER_LOCAL . '/room/include/pb/room-style.php');

	
    // enqueue necessay style/script
    if( !is_admin() ){ 
		add_action('wp_enqueue_scripts', 'tourmaster_room_enqueue_script', 11);
	}else{
		add_action('gdlr_core_front_script', 'tourmaster_room_enqueue_script', 11);
	}
	if( !function_exists('tourmaster_room_enqueue_script') ){
		function tourmaster_room_enqueue_script(){
            wp_enqueue_style('tourmaster-room-style', TOURMASTER_URL . '/room/tourmaster-room.css', null, '1.0.0');
            wp_enqueue_style('tourmaster-room-custom-style', tourmaster_room_get_style_custom());

			wp_enqueue_script('tourmaster-room-script', TOURMASTER_URL . '/room/tourmaster-room.js', array('jquery'), false, true);
        }
    }

    // archive template
	add_filter('template_include', 'tourmaster_room_archive_template_registration', 9998);
	if( !function_exists('tourmaster_room_archive_template_registration') ){
		function tourmaster_room_archive_template_registration( $template ){
            global $tourmaster_template;

			// archive template
			if( is_tax('room_category') || is_tax('room_tag') || tourmaster_is_custom_room_tax() ){
				$tourmaster_template = 'archive';
				$template = TOURMASTER_LOCAL . '/room/single/archive.php';
			}

			return $template;
		} // tourmaster_template_registration
	} // function_exists