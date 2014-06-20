<?php
/*
MarketPress Authorize.net AIM Gateway Plugin
Author: S H Mohanjith (Incsub)
*/

class MP_Gateway_AuthorizeNet_AIM extends MP_Gateway_API {}
mp_register_gateway_plugin( 'MP_Gateway_AuthorizeNet_AIM', 'authorizenet-aim', __('Authorize.net AIM Checkout', 'mp'), false, true );
?>