<?php

namespace Tests\Unit;

use App\Models\LabTest;
use PHPUnit\Framework\TestCase;

class LabTestFlagTest extends TestCase
{
    private function hb(): LabTest
    {
        return new LabTest([
            'ref_low' => 11, 'ref_high' => 16,
            'critical_low' => 7, 'critical_high' => 20,
        ]);
    }

    public function test_flags_normal_within_range(): void
    {
        $this->assertSame('normal', $this->hb()->flagFor('14'));
    }

    public function test_flags_low_and_high(): void
    {
        $this->assertSame('low', $this->hb()->flagFor('8'));
        $this->assertSame('high', $this->hb()->flagFor('17'));
    }

    public function test_flags_critical_thresholds(): void
    {
        $this->assertSame('critical_low', $this->hb()->flagFor('6.5'));
        $this->assertSame('critical_high', $this->hb()->flagFor('21'));
    }

    public function test_non_numeric_and_missing_range_return_null(): void
    {
        $this->assertNull($this->hb()->flagFor('Positive'));

        $noRange = new LabTest(['ref_low' => null, 'ref_high' => null]);
        $this->assertNull($noRange->flagFor('5'));
    }
}
