<?php
class ASA_Shortcodes {
    public function __construct() {
        add_shortcode('show_asa_user_tokens', [$this, 'show_tokens']);
        add_shortcode('relist_stocks', [$this, 'show_stocks']);
    }

    public function show_tokens() {
        if (!is_user_logged_in()) return 'Please login to view tokens';
        
        $user_id = get_current_user_id();
        $tokens = ASA_DB::get_user_tokens($user_id);
        
        return 'Your Tokens: ' . ($tokens ?: '0');
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
}