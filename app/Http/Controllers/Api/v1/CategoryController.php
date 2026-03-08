<?php
namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class CategoryController extends Controller
{
    public function index()
    {
        try {

            $categories = Category::orderBy('id', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Categories fetched successfully',
                'data'    => $categories,
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'        => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'status'      => ['boolean'],
            ]);

            $category = Category::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data'    => $category,
            ], 201);

        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $ve->errors(),
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id)
    {
        try {
            $category = Category::findOrFail($id);

            if (! $category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Category fetched successfully',
                'data'    => $category,
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch category',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            $category = Category::findOrFail($id);

            if (! $category) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Category not found',
                ], 404);
            }

            $validated = $request->validate([
                'name'        => ['sometimes', 'required', 'string', 'max:255'],
                'description' => ['sometimes', 'nullable', 'string'],
                'status'      => ['sometimes', 'boolean'],
            ]);
            $category->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'data'    => $category,

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
                'message' => 'Failed to update category',
                'error'   => $e->getMessage(),
            ], 500);

        }
    }

    public function destroy(int $id)
    {
        try {
            $category = Category::findOrFail($id);

            if (! $category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found',
                ], 404);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully',
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

}
