<?php
/*
Plugin Name: Azure Storage Cache Backup
Description: A simple plugin to backup /wp-content/cache/ folder and subfolders in Azure Storage service and serve files from there.
Version: 1.0
Author: Luiz Reimann
Author URI: https://luizreimann.dev
Text Domain: azure-storage-cache-backup
Domain Path: /languages
*/

if (!defined('ABSPATH')) {
    exit;
}

// Include the main plugin classes
require_once plugin_dir_path(__FILE__) . 'class-azure-storage-cache-backup.php';
require_once plugin_dir_path(__FILE__) . 'admin-storage-cache-backup.php';

// Initialize the plugin
function azure_storage_cache_backup_init() {
    new Azure_Storage_Cache_Backup_Admin();
}

// Adding support for multilingual
add_action('plugins_loaded', 'azure_storage_cache_backup_init');

function azure_storage_cache_backup_load_textdomain() {
    load_plugin_textdomain('azure-storage-cache-backup', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'azure_storage_cache_backup_load_textdomain');