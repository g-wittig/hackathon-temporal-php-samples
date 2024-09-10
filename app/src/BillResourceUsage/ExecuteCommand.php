<?php

declare(strict_types=1);

namespace Temporal\Samples\BillResourceUsage;

use Carbon\CarbonInterval;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Temporal\Client\WorkflowOptions;
use Temporal\SampleUtils\Command;

class ExecuteCommand extends Command
{
    protected const NAME = 'bill-resource-usage';
    protected const DESCRIPTION = 'Execute BillResourceUsage\BillResourceUsageWorkflow';

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $workflow = $this->workflowClient->newWorkflowStub(
            BillResourceUsageWorkflowInterface::class,
            WorkflowOptions::new()->withWorkflowExecutionTimeout(CarbonInterval::minute())
        );

        $output->writeln("Starting <comment>BillResourceUsageWorkflow</comment>... ");

        $args = new BillResourceUsageArgs(384, 1, 'payroll_next_day_pay_employee', 10);
        $run = $this->workflowClient->start($workflow, $args);

        $output->writeln(
            sprintf(
                'Started: WorkflowID=<fg=magenta>%s</fg=magenta>, RunID=<fg=magenta>%s</fg=magenta>',
                $run->getExecution()->getID(),
                $run->getExecution()->getRunID(),
            )
        );

        $output->writeln(sprintf("Result:\n<info>%s</info>", print_r($run->getResult(), true)));

        return self::SUCCESS;
    }
}