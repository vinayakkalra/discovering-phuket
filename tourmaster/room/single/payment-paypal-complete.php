<?php 

    get_header();


    echo '<div class="tourmaster-page-wrapper" id="tourmaster-room-payment-page" >';
    echo '<div class="tourmaster-container clearfix" >';

    tourmaster_room_payment_step(4);

    echo '<div class="tourmaster-payment-step clearfix" >';
    echo '<div class="tourmaster-room-complete-booking-wrap tourmaster-item-pdlr" >';
    echo tourmaster_room_booking_paypal_complete();
    echo '</div>';
    echo '</div>';

    echo '</div>'; // tourmaster-container
    echo '</div>'; // tourmaster-page-wrapper

    get_footer(); 

?>