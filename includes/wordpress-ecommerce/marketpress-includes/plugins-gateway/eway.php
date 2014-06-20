<?php
/*
MarketPress eWay Gateway Plugin
Author: Aaron Edwards (Incsub)
*/
class MP_Gateway_eWay_Shared extends MP_Gateway_API {}
mp_register_gateway_plugin( 'MP_Gateway_eWay_Shared', 'eway', __('eWay Shared Payments', 'mp'), false, true );
?>