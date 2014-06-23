<?php
/*
MarketPress UPS Calculated Shipping Plugin
Author: Arnold Bailey ( Incsub )
*/
class MP_Shipping_UPS extends MP_Shipping_API {}
mp_register_shipping_plugin( 'MP_Shipping_UPS', 'ups', __( 'UPS', 'mp' ), true, true );