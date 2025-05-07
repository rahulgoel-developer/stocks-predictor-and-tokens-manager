<?php
class ASA_Cron {
    public static function init() {
        add_action('asa_daily_stock_update', ['ASA_Stock_Updater', 'update_nse_stocks']);
        add_action('asa_hourly_stock_price_update', ['ASA_Price_Updater', 'record_all_selected_stocks_price']);
    }

    public static function activate() {
        if (!wp_next_scheduled('asa_daily_stock_update')) {
            wp_schedule_event(time(), 'daily', 'asa_daily_stock_update');
        }
        if (!wp_next_scheduled('asa_hourly_stock_price_update')) {
            wp_schedule_event(time(), 'hourly', 'asa_hourly_stock_price_update');
        }
    }

    public static function deactivate() {
        wp_clear_scheduled_hook('asa_daily_stock_update');
    }
}