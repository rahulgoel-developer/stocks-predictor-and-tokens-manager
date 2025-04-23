<?php
class ASA_Admin {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_menu() {
        add_menu_page(
            'Stock Token Settings',
            'AI Stock Analyzer',
            'manage_options',
            'asa-settings',
            [$this, 'settings_page']
        );
    }

    public function register_settings() {
        // Existing product tokens setting
        register_setting('asa-settings-group', 'asa_product_tokens');
        add_settings_section(
            'asa-main-section',
            'Product Token Mapping',
            null,
            'asa-settings'
        );
        add_settings_field(
            'asa-product-tokens',
            'Product ID to Tokens Mapping',
            [$this, 'product_tokens_callback'],
            'asa-settings',
            'asa-main-section'
        );

        // NEW: Stock Prediction Page setting
        register_setting('asa-settings-group', 'asa_prediction_page', [
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 0,
        ]);
        add_settings_field(
            'asa-prediction-page',
            'Stock Prediction Page',
            [$this, 'prediction_page_callback'],
            'asa-settings',
            'asa-main-section'
        );
    }

    public function product_tokens_callback() {
        $mapping = get_option('asa_product_tokens');
        echo '<textarea name="asa_product_tokens" rows="5" cols="50">'
             . esc_textarea($mapping)
             . '</textarea>';
        echo '<p class="description">Enter product ID:token pairs (one per line)<br>Example: 123:5</p>';
    }

    public function prediction_page_callback() {
        $selected = get_option('asa_prediction_page');
        wp_dropdown_pages([
            'name' => 'asa_prediction_page',
            'selected' => $selected,
            'show_option_none' => '-- Select a Page --',
        ]);
        echo '<p class="description">Choose the page where users will submit stock predictions.</p>';
        echo '<p class="description">Add Shortcode [asa-stocks-prediction-page] to the content of the page.</p>';
    }

    public function settings_page() {
        ?>
        <div class="wrap">
            <h2>AI Stock Analyzer Settings</h2>
            <form method="post" action="options.php">
                <?php
                settings_fields('asa-settings-group');
                do_settings_sections('asa-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}
