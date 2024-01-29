<?php
	/*	
	*	Payment Plugin
	*	---------------------------------------------------------------------
	*	creating the stripe payment option
	*	---------------------------------------------------------------------
	*/

	add_filter('goodlayers_credit_card_payment_gateway_options', 'goodlayers_stripe_payment_gateway_options');
	if( !function_exists('goodlayers_stripe_payment_gateway_options') ){
		function goodlayers_stripe_payment_gateway_options( $options ){
			$options['stripe'] = esc_html__('Stripe', 'tourmaster'); 

			return $options;
		}
	}

	add_filter('goodlayers_plugin_payment_option', 'goodlayers_stripe_payment_option');
	if( !function_exists('goodlayers_stripe_payment_option') ){
		function goodlayers_stripe_payment_option( $options ){

			$options['stripe'] = array(
				'title' => esc_html__('Stripe', 'tourmaster'),
				'options' => array(
					'stripe-secret-key' => array(
						'title' => __('Stripe Secret Key', 'tourmaster'),
						'type' => 'text'
					),
					'stripe-publishable-key' => array(
						'title' => __('Stripe Publishable Key', 'tourmaster'),
						'type' => 'text'
					),	
					'stripe-currency-code' => array(
						'title' => __('Stripe Currency Code', 'tourmaster'),
						'type' => 'text',	
						'default' => 'usd'
					),	
				)
			);

			return $options;
		} // goodlayers_stripe_payment_option
	}

	$current_payment_gateway = apply_filters('goodlayers_payment_get_option', '', 'credit-card-payment-gateway');
	if( $current_payment_gateway == 'stripe' ){
		if( !class_exists('Stripe\Stripe') ){
			include_once(TOURMASTER_LOCAL . '/include/stripe/init.php');
		}

		add_action('goodlayers_payment_page_init', 'goodlayers_stripe_payment_page_init');
		add_filter('goodlayers_plugin_payment_attribute', 'goodlayers_stripe_payment_attribute');
		add_filter('goodlayers_stripe_payment_form', 'goodlayers_stripe_payment_form', 10, 2);

		add_action('wp_ajax_stripe_payment_charge', 'goodlayers_stripe_payment_charge');
		add_action('wp_ajax_nopriv_stripe_payment_charge', 'goodlayers_stripe_payment_charge');
	}

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

	// add attribute for payment button
	if( !function_exists('goodlayers_stripe_payment_attribute') ){
		function goodlayers_stripe_payment_attribute( $attributes ){
			return array('method' => 'ajax', 'type' => 'stripe');
		}
	}

	// payment form
	if( !function_exists('goodlayers_stripe_payment_form') ){
		function goodlayers_stripe_payment_form( $ret = '', $tid = '' ){

			// get the price
			$api_key = trim(apply_filters('goodlayers_payment_get_option', '', 'stripe-secret-key'));
			$currency_code = trim(apply_filters('goodlayers_payment_get_option', 'usd', 'stripe-currency-code'));
			
			$t_data = apply_filters('goodlayers_payment_get_transaction_data', array(), $tid, array('email', 'currency', 'price', 'tour_id', 'contact_address', 'country', 'first_name', 'last_name'));
			
			$price = '';
			if( !empty($t_data['price']['deposit-price']) ){
				$price = $t_data['price']['deposit-price'];
				if( !empty($t_data['price']['deposit-price-raw']) ){
					$deposit_amount = $t_data['price']['deposit-price-raw'];
				}
			}else if( !empty($t_data['price']['pay-amount']) ){
				$price = $t_data['price']['pay-amount'];
			}

			// apply currency
			if( !empty($t_data['currency']) ){
				$currency_code = strtoupper($t_data['currency']['currency-code']);
				$price = $price * floatval($t_data['currency']['exchange-rate']);
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
			$customer = \Stripe\Customer::create([
				'name' => $t_data['first_name'] . ' ' . $t_data['last_name'],
				'email' => $t_data['email'],
				'address' => array(
					'line1' => $t_data['contact_address'],
					'country' => tourmaster_country_to_iso($t_data['country']),
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
			    'description' => html_entity_decode(get_the_title($t_data['tour_id']), ENT_QUOTES),
			    'customer' => $customer
			]);

			$publishable_key = apply_filters('goodlayers_payment_get_option', '', 'stripe-publishable-key');

			ob_start();
?>
<div class="goodlayers-payment-form goodlayers-with-border" >
	<form action="" method="POST" id="goodlayers-stripe-payment-form" data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" >
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
		<button id="card-button" data-secret="<?= $intent->client_secret ?>"><?php esc_html_e('Submit Payment', 'tourmaster'); ?></button>
		
		<!-- for proceeding to last step -->
		<div class="goodlayers-payment-plugin-complete" ></div>
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
							data: { 'action':'stripe_payment_charge', 'tid': tid, 'paymentIntent': result.paymentIntent },
							dataType: 'json',
							error: function(a, b, c){ 
								console.log(a, b, c); 

								// display error messages
								form.find('.payment-errors').text('<?php echo esc_html__('An error occurs, please refresh the page to try again.', 'tourmaster'); ?>').slideDown(200);
								form.find('.submit').prop('disabled', false).removeClass('now-loading'); 
							},
							success: function(data){
								if( data.status == 'success' ){
									form.find('.goodlayers-payment-plugin-complete').trigger('click');
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
	if( !function_exists('goodlayers_stripe_payment_charge') ){
		function goodlayers_stripe_payment_charge(){

			$ret = array();

			if( !empty($_POST['paymentIntent']) && !empty($_POST['tid']) ){
				$payment_intent = tourmaster_process_post_data($_POST['paymentIntent']);

				if( !empty($payment_intent['id']) ){
					$api_key = trim(apply_filters('goodlayers_payment_get_option', '', 'stripe-secret-key'));
					$currency_code = trim(apply_filters('goodlayers_payment_get_option', 'usd', 'stripe-currency-code'));
						
					\Stripe\Stripe::setApiKey($api_key);
					$pi = \Stripe\PaymentIntent::retrieve($payment_intent['id']);

					if( $pi['status'] == 'succeeded' && $pi['metadata']->tid == $_POST['tid'] ){

						$t_data = apply_filters('goodlayers_payment_get_transaction_data', array(), $_POST['tid'], array('price', 'email', 'currency'));
						
						// apply currency
						if( !empty($t_data['currency']) ){
							$currency_code = $t_data['currency']['currency-code'];
							$pi['amount'] = $pi['amount'] / floatval($t_data['currency']['exchange-rate']);
						}

						if( strtolower($currency_code) == 'jpy' ){
							$paid_amount = $pi['amount'];
						}else{
							$paid_amount = $pi['amount'] / 100;
						}

						// collect payment information
						$payment_info = array(
							'payment_method' => 'stripe',
							'amount' => $paid_amount,
							'transaction_id' => $pi['id'],
							'payment_status' => 'paid',
							'submission_date' => current_time('mysql')
						);

						// additional data for payment fee
						if( !empty($t_data['price']['deposit-price']) ){
							if( !empty($t_data['price']['deposit-price-raw']) ){
								$payment_info['deposit_amount'] = $t_data['price']['deposit-price-raw'];
							}
							if( !empty($t_data['price']['deposit-credit-card-service-rate']) ){
								$payment_info['deposit_credit_card_service_rate'] = $t_data['price']['deposit-credit-card-service-rate'];
							}
							if( !empty($t_data['price']['deposit-credit-card-service-fee']) ){
								$payment_info['deposit_credit_card_service_fee'] = $t_data['price']['deposit-credit-card-service-fee'];
							}
						}else{
							if( !empty($t_data['price']['pay-amount-raw']) ){
								$payment_info['pay_amount'] = $t_data['price']['pay-amount-raw'];
							}
							if( !empty($t_data['price']['pay-amount-credit-card-service-rate']) ){
								$payment_info['pay_credit_card_service_rate'] = $t_data['price']['pay-amount-credit-card-service-rate'];
							}
							if( !empty($t_data['price']['pay-amount-credit-card-service-fee']) ){
								$payment_info['pay_credit_card_service_fee'] = $t_data['price']['pay-amount-credit-card-service-fee'];
							}
						}

						// update data
						do_action('goodlayers_set_payment_complete', $_POST['tid'], $payment_info);

						$ret['status'] = 'success';

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