<?php

namespace Dvsa\Olcs\Api\Domain\Command\Email;

use Dvsa\Olcs\Api\Domain\Command\AbstractIdOnlyCommand;

/**
 * Send email to notify ECMT permits are being issued
 */
final class SendEcmtIssued extends AbstractIdOnlyCommand
{
}
