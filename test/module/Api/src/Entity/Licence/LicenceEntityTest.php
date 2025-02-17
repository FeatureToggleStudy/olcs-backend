<?php

namespace Dvsa\OlcsTest\Api\Entity\Licence;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection as CollectionInterface;
use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CaseEntity;
use Dvsa\Olcs\Api\Entity\Cases\Complaint as ComplaintEntity;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic as CommunityLicEntity;
use Dvsa\Olcs\Api\Entity\Licence\Continuation;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\Olcs\Api\Entity\Licence\Licence as Entity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre;
use Dvsa\Olcs\Api\Entity\Licence\LicenceStatusRule;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\Organisation\TradingName as TradingNameEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Entity\Publication\Publication;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address;

/**
 * Licence Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class LicenceEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testCanBecomeSpecialRestrictedNull()
    {
        /** @var Entity $licence */
        $licence = m::mock(Entity::class)->makePartial();
        $licence->shouldReceive('getGoodsOrPsv')->andReturn(null);
        $licence->shouldReceive('getLicenceType')->andReturn(null);

        $this->assertEquals(true, $licence->canBecomeSpecialRestricted());
    }

    /**
     * @dataProvider licenceTypeProvider
     */
    public function testCanBecomeSpecialRestricted($goodsOrPsv, $licenceType, $expected)
    {
        /** @var Entity $licence */
        $licence = m::mock(Entity::class)->makePartial();
        $licence->shouldReceive('getGoodsOrPsv->getId')->andReturn($goodsOrPsv);
        $licence->shouldReceive('getLicenceType->getId')->andReturn($licenceType);

        $this->assertEquals($expected, $licence->canBecomeSpecialRestricted());
    }

    /**
     * @dataProvider updateSafetyDetails
     */
    public function testUpdateSafetyDetails(
        $safetyInsVehicles,
        $safetyInsTrailers,
        $tachographIns,
        $tachographInsName,
        $safetyInsVaries,
        $expectedException = null
    ) {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);

        if ($expectedException !== null) {
            $this->expectException($expectedException);
        }

        $licence->updateSafetyDetails(
            $safetyInsVehicles,
            $safetyInsTrailers,
            $tachographIns,
            $tachographInsName,
            $safetyInsVaries
        );

        if ($expectedException == null) {
            $this->assertEquals($safetyInsVehicles, $licence->getSafetyInsVehicles());
            $this->assertEquals($safetyInsTrailers, $licence->getSafetyInsTrailers());
            $this->assertEquals($tachographIns, $licence->getTachographIns());
            $this->assertEquals($tachographInsName, $licence->getTachographInsName());
            $this->assertEquals($safetyInsVaries, $licence->getSafetyInsVaries());
        }
    }

    public function testGetRemainingSpaces()
    {
        $lvCollection = m::mock(ArrayCollection::class);
        $activeCollection = m::mock(ArrayCollection::class);

        $lvCollection->shouldReceive('matching')
            ->once()
            ->with(m::type(Criteria::class))
            ->andReturn($activeCollection);

        $activeCollection->shouldReceive('count')
            ->andReturn(6);

        $licence = $this->instantiate(Entity::class);

        $licence->setTotAuthVehicles(10);
        $licence->setLicenceVehicles($lvCollection);

        $this->assertEquals(4, $licence->getRemainingSpaces());
    }

    public function testGetActiveVehiclesCount()
    {
        $lvCollection = m::mock(ArrayCollection::class);
        $activeCollection = m::mock(ArrayCollection::class);

        $lvCollection->shouldReceive('matching')
            ->once()
            ->with(m::type(Criteria::class))
            ->andReturn($activeCollection);

        $activeCollection->shouldReceive('count')
            ->andReturn(6);

        $licence = $this->instantiate(Entity::class);
        $licence->setLicenceVehicles($lvCollection);

        $this->assertEquals(6, $licence->getActiveVehiclesCount());
    }

    public function testGetActiveVehicles()
    {
        $lvCollection = m::mock(ArrayCollection::class);
        $activeCollection = m::mock(ArrayCollection::class);

        $lvCollection->shouldReceive('matching')
            ->once()
            ->with(m::type(Criteria::class))
            ->andReturn($activeCollection);

        $licence = $this->instantiate(Entity::class);
        $licence->setLicenceVehicles($lvCollection);

        $this->assertSame($activeCollection, $licence->getActiveVehicles());
    }

    public function testGetOtherActiveLicences()
    {
        /** @var RefData $goodsOrPsv */
        $goodsOrPsv = m::mock(RefData::class)->makePartial();
        $goodsOrPsv->setId(Entity::LICENCE_CATEGORY_PSV);

        $licence1 = m::mock(Entity::class)->makePartial();

        $licences = m::mock(ArrayCollection::class)->makePartial();
        $licences->add($licence1);

        /** @var OrganisationEntity $org */
        $org = m::mock(OrganisationEntity::class)->makePartial();
        $org->setLicences($licences);

        /** @var RefData $type1 */
        $type1 = m::mock(RefData::class)->makePartial();
        $type1->setId(Entity::LICENCE_TYPE_SPECIAL_RESTRICTED);

        /** @var RefData $type2 */
        $type2 = m::mock(RefData::class)->makePartial();
        $type2->setId(Entity::LICENCE_TYPE_STANDARD_NATIONAL);

        /** @var Entity $otherLicence1 */
        $otherLicence1 = m::mock(Entity::class)->makePartial();
        $otherLicence1->setLicenceType($type1);

        $otherLicence2 = m::mock(Entity::class)->makePartial();
        $otherLicence2->setLicenceType($type2);

        $otherActiveLicences = m::mock(ArrayCollection::class)->makePartial();
        $otherActiveLicences->add($otherLicence1);
        $otherActiveLicences->add($otherLicence2);

        $licences->shouldReceive('matching')
            ->with(m::type(Criteria::class))
            ->andReturn($otherActiveLicences);

        $licence = $this->instantiate(Entity::class);
        $licence->setId(111);
        $licence->setGoodsOrPsv($goodsOrPsv);
        $licence->setOrganisation($org);

        $this->assertSame($otherActiveLicences, $licence->getOtherActiveLicences());
        $this->assertFalse($otherActiveLicences->contains($otherLicence1));
        $this->assertTrue($otherActiveLicences->contains($otherLicence2));
    }

    public function updateSafetyDetails()
    {
        return [
            [
                2,
                1,
                'tach_external',
                'Some name',
                'Y',
                null
            ],
            [
                2,
                1,
                'tach_external',
                '',
                'Y',
                ValidationException::class
            ],
            [
                2,
                1,
                'tach_internal',
                '',
                'Y',
                null
            ],
            [
                2,
                1,
                'tach_na',
                '',
                'Y',
                null
            ],
            [
                null,
                1,
                'tach_na',
                '',
                'Y',
                null
            ]
        ];
    }

    public function licenceTypeProvider()
    {
        return [
            [
                Entity::LICENCE_CATEGORY_GOODS_VEHICLE,
                Entity::LICENCE_TYPE_STANDARD_NATIONAL,
                false
            ],
            [
                Entity::LICENCE_CATEGORY_PSV,
                Entity::LICENCE_TYPE_STANDARD_NATIONAL,
                false
            ],
            [
                Entity::LICENCE_CATEGORY_PSV,
                Entity::LICENCE_TYPE_RESTRICTED,
                false
            ],
            [
                Entity::LICENCE_CATEGORY_PSV,
                Entity::LICENCE_TYPE_SPECIAL_RESTRICTED,
                true
            ]
        ];
    }

    public function testUpdateTotalCommunityLicences()
    {
        $sut = m::mock(Entity::class)->makePartial();
        $sut->updateTotalCommunityLicences(10);
        $this->assertEquals(10, $sut->getTotCommunityLicences());
    }

    /**
     * @dataProvider trafficAreaProvider
     */
    public function testGetSerialNoPrefixFromTrafficArea($isNi, $expected)
    {
        $sut = m::mock(Entity::class)->makePartial();
        $sut->shouldReceive('getTrafficArea')
            ->andReturn(
                m::mock()
                ->shouldReceive('getIsNi')
                ->andReturn($isNi)
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $this->assertEquals($expected, $sut->getSerialNoPrefixFromTrafficArea());
    }

    public function trafficAreaProvider()
    {
        return [
            [true, CommunityLicEntity::PREFIX_NI],
            [false, CommunityLicEntity::PREFIX_GB],
        ];
    }

    public function testHasCommunityLicenceOfficeCopy()
    {
        $licence = m::mock(Entity::class)->makePartial();
        $licence->shouldReceive('getCommunityLics->matching')
            ->with(m::type(Criteria::class))
            ->andReturnUsing(
                function (Criteria $criteria) {

                    /** @var \Doctrine\Common\Collections\Expr\Comparison $expr */
                    $compositeExpression = $criteria->getWhereExpression();
                    $expressions = $compositeExpression->getExpressionList();

                    $this->assertEquals('issueNo', $expressions[0]->getField());
                    $this->assertEquals('=', $expressions[0]->getOperator());
                    $this->assertEquals(0, $expressions[0]->getValue()->getValue());

                    $this->assertEquals('status', $expressions[1]->getField());
                    $this->assertEquals('IN', $expressions[1]->getOperator());
                    $this->assertEquals(
                        [
                            CommunityLicEntity::STATUS_PENDING,
                            CommunityLicEntity::STATUS_ACTIVE,
                            CommunityLicEntity::STATUS_WITHDRAWN,
                            CommunityLicEntity::STATUS_SUSPENDED
                        ],
                        $expressions[1]->getValue()->getValue()
                    );

                    $mockCollection = m::mock()
                        ->shouldReceive('current')
                        ->andReturn(
                            m::mock()
                            ->shouldReceive('getId')
                            ->andReturn(1)
                            ->once()
                            ->getMock()
                        )
                        ->once()
                        ->getMock();

                    return $mockCollection;
                }
            );

        $this->assertTrue($licence->hasCommunityLicenceOfficeCopy([1]));
    }

    public function testHasApprovedUnfulfilledConditions()
    {
        /** @var Entity $licence */
        $licence = m::mock(Entity::class)->makePartial();
        $licence->shouldReceive('getConditionUndertakings->matching->count')
            ->andReturn(1);

        $this->assertTrue($licence->hasApprovedUnfulfilledConditions());
    }

    public function testHasApprovedUnfulfilledConditionsNone()
    {
        /** @var Entity $licence */
        $licence = m::mock(Entity::class)->makePartial();
        $licence->shouldReceive('getConditionUndertakings->matching->count')
            ->andReturn(0);

        $this->assertFalse($licence->hasApprovedUnfulfilledConditions());
    }

    /**
     * Note: We've already tested the isValid, isGoods and isRestricted in our other tests - so we can safely assume these
     * parts are valid and only check they work together against a list of licence types
     *
     * @dataProvider dpEligibleForPermits
     */
    public function testisEligibleForPermits($licenceType, $expectedResult)
    {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setStatus(new RefData(Entity::LICENCE_STATUS_VALID));
        $licence->setGoodsOrPsv(new RefData(Entity::LICENCE_CATEGORY_GOODS_VEHICLE));
        $licence->setLicenceType(new RefData($licenceType));

        $this->assertEquals($expectedResult, $licence->isEligibleForPermits());
    }

    public function dpEligibleForPermits()
    {
        return [
            [Entity::LICENCE_TYPE_RESTRICTED, true],
            [Entity::LICENCE_TYPE_STANDARD_INTERNATIONAL, true],
            [Entity::LICENCE_TYPE_STANDARD_NATIONAL, true],
            [Entity::LICENCE_TYPE_SPECIAL_RESTRICTED, false],
        ];
    }

    /**
     * Note: We've already tested the isValid and isGoods in our other tests - so we can safely assume these
     * parts are valid and only check they work together against a list of licence types
     *
     * @dataProvider dpValidSiGoods
     */
    public function testIsValidSiGoods($licenceType, $expectedResult)
    {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setStatus(new RefData(Entity::LICENCE_STATUS_VALID));
        $licence->setGoodsOrPsv(new RefData(Entity::LICENCE_CATEGORY_GOODS_VEHICLE));
        $licence->setLicenceType(new RefData($licenceType));

        $this->assertEquals($expectedResult, $licence->isValidSiGoods());
    }





    public function dpValidSiGoods()
    {
        return [
            [Entity::LICENCE_TYPE_RESTRICTED, false],
            [Entity::LICENCE_TYPE_STANDARD_INTERNATIONAL, true],
            [Entity::LICENCE_TYPE_STANDARD_NATIONAL, false],
            [Entity::LICENCE_TYPE_SPECIAL_RESTRICTED, false],
        ];
    }

    /**
     * @dataProvider dpIsValid
     *
     * @param $status
     * @param $expectedResult
     */
    public function testIsValid($status, $expectedResult)
    {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setStatus(new RefData($status));

        $this->assertEquals($expectedResult, $licence->isValid());
    }

    public function dpIsValid()
    {
        return [
            [Entity::LICENCE_STATUS_SUSPENDED, true],
            [Entity::LICENCE_STATUS_VALID, true],
            [Entity::LICENCE_STATUS_CURTAILED, true],
            [Entity::LICENCE_STATUS_UNDER_CONSIDERATION, false],
            [Entity::LICENCE_STATUS_NOT_SUBMITTED, false],
            [Entity::LICENCE_STATUS_GRANTED, false],
            [Entity::LICENCE_STATUS_SURRENDERED, false],
            [Entity::LICENCE_STATUS_WITHDRAWN, false],
            [Entity::LICENCE_STATUS_REFUSED, false],
            [Entity::LICENCE_STATUS_REVOKED, false],
            [Entity::LICENCE_STATUS_NOT_TAKEN_UP, false],
            [Entity::LICENCE_STATUS_TERMINATED, false],
            [Entity::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT, false],
            [Entity::LICENCE_STATUS_UNLICENSED, false],
            [Entity::LICENCE_STATUS_CANCELLED, false],
        ];
    }

    /**
     * @dataProvider dpIsValidGoods
     */
    public function testIsValidGoods($status, $goodsOrPsv, $expectedResult)
    {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setStatus(new RefData($status));
        $licence->setGoodsOrPsv(new RefData($goodsOrPsv));

        $this->assertEquals($expectedResult, $licence->isValidGoods());
    }

    public function dpIsValidGoods()
    {
        return [
            //licence statuses with a GV licence
            [Entity::LICENCE_STATUS_SUSPENDED, Entity::LICENCE_CATEGORY_GOODS_VEHICLE, true],
            [Entity::LICENCE_STATUS_VALID, Entity::LICENCE_CATEGORY_GOODS_VEHICLE, true],
            [Entity::LICENCE_STATUS_CURTAILED, Entity::LICENCE_CATEGORY_GOODS_VEHICLE, true],
            [Entity::LICENCE_STATUS_UNDER_CONSIDERATION, Entity::LICENCE_CATEGORY_GOODS_VEHICLE, false],
            [Entity::LICENCE_STATUS_NOT_SUBMITTED, Entity::LICENCE_CATEGORY_GOODS_VEHICLE, false],
            [Entity::LICENCE_STATUS_GRANTED, Entity::LICENCE_CATEGORY_GOODS_VEHICLE, false],
            [Entity::LICENCE_STATUS_SURRENDERED, Entity::LICENCE_CATEGORY_GOODS_VEHICLE, false],
            [Entity::LICENCE_STATUS_WITHDRAWN, Entity::LICENCE_CATEGORY_GOODS_VEHICLE, false],
            [Entity::LICENCE_STATUS_REFUSED, Entity::LICENCE_CATEGORY_GOODS_VEHICLE, false],
            [Entity::LICENCE_STATUS_REVOKED, Entity::LICENCE_CATEGORY_GOODS_VEHICLE, false],
            [Entity::LICENCE_STATUS_NOT_TAKEN_UP, Entity::LICENCE_CATEGORY_GOODS_VEHICLE, false],
            [Entity::LICENCE_STATUS_TERMINATED, Entity::LICENCE_CATEGORY_GOODS_VEHICLE, false],
            [Entity::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT, Entity::LICENCE_CATEGORY_GOODS_VEHICLE, false],
            [Entity::LICENCE_STATUS_UNLICENSED, Entity::LICENCE_CATEGORY_GOODS_VEHICLE, false],
            [Entity::LICENCE_STATUS_CANCELLED, Entity::LICENCE_CATEGORY_GOODS_VEHICLE, false],
            //licence statuses with a psv licence
            [Entity::LICENCE_STATUS_SUSPENDED, Entity::LICENCE_CATEGORY_PSV, false],
            [Entity::LICENCE_STATUS_VALID, Entity::LICENCE_CATEGORY_PSV, false],
            [Entity::LICENCE_STATUS_CURTAILED, Entity::LICENCE_CATEGORY_PSV, false],
            [Entity::LICENCE_STATUS_UNDER_CONSIDERATION, Entity::LICENCE_CATEGORY_PSV, false],
            [Entity::LICENCE_STATUS_NOT_SUBMITTED, Entity::LICENCE_CATEGORY_PSV, false],
            [Entity::LICENCE_STATUS_GRANTED, Entity::LICENCE_CATEGORY_PSV, false],
            [Entity::LICENCE_STATUS_SURRENDERED, Entity::LICENCE_CATEGORY_PSV, false],
            [Entity::LICENCE_STATUS_WITHDRAWN, Entity::LICENCE_CATEGORY_PSV, false],
            [Entity::LICENCE_STATUS_REFUSED, Entity::LICENCE_CATEGORY_PSV, false],
            [Entity::LICENCE_STATUS_REVOKED, Entity::LICENCE_CATEGORY_PSV, false],
            [Entity::LICENCE_STATUS_NOT_TAKEN_UP, Entity::LICENCE_CATEGORY_PSV, false],
            [Entity::LICENCE_STATUS_TERMINATED, Entity::LICENCE_CATEGORY_PSV, false],
            [Entity::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT, Entity::LICENCE_CATEGORY_PSV, false],
            [Entity::LICENCE_STATUS_UNLICENSED, Entity::LICENCE_CATEGORY_PSV, false],
            [Entity::LICENCE_STATUS_CANCELLED, Entity::LICENCE_CATEGORY_PSV, false],
        ];
    }

    public function testIsGoods()
    {
        $goodsOrPsv = m::mock(RefData::class)->makePartial();
        $goodsOrPsv->setId(Entity::LICENCE_CATEGORY_GOODS_VEHICLE);

        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);

        $this->assertNull($licence->isGoods());

        $licence->setGoodsOrPsv($goodsOrPsv);

        $this->assertTrue($licence->isGoods());
    }

    public function testIsGoodsFalse()
    {
        $goodsOrPsv = m::mock(RefData::class)->makePartial();
        $goodsOrPsv->setId(Entity::LICENCE_CATEGORY_PSV);

        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setGoodsOrPsv($goodsOrPsv);

        $this->assertFalse($licence->isGoods());
    }

    public function testIsPsv()
    {
        $goodsOrPsv = m::mock(RefData::class)->makePartial();
        $goodsOrPsv->setId(Entity::LICENCE_CATEGORY_PSV);

        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);

        $this->assertNull($licence->isPsv());

        $licence->setGoodsOrPsv($goodsOrPsv);

        $this->assertTrue($licence->isPsv());
    }

    public function testIsPsvFalse()
    {
        $goodsOrPsv = m::mock(RefData::class)->makePartial();
        $goodsOrPsv->setId(Entity::LICENCE_CATEGORY_GOODS_VEHICLE);

        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setGoodsOrPsv($goodsOrPsv);

        $this->assertFalse($licence->isPsv());
    }

    public function testIsSpecialRestricted()
    {
        $licenceType = m::mock(RefData::class)->makePartial();
        $licenceType->setId(Entity::LICENCE_TYPE_SPECIAL_RESTRICTED);

        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);

        $this->assertNull($licence->isSpecialRestricted());

        $licence->setLicenceType($licenceType);

        $this->assertTrue($licence->isSpecialRestricted());
    }

    public function testIsSpecialRestrictedFalse()
    {
        $licenceType = m::mock(RefData::class)->makePartial();
        $licenceType->setId(Entity::LICENCE_TYPE_STANDARD_NATIONAL);

        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setLicenceType($licenceType);

        $this->assertFalse($licence->isSpecialRestricted());
    }

    public function testGetTradingNameNone()
    {
        $licence = m::mock(Entity::class)->makePartial();
        $licence
            ->shouldReceive('getTradingNames->getIterator')
            ->once()
            ->andReturn([]);

        $this->assertEquals('None', $licence->getTradingName());
    }

    public function testGetTradingNamesSorting()
    {
        $tradingName1 = m::mock(TradingNameEntity::class)
            ->makePartial()
            ->setName('Foo')
            ->setCreatedOn('2015-06-01 00:00:00');
        $tradingName2 = m::mock(TradingNameEntity::class)
            ->makePartial()
            ->setName('Bar')
            ->setCreatedOn('2015-06-01 00:00:00');
        $tradingName3 = m::mock(TradingNameEntity::class)
            ->makePartial()
            ->setName('Baz')
            ->setCreatedOn('2015-07-01 00:00:00');
        $tradingNames = new ArrayCollection();
        $tradingNames->add($tradingName1);
        $tradingNames->add($tradingName2);
        $tradingNames->add($tradingName3);

        $licence = m::mock(Entity::class)->makePartial();
        $licence
            ->shouldReceive('getTradingNames')
            ->once()
            ->andReturn($tradingNames);

        $this->assertEquals('Bar', $licence->getTradingName());
    }

    public function testGetAllTradingNames()
    {
        $tradingName1 = m::mock(TradingNameEntity::class)
            ->makePartial()
            ->setName('Foo')
            ->setCreatedOn('2015-06-01 00:00:00');
        $tradingName2 = m::mock(TradingNameEntity::class)
            ->makePartial()
            ->setName('Bar')
            ->setCreatedOn('2015-06-01 00:00:00');
        $tradingName3 = m::mock(TradingNameEntity::class)
            ->makePartial()
            ->setName('Baz')
            ->setCreatedOn('2015-07-01 00:00:00');
        $tradingNames = new ArrayCollection();
        $tradingNames->add($tradingName1);
        $tradingNames->add($tradingName2);
        $tradingNames->add($tradingName3);

        $licence = m::mock(Entity::class)->makePartial();
        $licence
            ->shouldReceive('getTradingNames')
            ->once()
            ->andReturn($tradingNames);

        $this->assertEquals(['Bar', 'Foo', 'Baz'], $licence->getAllTradingNames());
    }

    public function testGetOpenComplaintsCount()
    {
        $case1 = m::mock(CaseEntity::class)->makePartial();
        $case2 = m::mock(CaseEntity::class)->makePartial();
        $cases = new ArrayCollection();
        $cases->add($case1);
        $cases->add($case2);

        $case1
            ->shouldReceive('getComplaints')
            ->andReturn(
                [
                    m::mock(ComplaintEntity::class)
                        ->makePartial()
                        ->shouldReceive('getIsCompliance')
                        ->andReturn(0)
                        ->shouldReceive('isOpen')
                        ->andReturn(true)
                        ->getMock()
                ]
            );
        $case2
            ->shouldReceive('getComplaints')
            ->andReturn(
                [
                    m::mock(ComplaintEntity::class)
                        ->makePartial()
                        ->shouldReceive('getIsCompliance')
                        ->andReturn(1)
                        ->shouldReceive('isOpen')
                        ->andReturn(true)
                        ->getMock()
                ]
            );

        $licence = m::mock(Entity::class)->makePartial();
        $licence->setCases($cases);

        $this->assertEquals(1, $licence->getOpenComplaintsCount());
    }

    public function testGetOpenCases()
    {
        $case1 = m::mock(CaseEntity::class)->makePartial();
        $case2 = m::mock(CaseEntity::class)->makePartial();
        $cases = new ArrayCollection();
        $cases->add($case1);
        $cases->add($case2);

        $case1
            ->shouldReceive('isOpen')
            ->andReturn(true);
        $case2
            ->shouldReceive('isOpen')
            ->andReturn(false);

        $licence = m::mock(Entity::class)->makePartial();
        $licence->setCases($cases);

        $this->assertEquals([$case1], $licence->getOpenCases());
    }

    public function testGetOcForInspectionRequest()
    {
        $mockOperatingCentre = m::mock()
            ->shouldReceive('getOperatingCentre')
            ->andReturn('oc')
            ->once()
            ->getMock();

        $mockLicence = m::mock(Entity::class)->makePartial();
        $mockLicence->shouldReceive('getOperatingCentres')
            ->andReturn([$mockOperatingCentre])
            ->once()
            ->getMock();

        $this->assertEquals(['oc'], $mockLicence->getOcForInspectionRequest());
    }

    public function testIsRestricted()
    {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);

        $this->assertNull($licence->isRestricted());

        $licenceType = m::mock(RefData::class)->makePartial();
        $licenceType->setId(Entity::LICENCE_TYPE_STANDARD_NATIONAL);
        $licence->setLicenceType($licenceType);

        $this->assertFalse($licence->isRestricted());

        $licenceType = m::mock(RefData::class)->makePartial();
        $licenceType->setId(Entity::LICENCE_TYPE_RESTRICTED);
        $licence->setLicenceType($licenceType);

        $this->assertTrue($licence->isRestricted());
    }

    public function testIsStandardInternational()
    {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);

        $this->assertNull($licence->isStandardInternational());

        $licenceType = m::mock(RefData::class)->makePartial();
        $licenceType->setId(Entity::LICENCE_TYPE_STANDARD_NATIONAL);
        $licence->setLicenceType($licenceType);

        $this->assertFalse($licence->isStandardInternational());

        $licenceType = m::mock(RefData::class)->makePartial();
        $licenceType->setId(Entity::LICENCE_TYPE_STANDARD_INTERNATIONAL);
        $licence->setLicenceType($licenceType);

        $this->assertTrue($licence->isStandardInternational());
    }

    public function testIsStandardNational()
    {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);

        $this->assertNull($licence->isStandardNational());

        $licenceType = m::mock(RefData::class)->makePartial();
        $licenceType->setId(Entity::LICENCE_TYPE_STANDARD_INTERNATIONAL);
        $licence->setLicenceType($licenceType);

        $this->assertFalse($licence->isStandardNational());

        $licenceType = m::mock(RefData::class)->makePartial();
        $licenceType->setId(Entity::LICENCE_TYPE_STANDARD_NATIONAL);
        $licence->setLicenceType($licenceType);

        $this->assertTrue($licence->isStandardNational());
    }

    public function testCanHaveCommunityLicencesStandardInt()
    {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);

        $licenceType = m::mock(RefData::class)->makePartial();
        $licenceType->setId(Entity::LICENCE_TYPE_STANDARD_INTERNATIONAL);
        $licence->setLicenceType($licenceType);

        $this->assertTrue($licence->canHaveCommunityLicences());
    }

    public function testCanHaveCommunityLicencesPsvRestricted()
    {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);

        $goodsOrPsv = m::mock(RefData::class)->makePartial();
        $goodsOrPsv->setId(Entity::LICENCE_CATEGORY_PSV);
        $licence->setGoodsOrPsv($goodsOrPsv);

        $licenceType = m::mock(RefData::class)->makePartial();
        $licenceType->setId(Entity::LICENCE_TYPE_RESTRICTED);
        $licence->setLicenceType($licenceType);

        $this->assertTrue($licence->canHaveCommunityLicences());
    }

    public function testCanHaveCommunityLicencesSomethingElse()
    {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);

        $goodsOrPsv = m::mock(RefData::class)->makePartial();
        $goodsOrPsv->setId(Entity::LICENCE_CATEGORY_GOODS_VEHICLE);
        $licence->setGoodsOrPsv($goodsOrPsv);

        $licenceType = m::mock(RefData::class)->makePartial();
        $licenceType->setId(Entity::LICENCE_TYPE_STANDARD_NATIONAL);
        $licence->setLicenceType($licenceType);

        $this->assertFalse($licence->canHaveCommunityLicences());
    }

    public function testCopyInformationFromNewApplication()
    {
        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();

        $licenceType = m::mock(RefData::class)->makePartial();
        $licenceType->setId(Entity::LICENCE_TYPE_STANDARD_NATIONAL);
        $application->setLicenceType($licenceType);
        $application->shouldReceive('isVariation')->once()->withNoArgs()->andReturn(false);

        $goodsOrPsv = m::mock(RefData::class)->makePartial();
        $goodsOrPsv->setId(Entity::LICENCE_CATEGORY_GOODS_VEHICLE);
        $application->setGoodsOrPsv($goodsOrPsv);

        $application->setTotAuthTrailers(9);
        $application->setTotAuthVehicles(12);

        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);

        $licence->copyInformationFromApplication($application);

        $this->assertSame($licenceType, $licence->getLicenceType());
        $this->assertSame($goodsOrPsv, $licence->getGoodsOrPsv());
        $this->assertEquals(9, $licence->getTotAuthTrailers());
        $this->assertEquals(12, $licence->getTotAuthVehicles());
    }

    public function testCopyInformationFromVariationApplication()
    {
        $appCompletion = m::mock(ApplicationCompletion::class);
        $appCompletion->shouldReceive('variationSectionUpdated')->with('typeOfLicence')->once()->andReturn(true);
        $appCompletion->shouldReceive('variationSectionUpdated')->with('operatingCentres')->once()->andReturn(true);

        $licenceType = m::mock(RefData::class);
        $goodsOrPsv = m::mock(RefData::class);
        $totAuthTrailers = 9;
        $totAuthVehicles = 12;

        $application = m::mock(Application::class);
        $application->shouldReceive('isVariation')->once()->withNoArgs()->andReturn(true);
        $application->shouldReceive('getApplicationCompletion')->once()->withNoArgs()->andReturn($appCompletion);
        $application->shouldReceive('getLicenceType')->once()->withNoArgs()->andReturn($licenceType);
        $application->shouldReceive('getGoodsOrPsv')->once()->withNoArgs()->andReturn($goodsOrPsv);
        $application->shouldReceive('getTotAuthTrailers')->once()->withNoArgs()->andReturn($totAuthTrailers);
        $application->shouldReceive('getTotAuthVehicles')->once()->withNoArgs()->andReturn($totAuthVehicles);

        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);

        $licence->copyInformationFromApplication($application);

        $this->assertSame($licenceType, $licence->getLicenceType());
        $this->assertSame($goodsOrPsv, $licence->getGoodsOrPsv());
        $this->assertEquals($totAuthTrailers, $licence->getTotAuthTrailers());
        $this->assertEquals($totAuthVehicles, $licence->getTotAuthVehicles());
    }

    public function testCopyInformationFromUnchangedVariationApplication()
    {
        $appCompletion = m::mock(ApplicationCompletion::class);
        $appCompletion->shouldReceive('variationSectionUpdated')->with('typeOfLicence')->once()->andReturn(false);
        $appCompletion->shouldReceive('variationSectionUpdated')->with('operatingCentres')->once()->andReturn(false);

        $goodsOrPsv = m::mock(RefData::class);

        $application = m::mock(Application::class);
        $application->shouldReceive('isVariation')->once()->withNoArgs()->andReturn(true);
        $application->shouldReceive('getApplicationCompletion')->once()->withNoArgs()->andReturn($appCompletion);
        $application->shouldReceive('getGoodsOrPsv')->once()->withNoArgs()->andReturn($goodsOrPsv);
        $application->shouldReceive('getLicenceType')->never();
        $application->shouldReceive('getTotAuthTrailers')->never();
        $application->shouldReceive('getTotAuthVehicles')->never();

        $originalLicenceType = m::mock(RefData::class);
        $originalTotAuthTrailers = 6;
        $originalTotAuthVehicles = 10;

        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setLicenceType($originalLicenceType);
        $licence->setTotAuthTrailers($originalTotAuthTrailers);
        $licence->setTotAuthVehicles($originalTotAuthVehicles);

        $licence->copyInformationFromApplication($application);

        //only goodsOrPsv should have changed
        $this->assertSame($originalLicenceType, $licence->getLicenceType());
        $this->assertSame($goodsOrPsv, $licence->getGoodsOrPsv());
        $this->assertEquals($originalTotAuthTrailers, $licence->getTotAuthTrailers());
        $this->assertEquals($originalTotAuthVehicles, $licence->getTotAuthVehicles());
    }

    public function testGetPsvDiscsNotCeased()
    {
        $psvDiscsCollection = m::mock(ArrayCollection::class);
        $psvDiscsNotCeasedCollection = m::mock(ArrayCollection::class);

        $psvDiscsCollection->shouldReceive('matching')->once()->with(m::type(Criteria::class))->andReturnUsing(
            function (Criteria $criteria) use ($psvDiscsNotCeasedCollection) {
                $expectedCriteria = Criteria::create()
                    ->where(Criteria::expr()->isNull('ceasedDate'));

                $this->assertEquals($expectedCriteria, $criteria);

                return $psvDiscsNotCeasedCollection;
            }
        );

        $licence = $this->instantiate(Entity::class);

        $licence->setPsvDiscs($psvDiscsCollection);
        $this->assertSame($psvDiscsNotCeasedCollection, $licence->getPsvDiscsNotCeased());
    }

    /**
     * @dataProvider dpCanHaveVariationProvider
     */
    public function testCanHaveVariation($status, $expected)
    {
        $licence = m::mock(Entity::class)->makePartial();

        $licence->shouldReceive('getStatus->getId')
            ->andReturn($status);

        $this->assertEquals($licence->canHaveVariation(), $expected);
    }

    public function dpCanHaveVariationProvider()
    {
        return [
            [
                Entity::LICENCE_STATUS_VALID,
                true
            ],
            [
                Entity::LICENCE_STATUS_SURRENDERED,
                false
            ],
            [
                Entity::LICENCE_STATUS_REVOKED,
                false
            ],
            [
                Entity::LICENCE_STATUS_TERMINATED,
                false
            ]
        ];
    }

    /**
     * @param string $categoryId 'lcat_psv'|'lcat_gv'
     * @param string $expected 'O'|'P'
     * @dataProvider categoryPrefixDp
     */
    public function testGetCategoryPrefix($categoryId, $expected)
    {
        $category = new RefData($categoryId);

        $licence = $this->instantiate(Entity::class);
        $licence->setGoodsOrPsv($category);

        $this->assertEquals($expected, $licence->getCategoryPrefix());
    }

    public function categoryPrefixDp()
    {
        return [
            [Entity::LICENCE_CATEGORY_PSV, 'P'],
            [Entity::LICENCE_CATEGORY_GOODS_VEHICLE, 'O'],
        ];
    }

    public function testGetRemainingSpacesPsv()
    {
        $licence = m::mock(Entity::class)->makePartial()
            ->shouldReceive('getTotAuthVehicles')
            ->andReturn(2)
            ->once()
            ->shouldReceive('getPsvDiscsNotCeased')
            ->andReturn(new \ArrayObject([1, 2, 3]))
            ->once()
            ->getMock();

        $this->assertEquals(-1, $licence->getRemainingSpacesPsv());
    }

    /**
     * @dataProvider dpTestGetLicenceTypeShortCode
     * @param string $licenceType
     * @param string $shortCode
     */
    public function testGetLicenceTypeShortCode($licenceType, $shortCode)
    {
        $licence = m::mock(Entity::class)->makePartial();
        $licence->setLicenceType((new RefData())->setId($licenceType));

        $this->assertSame($shortCode, $licence->getLicenceTypeShortCode());
    }

    public function dpTestGetLicenceTypeShortCode()
    {
        return [
            ['ltyp_r', 'R'],
            ['ltyp_si', 'SI'],
            ['ltyp_sn', 'SN'],
            ['ltyp_sr', 'SR'],
            ['ltyp_cbp', 'CBP'],
            ['ltyp_dbp', 'DBP'],
            ['ltyp_lbp', 'LBP'],
            ['ltyp_sbp', 'SBP'],
            ['XXXX', null],
        ];
    }

    public function testGetContextValue()
    {
        $entity = $this->instantiate(Entity::class);
        $entity->setLicNo(111);

        $this->assertEquals(111, $entity->getContextValue());
    }

    public function testGetVariations()
    {
        $licence = $this->instantiate(Entity::class);
        $app1 = new Application($licence, $this->getRefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION), 0);
        $app1->setIsVariation(true);
        $app1->setId(1);
        $app2 = new Application($licence, $this->getRefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION), 0);
        $app2->setIsVariation(false);
        $app2->setId(2);
        $licence->setApplications(new \Doctrine\Common\Collections\ArrayCollection([$app1, $app2]));

        $this->assertEquals(1, $licence->getVariations()[0]->getId());
    }

    public function testGetNewApplications()
    {
        $licence = $this->instantiate(Entity::class);
        $app1 = new Application($licence, $this->getRefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION), 0);
        $app1->setIsVariation(false);
        $app1->setId(1);
        $app2 = new Application($licence, $this->getRefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION), 0);
        $app2->setIsVariation(true);
        $app2->setId(2);
        $licence->setApplications(new \Doctrine\Common\Collections\ArrayCollection([$app1, $app2]));

        $this->assertEquals(1, $licence->getNewApplications()[0]->getId());
    }

    protected function getRefData($id)
    {
        $refData = new RefData();
        $refData->setId($id);

        return $refData;
    }

    public function testGetCalculatedBundleValues()
    {
        $licence = m::mock(Entity::class)->makePartial();
        $licence->shouldReceive('getNiFlag')->andReturn('Y');

        $result = $licence->getCalculatedBundleValues();

        $expected = [
            'niFlag' => 'Y'
        ];

        $this->assertSame($expected, $result);
    }

    public function testGetCalculatedValues()
    {
        $licence = m::mock(Entity::class)->makePartial();

        $this->assertSame($licence->getCalculatedBundleValues(), $licence->getCalculatedValues());
    }

    public function testGetApplicationDocuments()
    {
        $licence = m::mock(Entity::class)->makePartial();
        $mockDocument1 = m::mock()
            ->shouldReceive('getcategory')
            ->andReturn('category')
            ->once()
            ->shouldReceive('getsubCategory')
            ->andReturn('subCategory')
            ->once()
            ->getMock();

        $mockDocument2 = m::mock()
            ->shouldReceive('getcategory')
            ->andReturn('category1')
            ->once()
            ->shouldReceive('getsubCategory')
            ->andReturn('subCategory1')
            ->never()
            ->getMock();

        $documentsCollection = new ArrayCollection([$mockDocument1, $mockDocument2]);
        $expected = new ArrayCollection([$mockDocument1]);

        $licence->setDocuments($documentsCollection);
        $this->assertEquals($expected, $licence->getLicenceDocuments('category', 'subCategory'));
    }

    public function testGetOutstandingOrganisations()
    {
        $licence = m::mock(Entity::class)->makePartial();

        $allApplications = m::mock(ArrayCollection::class);
        $outstandingApplications = m::mock(ArrayCollection::class);
        $licence->setApplications($allApplications);

        $allApplications->shouldReceive('matching')
            ->once()
            ->with(m::type(Criteria::class))
            ->andReturn($outstandingApplications);

        $this->assertEquals($outstandingApplications, $licence->getOutstandingApplications(true));
    }

    /**
     * @dataProvider firstApplicationIdProvider
     */
    public function testGetFirstApplicationId($status, $firstApplicationId)
    {
        $licence = m::mock(Entity::class)->makePartial();
        $licence->shouldReceive('getStatus')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->once()
                ->andReturn($status)
                ->getMock()
            )
            ->once()
            ->getMock();
        $application = m::mock(Entity::class)->makePartial();
        $application->shouldReceive('getId')
            ->andReturn($firstApplicationId)
            ->getMock();

        $applications = new ArrayCollection();
        $applications->add($application);
        $licence->setApplications($applications);
        $this->assertEquals($firstApplicationId, $licence->getFirstApplicationId());
    }

    public function firstApplicationIdProvider()
    {
        return [
            [Entity::LICENCE_STATUS_NOT_SUBMITTED, 1],
            [Entity::LICENCE_STATUS_VALID, null],
        ];
    }

    public function testGetApplicationsByStatus()
    {
        $licence = m::mock(Entity::class)->makePartial();

        $allApplications = m::mock(ArrayCollection::class);
        $outstandingApplications = m::mock(ArrayCollection::class);
        $licence->setApplications($allApplications);

        $allApplications->shouldReceive('matching')
            ->once()
            ->with(m::type(Criteria::class))
            ->andReturn($outstandingApplications);

        $this->assertEquals($outstandingApplications, $licence->getApplicationsByStatus(['foo']));
    }

    public function testGetTrafficAreaForTaskAllocationNonMlh()
    {
        $licence = m::mock(Entity::class)->makePartial()
            ->shouldReceive('getOrganisation')
            ->andReturn(
                m::mock()
                    ->shouldReceive('isMlh')
                    ->andReturn(false)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getTrafficArea')
            ->andReturn('B')
            ->once()
            ->shouldReceive('isGoods')
            ->andReturn(null)
            ->once()
            ->shouldReceive('isGoodsApplication')
            ->andReturn(true)
            ->once()
            ->getMock();

        $this->assertEquals('B', $licence->getTrafficAreaForTaskAllocation());
    }

    public function testGetTrafficAreaForTaskAllocationGoodsMlh()
    {
        $licence = m::mock(Entity::class)->makePartial()
            ->shouldReceive('getOrganisation')
            ->andReturn(
                m::mock()
                    ->shouldReceive('isMlh')
                    ->andReturn(true)
                    ->once()
                    ->shouldReceive('getLeadTcArea')
                    ->andReturn('B')
                    ->twice()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('isGoods')
            ->andReturn(true)
            ->twice()
            ->getMock();

        $this->assertEquals('B', $licence->getTrafficAreaForTaskAllocation());
    }

    public function testGetTrafficAreaForTaskAllocationGoodsMlhLeadTcEmpty()
    {
        $licence = m::mock(Entity::class)->makePartial()
            ->shouldReceive('getOrganisation')
            ->andReturn(
                m::mock()
                    ->shouldReceive('isMlh')
                    ->andReturn(true)
                    ->once()
                    ->shouldReceive('getLeadTcArea')
                    ->andReturn(null)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getTrafficArea')
            ->andReturn('B')
            ->once()
            ->shouldReceive('isGoods')
            ->andReturn(null)
            ->once()
            ->shouldReceive('isGoodsApplication')
            ->andReturn(true)
            ->once()
            ->getMock();

        $this->assertEquals('B', $licence->getTrafficAreaForTaskAllocation());
    }

    public function testIsGoodsApplication()
    {
        $application = m::mock()
            ->shouldReceive('isGoods')
            ->andReturn(true)
            ->getMock();

        $applications = new ArrayCollection();
        $applications->add($application);

        $licence = m::mock(Entity::class)->makePartial();
        $licence->shouldReceive('getStatus')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->once()
                    ->andReturn(Entity::LICENCE_STATUS_NOT_SUBMITTED)
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getApplications')
            ->andReturn($applications)
            ->once()
            ->getMock();

        $this->assertTrue($licence->isGoodsApplication());
    }

    /**
     * @dataProvider expiryDateProvider
     */
    public function testGetExpiryDateAsDate($expiryDate, $expected)
    {
        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial()
            ->shouldReceive('getExpiryDate')
            ->andReturn($expiryDate)
            ->once()
            ->getMock();

        $this->assertEquals($sut->getExpiryDateAsDate(), $expected);
    }

    public function expiryDateProvider()
    {
        $today = new DateTime('now');
        $date2017 = \DateTime::createFromFormat('Y-m-d', '2017-01-01');
        $date2017->setTime(0, 0, 0);
        $date2017->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        return [
            [
                null,
                null
            ],
            [
                $today,
                $today
            ],
            [
                '2017-01-01',
                $date2017
            ]
        ];
    }

    /**
     * @dataProvider getLatestBusVariationProvider
     *
     * @param $isEmpty
     * @param $expectedResult
     * @param $expectedCriteria
     * @param $statuses
     */
    public function testGetLatestBusVariation($isEmpty, $expectedResult, $expectedCriteria, $statuses)
    {
        $busRegCollection = m::mock(CollectionInterface::class);
        $matchedCollection = m::mock(CollectionInterface::class);
        $matchedCollection->shouldReceive('isEmpty')->times()->andReturn($isEmpty);
        $matchedCollection->shouldReceive('current')->times($isEmpty ? 0 : 1)->andReturn($expectedResult);

        $busRegCollection->shouldReceive('matching')
            ->once()
            ->with(m::type(Criteria::class))->andReturnUsing(
                function (Criteria $criteria) use ($matchedCollection, $expectedCriteria) {
                    $this->assertEquals($expectedCriteria, $criteria);
                    return $matchedCollection;
                }
            );

        /** @var Entity|\Mockery\MockInterface $licence */
        $licence = m::mock(Entity::class)->makePartial();
        $licence->shouldReceive('getBusRegs')->andReturn($busRegCollection);
        $this->assertEquals($expectedResult, $licence->getLatestBusVariation('1234567', $statuses));
    }

    /**
     * Data provider for testGetLatestBusVariation
     *
     * @return array
     */
    public function getLatestBusVariationProvider()
    {
        $mockBusReg = m::mock(BusRegEntity::class);

        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('regNo', '1234567'))
            ->orderBy(array('variationNo' => Criteria::DESC))
            ->setMaxResults(1);

        $notInStatus = ['status'];
        $criteriaWithStatus = clone $criteria;
        $criteriaWithStatus->andWhere(Criteria::expr()->notIn('status', $notInStatus));

        return [
            [true, null, $criteria, []],
            [false, $mockBusReg, $criteriaWithStatus, $notInStatus],
            [true, null, $criteriaWithStatus, $notInStatus],
            [false, $mockBusReg, $criteria, []]
        ];
    }

    /**
     * @dataProvider operatorLocationProvider
     */
    public function testGetOperatorLocation($niFlag, $expected)
    {
        $mockLicence = m::mock(Entity::class)->makePartial();
        $mockLicence->shouldReceive('getNiFlag')
            ->andReturn($niFlag)
            ->once()
            ->getMock();

        $this->assertEquals($expected, $mockLicence->getOperatorLocation());
    }

    public function operatorLocationProvider()
    {
        return [
            [
                'Y', 'Northern Ireland'
            ],
            [
                'N', 'Great Britain'
            ]
        ];
    }

    public function testGetOperatorType()
    {
        $mockLicence1 = m::mock(Entity::class)->makePartial();
        $mockLicence1->shouldReceive('isGoods')
            ->andReturn(true)
            ->once()
            ->getMock();

        $mockLicence2 = m::mock(Entity::class)->makePartial();
        $mockLicence2->shouldReceive('isGoods')
            ->andReturn(false)
            ->once()
            ->getMock();

        $this->assertEquals('Goods', $mockLicence1->getOperatorType());
        $this->assertEquals('PSV', $mockLicence2->getOperatorType());
    }

    /**
     * @dataProvider dpIsExpiredDataProvider
     *
     * @param bool     $expected   Is Expiring
     * @param DateTime $expiryDate Licence expiry date
     */
    public function testIsExpired($expected, $expiryDate)
    {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setExpiryDate($expiryDate);
        $this->assertSame($expected, $licence->isExpired());
    }

    public function dpIsExpiredDataProvider()
    {
        return [
            'Null expiry date' => [false, null],
            [true, (new DateTime())->setTime(0, 0, 0)->sub(new \DateInterval('P2Y4M'))],
            [true, (new DateTime())->setTime(0, 0, 0)->sub(new \DateInterval('P1Y'))],
            [true, (new DateTime())->setTime(0, 0, 0)->sub(new \DateInterval('P1M'))],
            [true, (new DateTime())->setTime(0, 0, 0)->sub(new \DateInterval('P1D'))],
            'Expiry is today' => [false, (new DateTime())->setTime(0, 0, 0)],
            [false, (new DateTime())->setTime(0, 0, 0)->add(new \DateInterval('P3M'))],
            [false, (new DateTime())->setTime(0, 0, 0)->add(new \DateInterval('P1Y'))],
        ];
    }

    /**
     * @dataProvider dpIsExpiringDataProvider
     *
     * @param bool     $expected   Is Expiring
     * @param DateTime $expiryDate Licence expiry date
     */
    public function testIsExpiringNoContinuation($expected, $expiryDate)
    {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setExpiryDate($expiryDate);
        $expected = false;
        $this->assertSame($expected, $licence->isExpiring());
    }

    /**
     * @dataProvider dpIsExpiringDataProvider
     *
     * @param bool     $expected   Is Expiring
     * @param DateTime $expiryDate Licence expiry date
     */
    public function testIsExpiringCurrentContinuation($expected, $expiryDate)
    {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setExpiryDate($expiryDate);

        $continuation = new Continuation();
        $continuation->setMonth($expiryDate->format('n'));
        $continuation->setYear($expiryDate->format('Y'));
        $continuationDetail1 = new ContinuationDetail();
        $continuationDetail1->setContinuation($continuation);
        $continuationDetail1->setStatus(new RefData(ContinuationDetail::STATUS_PRINTED));

        $continuation = new Continuation();
        $continuation->setMonth($expiryDate->format('n'));
        $continuation->setYear(2010);
        $continuationDetail2 = new ContinuationDetail();
        $continuationDetail2->setContinuation($continuation);
        $continuationDetail2->setStatus(new RefData(ContinuationDetail::STATUS_PRINTED));
        $licence->setContinuationDetails(new ArrayCollection([$continuationDetail2, $continuationDetail1]));

        $this->assertSame($expected, $licence->isExpiring());
    }

    public function dpIsExpiringDataProvider()
    {
        return [
            '-2 years 4 monsth' => [false, (new DateTime())->sub(new \DateInterval('P2Y4M'))],
            '-1 year' => [false, (new DateTime())->sub(new \DateInterval('P1Y'))],
            '-1 month' => [false, (new DateTime())->sub(new \DateInterval('P1M'))],
            '-1 day' => [false, (new DateTime())->sub(new \DateInterval('P1D'))],
            'Expiry is now' => [true, (new DateTime())],
            '+1 day' => [true, (new DateTime())->add(new \DateInterval('P1D'))],
            '+1 month' => [true, (new DateTime())->add(new \DateInterval('P1M'))],
            '+2 months' => [true, (new DateTime())->add(new \DateInterval('P2M'))],
            '+75 days' => [false, (new DateTime())->add(new \DateInterval('P75D'))],
            '+3 months' => [false, (new DateTime())->add(new \DateInterval('P3M'))],
            '+1 year' => [false, (new DateTime())->add(new \DateInterval('P1Y'))],
        ];
    }

    public function testGetActiveContinuationDetails()
    {
        $continuationDetail1 = new ContinuationDetail();
        $continuationDetail1->setStatus(new RefData(ContinuationDetail::STATUS_PRINTED));
        $continuationDetail2 = new ContinuationDetail();
        $continuationDetail2->setStatus(new RefData(ContinuationDetail::STATUS_ERROR));
        $continuationDetail3 = new ContinuationDetail();
        $continuationDetail3->setStatus(new RefData(ContinuationDetail::STATUS_UNACCEPTABLE));
        $continuationDetail4 = new ContinuationDetail();
        $continuationDetail4->setStatus(new RefData(ContinuationDetail::STATUS_PRINTING));
        $continuationDetail5 = new ContinuationDetail();
        $continuationDetail5->setStatus(new RefData(ContinuationDetail::STATUS_ACCEPTABLE));

        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setContinuationDetails(
            new ArrayCollection(
                [
                    $continuationDetail1,
                    $continuationDetail2,
                    $continuationDetail3,
                    $continuationDetail4,
                    $continuationDetail5,
                ]
            )
        );

        $activeContinuatioNDetails = $licence->getActiveContinuationDetails();

        $this->assertCount(3, $activeContinuatioNDetails);
        $this->assertSame($continuationDetail1, $activeContinuatioNDetails[0]);
        $this->assertSame($continuationDetail3, $activeContinuatioNDetails[2]);
        $this->assertSame($continuationDetail5, $activeContinuatioNDetails[4]);
    }

    public function testSerialize()
    {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);
        $serialized = $licence->serialize();

        $this->assertArrayNotHasKey('isExpired', $serialized);
        $this->assertArrayNotHasKey('isExpiring', $serialized);
    }

    public function testSerializeWithExpired()
    {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);
        $serialized = $licence->serialize(['isExpired']);

        $this->assertArrayHasKey('isExpired', $serialized);
        $this->assertArrayNotHasKey('isExpiring', $serialized);
    }

    public function testSerializeWithExpiring()
    {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);
        $serialized = $licence->serialize(['isExpiring']);

        $this->assertArrayNotHasKey('isExpired', $serialized);
        $this->assertArrayHasKey('isExpiring', $serialized);
    }

    public function testGetNotSubmittedOrUnderConsiderationVariations()
    {
        $app1 = m::mock()
            ->shouldReceive('getstatus')
            ->andReturn(Application::APPLICATION_STATUS_NOT_SUBMITTED)
            ->once()
            ->shouldReceive('getisVariation')
            ->andReturn(true)
            ->once()
            ->getMock();

        $app2 = m::mock()
            ->shouldReceive('getstatus')
            ->andReturn(Application::APPLICATION_STATUS_UNDER_CONSIDERATION)
            ->once()
            ->shouldReceive('getisVariation')
            ->andReturn(true)
            ->once()
            ->getMock();

        $app3 = m::mock()
            ->shouldReceive('getisVariation')
            ->andReturn(false)
            ->once()
            ->getMock();

        $applications = new ArrayCollection();
        $applications->add($app1);
        $applications->add($app2);
        $applications->add($app3);

        $licence = m::mock(Entity::class)->makePartial()
            ->shouldReceive('getApplications')
            ->andReturn($applications)
            ->once()
            ->getMock();

        $expected = new ArrayCollection();
        $expected->add($app1);
        $expected->add($app2);

        $result = $licence->getNotSubmittedOrUnderConsiderationVariations();

        $this->assertEquals($result, $expected);
    }

    public function testGetOcPendingChangesNoChanges()
    {
        $licence = m::mock(Entity::class)->makePartial()
            ->shouldReceive('getNotSubmittedOrUnderConsiderationVariations')
            ->andReturn(new ArrayCollection())
            ->once()
            ->getMock();

        $this->assertEquals(0, $licence->getOcPendingChanges());
    }

    public function testGetOcPendingChanges()
    {
        $operatingCentres = new ArrayCollection();
        $operatingCentres->add(['oc1']);

        $variation = m::mock()
            ->shouldReceive('getOperatingCentres')
            ->andReturn($operatingCentres)
            ->once()
            ->shouldReceive('getTotAuthTrailers')
            ->andReturn(2)
            ->once()
            ->shouldReceive('getTotAuthVehicles')
            ->andReturn(2)
            ->once()
            ->getMock();

        $variations = new ArrayCollection();
        $variations->add($variation);

        $licence = m::mock(Entity::class)->makePartial()
            ->shouldReceive('getNotSubmittedOrUnderConsiderationVariations')
            ->andReturn($variations)
            ->once()
            ->shouldReceive('getTotAuthTrailers')
            ->andReturn(1)
            ->once()
            ->shouldReceive('getTotAuthVehicles')
            ->andReturn(1)
            ->once()
            ->getMock();

        $this->assertEquals(3, $licence->getOcPendingChanges());
    }

    public function testGetTmPendingChangesNoChanges()
    {
        $licence = m::mock(Entity::class)->makePartial()
            ->shouldReceive('getNotSubmittedOrUnderConsiderationVariations')
            ->andReturn(new ArrayCollection())
            ->once()
            ->getMock();

        $this->assertEquals(0, $licence->getTmPendingChanges());
    }

    public function testGetTmPendingChanges()
    {
        $transportManagers = new ArrayCollection();
        $transportManagers->add(['tm1']);

        $variation = m::mock()
            ->shouldReceive('getTransportManagers')
            ->andReturn($transportManagers)
            ->once()
            ->getMock();

        $variations = new ArrayCollection();
        $variations->add($variation);

        $licence = m::mock(Entity::class)->makePartial()
            ->shouldReceive('getNotSubmittedOrUnderConsiderationVariations')
            ->andReturn($variations)
            ->once()
            ->getMock();

        $this->assertEquals(1, $licence->getTmPendingChanges());
    }

    public function testGetGroupedConditionsUndertakings()
    {
        $sut = new Licence(new OrganisationEntity(), new RefData(Entity::LICENCE_STATUS_VALID));

        $conditionType = new RefData(ConditionUndertaking::TYPE_CONDITION);
        $undertakingType = new RefData(ConditionUndertaking::TYPE_UNDERTAKING);
        $attachedToLicence = new RefData(ConditionUndertaking::ATTACHED_TO_LICENCE);
        $attachedToOc = new RefData(ConditionUndertaking::ATTACHED_TO_OPERATING_CENTRE);

        $licenceCondition1 = new ConditionUndertaking($conditionType, 'N', 'N');
        $licenceCondition1->setNotes('lic cond 1');
        $licenceCondition1->setCreatedOn(\DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-04 00:00:00'));
        $licenceCondition1->setAttachedTo($attachedToLicence);

        $licenceCondition2 = new ConditionUndertaking($conditionType, 'N', 'N');
        $licenceCondition2->setNotes('lic cond 2');
        $licenceCondition2->setCreatedOn(\DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-03 00:00:00'));
        $licenceCondition2->setAttachedTo($attachedToLicence);

        $licenceCondition3 = new ConditionUndertaking($conditionType, 'N', 'Y');
        $licenceCondition3->setNotes('lic cond 3');
        $licenceCondition3->setCreatedOn(\DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-02 00:00:00'));
        $licenceCondition3->setAttachedTo($attachedToLicence);

        $licenceCondition4 = new ConditionUndertaking($conditionType, 'Y', 'N');
        $licenceCondition4->setNotes('lic cond 4');
        $licenceCondition4->setCreatedOn(\DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-01 00:00:00'));
        $licenceCondition4->setAttachedTo($attachedToLicence);

        $licenceCondition5 = new ConditionUndertaking($conditionType, 'N', 'N');
        $licenceCondition5->setNotes('lic cond 5');
        $licenceCondition5->setCreatedOn(\DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-03 00:00:00'));
        $licenceCondition5->setAttachedTo($attachedToLicence);

        $licenceUndertaking1 = new ConditionUndertaking($undertakingType, 'N', 'N');
        $licenceUndertaking1->setNotes('lic und 1');
        $licenceUndertaking1->setCreatedOn(\DateTime::createFromFormat('Y-m-d H:i:s', '2016-01-04 00:00:00'));
        $licenceUndertaking1->setAttachedTo($attachedToLicence);

        $licenceUndertaking2 = new ConditionUndertaking($undertakingType, 'N', 'N');
        $licenceUndertaking2->setNotes('lic und 2');
        $licenceUndertaking2->setCreatedOn(\DateTime::createFromFormat('Y-m-d H:i:s', '2015-01-03 00:00:00'));
        $licenceUndertaking2->setAttachedTo($attachedToLicence);

        $address1 = new Address();
        $address1->setAddressLine1('line1');
        $address1->setTown('town');
        $address1->setPostcode('pc');
        $oc1 = new OperatingCentre();
        $oc1->setId(1);
        $oc1->setAddress($address1);

        $oc1Condition1 =  new ConditionUndertaking($conditionType, 'N', 'N');
        $oc1Condition1->setNotes('oc 1 cond 1');
        $oc1Condition1->setCreatedOn(\DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-04 00:00:00'));
        $oc1Condition1->setAttachedTo($attachedToOc);
        $oc1Condition1->setOperatingCentre($oc1);

        $oc1Condition2 =  new ConditionUndertaking($conditionType, 'N', 'N');
        $oc1Condition2->setNotes('oc 1 cond 2');
        $oc1Condition2->setCreatedOn(\DateTime::createFromFormat('Y-m-d H:i:s', '2016-01-04 00:00:00'));
        $oc1Condition2->setAttachedTo($attachedToOc);
        $oc1Condition2->setOperatingCentre($oc1);

        $oc1Undertaking1 =  new ConditionUndertaking($undertakingType, 'N', 'N');
        $oc1Undertaking1->setNotes('oc 1 und 1');
        $oc1Undertaking1->setCreatedOn(\DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-04 00:00:00'));
        $oc1Undertaking1->setAttachedTo($attachedToOc);
        $oc1Undertaking1->setOperatingCentre($oc1);

        $oc1Undertaking2 =  new ConditionUndertaking($undertakingType, 'N', 'N');
        $oc1Undertaking2->setNotes('oc 1 und 2');
        $oc1Undertaking2->setCreatedOn(\DateTime::createFromFormat('Y-m-d H:i:s', '2016-01-04 00:00:00'));
        $oc1Undertaking2->setAttachedTo($attachedToOc);
        $oc1Undertaking2->setOperatingCentre($oc1);

        $address2 = new Address();
        $address2->setAddressLine1('line12');
        $address2->setTown('town1');
        $address2->setPostcode('pc1');
        $oc2 = new OperatingCentre();
        $oc2->setId(2);
        $oc2->setAddress($address2);

        $oc2Condition1 =  new ConditionUndertaking($conditionType, 'N', 'N');
        $oc2Condition1->setNotes('oc 2 cond 1');
        $oc2Condition1->setCreatedOn(\DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-04 00:00:00'));
        $oc2Condition1->setAttachedTo($attachedToOc);
        $oc2Condition1->setOperatingCentre($oc2);

        $oc2Condition2 =  new ConditionUndertaking($conditionType, 'N', 'N');
        $oc2Condition2->setNotes('oc 2 cond 2');
        $oc2Condition2->setCreatedOn(\DateTime::createFromFormat('Y-m-d H:i:s', '2016-01-04 00:00:00'));
        $oc2Condition2->setAttachedTo($attachedToOc);
        $oc2Condition2->setOperatingCentre($oc2);

        $oc2Undertaking1 =  new ConditionUndertaking($undertakingType, 'N', 'N');
        $oc2Undertaking1->setNotes('oc 2 und 1');
        $oc2Undertaking1->setCreatedOn(\DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-04 00:00:00'));
        $oc2Undertaking1->setAttachedTo($attachedToOc);
        $oc2Undertaking1->setOperatingCentre($oc2);

        $oc2Undertaking2 =  new ConditionUndertaking($undertakingType, 'N', 'N');
        $oc2Undertaking2->setNotes('oc 2 und 2');
        $oc2Undertaking2->setCreatedOn(\DateTime::createFromFormat('Y-m-d H:i:s', '2016-01-04 00:00:00'));
        $oc2Undertaking2->setAttachedTo($attachedToOc);
        $oc2Undertaking2->setOperatingCentre($oc2);

        $conditionsUndertakings = new ArrayCollection();

        $conditionsUndertakings->add($licenceCondition1);
        $conditionsUndertakings->add($licenceCondition2);
        $conditionsUndertakings->add($licenceCondition3);
        $conditionsUndertakings->add($licenceCondition4);
        $conditionsUndertakings->add($licenceCondition5);
        $conditionsUndertakings->add($licenceUndertaking1);
        $conditionsUndertakings->add($licenceUndertaking2);
        $conditionsUndertakings->add($oc1Condition1);
        $conditionsUndertakings->add($oc1Condition2);
        $conditionsUndertakings->add($oc1Undertaking1);
        $conditionsUndertakings->add($oc1Undertaking2);
        $conditionsUndertakings->add($oc2Condition1);
        $conditionsUndertakings->add($oc2Condition2);
        $conditionsUndertakings->add($oc2Undertaking1);
        $conditionsUndertakings->add($oc2Undertaking2);

        $sut->setConditionUndertakings($conditionsUndertakings);

        $expected = [
            'licence' => [
                'conditions' => [
                    [
                        'notes' => 'lic cond 5',
                        'createdOn' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-03 00:00:00')
                    ],
                    [
                        'notes' => 'lic cond 2',
                        'createdOn' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-03 00:00:00')
                    ],
                    [
                        'notes' => 'lic cond 1',
                        'createdOn' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-04 00:00:00')
                    ],
                ],
                'undertakings' => [
                    [
                        'notes' => 'lic und 2',
                        'createdOn' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-01-03 00:00:00')
                    ],
                    [
                        'notes' => 'lic und 1',
                        'createdOn' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-01-04 00:00:00')
                    ],
                ]
            ],
            'operatingCentres' => [
                '1' => [
                    'conditions' => [
                        [
                            'notes' => 'oc 1 cond 2',
                            'createdOn' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-01-04 00:00:00'),
                            'address' => [
                                'addressLine1' => 'line1',
                                'addressLine2' => null,
                                'addressLine3' => null,
                                'addressLine4' => null,
                                'town' => 'town',
                                'postcode' => 'pc'
                            ]
                        ],
                        [
                            'notes' => 'oc 1 cond 1',
                            'createdOn' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-04 00:00:00'),
                            'address' => [
                                'addressLine1' => 'line1',
                                'addressLine2' => null,
                                'addressLine3' => null,
                                'addressLine4' => null,
                                'town' => 'town',
                                'postcode' => 'pc'
                            ]
                        ],
                    ],
                    'undertakings' => [
                        [
                            'notes' => 'oc 1 und 2',
                            'createdOn' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-01-04 00:00:00'),
                            'address' => [
                                'addressLine1' => 'line1',
                                'addressLine2' => null,
                                'addressLine3' => null,
                                'addressLine4' => null,
                                'town' => 'town',
                                'postcode' => 'pc'
                            ]
                        ],
                        [
                            'notes' => 'oc 1 und 1',
                            'createdOn' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-04 00:00:00'),
                            'address' => [
                                'addressLine1' => 'line1',
                                'addressLine2' => null,
                                'addressLine3' => null,
                                'addressLine4' => null,
                                'town' => 'town',
                                'postcode' => 'pc'
                            ]
                        ],
                    ]
                ],
                '2' => [
                    'conditions' => [
                        [
                            'notes' => 'oc 2 cond 2',
                            'createdOn' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-01-04 00:00:00'),
                            'address' => [
                                'addressLine1' => 'line12',
                                'addressLine2' => null,
                                'addressLine3' => null,
                                'addressLine4' => null,
                                'town' => 'town1',
                                'postcode' => 'pc1'
                            ]
                        ],
                        [
                            'notes' => 'oc 2 cond 1',
                            'createdOn' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-04 00:00:00'),
                            'address' => [
                                'addressLine1' => 'line12',
                                'addressLine2' => null,
                                'addressLine3' => null,
                                'addressLine4' => null,
                                'town' => 'town1',
                                'postcode' => 'pc1'
                            ]
                        ],
                    ],
                    'undertakings' => [
                        [
                            'notes' => 'oc 2 und 2',
                            'createdOn' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-01-04 00:00:00'),
                            'address' => [
                                'addressLine1' => 'line12',
                                'addressLine2' => null,
                                'addressLine3' => null,
                                'addressLine4' => null,
                                'town' => 'town1',
                                'postcode' => 'pc1'
                            ]
                        ],
                        [
                            'notes' => 'oc 2 und 1',
                            'createdOn' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-04 00:00:00'),
                            'address' => [
                                'addressLine1' => 'line12',
                                'addressLine2' => null,
                                'addressLine3' => null,
                                'addressLine4' => null,
                                'town' => 'town1',
                                'postcode' => 'pc1'
                            ]
                        ],
                    ]
                ]
            ]
        ];

        $this->assertEquals($expected, $sut->getGroupedConditionsUndertakings());
    }

    public function testGetLatestBusRouteNo()
    {
        /** @var BusRegEntity|m\Mock $busReg */
        $busReg = m::mock(BusRegEntity::class)->makePartial();
        $busReg->setRouteNo(52);

        $licence = m::mock(Entity::class)->makePartial();
        $licence->shouldReceive('getBusRegs->matching->current')->with()->twice()->andReturn($busReg);

        $this->assertSame(52, $licence->getLatestBusRouteNo());
    }

    public function testGetLatestBusRouteNoNull()
    {
        $licence = m::mock(Entity::class)->makePartial();
        $licence->shouldReceive('getBusRegs->matching->current')->with()->once()->andReturn(false);

        $this->assertSame(0, $licence->getLatestBusRouteNo());
    }

    public function testGetActiveCommunityLicences()
    {
        $licence = new Licence(m::mock(OrganisationEntity::class), m::mock(RefData::class));

        $communityLicStatuses = [
            CommunityLicEntity::STATUS_ANNUL,
            CommunityLicEntity::STATUS_ACTIVE,
            CommunityLicEntity::STATUS_EXPIRED,
            CommunityLicEntity::STATUS_PENDING,
            CommunityLicEntity::STATUS_RETURNDED,
            CommunityLicEntity::STATUS_SUSPENDED,
            CommunityLicEntity::STATUS_WITHDRAWN,
        ];

        $communityLics = [];
        foreach ($communityLicStatuses as $status) {
            $communityLic = new CommunityLicEntity();
            $communityLic->setIssueNo(0);
            $communityLic->setStatus($status);
            $communityLics[$status .'-'. 0] = $communityLic;

            $communityLic = new CommunityLicEntity();
            $communityLic->setIssueNo(1);
            $communityLic->setStatus($status);
            $communityLics[$status .'-'. 1] = $communityLic;
        }
        $licence->setCommunityLics(new ArrayCollection($communityLics));

        $result = $licence->getActiveCommunityLicences();

        $this->assertCount(3, $result);
        $this->assertTrue($result->contains($communityLics[CommunityLicEntity::STATUS_ACTIVE .'-1']));
        $this->assertTrue($result->contains($communityLics[CommunityLicEntity::STATUS_PENDING .'-1']));
        $this->assertTrue($result->contains($communityLics[CommunityLicEntity::STATUS_SUSPENDED .'-1']));
    }

    public function testGetActiveVariations()
    {
        $licence = new Licence(m::mock(OrganisationEntity::class), m::mock(RefData::class));

        $applicationStatuses = [
            Application::APPLICATION_STATUS_CANCELLED,
            Application::APPLICATION_STATUS_CURTAILED,
            Application::APPLICATION_STATUS_GRANTED,
            Application::APPLICATION_STATUS_NOT_SUBMITTED,
            Application::APPLICATION_STATUS_NOT_TAKEN_UP,
            Application::APPLICATION_STATUS_REFUSED,
            Application::APPLICATION_STATUS_UNDER_CONSIDERATION,
            Application::APPLICATION_STATUS_VALID,
            Application::APPLICATION_STATUS_WITHDRAWN,
        ];

        $applications = [];
        foreach ($applicationStatuses as $status) {
            $application = m::mock(Application::class)->makePartial();
            $application->setIsVariation(false);
            $application->setStatus($status);
            $applications[$status .'-'. 0] = $application;

            $application = m::mock(Application::class)->makePartial();
            $application->setIsVariation(true);
            $application->setStatus($status);
            $applications[$status .'-'. 1] = $application;
        }
        $licence->setApplications(new ArrayCollection($applications));

        $result = $licence->getActiveVariations();

        $this->assertCount(1, $result);
        $this->assertTrue($result->contains($applications[Application::APPLICATION_STATUS_UNDER_CONSIDERATION .'-1']));
    }

    /**
     * @dataProvider dataProviderTestDetermineNpNumber
     */
    public function testDetermineNpNumber($expected, $publication)
    {
        $licence = m::mock(Entity::class)->makePartial();
        $licence->shouldReceive('getLatestPublicationByType')->with(Publication::PUB_TYPE_N_P)->once()
            ->andReturn($publication);

        $this->assertSame($expected, $licence->determineNpNumber());
    }

    public function dataProviderTestDetermineNpNumber()
    {
        $publication = m::mock(Publication::class);
        $publication->shouldReceive('getPublicationNo')->with()->andReturn(99);
        return [
            [99, $publication],
            [null, 'X'],
            [null, new \stdClass()]
        ];
    }

    public function testGetPiRecordCount()
    {
        $licence = $this->instantiate(Entity::class);
        $case1 = m::mock(CaseEntity::class)->makePartial();
        $case1->setPublicInquiry('FOO');
        $case2 = m::mock(CaseEntity::class)->makePartial();
        $case3 = m::mock(CaseEntity::class)->makePartial();
        $case3->setPublicInquiry('BAR');
        $licence->setCases(new ArrayCollection([$case1, $case2, $case3]));

        $this->assertSame(2, $licence->getPiRecordCount());
    }

    /**
     * @dataProvider dataProviderTestAllowFeePayments
     */
    public function testAllowFeePayments($expected, $licenceStatusId)
    {
        $licence = $this->instantiate(Entity::class);
        $licence->setStatus(new RefData($licenceStatusId));
        $this->assertSame($expected, $licence->allowFeePayments());
    }

    public function dataProviderTestAllowFeePayments()
    {
        return [
            [true, Licence::LICENCE_STATUS_CANCELLED],
            [true, Licence::LICENCE_STATUS_UNDER_CONSIDERATION],
            [true, Licence::LICENCE_STATUS_NOT_SUBMITTED],
            [true, Licence::LICENCE_STATUS_SUSPENDED],
            [true, Licence::LICENCE_STATUS_VALID],
            [true, Licence::LICENCE_STATUS_CURTAILED],
            [true, Licence::LICENCE_STATUS_GRANTED],
            [false, Licence::LICENCE_STATUS_SURRENDERED],
            [false, Licence::LICENCE_STATUS_WITHDRAWN],
            [false, Licence::LICENCE_STATUS_REFUSED],
            [false, Licence::LICENCE_STATUS_REVOKED],
            [false, Licence::LICENCE_STATUS_NOT_TAKEN_UP],
            [false, Licence::LICENCE_STATUS_TERMINATED],
            [false, Licence::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT],
            [true, Licence::LICENCE_STATUS_UNLICENSED],
            [true, Licence::LICENCE_STATUS_CANCELLED],
        ];
    }

    public function testGetConditionUndertakingsAddedViaLicence()
    {
        /** @var Licence $licence */
        $licence = $this->instantiate(Entity::class);

        /** @var ConditionUndertaking $cu1 */
        $cu1 = m::mock(ConditionUndertaking::class)->makePartial();
        $cu1->setAddedVia(new RefData(ConditionUndertaking::ADDED_VIA_APPLICATION));
        $cu2 = m::mock(ConditionUndertaking::class)->makePartial();
        $cu2->setAddedVia(new RefData(ConditionUndertaking::ADDED_VIA_CASE));
        $cu3 = m::mock(ConditionUndertaking::class)->makePartial();
        $cu3->setAddedVia(new RefData(ConditionUndertaking::ADDED_VIA_LICENCE));

        $licence->setConditionUndertakings(new ArrayCollection([$cu1, $cu2, $cu3]));

        $result = $licence->getConditionUndertakingsAddedViaLicence();
        $this->assertCount(1, $result);
        $this->assertSame($cu3, $result->current());
    }

    public function testGetConditionUndertakingsAddedViaImport()
    {
        /** @var Licence $licence */
        $licence = $this->instantiate(Entity::class);

        /** @var ConditionUndertaking $cu1 */
        $cu1 = m::mock(ConditionUndertaking::class)->makePartial();
        $cu1->setAddedVia(new RefData(ConditionUndertaking::ADDED_VIA_APPLICATION));
        $cu1->setApplication(m::mock(Application::class));
        $cu2 = m::mock(ConditionUndertaking::class)->makePartial();
        $cu2->setAddedVia(new RefData(ConditionUndertaking::ADDED_VIA_CASE));
        $cu3 = m::mock(ConditionUndertaking::class)->makePartial();
        $cu3->setAddedVia(new RefData(ConditionUndertaking::ADDED_VIA_LICENCE));
        $cu4 = m::mock(ConditionUndertaking::class)->makePartial();
        $cu4->setAddedVia(new RefData(ConditionUndertaking::ADDED_VIA_APPLICATION));

        $licence->setConditionUndertakings(new ArrayCollection([$cu1, $cu2, $cu3, $cu4]));

        $result = $licence->getConditionUndertakingsAddedViaImport();
        $this->assertCount(1, $result);
        $this->assertSame($cu4, $result->current());
    }

    /**
     * @dataProvider dataProviderTestGetNiFlag
     */
    public function testGetNiFlag($expected, $trafficArea)
    {
        /** @var Licence $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setTrafficArea($trafficArea);

        $this->assertSame($expected, $licence->getNiFlag());
    }

    /**
     * @dataProvider dataProviderTestGetNiFlag
     */
    public function testIsNi($expected, $trafficArea)
    {
        /** @var Licence $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setTrafficArea($trafficArea);

        $this->assertSame($expected === 'Y', $licence->isNi());
    }

    public function dataProviderTestGetNiFlag()
    {
        return [
            ['Y', (new TrafficArea())->setIsNi(true)],
            ['N', (new TrafficArea())->setIsNi(false)],
            ['N', null],
        ];
    }

    public function testGetLatestPublicationByType()
    {
        /** @var Publication $publicationNp */
        $publicationNp1 = m::mock(Publication::class)->makePartial();
        $publicationNp1->setPubType(new RefData(Publication::PUB_TYPE_N_P));
        $publicationNp1->setPubDate('2017-10-02 12:45');
        $publicationNp2 = m::mock(Publication::class)->makePartial();
        $publicationNp2->setPubType(new RefData(Publication::PUB_TYPE_N_P));
        $publicationNp2->setPubDate('2017-10-03 12:45');
        $publicationAd1 = m::mock(Publication::class)->makePartial();
        $publicationAd1->setPubType(new RefData(Publication::PUB_TYPE_A_D));
        $publicationAd1->setPubDate('2017-09-02 12:45');
        $publicationAd2 = m::mock(Publication::class)->makePartial();
        $publicationAd2->setPubType(new RefData(Publication::PUB_TYPE_A_D));
        $publicationAd2->setPubDate('2017-09-02 13:45');

        /** @var PublicationLink $publicationLink1 */
        $publicationLink1 = m::mock(PublicationLink::class)->makePartial();
        $publicationLink1->setPublication($publicationNp1);
        $publicationLink2 = m::mock(PublicationLink::class)->makePartial();
        $publicationLink2->setPublication($publicationNp2);
        $publicationLink3 = m::mock(PublicationLink::class)->makePartial();
        $publicationLink3->setPublication($publicationAd1);
        $publicationLink4 = m::mock(PublicationLink::class)->makePartial();
        $publicationLink4->setPublication($publicationAd2);

        /** @var Licence $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setPublicationLinks(
            new ArrayCollection([$publicationLink1, $publicationLink2, $publicationLink3, $publicationLink4])
        );

        $this->assertSame(
            $publicationAd2,
            $licence->getLatestPublicationByType(new RefData(Publication::PUB_TYPE_A_D))
        );
        $this->assertSame(
            $publicationNp2,
            $licence->getLatestPublicationByType(new RefData(Publication::PUB_TYPE_N_P))
        );
    }

    public function testGetLatestPublicationByTypeNull()
    {
        /** @var Publication $publicationNp */
        $publicationNp1 = m::mock(Publication::class)->makePartial();
        $publicationNp1->setPubType(new RefData(Publication::PUB_TYPE_N_P));
        $publicationNp1->setPubDate('2017-10-02 12:45');
        $publicationNp2 = m::mock(Publication::class)->makePartial();
        $publicationNp2->setPubType(new RefData(Publication::PUB_TYPE_N_P));
        $publicationNp2->setPubDate('2017-10-03 12:45');

        /** @var PublicationLink $publicationLink1 */
        $publicationLink1 = m::mock(PublicationLink::class)->makePartial();
        $publicationLink1->setPublication($publicationNp1);
        $publicationLink2 = m::mock(PublicationLink::class)->makePartial();
        $publicationLink2->setPublication($publicationNp2);

        /** @var Licence $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setPublicationLinks(
            new ArrayCollection([$publicationLink1, $publicationLink2])
        );

        $this->assertNull(
            $licence->getLatestPublicationByType(new RefData(Publication::PUB_TYPE_A_D))
        );
    }

    public function testGetLocByOc()
    {
        $oc1 = m::mock(OperatingCentre::class);
        $oc2 = m::mock(OperatingCentre::class);
        $oc3 = m::mock(OperatingCentre::class);
        $loc1 = m::mock(LicenceOperatingCentre::class)->makePartial()->setOperatingCentre($oc1);
        $loc2 = m::mock(LicenceOperatingCentre::class)->makePartial()->setOperatingCentre($oc2);
        $loc3 = m::mock(LicenceOperatingCentre::class)->makePartial()->setOperatingCentre($oc2);

        /** @var Licence $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setOperatingCentres(new ArrayCollection([$loc1, $loc2, $loc3]));
        $this->assertSame($loc2, $licence->getLocByOc($oc2));
        $this->assertSame($loc1, $licence->getLocByOc($oc1));
        $this->assertNull($licence->getLocByOc($oc3));
    }

    /**
     * @dataProvider dataProviderTestHasQueuedRevocation
     */
    public function testHasQueuedRevocation($expected, ArrayCollection $licenceStatusRules)
    {
        /** @var Licence $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setLicenceStatusRules($licenceStatusRules);
        $this->assertSame($expected, $licence->hasQueuedRevocation());
    }

    public function dataProviderTestHasQueuedRevocation()
    {
        $lsr1 = m::mock(LicenceStatusRule::class)->makePartial()
            ->setLicenceStatus(new RefData(Licence::LICENCE_STATUS_VALID));
        $lsr1->shouldReceive('isQueued')->with()->andReturn(false);
        $lsr2 = m::mock(LicenceStatusRule::class)->makePartial()
            ->setLicenceStatus(new RefData(Licence::LICENCE_STATUS_GRANTED));
        $lsr2->shouldReceive('isQueued')->with()->andReturn(false);
        $lsr3 = m::mock(LicenceStatusRule::class)->makePartial()
            ->setLicenceStatus(new RefData(Licence::LICENCE_STATUS_REVOKED));
        $lsr3->shouldReceive('isQueued')->with()->andReturn(false);
        $lsr4 = m::mock(LicenceStatusRule::class)->makePartial()
            ->setLicenceStatus(new RefData(Licence::LICENCE_STATUS_REVOKED));
        $lsr4->shouldReceive('isQueued')->with()->andReturn(true);

        return [
            [true, new ArrayCollection([$lsr1, $lsr2, $lsr3, $lsr4])],
            [true, new ArrayCollection([$lsr4])],
            [false, new ArrayCollection()],
            [false, new ArrayCollection([$lsr1, $lsr2, $lsr3])],
        ];
    }

    public function testGetRelatedOrganisation()
    {
        $organisation = m::mock(OrganisationEntity::class);

        /** @var Licence $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setOrganisation($organisation);

        $this->assertSame($organisation, $licence->getRelatedOrganisation());
    }

    /**
     * @dataProvider dpTestCanMakeIrhpApplication
     */
    public function testCanMakeIrhpApplication(
        $irhpPermitType,
        $irhpApplications,
        $exclude,
        $isEligibleForPermits,
        $expected
    ) {
        /** @var IrhpPermitType $type */
        $type = m::mock(IrhpPermitType::class)->makePartial();
        $type->shouldReceive('getId')->andReturn($irhpPermitType);

        /** @var Entity $licence */
        $licence = m::mock(Entity::class)->makePartial();
        $licence->shouldReceive('isEligibleForPermits')->andReturn($isEligibleForPermits);
        $licence->shouldReceive('getIrhpApplications')->andReturn($irhpApplications);

        $this->assertEquals($expected, $licence->canMakeIrhpApplication($type, $exclude));
    }

    public function dpTestCanMakeIrhpApplication()
    {
        $activeBilateralIrhpApp = m::mock(IrhpApplication::class);
        $activeBilateralIrhpApp->shouldReceive('getId')
            ->andReturn(10)
            ->shouldReceive('isActive')
            ->andReturn(true)
            ->shouldReceive('getIrhpPermitType->getId')
            ->andReturn(IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL);

        $inactiveBilateralIrhpApp = m::mock(IrhpApplication::class);
        $inactiveBilateralIrhpApp->shouldReceive('getId')
            ->andReturn(11)
            ->shouldReceive('isActive')
            ->andReturn(false)
            ->shouldReceive('getIrhpPermitType->getId')
            ->andReturn(IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL);

        $activeMultilateralIrhpApp = m::mock(IrhpApplication::class);
        $activeMultilateralIrhpApp->shouldReceive('getId')
            ->andReturn(20)
            ->shouldReceive('isActive')
            ->andReturn(true)
            ->shouldReceive('getIrhpPermitType->getId')
            ->andReturn(IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL);

        $inactiveMultilateralIrhpApp = m::mock(IrhpApplication::class);
        $inactiveMultilateralIrhpApp->shouldReceive('getId')
            ->andReturn(21)
            ->shouldReceive('isActive')
            ->andReturn(false)
            ->shouldReceive('getIrhpPermitType->getId')
            ->andReturn(IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL);

        return [
            'no IRHP apps - not eligible for permits' => [
                'irhpPermitType' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
                'irhpApplications' => new ArrayCollection(),
                'exclude' => null,
                'isEligibleForPermits' => false,
                'expected' => false,
            ],
            'no IRHP apps - eligible for permits' => [
                'irhpPermitType' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
                'irhpApplications' => new ArrayCollection(),
                'exclude' => null,
                'isEligibleForPermits' => true,
                'expected' => true,
            ],
            'all inactive IRHP apps - eligible for permits' => [
                'irhpPermitType' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
                'irhpApplications' => new ArrayCollection([$inactiveMultilateralIrhpApp, $inactiveBilateralIrhpApp]),
                'exclude' => null,
                'isEligibleForPermits' => true,
                'expected' => true,
            ],
            'active IRHP app of different type - eligible for permits' => [
                'irhpPermitType' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
                'irhpApplications' => new ArrayCollection([$activeMultilateralIrhpApp, $inactiveBilateralIrhpApp]),
                'exclude' => null,
                'isEligibleForPermits' => true,
                'expected' => true,
            ],
            'active IRHP app of the same type - eligible for permits' => [
                'irhpPermitType' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
                'irhpApplications' => new ArrayCollection([$activeMultilateralIrhpApp, $activeBilateralIrhpApp]),
                'exclude' => null,
                'isEligibleForPermits' => true,
                'expected' => false,
            ],
            'active IRHP app of the same type but excluded - eligible for permits' => [
                'irhpPermitType' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
                'irhpApplications' => new ArrayCollection([$activeMultilateralIrhpApp, $activeBilateralIrhpApp]),
                'exclude' => $activeBilateralIrhpApp,
                'isEligibleForPermits' => true,
                'expected' => true,
            ],
        ];
    }

    /**
     * @dataProvider dpTestHasActiveIrhpApplication
     */
    public function testHasActiveIrhpApplication(
        $irhpPermitType,
        $irhpApplications,
        $exclude,
        $expected
    ) {
        /** @var IrhpPermitType $type */
        $type = m::mock(IrhpPermitType::class)->makePartial();
        $type->shouldReceive('getId')->andReturn($irhpPermitType);

        /** @var Entity $licence */
        $licence = m::mock(Entity::class)->makePartial();
        $licence->shouldReceive('getIrhpApplications')->andReturn($irhpApplications);

        $this->assertEquals($expected, $licence->hasActiveIrhpApplication($type, $exclude));
    }

    public function dpTestHasActiveIrhpApplication()
    {
        $activeBilateralIrhpApp = m::mock(IrhpApplication::class);
        $activeBilateralIrhpApp->shouldReceive('getId')
            ->andReturn(10)
            ->shouldReceive('isActive')
            ->andReturn(true)
            ->shouldReceive('getIrhpPermitType->getId')
            ->andReturn(IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL);

        $inactiveBilateralIrhpApp = m::mock(IrhpApplication::class);
        $inactiveBilateralIrhpApp->shouldReceive('getId')
            ->andReturn(11)
            ->shouldReceive('isActive')
            ->andReturn(false)
            ->shouldReceive('getIrhpPermitType->getId')
            ->andReturn(IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL);

        $activeMultilateralIrhpApp = m::mock(IrhpApplication::class);
        $activeMultilateralIrhpApp->shouldReceive('getId')
            ->andReturn(20)
            ->shouldReceive('isActive')
            ->andReturn(true)
            ->shouldReceive('getIrhpPermitType->getId')
            ->andReturn(IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL);

        $inactiveMultilateralIrhpApp = m::mock(IrhpApplication::class);
        $inactiveMultilateralIrhpApp->shouldReceive('getId')
            ->andReturn(21)
            ->shouldReceive('isActive')
            ->andReturn(false)
            ->shouldReceive('getIrhpPermitType->getId')
            ->andReturn(IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL);

        return [
            'no IRHP apps' => [
                'irhpPermitType' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
                'irhpApplications' => new ArrayCollection(),
                'exclude' => null,
                'expected' => false,
            ],
            'all inactive IRHP apps' => [
                'irhpPermitType' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
                'irhpApplications' => new ArrayCollection([$inactiveMultilateralIrhpApp, $inactiveBilateralIrhpApp]),
                'exclude' => null,
                'expected' => false,
            ],
            'active IRHP app of different type' => [
                'irhpPermitType' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
                'irhpApplications' => new ArrayCollection([$activeMultilateralIrhpApp, $inactiveBilateralIrhpApp]),
                'exclude' => null,
                'expected' => false,
            ],
            'active IRHP app of the same type' => [
                'irhpPermitType' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
                'irhpApplications' => new ArrayCollection([$activeMultilateralIrhpApp, $activeBilateralIrhpApp]),
                'exclude' => null,
                'expected' => true,
            ],
            'active IRHP app of the same type but excluded' => [
                'irhpPermitType' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
                'irhpApplications' => new ArrayCollection([$activeMultilateralIrhpApp, $activeBilateralIrhpApp]),
                'exclude' => $activeBilateralIrhpApp,
                'expected' => false,
            ],
        ];
    }
}
