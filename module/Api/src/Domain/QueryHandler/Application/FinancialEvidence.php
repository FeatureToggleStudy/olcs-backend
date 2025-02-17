<?php

/**
 * Financial Evidence
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;

/**
 * Financial Evidence
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FinancialEvidence extends AbstractQueryHandler
{
    protected $repoServiceName = 'Application';

    /**
     * @var \Dvsa\Olcs\Api\Service\FinancialStandingHelperService $helper
     */
    protected $helper;

    public function handleQuery(QueryInterface $query)
    {
        $applicationRepo = $this->getRepo();
        $application = $applicationRepo->fetchUsingId($query, Query::HYDRATE_OBJECT);

        // add documents if not READ-ONLY user
        $financialDocuments = null;
        if (!$this->isReadOnlyInternalUser()) {
            $financialDocuments = $application->getApplicationDocuments(
                $applicationRepo->getCategoryReference(Category::CATEGORY_APPLICATION),
                $applicationRepo->getSubCategoryReference(SubCategory::DOC_SUB_CATEGORY_FINANCIAL_EVIDENCE_DIGITAL)
            );
            $financialDocuments = $this->resultList($financialDocuments);
        }


        // add calculated finance data
        $financialEvidence = $this->getTotalNumberOfAuthorisedVehicles($application);
        $financialEvidence['requiredFinance'] = $this->getRequiredFinance($application);
        $financialEvidence = array_merge(
            $financialEvidence,
            $this->helper->getRatesForView($application->getGoodsOrPsv()->getId())
        );

        return $this->result(
            $application,
            [
                'licence',
            ],
            [
                'documents' => $financialDocuments,
                'financialEvidence' => $financialEvidence
            ]
        );
    }

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        parent::createService($serviceLocator);
        $this->helper = $serviceLocator->getServiceLocator()->get('FinancialStandingHelperService');
        return $this;
    }

    protected function getRequiredFinance($application)
    {
        // add the application count
        $type = null;
        if ($application->getLicenceType()) {
            $type = $application->getLicenceType()->getId();
        }
        $auths[] = [
            'type' => $type,
            'count' => $application->getTotAuthVehicles(),
            'category' => $application->getGoodsOrPsv()->getId(),
        ];

        // add the counts for each licence
        foreach ($application->getOtherActiveLicencesForOrganisation() as $licence) {
            $auths[] = [
                'type' => $licence->getLicenceType()->getId(),
                'count' => $licence->getTotAuthVehicles(),
                'category' => $licence->getGoodsOrPsv()->getId(),
            ];
        }

        // add the counts for each other application
        $applications = $this->getOtherNewApplications($application);
        foreach ($applications as $app) {
            if (!is_null($app->getGoodsOrPsv())) {
                $type = null;
                if ($app->getLicenceType()) {
                    $type = $app->getLicenceType()->getId();
                }
                $auths[] = [
                    'type' => $type,
                    'count' => $app->getTotAuthVehicles(),
                    'category' => $app->getGoodsOrPsv()->getId(),
                ];
            }
        }

        return $this->helper->getFinanceCalculation($auths);
    }

    protected function getOtherNewApplications($application)
    {
        $organisation = $application->getLicence()->getOrganisation();

        $applications = $this->getRepo()->fetchActiveForOrganisation($organisation->getId());

        return array_filter(
            $applications,
            function ($app) use ($application) {
                if ($app->isVariation()) {
                    // exclude variations
                    return false;
                }
                // exclude the current application so we don't double-count
                return $app->getId() !== $application->getId();
            }
        );
    }

    /**
     * Get the total vehicle authority which includes:
     *
     *  The vehicles on this application
     *  PLUS the vehicles across other licence records with a status of:
     *   Valid
     *   Suspended
     *   Curtailed
     *  PLUS the vehicles across other new applications with a status of:
     *   Under consideration
     *   Granted
     *
     * @param ApplicationEntity $application
     *
     * @return array containing total vehicles for application, other licences and other application
     */
    public function getTotalNumberOfAuthorisedVehicles($application)
    {
        // get the total vehicle authorisation for this application
        $appVehicles = $application->getTotAuthVehicles();

        // get the total vehicle authorisation for other licences
        $otherLicenceVehicles = 0;
        foreach ($application->getOtherActiveLicencesForOrganisation() as $licence) {
            $otherLicenceVehicles += (int)$licence->getTotAuthVehicles();
        }

        // get the total vehicle authorisation for other applications
        // that are 'under consideration' or 'granted'
        $otherApplicationVehicles = 0;
        $applications = $this->getOtherNewApplications($application);
        foreach ($applications as $application) {
            $otherApplicationVehicles += (int)$application->getTotAuthVehicles();
        }

        return [
            'applicationVehicles' => $appVehicles,
            'otherLicenceVehicles' => $otherLicenceVehicles,
            'otherApplicationVehicles' => $otherApplicationVehicles,
        ];
    }
}
