<?php
/*
MarketPress Example Shipping Plugin Template
*/

class MP_Shipping_Pickup extends MP_Shipping_API {}

//register plugin - uncomment to register
mp_register_shipping_plugin( 'MP_Shipping_Pickup', 'pickup', __('Pickup', 'mp'), true, true );