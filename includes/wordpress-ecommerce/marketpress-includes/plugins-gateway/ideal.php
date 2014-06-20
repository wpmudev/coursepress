<?php
/*
MarketPress iDeal Gateway Plugin
Author: Remi Schouten
*/

class MP_Gateway_IDeal extends MP_Gateway_API {}

mp_register_gateway_plugin( 'MP_Gateway_IDeal', 'ideal', __('iDEAL (beta)', 'mp'), false, true );
?>