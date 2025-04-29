<?php
/**
 * Handles the Stock Prediction Page: URL lookup, content rendering and form processing.
 */
class ASA_Stocks_Prediction_Page {
    /**
     * Get the URL of the configured Stock Prediction Page.
     *
     * @return string|false URL or false if not set.
     */
    public static function get_url() {
        $page_id = get_option('asa_prediction_page');
        if ( ! $page_id ) {
            return false;
        }
        return get_permalink( absint( $page_id ) );
    }

    /**
     * Render the page content: form or purchase prompt.
     * Should be hooked into 'the_content'.
     *
     * @param string $content Original content.
     * @return string Modified content.
     */
    public static function get_content() {
        if ( ! is_user_logged_in() ) {
            return '<p>Please <a href="' . wp_login_url( self::get_url() ) . '">log in</a> to predict stocks.</p>' . $content;
        }

        $page_id = get_option('asa_prediction_page');

        // Process form submission if any
        if ( ! empty( $_POST['asa_submit_prediction'] ) ) {
            self::assign_stock_to_user();
        }

        $user_id = get_current_user_id();
        $tokens  = ASA_DB::get_user_tokens( $user_id );
        $markup  = '';

        if ( $tokens < 1 ) {
            $shop_url = function_exists('wc_get_page_id')
                ? get_permalink( wc_get_page_id('shop') )
                : site_url('/shop/');
            $markup .= '<p>Please <a href="' . esc_url( $shop_url ) . '">buy tokens</a> to continue.</p>';
        } else {
            $stocks = ASA_DB::get_stocks_list();
            $markup .= '<form method="post">';
            $markup .= wp_nonce_field('asa_stock_prediction','asa_nonce', true, false);
            $markup .= '<label for="stock_symbol">Select Stock:</label> ';
            $markup .= '<select id="stock_symbol" name="stock_symbol" required>';
            foreach ( $stocks as $stock ) {
                $symbol = esc_attr( $stock->symbol );
                $label  = esc_html( "$stock->company_name ({$stock->symbol})" );
                $markup .= "<option value=\"$symbol\">$label</option>";
            }
            $markup .= '</select> ';
            $markup .= '<button type="submit" name="asa_submit_prediction">Predict Stock</button>';
            $markup .= '</form>';
        }

        // Append any message set during assign_stock_to_user()
        if ( isset( $_SESSION['asa_message'] ) ) {
            $markup .= '<div class="asa-message">' . esc_html( $_SESSION['asa_message'] ) . '</div>';
            unset( $_SESSION['asa_message'] );
        }

        return $markup;
    }

    /**
     * Handle the form submission: assign stock and debit a token.
     */
    public static function assign_stock_to_user() {
        if ( ! isset( $_POST['asa_nonce'] ) || ! wp_verify_nonce( $_POST['asa_nonce'], 'asa_stock_prediction' ) ) {
            return;
        }

        if ( ! is_user_logged_in() ) {
            return;
        }

        $user_id = get_current_user_id();
        $symbol  = sanitize_text_field( $_POST['stock_symbol'] ?? '' );

        if ( empty( $symbol ) ) {
            self::set_message('Please select a stock.');
            return;
        }

        $tokens = ASA_DB::get_user_tokens( $user_id );
        if ( $tokens < 1 ) {
            self::set_message('Insufficient tokens.');
            return;
        }

        // Record the chosen stock
        ASA_DB::add_chosen_stock( $user_id, $symbol );

        // Debit one token
        ASA_DB::add_tokens( $user_id, -1 );

        self::set_message("Stock $symbol assigned. 1 token deducted.");
    }

    /**
     * Helper to store a flash message in session.
     *
     * @param string $msg
     */
    protected static function set_message( $msg ) {
        if ( ! session_id() ) {
            session_start();
        }
        $_SESSION['asa_message'] = $msg;
    }
}