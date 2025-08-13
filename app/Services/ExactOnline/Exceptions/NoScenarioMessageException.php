<?php

namespace App\Services\ExactOnline\Exceptions;

use Exception;

class NoScenarioMessageException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }

    // Optional: prevent stack trace in response
    public function render($request)
    {
        return response()->json([
            'message' => $this->getMessage()
        ], 400); // HTTP status code
    }
}
