<?php

namespace App\Http\Controllers\API;

use AllowDynamicProperties;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

#[AllowDynamicProperties] class WorkOrderController extends Controller
{
    public function __construct()
    {
        $userData = User::with('role')->find(Auth::id());
        $this->userId = Auth::id();
        $this->userRole = $userData['role']['name'];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Apply 'status' and 'deadline' filters to the WorkOrder query
        $filters = $request->only(['status', 'deadline']);
        $workOrders = WorkOrder::with(['creator', 'assignedOperator', 'progress'])->where($filters);

        // If the user is an Operator, show only their assigned work orders
        // Otherwise, show all filtered work orders
        $workOrders = $this->userRole === 'Operator'
            ? $workOrders->where('assigned_operator_id', $this->userId)->get()->makeHidden(['assigned_operator_id'])
            : $workOrders->get()->makeHidden(['assigned_operator_id']);

        // Return the filtered work orders as a JSON response
        return response()->json([
            'status' => 'success',
            'work_orders' => $workOrders,
        ], ResponseAlias::HTTP_OK);

    }

    /**
     * Store a newly created resource in storage.
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = Validator::make($request->all(), [
                'product_name' => 'required|string|max:100',
                'quantity' => 'required|integer',
                'deadline' => 'required|date',
                'assigned_operator_id' => 'required|exists:users,id',
            ]);

//          Check validation
            if ($validated->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Validation failed',
                    'errors' => $validated->errors()
                ], ResponseAlias::HTTP_BAD_REQUEST);
            }

//          Get validated data
            $data = $validated->validated();
            $data['created_by'] = $this->userId;
            $data['work_order_number'] = WorkOrder::generateWorkOrderNumber();

//          Create the work order
            $workOrder = WorkOrder::create($data);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Work Order created',
            ], ResponseAlias::HTTP_CREATED);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => $exception->getMessage(),
                'errors' => $exception->getTrace()
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     * @throws ValidationException
     */
    public function update(Request $request, $id)
    {
    //    Start the transaction
        DB::beginTransaction();
        try {

            // Search WorkOrder by ID
            $workOrder = WorkOrder::findOrFail($id);

            if (!$workOrder) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Work Order not found',
                    'errors' => []
                ], ResponseAlias::HTTP_BAD_REQUEST);
            }

            // Initial validation
            $validated = Validator::make($request->all(), [
                'status' => 'sometimes|in:In Progress,Completed,Canceled',
                'assigned_operator_id' => 'sometimes|exists:users,id',
            ]);

            if ($validated->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Validation failed',
                    'errors' => $validated->errors()
                ], ResponseAlias::HTTP_BAD_REQUEST);
            }

            // Get validated data
            $data = $validated->validated();

            $currentStatus = $workOrder->status;

            // Additional validation logic for Operators
            if ($this->userRole === 'Operator') {
                // Operator cannot update 'assigned_operator_id'
                if (array_key_exists('assigned_operator_id', $data)) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Operators are not allowed to modify the assigned operator.',
                        'errors' => ['assigned_operator_id' => 'Modification not allowed for Operators.']
                    ], ResponseAlias::HTTP_FORBIDDEN);
                }

                // Operators can only change status
                if (!isset($data['status'])) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Status is required for Operators.',
                        'errors' => ['status' => 'Operators can only modify status.']
                    ], ResponseAlias::HTTP_BAD_REQUEST);
                }

                // Operator can only change to 'In Progress' or 'Completed'
                if (!in_array($data['status'], ['In Progress', 'Completed'])) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Operators can only change status to "In Progress" or "Completed".',
                        'errors' => ['status' => 'Invalid status change.']
                    ], ResponseAlias::HTTP_BAD_REQUEST);
                }
            }

            // cannot change from 'Completed' to 'In Progress'
            if ($currentStatus === 'Completed' && $data['status'] === 'In Progress') {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Cannot change status from "Completed" to "In Progress".',
                    'errors' => ['status' => 'Invalid status change.']
                ], ResponseAlias::HTTP_BAD_REQUEST);
            }

            // cannot change from 'Canceled' to 'In Progress'
            if ($currentStatus === 'Canceled' && $data['status'] === 'In Progress') {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Cannot change status from "Canceled" to "In Progress".',
                    'errors' => ['status' => 'Invalid status change.']
                ], ResponseAlias::HTTP_BAD_REQUEST);
            }

            // cannot change from 'Canceled' to 'Completed'
            if ($currentStatus === 'Canceled' && $data['status'] === 'In Progress') {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Cannot change status from "Canceled" to "Completed".',
                    'errors' => ['status' => 'Invalid status change.']
                ], ResponseAlias::HTTP_BAD_REQUEST);
            }

            // Update data WorkOrder
            $workOrder->update($data);

//            commiting the changes
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Work Order updated successfully.',
            ], 200);
        } catch (\Exception $exception) {
//            rollback the changes
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => $exception->getMessage(),
                'errors' => $exception->getTrace()
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WorkOrder $workOrder)
    {
        //
    }
}
