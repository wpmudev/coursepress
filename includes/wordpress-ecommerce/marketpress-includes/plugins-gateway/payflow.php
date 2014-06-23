<?php
/*
MarketPress Payflow Pro Gateway Plugin
Author: Sue Cline ( Cyclonic Consulting )
*/

class MP_Gateway_Payflow extends MP_Gateway_API {}

//register payment gateway plugin
mp_register_gateway_plugin( 'MP_Gateway_Payflow', 'payflow', __( 'PayPal Payflow Pro', 'mp' ), false, true );
?>