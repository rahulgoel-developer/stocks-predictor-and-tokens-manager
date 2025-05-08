<?php
class ASA_Price_Updater {
    public static function record_all_selected_stocks_price() {
        global $wpdb;

        $stocks = ASA_DB::get_all_selected_stocks_isin();
        if ( empty( $stocks ) ) {
            error_log( 'ASA_Price_Updater: No stocks to fetch prices for.' );
            return;
        }

        $live_table  = $wpdb->prefix . 'asa_live_prices';
        $accessToken = getenv( 'UPSTOX_ACCESS_TOKEN' );
        if ( empty( $accessToken ) ) {
            error_log( 'ASA_Price_Updater: Missing UPSTOX_ACCESS_TOKEN.' );
            return;
        }

        foreach ( $stocks as $stock ) {
            $symbol_param = 'NSE_EQ|' . $stock->isin;

            // v2 endpoint for LTP
            $url = 'https://api.upstox.com/v2/market-quote/ltp?symbol=' . rawurlencode( $symbol_param );
            $ch  = curl_init( $url );
            curl_setopt_array( $ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => [
                    "Authorization: Bearer {$accessToken}",
                    "Accept: application/json",
                ],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT        => 15,
            ] );
            $body = curl_exec( $ch );
            $err  = curl_error( $ch );
            curl_close( $ch );

            if ( $err ) {
                error_log( "ASA_Price_Updater: cURL error for {$symbol_param}: {$err}" );
                continue;
            }

            $data = json_decode( $body, true );
            if ( empty( $data['status'] ) || 'success' !== $data['status'] ) {
                error_log( "ASA_Price_Updater: API error for {$symbol_param}: " . var_export( $data, true ) );
                continue;
            }

            // ** New extraction logic **:
            // Find the entry in data[] whose 'instrument_token' matches our request
            $price = null;
            foreach ( $data['data'] as $entry ) {
                if ( isset( $entry['instrument_token'] ) && $entry['instrument_token'] === $symbol_param ) {
                    $price = floatval( $entry['last_price'] );
                    break;
                }
            }

            if ( null === $price ) {
                error_log( "ASA_Price_Updater: Unable to extract price for {$symbol_param}. Response: " . var_export( $data, true ) );
                continue;
            }

            // Insert into DB
            $inserted = $wpdb->insert(
                $live_table,
                [
                    'stock_symbol' => sanitize_text_field( $stock->stock_symbol ),
                    'price'        => $price,
                    'recorded_at'  => current_time( 'mysql' ),
                ],
                [ '%s', '%f', '%s' ]
            );

            if ( false === $inserted ) {
                error_log( "ASA_Price_Updater: DB insert failed for {$stock->stock_symbol}: " . $wpdb->last_error );
            }
        }

        error_log( 'ASA_Price_Updater: Completed for ' . count( $stocks ) . ' stocks.' );
    }
}
