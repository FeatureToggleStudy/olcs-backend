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
use Dvsa\Olcs\Api\Entity\Fee\FeeTransaction as FeeTransactionEntity;
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

    protected $extraRepos = ['Fee'];

    public function handleCommand(CommandInterface $command)
    {
        $transaction = $this->getRepo()->fetchUsingId($command);

        if (!$transaction->isComplete()) {
            throw new ValidationException(['Cannot reverse a pending transaction']);
        }

        switch ($transaction->getPaymentMethod()->getId()) {
            case FeeEntity::METHOD_CHEQUE:
                $method = 'reverseChequePayment';
                break;
            case FeeEntity::METHOD_CARD_ONLINE:
            case FeeEntity::METHOD_CARD_OFFLINE:
                $method = 'chargeBackCardPayment';
                break;
            default:
                throw new ValidationException(['Invalid payment method for reversal']);
        }

        try {
            $fee = $transaction->getFeeTransactions()->first()->getFee();
            $response = $this->getCpmsService()->$method(
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

        // create reversal transaction
        $transactionReference = $response['receipt_reference'];
        $now = new DateTime();
        $newTransaction = new TransactionEntity();
        $newTransaction
            ->setType($this->getRepo()->getRefdataReference(TransactionEntity::TYPE_REVERSAL))
            ->setStatus($this->getRepo()->getRefdataReference(TransactionEntity::STATUS_COMPLETE))
            ->setPaymentMethod($this->getRepo()->getRefdataReference(FeeEntity::METHOD_REVERSAL))
            ->setComment($comment)
            ->setCompletedDate($now)
            ->setProcessedByUser($this->getCurrentUser())
            ->setReference($transactionReference);

        $fees = [];
        foreach ($transaction->getFeeTransactionsForReversal() as $originalFt) {
            $feeTransaction = new FeeTransactionEntity();
            $reversalAmount = $originalFt->getAmount() * -1;
            $fee = $originalFt->getFee();
            $feeTransaction
                ->setFee($fee)
                ->setTransaction($transaction)
                ->setAmount($reversalAmount)
                ->setReversedFeeTransaction($originalFt);
            $newTransaction->getFeeTransactions()->add($feeTransaction);
            $fees[$fee->getId()] = $fee;
        }

        $this->getRepo()->save($newTransaction);

        $this->result
            ->addMessage(
                sprintf('Transaction %d reversed using [%s]', $transaction->getId(), $method)
            )
            ->addId('transaction', $newTransaction->getId())
            ->addMessage('Transaction record created');

        $this->resetFees($fees);

        return $this->result;
    }

    /**
     * @todo there may be edge cases we need to consider here, e.g.:
     *  - if fees still have a zero or negative outstanding after reversing the payment
     *  - overpayment balancing fees can probably just be cancelled
     */
    private function resetFees(array $fees)
    {
        $status = $this->getRepo()->getRefdataReference(FeeEntity::STATUS_OUTSTANDING);
        foreach ($fees as $feeId => $fee) {
            $fee->setFeeStatus($status);
            $this->getRepo('Fee')->save($fee);
            $this->result->addMessage(sprintf('Fee %d reset to outstanding', $feeId));
        }
    }
}
