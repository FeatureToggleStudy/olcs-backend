<?php

/**
 * Application Review
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview;

use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion;
use Zend\Filter\Word\UnderscoreToCamelCase;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\View\Model\ViewModel;

/**
 * Application Review
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Generator implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    protected $defaultBundle = [
        'licence' => [
            'organisation' => []
        ]
    ];

    protected $sharedBundles = [
        'transport_managers' => [
            'transportManagers' => [
                'transportManager' => [
                    'homeCd' => [
                        'person' => [
                            'title'
                        ]
                    ]
                ]
            ]
        ],
        'operating_centres' => [
            'licence' => [
                'trafficArea'
            ],
            'operatingCentres' => [
                'application',
                'operatingCentre' => [
                    'address',
                    'adDocuments' => [
                        'application'
                    ]
                ]
            ]
        ],
        'vehicles' => [
            'licenceVehicles' => [
                'vehicle'
            ]
        ],
        'vehicles_psv' => [
            'licenceVehicles' => [
                'vehicle' => [
                    'psvType'
                ]
            ]
        ],
        'convictions_penalties' => [
            'previousConvictions' => [
                'title'
            ]
        ],
        'licence_history' => [
            'otherLicences' => [
                'previousLicenceType'
            ]
        ],
        'financial_history' => [
            'documents' => [
                'category',
                'subCategory'
            ]
        ],
        'conditions_undertakings' => [
            'conditionUndertakings' => [
                'conditionType',
                'attachedTo',
                'operatingCentre' => [
                    'address'
                ]
            ]
        ]
    ];

    protected $applicationBundles = [
        'business_type' => [
            'licence' => [
                'organisation' => [
                    'type'
                ]
            ]
        ],
        'business_details' => [
            'licence' => [
                'companySubsidiaries',
                'organisation' => [
                    'type',
                    'natureOfBusinesses',
                    'contactDetails' => [
                        'address'
                    ]
                ],
                'tradingNames'
            ]
        ],
        'safety' => [
            'licence' => [
                'workshops' => [
                    'contactDetails' => [
                        'address'
                    ]
                ],
                'tachographIns'
            ]
        ],
        'addresses' => [
            'licence' => [
                'correspondenceCd' => [
                    'address',
                    'phoneContacts' => [
                        'phoneContactType'
                    ]
                ],
                'establishmentCd' => [
                    'address'
                ]
            ]
        ],
        'taxi_phv' => [
            'licence' => [
                'trafficArea',
                'privateHireLicences' => [
                    'contactDetails' => [
                        'address'
                    ]
                ]
            ]
        ],
        'people' => [
            'licence' => [
                'organisation' => [
                    'type',
                    'organisationPersons' => [
                        'person' => [
                            'title'
                        ]
                    ]
                ]
            ],
            'applicationOrganisationPersons' => [
                'originalPerson',
                'person' => [
                    'title'
                ]
            ]
        ],
        'vehicles_declarations' => [
            'licence' => [
                'trafficArea'
            ]
        ]
    ];

    protected $variationBundles = [
        'type_of_licence' => [
            'licence' => [
                'licenceType'
            ]
        ],
        'people' => [
            'licence' => [
                'organisation' => [
                    'type'
                ]
            ],
            'applicationOrganisationPersons' => [
                'person' => [
                    'title'
                ]
            ]
        ],
        'conditions_undertakings' => [
            'conditionUndertakings' => [
                'licConditionVariation'
            ]
        ]
    ];

    protected $ignoredApplicationSections = [
        'community_licences'
    ];

    protected $ignoredVariationSections = [
        'community_licences'
    ];

    protected $lva;

    public function __construct()
    {
        $notRemovedCriteria = Criteria::create();
        $notRemovedCriteria->andWhere(
            $notRemovedCriteria->expr()->isNull('removalDate')
        );

        $this->sharedBundles['vehicles']['licenceVehicles']['criteria'] = $notRemovedCriteria;
        $this->sharedBundles['vehicles_psv']['licenceVehicles']['criteria'] = $notRemovedCriteria;
    }

    public function generate(Application $application)
    {
        $sections = $this->getServiceLocator()->get('SectionAccessService')->getAccessibleSections($application);
        $sections = array_keys($sections);

        if ($application->isVariation()) {

            $this->lva = 'variation';
            $sections = $this->filterVariationSections($sections, $application->getApplicationCompletion());

            $bundle = $this->getReviewDataBundleForVariation($sections);
        } else {
            $this->lva = 'application';
            $sections = $this->filterApplicationSections($sections);

            $bundle = $this->getReviewDataBundleForApplication($sections);
        }

        $result = new Result(
            $application,
            $bundle,
            [
                'sections' => $sections,
                'isGoods' => $application->isGoods(),
                'isSpecialRestricted' => $application->isSpecialRestricted()
            ]
        );

        $data = $result->serialize();

        $config = $this->buildReadonlyConfigForSections($data['sections'], $data);

        // Generate readonly markup
        return $this->generateReadonly($config);
    }

    protected function generateReadonly(array $config)
    {
        $model = new ViewModel($config);
        $model->setTerminal(true);
        $model->setTemplate('layout/review');

        $renderer = $this->getServiceLocator()->get('ViewRenderer');
        return $renderer->render($model);
    }

    protected function buildReadonlyConfigForSections($sections, $reviewData)
    {
        $entity = ucfirst($this->lva);

        $filter = new UnderscoreToCamelCase();

        $sectionConfig = [];

        foreach ($sections as $section) {
            $serviceName = 'Review\\' . $entity . ucfirst($filter->filter($section));

            $config = null;

            // @NOTE this check is in place while we implement each section
            // eventually we should be able to remove the if
            if ($this->getServiceLocator()->has($serviceName)) {
                $service = $this->getServiceLocator()->get($serviceName);
                $config = $service->getConfigFromData($reviewData);
            }

            $sectionConfig[] = [
                'header' => 'review-' . $section,
                'config' => $config
            ];
        }

        return [
            'reviewTitle' => $this->getTitle($reviewData),
            'subTitle' => $this->getSubTitle($reviewData),
            'sections' => $sectionConfig
        ];
    }

    protected function getSubTitle($data)
    {
        return sprintf('%s %s/%s', $data['licence']['organisation']['name'], $data['licence']['licNo'], $data['id']);
    }

    protected function getTitle($data)
    {
        return sprintf(
            '%s-review-title-%s%s',
            $this->lva,
            $data['isGoods'] ? 'gv' : 'psv',
            $this->isNewPsvSpecialRestricted($data) ? '-sr' : ''
        );
    }

    protected function isNewPsvSpecialRestricted($data)
    {
        return $this->lva === 'application' && !$data['isGoods'] && $data['isSpecialRestricted'];
    }

    protected function filterVariationSections($sections, ApplicationCompletion $completion)
    {
        $sections = array_values(array_diff($sections, $this->ignoredVariationSections));

        $filter = new UnderscoreToCamelCase();

        foreach ($sections as $key => $section) {

            $getter = 'get' . ucfirst($filter->filter($section)) . 'Status';

            if ($completion->$getter() !== Application::VARIATION_STATUS_UPDATED) {
                unset($sections[$key]);
            }
        }

        return $sections;
    }

    protected function filterApplicationSections($sections)
    {
        return array_values(array_diff($sections, $this->ignoredApplicationSections));
    }

    protected function getReviewDataBundleForApplication(array $sections = [])
    {
        return $this->getReviewBundle($sections, 'application');
    }

    /**
     * Grab all of the review for a variation
     *
     * @param array $sections
     *
     * @return array
     */
    protected function getReviewDataBundleForVariation(array $sections = array())
    {
        return $this->getReviewBundle($sections, 'variation');
    }

    /**
     * Dynamically build the review bundle
     *
     * @param array $sections
     * @param string $lva
     * @return array
     */
    protected function getReviewBundle($sections, $lva)
    {
        $bundle = $this->defaultBundle;

        foreach ($sections as $section) {

            if (isset($this->sharedBundles[$section])) {
                $bundle = array_merge_recursive($bundle, $this->sharedBundles[$section]);
            }

            if (isset($this->{$lva . 'Bundles'}[$section])) {
                $bundle = array_merge_recursive($bundle, $this->{$lva . 'Bundles'}[$section]);
            }
        }

        return $bundle;
    }
}
