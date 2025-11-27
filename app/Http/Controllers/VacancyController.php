<?php

namespace App\Http\Controllers;

use App\Models\Vacancy;
use App\Models\ResponsibilityItem;
use App\Models\RequirementItem;
use App\Models\ConditionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VacancyController extends Controller
{
    public function index()
    {
        try {
            $vacancies = Vacancy::with([
                'department',
                'responsibilities',
                'requirements',
                'conditions'
            ])->where('status', 'active')->get();
            return response()->json($vacancies);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch vacancies'], 500);
        }
    }

    public function count()
    {
        try {
            $count = Vacancy::where('status', 'active')->count();
            return response()->json(['count' => $count]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to count vacancies'], 500);
        }
    }

    public function show($id)
    {
        try {
            $vacancy = Vacancy::with([
                'department',
                'responsibilities',
                'requirements',
                'conditions'
            ])->findOrFail($id);
            return response()->json($vacancy);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Vacancy not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch vacancy details'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'department_id' => 'required|exists:departments,id',
                'status' => 'in:active,inactive,draft',
                'responsibilities' => 'nullable|array',
                'responsibilities.*.text' => 'required|string',
                'responsibilities.*.sort_order' => 'integer|min:0',
                'requirements' => 'nullable|array',
                'requirements.*.text' => 'required|string',
                'requirements.*.sort_order' => 'integer|min:0',
                'conditions' => 'nullable|array',
                'conditions.*.text' => 'required|string',
                'conditions.*.sort_order' => 'integer|min:0',
            ]);
            $vacancy = Vacancy::create($request->only(['name', 'department_id', 'status']));

            $this->syncPivotItems($vacancy, $request->responsibilities, ResponsibilityItem::class, 'responsibilities');
            $this->syncPivotItems($vacancy, $request->requirements, RequirementItem::class, 'requirements');
            $this->syncPivotItems($vacancy, $request->conditions, ConditionItem::class, 'conditions');

            $vacancy->load(['department', 'responsibilities', 'requirements', 'conditions']);
            
            return response()->json($vacancy, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create vacancy'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $vacancy = Vacancy::findOrFail($id);

            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'department_id' => 'sometimes|required|exists:departments,id',
                'status' => 'in:active,inactive,draft',
                'responsibilities' => 'nullable|array',
                'responsibilities.*.text' => 'required|string',
                'responsibilities.*.sort_order' => 'integer|min:0',
                'requirements' => 'nullable|array',
                'requirements.*.text' => 'required|string',
                'requirements.*.sort_order' => 'integer|min:0',
                'conditions' => 'nullable|array',
                'conditions.*.text' => 'required|string',
                'conditions.*.sort_order' => 'integer|min:0',
            ]);
            $vacancy->update($request->only(['name', 'department_id', 'status']));

            $this->syncPivotItems($vacancy, $request->responsibilities, ResponsibilityItem::class, 'responsibilities');
            $this->syncPivotItems($vacancy, $request->requirements, RequirementItem::class, 'requirements');
            $this->syncPivotItems($vacancy, $request->conditions, ConditionItem::class, 'conditions');
            

            $vacancy->load(['department', 'responsibilities', 'requirements', 'conditions']);
            
            return response()->json($vacancy);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Vacancy not found'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update vacancy'], 500);
        }
    }

    public function destroy($id)
    {
        
        try {
            $vacancy = Vacancy::findOrFail($id);

            $vacancy->delete();

            return response()->json(null, 204);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Vacancy not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete vacancy'], 500);
        }
    }

    protected function syncPivotItems($vacancy, $items, $modelClass, $relationName)
    {
        if (is_null($items)) {
            return;
        }
        $syncData = [];
        foreach ($items as $item) {
            $model = $modelClass::firstOrCreate(['text' => $item['text']]);
            $syncData[$model->id] = ['sort_order' => $item['sort_order'] ?? 0];
        }
        $vacancy->{$relationName}()->sync($syncData);
    }
}