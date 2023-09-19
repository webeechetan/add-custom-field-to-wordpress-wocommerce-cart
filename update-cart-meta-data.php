<?php

/**
 * Update cart item notes
 */
function prefix_update_cart_notes()
{
    // Do a nonce check
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'woocommerce-cart')) {
        wp_send_json(array('nonce_fail' => 1));
        exit;
    }
    // Save the notes to the cart meta
    $cart = WC()->cart->cart_contents;
    $cart_id = $_POST['cart_id'];
    $notes = $_POST['notes'];
    $cart_item = $cart[$cart_id];
    $cart_item['notes'] = $notes;
    WC()->cart->cart_contents[$cart_id] = $cart_item;
    WC()->cart->set_session();
    wp_send_json(array('success' => 1));
    exit;
}
add_action('wp_ajax_prefix_update_cart_notes', 'prefix_update_cart_notes');
add_action('wp_ajax_nopriv_prefix_update_cart_notes', 'prefix_update_cart_notes');

function prefix_checkout_create_order_line_item($item, $cart_item_key, $values, $order)
{
    foreach ($item as $cart_item_key => $cart_item) {
        if (isset($cart_item['notes'])) {
            $item->add_meta_data('notes', $cart_item['notes'], true);
        }
    }
}
add_action('woocommerce_checkout_create_order_line_item', 'prefix_checkout_create_order_line_item', 10, 4);
