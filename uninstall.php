<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$options = [
    'azure_storage_account_name',
    'azure_storage_account_key',
    'azure_storage_container_name'
];

foreach ($options as $option) {
    delete_option($option);
}