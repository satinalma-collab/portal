<?php

// Define constants
define('DATA_FILE', __DIR__ . '/../data/data.json.enc');
define('ENCRYPTION_METHOD', 'aes-256-cbc');

/**
 * Get the secret key from an environment variable.
 * WARNING: For production, you MUST set this environment variable to a strong, unique key.
 *
 * @return string The secret key.
 */
function get_secret_key() {
    $key = getenv('PORTAL_CORE_SECRET_KEY');
    if (empty($key)) {
        // Fallback for development environments. NOT for production.
        $key = 'default-dev-secret-key-please-change';
    }
    // The key must be 32 bytes (256 bits) for AES-256.
    // We hash it to ensure it's the correct length.
    return hash('sha256', $key, true);
}

/**
 * Reads and decrypts the data file.
 *
 * @return array The decoded data as an associative array. Returns a default structure if file doesn't exist or is invalid.
 */
function get_data() {
    if (!file_exists(DATA_FILE)) {
        return ['users' => [], 'applications' => [], 'menus' => []];
    }

    $encrypted_content = file_get_contents(DATA_FILE);
    if (empty($encrypted_content)) {
        return ['users' => [], 'applications' => [], 'menus' => []];
    }

    // The IV is prepended to the encrypted data
    $iv_length = openssl_cipher_iv_length(ENCRYPTION_METHOD);
    $iv = substr($encrypted_content, 0, $iv_length);
    $encrypted_data = substr($encrypted_content, $iv_length);

    $decrypted_json = openssl_decrypt($encrypted_data, ENCRYPTION_METHOD, get_secret_key(), 0, $iv);

    if ($decrypted_json === false) {
        // Handle decryption failure (e.g., wrong key, corrupted data)
        error_log("PortalCore: Failed to decrypt data file. Check secret key and file integrity.");
        return ['users' => [], 'applications' => [], 'menus' => []];
    }

    return json_decode($decrypted_json, true);
}

/**
 * Encrypts and saves data to the file.
 *
 * @param array $data The data to save.
 * @return bool True on success, false on failure.
 */
function save_data($data) {
    $json_data = json_encode($data, JSON_PRETTY_PRINT);

    // Create a new initialization vector (IV) for each encryption
    $iv_length = openssl_cipher_iv_length(ENCRYPTION_METHOD);
    $iv = openssl_random_pseudo_bytes($iv_length);

    $encrypted_data = openssl_encrypt($json_data, ENCRYPTION_METHOD, get_secret_key(), 0, $iv);

    if ($encrypted_data === false) {
        error_log("PortalCore: Failed to encrypt data.");
        return false;
    }

    // Prepend the IV to the encrypted data for use during decryption
    $result = file_put_contents(DATA_FILE, $iv . $encrypted_data);

    return $result !== false;
}

?>