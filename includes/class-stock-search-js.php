<?php
class ASA_Stock_Search_JS {
    public static function init() {
        add_action('wp_enqueue_scripts', ['ASA_Stock_Search_JS', 'enqueue_select2_assets_for_stock_dropdown']);
    }

    public static function enqueue_select2_assets_for_stock_dropdown() {
        // Enqueue Select2 CSS
        if(is_page(get_option('asa_prediction_page'))){
        wp_enqueue_style(
            'select2-css',
            'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
            array(),
            '4.1.0'
        );
    
        // Enqueue Select2 JS
        wp_enqueue_script(
            'select2-js',
            'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
            array('jquery'),
            '4.1.0',
            true
        );
    
        // Initialize Select2 on your specific <select> field
        wp_add_inline_script('select2-js', "
            jQuery(document).ready(function() {
                jQuery('#stock_symbol').select2({
                    placeholder: 'Search for a stock',
                    allowClear: true
                });
            });
        ");
        }
    }
    
}