<?php
	/*	
	*	Payment Plugin
	*	---------------------------------------------------------------------
	*	creating the authorize payment option
	*	---------------------------------------------------------------------
	*/

	include_once(TOURMASTER_LOCAL . '/include/authorize/autoload.php');

	add_filter('goodlayers_room_authorize_payment_form', 'goodlayers_room_authorize_payment_form', 10, 3);

	add_action('wp_ajax_authorize_room_payment_charge', 'goodlayers_authorize_room_payment_charge');
	add_action('wp_ajax_nopriv_authorize_room_payment_charge', 'goodlayers_authorize_room_payment_charge');

	// payment form
	if( !function_exists('goodlayers_room_authorize_payment_form') ){
		function goodlayers_room_authorize_payment_form( $ret = '', $tid = '', $pay_full_amount = true ){
			ob_start();
?>
<div class="goodlayers-payment-form goodlayers-with-border" >
	<form action="" method="POST" id="goodlayers-authorize-payment-form" data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" >
		<div class="goodlayers-payment-form-field">
			<label>
				<span class="goodlayers-payment-field-head" ><?php esc_html_e('Card Number', 'tourmaster'); ?></span>
				<input type="text" data-authorize="number">
			</label>
		</div>
		<div class="goodlayers-payment-form-field">
			<label>
				<span class="goodlayers-payment-field-head" ><?php esc_html_e('Expiration (MM/YYYY)', 'tourmaster'); ?></span>
				<input class="goodlayers-size-small" type="text" size="2" data-authorize="exp_month" />
			</label>
			<span class="goodlayers-separator" >/</span>
			<input class="goodlayers-size-small" type="text" size="2" data-authorize="exp_year" />
		</div>
		<div class="goodlayers-payment-form-field">
			<label>
				<span class="goodlayers-payment-field-head" ><?php esc_html_e('CVC', 'tourmaster'); ?></span>
				<input class="goodlayers-size-small" type="text" size="4" data-authorize="cvc" />
			</label>
		</div>
		<div class="now-loading"></div>
		<div class="payment-errors"></div>
		<div class="goodlayers-payment-req-field" ><?php esc_html_e('Please fill all required fields', 'tourmaster'); ?></div>
		<input type="hidden" data-authorize="pay_full_amount" value="<?php echo $pay_full_amount? 1: 0; ?>" />
		<input type="hidden" name="tid" value="<?php echo esc_attr($tid) ?>" />
		<input class="goodlayers-payment-button submit" type="submit" value="<?php esc_html_e('Submit Payment', 'tourmaster'); ?>" />
		
		<!-- for proceeding to last step -->
		<div class="goodlayers-payment-plugin-complete" ></div>
	</form>
</div>
<script type="text/javascript">
	(function($){
		var form = $('#goodlayers-authorize-payment-form');

		function goodlayersAuthorizeCharge(){

			var tid = form.find('input[name="tid"]').val();
			var form_value = {};
			form.find('[data-authorize]').each(function(){
				form_value[$(this).attr('data-authorize')] = $(this).val(); 
			});

			$.ajax({
				type: 'POST',
				url: form.attr('data-ajax-url'),
				data: { 'action':'authorize_room_payment_charge', 'tid': tid, 'form': form_value },
				dataType: 'json',
				error: function(a, b, c){ 
					console.log(a, b, c); 

					// display error messages
					form.find('.payment-errors').text('<?php echo esc_html__('An error occurs, please refresh the page to try again.', 'tourmaster'); ?>').slideDown(200);
					form.find('.submit').prop('disabled', false).removeClass('now-loading'); 
				},
				success: function(data){
					if( data.status == 'success' ){
						console.log(form.closest('.tourmaster-lightbox-wrapper'));
						form.closest('.tourmaster-room-payment-lb').trigger('payment_complete');
					}else if( typeof(data.message) != 'undefined' ){
						form.find('.payment-errors').text(data.message).slideDown(200);
						form.find('.submit').prop('disabled', false).removeClass('now-loading'); 
					}

				}
			});	
		};
		
		form.submit(function(event){
			var req = false;
			form.find('input').each(function(){
				if( !$(this).val() ){
					req = true;
				}
			});

			if( req ){
				form.find('.goodlayers-payment-req-field').slideDown(200)
			}else{
				form.find('.submit').prop('disabled', true).addClass('now-loading');
				form.find('.payment-errors, .goodlayers-payment-req-field').slideUp(200);
				goodlayersAuthorizeCharge();
			}

			return false;
		});
	})(jQuery);
</script>
<?php
			$ret = ob_get_contents();
			ob_end_clean();
			return $ret;
		}
	}

	// ajax for payment submission
	if( !function_exists('goodlayers_authorize_room_payment_charge') ){
		function goodlayers_authorize_room_payment_charge(){

			$ret = array();

			if( !empty($_POST['tid']) && !empty($_POST['form']) ){

				// prepare data
				$form = stripslashes_deep($_POST['form']);
				$pay_full_amount = empty($form['pay_full_amount'])? false: true;

				$tid = trim($_POST['tid']);
				$live_mode = tourmaster_get_option('room_payment', 'authorize-live-mode', '');
				$api_id = trim(tourmaster_get_option('room_payment', 'authorize-api-id', ''));
				$transaction_key = trim(tourmaster_get_option('room_payment', 'authorize-transaction-key', ''));
				$currency_code = trim(tourmaster_get_option('room_payment', 'authorize-currency-code', ''));
				$service_fee = tourmaster_get_option('room_payment', 'credit-card-service-fee', '');

				if( empty($live_mode) || $live_mode == 'enable' ){
					$environment = \net\authorize\api\constants\ANetEnvironment::PRODUCTION;
				}else{
					$environment = \net\authorize\api\constants\ANetEnvironment::SANDBOX;
				}

				global $wpdb;
				$sql  = "SELECT contact_info, total_price, payment_info, currency FROM {$wpdb->prefix}tourmaster_room_order ";
				$sql .= $wpdb->prepare("WHERE id = %d", $tid);
				$order = $wpdb->get_row($sql);
				$payment_infos = empty($order->payment_info)? array(): json_decode($order->payment_info, true);

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

				// save amount before service fee
				$amount = $price;
				if( !empty($service_fee) ){
					$price = $price * (1 + (floatval($service_fee) / 100));
				}
				$paid_amount = $price;
				
				// apply currency
				if( !empty($order->currency) ){
					$currency = json_decode($order->currency, true);
					if( !empty($currency) ){
						$currency_code = strtoupper($currency['currency-code']);
						$price = $price * floatval($currency['exchange-rate']);
					}
				}

				if( empty($price) ){
					$ret['status'] = 'failed';
					$ret['message'] = esc_html__('Cannot retrieve pricing data, please try again.', 'tourmaster');
				
				// Start the payment process
				}else{

					$price = round(floatval($price) * 100) / 100;

					try{
						// Common setup for API credentials
						$merchantAuthentication = new net\authorize\api\contract\v1\MerchantAuthenticationType();
						$merchantAuthentication->setName(trim($api_id));
						$merchantAuthentication->setTransactionKey(trim($transaction_key));

						// Create the payment data for a credit card
						$creditCard = new net\authorize\api\contract\v1\CreditCardType();
						$creditCard->setCardNumber($form['number']);
						$creditCard->setExpirationDate($form['exp_year'] . '-' . $form['exp_month']);
						$creditCard->setCardCode($form['cvc']);
						$paymentOne = new net\authorize\api\contract\v1\PaymentType();
						$paymentOne->setCreditCard($creditCard);

						// Create transaction
						$transactionRequestType = new net\authorize\api\contract\v1\TransactionRequestType();
						$transactionRequestType->setTransactionType("authCaptureTransaction"); 
						$transactionRequestType->setAmount($price);
						$transactionRequestType->setPayment($paymentOne);
						if( !empty($currency_code) ){
							$transactionRequestType->setCurrencyCode($currency_code);
						}

						// Send request
						$request = new net\authorize\api\contract\v1\CreateTransactionRequest();
						$request->setMerchantAuthentication($merchantAuthentication);
						$request->setTransactionRequest($transactionRequestType);
						$controller = new net\authorize\api\controller\CreateTransactionController($request);
						$response = $controller->executeWithApiResponse($environment);
						
						if( $response != null ){
						    $tresponse = $response->getTransactionResponse();

						    if( ($tresponse != null) && ($tresponse->getResponseCode() == '1') ){
						      	
								// collect payment information
								$payment_info = array(
									'payment_method' => 'authorize',
									'amount' => $amount,
									'paid_amount' => $paid_amount,
									'service_fee' => $paid_amount - $amount,
									'service_fee_rate' => $service_fee,
									'transaction_id' => $tresponse->getTransId(),
									'payment_status' => 'paid',
									'submission_date' => current_time('mysql')
								);
								
								// global $wpdb;
								// $sql  = "SELECT total_price, contact_info, payment_info FROM {$wpdb->prefix}tourmaster_room_order ";
								// $sql .= $wpdb->prepare("WHERE id = %d", $tid);
								// $order = $wpdb->get_row($sql);

								// update data
								$payment_infos = empty($order->payment_info)? array(): json_decode($order->payment_info, true);
								$payment_infos[] = $payment_info;
								$order_status = tourmaster_room_payment_order_status($order->total_price, $payment_infos, true);

								$wpdb->update(
									"{$wpdb->prefix}tourmaster_room_order", 
									array('payment_info'=> json_encode($payment_infos), 'order_status' => $order_status), 
									array('id' => $tid),
									array('%s', '%s'),
									array('%d')
								);
								$ret['status'] = 'success';

								// send an email
								if( $order_status == 'deposit-paid' ){
									tourmaster_room_mail_notification('deposit-payment-made-mail', $tid, '', array('custom' => $payment_info));
									tourmaster_room_mail_notification('admin-deposit-payment-made-mail', $tid, '', array('custom' => $payment_info));
								}else if( $order_status == 'approved' || $order_status == 'online-paid' ){
									tourmaster_room_mail_notification('payment-made-mail', $tid, '', array('custom' => $payment_info));
									tourmaster_room_mail_notification('admin-online-payment-made-mail', $tid, '', array('custom' => $payment_info));
								}
								tourmaster_room_send_email_invoice($tid);

						    }else{
						        $ret['status'] = 'failed';
						    	$ret['message'] = esc_html__('Cannot charge credit card, please check your card credentials again.', 'tourmaster');
						    	
						    	$error = $tresponse->getErrors();
						    	if( !empty($error[0]) ){
							    	$ret['message'] = $error[0]->getErrorText();
						    	}
						    }
						}else{
						    $ret['status'] = 'failed';
						    $ret['message'] = esc_html__('No response returned, please try again.', 'tourmaster');
						}
						$ret['data'] = $_POST;

					}catch( Exception $e ){
						$ret['status'] = 'failed';
						$ret['message'] = $e->getMessage();
					}
				}
			}

			die(json_encode($ret));

		} // goodlayers_authorize_payment_charge
	}
