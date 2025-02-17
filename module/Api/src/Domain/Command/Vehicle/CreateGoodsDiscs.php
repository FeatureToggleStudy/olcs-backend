<?php

/**
 * Create Goods Discs
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Api\Domain\Command\Vehicle;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Create Goods Discs
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CreateGoodsDiscs extends AbstractCommand
{
    /**
     * @Transfer\ArrayInput
     * @Transfer\Filter({"name":"Zend\Filter\Digits"})
     * @Transfer\Validator({"name":"Zend\Validator\Digits"})
     * @Transfer\Validator({"name":"Zend\Validator\GreaterThan", "options": {"min": 0}})
     */
    protected $ids = [];

    protected $isCopy = 'N';

    /**
     * Get ids
     *
     * @return array
     */
    public function getIds()
    {
        return $this->ids;
    }

    /**
     * Get is copy
     *
     * @return string
     */
    public function getIsCopy()
    {
        return $this->isCopy;
    }
}
