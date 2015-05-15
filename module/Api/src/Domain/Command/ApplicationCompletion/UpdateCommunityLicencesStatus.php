<?php

/**
 * Update CommunityLicences Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Update CommunityLicences Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateCommunityLicencesStatus extends AbstractCommand
{
    protected $id;

    public function getId()
    {
        return $this->id;
    }
}
