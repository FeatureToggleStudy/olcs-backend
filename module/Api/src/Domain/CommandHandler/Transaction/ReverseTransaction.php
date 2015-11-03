<?php

/**
 * Reverse Transaction
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Dvsa\Olcs\Api\Domain\CommandHandler\Transaction;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\CpmsAwareInterface;
use Dvsa\Olcs\Api\Domain\CpmsAwareTrait;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\Transaction as TransactionEntity;
use Dvsa\Olcs\Api\Service\CpmsResponseException;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Reverse Transaction
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class ReverseTransaction extends AbstractCommandHandler implements
    TransactionedInterface,
    AuthAwareInterface,
    CpmsAwareInterface
{
    use AuthAwareTrait, CpmsAwareTrait;

    protected $repoServiceName = 'Transaction';

    public function handleCommand(CommandInterface $command)
    {
        $transaction = $this->getRepo()->fetchUsingId($command);

        $fee = $transaction->getFeeTransactions()->first()->getFee();

        // @todo check transaction is complete before trying to reverse it

        switch ($transaction->getPaymentMethod()->getId()) {
            case FeeEntity::METHOD_CHEQUE:
                $method = 'reverseChequePayment';
                break;
            case FeeEntity::METHOD_CARD_ONLINE:
            case FeeEntity::METHOD_CARD_OFFLINE:
                $method = 'chargeBack';
                break;
            default:
                throw new ValidationException(['Invalid payment method for reversal']);
        }

        try {
            $response = $this->getCpmsService()->reverseChequePayment(
                $transaction->getReference(),
                [$fee]
            );
         } catch (CpmsResponseException $e) {
            // rethrow as Domain exception
            throw new RuntimeException(
                'Error from CPMS service: ' . json_encode($e->getResponse()),
                $e->getCode(),
                $e
            );
        }

        $receiptReference = $response['receipt_reference'];
        // create reversal transaction
        $now = new DateTime();

        $newTransaction = new TransactionEntity();
        $newTransaction
            ->setType($this->getRepo()->getRefdataReference(TransactionEntity::TYPE_REVERSAL))
            ->setStatus($this->getRepo()->getRefdataReference(TransactionEntity::STATUS_COMPLETE))
            ->setPaymentMethod($this->getRepo()->getRefdataReference(FeeEntity::METHOD_REVERSAL))
            ->setComment($comment)
            ->setCompletedDate($now)
            ->setProcessedByUser($this->getCurrentUser())
            ->setReference($transactionReference)
            ->setFeeTransactions(new ArrayCollection());

        foreach ($transaction->getFeeTransactionsForReversal() as $originalFt) {
            $feeTransaction = new FeeTransactionEntity();
            $reversalAmount = $originalFt->getAmount() * -1;
            $feeTransaction
                ->setFee($originalFt->getFee())
                ->setTransaction($transaction)
                ->setAmount($reversalAmount)
                ->setReversedFeeTransaction($originalFt);
            $newTransaction->getFeeTransactions()->add($feeTransaction);
        }

        $this->getRepo()->save($newTransaction);

        $this->result
            ->addMessage(
                sprintf('Transaction %d reversed using [%s]', $transaction->getId(), $method)
            )
            ->addId('transaction', $newTransaction->getId())
            ->addMessage('Transaction record created');

        return $this->result;
    }
}
