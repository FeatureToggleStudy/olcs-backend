<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\NoValidationRequired;

return [
  QueryHandler\Permits\SectorsList::class => NoValidationRequired::class,
  QueryHandler\Permits\ConstrainedCountries::class => NoValidationRequired::class,
  QueryHandler\Permits\EcmtPermits::class => NoValidationRequired::class,
  CommandHandler\Permits\CreateEcmtPermits::class => NoValidationRequired::class,
  CommandHandler\Permits\CreateEcmtPermitCountryLink::class => NoValidationRequired::class,
];
