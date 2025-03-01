<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class WorkOrderController extends Controller
{

//    create next work order process
    private function getWorkOrderNumber(): string
    {
//        create next work order number
        return sprintf(
            'WO-%s-%03d',
            Date::now()->format('Ymd'),
            WorkOrder::whereDate('date_work_order', today())->count() + 1
        );
    }

    private function getUserRole(): string
    {
//        get user role
        return Auth::user()->role;
    }

//    create WO process
    public function createWorkOrder(Request $request)
    {
        try {
//            start transaction
            DB::beginTransaction();

//            validation the input
            $validator = Validator::make($request->all(), [
                'operator_id' => 'required|exists:users,id',
                'product_name' => 'required',
                'date_work_order' => 'required', Rule::date(),
                'deadline' => 'required', Rule::date(),
                'qty_order' => 'required|numeric',
                'status' => 'required|in:pending,inProgress,completed,canceled',
            ]);

//            if validation error
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], ResponseAlias::HTTP_BAD_REQUEST);
            }

//            insert work order to table
            WorkOrder::create([
                'operator_id' => $request->operator_id,
                'no_wo' => $this->getWorkOrderNumber(),
                'product_name' => $request->product_name,
                'date_work_order' => $request->date_work_order,
                'deadline' => $request->deadline,
                'qty_order' => $request->qty_order,
                'status' => $request->status
            ]);

//            commit changes to database
            DB::commit();

//            response if process success
            return response()->json([
                'status' => 'success',
                'message' => 'Work Order created successfully',
            ], ResponseAlias::HTTP_CREATED);

        } catch (\Exception $exception) {
//            rollback changes to database
            DB::rollBack();
//            response failure process
            return response()->json([
                'status' => 'failed',
                'message' => 'Something went wrong',
                'errors' => $exception->getTrace()
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

//    update process
    public function updateWorkOrder(Request $request, $id)
    {
        try {
//            start the transaction
            DB::beginTransaction();

//            if role use pm
            if ($this->getUserRole() == 'pm') {
//                validation the request
                $validator = Validator::make($request->all(), [
                    'operator_id' => 'required|exists:users,id',
                    'status' => 'required|in:pending,inProgress,completed,canceled',
                ]);

//                if validation fails
                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Validation failed',
                        'errors' => $validator->errors()
                    ]);
                }

//                  update work order
                WorkOrder::where('id', $id)->update([
                    'operator_id' => $request->operator_id,
                    'status' => $request->status
                ]);

            } else {
                $status = $request->status;

                // validation request
                $validator = Validator::make($request->all(), [
                    'status' => 'required|in:pending,inProgress,completed,canceled',
                ])->after(function ($validator) use ($request, $status) {
                // add dynamic validation for qty_{status}
                    if ($status) {
                        $field = "qty_{$status}";
                        if (!isset($request->$field)) {
                            $validator->errors()->add($field, "The {$field} field is required.");
                        } elseif (!is_numeric($request->$field)) {
                            $validator->errors()->add($field, "The {$field} field must be numeric.");
                        }
                    }
                });

//                response if validator fails
                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Validation failed',
                        'errors' => $validator->errors()
                    ], ResponseAlias::HTTP_BAD_REQUEST);
                }

//                update work order
                WorkOrder::where('id', $id)->update([
                    'status' => $status,
                    "qty_{$status}" => $request->{"qty_{$status}"},
                ]);
            }

//            commit changes
            DB::commit();

//            response success
            return response()->json([
                'status' => 'success',
                'message' => 'Work Order updated successfully',
            ], ResponseAlias::HTTP_OK);

        } catch (\Exception $exception) {
//            rollback the changes
            DB::rollBack();

//            response failure
            return response()->json([
                'status' => 'failed',
                'message' => 'Something went wrong',
                'errors' => $exception->getTrace()
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }
}
