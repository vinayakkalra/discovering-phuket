<?php

	add_filter('goodlayers_room_paypal_payment_form', 'tourmaster_room_paypal_payment_form', 10, 3);
	if( !function_exists('tourmaster_room_paypal_payment_form') ){
		function tourmaster_room_paypal_payment_form($ret = '', $tid = '', $pay_full_amount = true){
			
			$live_mode = tourmaster_get_option('room_payment', 'paypal-live-mode', 'disable');
			$business_email = tourmaster_get_option('room_payment', 'paypal-business-email', '');
			$currency_code = tourmaster_get_option('room_payment', 'paypal-currency-code', '');
			$service_fee = tourmaster_get_option('room_payment', 'paypal-service-fee', '');

			global $wpdb;
			$sql  = "SELECT total_price, payment_info, currency FROM {$wpdb->prefix}tourmaster_room_order ";
			$sql .= $wpdb->prepare("WHERE id = %d", $tid);
			$order = $wpdb->get_row($sql);	
			$payment_infos = empty($order->payment_info)? array(): json_decode($order->payment_info, true);

			// calculate price
			$price = $order->total_price;
			if( empty($pay_full_amount) ){
				$deposit_info = tourmaster_room_get_deposit_info($price, $payment_infos);
				if( !empty($deposit_info['deposit_amount']) ){
					$price = $deposit_info['deposit_amount'];
				}
			}else{
				$paid_amount = 0;
                foreach( $payment_infos as $payment_info ){
                    $paid_amount += empty($payment_info['amount'])? 0: floatval($payment_info['amount']);
                }
				$price = $price - $paid_amount;
			}
			if( !empty($service_fee) ){
				$price = $price * (1 + (floatval($service_fee) / 100));
			}

			// apply currency
			if( !empty($order->currency) ){
				$currency = json_decode($order->currency, true);
				if( !empty($currency) ){
					$currency_code = strtoupper($currency['currency-code']);
					$price = $price * floatval($currency['exchange-rate']);
				}
			}

			$price = round($price, 2);
			
			ob_start();

?>
<div class="goodlayers-paypal-redirecting-message" ><?php esc_html_e('Please wait while we redirect you to paypal.', 'tourmaster') ?></div>
<form id="goodlayers-paypal-redirection-form" method="post" action="<?php
		if( empty($live_mode) || $live_mode == 'disable' ){
			echo 'https://www.sandbox.paypal.com/cgi-bin/webscr';
		}else{
			echo 'https://www.paypal.com/cgi-bin/webscr';
		}
	?>" >
	<input type="hidden" name="cmd" value="_xclick" />
	<input type="hidden" name="business" value="<?php echo esc_attr(trim($business_email)); ?>" />
	<input type="hidden" name="currency_code" value="<?php echo esc_attr(trim($currency_code)); ?>" />
	<input type="hidden" name="item_name" value="<?php echo esc_attr(esc_html__('Room Booking', 'tourmaster')); ?>" />
	<input type="hidden" name="invoice" value="<?php
		// 11 for tourmaster room
		echo '11' . date('dmYHis') . $tid;
	?>" />
	<input type="hidden" name="amount" value="<?php echo esc_attr($price); ?>" />
	<input type="hidden" name="notify_url" value="<?php 
		if( function_exists('pll_home_url') ){
			$home_url = pll_home_url();
		}else{
			$home_url = apply_filters('wpml_home_url', home_url('/'));
		}
		echo add_query_arg(array('room_paypal'=>''), $home_url); 

	?>" />
	<input type="hidden" name="return" value="<?php
		echo add_query_arg(array('pt' => 'room', 'step' => 4, 'payment_method' => 'paypal'), tourmaster_get_template_url('payment'));
	?>" />
</form>
<script type="text/javascript">
	(function($){
		$('#goodlayers-paypal-redirection-form').submit();
	})(jQuery);
</script>
<?php
			$ret = ob_get_contents();
			ob_end_clean();

			return $ret;

		} // goodlayers_paypal_payment_form
	}

	add_action('wp', 'tourmaster_room_paypal_process_ipn');
	if( !function_exists('tourmaster_room_paypal_process_ipn') ){
		function tourmaster_room_paypal_process_ipn(){

			if( isset($_GET['room_paypal']) ){

				$live_mode = tourmaster_get_option('room_payment', 'paypal-live-mode', '');
				if( empty($live_mode) || $live_mode == 'disable' ){
					$paypal_action_url = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';
				}else{
					$paypal_action_url = 'https://ipnpb.paypal.com/cgi-bin/webscr';
				}
				
				// read the post data
				$raw_post_data = file_get_contents('php://input');
				$raw_post_array = explode('&', $raw_post_data);
				$myPost = array();
				foreach ($raw_post_array as $keyval) {
				    $keyval = explode('=', $keyval);
				    if (count($keyval) == 2) {
				        // Since we do not want the plus in the datetime string to be encoded to a space, we manually encode it.
				        if ($keyval[0] === 'payment_date') {
				            if (substr_count($keyval[1], '+') === 1) {
				                $keyval[1] = str_replace('+', '%2B', $keyval[1]);
				            }
				        }
				        $myPost[$keyval[0]] = urldecode($keyval[1]);
				    }
				}

				// prepare post request
				$req = 'cmd=_notify-validate';
		        $get_magic_quotes_exists = false;
		        if (function_exists('get_magic_quotes_gpc')) {
		            $get_magic_quotes_exists = true;
		        }
		        foreach ($myPost as $key => $value) {
		            if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
		                $value = urlencode(stripslashes($value));
		            } else {
		                $value = urlencode($value);
		            }
		            $req .= "&$key=$value";
		        }

		        // Post the data back to PayPal, using curl. Throw exceptions if errors occur.
		        $ch = curl_init($paypal_action_url);
		        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		        curl_setopt($ch, CURLOPT_POST, 1);
		        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
		        curl_setopt($ch, CURLOPT_SSLVERSION, 6);
		        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
				curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close', 'User-Agent: tourmaster'));
				
				$res = curl_exec($ch);

				// query the selected order
				global $wpdb;
				$tid = substr($_POST['invoice'], 16);

				$sql  = "SELECT total_price, contact_info, payment_info, currency FROM {$wpdb->prefix}tourmaster_room_order ";
				$sql .= $wpdb->prepare("WHERE id = %d", $tid);
				$order = $wpdb->get_row($sql);

				$payment_infos = empty($order->payment_info)? array(): json_decode($order->payment_info, true);
		        $payment_info = array(
					'payment_method' => 'paypal',
					'submission_date' => current_time('mysql')
				);

				if( !$res ){
		            $payment_info['error'] = curl_error($ch);

		            if( !empty($_POST['invoice']) ){
						$payment_infos[] = $payment_info;

		            	$wpdb->update(
							"{$wpdb->prefix}tourmaster_room_order", 
							array('payment_info'=> json_encode($payment_infos)), 
							array('id' => $tid),
							array('%s'),
							array('%d')
						);
		            }
		        }else if( strcmp ($res, "VERIFIED") == 0 ){
					
					$paid_amount = floatval($_POST['mc_gross']);
					
					// apply currency
					if( !empty($order->currency) ){
						$currency = json_decode($order->currency, true);
						if( !empty($currency) ){
							$paid_amount = $paid_amount / floatval($currency['exchange-rate']);
						}
					}

					$amount = $paid_amount;
					$service_fee = tourmaster_get_option('room_payment', 'paypal-service-fee', '0');
					if( !empty($service_fee) ){
						$amount = $amount / (1 + (floatval($service_fee) / 100));
					}

		        	$payment_info['transaction_id'] = $_POST['txn_id'];
		        	$payment_info['amount'] = $amount;
		        	$payment_info['paid_amount'] = $paid_amount;
		        	$payment_info['service_fee'] = $paid_amount - $amount;
		        	$payment_info['service_fee_rate'] = $service_fee;
		        	$payment_info['payment_status'] = 'paid';
					
					// prevent duplicate transaction
					$duplicated = false;
					foreach($payment_infos as $orig_info){
						if( $orig_info['transaction_id'] == $payment_info['transaction_id'] ){
							$duplicated = true;
						}
					}
					if( !$duplicated ){
						$payment_infos[] = $payment_info;
						$order_status = tourmaster_room_payment_order_status($order->total_price, $payment_infos, true);
						
						$wpdb->update(
							"{$wpdb->prefix}tourmaster_room_order", 
							array('payment_info'=> json_encode($payment_infos), 'order_status' => $order_status), 
							array('id' => $tid),
							array('%s', '%s'),
							array('%d')
						);

						// send an email
						if( $order_status == 'deposit-paid' ){
							tourmaster_room_mail_notification('deposit-payment-made-mail', $tid, '', array('custom' => $payment_info));
							tourmaster_room_mail_notification('admin-deposit-payment-made-mail', $tid, '', array('custom' => $payment_info));
						}else if( $order_status == 'approved' || $order_status == 'online-paid' ){
							tourmaster_room_mail_notification('payment-made-mail', $tid, '', array('custom' => $payment_info));
							tourmaster_room_mail_notification('admin-online-payment-made-mail', $tid, '', array('custom' => $payment_info));
						}
						tourmaster_room_send_email_invoice($tid);
					}
				}
				curl_close($ch);

		        exit;
			}

		} // tourmaster_paypal_process_ipn
	}