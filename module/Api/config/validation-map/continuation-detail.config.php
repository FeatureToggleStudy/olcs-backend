<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\ContinuationDetail\CanAccessContinuationDetailWithId;

return [
    QueryHandler\ContinuationDetail\LicenceChecklist::class => CanAccessContinuationDetailWithId::class,
    QueryHandler\ContinuationDetail\Review::class => CanAccessContinuationDetailWithId::class,
    QueryHandler\ContinuationDetail\Get::class => CanAccessContinuationDetailWithId::class,
    CommandHandler\ContinuationDetail\UpdateFinances::class => CanAccessContinuationDetailWithId::class,
    CommandHandler\ContinuationDetail\UpdateInsufficientFinances::class => CanAccessContinuationDetailWithId::class,
];
