<?php
/*
MarketPress Stripe Gateway Plugin
Author: Aaron Edwards
*/

class MP_Gateway_Stripe extends MP_Gateway_API {}
 
//register payment gateway plugin
mp_register_gateway_plugin( 'MP_Gateway_Stripe', 'stripe', __('Stripe', 'mp'), false, true );
?>