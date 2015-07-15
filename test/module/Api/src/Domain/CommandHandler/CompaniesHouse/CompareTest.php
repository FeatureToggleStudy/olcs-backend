<?php

/**
 * Companies House Compare Command Handler Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\CompaniesHouse;

use Dvsa\Olcs\Api\Domain\Command\CompaniesHouse\Compare as Cmd;
use Dvsa\Olcs\Api\Domain\Command\CompaniesHouse\CreateAlert as CreateAlertCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\CompaniesHouse\Compare;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\CompaniesHouseCompany as CompanyRepo;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseAlert as AlertEntity;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseCompany as CompanyEntity;
use Dvsa\Olcs\CompaniesHouse\Service\Client as CompaniesHouseApi;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\CommandHandlerTestCase;
use Mockery as m;

/**
 *  Companies House Compare Command Handler Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class CompareTest extends CommandHandlerTestCase
{
    protected $mockApi;

    public function setUp()
    {
        $this->mockApi = m::mock(CompaniesHouseApi::class);
        $this->mockedSmServices = [
            CompaniesHouseApi::class => $this->mockApi,
        ];
        $this->sut = new Compare();
        $this->mockRepo('CompaniesHouseCompany', CompanyRepo::class);

        parent::setUp();
    }

    /**
     * Test process method with no changes
     *
     * @dataProvider noChangesProvider
     */
    public function testProcessNoChanges($companyNumber, $stubResponse, $stubSavedData)
    {
        // expectations
        $this->mockApi
            ->shouldReceive('getCompanyProfile')
            ->once()
            ->with($companyNumber, true)
            ->andReturn($stubResponse);

        /** @var CompanyEntity $company */
        $company = new CompanyEntity($stubSavedData);
        $this->repoMap['CompaniesHouseCompany']
            ->shouldReceive('getLatestByCompanyNumber')
            ->once()
            ->with($companyNumber)
            ->andReturn($company);

        // invoke
        $command = Cmd::create(['companyNumber' => $companyNumber]);
        $result = $this->sut->handleCommand($command);

        // assertions
        $this->assertEquals(['No changes'], $result->getMessages());
    }

    /**
     * Test process method when company not found
     */
    public function testProcessCompanyNotFound()
    {
        // data
        $companyNumber = '01234567';

        $expectedAlertData = [
            'companyNumber' => $companyNumber,
            'reasons' => [
                AlertEntity::REASON_INVALID_COMPANY_NUMBER,
            ],
        ];

        $alertResult = new Result();
        $alertResult
            ->addId('companiesHouseAlert', 101)
            ->addMessage('Alert created');

        // expectations
        $this->mockApi
            ->shouldReceive('getCompanyProfile')
            ->once()
            ->with($companyNumber, true)
            ->andReturn(['not found!']);

        $this->expectedSideEffect(CreateAlertCmd::class, $expectedAlertData, $alertResult);

        // invoke
        $command = Cmd::create(['companyNumber' => $companyNumber]);
        $result = $this->sut->handleCommand($command);

        // assertions
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(['Alert created'], $result->getMessages());
        $this->assertEquals(['companiesHouseAlert' => 101], $result->getIds());
    }

    /**
     * Test process method when company was not previously stored
     *
     * @dataProvider firstTimeProvider
     */
    public function testProcessCompanyFirstTimeFound($companyNumber, $stubResponse, $expectedSaveData)
    {
        // expectations
        $this->mockApi
            ->shouldReceive('getCompanyProfile')
            ->once()
            ->with($companyNumber, true)
            ->andReturn($stubResponse);

        $this->repoMap['CompaniesHouseCompany']
            ->shouldReceive('getLatestByCompanyNumber')
            ->once()
            ->with($companyNumber)
            ->andThrow(new NotFoundException('company not found'));

        // invoke
        /** @var CompanyEntity $company */
        $company = null;
        $this->repoMap['CompaniesHouseCompany']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(CompanyEntity::class))
            ->andReturnUsing(
                function (CompanyEntity $co) use (&$company) {
                    $company = $co;
                    $co->setId(69);
                }
            );

        // invoke
        $command = Cmd::create(['companyNumber' => $companyNumber]);
        $result = $this->sut->handleCommand($command);

        // assertions
        $this->assertEquals($expectedSaveData, $company->toArray());
        $this->assertEquals(['companiesHouseCompany' => 69], $result->getIds());
        $this->assertEquals(['Saved new company'], $result->getMessages());
    }

    public function noChangesProvider()
    {
        return array(
            'no changes' => array(
                'companyNumber' => '03127414',
                'stubResponse' => array(
                    'registered_office_address' => array(
                        'address_line_1' => '120 Aldersgate Street',
                        'address_line_2' => 'London',
                        'postal_code' => 'EC1A 4JQ',
                    ),
                    'last_full_members_list_date' => '2014-11-17',
                    'accounts' => array(
                        'next_due' => '2015-09-30',
                        'last_accounts' => array(
                            'type' => 'full',
                            'made_up_to' => '2013-12-31',
                        ),
                        'accounting_reference_date' => array(
                            'day' => '31',
                            'month' => '12',
                        ),
                        'next_made_up_to' => '2014-12-31',
                        'overdue' => false,
                    ),
                    'date_of_creation' => '1995-11-17',
                    'sic_codes' => array(
                        0 => '62020',
                    ),
                    'undeliverable_registered_office_address' => false,
                    'annual_return' => array(
                        'next_due' => '2015-12-15',
                        'overdue' => false,
                        'next_made_up_to' => '2015-11-17',
                        'last_made_up_to' => '2014-11-17',
                    ),
                    'company_name' => 'VALTECH LIMITED',
                    'jurisdiction' => 'england-wales',
                    'company_number' => '03127414',
                    'type' => 'ltd',
                    'has_been_liquidated' => false,
                    'has_insolvency_history' => false,
                    'etag' => 'ec52ec76d16210d1133df1b4c9bb8f797a38d09c',
                    'officer_summary' => array(
                        'resigned_count' => 17,
                        'officers' => array(
                            0 => array(
                                'officer_role' => 'director',
                                'name' => 'DILLON, Andrew',
                                'date_of_birth' => [
                                    'year' => '1979',
                                    'month' => '02',
                                ],
                            ),
                            1 => array(
                                'officer_role' => 'director',
                                'name' => 'HALL, Philip',
                                'date_of_birth' =>  [
                                    'year' => '1968',
                                    'month' => '12',
                                ],
                            ),
                            2 => array(
                                'officer_role' => 'director',
                                'name' => 'SKINNER, Mark James',
                                'date_of_birth' =>  [
                                    'year' => '1969',
                                    'month' => '06',
                                ],
                            ),
                        ),
                        'active_count' => 3,
                    ),
                    'company_status' => 'active',
                    'can_file' => true,
                ),
                'stubSavedData' => array(
                    'addressLine1' => '120 Aldersgate Street',
                    'addressLine2' => 'London',
                    'companyName' => 'VALTECH LIMITED',
                    'companyNumber' => '03127414',
                    'locality' => null,
                    'poBox' => null,
                    'postalCode' => 'EC1A 4JQ',
                    'premises' => null,
                    'region' => null,
                    'id' => 2,
                    'version' => 1,
                    'officers' => array(
                        array(
                            'dateOfBirth' => new \DateTime('1979-02-01'),
                            'name' => 'DILLON, Andrew',
                            'role' => 'director'
                        ),
                        array(
                            'dateOfBirth' => new \DateTime('1968-12-01'),
                            'name' => 'HALL, Philip',
                            'role' => 'director',
                        ),
                        array (
                            'dateOfBirth' => new \DateTime('1969-06-01'),
                            'name' => 'SKINNER, Mark James',
                            'role' => 'director',
                        ),
                    ),
                    'companyStatus' => 'active',
                    'country' => null,
                )
            ),
        );
    }

    public function firstTimeProvider()
    {
        return array(
            array(
                'companyNumber' => '03127414',
                'stubResponse' => array(
                    'registered_office_address' => array(
                        'address_line_1' => '120 Aldersgate Street',
                        'address_line_2' => 'London',
                        'postal_code' => 'EC1A 4JQ',
                    ),
                    'company_name' => 'VALTECH LIMITED',
                    'company_number' => '03127414',
                    'officer_summary' => array(
                        'resigned_count' => 17,
                        'officers' => array(
                            0 => array(
                                'officer_role' => 'director',
                                'name' => 'DILLON, Andrew',
                                'date_of_birth' => [
                                    'year' => '1979',
                                    'month' => '02',
                                ],
                            ),
                            1 => array(
                                'officer_role' => 'director',
                                'name' => 'HALL, Philip',
                                'date_of_birth' => [
                                    'year' => '1968',
                                    'month' => '12',
                                ],
                            ),
                            2 => array(
                                'officer_role' => 'director',
                                'name' => 'SKINNER, Mark James',
                                'date_of_birth' => [
                                    'year' => '1969',
                                    'month' => '06',
                                ],
                            ),
                        ),
                        'active_count' => 3,
                    ),
                    'company_status' => 'active',
                ),
                'expectedSaveData' => array(
                    'companyName' => 'VALTECH LIMITED',
                    'companyNumber' => '03127414',
                    'companyStatus' => 'active',
                    'addressLine1' => '120 Aldersgate Street',
                    'addressLine2' => 'London',
                    'postalCode' => 'EC1A 4JQ',
                    'officers' => array(
                        array(
                          'name' => 'DILLON, Andrew',
                          'role' => 'director',
                          'dateOfBirth' => new \DateTime('1979-02-01'),
                        ),
                        array(
                          'name' => 'HALL, Philip',
                          'role' => 'director',
                          'dateOfBirth' => new \DateTime('1968-12-01'),
                        ),
                        array(
                          'name' => 'SKINNER, Mark James',
                          'role' => 'director',
                          'dateOfBirth' => new \DateTime('1969-06-01'),
                        ),
                    ),
                    'country' => null,
                    'locality' => null,
                    'poBox' => null,
                    'premises' => null,
                    'region' => null,
                ),
            ),
        );
    }
}
