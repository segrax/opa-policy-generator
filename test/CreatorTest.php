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

namespace Segrax\OpaPolicyGenerator\Tests;

use PHPUnit\Framework\TestCase;
use Segrax\OpaPolicyGenerator\Policy\Creator;

class CreatorTest extends TestCase
{
    public function testFromFile(): void
    {
        $creator = new Creator();
        $set = $creator->fromFile(__DIR__ . '/testapi.yaml');
        $policies = $set->policiesGet();
        $this->assertArrayHasKey('policy', $policies);
        $this->assertArrayHasKey('test', $policies);
    }

    public function testToFile(): void
    {
        $creator = new Creator();
        $set1 = $creator->fromFile(__DIR__ . '/testapi.yaml');
        $set2 = $creator->fromFile(__DIR__ . '/testapi.json');
        $this->assertEquals($set1, $set2);

        $creator->toFile($set1, sys_get_temp_dir() . '/test');
    }

}