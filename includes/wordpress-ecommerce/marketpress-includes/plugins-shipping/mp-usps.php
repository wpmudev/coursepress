<?php
/*
MarketPress USPS Calculated Shipping Plugin
Author: Arnold Bailey ( Incsub )
*/
class MP_Shipping_USPS extends MP_Shipping_API {}

mp_register_shipping_plugin( 'MP_Shipping_USPS', 'usps', __( 'USPS', 'mp' ), true, true );
?>