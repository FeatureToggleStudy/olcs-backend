<?php
/**
 * EcmtPermitStartDate
 */
namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\EcmtPermitBundle as Qry;

/**
 * EcmtPermitStartDate
 */
class EcmtPermitStartDate extends SingleValueAbstract
{
    const FORMATTER = 'Date';
    const FIELD  = 'inForceDate';
    const SRCH_FLD_KEY = 'id';
    const SRCH_VAL_KEY = 'ecmtPermit';
    const QUERY_CLASS = Qry::class;
}
