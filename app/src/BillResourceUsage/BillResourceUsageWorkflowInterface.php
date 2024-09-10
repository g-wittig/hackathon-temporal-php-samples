<?php

declare(strict_types=1);

namespace Temporal\Samples\BillResourceUsage;

use Temporal\Workflow\WorkflowInterface;
use Temporal\Workflow\WorkflowMethod;

#[WorkflowInterface]
interface BillResourceUsageWorkflowInterface
{
    #[WorkflowMethod("BillResourceUsage")]
    public function billResourceUsage(BillResourceUsageArgs $args);
}