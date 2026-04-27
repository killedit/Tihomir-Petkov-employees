<?php

namespace Tests\Unit;

use App\Services\EmployeeProjectService;
use DateTime;
use PHPUnit\Framework\TestCase;

class EmployeeProjectServiceTest extends TestCase
{
    private EmployeeProjectService $service;

    protected function setUp(): void
    {
        $this->service = new EmployeeProjectService();
    }

    public function test_parse_row_returns_null_for_invalid_row(): void
    {
        $result = $this->service->parseRow(['1']);
        $this->assertNull($result);
    }

    public function test_parse_row_returns_null_for_non_numeric_ids(): void
    {
        $result = $this->service->parseRow(['abc', '1', '2024-01-01', '2024-01-05']);
        $this->assertNull($result);
    }

    public function test_parse_row_parses_valid_row(): void
    {
        $result = $this->service->parseRow(['1', '101', '2024-01-01', '2024-01-05']);

        $this->assertNotNull($result);
        $this->assertEquals(1, $result['employeeId']);
        $this->assertEquals(101, $result['projectId']);
        $this->assertInstanceOf(DateTime::class, $result['dateFrom']);
        $this->assertInstanceOf(DateTime::class, $result['dateTo']);
    }

    public function test_parse_row_handles_null_date_to_as_today(): void
    {
        $today = (new DateTime())->format('Y-m-d');
        $result = $this->service->parseRow(['1', '101', '2024-01-01', 'null']);

        $this->assertNotNull($result);
        $this->assertEquals($today, $result['dateTo']->format('Y-m-d'));
    }

    public function test_parse_date_returns_datetime_for_valid_date(): void
    {
        $result = $this->service->parseDate('2024-01-01');
        $this->assertInstanceOf(DateTime::class, $result);
    }

    public function test_parse_date_returns_null_for_invalid_date(): void
    {
        $result = $this->service->parseDate('invalid-date');
        $this->assertNull($result);
    }

    public function test_find_common_project_pairs_returns_empty_for_no_overlaps(): void
    {
        $projects = [
            ['employeeId' => 1, 'projectId' => 101, 'dateFrom' => new DateTime('2024-01-01'), 'dateTo' => new DateTime('2024-01-05')],
            ['employeeId' => 2, 'projectId' => 102, 'dateFrom' => new DateTime('2024-02-01'), 'dateTo' => new DateTime('2024-02-05')],
        ];

        $result = $this->service->findCommonProjectPairs($projects);
        $this->assertEmpty($result);
    }

    public function test_find_common_project_pairs_finds_overlap(): void
    {
        $projects = [
            ['employeeId' => 1, 'projectId' => 101, 'dateFrom' => new DateTime('2024-01-01'), 'dateTo' => new DateTime('2024-01-10')],
            ['employeeId' => 2, 'projectId' => 101, 'dateFrom' => new DateTime('2024-01-05'), 'dateTo' => new DateTime('2024-01-15')],
        ];

        $result = $this->service->findCommonProjectPairs($projects);

        $this->assertNotEmpty($result);
        $this->assertCount(1, $result);
        $this->assertEquals(6, $result[0]['total_days']);
    }

    public function test_find_common_project_pairs_sorts_by_total_days_descending(): void
    {
        $projects = [
            ['employeeId' => 1, 'projectId' => 101, 'dateFrom' => new DateTime('2024-01-01'), 'dateTo' => new DateTime('2024-01-02')],
            ['employeeId' => 2, 'projectId' => 101, 'dateFrom' => new DateTime('2024-01-01'), 'dateTo' => new DateTime('2024-01-02')],
            ['employeeId' => 1, 'projectId' => 102, 'dateFrom' => new DateTime('2024-01-01'), 'dateTo' => new DateTime('2024-01-10')],
            ['employeeId' => 2, 'projectId' => 102, 'dateFrom' => new DateTime('2024-01-01'), 'dateTo' => new DateTime('2024-01-10')],
        ];

        $result = $this->service->findCommonProjectPairs($projects);

        $this->assertGreaterThan($result[1]['total_days'], $result[0]['total_days']);
    }

    public function test_overlap_includes_both_start_and_end_day(): void
    {
        $projects = [
            ['employeeId' => 1, 'projectId' => 101, 'dateFrom' => new DateTime('2024-01-01'), 'dateTo' => new DateTime('2024-01-01')],
            ['employeeId' => 2, 'projectId' => 101, 'dateFrom' => new DateTime('2024-01-01'), 'dateTo' => new DateTime('2024-01-01')],
        ];

        $result = $this->service->findCommonProjectPairs($projects);

        $this->assertEquals(1, $result[0]['total_days']);
    }
}