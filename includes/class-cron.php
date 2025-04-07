<?php
class ASA_Cron {
    public static function init() {
        add_action('asa_daily_stock_update', ['ASA_Stock_Updater', 'update_nse_stocks']);
    }

    public static function activate() {
        if (!wp_next_scheduled('asa_daily_stock_update')) {
            wp_schedule_event(time(), 'daily', 'asa_daily_stock_update');
        }
    }

    public static function deactivate() {
        wp_clear_scheduled_hook('asa_daily_stock_update');
    }
}