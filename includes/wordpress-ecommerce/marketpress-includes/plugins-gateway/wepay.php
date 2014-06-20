<?php
/*
  MarketPress WePay Gateway Plugin
  Author: Marko Miljus (Incsub)
 */
class MP_Gateway_Wepay extends MP_Gateway_API {}

//register payment gateway plugin
mp_register_gateway_plugin('MP_Gateway_Wepay', 'wepay', __('WePay', 'mp'), false, true );