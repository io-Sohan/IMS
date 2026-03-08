<?php
namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class StockController extends Controller
{
    public function index()
    {
        try {
            $stockMovements = StockMovement::with('product.category')->orderBy('id', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Stock movements list retrieved successfully',
                'data'    => $stockMovements,
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to retrieve stock movements',
                'error'   => $e->getMessage(),
            ], 500);
        }

    }

    public function stockIn(Request $request)
    {

        try {

            $validated = $request->validate([
                'product_id' => ['required', 'exists:products,id'],
                'quantity'   => ['required', 'integer', 'min:1'],
                'note'       => ['nullable', 'string'],
            ]);

            DB::beginTransaction();

            $stockMovement = StockMovement::create([
                'product_id' => $validated['product_id'],
                'quantity'   => $validated['quantity'],
                'type'       => 'IN',
                'note'       => $validated['note'] ?? null,
            ]);

            $product = Product::findOrFail($validated['product_id']);
            // $currentStock       = $product->stock_qty ?? 0;
            // $updatedStock       = $currentStock + $validated['quantity'];
            // $product->stock_qty = $updatedStock;
            // $product->save();

            $product->stock_qty += $validated['quantity'];
            $product->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock-in recorded successfully',
                'data'    => $stockMovement,
            ], 201);

        } catch (ValidationException $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);

        } catch (Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => 'An error occurred during validation',
                'error'   => $e->getMessage(),
            ], 500);

        }
    }

    public function stockAdjustment(Request $request)
    {

        try {
            $validated = $request->validate([
                'product_id' => ['required', 'exists:products,id'],
                'quantity'   => ['required', 'integer'],
                'type'       => ['required', 'string', 'in:IN,OUT'],
                'note'       => ['nullable', 'string'],
                'invoice_id' => ['nullable', 'string', 'exists:invoices,id'],
            ]);

            DB::beginTransaction();

            $stockMovement = StockMovement::create([
                'product_id' => $validated['product_id'],
                'quantity'   => $validated['quantity'],
                'type'       => $validated['type'],
                'note'       => $validated['note'] ?? null,
                'invoice_id' => $validated['invoice_id'] ?? null,
            ]);

            // Update product stock quantity based on adjustment type
            $product = Product::findOrFail($validated['product_id']);
            if ($validated['type'] === 'IN') {
                $product->stock_qty += $validated['quantity'];
            } else {
                if ($product->stock_qty < $validated['quantity']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient stock for adjustment',
                    ], 400);
                } else {
                    $product->stock_qty -= $validated['quantity'];
                }
            }
            $product->save();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Stock adjustment recorded successfully',
                'data'    => $stockMovement,
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during validation',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
