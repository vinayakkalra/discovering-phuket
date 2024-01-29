<?php
    // enquiry form
    if( !function_exists('tourmaster_room_get_enquiry_form') ){
        function tourmaster_room_get_enquiry_form( $post_id = '' ){

            if( !empty($post_id) ){
                $custom_fields = get_post_meta($post_id, 'tourmaster-enquiry-form-fields', true);
            }
            if( empty($custom_fields) ){
                $custom_fields = tourmaster_get_option('room_general', 'enquiry-form-fields', '');
            }

            if( empty($custom_fields) ){
                $enquiry_fields = array(
                    'full-name' => array(
                        'title' => esc_html__('Full Name', 'tourmaster'),
                        'type' => 'text',
                        'required' => true
                    ),
                    'email-address' => array(
                        'title' => esc_html__('Email Address', 'tourmaster'),
                        'type' => 'text',
                        'required' => true
                    ),
                    'your-enquiry' => array(
                        'title' => esc_html__('Your Enquiry', 'tourmaster'),
                        'type' => 'textarea',
                        'required' => true
                    ),
                );
            }else{
                $enquiry_fields = tourmaster_read_custom_fields($custom_fields);
            }

            $ret  = '<form class="tourmaster-enquiry-form tourmaster-form-field tourmaster-room clearfix" ';
            $ret .= ' id="tourmaster-enquiry-form" ';
            $ret .= ' data-ajax-url="' . esc_url(TOURMASTER_AJAX_URL) . '" '; 
            $ret .= ' data-action="tourmaster_room_send_enquiry_form" ';
            $ret .= ' data-validate-error="' . esc_attr(esc_html__('Please fill all required fields.', 'tourmaster')) . '" ';
            $ret .= ' >';
            foreach( $enquiry_fields as $slug => $enquiry_field ){
                $enquiry_field['echo'] = false;
                $enquiry_field['slug'] = $slug;
                
                $ret .= tourmaster_get_form_field($enquiry_field, 'enquiry');
            }

            $recaptcha = tourmaster_get_option('general', 'enable-recaptcha', 'disable');
            if( $recaptcha == 'enable' ){
                $ret .= apply_filters('gglcptch_display_recaptcha', '', 'tourmaster-enquiry');
            }

            $our_term = tourmaster_get_option('room_payment', 'term-of-service-page', '#');
            $our_term = is_numeric($our_term)? get_permalink($our_term): $our_term; 
            $privacy = tourmaster_get_option('room_payment', 'privacy-statement-page', '#');
            $privacy = is_numeric($privacy)? get_permalink($privacy): $privacy; 
            $ret .= '<div class="tourmaster-enquiry-term" >';
            $ret .= '<input type="checkbox" name="tourmaster-require-acceptance" />';
            $ret .= sprintf(wp_kses(
                __('* I agree with <a href="%s" target="_blank">Terms of Service</a> and <a href="%s" target="_blank">Privacy Statement</a>.', 'tourmaster'), 
                array('a' => array( 'href'=>array(), 'target'=>array() ))
            ), $our_term, $privacy);
            $ret .= '<div class="tourmaster-enquiry-term-message tourmaster-enquiry-form-message tourmaster-failed" >' . esc_html__('Please agree to all the terms and conditions before proceeding to the next step', 'tourmaster') . '</div>';
            $ret .= '</div>';

            $ret .= '<div class="tourmaster-enquiry-form-message" ></div>';
            $ret .= '<input type="hidden" name="room-id" value="' . get_the_ID() . '" />';
            $ret .= '<input type="submit" class="tourmaster-room-button tourmaster-full" value="' . esc_html__('Submit Enquiry', 'tourmaster') . '" />';
            $ret .= '</form>';

            return $ret;
        }
    }
    add_action('wp_ajax_tourmaster_room_send_enquiry_form', 'tourmaster_room_ajax_send_enquiry_form');
    add_action('wp_ajax_nopriv_tourmaster_room_send_enquiry_form', 'tourmaster_room_ajax_send_enquiry_form');
    if( !function_exists('tourmaster_room_ajax_send_enquiry_form') ){
        function tourmaster_room_ajax_send_enquiry_form(){

            $data = tourmaster_process_post_data($_POST['data']);
            
            // recaptcha tourmaster-enquiry
            $recaptcha = tourmaster_get_option('general', 'enable-recaptcha', 'disable');
            if( $recaptcha == 'enable' ){
                $_POST['g-recaptcha-response'] = empty($data['g-recaptcha-response'])? '': $data['g-recaptcha-response'];
                
                if( $_POST['g-recaptcha-response'] == 'gdlr-verfied' ){
                    $recaptcha_result = true;
                }else{
                    $recaptcha_result = apply_filters('gglcptch_verify_recaptcha', true, 'tourmaster-enquiry');
            
                }
            }
            if( $recaptcha == 'enable' && $recaptcha_result !== true ){
                $ret = array(
                    'status' => 'failed',
                    'message' => esc_html__('Invalid captcha verification.', 'tourmaster') . $data['g-recaptcha-response']
                );
            }else{
                if( !empty($data['email-address']) && is_email($data['email-address']) ){

                    // send an email to admin
                    $admin_mail_title = tourmaster_get_option('room_general', 'admin-enquiry-mail-title','');
                    $admin_mail_content = get_post_meta($data['room-id'], 'tourmaster-enquiry-form-mail-content-admin', true);
                    if( empty($admin_mail_content) ){
                        $admin_mail_content = tourmaster_get_option('room_general', 'admin-enquiry-mail-content','');
                    }
                    $admin_mail_content = tourmaster_room_set_enquiry_data($admin_mail_content, $data);
                    if( !empty($admin_mail_title) && !empty($admin_mail_content) ){
                        $admin_mail_address = tourmaster_get_option('room_general', 'admin-email-address');
                        tourmaster_room_mail(array(
                            'recipient' => $admin_mail_address,
                            'reply-to' => $data['email-address'],
                            'title' => $admin_mail_title,
                            'message' => tourmaster_room_mail_content($admin_mail_content)
                        ));
                    }

                    // send an email to customer
                    $mail_title = tourmaster_get_option('room_general', 'enquiry-mail-title','');
                    $mail_content = get_post_meta($data['room-id'], 'tourmaster-enquiry-form-mail-content-customer', true);
                    if( empty($mail_content) ){
                        $mail_content = tourmaster_get_option('room_general', 'enquiry-mail-content','');
                    }
                    $mail_title = tourmaster_room_set_enquiry_data($mail_title, $data);
                    $mail_content = tourmaster_room_set_enquiry_data($mail_content, $data);
                    if( !empty($mail_title) && !empty($mail_content) ){
                        tourmaster_room_mail(array(
                            'recipient' => $data['email-address'],
                            'title' => $mail_title,
                            'message' => tourmaster_room_mail_content($mail_content)
                        ));
                    }

                    $ret = array(
                        'status' => 'success',
                        'message' => esc_html__('Your enquiry has been sent. Thank you!', 'tourmaster')
                    );
                }else{
                    $ret = array(
                        'status' => 'failed',
                        'message' => esc_html__('Invalid Email Address', 'tourmaster')
                    );
                }
            }

            die(json_encode($ret));
        }
    }
    if( !function_exists('tourmaster_room_set_enquiry_data') ){
        function tourmaster_room_set_enquiry_data( $content, $data ){
            foreach( $data as $slug => $value ){
                $content = str_replace('{' . $slug . '}', $value, $content);
            }

            if( !empty($data['room-id']) ){
                $tour_title = '<a href="' . esc_url(get_permalink($data['room-id'])) . '" >' . get_the_title($data['room-id']) . '</a>';
                $content = str_replace('{room-name}', $tour_title, $content);
            }
            return $content;
        }
    }