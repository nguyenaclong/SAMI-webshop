<?php
require_once '/var/www/html/wp-load.php';

// Enable COD and BACS gateways
$cod = get_option('woocommerce_cod_settings', array());
$cod['enabled'] = 'yes';
update_option('woocommerce_cod_settings', $cod);

$bacs = get_option('woocommerce_bacs_settings', array());
$bacs['enabled'] = 'yes';
update_option('woocommerce_bacs_settings', $bacs);

$gateways = WC()->payment_gateways->get_available_payment_gateways();
echo "Available Payment Gateways: " . implode(', ', array_keys($gateways)) . "\n";
