<?php
class ASA_Cron {
    public static function init() {
        // Add custom cron schedules
        add_filter('cron_schedules', [self::class, 'add_custom_schedules']);
        
        // Hook up scheduled events
        add_action('asa_daily_stock_update', ['ASA_Stock_Updater', 'update_nse_stocks']);
        add_action('asa_hourly_stock_price_update', ['ASA_Price_Updater', 'record_all_selected_stocks_price']);
    }

    public static function add_custom_schedules($schedules) {
        $schedules['one_minute'] = [
            'interval' => 60, // 1 minute in seconds
            'display'  => __('Every Minute')
        ];
        return $schedules;
    }

    public static function activate() {
        // Daily stock update (existing)
        if (!wp_next_scheduled('asa_daily_stock_update')) {
            wp_schedule_event(time(), 'daily', 'asa_daily_stock_update');
        }
        
        // Changed from hourly to 1-minute interval
        if (!wp_next_scheduled('asa_hourly_stock_price_update')) {
            wp_schedule_event(time(), 'one_minute', 'asa_hourly_stock_price_update');
        }
    }

    public static function deactivate() {
        wp_clear_scheduled_hook('asa_daily_stock_update');
        wp_clear_scheduled_hook('asa_hourly_stock_price_update');
    }
}