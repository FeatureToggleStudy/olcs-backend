<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Domain\ToggleRequiredInterface;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;

/**
 * Get a list of all IRHP Applications
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
class GetList extends AbstractListQueryHandler implements ToggleRequiredInterface
{
    use ToggleAwareTrait;

    protected $toggleConfig = [FeatureToggle::BACKEND_ECMT];
    protected $repoServiceName = 'IrhpApplication';
    protected $bundle = ['irhpPermitType' => ['name'], 'irhpPermitApplications'];
}
