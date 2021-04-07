<?php

declare(strict_types=1);

/*
 * eduVPN - End-user friendly VPN.
 *
 * Copyright: 2016-2021, The Commons Conservancy eduVPN Programme
 * SPDX-License-Identifier: AGPL-3.0+
 */

require_once dirname(__DIR__).'/vendor/autoload.php';

use fkooman\Jwt\Keys\EdDSA\SecretKey;
use LC\Portal\FileIO;

$baseDir = dirname(__DIR__);
$keyFile = $baseDir.'/config/oauth.key';

try {
    // generate OAuth key
    if (!FileIO::exists($keyFile)) {
        throw new Exception('unable to find "'.$keyFile.'"');
    }
    $secretKey = SecretKey::fromEncodedString(FileIO::readFile($keyFile));
    echo 'Public Key: '.$secretKey->getPublicKey()->encode().\PHP_EOL;
} catch (Exception $e) {
    echo 'ERROR: '.$e->getMessage().\PHP_EOL;
    exit(1);
}
