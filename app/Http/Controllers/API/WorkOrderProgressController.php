<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\WorkOrderProgress;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class WorkOrderProgressController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validate the request data
            $validated = Validator::make($request->all(), [
                'work_order_id' => 'required|exists:work_orders,id',
                'status' => 'required|in:In Progress,Completed',
                'quantity' => 'required|integer',
                'notes' => 'nullable|string',
            ]);

            if ($validated->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Validation failed',
                    'errors' => $validated->errors(),
                ], ResponseAlias::HTTP_BAD_REQUEST);
            }

            $workOrderId = $request->work_order_id;
            $newStatus = $request->status;
            $notes = $request->notes;

            if ($newStatus !== 'In Progress' && !empty($notes)) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Notes can only be created during the in progress stage.',
                    'errors' => [
                        'notes' => 'Notes are not allowed unless the status is "In Progress".'
                    ]
                ], ResponseAlias::HTTP_BAD_REQUEST);
            }

            // Check for existing progress records
            $oldProgress = WorkOrderProgress::where('work_order_id', $workOrderId)
                ->orderBy('created_at', 'desc')
                ->first();

            // Validation logic based on existing progress
            if ($oldProgress) {
                if ($oldProgress->status === 'In Progress' && $newStatus !== 'Completed') {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Cannot change status from "In Progress" to anything other than "Completed".',
                        'errors' => ['status' => 'Invalid status transition.'],
                    ], ResponseAlias::HTTP_BAD_REQUEST);
                }

                if ($oldProgress->status === 'Completed' && $newStatus === 'In Progress') {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Cannot change status from "Completed" to "In Progress".',
                        'errors' => ['status' => 'Invalid status transition.'],
                    ], ResponseAlias::HTTP_BAD_REQUEST);
                }
            } else {
                // If no previous record, only allow 'In Progress'
                if ($newStatus !== 'In Progress') {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Initial status must be "In Progress".',
                        'errors' => ['status' => 'Invalid initial status.'],
                    ], ResponseAlias::HTTP_BAD_REQUEST);
                }
            }

            // Create the new progress record
            WorkOrderProgress::create($validated->validated());

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Work Order Progress created.',
            ], ResponseAlias::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while creating Work Order Progress.',
                'errors' => $e->getMessage(),
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
