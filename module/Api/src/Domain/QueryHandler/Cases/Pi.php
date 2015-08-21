<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Util\SlaCalculatorInterface;
use Dvsa\Olcs\Api\Entity\System\Sla as SlaEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * PI
 */
final class Pi extends AbstractQueryHandler
{
    protected $repoServiceName = 'Pi';

    protected $extraRepos = ['Licence', 'Sla'];

    /**
     * @var SlaCalculatorInterface
     */
    private $slaService;

    public function handleQuery(QueryInterface $query)
    {
        $pi = $this->getRepo()->fetchUsingCase($query, Query::HYDRATE_ARRAY);

        $this->extractHearingDate($pi);

        $pi['canClose'] = $this->canClose($pi);
        $pi['isClosed'] = $this->isClosed($pi);
        $pi['canReopen'] = $this->canReopen($pi);

        /** @TODO change me to use queries */
        if (!empty($pi['case']['transportManager'])) {
            //no licence for TM cases so using English TA
            $trafficArea = $this->getRepo()->getReference(
                TrafficAreaEntity::class,
                TrafficAreaEntity::SE_MET_TRAFFIC_AREA_CODE
            );
        } else {
            /** @var LicenceEntity $licence */
            $licence = $this->getRepo('Licence')->fetchByCaseId($query->getId(), Query::HYDRATE_OBJECT);
            $trafficArea = $licence->getTrafficArea();
        }

        $slas = $this->getRepo('Sla')->fetchByCategory('pi', Query::HYDRATE_OBJECT);

        foreach ($slas as $sla) {
            /** @var SlaEntity $sla*/
            if (isset($pi[$sla->getCompareTo()]) && !empty($pi[$sla->getCompareTo()])) {

                $dateTime = date_create($pi[$sla->getCompareTo()]);

                if ($dateTime && $sla->appliesTo($dateTime)) {
                    $targetDate = $this->slaService->applySla($dateTime, $sla, $trafficArea);
                    $pi[$sla->getField() . 'Target'] = $targetDate->format('Y-m-d');
                }
            }

            if (!isset($pi[$sla->getField() . 'Target'])) {
                $pi[$sla->getField() . 'Target'] = '';
            }
        }

        return $pi;
    }

    protected function canClose($data)
    {
        if (isset($data['piHearings'][0])) {
            if (!empty($data['piHearings'][0]['cancelledDate'])) {
                return !$this->isClosed($data);
            }
        }

        if (isset($data['writtenOutcome']['id'])) {
            switch($data['writtenOutcome']['id']) {
                case 'piwo_none':
                    return !$this->isClosed($data);
                case 'piwo_reason':
                    if (empty($data['tcWrittenReasonDate']) ||
                        empty($data['writtenReasonLetterDate'])
                    ) {
                        return false;
                    }
                    return !$this->isClosed($data);
                case 'piwo_decision':
                    if (empty($data['tcWrittenDecisionDate']) ||
                        empty($data['decisionLetterSentDate'])
                    ) {
                        return false;
                    }
                    return !$this->isClosed($data);
            }
        }
        return false;
    }

    /**
     * Is this entity closed
     * @param array $data
     * @return bool
     */
    public function isClosed($data)
    {
        return (bool) isset($data['closedDate']);
    }

    /**
     * Can this entity be reopened
     * @param array $data
     * @return bool
     */
    public function canReopen($data)
    {
        return $this->isClosed($data);
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

        return $this;
    }
}
