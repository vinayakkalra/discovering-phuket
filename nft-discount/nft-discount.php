<?php
/**
 * Plugin Name:  nft discount
 * Plugin URI: 
 * Description: Nft discount for WooCommerce
 * Version: 1.0.0
 * Author: 
 * Author URI: 
 * Requires PHP: 7.3
 * Text Domain: nftdiscount
 **/
//echo $_COOKIE['profile_viewer_uid'];


add_action('woocommerce_cart_calculate_fees', 'wk_add_custom_discount');

function wk_add_custom_discount($cart) {
	/* $_SESSION["length"] = $_COOKIE['profile_viewer_uid'] ? $_COOKIE['profile_viewer_uid'] : null;
	;
	echo $_SESSION["length"];
    // Check if the session variable is set
    if (!isset($_SESSION["length"])) {
        $profile_viewer_uid = $_SESSION["length"];
        // Add the discount based on the value of $profile_viewer_uid
        switch ($profile_viewer_uid) {
            case 1:
                $cart->add_fee('NFT Discount', -10);
                break;
            case 2:
                $cart->add_fee('NFT Discount', -20);
                break;
            case 3:
                $cart->add_fee('NFT Discount', -30);
                break;
            default:
                $cart->add_fee('NFT Discount', -1);
                break;
        }
		
    } else { */
        // If the session variable is not set, add zero discount
		//$_SESSION["length"] = $_COOKIE['profile_viewer_uid'] ? $_COOKIE['profile_viewer_uid'] : null
		
    //}
	if(isset($_COOKIE['nftLength'])){
		$cart->add_fee('NFT Discount', -10);
	}else{
		$cart->add_fee('NFT Discount', 0);

	}
}
