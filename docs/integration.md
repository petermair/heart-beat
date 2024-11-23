# ThingsBoard Integration

## Overview
This document outlines the requirements and implementation plan for the ThingsBoard integration with our monitoring system.

## API Endpoints

### Authentication
- Endpoint: `/api/auth/login`
- Method: POST
- Payload: `{ "username": string, "password": string }`
- Response: `{ "token": string }`

### Health Check
- Endpoint: `/api/health`
- Method: GET
- Headers: `X-Authorization: Bearer {token}`
- Response: Status 200 if healthy

### System Info
- Endpoint: `/api/system/info`
- Method: GET
- Headers: `X-Authorization: Bearer {token}`
- Response: System information including version, OS details

### System Metrics
- Endpoint: `/api/system/metrics`
- Method: GET
- Headers: `X-Authorization: Bearer {token}`
- Response: System metrics including CPU, memory, disk usage

### Active Sessions
- Endpoint: `/api/system/sessions`
- Method: GET
- Headers: `X-Authorization: Bearer {token}`
- Response: List of active user sessions

## Implementation Plan

### 1. Core Components

#### ThingsBoardConnector
- Extends Saloon's Connector class
- Handles authentication and token management
- Manages base URL and headers

#### ThingsBoardMonitor
- Extends AbstractMonitor
- Implements health check logic
- Caches responses for performance
- Handles error states

### 2. Request Classes
- GetHealthRequest
- GetSystemInfoRequest
- GetSystemMetricsRequest
- GetActiveSessionsRequest

### 3. Tests

#### ThingsBoardMonitorTest
- Test health check functionality
- Test metrics retrieval
- Test error handling
- Test caching behavior

#### ThingsBoardConnectorTest
- Test authentication flow
- Test token management
- Test request headers

## Implementation Details with Saloon

### Connector Implementation
```php
class ThingsBoardConnector extends Connector
{
    protected ?string $token = null;

    public function __construct(
        protected string $baseUrl,
        protected array $credentials = [],
        protected array $config = []
    ) {
        parent::__construct();
    }

    public function resolveBaseUrl(): string
    {
        return $this->baseUrl;
    }

    protected function defaultConfig(): array
    {
        return [
            'timeout' => 30,
            'verify_ssl' => true,
        ];
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    public function resolveAuthenticator(): ?TokenAuthenticator
    {
        return $this->token ? new TokenAuthenticator('Bearer ' . $this->token) : null;
    }

    protected function authenticate(): void
    {
        if (empty($this->credentials)) {
            return;
        }

        $response = $this->send(new LoginRequest([
            'username' => $this->credentials['username'],
            'password' => $this->credentials['password'],
        ]));

        if ($response->ok()) {
            $this->token = $response->json('token');
        }
    }
}
```

### Request Implementation
```php
class GetHealthRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/api/health';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
        ];
    }
}
```

### Authentication Flow
```php
// In ThingsBoardConnector
protected function authenticate(): void
{
    if (empty($this->credentials)) {
        return;
    }

    $response = $this->send(new LoginRequest([
        'username' => $this->credentials['username'],
        'password' => $this->credentials['password'],
    ]));

    if ($response->ok()) {
        $this->token = $response->json('token');
    }
}
```

### Error Handling
```php
try {
    $response = $connector->send($request);
} catch (RequestException $e) {
    // Handle request exceptions (400-500 errors)
    $response = $e->response;
    $status = $response->status();
    $body = $response->body();
} catch (ConnectorException $e) {
    // Handle connection errors (timeout, DNS, etc)
} catch (SaloonException $e) {
    // Handle all other Saloon errors
}
```

### Testing
```php
class ThingsBoardMonitorTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock responses
        MockResponse::fake([
            '*' => MockResponse::make(
                ['status' => 'ok'],
                200
            )
        ]);
    }

    public function test_health_check(): void
    {
        $response = $this->connector->send(new GetHealthRequest());
        
        $this->assertTrue($response->ok());
        $this->assertSame(200, $response->status());
    }
}
```

## Key Saloon Features to Use

### Request Body Handling
- JSON data using `json()` method
- Stream data for large responses
- Form data when needed

### Response Handling
- Access response data: `$response->json()`
- Check status: `$response->status()`
- Validate success: `$response->successful()`
- Get headers: `$response->headers()`

### Debugging Tools
- Request/Response logging
- Debug mode for development
- Response recording for tests

### Best Practices
1. **Authentication**
   - Store tokens securely
   - Refresh tokens when needed
   - Handle auth failures gracefully

2. **Error Handling**
   - Use specific exception types
   - Log meaningful error messages
   - Implement retry logic for transient failures

3. **Testing**
   - Mock responses for predictable tests
   - Test error scenarios
   - Validate request data

4. **Performance**
   - Use response caching
   - Implement request pooling for bulk operations
   - Monitor response times

## Error Handling
- Connection timeouts
- Authentication failures
- Invalid responses
- Rate limiting

## Caching Strategy
- Cache health status
- Cache system metrics
- Cache system info
- Implement TTL for cached data

## Security Considerations
- Secure token storage
- SSL verification
- Credential management
