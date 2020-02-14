<?php

/*
Copyright (c) 2019 Robert Crossfield

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

/**
 * @see       https://github.com/segrax/opa-policy-generator
 * @license   https://www.opensource.org/licenses/mit-license.php
 */

declare(strict_types=1);

namespace Segrax\OpaPolicyGenerator\Policy\Security;

/**
 * HTTP security
 */
class Http extends Base
{
    private const SCHEME_BASIC = 'basic';
    private const SCHEME_BEARER = 'bearer';

    /**
     * @var string
     */
    private $scheme = '';

    /**
     * @var string
     */
    private $bearerFormat = '';

    /**
     * @var array
     */
    private $token = [];

    /**
     * @inheritdoc
     */
    public function set(string $pScheme, string $pBearerFormat = ''): void
    {
        $this->scheme = $pScheme;
        $this->bearerFormat = $pBearerFormat;
    }

    /**
     * @inheritdoc
     */
    public function getRule(array $pScopes): string
    {
        switch ($this->scheme) {
            case self::SCHEME_BASIC:
                return " basic not implemented\n";
            case self::SCHEME_BEARER:
                if(empty($this->token)) 
                    return "    input.token.sub != []\n";
                return "    input.token.sub == \"" . $this->token['sub'] . "\"\n";

            default:
                break;
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    public function getTestAllow(array $pScopes): array
    {
        switch ($this->scheme) {
            case self::SCHEME_BASIC:
                return [];
            case self::SCHEME_BEARER:
                return ['token' => ['sub' => 'test']];

            default:
                break;
        }
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getTestDeny(array $pScopes): array
    {
        switch ($this->scheme) {
            case self::SCHEME_BASIC:
                return [];
            case self::SCHEME_BEARER:
                return ['token' => []];

            default:
                break;
        }
        return [];
    }

    public function setToken(array $pToken): void
    {
        $this->token = $pToken;
    }
}
