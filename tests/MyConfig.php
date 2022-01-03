<?php

declare(strict_types=1);

/*
 * eduVPN - End-user friendly VPN.
 *
 * Copyright: 2016-2022, The Commons Conservancy eduVPN Programme
 * SPDX-License-Identifier: AGPL-3.0+
 */

namespace Vpn\Portal\Tests;

use Vpn\Portal\Config;

class MyConfig extends Config
{
    public static function defaultConfig(): array
    {
        return [
            'foo' => [
                'bar' => ['baz'],
            ],
        ];
    }
}
