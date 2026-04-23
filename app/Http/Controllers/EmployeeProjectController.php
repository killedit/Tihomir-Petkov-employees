<?php

namespace App\Http\Controllers;

use App\Http\Requests\CSVResource;
use Illuminate\Http\Request;
use DateTime;

class EmployeeProjectController extends Controller
{
    public function show(Request $request)
    {
        $results = $request->session()->get('employee_results');
        $parseErrors = $request->session()->get('employee_errors');

        return view('employees.terminal', ['results' => $results, 'parseErrors' => $parseErrors]);
    }

    public function process(CSVResource $request)
    {
        # https://www.php.net/manual/en/function.fgetcsv.php
        ini_set('auto_detect_line_endings', TRUE);

        $projects = [];
        $errors = [];

        try {
            $request_file = $request->file('file');
            $handle = fopen($request_file->getRealPath(), 'r');

// dd(
//     $request
// );

            while (($row = fgetcsv($handle)) !== FALSE) {

                if(implode(',', $row) == "EmpID,ProjectID,DateFrom,DateTo") {
                    continue;
                }

                [$employeeID, $projectID, $dateFrom, $dateTo] = [(int) trim($row[0]), (int) trim($row[1]), trim($row[2]), trim($row[3] ?: '')];

                if (!is_numeric($employeeID) || !is_numeric($projectID)) {
                    continue;
                }

                $parsedFrom = $this->parseDate($dateFrom, $errors, $employeeID, $projectID, 'DateFrom');
                if (!$parsedFrom) {
                    continue;
                }

                $parsedTo = $this->parseDate($dateTo === '' || strtolower($dateTo) === 'null' ? 'today' : $dateTo, $errors, $employeeID, $projectID, 'DateTo');
                if (!$parsedTo) {
                    continue;
                }

                $projects[] = [
                    'emp_id' => $employeeID,
                    'project_id' => $projectID,
                    'date_from' => $parsedFrom,
                    'date_to' => $parsedTo,
                ];
            }
            fclose($handle);

            $results = $this->findCommonProjectPairs($projects);
        } catch (\Exception $e) {
            // $errors[] = 'Error processing file: ' . $e->getMessage();
            $errors[] = 'Error processing row [' . implode(', ', $row) . ']: ' . $e->getMessage();
            $results = [];
        }

        $request->session()->flash('employee_results', $results);

        if (!empty($errors)) {
            $request->session()->flash('employee_errors', $errors);
        }

        return redirect()->back();
    }

    private function parseDate(string $date, array &$errors, int $employeeID, int $projectID, string $field): ?DateTime
    {
        if ($date === 'today' || strtolower($date) === 'null') {
            return new DateTime();
        }

        try {
            return new DateTime($date);
        } catch (\Exception $e) {
            $errors[] = "employeeID {$employeeID}, projectID {$projectID}, {$field}: Unable to parse '{$date}'";
            return null;
        }
    }

    private function findCommonProjectPairs(array $projects): array
    {
        $overlaps = [];

        foreach ($projects as $i => $p1) {
            foreach ($projects as $j => $p2) {
                if ($i >= $j) {
                    continue;
                }

                if ($p1['emp_id'] === $p2['emp_id']) {
                    continue;
                }

                if ($p1['project_id'] !== $p2['project_id']) {
                    continue;
                }

                $overlapStart = max($p1['date_from']->getTimestamp(), $p2['date_from']->getTimestamp());
                $overlapEnd = min($p1['date_to']->getTimestamp(), $p2['date_to']->getTimestamp());

                if ($overlapStart > $overlapEnd) {
                    continue;
                }

                $key = $this->pairKey($p1['emp_id'], $p2['emp_id']);
                $days = (int) floor(($overlapEnd - $overlapStart) / (60 * 60 * 24));

                if (!isset($overlaps[$key])) {
                    $overlaps[$key] = [
                        'emp1' => $p1['emp_id'],
                        'emp2' => $p2['emp_id'],
                        'projects' => [],
                        'total_days' => 0,
                    ];
                }

                $overlaps[$key]['projects'][] = [
                    'project_id' => $p1['project_id'],
                    'days' => $days,
                ];
                $overlaps[$key]['total_days'] += $days;
            }
        }

        usort($overlaps, fn($a, $b) => $b['total_days'] <=> $a['total_days']);

        return $overlaps;
    }

    private function pairKey(int $emp1, int $emp2): string
    {
        $ids = [$emp1, $emp2];
        sort($ids);
        return implode('-', $ids);
    }
}
