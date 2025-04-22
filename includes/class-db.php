<?php
class ASA_DB {
    public static function activate() {
        global $wpdb;
        
        $tokens_table = $wpdb->prefix . 'asa_user_tokens';
        $stocks_table = $wpdb->prefix . 'asa_stocks_list';
        $chosen_table      = $wpdb->prefix . 'asa_user_chosen_stocks_to_predict';
        $live_prices_table = $wpdb->prefix . 'asa_live_prices';
        $predictions_table = $wpdb->prefix . 'asa_predictions';
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

        $sql_chosen = "CREATE TABLE IF NOT EXISTS $chosen_table (
            id             bigint(20)    NOT NULL AUTO_INCREMENT,
            user_id        bigint(20)    NOT NULL,
            stock_symbol   varchar(50)    NOT NULL,
            chosen_at      datetime      NOT NULL,
            PRIMARY KEY (id),
            INDEX idx_user        (user_id),
            INDEX idx_stock       (stock_symbol),
            FOREIGN KEY (user_id)      REFERENCES $tokens_table(user_id) ON DELETE CASCADE,
            FOREIGN KEY (stock_symbol) REFERENCES $stocks_table(symbol) ON DELETE CASCADE
        ) $charset_collate;";

        $sql_live_prices = "CREATE TABLE IF NOT EXISTS $live_prices_table (
            id            bigint(20)    NOT NULL AUTO_INCREMENT,
            stock_symbol  varchar(50)    NOT NULL,
            price         decimal(10,2) NOT NULL,
            recorded_at   datetime      NOT NULL,
            PRIMARY KEY (id),
            INDEX idx_symbol (stock_symbol),
            FOREIGN KEY (stock_symbol) REFERENCES $stocks_table(symbol) ON DELETE CASCADE
        ) $charset_collate;";

        $sql_predictions = "CREATE TABLE IF NOT EXISTS $predictions_table (
            id                   bigint(20)    NOT NULL AUTO_INCREMENT,
            stock_symbol         varchar(50)    NOT NULL,
            predicted_price      decimal(10,2) NOT NULL,
            prediction_for_time  datetime      NOT NULL,
            PRIMARY KEY (id),
            INDEX idx_symbol (stock_symbol),
            FOREIGN KEY (stock_symbol) REFERENCES $stocks_table(symbol) ON DELETE CASCADE
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        dbDelta( $sql_tokens );
        dbDelta( $sql_stocks );
        dbDelta( $sql_chosen );
        dbDelta( $sql_live_prices );
        dbDelta( $sql_predictions );

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

    public static function add_chosen_stock( $user_id, $symbol ) {
        global $wpdb;
        $table = $wpdb->prefix . 'asa_user_chosen_stocks_to_predict';
        $wpdb->insert( $table, [
            'user_id'      => $user_id,
            'stock_symbol' => $symbol,
            'chosen_at'    => current_time( 'mysql' ),
        ], ['%d','%s','%s'] );
    }

    public static function record_live_price( $symbol, $price ) {
        global $wpdb;
        $table = $wpdb->prefix . 'asa_live_prices';
        $wpdb->insert( $table, [
            'stock_symbol' => $symbol,
            'price'        => $price,
            'recorded_at'  => current_time( 'mysql' ),
        ], ['%s','%f','%s'] );
    }

    public static function add_prediction( $symbol, $predicted_price, $prediction_for_time ) {
        global $wpdb;
        $table = $wpdb->prefix . 'asa_predictions';
        $wpdb->insert( $table, [
            'stock_symbol'        => $symbol,
            'predicted_price'     => $predicted_price,
            'prediction_for_time' => $prediction_for_time,
        ], ['%s','%f','%s'] );
    }



}