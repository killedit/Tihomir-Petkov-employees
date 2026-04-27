<?php

namespace App\Services;

use DateTime;

class EmployeeProjectService
{
    public function parseRow(array $row): ?array
    {
        if (count($row) < 4) {
            return null;
        }

        [$employeeId, $projectId, $dateFrom, $dateTo] = array_map('trim', $row);

        if (!ctype_digit($employeeId) || !ctype_digit($projectId)) {
            return null;
        }

        $parsedFrom = $this->parseDate($dateFrom);
        $parsedTo   = $this->parseDate(empty($dateTo) || strtolower($dateTo) === 'null' ? 'today' : $dateTo);

        if (!$parsedFrom || !$parsedTo) {
            return null;
        }

        return [
            'employeeId' => (int) $employeeId,
            'projectId'  => (int) $projectId,
            'dateFrom'   => $parsedFrom,
            'dateTo'     => $parsedTo,
        ];
    }

    public function parseDate(string $date): ?DateTime
    {
        try {
            return new DateTime($date);
        } catch (\Exception) {
            return null;
        }
    }

    public function findCommonProjectPairs(array $projects): array
    {
        $overlaps = [];

        $seen = [];

        foreach ($projects as $idx1 => $p1) {
            foreach ($projects as $idx2 => $p2) {
                if ($idx1 >= $idx2) {
                    continue;
                }

                if ($p1['employeeId'] === $p2['employeeId']
                    || $p1['projectId']  !== $p2['projectId']
                ) {
                    continue;
                }

                $overlapStart = max($p1['dateFrom'], $p2['dateFrom']);
                $overlapEnd   = min($p1['dateTo'],   $p2['dateTo']);

                if ($overlapStart > $overlapEnd) {
                    continue;
                }

                $emp1 = min($p1['employeeId'], $p2['employeeId']);
                $emp2 = max($p1['employeeId'], $p2['employeeId']);
                $key  = "{$emp1}-{$emp2}";

                $days = $this->calculateDays($overlapStart, $overlapEnd);

                if (!isset($seen[$key])) {
                    $seen[$key] = true;
                    $overlaps[$key] = ['emp1' => $emp1, 'emp2' => $emp2, 'projects' => [], 'total_days' => 0];
                }

                $overlaps[$key]['projects'][]  = ['project_id' => $p1['projectId'], 'days' => $days];
                $overlaps[$key]['total_days'] += $days;
            }
        }

        usort($overlaps, function ($a, $b) {
            return $b['total_days'] <=> $a['total_days'];
        });

        return array_values($overlaps);
    }

    private function calculateDays(DateTime $start, DateTime $end): int
    {
        $diff = $start->diff($end);
        return $diff->days + 1;
    }
}