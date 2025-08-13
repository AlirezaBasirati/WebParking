<?php

namespace App\Http\Controllers;

use App\DTOs\CreateInvoiceDto;
use App\Http\Requests\StoreSalesInvoiceRequest;
use App\Jobs\SendInvoiceToExact;
use App\Repositories\Contracts\ClientRequestLogRepositoryInterface;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use App\Services\ExactOnline\ExactOnlineInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Invoices",
 *     description="Invoice management operations"
 * )
 */
class InvoiceController extends Controller
{
    protected InvoiceRepositoryInterface $invoice;
    protected ExactOnlineInterface $exactOnline;
    protected ClientRequestLogRepositoryInterface $clientRequestLogRepository;

    public function __construct(
        InvoiceRepositoryInterface $invoice,
        ExactOnlineInterface $exactOnline,
        ClientRequestLogRepositoryInterface $clientRequestLogRepository
    ) {
        $this->invoice = $invoice;
        $this->exactOnline = $exactOnline;
        $this->clientRequestLogRepository = $clientRequestLogRepository;
    }

    /**
     * @OA\Get(
     *   path="/api/invoice",
     *   operationId="getAllInvoices",
     *   tags={"Invoices"},
     *   summary="Get all invoices",
     *   description="Retrieve a list of all invoices in the system",
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/InvoiceList")
     *   ),
     *   @OA\Response(
     *     response=500,
     *     description="Internal server error",
     *     @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *   )
     * )
     */
    public function index()
    {
        return $this->invoice->allInvoices();
    }


    /**
     * @OA\Post(
     *   path="/api/sales-invoices",
     *   operationId="createSalesInvoice",
     *   tags={"Invoices"},
     *   summary="Create a new sales invoice",
     *   description="Create a new sales invoice with line items and dispatch it to the Exact Online queue",
     
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/CreateInvoiceRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Invoice created successfully",
     *     @OA\JsonContent(ref="#/components/schemas/CreateInvoiceResponse")
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Validation error",
     *     @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *   ),
     *   @OA\Response(
     *     response=500,
     *     description="Internal server error",
     *     @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *   )
     * )
     */
    public function create(StoreSalesInvoiceRequest $request)
    {
        $dto = CreateInvoiceDto::fromRequest($request);

        $invoiceData = [
            'invoice_number' => $dto->invoice_number,
            'customer_id' => $dto->customer_id,
            'invoice_date' => $dto->invoice_date->format('Y-m-d'),
            'due_date' => $dto->due_date->format('Y-m-d'),
            'currency' => $dto->currency,
            'subtotal' => $dto->subtotal,
            'tax' => $dto->tax,
            'total' => $dto->total,
        ];

        $newInvoice = $this->invoice->create($invoiceData, $dto->lines);

        $uuid = $request->header('X-Request-UUID');
        $this->clientRequestLogRepository->updateByRequestUuid($uuid, [
            'customer_id' => $dto->customer_id,
            'invoice_id'  => $newInvoice->id,
        ]);

        $requestLogId = $request->header('X-Request-ID');
        SendInvoiceToExact::dispatch($dto, $requestLogId, $newInvoice->id)->onQueue('exact');
        $this->invoice->updateStatus($newInvoice->id, ['status' => 'posted']);

        return response()->json([201, 'Your Request Has Dispatched On Queue.', $newInvoice]);
    }

    /**
     * @OA\Get(
     *   path="/api/log",
     *   operationId="getAllInvoicesWithLog",
     *   tags={"InvoiceLog"},
     *   summary="Get all invoices With Logs",
     *   description="Retrieve a list of all invoices with Log in the system",
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/InvoiceLog")
     *   ),
     *   @OA\Response(
     *     response=500,
     *     description="Internal server error",
     *     @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *   )
     * )
     */
    public function invoiceWithLog()
    {
        return $this->invoice->invoiceWithLog();
    }
}
