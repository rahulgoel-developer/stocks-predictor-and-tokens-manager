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
    }

    public function product_tokens_callback() {
        $mapping = get_option('asa_product_tokens');
        echo '<textarea name="asa_product_tokens" rows="5" cols="50">' 
             . esc_textarea($mapping) 
             . '</textarea>';
        echo '<p class="description">Enter product ID:token pairs (one per line)<br>Example: 123:5</p>';
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