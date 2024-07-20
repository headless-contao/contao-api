<?php

declare(strict_types=1);

/*
 * This file is part of janmarkuslanger/ApiBundle.
 *
 * (c) Jan-Markus Langer
 *
 * @license LGPL-3.0-or-later
 */

namespace Janmarkuslanger\ApiBundle\Tests;

use Janmarkuslanger\ApiBundle\JanmarkuslangerApiBundle;
use PHPUnit\Framework\TestCase;

class JanmarkuslangerApiBundleTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $bundle = new JanmarkuslangerApiBundle();

        $this->assertInstanceOf('Janmarkuslanger\ApiBundle\JanmarkuslangerApiBundle', $bundle);
    }
}
