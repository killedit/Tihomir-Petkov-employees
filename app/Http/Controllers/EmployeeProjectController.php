<?php

namespace App\Http\Controllers;

use App\Http\Requests\CSVRequest;
use DateTime;

class EmployeeProjectController extends Controller
{
    public function show()
    {
        return view('employees.terminal', [
            'results'     => session('results'),
            'parseErrors' => session('parseErrors'),
        ]);
    }

    public function process(CSVRequest $request)
    {
        $projects = [];
        $errors   = [];

        $handle = fopen($request->file('file')->getRealPath(), 'r');

        while (($row = fgetcsv($handle)) !== false) {

            $parsed = $this->parseRow($row);

            if(implode(',', $row) == "EmpID,ProjectID,DateFrom,DateTo") {
                continue;
            }

            if ($parsed === null) {
                $errors[] = "Skipped invalid row: " . implode(',', $row);
                continue;
            }

            $projects[] = $parsed;
        }

        fclose($handle);

// dd(
//     $projects
// );

        return redirect()->route('employees.show')
        ->with('results',     $this->findCommonProjectPairs($projects))
        ->with('parseErrors', $errors);
    }

    private function parseRow(array $row): ?array
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

    private function parseDate(string $date): ?DateTime
    {
        try {
            return new DateTime($date);
        } catch (\Exception) {
            return null;
        }
    }

    private function findCommonProjectPairs(array $projects): array
    {
        $overlaps = [];

        foreach ($projects as $p1) {
            foreach ($projects as $p2) {
                if ($p1 === $p2
                    || $p1['employeeId'] === $p2['employeeId']
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
                $days = $overlapStart->diff($overlapEnd)->days;

                $overlaps[$key] ??= ['emp1' => $emp1, 'emp2' => $emp2, 'projects' => [], 'total_days' => 0];
                $overlaps[$key]['projects'][]  = ['project_id' => $p1['projectId'], 'days' => $days];
                $overlaps[$key]['total_days'] += $days;
            }
        }

        usort($overlaps, function ($a, $b) {
            return $b['total_days'] <=> $a['total_days'];
        });

        return $overlaps;
    }
}
