<?php
/*
  MarketPress PIN Gateway (www.pin.net.au) Plugin
  Author: Marko Miljus (Incsub)
 */

class MP_Gateway_PIN extends MP_Gateway_API {}
mp_register_gateway_plugin('MP_Gateway_PIN', 'pin', __('PIN', 'mp'), false, true);