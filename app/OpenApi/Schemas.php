<?php

namespace App\OpenApi;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Invoices API"
 * )
 *
 * @OA\Server(
 *     url="/",
 *     description="Base server"
 * )
 *
 
 * ---------------------------------------------------------
 * Basic error shapes
 * ---------------------------------------------------------
 * @OA\Schema(
 *   schema="ErrorResponse",
 *   type="object",
 *   @OA\Property(property="message", type="string", example="Internal server error")
 * )
 * @OA\Schema(
 *   schema="ValidationErrorResponse",
 *   type="object",
 *   @OA\Property(property="message", type="string", example="The invoice number field is required."),
 *   @OA\Property(
 *     property="errors",
 *     type="object",
 *     additionalProperties=@OA\Schema(type="array", @OA\Items(type="string"))
 *   )
 * )
 *
 * ---------------------------------------------------------
 * Invoice & line item schemas
 * ---------------------------------------------------------
 * @OA\Schema(
 *   schema="LineItem",
 *   type="object",
 *   required={"line_no","description","quantity","unit_price","vat_code","line_total"},
 *   @OA\Property(property="line_no", type="integer", example=1),
 *   @OA\Property(property="description", type="string", example="Product A"),
 *   @OA\Property(property="quantity", type="number", format="float", example=2),
 *   @OA\Property(property="unit_price", type="number", format="float", example=50.00),
 *   @OA\Property(property="vat_code", type="string", example="VAT20"),
 *   @OA\Property(property="line_total", type="number", format="float", example=100.00)
 * )
 * @OA\Schema(
 *   schema="Invoice",
 *   type="object",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="invoice_number", type="string", example="INV-001"),
 *   @OA\Property(property="customer_id", type="integer", example=1),
 *   @OA\Property(property="invoice_date", type="string", format="date", example="2023-12-01"),
 *   @OA\Property(property="due_date", type="string", format="date", example="2023-12-31"),
 *   @OA\Property(property="currency", type="string", example="USD"),
 *   @OA\Property(property="subtotal", type="number", format="float", example=100.00),
 *   @OA\Property(property="tax", type="number", format="float", example=20.00),
 *   @OA\Property(property="total", type="number", format="float", example=120.00),
 *   @OA\Property(property="status", type="string", example="posted"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * @OA\Schema(
 *   schema="InvoiceList",
 *   type="array",
 *   @OA\Items(ref="#/components/schemas/Invoice")
 * )
 *
 * 
 * 
*
 * ---------------------------------------------------------
 * Invoice with Log schema
 * ---------------------------------------------------------
 * @OA\Schema(
 *   schema="InvoiceLog",
 *   type="object",
 *   @OA\Property(property="invoice", ref="#/components/schemas/Invoice"),
 *   @OA\Property(
 *     property="logs",
 *     type="array",
 *     @OA\Items(
 *       type="object",
 *       @OA\Property(property="id", type="integer", example=101),
 *       @OA\Property(property="invoice_id", type="integer", example=1),
 *       @OA\Property(property="action", type="string", example="status_changed"),
 *       @OA\Property(property="description", type="string", example="Invoice marked as paid"),
 *       @OA\Property(property="created_at", type="string", format="date-time", example="2023-12-05T14:30:00Z"),
 *       @OA\Property(property="updated_at", type="string", format="date-time", example="2023-12-05T14:35:00Z")
 *     )
 *   )
 * )
 *
 * ---------------------------------------------------------
 * Request/response bodies
 * ---------------------------------------------------------
 * @OA\Schema(
 *   schema="CreateInvoiceRequest",
 *   type="object",
 *   required={"invoice_number","customer_id","invoice_date","due_date","currency","subtotal","tax","total","lines"},
 *   @OA\Property(property="invoice_number", type="string", example="INV-001"),
 *   @OA\Property(property="customer_id", type="integer", example=1),
 *   @OA\Property(property="invoice_date", type="string", format="date", example="2023-12-01"),
 *   @OA\Property(property="due_date", type="string", format="date", example="2023-12-31"),
 *   @OA\Property(property="currency", type="string", example="USD"),
 *   @OA\Property(property="subtotal", type="number", format="float", example=100.00),
 *   @OA\Property(property="tax", type="number", format="float", example=20.00),
 *   @OA\Property(property="total", type="number", format="float", example=120.00),
 *   @OA\Property(
 *     property="lines",
 *     type="array",
 *     minItems=1,
 *     @OA\Items(ref="#/components/schemas/LineItem")
 *   )
 * )
 * @OA\Schema(
 *   schema="CreateInvoiceResponse",
 *   type="object",
 *   @OA\Property(property="0", type="integer", example=201),
 *   @OA\Property(property="1", type="string", example="Your Request Has Dispatched On Queue."),
 *   @OA\Property(property="2", ref="#/components/schemas/Invoice")
 * )
 */
class Schemas {}
