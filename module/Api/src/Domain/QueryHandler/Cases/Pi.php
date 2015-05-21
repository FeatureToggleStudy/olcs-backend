<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Util\SlaCalculatorInterface;
use Dvsa\Olcs\Api\Entity\System\Sla;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Domain\Repository\Licence;
use Dvsa\Olcs\Api\Domain\Repository\Sla as SlaRepo;

/**
 * PI
 */
final class Pi extends AbstractQueryHandler
{
    protected $repoServiceName = 'Pi';

    /**
     * @var SlaCalculatorInterface
     */
    private $slaService;

    /**
     * @var Licence
     */
    private $licenceRepo;

    /**
     * @var SlaRepo
     */
    private $slaRepo;

    public function handleQuery(QueryInterface $query)
    {
        $case = $this->getRepo()->fetchUsingId($query);

        $this->extractHearingDate($case['pi']);

        /** @TODO change me to use queries*/
        $licence = $this->licenceRepo->fetchByCaseId($query->getId(), Query::HYDRATE_OBJECT);

        $slas = $this->slaRepo->fetchByCategory('pi', Query::HYDRATE_OBJECT);

        foreach ($slas as $sla) {
            /** @var Sla $sla*/
            if (isset($case['pi'][$sla->getCompareTo()]) && !empty($case['pi'][$sla->getCompareTo()])) {

                $dateTime = date_create($case['pi'][$sla->getCompareTo()]);

                if ($dateTime && $sla->appliesTo($dateTime)) {
                    $targetDate = $this->slaService->applySla($dateTime, $sla, $licence->getTrafficArea());
                    $case['pi'][$sla->getField() . 'Target'] = $targetDate->format('Y-m-d');
                }
            }

            if (!isset($case['pi'][$sla->getField() . 'Target'])) {
                $case['pi'][$sla->getField() . 'Target'] = '';
            }
        }

        return $case;
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function extractHearingDate(&$data)
    {
        if (isset($data['piHearings']) && is_array($data['piHearings']) && count($data['piHearings']) > 0) {
            $hearing = end($data['piHearings']);
            if ($hearing['isAdjourned'] != 'Y' && $hearing['isCancelled'] != 'Y') {
                $data['hearingDate'] = $hearing['hearingDate'];
            }
        }
        if (!isset($data['hearingDate'])) {
            $data['hearingDate'] = '';
        }
    }

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        parent::createService($serviceLocator); // TODO: Change the autogenerated stub
        $serviceLocator = $serviceLocator->getServiceLocator();

        $this->slaService = $serviceLocator->get(SlaCalculatorInterface::class);
        $this->slaRepo = $serviceLocator->get('RepositoryServiceManager')->get('Sla');
        $this->licenceRepo = $serviceLocator->get('RepositoryServiceManager')->get('Licence');

        return $this;
    }
}
