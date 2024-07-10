<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadBillingCsvRequest;
use App\Services\BillingCsvService;
use Illuminate\Http\JsonResponse;
use League\Csv\Exception;
use League\Csv\InvalidArgument;
use League\Csv\SyntaxError;
use League\Csv\UnavailableStream;

class BillingCsvController extends Controller
{
    protected BillingCsvService $billingCsvService;

    public function __construct(BillingCsvService $billingCsvService)
    {
        $this->billingCsvService = $billingCsvService;
    }

    /**
     * Handle the file upload and process the CSV.
     *
     * @param UploadBillingCsvRequest $request
     * @return JsonResponse
     * @throws Exception
     * @throws InvalidArgument
     * @throws SyntaxError
     * @throws UnavailableStream
     */
    public function upload(UploadBillingCsvRequest $request): JsonResponse
    {
        $this->billingCsvService->processCsvFile($request->file('file'));
        $responseData = [
            'data' => [
                'uuid' => $this->billingCsvService->getUuidStorage()
            ],
            'status' => true
        ];

        return response()->json($responseData, 200);
    }
}
