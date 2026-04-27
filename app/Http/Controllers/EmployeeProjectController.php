<?php

namespace App\Http\Controllers;

use App\Http\Requests\CSVRequest;
use App\Services\EmployeeProjectService;
use Illuminate\Http\Request;

class EmployeeProjectController extends Controller
{
    public function __construct(
        private EmployeeProjectService $service
    ) {}

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

            $parsed = $this->service->parseRow($row);

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

        return redirect()->route('employees.show')
        ->with('results',     $this->service->findCommonProjectPairs($projects))
        ->with('parseErrors', $errors);
    }
}
