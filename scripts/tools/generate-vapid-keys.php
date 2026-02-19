<?php
/**
 * UEDF SENTINEL - Simple VAPID Key Generator
 */

echo "=============================================\n";
echo "UEDF SENTINEL VAPID Key Generator\n";
echo "=============================================\n\n";

// Check if OpenSSL is loaded
if (!extension_loaded('openssl')) {
    die("❌ OpenSSL extension is not loaded!\n");
}

echo "✅ OpenSSL is loaded\n";

// Try to generate keys using OpenSSL
try {
    // Generate a new EC key pair for prime256v1
    $config = [
        'digest_alg' => 'sha256',
        'private_key_type' => OPENSSL_KEYTYPE_EC,
        'curve_name' => 'prime256v1'
    ];
    
    $res = openssl_pkey_new($config);
    
    if (!$res) {
        throw new Exception("Failed to create key resource");
    }
    
    // Get private key
    openssl_pkey_export($res, $privateKey);
    
    // Get public key details
    $details = openssl_pkey_get_details($res);
    $publicKey = $details['key'];
    
    // Extract the raw EC public key (remove PEM headers)
    $publicKeyRaw = extractRawKey($publicKey);
    $privateKeyRaw = extractRawKey($privateKey);
    
    echo "\n✅ Keys generated successfully!\n\n";
    echo "=============================================\n";
    echo "Copy these to your .env file:\n";
    echo "=============================================\n\n";
    echo "VAPID_PUBLIC_KEY=" . $publicKeyRaw . "\n";
    echo "VAPID_PRIVATE_KEY=" . $privateKeyRaw . "\n";
    echo "VAPID_SUBJECT=mailto:commander@uedf.gov.sz\n\n";
    
    // Save to .env file
    file_put_contents('.env', 
        "VAPID_PUBLIC_KEY=" . $publicKeyRaw . "\n" .
        "VAPID_PRIVATE_KEY=" . $privateKeyRaw . "\n" .
        "VAPID_SUBJECT=mailto:commander@uedf.gov.sz\n"
    );
    
    echo "✅ Keys also saved to .env file\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
    echo "Using fallback keys for testing:\n";
    echo "=============================================\n\n";
    echo "VAPID_PUBLIC_KEY=BLC8GOevpcpjQiLkO7JmVxBMo7IwRfuJRq8x6C7otldjG7G3zzJsHfKYP5DgYb7WQ4yT2sJGQe3b_GpF3Y7Yw6A\n";
    echo "VAPID_PRIVATE_KEY=3U5g3u8q9kQ2kR7vX2yW4tZ8aN1cM6bV9xZ3fH7jK2\n";
    echo "VAPID_SUBJECT=mailto:commander@uedf.gov.sz\n\n";
    
    file_put_contents('.env', 
        "VAPID_PUBLIC_KEY=BLC8GOevpcpjQiLkO7JmVxBMo7IwRfuJRq8x6C7otldjG7G3zzJsHfKYP5DgYb7WQ4yT2sJGQe3b_GpF3Y7Yw6A\n" .
        "VAPID_PRIVATE_KEY=3U5g3u8q9kQ2kR7vX2yW4tZ8aN1cM6bV9xZ3fH7jK2\n" .
        "VAPID_SUBJECT=mailto:commander@uedf.gov.sz\n"
    );
}

function extractRawKey($pemKey) {
    // Remove PEM headers and footers
    $pemKey = preg_replace('/-----.*?-----/', '', $pemKey);
    $pemKey = str_replace(["\r", "\n"], '', $pemKey);
    $pemKey = trim($pemKey);
    
    // Decode from base64
    $binary = base64_decode($pemKey);
    
    // For EC keys, we need to extract just the public/private key
    if (strlen($binary) > 64) {
        // Try to find the raw key (for EC, it's usually the last 64 bytes for public)
        $binary = substr($binary, -64);
    }
    
    // Encode to base64 and format for VAPID
    return rtrim(strtr(base64_encode($binary), '+/', '-_'), '=');
}

echo "\n=============================================\n";
?>
