<?php
class ASA_Shortcodes {
    public function __construct() {
        add_shortcode('prediction_page_link_with_token_amount', [$this, 'prediction_page_link_with_show_tokens']);
        add_shortcode('relist_stocks', [$this, 'show_stocks']);
        add_shortcode('stocks_prediction_page', [$this, 'stocks_prediction_page']);
    }

    public function prediction_page_link_with_show_tokens() {
        if (!is_user_logged_in()) return 'Please login to view tokens';
        
        $user_id = get_current_user_id();
        $tokens = ASA_DB::get_user_tokens($user_id);

        if($tokens){
            return "<a class=\"prediction-page-link\" href=\"".ASA_Stocks_Prediction_Page::get_url()."\">See Predictions<span>".$tokens." Tokens</span></a>";
        } else{
            return 'Buy tokens to see predictions' ;
        }
        
    }

    public function show_stocks() {
        $stocks = ASA_DB::get_stocks_list();

        ob_start(); ?>
        <div class="nse-stock-list">
            <h3>NSE Listed Companies (Equity)</h3>
            <table>
                <thead>
                    <tr>
                        <th>Symbol</th>
                        <th>Company Name</th>
                        <th>Listing Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stocks as $stock): ?>
                    <tr>
                        <td><?php echo esc_html($stock->symbol); ?></td>
                        <td><?php echo esc_html($stock->company_name); ?></td>
                        <td><?php echo date('d M Y', strtotime($stock->listing_date)); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
        return ob_get_clean();
    }

    public function stocks_prediction_page(){
        return ASA_Stocks_Prediction_Page::get_content();
    }
}