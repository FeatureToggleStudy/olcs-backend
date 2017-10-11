<?php
namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\OperatingCentres;
use Dvsa\Olcs\Api\Service\Document\Parser\RtfParser;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking as ConditionUndertakingEntity;

/**
 * Operating Centres test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class OperatingCentresTest extends \PHPUnit_Framework_TestCase
{
    public function testGetQuery()
    {
        $bookmark = new OperatingCentres();
        $query = $bookmark->getQuery(['licence' => 7]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRenderWithNoData()
    {
        $parser = new RtfParser();
        $bookmark = new OperatingCentres();
        $bookmark->setData([]);
        $bookmark->setParser($parser);

        $result = $bookmark->render();

        $this->assertEquals('', $result);
    }

    public function testRenderWithGoodsLicence()
    {
        $data = [
            'id' => 100,
            'goodsOrPsv' => [
                'id' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
            ],
            'operatingCentres' => [
                [
                    'noOfVehiclesRequired' => 10,
                    'noOfTrailersRequired' => 5,
                    'operatingCentre' => [
                        'address' => [
                            'addressLine1' => 'Address 1',
                            'addressLine2' => 'Address 2'
                        ],
                        'conditionUndertakings' => [
                            [
                                'conditionType' => [
                                    'id' => ConditionUndertakingEntity::TYPE_CONDITION
                                ],
                                'attachedTo' => [
                                    'id' => ConditionUndertakingEntity::ATTACHED_TO_OPERATING_CENTRE
                                ],
                                'isFulfilled' => 'N',
                                'isDraft' => 'N',
                                'notes' => 'condition 1',
                                'licence' => [
                                    'id' => 100
                                ]
                            ], [
                                'conditionType' => [
                                    'id' => ConditionUndertakingEntity::TYPE_UNDERTAKING
                                ],
                                'attachedTo' => [
                                    'id' => ConditionUndertakingEntity::ATTACHED_TO_OPERATING_CENTRE
                                ],
                                'isFulfilled' => 'N',
                                'isDraft' => 'N',
                                'notes' => 'undertaking 1',
                                'licence' => [
                                    'id' => 100
                                ]
                            ], [
                                'conditionType' => [
                                    'id' => ConditionUndertakingEntity::TYPE_UNDERTAKING
                                ],
                                'attachedTo' => [
                                    'id' => ConditionUndertakingEntity::ATTACHED_TO_OPERATING_CENTRE
                                ],
                                'isFulfilled' => 'N',
                                'isDraft' => 'N',
                                'notes' => 'undertaking 2',
                                'licence' => [
                                    'id' => 100
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $parser = $this->createPartialMock('Dvsa\Olcs\Api\Service\Document\Parser\RtfParser', ['replace']);

        $conditionUndertakings = "Conditions\n\n1).\tcondition 1\n\n" .
            "Undertakings\n\n1).\tundertaking 1\n\n2).\tundertaking 2";

        $expectedRow = [
            'TAB_OC_ADD' => "Address 1\nAddress 2",
            'TAB_OC_VEH' => 10,
            'TAB_TRAILER' => 'Trailers',
            'TAB_OC_TRAILER' => 5,
            'TAB_OC_CONDS_UNDERS' => $conditionUndertakings
        ];

        $parser->expects($this->at(0))
            ->method('replace')
            ->with('snippet', $expectedRow)
            ->willReturn('foo');

        $bookmark = $this->createPartialMock(OperatingCentres::class, ['getSnippet']);

        $bookmark->expects($this->any())
            ->method('getSnippet')
            ->willReturn('snippet');

        $bookmark->setData($data);
        $bookmark->setParser($parser);

        $result = $bookmark->render();
        $this->assertEquals('foo', $result);
    }

    public function testRenderWithPsvLicence()
    {
        $data = [
            'id' => 123,
            'goodsOrPsv' => [
                'id' => Licence::LICENCE_CATEGORY_PSV,
            ],
            'operatingCentres' => [
                [
                    'noOfVehiclesRequired' => 10,
                    'operatingCentre' => [
                        'address' => [
                            'addressLine1' => 'Address 1',
                            'addressLine2' => 'Address 2'
                        ],
                        'conditionUndertakings' => []
                    ]
                ]
            ]
        ];

        $parser = $this->createPartialMock('Dvsa\Olcs\Api\Service\Document\Parser\RtfParser', ['replace']);

        $expectedRow = [
            'TAB_OC_ADD' => "Address 1\nAddress 2",
            'TAB_OC_VEH' => 10,
            'TAB_TRAILER' => '',
            'TAB_OC_TRAILER' => '',
            'TAB_OC_CONDS_UNDERS' => ''
        ];

        $parser->expects($this->at(0))
            ->method('replace')
            ->with('snippet', $expectedRow)
            ->willReturn('foo');

        $bookmark = $this->createPartialMock(OperatingCentres::class, ['getSnippet']);

        $bookmark->expects($this->any())
            ->method('getSnippet')
            ->willReturn('snippet');

        $bookmark->setData($data);
        $bookmark->setParser($parser);

        $result = $bookmark->render();
        $this->assertEquals('foo', $result);
    }
}
