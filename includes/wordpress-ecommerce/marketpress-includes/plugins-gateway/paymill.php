<?php
/*
  MarketPress Paymill Gateway Plugin
  Author: Marko Miljus
 */

class MP_Gateway_Paymill extends MP_Gateway_API {}
mp_register_gateway_plugin('MP_Gateway_Paymill', 'paymill', __('Paymill', 'mp'), false, true);