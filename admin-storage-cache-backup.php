<?php

class Azure_Storage_Cache_Backup_Admin {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'settings_init'));
        add_action('update_option_azure_storage_account_name', array($this, 'trigger_backup'), 10, 2);
        add_action('update_option_azure_storage_account_key', array($this, 'trigger_backup'), 10, 2);
        add_action('update_option_azure_storage_container_name', array($this, 'trigger_backup'), 10, 2);
        add_action('admin_post_clear_log', array($this, 'clear_log'));
    }

    public function add_admin_menu() {
        add_options_page(
            __('Azure Storage Cache Backup', 'azure-storage-cache-backup'), 
            __('Azure Storage Cache Backup', 'azure-storage-cache-backup'), 
            'manage_options', 
            'azure-storage-cache-backup', 
            array($this, 'settings_page')
        );
    }

    public function settings_page() {
        $azureStorageCacheBackup = new Azure_Storage_Cache_Backup();
        $error = $azureStorageCacheBackup->get_last_error();
        $logContents = $azureStorageCacheBackup->get_log_file_contents();
        ?>
        <div class="wrap">
            <h1><?php _e('Azure Storage Cache Backup', 'azure-storage-cache-backup'); ?></h1>
            <?php if ($error): ?>
                <div class="notice notice-error">
                    <p><?php echo esc_html($error); ?></p>
                </div>
            <?php endif; ?>
            <form method="post" action="options.php">
                <?php
                settings_fields('azure_storage_cache_backup_settings');
                do_settings_sections('azure_storage_cache_backup');
                submit_button();
                ?>
            </form>
            <h2>
                <?php _e('Debug Log', 'azure-storage-cache-backup'); ?>
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display: inline;">
                    <input type="hidden" name="action" value="clear_log">
                    <input type="submit" class="button" value="<?php _e('Clear', 'azure-storage-cache-backup'); ?>">
                </form>
            </h2>
            <pre style="background: #fff; padding: 1em; border: 1px solid #ddd; max-height: 300px; overflow: auto;"><?php echo esc_html($logContents); ?></pre>
        </div>
        <?php
    }

    public function settings_init() {
        register_setting('azure_storage_cache_backup_settings', 'azure_storage_account_name');
        register_setting('azure_storage_cache_backup_settings', 'azure_storage_account_key');
        register_setting('azure_storage_cache_backup_settings', 'azure_storage_container_name');

        add_settings_section(
            'azure_storage_cache_backup_section',
            __('Azure Storage Settings', 'azure-storage-cache-backup'),
            null,
            'azure_storage_cache_backup'
        );

        add_settings_field(
            'azure_storage_account_name',
            __('Storage Account Name', 'azure-storage-cache-backup'),
            array($this, 'account_name_render'),
            'azure_storage_cache_backup',
            'azure_storage_cache_backup_section'
        );

        add_settings_field(
            'azure_storage_account_key',
            __('Storage Account Key', 'azure-storage-cache-backup'),
            array($this, 'account_key_render'),
            'azure_storage_cache_backup',
            'azure_storage_cache_backup_section'
        );

        add_settings_field(
            'azure_storage_container_name',
            __('Container Name', 'azure-storage-cache-backup'),
            array($this, 'container_name_render'),
            'azure_storage_cache_backup',
            'azure_storage_cache_backup_section'
        );
    }

    public function account_name_render() {
        ?>
        <input type='text' name='azure_storage_account_name' id='azure_storage_account_name' value='<?php echo esc_attr(get_option('azure_storage_account_name')); ?>'>
        <?php
    }

    public function account_key_render() {
        ?>
        <input type='text' name='azure_storage_account_key' id='azure_storage_account_key' value='<?php echo esc_attr(get_option('azure_storage_account_key')); ?>'>
        <?php
    }

    public function container_name_render() {
        ?>
        <input type='text' name='azure_storage_container_name' id='azure_storage_container_name' value='<?php echo esc_attr(get_option('azure_storage_container_name')); ?>'>
        <?php
    }

    public function trigger_backup($old_value, $value) {
        $accountName = get_option('azure_storage_account_name');
        $accountKey = get_option('azure_storage_account_key');
        $containerName = get_option('azure_storage_container_name');

        if ($accountName && $accountKey && $containerName) {
            $azureStorageCacheBackup = new Azure_Storage_Cache_Backup();
            $azureStorageCacheBackup->backup_cache_to_azure();
        }
    }

    public function clear_log() {
        $azureStorageCacheBackup = new Azure_Storage_Cache_Backup();
        file_put_contents($azureStorageCacheBackup->logFile, '');
        wp_redirect(admin_url('options-general.php?page=azure-storage-cache-backup'));
        exit;
    }
}