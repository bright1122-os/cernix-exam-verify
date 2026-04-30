<?php
$key = '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef';
echo "Key length: " . strlen($key) . PHP_EOL;
$iv = random_bytes(12);
$tag = '';
$ct = openssl_encrypt('test payload data', 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, '', 16);
var_dump($ct);
echo "Tag length: " . strlen($tag) . PHP_EOL;
echo "Tag hex: " . bin2hex($tag) . PHP_EOL;
