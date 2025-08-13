<?php

namespace App\Http\Middleware;

use App\Repositories\Contracts\ClientRequestLogRepositoryInterface;
use Closure;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestLogger
{
    protected ClientRequestLogRepositoryInterface $clientRequestLogRepository;

    public function __construct(ClientRequestLogRepositoryInterface $clientRequestLogRepository)
    {
        $this->clientRequestLogRepository = $clientRequestLogRepository;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $clientRequest = $this->clientRequestLogRepository->create([
            'request_uuid'   => Str::uuid()->toString(),
            'request_url'    => $request->fullUrl(),
            'request_method' => $request->method(),
            'request_headers' => $request->headers->all(),
            'request_body'   => $request->all(),
        ]);
        $request->headers->set('X-Request-UUID', $clientRequest->request_uuid);
        $request->headers->set('X-Request-ID', $clientRequest->id);

        return $next($request);
    }
}
