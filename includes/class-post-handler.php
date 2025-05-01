<?php
class ASA_Post_Handler {
    public static function init() {
        add_action('init', ['ASA_Stocks_Prediction_Page', 'assign_stock_to_user']);
    }
}