<?php

namespace App\Http\Requests;

use App\Repositories\Contracts\ClientRequestLogRepositoryInterface;
use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class StoreSalesInvoiceRequest extends FormRequest
{
    protected ClientRequestLogRepositoryInterface $clientRequestLogRepository;

    public function __construct(ClientRequestLogRepositoryInterface $clientRequestLogRepository)
    {
        $this->clientRequestLogRepository = $clientRequestLogRepository;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'invoice_number'        => ['required', 'string', 'max:255',  Rule::unique('invoices')
            ->where(fn ($query) => $query->where('customer_id', request('customer_id')))],
            'invoice_date'          => ['required', 'date'],
            'due_date'              => ['required', 'date', 'after_or_equal:invoice_date'],
            'customer_id'           => ['required', 'integer'],
            'currency'              => ['required', 'string', 'size:3'], // ISO currency code
            'lines'                 => ['required', 'array', 'min:1'],
            'lines.*.line_no'       => ['required', 'integer', 'min:1'],
            'lines.*.description'   => ['required', 'string', 'max:255'],
            'lines.*.quantity'      => ['required', 'numeric', 'min:0.01'],
            'lines.*.unit_price'    => ['required', 'numeric', 'min:0'],
            'lines.*.vat_code'      => ['required', 'string', 'max:10'],
            'lines.*.line_total'    => ['required', 'numeric', 'min:0'],
            'subtotal'              => ['required', 'numeric', 'min:0'],
            'tax'                   => ['required', 'numeric', 'min:0'],
            'total'                 => ['required', 'numeric', 'min:0'],
        ];
    }
    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $subtotal = $this->input('subtotal');
            $tax = $this->input('tax');
            $total = $this->input('total');

            if (is_numeric($subtotal) && is_numeric($tax) && is_numeric($total)) {
                // Compare with small floating point tolerance
                if (abs(($subtotal + $tax) - $total) > 0.0001) {
                    $validator->errors()->add('total', 'The total must equal subtotal plus tax.');
                }
            }
        });
    }

    protected function failedValidation(Validator $validator)
    {
        // This code will only run if the exception is somehow caught
        try {
            $uuid = $this->header('X-Request-UUID');
            if ($uuid) {
                // Save the full error bag as JSON (or adjust to your column type)
                $errorsJson = json_encode($validator->errors()->toArray(), JSON_UNESCAPED_UNICODE);
                $this->clientRequestLogRepository->updateByRequestUuid($uuid, ['validation_error' => $errorsJson]);
            }
        } catch (\Throwable $e) {
            // Swallow logging errors so we still return the proper validation response
            // Optionally: report($e);
        }
    }
}
