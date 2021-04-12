<?php

declare(strict_types=1);

/*
 * eduVPN - End-user friendly VPN.
 *
 * Copyright: 2016-2021, The Commons Conservancy eduVPN Programme
 * SPDX-License-Identifier: AGPL-3.0+
 */

namespace LC\Portal\Http\Auth;

use LC\Portal\Http\UserInfo;
use LC\Portal\Storage;

class DbCredentialValidator implements CredentialValidatorInterface
{
    private Storage $storage;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @return false|\LC\Portal\Http\UserInfo
     */
    public function isValid(string $authUser, string $authPass)
    {
        if (null === $passwordHash = $this->storage->getPasswordHash($authUser)) {
            // no such user
            return false;
        }

        if (password_verify($authPass, $passwordHash)) {
            return new UserInfo($authUser, []);
        }

        return false;
    }
}