<?php

/**
 * Application Safety Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\ApplicationSafetyReviewService;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;

/**
 * Application Safety Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationSafetyReviewServiceTest extends MockeryTestCase
{
    protected $sut;
    protected $sm;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = new ApplicationSafetyReviewService();
        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * @dataProvider providerGetConfigFromData
     */
    public function testGetConfigFromData($data, $expected)
    {
        $mockTranslator = m::mock();
        $this->sm->setService('translator', $mockTranslator);

        $mockTranslator->shouldReceive('translate')
            ->andReturnUsing(
                function ($string) {
                    return $string . '-translated';
                }
            );

        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }

    public function providerGetConfigFromData()
    {
        return [
            'PSV' => [
                [
                    'safetyConfirmation' => 'Y',
                    'isGoods' => false,
                    'goodsOrPsv' => [
                        'id' => Licence::LICENCE_CATEGORY_PSV
                    ],
                    'licence' => [
                        'safetyInsVehicles' => 1,
                        'safetyInsTrailers' => 0,
                        'safetyInsVaries' => 'Y',
                        'tachographIns' => [
                            'id' => 'tach_external'
                        ],
                        'tachographInsName' => 'Bob',
                        'workshops' => [
                            [
                                'isExternal' => 'Y',
                                'contactDetails' => [
                                    'fao' => 'Bob Smith',
                                    'address' => [
                                        'addressLine1' => '123',
                                        'addressLine2' => 'Foo street',
                                        'town' => 'Footown'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'documents' => [
                        [
                            'description' => 'file',
                            'category' => [
                                'id' => Category::CATEGORY_APPLICATION,
                            ],
                            'subCategory' => [
                                'id' => SubCategory::DOC_SUB_CATEGORY_MAINT_OTHER_DIGITAL
                            ]
                        ]
                    ]
                ],
                [
                    'subSections' => [
                        [
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        'safetyIns' => [
                                            [
                                                'label' => 'application-review-safety-safetyInsVehicles',
                                                'value' => '1 Week-translated'
                                            ]
                                        ],
                                        'safetyInsVaries' => [
                                            [
                                                'label' => 'application-review-safety-safetyInsVaries-psv',
                                                'value' => 'Yes'
                                            ]
                                        ],
                                        [
                                            [
                                                'label' => 'application-review-safety-tachographIns',
                                                'value' => 'tachograph_analyser.tach_external-translated'
                                            ],
                                            [
                                                'label' => 'application-review-safety-tachographInsName',
                                                'value' => 'Bob'
                                            ]
                                        ],
                                        [
                                            [
                                                'label' => 'application-review-safety-additional-information',
                                                'noEscape' => true,
                                                'value' => 'file'
                                            ]
                                        ],
                                        [
                                            [
                                                'label' => 'application-review-safety-safetyConfirmation',
                                                'value' => 'Confirmed'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'title' => 'application-review-safety-workshop-title',
                            'mainItems' => [
                                [
                                    'header' => '123, Footown',
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'application-review-safety-workshop-isExternal',
                                                'value' => 'application-review-safety-workshop-isExternal-Y-translated'
                                            ],
                                            [
                                                'label' => 'application-review-safety-workshop-name',
                                                'value' => 'Bob Smith'
                                            ],
                                            [
                                                'label' => 'application-review-safety-workshop-address',
                                                'value' => '123, Foo street, Footown'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'Goods' => [
                [
                    'safetyConfirmation' => 'Y',
                    'isGoods' => true,
                    'goodsOrPsv' => [
                        'id' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE
                    ],
                    'totAuthTrailers' => 1,
                    'licence' => [
                        'safetyInsVehicles' => 2,
                        'safetyInsTrailers' => 0,
                        'safetyInsVaries' => 'Y',
                        'tachographIns' => [
                            'id' => 'tach_external'
                        ],
                        'tachographInsName' => 'Bob',
                        'workshops' => [
                            [
                                'isExternal' => 'Y',
                                'contactDetails' => [
                                    'fao' => 'Bob Smith',
                                    'address' => [
                                        'addressLine1' => '123',
                                        'addressLine2' => 'Foo street',
                                        'town' => 'Footown'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'documents' => [
                        [
                            'description' => 'file',
                            'category' => [
                                'id' => Category::CATEGORY_APPLICATION,
                            ],
                            'subCategory' => [
                                'id' => SubCategory::DOC_SUB_CATEGORY_MAINT_OTHER_DIGITAL
                            ]
                        ]
                    ]
                ],
                [
                    'subSections' => [
                        [
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        'safetyIns' => [
                                            [
                                                'label' => 'application-review-safety-safetyInsVehicles',
                                                'value' => 'no.of.weeks-translated'
                                            ],
                                            [
                                                'label' => 'application-review-safety-safetyInsTrailers',
                                                'value' => 'N/A-translated'
                                            ]
                                        ],
                                        'safetyInsVaries' => [
                                            [
                                                'label' => 'application-review-safety-safetyInsVaries',
                                                'value' => 'Yes'
                                            ]
                                        ],
                                        [
                                            [
                                                'label' => 'application-review-safety-tachographIns',
                                                'value' => 'tachograph_analyser.tach_external-translated'
                                            ],
                                            [
                                                'label' => 'application-review-safety-tachographInsName',
                                                'value' => 'Bob'
                                            ]
                                        ],
                                        [
                                            [
                                                'label' => 'application-review-safety-additional-information',
                                                'noEscape' => true,
                                                'value' => 'file'
                                            ]
                                        ],
                                        [
                                            [
                                                'label' => 'application-review-safety-safetyConfirmation',
                                                'value' => 'Confirmed'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'title' => 'application-review-safety-workshop-title',
                            'mainItems' => [
                                [
                                    'header' => '123, Footown',
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'application-review-safety-workshop-isExternal',
                                                'value' => 'application-review-safety-workshop-isExternal-Y-translated'
                                            ],
                                            [
                                                'label' => 'application-review-safety-workshop-name',
                                                'value' => 'Bob Smith'
                                            ],
                                            [
                                                'label' => 'application-review-safety-workshop-address',
                                                'value' => '123, Foo street, Footown'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
