<?php

/**
 * Expire All For Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Api\Domain\Repository\Query\CommunityLicence;

use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic;
use Dvsa\Olcs\Api\Domain\Repository\Query\AbstractRawQuery;

/**
 * Expire All For Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ExpireAllForLicence extends AbstractRawQuery
{
    protected $templateMap = [
        'cl' => CommunityLic::class
    ];

    protected $queryTemplate = 'UPDATE {cl}
      SET {cl.status} = :status, {cl.expiredDate} = :expiredDate
      WHERE {cl.expiredDate} IS NULL AND {cl.licence} = :licence';

    /**
     * {@inheritdoc}
     */
    protected function getParams()
    {
        return [
            'status' => CommunityLic::STATUS_EXPIRED,
            'expiredDate' => date('Y-m-d H:i:s')
        ];
    }
}
