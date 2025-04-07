<?php
class ASA_WooCommerce {
    public function __construct() {
        add_action('woocommerce_payment_complete', [$this, 'handle_payment']);
    }

    public function handle_payment($order_id) {
        if (!class_exists('WooCommerce')) return;

        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();
        
        if (!$user_id) return;
        
        $product_mapping = get_option('asa_product_tokens');
        
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $lines = explode("\n", $product_mapping);
            
            foreach ($lines as $line) {
                $parts = explode(':', trim($line));
                if (count($parts) === 2) {
                    list($mapped_id, $tokens) = $parts;
                    if ($product_id == $mapped_id) {
                        ASA_DB::add_tokens($user_id, intval($tokens));
                    }
                }
            }
        }
    }
}