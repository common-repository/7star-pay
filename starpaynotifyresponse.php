<?php
    require_once( explode( "wp-content" , __FILE__ )[0] . "wp-load.php" );
    $return_data = array(
        'code' => '0',
        'orderId' => sanitize_text_field($_GET['orderId'])
    );
    esc_textarea(json_encode( $return_data ));
?>
