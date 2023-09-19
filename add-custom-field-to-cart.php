<?php
/**
 * Plugin Name: Add Custom Field to Cart
 * Plugin URI: https://www.skyverge.com/blog/add-custom-data-woocommerce-cart-items/
 * Description: Add a custom field to WooCommerce cart items
 * Version: 1.0

 * Add a text field to each cart item
 */
function prefix_after_cart_item_name($cart_item, $cart_item_key)
{
    $notes = isset($cart_item['notes']) ? $cart_item['notes'] : '';
    printf(
        '<div><textarea class="%s" id="cart_notes_%s" data-cart-id="%s">%s</textarea></div>',
        'prefix-cart-notes',
        $cart_item_key,
        $cart_item_key,
        $notes
    );
}
add_action('woocommerce_after_cart_item_name', 'prefix_after_cart_item_name', 10, 2);

/**
 * Enqueue our JS file
 */
function prefix_enqueue_scripts()
{
    wp_register_script('prefix-script', trailingslashit(plugin_dir_url(__FILE__)) . 'update-cart-item-ajax.js', array('jquery-blockui'), time(), true);
    wp_localize_script(
        'prefix-script',
        'prefix_vars',
        array(
            'ajaxurl' => admin_url('admin-ajax.php')
        )
    );
    wp_enqueue_script('prefix-script');
}
add_action('wp_enqueue_scripts', 'prefix_enqueue_scripts');

/**
 * Update custom field value for cart item using AJAX
 */
add_action('wp_ajax_prefix_update_cart_notes', 'prefix_update_cart_notes');
add_action('wp_ajax_nopriv_prefix_update_cart_notes', 'prefix_update_cart_notes');
function prefix_update_cart_notes()
{
    if (isset($_POST['cart_id']) && isset($_POST['notes'])) {
        $cart_item_key = sanitize_text_field($_POST['cart_id']);
        $notes = sanitize_text_field($_POST['notes']);

        // Update the custom field value for the cart item
        WC()->cart->cart_contents[$cart_item_key]['notes'] = $notes;
        WC()->cart->set_session();

        echo 'success'; // Return a response to the AJAX call
    }

    wp_die(); // Always add this at the end of AJAX functions
}

/**
 * Add custom field value to order item meta
 */

function prefix_checkout_create_order_line_item($item, $cart_item_key, $values, $order)
{
    foreach ($item as $cart_item_key => $cart_item) {
        if (isset($cart_item['notes'])) {
            $item->add_meta_data('notes', $cart_item['notes'], true);
        }
    }
}

add_action('woocommerce_checkout_create_order_line_item', 'prefix_checkout_create_order_line_item', 10, 4);

/**
 * Display custom field value on the order edit page
 */
function prefix_display_order_notes($item, $item_id, $product)
{
    echo '<p><strong>' . __('Notes', 'woocommerce') . ':</strong> ' . wc_get_order_item_meta($item_id, 'notes', true) . '</p>';
}

add_action('woocommerce_before_order_itemmeta', 'prefix_display_order_notes', 10, 3);


