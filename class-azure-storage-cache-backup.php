<?php

require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\CreateBlockBlobOptions;

class Azure_Storage_Cache_Backup {

    private $blobClient;
    private $storageAccountName;
    private $storageAccountKey;
    private $storageContainerName;
    public $logFile;

    public function __construct() {
        // Set the log file path
        $this->logFile = plugin_dir_path(__FILE__) . 'logs/debug.log';
        // Create the log directory if it doesn't exist
        if (!file_exists(plugin_dir_path(__FILE__) . 'logs')) {
            mkdir(plugin_dir_path(__FILE__) . 'logs', 0755, true);
        }
        $this->initialize_blob_client();
        add_action('shutdown', array($this, 'backup_cache_to_azure'));
    }

    private function initialize_blob_client() {
        $this->storageAccountName = get_option('azure_storage_account_name', '');
        $this->storageAccountKey = get_option('azure_storage_account_key', '');
        $this->storageContainerName = get_option('azure_storage_container_name', '');

        if (empty($this->storageAccountName) || empty($this->storageAccountKey) || empty($this->storageContainerName)) {
            $this->log_message('One or more Azure Storage settings are missing.');
            return false;
        }

        if ($this->is_valid_base64($this->storageAccountKey)) {
            $connectionString = 'DefaultEndpointsProtocol=https;AccountName=' . $this->storageAccountName . ';AccountKey=' . $this->storageAccountKey . ';';
            $this->blobClient = BlobRestProxy::createBlobService($connectionString);
            $this->log_message('Blob client initialized.');
            $this->clear_error();
            return true;
        } else {
            $this->log_error('Azure Storage account key is not valid.');
            return false;
        }
    }

    private function is_valid_base64($string) {
        return base64_decode($string, true) !== false;
    }

    public function backup_cache_to_azure() {
        $this->log_message('Starting backup_cache_to_azure method.');

        if (!$this->initialize_blob_client()) {
            $this->log_message('Blob client not initialized.');
            return;
        }

        if (!$this->blobClient) {
            $this->log_error('Azure Blob client is not initialized. Please check your account name and key.');
            return;
        }

        $containerExists = $this->ensure_container_exists();
        if (!$containerExists) {
            $this->log_message('Container does not exist and could not be created.');
            return;
        }

        $cacheDir = WP_CONTENT_DIR . '/cache/';
        $this->log_message('Cache directory: ' . $cacheDir);

        if (!is_dir($cacheDir)) {
            $this->log_error('Cache directory does not exist.');
            return;
        }

        $this->log_message('Uploading directory: ' . $cacheDir);
        $this->upload_dir_to_azure($cacheDir);
    }

    private function upload_dir_to_azure($dir) {
        if (!$this->blobClient) {
            $this->log_error('Blob client not initialized in upload_dir_to_azure.');
            return;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }

            $filePath = $file->getPathname();
            $blobName = str_replace(WP_CONTENT_DIR . '/', '', $filePath);

            try {
                $content = file_get_contents($filePath);
                $options = new CreateBlockBlobOptions();
                $options->setContentType(mime_content_type($filePath));

                $this->blobClient->createBlockBlob($this->storageContainerName, $blobName, $content, $options);
                $this->log_message('Uploaded ' . $blobName . ' to Azure.');
            } catch (ServiceException $e) {
                $this->log_error('Error uploading to Azure: ' . $e->getMessage());
            }
        }
    }

    private function ensure_container_exists() {
        try {
            $this->blobClient->getContainerProperties($this->storageContainerName);
            $this->log_message('Container ' . $this->storageContainerName . ' exists.');
            $this->clear_error();
            return true;
        } catch (ServiceException $e) {
            if ($e->getCode() == 404) {
                $this->log_message('Container ' . $this->storageContainerName . ' does not exist. Creating container.');
                try {
                    $this->blobClient->createContainer($this->storageContainerName);
                    $this->log_message('Container ' . $this->storageContainerName . ' created successfully.');
                    return true;
                } catch (ServiceException $createException) {
                    $this->log_error('Error creating container: ' . $createException->getMessage());
                    return false;
                }
            } else {
                $this->log_error('Error checking container: ' . $e->getMessage());
                return false;
            }
        }
    }

    private function log_error($message) {
        $this->log_message($message, 'ERROR');
        update_option('azure_storage_cache_backup_error', $message);
    }

    private function log_message($message, $level = 'INFO') {
        $logEntry = date('Y-m-d H:i:s') . " [$level] $message" . PHP_EOL;
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }

    private function clear_error() {
        delete_option('azure_storage_cache_backup_error');
    }

    public function get_last_error() {
        return get_option('azure_storage_cache_backup_error', '');
    }

    public function serve_files_from_azure() {
        if (!$this->initialize_blob_client()) {
            $this->log_message('Blob client not initialized for serve_files_from_azure.');
            return;
        }

        if (!$this->blobClient) {
            $this->log_message('Blob client is null in serve_files_from_azure.');
            return;
        }

        $requestUri = $_SERVER['REQUEST_URI'];

        if (strpos($requestUri, '/wp-content/cache/') !== false) {
            $blobName = str_replace('/wp-content/', '', $requestUri);

            $blobUrl = sprintf('https://%s.blob.core.windows.net/%s/%s', $this->storageAccountName, $this->storageContainerName, $blobName);

            $this->log_message('Redirecting to blob URL: ' . $blobUrl);
            wp_redirect($blobUrl);
            exit;
        }
    }

    public function get_log_file_contents() {
        if (file_exists($this->logFile)) {
            return file_get_contents($this->logFile);
        }
        return '';
    }
}
