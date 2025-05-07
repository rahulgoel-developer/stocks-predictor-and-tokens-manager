<?php
/*
Plugin Name: Stocks Predictor and Tokens Manager
Description: Stock token management system for predictions with Indian market data. A.k.a AI Stocks Analyser.
Version: 1.0
Author: Rahul Goel
*/

if (!defined('ABSPATH')) exit;

$dotenv = Dotenv\Dotenv::createImmutable( __DIR__ );
$dotenv->load();

// Load required files
require_once plugin_dir_path(__FILE__) . 'includes/class-db.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-cron.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-stock-updater.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-price-updater.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-stocks-prediction-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-woocommerce.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-shortcodes.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-post-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-stock-search-js.php';

// Activation/Deactivation hooks
register_activation_hook(__FILE__, ['ASA_DB', 'activate']);
register_deactivation_hook(__FILE__, ['ASA_Cron', 'deactivate']);

// Initialize components
new ASA_Admin();
new ASA_WooCommerce();
new ASA_Shortcodes();
ASA_Cron::init();
ASA_Post_Handler::init();
ASA_Stock_Search_JS::init();
ASA_Price_Updater::record_all_selected_stocks_price(); // For testing