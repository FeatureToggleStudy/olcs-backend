<?php

/**
 * Adjust Transaction Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Transaction;

use Doctrine\ORM\Query;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Fee\CreateOverpaymentFee as CreateOverpaymentFeeCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Transaction\AdjustTransaction;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeTransaction as FeeTransactionEntity;
use Dvsa\Olcs\Api\Entity\Fee\Transaction as TransactionEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\CpmsHelperInterface as CpmsHelper;
use Dvsa\Olcs\Transfer\Command\Transaction\AdjustTransaction as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\CommandHandlerTestCase;
use Mockery as m;
use ZfcRbac\Service\AuthorizationService;

/**
 * Adjust Transaction Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class AdjustTransactionTest extends CommandHandlerTestCase
{
    protected $mockCpmsService;

    protected $mockFeesHelperService;

    public function setUp()
    {
        $this->mockCpmsService = m::mock(CpmsHelper::class);
        $this->mockFeesHelperService = m::mock(FeesHelper::class);
        $this->mockedSmServices = [
            'CpmsHelperService' => $this->mockCpmsService,
            'FeesHelperService' => $this->mockFeesHelperService,
            AuthorizationService::class => m::mock(AuthorizationService::class)->makePartial(),
            'Config' => [],
        ];

        $this->sut = new AdjustTransaction();
        $this->mockRepo('Transaction', Repository\Transaction::class);

        /** @var UserEntity $mockUser */
        $mockUser = m::mock(UserEntity::class)
            ->shouldReceive('getLoginId')
            ->andReturn('bob')
            ->getMock();

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);

        parent::setUp();
    }

    protected function initReferences()
    {
        $this->refData = [
            TransactionEntity::STATUS_COMPLETE,
            TransactionEntity::TYPE_ADJUSTMENT,
            FeeEntity::STATUS_PAID => m::mock(RefData::class)
                ->makePartial()
                ->shouldReceive('getDescription')
                ->andReturn('Paid')
                ->getMock(),
            FeeEntity::METHOD_CHEQUE,
        ];

        $this->references = [
            TransactionEntity::class => [
                69 => m::mock(TransactionEntity::class),
            ],
            FeeEntity::class => [
                100 => m::mock(FeeEntity::class),
            ],
            FeeTransactionEntity::class => [
                200 => m::mock(FeeTransactionEntity::class),
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand()
    {
        $transactionId = 69;

        // set up 'new' data
        //////////////////////////////////////
        $data = [
            'id' => $transactionId,
            'version' => 1,
            'received' => '10.00',
            'payer' => 'Dan',
            'slipNo' => '1234',
            'chequeNo' => '2345',
            'chequeDate' => '2015-12-01',
            'reason' => 'Keying error'
        ];

        $command = Cmd::create($data);
        ////////////////////////////////////////

        // set up 'existing' data
        ////////////////////////////////////////
        $fee = $this->mapReference(FeeEntity::class, 100);

        $transaction = $this->mapReference(TransactionEntity::class, $transactionId);

        $feeTransaction = $this->mapReference(FeeTransactionEntity::class, 200);
        $feeTransaction
            ->setFee($fee)
            ->setTransaction($transaction)
            ->shouldReceive('getAmount')->andReturn('100.00');

        $fee->setFeeTransactions(new ArrayCollection([$feeTransaction]));
        $fee->shouldReceive('isBalancingFee')->andReturn(false);

        $transaction
            ->shouldReceive('getReference')->andReturn('OLCS-CHEQUE-REF-1')
            ->shouldReceive('getId')->andReturn($transactionId)
            ->shouldReceive('getFees')->andReturn([$fee])
            ->shouldReceive('getTotalAmount')->andReturn('100.00')
            ->shouldReceive('getPayerName')->andReturn('Dan')
            ->shouldReceive('getPayingInSlipNumber')->andReturn('1234')
            ->shouldReceive('getChequePoNumber')->andReturn('2345')
            ->shouldReceive('getChequePoDate')->andReturn('2015-12-01')
            ->shouldReceive('getFeeTransactionsForAdjustment')->andReturn([$feeTransaction]);
        ////////////////////////////////////////

        $this->repoMap['Transaction']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($transaction);

        $this->mockCpmsService
            ->shouldReceive('adjustTransaction')
            ->once()
            ->with('OLCS-CHEQUE-REF-1', 69, [$fee], '10.00', 'Dan', '1234', '2345', '2015-12-01', null)
            ->andReturn(
                [
                    'code' => CpmsHelper::RESPONSE_SUCCESS,
                    'receipt_reference' => 'OLCS-ADJ-REF-1',
                ]
            );

        $this->expectedSideEffect(
            CreateOverpaymentFeeCmd::class,
            [
                'receivedAmount' => '10.00',
                'fees' => [100 => $fee],
            ],
            new Result()
        );

        $this->mockFeesHelperService
            ->shouldReceive('allocatePayments')
            ->once()
            ->with(
                '10.00',
                [100 => $fee]
            )
            ->andReturn(
                [
                    // 100 => '10.00',
                ]
            );

        $savedTransaction = null;
        $newTransactionId = 70;
        $this->repoMap['Transaction']
            ->shouldReceive('save')
            ->andReturnUsing(
                function ($transaction) use (&$savedTransaction, $newTransactionId) {
                    $savedTransaction = $transaction;
                    $savedTransaction->setId($newTransactionId);
                    // $feeTransactionId = 200;
                    // $savedTransaction->getFeeTransactions()->forAll(
                    //     function ($key, $ft) use (&$feeTransactionId) {
                    //         $ft->setId($feeTransactionId + $key);
                    //         return true; // closure *must* return true to continue
                    //     }
                    // );
                }
            );

        $this->sut->handleCommand($command);
    }

    /**
     * Test validation (command is invalid if no details have changed)
     *
     * @param  Cmd               $command
     * @param  TransactionEntity $transaction
     * @param  boolean           $expected
     * @dataProvider validateChangesProvider
     */
    public function testValidate(Cmd $command, $expected)
    {
        if ($expected === false) {
            $this->setExpectedException(ValidationException::class);
        }

        $transaction = m::mock(TransactionEntity::class)
            ->shouldReceive('getTotalAmount')->andReturn('100.00')
            ->shouldReceive('getPayerName')->andReturn('Dan')
            ->shouldReceive('getPayingInSlipNumber')->andReturn('1234')
            ->shouldReceive('getChequePoNumber')->andReturn('2345')
            ->shouldReceive('getChequePoDate')->andReturn('2015-12-09')
            ->getMock();

        $result = $this->sut->validate($command, $transaction);

        $this->assertSame($expected, $result);
    }

    public function validateChangesProvider()
    {
        return [
            'no changes' => [
                Cmd::create(
                    [
                        'received' => '100.00',
                        'payer' => 'Dan',
                        'slipNo' => '1234',
                        'chequeNo' => '2345',
                        'poNo' => '2345',
                        'chequeDate' => '2015-12-09',
                    ]
                ),
                false,
            ],
            'amount changed' => [
                Cmd::create(
                    [
                        'received' => '200.00',
                        'payer' => 'Dan',
                        'slipNo' => '1234',
                        'chequeNo' => '2345',
                        'poNo' => '2345',
                        'chequeDate' => '2015-12-09',
                    ]
                ),
                true,
            ],
            'payer changed' => [
                Cmd::create(
                    [
                        'received' => '100.00',
                        'payer' => 'Bob',
                        'slipNo' => '1234',
                        'chequeNo' => '2345',
                        'poNo' => '2345',
                        'chequeDate' => '2015-12-09',
                    ]
                ),
                true,
            ],
            'slip no changed' => [
                Cmd::create(
                    [
                        'received' => '100.00',
                        'payer' => 'Dan',
                        'slipNo' => '1235',
                        'chequeNo' => '2345',
                        'poNo' => '2345',
                        'chequeDate' => '2015-12-09',
                    ]
                ),
                true,
            ],
            'cheque no changed' => [
                Cmd::create(
                    [
                        'received' => '100.00',
                        'payer' => 'Dan',
                        'slipNo' => '1234',
                        'chequeNo' => '2346',
                        'poNo' => '2345',
                        'chequeDate' => '2015-12-09',
                    ]
                ),
                true,
            ],
            'PO no changed' => [
                Cmd::create(
                    [
                        'received' => '100.00',
                        'payer' => 'Dan',
                        'slipNo' => '1234',
                        'chequeNo' => '2345',
                        'poNo' => '2346',
                        'chequeDate' => '2015-12-09',
                    ]
                ),
                true,
            ],
            'cheque date changed' => [
                Cmd::create(
                    [
                        'received' => '100.00',
                        'payer' => 'Dan',
                        'slipNo' => '1234',
                        'chequeNo' => '2345',
                        'poNo' => '2345',
                        'chequeDate' => '2015-12-01',
                    ]
                ),
                true,
            ],
        ];
    }
}
