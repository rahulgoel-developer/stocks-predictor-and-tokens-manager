<?php
class ASA_Price_Updater {
    public static function record_all_selected_stocks_price() {
        global $wpdb;

        // 1) Get all unique user-chosen symbols + ISINs
        $stocks = ASA_DB::get_all_selected_stocks_isin();

        if ( empty( $stocks ) ) {
            error_log( 'ASA_Price_Updater: No stocks to fetch prices for.' );
            return;
        }

        // 2) Table name for live prices
        $live_prices_table = $wpdb->prefix . 'asa_live_prices';

        // 3) Your Upstox access token (e.g. define in wp-config.php or .env)
        $accessToken = defined('UPSTOX_ACCESS_TOKEN') ? UPSTOX_ACCESS_TOKEN : '';

        if ( empty( $accessToken ) ) {
            error_log( 'ASA_Price_Updater: Missing UPSTOX_ACCESS_TOKEN.' );
            return;
        }

        foreach ( $stocks as $stock ) {
            // Build instrument key: NSE_EQ|<ISIN>
            $instrument_key = 'NSE_EQ|' . $stock->isin;

            // 4) Prepare request
            $url = "https://api.upstox.com/v3/market-quote/ltp?instrument_key=" . rawurlencode( $instrument_key );
            $args = [
                'timeout'   => 15,
                'sslverify' => false,
                'headers'   => [
                    'Accept'        => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ];

            // Fetch via WP HTTP API :contentReference[oaicite:1]{index=1}
            $response = wp_remote_get( $url, $args );

            if ( is_wp_error( $response ) ) {
                error_log( "ASA_Price_Updater: HTTP error for {$instrument_key}: " . $response->get_error_message() );
                continue;
            }

            $body = wp_remote_retrieve_body( $response );
            $data = json_decode( $body, true );

            // 5) Extract price (assumes structure: ['data']['ltp'] or ['ltp'])
            $ltp = null;
            if ( isset( $data['data']['ltp'] ) ) {
                $ltp = floatval( $data['data']['ltp'] );
            } elseif ( isset( $data['ltp'] ) ) {
                $ltp = floatval( $data['ltp'] );
            } else {
                error_log( "ASA_Price_Updater: Unable to find 'ltp' in response for {$instrument_key}." );
                continue;
            }

            // 6) Insert into live_prices table
            $inserted = $wpdb->insert(
                $live_prices_table,
                [
                    'stock_symbol' => sanitize_text_field( $stock->stock_symbol ),
                    'price'        => $ltp,
                    'recorded_at'  => current_time( 'mysql' ),
                ],
                [ '%s', '%f', '%s' ]
            );

            if ( false === $inserted ) {
                error_log( "ASA_Price_Updater: DB insert failed for {$stock->stock_symbol}: " . $wpdb->last_error );
            }
        }

        error_log( 'ASA_Price_Updater: Price recording completed for ' . count( $stocks ) . ' instruments.' );
    }
}
