<?php
namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class InvoiceController extends Controller
{
    public function index()
    {
        try {
            $invoices = Invoice::with(['customer', 'items.product.category'])->orderByDesc('id')->get();
            return response()->json([
                'success' => true,
                'message' => 'Invoices retrieved successfully',
                'data'    => $invoices,
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve invoices',
                'error'   => $e->getMessage(),
            ], 500);
        }

    }

    public function store(Request $request)
    {

        try {
            $validated = $request->validate([
                'customer_id'             => ['nullable', 'exists:customers,id'],
                'invoice_no'              => ['nullable', 'string', 'max:255', 'unique:invoices,invoice_no'],
                'invoice_date'            => ['nullable', 'date'],
                'items'                   => ['required', 'array', 'min:1'],
                'items.*.product_id'      => ['required', 'exists:products,id'],
                'items.*.quantity'        => ['required', 'integer', 'min:1'],
                'items.*.unit_price'      => ['required', 'numeric', 'min:0'],
                'items.*.discount_type'   => ['nullable', 'string', 'in:percent,fixed'],
                'items.*.discount_value'  => ['required', 'numeric', 'min:0'],
                'items.*.discount_amount' => ['required', 'numeric', 'min:0'],
                'items.*.line_total'      => ['required', 'numeric', 'min:0'],
                'subtotal'                => ['required', 'numeric', 'min:0'],
                'discount_type'           => ['nullable', 'string', 'in:percent,fixed'],
                'discount_value'          => ['nullable', 'numeric', 'min:0'],
                'discount_amount'         => ['required', 'numeric', 'min:0'],
                'grand_total'             => ['required', 'numeric', 'min:0'],
                'status'                  => ['nullable', 'string', 'in:draft,finalized,cancelled'],
            ]);

            DB::beginTransaction();

            // Generate invoice number if not provided
            if (empty($validated['invoice_no'])) {
                $validated['invoice_no'] = $this->generateInvoiceNumber();
            }

            $invoice = Invoice::create([
                'customer_id'     => $validated['customer_id'] ?? null,
                'invoice_no'      => $validated['invoice_no'],
                'invoice_date'    => $validated['invoice_date'] ?? now(),
                'subtotal'        => $validated['subtotal'],
                'discount_type'   => $validated['discount_type'] ?? null,
                'discount_value'  => $validated['discount_value'] ?? null,
                'discount_amount' => $validated['discount_amount'],
                'grand_total'     => $validated['grand_total'],
                'status'          => $validated['status'],
            ]);

            foreach ($validated['items'] as $item) {
                InvoiceItem::create([
                    'invoice_id'      => $invoice->id,
                    'product_id'      => $item['product_id'],
                    'quantity'        => $item['quantity'],
                    'unit_price'      => $item['unit_price'],
                    'discount_type'   => $item['discount_type'] ?? null,
                    'discount_value'  => $item['discount_value'],
                    'discount_amount' => $item['discount_amount'],
                    'line_total'      => $item['line_total'],
                ]);
            }

            //create stock movements for each invoice item
            if ($invoice->status === 'finalized') {
                $this->createStockMovement($invoice);
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully',
                'data'    => $invoice->load(['customer', 'items.product.category']),
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
                'message' => 'Failed to create invoice',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id)
    {
        try {
            $invoice = Invoice::with(['customer', 'items.product.category'])->find($id);
            if (! $invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Invoice retrieved successfully',
                'data'    => $invoice,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve invoice',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, int $id)
    {
        try {

            $invoice = Invoice::with(['items'])->find($id);

            if (! $invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found',
                ], 404);
            }

            if ($invoice->status === 'finalized') {
                return response()->json([
                    'success' => false,
                    'message' => 'Finalized invoices cannot be updated',
                ], 400);
            }

            $validated = $request->validate([
                'invoice_no'              => ['sometimes', 'required', 'string', 'max:255', 'unique:invoices,invoice_no'],
                'invoice_date'            => ['sometimes', 'required', 'date'],
                'items'                   => ['sometimes', 'required', 'array', 'min:1'],
                'items.*.product_id'      => ['required', 'exists:products,id'],
                'items.*.quantity'        => ['required', 'integer', 'min:1'],
                'items.*.unit_price'      => ['required', 'numeric', 'min:0'],
                'items.*.discount_type'   => ['nullable', 'string', 'in:percent,fixed'],
                'items.*.discount_value'  => ['required', 'numeric', 'min:0'],
                'items.*.discount_amount' => ['required', 'numeric', 'min:0'],
                'items.*.line_total'      => ['required', 'numeric', 'min:0'],
                'subtotal'                => ['sometimes', 'required', 'numeric', 'min:0'],
                'discount_type'           => ['nullable', 'string', 'in:percent,fixed'],
                'discount_value'          => ['sometimes', 'required', 'numeric', 'min:0'],
                'discount_amount'         => ['sometimes', 'required', 'numeric', 'min:0'],
                'grand_total'             => ['sometimes', 'required', 'numeric', 'min:0'],
                'status'                  => ['sometimes', 'nullable', 'string', 'in:draft,finalized,cancelled'],
            ]);

            DB::beginTransaction();
            $oldStatus = $invoice->status;
            if (isset($validated['items'])) {

                //delete old items
                $invoice->items()->delete();

                //create new items
                foreach ($validated['items'] as $item) {
                    InvoiceItem::create([
                        'invoice_id'      => $invoice->id,
                        'product_id'      => $item['product_id'],
                        'quantity'        => $item['quantity'],
                        'unit_price'      => $item['unit_price'],
                        'discount_type'   => $item['discount_type'] ?? null,
                        'discount_value'  => $item['discount_value'],
                        'discount_amount' => $item['discount_amount'],
                        'line_total'      => $item['line_total'],
                    ]);
                }

            }

            $updateData = [
                'invoice_no'     => $validated['invoice_no'] ?? $invoice->invoice_no,
                'invoice_date'   => $validated['invoice_date'] ?? $invoice->invoice_date,
                'discount_type'  => $validated['discount_type'] ?? $invoice->discount_type,
                'discount_value' => $validated['discount_value'] ?? $invoice->discount_value,
                'status'         => $validated['status'] ?? $invoice->status,
            ];

            if (isset($validated['subtotal'])) {
                $updateData['subtotal']        = $validated['subtotal'];
                $updateData['discount_amount'] = $validated['discount_amount'];
                $updateData['grand_total']     = $validated['grand_total'];
            }

            if (isset($validated['discount_amount'])) {
                $updateData['discount_amount'] = $validated['discount_amount'];
                $updateData['grand_total']     = $validated['grand_total'];

            }

            //update invoice fields
            $invoice->update($updateData);

            //if status changed to finalized, create stock movements
            $newStatus = $invoice->status ?? $invoice->status;
            if ($oldStatus !== 'finalized' && $newStatus === 'finalized') {
                $this->createStockMovement($invoice->fresh());
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Invoice updated successfully',
                'data'    => $invoice->load(['customer', 'items.product.category']),
            ]);
        } catch (ValidationException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update invoice',
                'error'   => $e->errors(),
            ], 500);
        } catch (Throwable $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update invoice',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $invoice = Invoice::with(['items'])->find($id); //$invoice = Invoice::find($id);//same

            if (! $invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found',
                ], 404);
            }

            if ($invoice->status === 'finalized') {
                return response()->json([
                    'success' => false,
                    'message' => 'Finalized invoices cannot be deleted',
                ], 400);
            }

            DB::beginTransaction();

            $invoice->items()->delete();
            $invoice->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice deleted successfully',
            ]);

        } catch (Throwable $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete invoice',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function createStockMovement(Invoice $invoice)
    {
        foreach ($invoice->items as $item) {
            $product = Product::findOrFail($item->product_id);

            //check stock availability
            if ($product->stock_qty < $item->quantity) {
                throw new \Exception("Insufficient stock for product ID: {$product->product_name} . Available: {$product->stock_qty} . Required: {$item->quantity}");
            }

            //create stock movement
            StockMovement::create([
                'product_id' => $item->product_id,
                'quantity'   => $item->quantity,
                'type'       => 'OUT',
                'note'       => "Stock out for Invoice #{$invoice->invoice_no}",
                'invoice_id' => $invoice->id,

            ]);

            //update product stock quantity
            $product->stock_qty -= $item->quantity;
            $product->save();
        }
    }

    public function cancel(int $id)
    {
        try {
            $invoice = Invoice::with(['items'])->find($id);

            if (! $invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found',
                ], 404);
            }

            if ($invoice->status !== 'finalized') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only finalized invoice can be cancelled',
                ], 400);
            }

            DB::beginTransaction();

            foreach ($invoice->items as $item) {

                $product = Product::findOrFail($item->product_id);

                // Reverse stock (add back)
                $product->stock_qty += $item->quantity;
                $product->save();

                //  Create reverse stock movement
                StockMovement::create([
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                    'type'       => 'IN',
                    'note'       => "Stock reversed for cancelled Invoice #{$invoice->invoice_no}",
                    'invoice_id' => $invoice->id,
                ]);
            }

            // Update invoice status
            $invoice->update([
                'status' => 'cancelled',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice cancelled and stock reversed successfully',
            ]);

        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel invoice',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function generateInvoiceNumber()
    {

        $year        = Carbon::now()->format('Y');
        $month       = Carbon::now()->format('m');
        $lastInvoice = Invoice::where('invoice_no', 'like', "INV-{$year}-{$month}-%")
            ->orderBy('invoice_no', 'desc')
            ->first();

        if ($lastInvoice) {
            $sequence = (int) substr($lastInvoice->invoice_no, -4);
            $sequence++;
        } else {
            $sequence = 1;
        }
        return sprintf("INV-%s-%s-%04d", $year, $month, $sequence);

    }

}
