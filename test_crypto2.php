<?php
$keyHex = '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef';
$keyBin = hex2bin($keyHex);
echo "Key hex length: " . strlen($keyHex) . PHP_EOL;
echo "Key bin length: " . strlen($keyBin) . PHP_EOL;

$iv = random_bytes(12);
$tag = '';
$ct = openssl_encrypt('test payload data', 'aes-256-gcm', $keyHex, OPENSSL_RAW_DATA, $iv, $tag, '', 16);
echo "Ciphertext hex: " . bin2hex($ct) . PHP_EOL;

// Now decrypt with bin key (simulating the bug)
$decrypted = openssl_decrypt($ct, 'aes-256-gcm', $keyBin, OPENSSL_RAW_DATA, $iv, $tag);
var_dump($decrypted);

// Now decrypt with hex key (correct way if both used same)
$decrypted2 = openssl_decrypt($ct, 'aes-256-gcm', $keyHex, OPENSSL_RAW_DATA, $iv, $tag);
var_dump($decrypted2);
