<?php
/**
 * GitHub push webhook -> git pull on the live checkout.
 * Secret lives outside the web root at /home/albytfon/.webhook_secret
 */

header('Content-Type: text/plain');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    exit("POST only\n");
}

$secret = trim(@file_get_contents('/home/albytfon/.webhook_secret'));
if ($secret === '') {
    http_response_code(500);
    exit("secret unreadable\n");
}

$body = file_get_contents('php://input');
$sig  = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

if (!hash_equals('sha256=' . hash_hmac('sha256', $body, $secret), $sig)) {
    http_response_code(401);
    exit("bad signature\n");
}

if (($_SERVER['HTTP_X_GITHUB_EVENT'] ?? '') === 'ping') {
    exit("pong\n");
}

$payload = json_decode($body, true);
$ref     = $payload['ref'] ?? '';

if ($ref !== 'refs/heads/master') {
    exit("ignored: {$ref}\n");
}

$cmd = 'flock -n /home/albytfon/.deploy.lock '
     . 'env HOME=/home/albytfon '
     . '/usr/bin/git -C /home/albytfon/public_html pull --ff-only origin master 2>&1';

$out = shell_exec($cmd);

if ($out === null || trim($out) === '') {
    http_response_code(500);
    exit("no output -- lock held or command failed\n");
}

echo $out;
if (stripos($out, 'error') !== false || stripos($out, 'fatal') !== false) {
    http_response_code(500);
}
