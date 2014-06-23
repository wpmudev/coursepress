<?php
/*
MarketPress PayPal Chained Payments Gateway Plugin
Author: Aaron Edwards ( Incsub )
*/

class MP_Gateway_Paypal_Chained_Payments extends MP_Gateway_API {}
mp_register_gateway_plugin( 'MP_Gateway_Paypal_Chained_Payments', 'paypal-chained', __( 'PayPal Chained Payments', 'mp' ), false, true );
?>