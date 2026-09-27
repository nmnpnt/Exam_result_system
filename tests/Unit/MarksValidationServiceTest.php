<?php

namespace Tests\Unit;

use App\Services\MarksValidationService;
use PHPUnit\Framework\TestCase;

class MarksValidationServiceTest extends TestCase
{
    private MarksValidationService $service;

    protected function setUp(): void
    {
        $this->service = new MarksValidationService;
    }

    public function test_marks_within_range_are_valid(): void
    {
        [$status, $error] = $this->service->validate(55, 70);

        $this->assertSame('valid', $status);
        $this->assertNull($error);
    }

    public function test_marks_exceeding_max_are_rejected(): void
    {
        [$status, $error] = $this->service->validate(85, 70);

        $this->assertSame('rejected', $status);
        $this->assertNotNull($error);
    }

    public function test_negative_marks_are_rejected(): void
    {
        [$status] = $this->service->validate(-5, 70);

        $this->assertSame('rejected', $status);
    }

    public function test_marks_equal_to_max_are_valid(): void
    {
        [$status] = $this->service->validate(70, 70);

        $this->assertSame('valid', $status);
    }
}
