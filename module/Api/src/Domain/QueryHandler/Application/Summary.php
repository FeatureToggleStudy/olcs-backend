<?php

/**
 * Summary
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Api\Domain\QueryHandler\Application;

use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Utils\Helper\ValueHelper;

/**
 * Summary
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Summary extends AbstractQueryHandler
{
    const ACTION_PRINT_SIGN_RETURN = 'PRINT_SIGN_RETURN';
    const ACTION_SUPPLY_SUPPORTING_EVIDENCE = 'SUPPLY_SUPPORTING_EVIDENCE';
    const ACTION_APPROVE_TM = 'APPROVE_TM';

    const MISSING_EVIDENCE_OC = 'MISSING_EVIDENCE_OC';
    const MISSING_EVIDENCE_FINANCIAL = 'MISSING_EVIDENCE_FINANCIAL';

    protected $repoServiceName = 'Application';

    public function handleQuery(QueryInterface $query)
    {
        /** @var Entity\Application\Application $application */
        $application = $this->getRepo()->fetchUsingId($query);

        $actions = $this->determineActions($application);

        $bundle = [
            'licence',
            'status'
        ];

        if (array_key_exists(self::ACTION_APPROVE_TM, $actions)) {
            $bundle['transportManagers'] = [
                'tmApplicationStatus',
                'transportManager' => [
                    'homeCd' => [
                        'person'
                    ]
                ]
            ];

            if ($application->isVariation()) {
                $criteria = Criteria::create();
                $criteria->where($criteria->expr()->in('action', ['A', 'U']));

                $bundle['transportManagers']['criteria'] = $criteria;
            }
        }

        return $this->result($application, $bundle, ['actions' => $actions]);
    }

    protected function determineActions(Entity\Application\Application $application)
    {
        $actions = [];

        if ($this->needsToSign($application)) {
            $actions[self::ACTION_PRINT_SIGN_RETURN] = self::ACTION_PRINT_SIGN_RETURN;
        }

        $missingEvidence = $this->determineMissingEvidence($application);
        if (!empty($missingEvidence)) {
            $actions[self::ACTION_SUPPLY_SUPPORTING_EVIDENCE] = $missingEvidence;
        }

        if ($this->needsToApproveTms($application)) {
            $actions[self::ACTION_APPROVE_TM] = self::ACTION_APPROVE_TM;
        }

        return $actions;
    }

    protected function needsToSign(Entity\Application\Application $application)
    {
        if ($application->isVariation()) {
            return false;
        }

        if (ValueHelper::isOn($application->getAuthSignature())) {
            return false;
        }

        return true;
    }

    protected function determineMissingEvidence(Entity\Application\Application $application)
    {
        $evidence = [];

        if ($this->isMissingOcDocuments($application)) {
            $evidence[] = self::MISSING_EVIDENCE_OC;
        }

        if ($this->isMissingFinancialEvidence($application)) {
            $evidence[] = self::MISSING_EVIDENCE_FINANCIAL;
        }

        return $evidence;
    }

    protected function isMissingOcDocuments(Entity\Application\Application $application)
    {
        $ocs = $this->getAocsToCheck($application);

        // If there are no OCs then we can return false
        if ($ocs->isEmpty()) {
            return false;
        }

        $criteria = Criteria::create();
        $criteria->where($criteria->expr()->eq('application', $application));

        /** @var Entity\Application\ApplicationOperatingCentre $aoc */
        foreach ($ocs as $aoc) {
            // Grab all ad documents linked to the OC and the application
            $docs = $aoc->getOperatingCentre()->getAdDocuments()->matching($criteria);

            // If there are some, then we can skip this record
            if ($docs->isEmpty() === false) {
                continue;
            }

            if ($this->doesAocRequireDocs($application, $aoc)) {
                return true;
            }
        }

        return false;
    }

    protected function doesAocRequireDocs(
        Entity\Application\Application $application,
        Entity\Application\ApplicationOperatingCentre $aoc
    ) {
        // If we are not updating the OC, then we definitely need some docs, so we need to return here
        if ($aoc->getAction() !== 'U') {
            return true;
        }

        // If we are updating the record, we need to see if we have increased auth
        $loc = $application->getLicence()->getLocByOc($aoc->getOperatingCentre());

        return (
            $aoc->getNoOfVehiclesRequired() > $loc->getNoOfVehiclesRequired()
            || $aoc->getNoOfTrailersRequired() > $loc->getNoOfTrailersRequired()
        );
    }

    /**
     * @param Entity\Application\Application $application
     * @return \Doctrine\Common\Collections\ArrayCollection|\Doctrine\Common\Collections\Collection|static
     */
    protected function getAocsToCheck(Entity\Application\Application $application)
    {
        $ocs = $application->getOperatingCentres();

        // Filter to just add/edit records for variation
        if ($application->isVariation()) {
            $criteria = Criteria::create();
            $criteria->where($criteria->expr()->in('action', ['A', 'U']));

            $ocs = $ocs->matching($criteria);
        }

        return $ocs;
    }

    protected function isMissingFinancialEvidence(Entity\Application\Application $application)
    {
        $updated = Entity\Application\Application::VARIATION_STATUS_UPDATED;

        // If the application is a variation and the financial evidence section hasn't been updated, then we don't need
        // evidence
        if ($application->isVariation()
            && $application->getApplicationCompletion()->getFinancialEvidenceStatus() !== $updated) {
            return false;
        }

        $appCategory = $this->getRepo()->getCategoryReference(Entity\System\Category::CATEGORY_APPLICATION);
        $digitalCategory = $this->getRepo()->getSubCategoryReference(
            Entity\System\Category::DOC_SUB_CATEGORY_FINANCIAL_EVIDENCE_DIGITAL
        );

        $docs = $application->getApplicationDocuments($appCategory, $digitalCategory);

        if ($docs->isEmpty() === false) {
            return false;
        }

        $assistedDigitalCategory = $this->getRepo()->getSubCategoryReference(
            Entity\System\Category::DOC_SUB_CATEGORY_FINANCIAL_EVIDENCE_ASSISTED_DIGITAL
        );

        $docs = $application->getApplicationDocuments($appCategory, $assistedDigitalCategory);

        if ($docs->isEmpty() === false) {
            return false;
        }

        return true;
    }

    protected function needsToApproveTms(Entity\Application\Application $application)
    {
        $criteria = Criteria::create();
        $criteria->andWhere(
            $criteria->expr()->notIn(
                'tmApplicationStatus',
                [
                    Entity\Tm\TransportManagerApplication::STATUS_OPERATOR_SIGNED,
                    Entity\Tm\TransportManagerApplication::STATUS_RECEIVED
                ]
            )
        );

        if ($application->isVariation()) {
            $criteria->andWhere($criteria->expr()->in('action', ['A', 'U']));
        }

        $tms = $application->getTransportManagers()->matching($criteria);

        return $tms->isEmpty() === false;
    }
}
