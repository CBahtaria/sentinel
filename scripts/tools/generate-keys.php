<?php
require_once 'vendor/autoload.php';

use Minishlink\WebPush\VAPID;

$vapid = VAPID::createVapidKeys();

echo "=============================================\n";
echo "UEDF SENTINEL - VAPID Keys Generator\n";
echo "=============================================\n\n";
echo "Copy these to your .env file:\n\n";
echo "VAPID_PUBLIC_KEY=" . $vapid['publicKey'] . "\n";
echo "VAPID_PRIVATE_KEY=" . $vapid['privateKey'] . "\n\n";
echo "=============================================\n";
?>
