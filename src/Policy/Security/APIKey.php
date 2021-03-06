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

class APIKey extends Base
{
    private const PLACEHOLDER_APIKEY = 'a-fake-api-key';

    /**
     * @var string
     */
    private $location = '';

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string
     */
    private $testApiKey = self::PLACEHOLDER_APIKEY;

    /**
     * Set the name and location
     */
    public function set(string $pLocation, string $pName): void
    {
        $this->location = $pLocation;
        $this->name = $pName;
    }

    /**
     * @inheritdoc
     */
    public function getRule(array $pScopes): string
    {
        switch ($this->location) {
            case 'header':
                return "    input.header[\"{$this->name}\"] == \"{$this->testApiKey}\"\n";

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
        switch ($this->location) {
            case 'header':
                return ['header' => [$this->name => $this->testApiKey]];

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
        switch ($this->location) {
            case 'header':
                return ['header' => [$this->name => '123']];

            default:
                break;
        }
        return [];
    }
}
