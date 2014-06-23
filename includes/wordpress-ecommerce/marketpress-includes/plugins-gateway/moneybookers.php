<?php
/*
MarketPress Moneybookers Gateway Plugin
Author: Aaron Edwards
*/

class MP_Gateway_Moneybookers extends MP_Gateway_API {}
mp_register_gateway_plugin( 'MP_Gateway_Moneybookers', 'moneybookers', __( 'Moneybookers', 'mp' ), false, true );
?>