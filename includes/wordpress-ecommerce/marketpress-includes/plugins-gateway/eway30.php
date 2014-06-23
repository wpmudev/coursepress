<?php
/*
MarketPress eWay Rapid 3.0 Payments Gateway Plugin
Author: Mariusz Maniu ( Incsub )
*/

class MP_Gateway_eWay30 extends MP_Gateway_API {}

mp_register_gateway_plugin( 'MP_Gateway_eWay30', 'eway30', __( 'eWay Rapid 3.0 Payments', 'mp' ), false, true );