<?php

/*
 * eduVPN - End-user friendly VPN.
 *
 * Copyright: 2016-2019, The Commons Conservancy eduVPN Programme
 * SPDX-License-Identifier: AGPL-3.0+
 */

namespace LetsConnect\Portal;

use LetsConnect\Common\Http\BeforeHookInterface;
use LetsConnect\Common\Http\Exception\HttpException;
use LetsConnect\Common\Http\Request;
use LetsConnect\Common\Http\Service;
use LetsConnect\Common\TplInterface;

/**
 * Augments the "template" with information about whether or not the user is
 * an "admin", i.e. should see the admin menu items.
 */
class AdminHook implements BeforeHookInterface
{
    /** @var array<string> */
    private $adminPermissionList;

    /** @var array<string> */
    private $adminUserIdList;

    /** @var \LetsConnect\Common\TplInterface */
    private $tpl;

    /**
     * @param array<string>                    $adminPermissionList
     * @param array<string>                    $adminUserIdList
     * @param \LetsConnect\Common\TplInterface $tpl
     */
    public function __construct(array $adminPermissionList, array $adminUserIdList, TplInterface &$tpl)
    {
        $this->adminPermissionList = $adminPermissionList;
        $this->adminUserIdList = $adminUserIdList;
        $this->tpl = $tpl;
    }

    /**
     * @param Request $request
     * @param array   $hookData
     *
     * @return bool
     */
    public function executeBefore(Request $request, array $hookData)
    {
        $whiteList = [
            'POST' => [
                '/_saml/acs',
                '/_form/auth/verify',
                '/_form/auth/logout',   // DEPRECATED
                '/_logout',
            ],
            'GET' => [
                '/_saml/logout',
                '/_saml/login',
                '/_saml/metadata',
            ],
        ];
        if (Service::isWhitelisted($request, $whiteList)) {
            return false;
        }

        if (!array_key_exists('auth', $hookData)) {
            throw new HttpException('authentication hook did not run before', 500);
        }

        $userInfo = $hookData['auth'];

        // is the userId listed in the adminUserIdList?
        if (\in_array($userInfo->id(), $this->adminUserIdList, true)) {
            $this->tpl->addDefault(['isAdmin' => true]);

            return true;
        }

        // is any of the user's permissions listed in adminPermissionList?
        $userPermissionList = $userInfo->permissionList();
        foreach ($userPermissionList as $userPermission) {
            if (\in_array($userPermission, $this->adminPermissionList, true)) {
                $this->tpl->addDefault(['isAdmin' => true]);

                return true;
            }
        }

        $this->tpl->addDefault(['isAdmin' => false]);

        return false;
    }
}