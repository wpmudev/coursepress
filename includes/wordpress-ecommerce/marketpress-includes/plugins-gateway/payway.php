<?php
/*
MarketPress Payway Gateway Plugin
Author: Mindblaze(Rashid Ali)
*/

class MP_Gateway_PayWay extends MP_Gateway_API {}
mp_register_gateway_plugin( 'MP_Gateway_PayWay', 'payway', __('PayWay (beta)', 'mp'), false, true );