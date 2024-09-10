<?php

/**
 * This file is part of Temporal package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Temporal\SampleUtils;

use OpenTelemetry\API\Signals;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\Contrib\Grpc\GrpcTransportFactory;
use OpenTelemetry\Contrib\Otlp\OtlpUtil;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SemConv\ResourceAttributes;
use Temporal\OpenTelemetry\Tracer;
use OpenTelemetry\SDK\Trace\SpanProcessorFactory;
use OpenTelemetry\SDK\Trace\TracerProvider;

final class TracerFactory
{
    public static function create(string $serviceName): Tracer
    {
        $endpoint = getenv('OTEL_COLLECTOR_ENDPOINT');
        if (empty($endpoint)) {
            $endpoint = 'http://collector:4319';
        }

        $transport = (new GrpcTransportFactory())->create($endpoint . OtlpUtil::method(Signals::TRACE));
        $spanProcessor = (new SpanProcessorFactory())->create(new SpanExporter($transport));

        $defaultResource = ResourceInfoFactory::defaultResource();
        $resource = ResourceInfo::create(Attributes::create([
            ResourceAttributes::SERVICE_NAME => $serviceName,
        ]))->merge($defaultResource);

        return new Tracer(
            (new TracerProvider(spanProcessors: $spanProcessor, resource: $resource))->getTracer('Temporal Samples'),
            TraceContextPropagator::getInstance()
        );
    }
}
