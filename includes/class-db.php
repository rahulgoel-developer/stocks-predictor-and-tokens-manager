<?php
class ASA_DB {
    public static function activate() {
        global $wpdb;
        
        $tokens_table = $wpdb->prefix . 'asa_user_tokens';
        $stocks_table = $wpdb->prefix . 'asa_stocks_list';
        $charset_collate = $wpdb->get_charset_collate();

        $sql_tokens = "CREATE TABLE IF NOT EXISTS $tokens_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL UNIQUE,
            tokens int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (id)
        ) $charset_collate;";

        $sql_stocks = "CREATE TABLE IF NOT EXISTS $stocks_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            symbol varchar(50) NOT NULL,
            company_name varchar(255) NOT NULL,
            isin varchar(20) NOT NULL,
            series varchar(10) NOT NULL,
            listing_date date NOT NULL,
            last_updated datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY symbol (symbol)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_tokens);
        dbDelta($sql_stocks);
        
        self::seed_initial_data();
    }

    private static function seed_initial_data() {
        ASA_Stock_Updater::update_nse_stocks();
    }

    public static function add_tokens($user_id, $tokens) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'asa_user_tokens';
        
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT tokens FROM $table_name WHERE user_id = %d", 
            $user_id
        ));
        
        if ($existing === null) {
            $wpdb->insert($table_name, [
                'user_id' => $user_id,
                'tokens' => $tokens
            ], ['%d', '%d']);
        } else {
            $wpdb->update($table_name, 
                ['tokens' => $existing + $tokens],
                ['user_id' => $user_id],
                ['%d'],
                ['%d']
            );
        }
    }

    public static function get_user_tokens($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'asa_user_tokens';
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT tokens FROM $table_name WHERE user_id = %d", 
            $user_id
        ));
    }

    public static function get_stocks_list() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'asa_stocks_list';
        
        return $wpdb->get_results("
            SELECT symbol, company_name, listing_date 
            FROM $table_name
            WHERE series = 'EQ'
            ORDER BY company_name ASC
        ");
    }
}