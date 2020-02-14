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

namespace Segrax\OpaPolicyGenerator\Convert;

use RuntimeException;
use Segrax\OpaPolicyGenerator\Policy\Path;
use Segrax\OpaPolicyGenerator\Policy\Security\APIKey;
use Segrax\OpaPolicyGenerator\Policy\Security\Base;
use Segrax\OpaPolicyGenerator\Policy\Security\Http;
use Segrax\OpaPolicyGenerator\Policy\Security\OAuth2;
use Segrax\OpaPolicyGenerator\Policy\Security\OpenID;
use Segrax\OpaPolicyGenerator\Policy\Set;

/**
 * Conversions from JSON to a Policy Set
 */
class Json
{
    /**
     * @var array
     */
    protected $parsed = [];

    /**
     * @var Set $policySet
     */
    protected $policySet;

    /**
     * @var string
     */
    protected $name = '';

    /**
     * Setup the policy set
     */
    public function __construct(string $pName = 'name.api')
    {
        $this->name = $pName;
        $this->policySet = new Set($this->name);
    }

    /**
     * @inheritdoc
     */
    public function fromString(string $pContent): ?Set
    {
        $this->policySet = new Set($this->name);

        $this->parsed = json_decode($pContent, true);
        if ($this->parsed === false) {
            return null;
        }

        $this->securitySchemeLoad();
        $this->pathsLoad();

        return $this->policySet;
    }

    /**
     * Load all paths
     */
    protected function pathsLoad(): void
    {
        // Loop every defined path
        foreach ($this->parsed['paths'] as $routePath => $methods) {
            // Loop every defined method
            foreach ($methods as $method => $options) {
                $path = new Path($this->policySet, $routePath, $method);

                if (isset($options['security'])) {
                    $this->pathSecurityParse($path, $options['security']);
                }

                // Check for options
                if (isset($options['parameters'])) {
                    $this->pathParameterParse($path, $options['parameters']);
                }

                $this->policySet->pathAdd($path);
            }
        }
    }

    /**
     * Parse the security of a path
     */
    protected function pathSecurityParse(Path $pPath, array $pSecurityTypes): void
    {
        foreach ($pSecurityTypes as $type) {
            foreach ($type as $name => $data) {
                $pPath->securityAdd($name, $data);
            }
        }
    }

    /**
     * Parse the parameters of a path
     */
    protected function pathParameterParse(Path $pPath, array $pParameters): void
    {
        foreach ($pParameters as $parameter) {
            $pathParameter = $parameter;

            if (isset($pathParameter['$ref'])) {
                $pathParameter = $this->getRef($parameter['$ref']);
            }

            if ($pathParameter['in'] === 'path') {
                $pPath->parameterAdd($pathParameter['name'], $pathParameter['in']);
            }
        }
    }

    /**
     * Load the component security schemes, and the global security rules
     */
    protected function securitySchemeLoad(): void
    {
        if (isset($this->parsed['components']['securitySchemes'])) {
            foreach ($this->parsed['components']['securitySchemes'] as $name => $entry) {
                $security = $this->securitySchemeCreate($name, $entry);
                $this->policySet->securitySchemeAdd($security);
            }
        }

        // Global security rules?
        if (isset($this->parsed['security'])) {
            foreach ($this->parsed['security'] as $security) {
                foreach ($security as $name => $options) {
                    $this->policySet->securityGlobalAdd($name, $options);
                }
            }
        }
    }

    /**
     * Create and prepare a security scheme
     */
    protected function securitySchemeCreate(string $pName, array $pEntry): Base
    {
        switch (strtolower($pEntry['type'])) {
            case 'http':
                $security = new Http($pName);
                $security->set($pEntry['scheme'], $pEntry['bearerFormat'] ?? '');
                break;

            case 'apikey':
                $security = new APIKey($pName);
                $security->set($pEntry['in'], $pEntry['name']);
                break;

            case 'openidconnect':
                $security = new OpenID($pName);
                break;

            case 'oauth2':
                $security = new OAuth2($pName);
                foreach ($pEntry['flows'] as $flowName => $data) {
                    $security->flowAdd($flowName, $data['scopes'] ?? []);
                }
                break;

            default:
                throw new RuntimeException('Unknown security schema type: ' . $pEntry['type']);
        }
        return $security;
    }

    /**
     * Get the contents of a referenced path
     *
     * @return mixed
     */
    protected function getRef(string $pPath)
    {
        $pathPieces = explode('/', trim($pPath, '#'));
        array_shift($pathPieces);
        $current = $this->parsed;
        foreach ($pathPieces as $piece) {
            if (!isset($current[$piece])) {
                throw new RuntimeException("$pPath could not be found");
            }
            $current = $current[$piece];
        }

        return $current;
    }
}
