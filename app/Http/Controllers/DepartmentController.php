<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DepartmentController extends Controller
{
    public function index()
    {
        try {
            $departments = Department::all();
            return response()->json($departments);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch departments'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:20|unique:departments,name'
            ]);
            
            $department = Department::create($request->only(['name']));
            
            return response()->json($department, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create department'], 500);
        }
    }

    public function update(Request $request, Department $department)
    {

        try {
            $request->validate([
                'name' => 'required|string|max:20|unique:departments,name,' . $department->id
            ]);

            $oldName = $department->name;
            $department->update($request->only(['name']));

            return response()->json($department);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update department'], 500);
        }
    }

    public function destroy(Department $department)
    {

        try {
            $usedCount = \App\Models\Vacancy::where('department_id', $department->id)->count();
            if ($usedCount > 0) {
                $message = "Нельзя удалить отдел «{$department->name}» — он используется в {$usedCount} вакансиях.";
                return response()->json([
                    'error' => $message
                ], 409); 
            }

            $departmentName = $department->name;
            $department->delete();

            return response()->json(null, 204); 

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete department'], 500);
        }
    }
}