<?php
namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProductController extends Controller
{
    public function index()
    {
        try {

            $products = Product::with('category')->orderByDesc('id')->get();

            return response()->json([
                'success' => true,
                'message' => 'Products fetched successfully',
                'data'    => $products,
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch products',
                'error'   => $e->getMessage(),
            ], 500);
        }

    }

    public function store(Request $request)
    {
        try {

            $validated = $request->validate([
                'category_id'         => ['required', 'integer', 'exists:categories,id'],
                'product_name'        => ['required', 'string', 'max:255'],
                'sku'                 => ['required', 'string', 'max:255', 'unique:products,sku'],
                'unit'                => ['required', 'string', 'max:50'],
                'image_path'          => ['nullable', 'string', 'max:255'],
                'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
                'color'               => ['nullable', 'string', 'max:100'],
                'size'                => ['nullable', 'string', 'max:100'],
                'weight'              => ['nullable', 'numeric', 'min:0'],
                'price'               => ['required', 'numeric', 'min:0'],
                'status'              => ['boolean'],
                'stock_qty'           => ['nullable', 'integer', 'min:0'],
            ]);

            $product = Product::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data'    => $product,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product',
                'error'   => $e->getMessage(),
            ], 500);
        }

    }

    public function show(int $id)
    {
        try {
            $product = Product::with('category')->find($id);

            if (! $product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product fetched successfully',
                'data'    => $product,
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch product',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            $product = Product::find($id);

            if (! $product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                ], 404);
            }

            $validated = $request->validate([
                'category_id'         => ['sometimes', 'integer', 'exists:categories,id'],
                'product_name'        => ['sometimes', 'string', 'max:255'],
                'sku'                 => ['sometimes', 'string', 'max:255', 'unique:products,sku,' . $id],
                'unit'                => ['sometimes', 'string', 'max:50'],
                'image_path'          => ['nullable', 'string', 'max:255'],
                'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
                'color'               => ['nullable', 'string', 'max:100'],
                'size'                => ['nullable', 'string', 'max:100'],
                'weight'              => ['nullable', 'numeric', 'min:0'],
                'price'               => ['sometimes', 'numeric', 'min:0'],
                'status'              => ['boolean'],
                'stock_qty'           => ['nullable', 'integer', 'min:0'],
            ]);
            $product->update($validated);
            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data'    => $product,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $product = Product::find($id);

            if (! $product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                ], 404);
            }

            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully',
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

}
