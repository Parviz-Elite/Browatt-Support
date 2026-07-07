<?php

namespace Browatt\MehrsoftIntegration\Client;

use SoapClient;
use SoapFault;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Browatt\MehrsoftIntegration\Contracts\MehrsoftClient;
use Browatt\MehrsoftIntegration\Exceptions\MehrsoftException;

class SoapMehrsoftClient implements MehrsoftClient
{
    private ?SoapClient $client;

    private ?string $sessionId = null;

    public function __construct(?SoapClient $client = null)
    {
        $this->client = $client;
    }

    /**
     * Get MehrSoft product status by product serial.
     *
     * @return array<string, mixed>
     */
    public function getProductStatusBySerial(string $serial): array
    {
        $response = $this->request('AfterSales_GetProductStatusBySerial', [
            'jsonParams' => $this->encodeJsonParams([
                'Serial' => $serial,
            ]),
        ]);

        return is_array($response) ? $response : [];
    }

    /**
     * Get warranty period in months for a warranty type and product code.
     */
    public function getWarrantyMonths(string $warrantyType, string $goodFullCode): ?int
    {
        $response = $this->request('AfterSales_GetWarrantyMonths', [
            'jsonParams' => $this->encodeJsonParams([
                'WarrantyType' => $warrantyType,
                'GoodFullCode' => $goodFullCode,
            ]),
        ]);

        if (is_numeric($response)) {
            return (int) $response;
        }

        if (is_array($response)) {
            $value = Arr::first($response, static fn ($item) => is_numeric($item));

            return is_numeric($value) ? (int) $value : null;
        }

        return null;
    }

    /**
     * Get MehrSoft warranty settings.
     *
     * @return array<int|string, mixed>
     */
    public function getWarrantySettings(): array
    {
        $response = $this->request('AfterSales_GetWarrantySettings');

        return is_array($response) ? $response : [];
    }

    /**
     * Get accounting states and cities.
     *
     * @return array<int|string, mixed>
     */
    public function getAccCities(): array
    {
        $response = $this->request('GetAccCities');

        return is_array($response) ? $response : [];
    }

    /**
     * Save an after-sales document.
     *
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function saveAfterSales(array $payload): array
    {
        $response = $this->request('AfterSales_Save', [
            'jsonParams' => $this->encodeJsonParams($payload),
        ]);

        if (is_array($response)) {
            return $response;
        }

        return [
            'result' => $response,
        ];
    }

    /**
     * Close the current MehrSoft session.
     */
    public function logout(): void
    {
        if ($this->sessionId === null) {
            return;
        }

        $this->request('Logout');
        $this->sessionId = null;
    }

    /**
     * Create the SOAP client from module configuration.
     */
    private function makeSoapClient(): SoapClient
    {
        if (!extension_loaded('soap')) {
            throw new MehrsoftException('PHP SOAP extension is required for MehrSoft integration.');
        }

        $wsdlUrl = (string) config('mehrsoftintegration.wsdl_url');

        if ($wsdlUrl === '') {
            throw new MehrsoftException('MehrSoft WSDL URL is not configured.');
        }

        return new SoapClient($wsdlUrl, (array) config('mehrsoftintegration.soap', []));
    }

    /**
     * Perform a SOAP request and parse MehrSoft's common response envelope.
     *
     * @param array<string, mixed> $payload
     */
    private function request(string $method, array $payload = [], bool $withSession = true): mixed
    {
        if ($withSession) {
            $payload['sessionId'] = $this->getSessionId();
        }

        try {
            $response = $this->soapClient()->__soapCall($method, [$payload]);
        } catch (SoapFault $exception) {
            $this->logRequest($method, $payload, null, $exception->getMessage());

            throw new MehrsoftException($exception->getMessage(), (int) $exception->getCode(), $exception);
        }

        $response = $this->normalizeSoapResponse($response);
        $this->logRequest($method, $payload, $response);

        return $this->parseResponse($response, $method);
    }

    /**
     * Login lazily and cache the session id for this client instance.
     */
    private function getSessionId(): string
    {
        if ($this->sessionId !== null) {
            return $this->sessionId;
        }

        $financialUnit = (int) config('mehrsoftintegration.financial_unit');
        $username = (string) config('mehrsoftintegration.username');
        $password = (string) config('mehrsoftintegration.password');

        if ($financialUnit === 0 || $username === '' || $password === '') {
            throw new MehrsoftException('MehrSoft credentials are not configured.');
        }

        $response = $this->request('Login', [
            'fuCode' => $financialUnit,
            'userName' => $username,
            'password' => $password,
        ], false);

        if (!is_array($response)) {
            throw new MehrsoftException('Invalid MehrSoft login response.');
        }

        $sessionId = (string) Arr::get($response, 'SessionID', '');

        if ($sessionId === '') {
            throw new MehrsoftException('Empty MehrSoft session id.');
        }

        return $this->sessionId = $sessionId;
    }

    /**
     * Create the SOAP client only when an actual MehrSoft request is made.
     */
    private function soapClient(): SoapClient
    {
        return $this->client ??= $this->makeSoapClient();
    }

    /**
     * Encode jsonParams with Unicode preserved for Persian customer data.
     *
     * @param array<string, mixed> $payload
     */
    private function encodeJsonParams(array $payload): string
    {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            throw new MehrsoftException('Invalid MehrSoft JSON payload.');
        }

        return $json;
    }

    /**
     * Parse MehrSoft responses shaped as "status:content".
     */
    private function parseResponse(mixed $response, string $method): mixed
    {
        if (!is_string($response)) {
            return $response;
        }

        if ($response === '') {
            throw new MehrsoftException('Empty MehrSoft response.');
        }

        if (!str_contains($response, ':')) {
            return $this->decodeContent($response);
        }

        [$status, $content] = explode(':', $response, 2);

        if ((int) $status !== 1) {
            throw new MehrsoftException(trim($content));
        }

        return $this->decodeContent($content, $method);
    }

    /**
     * Decode JSON content when possible, otherwise return the original text.
     */
    private function decodeContent(string $content, string $method = ''): mixed
    {
        $decoded = json_decode($content, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        if ($method === 'Login') {
            Log::warning('MehrSoft login response JSON decode failed.', [
                'jsonError' => json_last_error_msg(),
            ]);
        }

        return $content;
    }

    /**
     * Reduce SOAP wrapper objects to their actual response value.
     */
    private function normalizeSoapResponse(mixed $response): mixed
    {
        if (!is_object($response)) {
            return $response;
        }

        $values = array_values(get_object_vars($response));

        return $values[0] ?? null;
    }

    /**
     * Log sanitized SOAP request/response previews.
     *
     * @param array<string, mixed> $payload
     */
    private function logRequest(string $method, array $payload, mixed $response = null, ?string $error = null): void
    {
        if (!config('mehrsoftintegration.logging.enabled', true)) {
            return;
        }

        Log::info('MehrSoft SOAP request handled.', [
            'method' => $method,
            'payload' => $this->sanitizePayload($payload),
            'responsePreview' => $method === 'Login' ? '[redacted login response]' : $this->previewValue($response),
            'error' => $error,
        ]);
    }

    /**
     * Remove sensitive values before logging.
     *
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function sanitizePayload(array $payload): array
    {
        foreach (['password', 'sessionId'] as $key) {
            if (array_key_exists($key, $payload)) {
                $payload[$key] = '[redacted]';
            }
        }

        return $payload;
    }

    /**
     * Build a compact preview for logs.
     */
    private function previewValue(mixed $value): string
    {
        $limit = max(100, (int) config('mehrsoftintegration.logging.response_preview_limit', 2000));

        if (is_scalar($value) || $value === null) {
            return Str::limit((string) $value, $limit);
        }

        return Str::limit(json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: get_debug_type($value), $limit);
    }
}
