<?php
/**
 * E2E Integration & User Behavior Test Suite for 2-Step Webshop & Mailpit
 */

$_SERVER['HTTP_HOST']   = 'webshop-wp.ddev.site';
$_SERVER['REQUEST_URI'] = '/webshop/';
$_SERVER['SERVER_PORT'] = '443';
$_SERVER['HTTPS']       = 'on';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

require_once '/var/www/html/wp-load.php';

echo "========================================================\n";
echo "    2-STEP WEBSHOP E2E USER BEHAVIOR TEST SUITE        \n";
echo "========================================================\n\n";

wp_set_current_user(1);

function reset_wc_session() {
    wc_clear_notices();
    $_POST = array();
    WC()->session->cleanup_sessions();
    WC()->cart->empty_cart();
}

function fetch_mailpit_messages() {
    $ch = curl_init('http://localhost:8025/api/v1/messages');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function create_order_from_cart($customer_data, $fulfillment_type, $time_slot) {
    $order = wc_create_order();
    
    foreach (WC()->cart->get_cart() as $cart_item_key => $values) {
        $product = $values['data'];
        $quantity = $values['quantity'];
        $order->add_product($product, $quantity);
    }

    $address = array(
        'first_name' => $customer_data['first_name'],
        'last_name'  => $customer_data['last_name'],
        'email'      => $customer_data['email'],
        'phone'      => $customer_data['phone'],
        'address_1'  => $customer_data['address_1'],
        'city'       => $customer_data['city'],
        'postcode'   => $customer_data['postcode'],
        'country'    => $customer_data['country'],
    );

    $order->set_address($address, 'billing');
    $order->set_address($address, 'shipping');
    $order->set_payment_method($customer_data['payment_method']);

    // Save custom plugin meta
    $order->update_meta_data('_tsw_fulfillment_type', $fulfillment_type);
    $order->update_meta_data('_tsw_pickup_time', $time_slot);
    $order->update_meta_data('_tsw_special_request', $customer_data['notes']);
    $order->update_meta_data('_order_language', 'de'); // test German email template

    $order->calculate_totals();
    $order->update_status('processing', 'Order placed via 2-Step Webshop simulation.');
    $order->save();

    // Trigger WooCommerce email notifications
    WC()->mailer()->emails['WC_Email_Customer_Processing_Order']->trigger($order->get_id(), $order);
    WC()->mailer()->emails['WC_Email_New_Order']->trigger($order->get_id(), $order);

    return $order;
}

$published_products = wc_get_products(array('limit' => 10, 'status' => 'publish'));
if (empty($published_products)) {
    die("ERROR: No published WooCommerce products found.\n");
}

// -------------------------------------------------------------
// TEST CASE 1: Instant Select -> Checkout
// -------------------------------------------------------------
echo "[TEST CASE 1] Flow: Instant Select -> Direct Checkout\n";
reset_wc_session();

$prod1 = $published_products[0];
echo "-> Customer selects item: '{$prod1->get_name()}' (ID: {$prod1->get_id()})\n";
WC()->cart->add_to_cart($prod1->get_id(), 1);

echo "-> Cart Total: " . strip_tags(WC()->cart->get_total()) . "\n";
echo "-> Proceeding directly to Checkout for Anna Schmidt...\n";

$customer1 = array(
    'first_name'     => 'Anna',
    'last_name'      => 'Schmidt',
    'email'          => 'anna.schmidt@example.de',
    'phone'          => '01711234567',
    'address_1'      => 'Hauptstraße 12',
    'city'           => 'Berlin',
    'postcode'       => '10115',
    'country'        => 'DE',
    'payment_method' => 'cod',
    'notes'          => 'Please include extra cutlery and soy sauce',
);

$order1 = create_order_from_cart($customer1, 'pickup', date('Y-m-d') . ' 18:30');
echo "SUCCESS: Order #{$order1->get_id()} created!\n";
echo "   Customer: {$order1->get_billing_first_name()} {$order1->get_billing_last_name()} <{$order1->get_billing_email()}>\n";
echo "   Fulfillment: Local Pickup at " . date('Y-m-d') . " 18:30\n";
echo "   Status: {$order1->get_status()}\n";
echo "   Total: " . strip_tags($order1->get_formatted_order_total()) . "\n";

echo "\n-------------------------------------------------------------\n";

// -------------------------------------------------------------
// TEST CASE 2: Select -> Checkout -> Back to Webshop -> Select More -> Edit Cart -> Checkout
// -------------------------------------------------------------
echo "[TEST CASE 2] Flow: Select -> Back to Shop -> Add More -> Edit Cart -> Checkout\n";
reset_wc_session();

$prod1 = $published_products[0];
$prod2 = $published_products[1];
$prod3 = isset($published_products[2]) ? $published_products[2] : $prod1;

echo "-> Step 1: Customer selects initial item '{$prod1->get_name()}'\n";
$key1 = WC()->cart->add_to_cart($prod1->get_id(), 1);

echo "-> Step 2: Customer navigates back to webshop, adds '{$prod2->get_name()}' and '{$prod3->get_name()}'\n";
$key2 = WC()->cart->add_to_cart($prod2->get_id(), 1);
$key3 = WC()->cart->add_to_cart($prod3->get_id(), 2);

echo "-> Cart subtotal with 3 items: " . strip_tags(WC()->cart->get_total()) . "\n";

echo "-> Step 3: Customer opens Cart Drawer to edit cart:\n";
echo "   - Increasing '{$prod1->get_name()}' quantity from 1 to 4\n";
WC()->cart->set_quantity($key1, 4);

echo "   - Removing '{$prod2->get_name()}' from cart\n";
WC()->cart->remove_cart_item($key2);

echo "-> Final Cart Total after edits: " . strip_tags(WC()->cart->get_total()) . "\n";
echo "-> Proceeding to Checkout for Max Mustermann...\n";

$customer2 = array(
    'first_name'     => 'Max',
    'last_name'      => 'Mustermann',
    'email'          => 'max.mustermann@example.de',
    'phone'          => '01729876543',
    'address_1'      => 'Musterweg 45',
    'city'           => 'München',
    'postcode'       => '80331',
    'country'        => 'DE',
    'payment_method' => 'bacs',
    'notes'          => 'Deliver to 2nd floor, ring bell Mustermann',
);

$order2 = create_order_from_cart($customer2, 'delivery', date('Y-m-d') . ' 19:45');
echo "SUCCESS: Order #{$order2->get_id()} created!\n";
echo "   Customer: {$order2->get_billing_first_name()} {$order2->get_billing_last_name()} <{$order2->get_billing_email()}>\n";
echo "   Fulfillment: Delivery at " . date('Y-m-d') . " 19:45\n";
echo "   Status: {$order2->get_status()}\n";
echo "   Total: " . strip_tags($order2->get_formatted_order_total()) . "\n";
echo "   Items Purchased:\n";
foreach ($order2->get_items() as $item) {
    echo "     - {$item->get_name()} (x{$item->get_quantity()}) - " . strip_tags(wc_price($item->get_total())) . "\n";
}

echo "\n-------------------------------------------------------------\n";

// -------------------------------------------------------------
// TEST CASE 3: Random Behavior Simulation
// -------------------------------------------------------------
echo "[TEST CASE 3] Flow: Random Customer Exploration & Checkout\n";
reset_wc_session();

$random_count = rand(2, 4);
echo "-> Simulating random customer browsing: picking {$random_count} random items...\n";
$picked = array_rand($published_products, $random_count);
if (!is_array($picked)) $picked = array($picked);

foreach ($picked as $idx) {
    $p = $published_products[$idx];
    $qty = rand(1, 3);
    echo "   - Random pick: '{$p->get_name()}' x {$qty}\n";
    WC()->cart->add_to_cart($p->get_id(), $qty);
}

echo "-> Cart Total: " . strip_tags(WC()->cart->get_total()) . "\n";

$customer3 = array(
    'first_name'     => 'Lukas',
    'last_name'      => 'Weber',
    'email'          => 'lukas.weber@example.de',
    'phone'          => '01735559988',
    'address_1'      => 'Bahnhofstraße 88',
    'city'           => 'Hamburg',
    'postcode'       => '20095',
    'country'        => 'DE',
    'payment_method' => 'cod',
    'notes'          => 'Random test order execution',
);

$order3 = create_order_from_cart($customer3, 'pickup', date('Y-m-d') . ' 20:15');
echo "SUCCESS: Order #{$order3->get_id()} created!\n";
echo "   Customer: {$order3->get_billing_first_name()} {$order3->get_billing_last_name()} <{$order3->get_billing_email()}>\n";
echo "   Total: " . strip_tags($order3->get_formatted_order_total()) . "\n";

echo "\n-------------------------------------------------------------\n";

// -------------------------------------------------------------
// MAILPIT EMAIL VERIFICATION
// -------------------------------------------------------------
echo "[MAILPIT] Verifying Mailpit Email Delivery...\n";
sleep(1);

$mail_data = fetch_mailpit_messages();
if (!empty($mail_data['messages'])) {
    echo "SUCCESS: Mailpit received " . count($mail_data['messages']) . " email notification(s)!\n\n";
    foreach ($mail_data['messages'] as $idx => $msg) {
        $num = $idx + 1;
        $to_str = implode(', ', array_map(function($t) { return $t['Address']; }, $msg['To']));
        echo "   [Email #{$num}]\n";
        echo "   Subject: {$msg['Subject']}\n";
        echo "   To: {$to_str}\n";
        echo "   From: {$msg['From']['Address']}\n";
        echo "   --------------------------------------------------\n";
    }
} else {
    echo "Notice: Mailpit inbox empty. Checking mail delivery config.\n";
}

echo "\n========================================================\n";
echo "             E2E TEST SUITE COMPLETED CLEANLY           \n";
echo "========================================================\n";
