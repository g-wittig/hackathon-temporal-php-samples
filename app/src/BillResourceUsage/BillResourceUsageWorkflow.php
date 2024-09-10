<?php

declare(strict_types=1);

namespace Temporal\Samples\BillResourceUsage;

use Carbon\CarbonInterval;
use Temporal\Activity\ActivityOptions;
use Temporal\Common\RetryOptions;
use Temporal\Workflow;

class BillResourceUsageWorkflow implements BillResourceUsageWorkflowInterface
{

    public function __construct()
    {
        $this->activities = Workflow::newActivityStub(
            BillResourceUsageWorkflowInterface::class,
            ActivityOptions::new()
                ->withStartToCloseTimeout(CarbonInterval::hour(1))
                // disable retries for example to run faster
                ->withRetryOptions(RetryOptions::new()->withMaximumAttempts(1))
        );
    }

    public function billResourceUsage(BillResourceUsageArgs $args)
    {
        // Activities to complete workflow:
        // RecordIfFirstTimeUsingResource
        // ListProductsMeteredByResource
        // for each product:
        //   AddOneTimePurchaseToInvoice -- child workflow?
        //     ...
        //   CreateRecurringSubscriptionForProductBilledOnFirstUsage -- child workflow?
        //     ...
    }

    public function bookTrip(string $name)
    {
        $saga = new Workflow\Saga();

        // Configure SAGA to run compensation activities in parallel
        $saga->setParallelCompensation(true);

        try {
            $carReservationID = yield $this->activities->reserveCar($name);
            $saga->addCompensation(fn() => yield $this->activities->cancelCar($carReservationID, $name));

            $hotelReservationID = yield $this->activities->bookHotel($name);
            $saga->addCompensation(fn() => yield $this->activities->cancelHotel($hotelReservationID, $name));

            $flightReservationID = yield $this->activities->bookFlight($name);
            $saga->addCompensation(fn() => yield $this->activities->cancelFlight($flightReservationID, $name));

            return [
                'car' => $carReservationID,
                'hotel' => $hotelReservationID,
                'flight' => $flightReservationID
            ];
        } catch (\Throwable $e) {
            yield $saga->compensate();
            throw $e;
        }
    }
}