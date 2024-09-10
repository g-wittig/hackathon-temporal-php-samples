<?php

namespace Temporal\Samples\BillResourceUsage;

class BillResourceUsageArgs
{
    public function __construct(
        public int $company_id,
        public int $location_id,
        public string $metered_resource,
        public int $quantity
    ) {
    }
}