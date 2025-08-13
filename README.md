
<b>Overview :<b>
<br>
This application showcases fundamental skills: clean code practices, solid, the Laravel framework, selected design patterns, and a foundational understanding of architecture and system design. I wrote several tests to illustrate my approach to testing, though coverage is not yet complete. I kept the assignment intentionally simple with minimal dependencies—see the setup instructions for details. While this is a take-home assignment, the design is built to scale; I’m happy to discuss options for expanding it.
    • Focus: clean code, solid, Laravel, common design patterns, basic low level architecture/system design.
    • Tests: several included; coverage not yet complete.
    • Scope: intentionally simple; minimal dependencies (see setup instructions).
    • Scalability: designed with room to grow; open to discussing next steps.


Setup Instruction :
 
Install if not it is installed on your machine:
1-PHP.8    2-Composer 3-Mysql.8
    • Clone Project
    • CD .../your project folder
    • Run composer install
    • Create a database on mysql
    • Set database name, mysql username and password in .env
    • php artisan migrate “to create tables”
    • php artisan serve “to run the project” 
    • php artisan queue:work –queue=exact “to start the exact job”
    • http://localhost:8000/api/documentation# “for swagger”
    • OR Import the postman collection file(WebParking.postman_collection) that is in the root of project

How To Run Test:
    • Run php artisan test

Architecture Choice:

Architecture Choices. The service exposes a REST endpoint to receive invoices, persists them, and forwards them asynchronously to the external provider. We use Laravel’s container for dependency injection to isolate boundaries (ExactOnlineInterface, repositories) and enable test doubles. Invoice forwarding runs in a queued job (SendInvoiceToExact) backed by MySQL, which keeps API latency low and enables controlled retries.
Reliability. Calls to the provider use strict HTTP timeouts and an idempotency key derived from business identifiers. Transient failures (5xx/timeouts) are retried with exponential backoff and jitter; rate limits (429) honor Retry-After. A lightweight circuit breaker in cache avoids hammering the provider during incidents by deferring jobs temporarily.
Data & Status. Invoice lifecycle is tracked via statuses: draft → posted → forwarded or failed. 
Duplicate responses (409) are treated as failed if the remote invoice matches our idempotency key. 
Observability. Every attempt is logged with correlation IDs and persisted to an external call log for auditability. The API returns 201 Accepted with a tracking ID.
Testing. We mock ExactOnlineInterface to simulate 429/409/5xx paths, fake the queue for enqueue assertions, and run feature tests against MySQL. This ensures correctness of retries, idempotency, and terminal states without calling the real provider.

* This is a minimal design. It would be more production-ready if I implemented:
    • Factory Design Pattern for handling fallback. 
    • More worker than 1.
    • More jobs to handle each response from Exact.
    • Service For Log management to support multi service. 
    • Service For send notifications

My approach to simulating Exact Online
    • Interface-first design: I depend on an ExactOnlineInterface so the simulator can be swapped for a real HTTP client without touching business logic.
    • Scenario modeling: The mock returns realistic outcomes for invoice creation—201 Created, 409 Conflict (duplicate), 429 Too Many Requests, and 500 Internal Server Error—each with structured payloads via ExactResponse.
    • Idempotency handling: On duplicates I return a consistent external_id, treating 409 as “already created,” so downstream logic can reconcile safely.
    • Traceable IDs: Successful/duplicate responses include a deterministic-looking external ID (EXT-{invoice_number}-{uniqid}) to simplify logging and tests.
    • Determinism-ready: While it can randomize scenarios for exploratory testing, it’s trivial to force scenarios (e.g., via config or a seeded function) for repeatable tests.
    • Production parity: Responses mirror HTTP semantics you’d see from the real API, enabling retry/backoff, error surfacing, and idempotent upserts to be exercised now and reused later.



