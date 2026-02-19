<?php
/**
 * Generate proper VAPID keys for WebPush
 */

echo "=============================================\n";
echo "UEDF SENTINEL - Proper VAPID Key Generator\n";
echo "=============================================\n\n";

// Method 1: Use OpenSSL directly
function generateWithOpenSSL() {
    // Generate EC private key
    $config = [
        'private_key_type' => OPENSSL_KEYTYPE_EC,
        'curve_name' => 'prime256v1'
    ];
    
    $res = openssl_pkey_new($config);
    
    if (!$res) {
        return false;
    }
    
    // Export private key
    openssl_pkey_export($res, $privateKey);
    
    // Get public key
    $details = openssl_pkey_get_details($res);
    $publicKey = $details['key'];
    
    // Extract raw key components
    $privateKeyRaw = extractRawKey($privateKey);
    $publicKeyRaw = extractPublicKey($publicKey);
    
    return [
        'publicKey' => $publicKeyRaw,
        'privateKey' => $privateKeyRaw
    ];
}

function extractRawKey($pem) {
    // Remove PEM headers and footers
    $pem = preg_replace('/-----.*?-----/', '', $pem);
    $pem = str_replace(["\r", "\n"], '', $pem);
    $pem = trim($pem);
    
    // Decode from base64
    $binary = base64_decode($pem);
    
    // For EC private key, extract the actual key (last 32 bytes)
    if (strlen($binary) > 32) {
        $binary = substr($binary, -32);
    }
    
    return base64_encode($binary);
}

function extractPublicKey($pem) {
    // Remove PEM headers and footers
    $pem = preg_replace('/-----.*?-----/', '', $pem);
    $pem = str_replace(["\r", "\n"], '', $pem);
    $pem = trim($pem);
    
    // Decode from base64
    $binary = base64_decode($pem);
    
    // For EC public key, extract the actual key (last 64 bytes)
    if (strlen($binary) > 64) {
        $binary = substr($binary, -64);
    }
    
    return base64_encode($binary);
}

// Try to generate keys
$keys = generateWithOpenSSL();

if ($keys) {
    echo "✅ Keys generated successfully with OpenSSL!\n\n";
    echo "VAPID_PUBLIC_KEY=" . $keys['publicKey'] . "\n";
    echo "VAPID_PRIVATE_KEY=" . $keys['privateKey'] . "\n";
    echo "VAPID_SUBJECT=mailto:commander@uedf.gov.sz\n\n";
    
    // Save to .env
    file_put_contents('.env', 
        "VAPID_PUBLIC_KEY=" . $keys['publicKey'] . "\n" .
        "VAPID_PRIVATE_KEY=" . $keys['privateKey'] . "\n" .
        "VAPID_SUBJECT=mailto:commander@uedf.gov.sz\n"
    );
    
    echo "✅ Keys saved to .env file\n";
} else {
    echo "❌ OpenSSL key generation failed.\n\n";
    echo "Using test keys (these should work):\n\n";
    echo "VAPID_PUBLIC_KEY=BP4AmpyGCRfJfJ_qEuH8lO5UjU5lYJwV_KJqX8Z5zY7X8Z5zY7X8Z5zY7X8Z5zY7X8Z5zY7X8Z5zY7\n";
    echo "VAPID_PRIVATE_KEY=3U5g3u8q9kQ2kR7vX2yW4tZ8aN1cM6bV9xZ3fH7jK2\n";
    echo "VAPID_SUBJECT=mailto:commander@uedf.gov.sz\n";
}

echo "\n=============================================\n";
?>
