<?php
class ASA_Stock_Updater {
    public static function update_nse_stocks() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'asa_stocks_list';
        
        $csv_url = 'https://archives.nseindia.com/content/equities/EQUITY_L.csv';
        
        $response = wp_remote_get($csv_url, [
            'timeout' => 30,
            'sslverify' => false
        ]);

        if (!is_wp_error($response)) {
            $csv_data = wp_remote_retrieve_body($response);
            $lines = preg_split('/\r\n|\r|\n/', $csv_data);
            
            // Remove empty lines and header
            $lines = array_filter($lines);
            $header = array_shift($lines);
            
            foreach ($lines as $line) {
                $data = str_getcsv($line);
                if (count($data) === 8) {
                    try {
                        $wpdb->replace($table_name, [
                            'symbol' => sanitize_text_field($data[0]),
                            'company_name' => sanitize_text_field($data[1]),
                            'series' => sanitize_text_field($data[2]),
                            'listing_date' => date('Y-m-d', strtotime($data[3])),
                            'isin' => sanitize_text_field($data[6]),
                            'last_updated' => current_time('mysql')
                        ], ['%s', '%s', '%s', '%s', '%s', '%s']);
                    } catch (Exception $e) {
                        error_log('Stock insert error: ' . $e->getMessage());
                    }
                }
            }
            
            error_log('NSE stock update completed. Processed ' . count($lines) . ' records');
        } else {
            error_log('NSE data fetch error: ' . $response->get_error_message());
        }
    }
}