<?php
/*
MarketPress CubePoints Plugin
Requires the CubePoints plugin: http://wordpress.org/extend/plugins/cubepoints/
Author: David Mallonee ( Incsub )
*/
class MP_Gateway_CubePoints extends MP_Gateway_API {}
mp_register_gateway_plugin( 'MP_Gateway_CubePoints', 'cubepoints', __( 'CubePoints', 'mp' ), false, true );
?>