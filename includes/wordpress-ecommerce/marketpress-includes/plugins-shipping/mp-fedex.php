<?php
/*
MarketPress Example Shipping Plugin Template
*/

class MP_Shipping_FedEx extends MP_Shipping_API {}
mp_register_shipping_plugin('MP_Shipping_FedEx', 'fedex', __('FedEx', 'mp'), true, true );