<?php
	/*	
	*	Payment Plugin
	*	---------------------------------------------------------------------
	*	creating the stripe payment option
	*	---------------------------------------------------------------------
	*/


	if( !class_exists('Stripe\Stripe') ){
		include_once(TOURMASTER_LOCAL . '/include/stripe/init.php');
	}

	add_action('goodlayers_room_payment_page_init', 'goodlayers_stripe_payment_page_init');
	add_filter('goodlayers_room_stripe_payment_form', 'goodlayers_room_stripe_payment_form', 10, 3);

	add_action('wp_ajax_stripe_room_payment_charge', 'goodlayers_stripe_room_payment_charge');
	add_action('wp_ajax_nopriv_stripe_room_payment_charge', 'goodlayers_stripe_room_payment_charge');

	// init the script on payment page head
	if( !function_exists('goodlayers_stripe_payment_page_init') ){
		function goodlayers_stripe_payment_page_init( $options ){
			add_action('wp_head', 'goodlayers_stripe_payment_script_include');
		}
	}
	if( !function_exists('goodlayers_stripe_payment_script_include') ){
		function goodlayers_stripe_payment_script_include( $options ){
			echo '<script src="https://js.stripe.com/v3/"></script>';
		}
	}	

	// payment form
	if( !function_exists('goodlayers_room_stripe_payment_form') ){
		function goodlayers_room_stripe_payment_form( $ret = '', $tid = '', $pay_full_amount = true){

			// get the price
			$api_key = trim(tourmaster_get_option('room_payment', 'stripe-secret-key', ''));
			$publishable_key = tourmaster_get_option('room_payment', 'stripe-publishable-key', '');
			$currency_code = trim(tourmaster_get_option('room_payment', 'stripe-currency-code', 'usd'));
			$service_fee = tourmaster_get_option('room_payment', 'credit-card-service-fee', '');

			global $wpdb;
			$sql  = "SELECT contact_info, total_price, payment_info, currency FROM {$wpdb->prefix}tourmaster_room_order ";
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

			// jpy case
			if( strtolower($currency_code) == 'jpy' ){
				$price = round(floatval($price));
			}else{
				$price = round(floatval($price) * 100);
			}
			
			\Stripe\Stripe::setAppInfo(
			  "WordPress Tourmaster Plugin",
			  "4.1.6",
			  "https://codecanyon.net/item/tour-master-tour-booking-travel-wordpress-plugin/20539780"
			);
			\Stripe\Stripe::setApiKey($api_key);

			// create customer
			$contact_info = json_decode($order->contact_info, true);
			$customer = \Stripe\Customer::create([
				'name' => $contact_info['first_name'] . ' ' . $contact_info['last_name'],
				'email' => $t_data['email'],
				'address' => array(
					'line1' => $contact_info['address'],
					'country' => tourmaster_country_to_iso($contact_info['country']),
				),
			]);

			// set payment intent
			$intent = \Stripe\PaymentIntent::create([
			    'amount' => $price,
			    'currency' => $currency_code,
			    'metadata' => array(
			    	'tid' => $tid
			    ),
				'setup_future_usage' => 'off_session',
			    'description' => html_entity_decode(esc_html__('Room Booking', 'tourmaster'), ENT_QUOTES),
			    'customer' => $customer
			]);


			ob_start();
?>
<div class="goodlayers-payment-form goodlayers-with-border" >
	<form action="" method="POST" id="goodlayers-stripe-payment-form" data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" >
		<h4><?php esc_html_e('Credit Card Payment', 'tourmaster'); ?></h4>
		<div class="goodlayers-payment-form-field">
			<label>
				<span class="goodlayers-payment-field-head" ><?php esc_html_e('Card Holder Name', 'tourmaster'); ?></span>
				<input id="cardholder-name" type="text">
			</label>
		</div>

		<div class="goodlayers-payment-form-field">
			<label> 
				<span class="goodlayers-payment-field-head" ><?php esc_html_e('Card Information', 'tourmaster'); ?></span>
			</label>
			<div id="card-element"></div>
		</div>

		<input type="hidden" name="tid" value="<?php echo esc_attr($tid) ?>" />

		<!-- error message -->
		<div class="payment-errors"></div>
		<div class="goodlayers-payment-req-field" ><?php esc_html_e('Please fill all required fields', 'tourmaster'); ?></div>

		<!-- submit button -->
		<button id="card-button" data-secret="<?= $intent->client_secret ?>"><?php esc_html_e('Pay Now', 'tourmaster'); ?></button>
	</form>
</div>
<script type="text/javascript">
	(function($){
		var form = $('#goodlayers-stripe-payment-form');
		var tid = form.find('input[name="tid"]').val();

		var stripe = Stripe('<?php echo esc_js(trim($publishable_key)); ?>', {locale: '<?php echo get_locale(); ?>'.slice(0, 2) });
		var elements = stripe.elements();
		var cardElement = elements.create('card');
		cardElement.mount('#card-element');

		var cardholderName = document.getElementById('cardholder-name');
		var cardButton = document.getElementById('card-button');
		var clientSecret = cardButton.dataset.secret;
		cardButton.addEventListener('click', function(ev){
			form.find('.payment-errors, .goodlayers-payment-req-field').slideUp(200);

			// validate empty input field
			if( !form.find('#cardholder-name').val() ){
				var req = true;
			}else{
				var req = false;
			}

			// make the payment
			if( req ){
				form.find('.goodlayers-payment-req-field').slideDown(200);
			}else{

				// prevent multiple submission
				if( $(cardButton).hasClass('now-loading') ){
					return;
				}else{
					$(cardButton).prop('disabled', true).addClass('now-loading');
				}
				
				// made a payment
				stripe.handleCardPayment(
					clientSecret, cardElement, {
						payment_method_data: {
							billing_details: {name: cardholderName.value}
						}
					}
				).then(function(result){
					if( result.error ){

						$(cardButton).prop('disabled', false).removeClass('now-loading'); 

						// Display error.message in your UI.
						var error_message = '';
						switch(result.error.code){
							case 'incomplete_number': error_message = '<?php echo trim(esc_html__('Your card number is incomplete.', 'tourmaster')); ?>'; break;
							case 'invalid_number': error_message = '<?php echo trim(esc_html__('Your card number is invalid.', 'tourmaster')); ?>'; break;
							case 'card_declined': error_message = '<?php echo trim(esc_html__('Your card was declined.', 'tourmaster')); ?>'; break;
							case 'expired_card': error_message = '<?php echo trim(esc_html__('Your card has expired.', 'tourmaster')); ?>'; break;
							case 'incomplete_expiry': error_message = '<?php echo trim(esc_html__('Your card\'s expiration date is incomplete.', 'tourmaster')); ?>'; break;
							case 'invalid_expiry_year': error_message = '<?php echo trim(esc_html__('Your card\'s expiration year is invalid.', 'tourmaster')); ?>'; break;
							case 'invalid_expiry_month_past': error_message = '<?php echo trim(esc_html__('Your card\'s expiration date is in the past.', 'tourmaster')); ?>'; break;
							case 'invalid_expiry_year_past': error_message = '<?php echo trim(esc_html__('Your card\'s expiration date is in the past.', 'tourmaster')); ?>'; break;
							case 'payment_intent_authentication_failure': error_message = '<?php echo trim(esc_html__('We are unable to authenticate your payment method. Please choose another payment method and try again.', 'tourmaster')); ?>'; break;
							case 'incomplete_cvc': error_message = '<?php echo trim(esc_html__('Your card\'s security code is incomplete.', 'tourmaster')); ?>'; break;
							case 'incorrect_cvc': error_message = '<?php echo trim(esc_html__('Your card\'s security code is incorrect.', 'tourmaster')); ?>'; break;
							case 'incomplete_zip': error_message = '<?php echo trim(esc_html__('Your postal code is incomplete.', 'tourmaster')); ?>'; break;
							case 'processing_error': error_message = '<?php echo trim(esc_html__('An error occurred while processing your card. Try again in a little bit.', 'tourmaster')); ?>'; break;
						}
						if( error_message == '' ){
							error_message = result.error.message; //  + ' ' + result.error.code
						}
						form.find('.payment-errors').text(error_message).slideDown(200);

					}else{

						// The payment has succeeded. Display a success message.
						$.ajax({
							type: 'POST',
							url: form.attr('data-ajax-url'),
							data: { 'action':'stripe_room_payment_charge', 'tid': tid, 'paymentIntent': result.paymentIntent },
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
								}
							}
						});	
						
					}
				});
			}
		});
		$(cardButton).on('click', function(){
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
	if( !function_exists('goodlayers_stripe_room_payment_charge') ){
		function goodlayers_stripe_room_payment_charge(){

			$ret = array();

			if( !empty($_POST['paymentIntent']) && !empty($_POST['tid']) ){
				$tid = $_POST['tid'];
				$payment_intent = tourmaster_process_post_data($_POST['paymentIntent']);

				if( !empty($payment_intent['id']) ){
					$api_key = trim(tourmaster_get_option('room_payment', 'stripe-secret-key', ''));
					$currency_code = trim(tourmaster_get_option('room_payment', 'stripe-currency-code', 'usd'));

					\Stripe\Stripe::setApiKey($api_key);
					$pi = \Stripe\PaymentIntent::retrieve($payment_intent['id']);

					if( $pi['status'] == 'succeeded' && $pi['metadata']->tid == $tid ){

						global $wpdb;
						$sql  = "SELECT total_price, contact_info, payment_info, currency FROM {$wpdb->prefix}tourmaster_room_order ";
						$sql .= $wpdb->prepare("WHERE id = %d", $tid);
						$order = $wpdb->get_row($sql);

						// apply currency
						if( !empty($order->currency) ){
							$currency = json_decode($order->currency, true);
							if( !empty($currency) ){
								$currency_code = $currency['currency-code'];
								$pi['amount'] = $pi['amount'] / floatval($currency['exchange-rate']);
							}
						}

						if( strtolower($currency_code) == 'jpy' ){
							$paid_amount = floatval($pi['amount']);
						}else{
							$paid_amount = floatval($pi['amount']) / 100;
						}

						$amount = $paid_amount;
						$service_fee = tourmaster_get_option('room_payment', 'credit-card-service-fee', '0');
						if( !empty($service_fee) ){
							$amount = $amount / (1 + (floatval($service_fee) / 100));
						}

						// collect payment information
						$payment_info = array(
							'payment_method' => 'stripe',
							'amount' => $amount,
							'paid_amount' => $paid_amount,
							'service_fee' => $paid_amount - $amount,
							'service_fee_rate' => $service_fee,
							'transaction_id' => $pi['id'],
							'payment_status' => 'paid',
							'submission_date' => current_time('mysql')
						);

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
					}
				}
			}

			die(json_encode($ret));

		} // goodlayers_stripe_payment_charge
	}

	if( !function_exists('tourmaster_country_to_iso') ){
		function tourmaster_country_to_iso($country){
			$countries = array(
				'AF' => 'Afghanistan',
				'AX' => 'Aland Islands',
				'AL' => 'Albania',
				'DZ' => 'Algeria',
				'AS' => 'American Samoa',
				'AD' => 'Andorra',
				'AO' => 'Angola',
				'AI' => 'Anguilla',
				'AQ' => 'Antarctica',
				'AG' => 'Antigua And Barbuda',
				'AR' => 'Argentina',
				'AM' => 'Armenia',
				'AW' => 'Aruba',
				'AU' => 'Australia',
				'AT' => 'Austria',
				'AZ' => 'Azerbaijan',
				'BS' => 'Bahamas',
				'BH' => 'Bahrain',
				'BD' => 'Bangladesh',
				'BB' => 'Barbados',
				'BY' => 'Belarus',
				'BE' => 'Belgium',
				'BZ' => 'Belize',
				'BJ' => 'Benin',
				'BM' => 'Bermuda',
				'BT' => 'Bhutan',
				'BO' => 'Bolivia',
				'BA' => 'Bosnia And Herzegovina',
				'BW' => 'Botswana',
				'BV' => 'Bouvet Island',
				'BR' => 'Brazil',
				'IO' => 'British Indian Ocean Territory',
				'BN' => 'Brunei Darussalam',
				'BG' => 'Bulgaria',
				'BF' => 'Burkina Faso',
				'BI' => 'Burundi',
				'KH' => 'Cambodia',
				'CM' => 'Cameroon',
				'CA' => 'Canada',
				'CV' => 'Cape Verde',
				'KY' => 'Cayman Islands',
				'CF' => 'Central African Republic',
				'TD' => 'Chad',
				'CL' => 'Chile',
				'CN' => 'China',
				'CX' => 'Christmas Island',
				'CC' => 'Cocos (Keeling) Islands',
				'CO' => 'Colombia',
				'KM' => 'Comoros',
				'CG' => 'Congo',
				'CD' => 'Congo, Democratic Republic',
				'CK' => 'Cook Islands',
				'CR' => 'Costa Rica',
				'CI' => 'Cote D\'Ivoire',
				'HR' => 'Croatia',
				'CU' => 'Cuba',
				'CY' => 'Cyprus',
				'CZ' => 'Czech Republic',
				'DK' => 'Denmark',
				'DJ' => 'Djibouti',
				'DM' => 'Dominica',
				'DO' => 'Dominican Republic',
				'EC' => 'Ecuador',
				'EG' => 'Egypt',
				'SV' => 'El Salvador',
				'GQ' => 'Equatorial Guinea',
				'ER' => 'Eritrea',
				'EE' => 'Estonia',
				'ET' => 'Ethiopia',
				'FK' => 'Falkland Islands (Malvinas)',
				'FO' => 'Faroe Islands',
				'FJ' => 'Fiji',
				'FI' => 'Finland',
				'FR' => 'France',
				'GF' => 'French Guiana',
				'PF' => 'French Polynesia',
				'TF' => 'French Southern Territories',
				'GA' => 'Gabon',
				'GM' => 'Gambia',
				'GE' => 'Georgia',
				'DE' => 'Germany',
				'GH' => 'Ghana',
				'GI' => 'Gibraltar',
				'GR' => 'Greece',
				'GL' => 'Greenland',
				'GD' => 'Grenada',
				'GP' => 'Guadeloupe',
				'GU' => 'Guam',
				'GT' => 'Guatemala',
				'GG' => 'Guernsey',
				'GN' => 'Guinea',
				'GW' => 'Guinea-Bissau',
				'GY' => 'Guyana',
				'HT' => 'Haiti',
				'HM' => 'Heard Island & Mcdonald Islands',
				'VA' => 'Holy See (Vatican City State)',
				'HN' => 'Honduras',
				'HK' => 'Hong Kong',
				'HU' => 'Hungary',
				'IS' => 'Iceland',
				'IN' => 'India',
				'ID' => 'Indonesia',
				'IR' => 'Iran, Islamic Republic Of',
				'IQ' => 'Iraq',
				'IE' => 'Ireland',
				'IM' => 'Isle Of Man',
				'IL' => 'Israel',
				'IT' => 'Italy',
				'JM' => 'Jamaica',
				'JP' => 'Japan',
				'JE' => 'Jersey',
				'JO' => 'Jordan',
				'KZ' => 'Kazakhstan',
				'KE' => 'Kenya',
				'KI' => 'Kiribati',
				'KR' => 'Korea',
				'KW' => 'Kuwait',
				'KG' => 'Kyrgyzstan',
				'LA' => 'Lao People\'s Democratic Republic',
				'LV' => 'Latvia',
				'LB' => 'Lebanon',
				'LS' => 'Lesotho',
				'LR' => 'Liberia',
				'LY' => 'Libyan Arab Jamahiriya',
				'LI' => 'Liechtenstein',
				'LT' => 'Lithuania',
				'LU' => 'Luxembourg',
				'MO' => 'Macao',
				'MK' => 'Macedonia',
				'MG' => 'Madagascar',
				'MW' => 'Malawi',
				'MY' => 'Malaysia',
				'MV' => 'Maldives',
				'ML' => 'Mali',
				'MT' => 'Malta',
				'MH' => 'Marshall Islands',
				'MQ' => 'Martinique',
				'MR' => 'Mauritania',
				'MU' => 'Mauritius',
				'YT' => 'Mayotte',
				'MX' => 'Mexico',
				'FM' => 'Micronesia, Federated States Of',
				'MD' => 'Moldova',
				'MC' => 'Monaco',
				'MN' => 'Mongolia',
				'ME' => 'Montenegro',
				'MS' => 'Montserrat',
				'MA' => 'Morocco',
				'MZ' => 'Mozambique',
				'MM' => 'Myanmar',
				'NA' => 'Namibia',
				'NR' => 'Nauru',
				'NP' => 'Nepal',
				'NL' => 'Netherlands',
				'AN' => 'Netherlands Antilles',
				'NC' => 'New Caledonia',
				'NZ' => 'New Zealand',
				'NI' => 'Nicaragua',
				'NE' => 'Niger',
				'NG' => 'Nigeria',
				'NU' => 'Niue',
				'NF' => 'Norfolk Island',
				'MP' => 'Northern Mariana Islands',
				'NO' => 'Norway',
				'OM' => 'Oman',
				'PK' => 'Pakistan',
				'PW' => 'Palau',
				'PS' => 'Palestinian Territory, Occupied',
				'PA' => 'Panama',
				'PG' => 'Papua New Guinea',
				'PY' => 'Paraguay',
				'PE' => 'Peru',
				'PH' => 'Philippines',
				'PN' => 'Pitcairn',
				'PL' => 'Poland',
				'PT' => 'Portugal',
				'PR' => 'Puerto Rico',
				'QA' => 'Qatar',
				'RE' => 'Reunion',
				'RO' => 'Romania',
				'RU' => 'Russian Federation',
				'RW' => 'Rwanda',
				'BL' => 'Saint Barthelemy',
				'SH' => 'Saint Helena',
				'KN' => 'Saint Kitts And Nevis',
				'LC' => 'Saint Lucia',
				'MF' => 'Saint Martin',
				'PM' => 'Saint Pierre And Miquelon',
				'VC' => 'Saint Vincent And Grenadines',
				'WS' => 'Samoa',
				'SM' => 'San Marino',
				'ST' => 'Sao Tome And Principe',
				'SA' => 'Saudi Arabia',
				'SN' => 'Senegal',
				'RS' => 'Serbia',
				'SC' => 'Seychelles',
				'SL' => 'Sierra Leone',
				'SG' => 'Singapore',
				'SK' => 'Slovakia',
				'SI' => 'Slovenia',
				'SB' => 'Solomon Islands',
				'SO' => 'Somalia',
				'ZA' => 'South Africa',
				'GS' => 'South Georgia And Sandwich Isl.',
				'ES' => 'Spain',
				'LK' => 'Sri Lanka',
				'SD' => 'Sudan',
				'SR' => 'Suriname',
				'SJ' => 'Svalbard And Jan Mayen',
				'SZ' => 'Swaziland',
				'SE' => 'Sweden',
				'CH' => 'Switzerland',
				'SY' => 'Syrian Arab Republic',
				'TW' => 'Taiwan',
				'TJ' => 'Tajikistan',
				'TZ' => 'Tanzania',
				'TH' => 'Thailand',
				'TL' => 'Timor-Leste',
				'TG' => 'Togo',
				'TK' => 'Tokelau',
				'TO' => 'Tonga',
				'TT' => 'Trinidad And Tobago',
				'TN' => 'Tunisia',
				'TR' => 'Turkey',
				'TM' => 'Turkmenistan',
				'TC' => 'Turks And Caicos Islands',
				'TV' => 'Tuvalu',
				'UG' => 'Uganda',
				'UA' => 'Ukraine',
				'AE' => 'United Arab Emirates',
				'GB' => 'United Kingdom',
				'US' => 'United States',
				'UM' => 'United States Outlying Islands',
				'UY' => 'Uruguay',
				'UZ' => 'Uzbekistan',
				'VU' => 'Vanuatu',
				'VE' => 'Venezuela',
				'VN' => 'Vietnam',
				'VG' => 'Virgin Islands, British',
				'VI' => 'Virgin Islands, U.S.',
				'WF' => 'Wallis And Futuna',
				'EH' => 'Western Sahara',
				'YE' => 'Yemen',
				'ZM' => 'Zambia',
				'ZW' => 'Zimbabwe',
			);

			return array_search($country, $countries);
		}
	}