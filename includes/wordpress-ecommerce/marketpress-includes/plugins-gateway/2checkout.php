<?php
/*
MarketPress 2Checkout Gateway Plugin
Author: S H Mohanjith ( Incsub )
*/
class MP_Gateway_2Checkout extends MP_Gateway_API {}
//register payment gateway plugin
mp_register_gateway_plugin( 'MP_Gateway_2Checkout', '2checkout', __( '2Checkout', 'mp' ), false, true );
?>