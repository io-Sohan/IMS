<?php
namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    public function index()
    {

        try {
            $customer = Customer::orderBy('id', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Customer fetched successfully',
                'data'    => $customer,
            ], 200);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch customers',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'        => ['required', 'string', 'max:255'],
                'email'       => ['nullable', 'email', 'unique:customers,email'],
                'mobile'       => ['required', 'string', 'unique:customers,mobile', 'max:20'],
                'description' => ['nullable', 'string'],
            ]);

            $customer = Customer::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully',
                'data'    => $customer,
            ], 201);

        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $ve->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create customer',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id)
    {
        try {
            $customer = Customer::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Customer fetched successfully',
                'data'    => $customer,
            ], 200);

            if (! $customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found',
                ], 404);
            }

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch customer',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            $customer = Customer::findOrFail($id);

            if (! $customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found',
                ], 404);
            }

            $validated = $request->validate([
                'name'        => ['required', 'string', 'max:255'],
                'email'       => ['nullable', 'email', 'unique:customers,email,' . $customer->id],
                'mobile'       => ['required', 'string','unique:customers,mobile,' . $customer->id, 'max:20'],
                'description' => ['nullable', 'string'],
            ]);

            $customer->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Customer updated successfully',
                'data'    => $customer,
            ], 200);

        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $ve->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update customer',
                'error'   => $e->getMessage(),
            ], 500);
        }

    }


    public function destroy(int $id)
    {
        try {
            $customer = Customer::findOrFail($id);

            if (! $customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found',
                ], 404);
            }

            $customer->delete();

            return response()->json([
                'success' => true,
                'message' => 'Customer deleted successfully',
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete customer',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }




}
