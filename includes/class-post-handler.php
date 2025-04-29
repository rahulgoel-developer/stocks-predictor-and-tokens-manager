<?php
class ASA_Post_Handler {
    public static function init() {
        add_action('admin_post_nopriv_my_custom_action', ['ASA_Stocks_Prediction_Page', 'assign_stock_to_user_handler']);
    }
}