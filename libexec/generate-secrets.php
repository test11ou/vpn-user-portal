<?php

declare(strict_types=1);

/*
 * eduVPN - End-user friendly VPN.
 *
 * Copyright: 2016-2022, The Commons Conservancy eduVPN Programme
 * SPDX-License-Identifier: AGPL-3.0+
 */

require_once dirname(__DIR__).'/vendor/autoload.php';
$baseDir = dirname(__DIR__);

use fkooman\OAuth\Server\Signer;
use Vpn\Portal\Config;
use Vpn\Portal\FileIO;
use Vpn\Portal\OpenVpn\CA\VpnCa;

// allow group to read the created files/folders
umask(0027);

try {
    $nodeNumberStr = null;
    for ($i = 1; $i < $argc; ++$i) {
        if ('--node' === $argv[$i]) {
            if ($i + 1 < $argc) {
                $nodeNumberStr = $argv[$i + 1];
            }

            continue;
        }
        if ('--help' === $argv[$i]) {
            echo 'SYNTAX: '.$argv[0].' [--node NODE_NUMBER]'.\PHP_EOL;

            exit(0);
        }
    }

    if (null !== $nodeNumberStr) {
        // generate a secret for the specified node number
        $nodeNumber = (int) $nodeNumberStr;
        if ($nodeNumber < 0) {
            throw new Exception('--node MUST be followed by a number >= 0');
        }
        // Node Key
        $nodeKeyFile = $baseDir.'/config/node.'.$nodeNumber.'.key';
        if (!FileIO::exists($nodeKeyFile)) {
            $secretKey = random_bytes(32);
            FileIO::write($nodeKeyFile, sodium_bin2hex($secretKey));
        }

        exit(0);
    }

    $config = Config::fromFile($baseDir.'/config/config.php');
    // OAuth key
    $apiKeyFile = $baseDir.'/config/oauth.key';
    if (!FileIO::exists($apiKeyFile)) {
        FileIO::write($apiKeyFile, Signer::generateSecretKey());
    }

    // Node Key
    $nodeKeyFile = $baseDir.'/config/node.0.key';
    if (!FileIO::exists($nodeKeyFile)) {
        $secretKey = random_bytes(32);
        FileIO::write($nodeKeyFile, sodium_bin2hex($secretKey));
    }

    // OpenVPN CA
    // NOTE: only created when the ca.crt and ca.key do not yet exist...
    $vpnCa = new VpnCa($baseDir.'/config/ca', $config->vpnCaPath());
    $vpnCa->initCa($config->caExpiry());
    // the CA files have 0600 permissions, but should be 0640 so PHP can read
    // them, requires update to vpn-ca to use mode 0660 or similar so our
    // umask takes care of it
    chmod($baseDir.'/config/ca/ca.crt', 0640);
    chmod($baseDir.'/config/ca/ca.key', 0640);
} catch (Exception $e) {
    echo 'ERROR: '.$e->getMessage().\PHP_EOL;

    exit(1);
}
