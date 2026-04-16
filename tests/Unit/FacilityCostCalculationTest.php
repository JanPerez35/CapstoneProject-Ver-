<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class FacilityCostCalculationTest extends TestCase
{
    public function test_basic_math_works(): void
    {
        $result = 2 * 5;

        $this->assertEquals(10, $result);
    }
}