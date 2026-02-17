<?php

namespace Preprio;

use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use RuntimeException;

class PreprClient
{
    public function sendRequest(Factory|PendingRequest $request, array $data): mixed
    {
        [$headers, $json] = $this->buildPayload($data);

        return $request
            ->acceptJson()
            ->timeout((int) config('prepr.timeout', 30))
            ->connectTimeout((int) config('prepr.connect_timeout', 10))
            ->withHeaders($headers)
            ->post((string) config('prepr.endpoint'), $json);
    }

    /**
     * @return array{0: array<string, mixed>, 1: array{query: ?string, variables: array<mixed>, query_file: ?string}}
     */
    public function buildPayload(array $data): array
    {
        return [
            $this->resolveHeaders($data),
            $this->resolveJsonPayload($data),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function resolveHeaders(array $data): array
    {
        $headers = array_merge(
            config('prepr.headers', []),
            data_get($data, 'headers', [])
        );

        $request = request();
        if ($request->hasHeader('CF-Connecting-IP')) {
            $headers['Prepr-Visitor-IP'] = $request->header('CF-Connecting-IP');
        } elseif ($request->hasHeader('x-real-ip')) {
            $headers['Prepr-Visitor-IP'] = $request->header('x-real-ip');
        }

        $segmentPreview = $request->input('prepr_preview_segment');
        $abPreview = $request->input('prepr_preview_ab');

        $segmentPreviewActive = is_string($segmentPreview) && trim($segmentPreview) !== '';
        $abPreviewActive = is_string($abPreview) && trim($abPreview) !== '';

        if ($segmentPreviewActive) {
            $headers['Prepr-Segments'] = $segmentPreview;
        }

        if ($abPreviewActive) {
            $headers['Prepr-ABtesting'] = $abPreview;
        }

        if ($segmentPreviewActive || $abPreviewActive) {
            unset($headers['Prepr-Customer-Id']);
        }

        return array_filter($headers, function (mixed $value): bool {
            if (is_null($value)) {
                return false;
            }

            if (is_string($value) && trim($value) === '') {
                return false;
            }

            return true;
        });
    }

    /**
     * @return array{query: ?string, variables: array<mixed>, query_file: ?string}
     */
    protected function resolveJsonPayload(array $data): array
    {
        $queryName = data_get($data, 'query');
        $json = [
            'query' => null,
            'variables' => data_get($data, 'variables', []),
            'query_file' => is_string($queryName) && $queryName !== '' ? $queryName . '.graphql' : null,
        ];

        if (is_string($queryName) && $queryName !== '') {
            $queryFile = app_path('Queries/' . $queryName . '.graphql');

            if (!is_file($queryFile)) {
                throw new RuntimeException("Prepr GraphQL query file not found: {$queryFile}");
            }

            $query = file_get_contents($queryFile) ?: '';

            $query = preg_replace('/\s+/', ' ', $query);
            $query = preg_replace('/\s*([{}():,])\s*/', '$1', $query);

            $json['query'] = $query;
        } elseif (is_string(data_get($data, 'raw-query')) && data_get($data, 'raw-query') !== '') {
            $json['query'] = data_get($data, 'raw-query');
        }

        return $json;
    }
}
