<?php

/**
 * Pay Outstanding Fees
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Dvsa\Olcs\Api\Domain\CommandHandler\Payment;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Fee\Payment as PaymentEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeePayment as FeePaymentEntity;
use Dvsa\Olcs\Transfer\Command\Payment\PayOutstandingFees as Cmd;
use Dvsa\Olcs\Transfer\Query\Organisation\OutstandingFees as OutstandingFeesQry;
use Zend\ServiceManager\ServiceLocatorInterface;


// @todo move /////////////
use CpmsClient\Service\ApiService;

/**
 * Pay Outstanding Fees
 * (initiates a CPMS payment which is a two-step process)
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class PayOutstandingFees extends AbstractCommandHandler
{
    // @todo move
/////////////////
    const PAYMENT_SUCCESS      = 801;
    const PAYMENT_FAILURE      = 802;
    const PAYMENT_CANCELLATION = 807;
    const PAYMENT_IN_PROGRESS  = 800;

    const RESPONSE_SUCCESS = '000';

    // CPMS' preferred date format (note: this changed around 03/2015)
    const DATE_FORMAT = 'Y-m-d';

    // @TODO product ref shouldn't have to come from a whitelist...
    const PRODUCT_REFERENCE = 'GVR_APPLICATION_FEE';

    // @TODO this is a dummy value for testing purposes as cost_centre is now
    // a required parameter in cpms/payment-service. Awaiting further info on
    // what OLCS should pass for this field.
    const COST_CENTRE = '12345,67890';
/////////////////

    protected $repoServiceName = 'Payment';

    protected $cpmsClient;

    protected $feeRepo;

    protected $feePaymentRepo;

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $organisationId = $command->getOrganisationId();
        if ($organisationId) {

            // get outstanding fees for organisation
            // $query = OutstandingFeesQry::create(['id' => $organisationId]);
            // $result = $this->getQueryHandler()->handleQuery($query);
            // $outstandingFees = $result['outstandingFees'];
            $outstandingFees = $this->feeRepo->fetchOutstandingFeesByOrganisationId($organisationId);

            // filter requested fee ids against outstanding fees
            $fees = [];
            if (!empty($outstandingFees)) {
                $ids = $command->getFeeIds();
                foreach ($outstandingFees as $fee) {
                    if (in_array($fee->getId(), $ids)) {
                        $fees[] = $fee;
                    }
                }
            }

            $service = $this; // until we move to separate service

            // filter out fees that may have been paid by resolving outstanding payments
            $feesToPay = [];
            foreach ($fees as $fee) {
                if ($fee->hasOutstandingPayment()) {
                    $paid = $service->resolveOutstandingPayments($fee);
                    if (!$paid) {
                        $feesToPay[] = $fee;
                    }
                } else {
                    $feesToPay[] = $fee;
                }
            }

            if (empty($feesToPay)) {
                $result->addMessage('No fees to pay');
                return $result;
            }

            try {
                $response = $service->initiateCardRequest(
                    $organisationId,
                    $command->getCpmsRedirectUrl(),
                    $feesToPay,
                    $command->getPaymentMethod()
                );
            } catch (CpmsException\PaymentInvalidResponseException $e) {
                // @TODO
                // $this->addErrorMessage('payment-failed');
                // return $this->redirectToIndex();
            }

            $payment = new PaymentEntity();
            $payment->setGuid($response['receipt_reference']);
            $payment->setStatus($this->getRepo()->getRefdataReference(PaymentEntity::STATUS_OUTSTANDING));
            $this->getRepo()->save($payment);

            foreach ($feesToPay as $fee) {
                $feePayment = new FeePaymentEntity();
                $feePayment->setPayment($payment);
                $feePayment->setFee($fee);
                $feePayment->setFeeValue($fee->getAmount());
                $this->feePaymentRepo->save($feePayment);

                // ensure payment method is recorded
                $fee->setPaymentMethod($this->getRepo()->getRefdataReference($command->getPaymentMethod()));
                $this->feeRepo->save($fee);
            }
        } else {
            // not implemented yet (fees with no organisation id)
            // @todo throw Exception
        }
        $result->addMessage('Done');
        return $result;
        /** @var Application $application */
        // $application = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        // $application->setFinancialEvidenceUploaded($command->getFinancialEvidenceUploaded());

        // $this->getRepo()->save($application);

        // $result->addMessage('Financial evidence section has been updated');
        // return $result;
    }

    // old controller method
    protected function payFeesViaCpms($fees)
    {
        // Check for and resolve any outstanding payment requests
        $service = $this->getServiceLocator()->get('Cpms\FeePayment');
        $feesToPay = [];
        foreach ($fees as $fee) {
            if ($service->hasOutstandingPayment($fee)) {
                $paid = $service->resolveOutstandingPayments($fee);
                if (!$paid) {
                    $feesToPay[] = $fee;
                }
            } else {
                $feesToPay[] = $fee;
            }
        }
        if (empty($feesToPay)) {
            // fees were all paid
            return $this->redirectToIndex();
        }

        $customerReference = $this->getCurrentOrganisationId();
        $redirectUrl = $this->getServiceLocator()->get('Helper\Url')
            ->fromRoute('fees/result', [], ['force_canonical' => true], true);

        try {
            $response = $service->initiateCardRequest($customerReference, $redirectUrl, $feesToPay);
        } catch (CpmsException\PaymentInvalidResponseException $e) {
            $this->addErrorMessage('payment-failed');
            return $this->redirectToIndex();
        }

        $view = new ViewModel(
            [
                'gateway' => $response['gateway_url'],
                'data' => [
                    'receipt_reference' => $response['receipt_reference']
                ]
            ]
        );
        $view->setTemplate('cpms/payment');

        return $this->render($view);
    }

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        parent::createService($serviceLocator);

        $mainServiceLocator = $serviceLocator->getServiceLocator();
        $this->cpmsClient = $mainServiceLocator->get('cpms\service\api');
        $this->feeRepo = $mainServiceLocator->get('RepositoryServiceManager')->get('Fee');
        $this->feePaymentRepo = $mainServiceLocator->get('RepositoryServiceManager')->get('FeePayment');
        return $this;
    }

    protected function resolveOutstandingPayments($fee)
    {
        // @TODO!
        return false;
    }


    // the following could move to a new Cpms helper service?

    /**
     * @param string $customerReference usually organisation id
     * @param string $redirectUrl redirect back to here from payment gateway
     * @param array $fees
     * @param string $paymentMethod FeePaymentEntityService::METHOD_CARD_OFFLINE|METHOD_CARD_ONLINE
     *
     * @return array
     * @throws Common\Service\Cpms\Exception\PaymentInvalidResponseException on error
     */
    protected function initiateCardRequest(
        $customerReference,
        $redirectUrl,
        array $fees,
        $paymentMethod
    ) {
        $totalAmount = $this->getTotalAmountFromFees($fees);

        $paymentData = [];
        foreach ($fees as $fee) {
            $paymentData[] = [
                'amount' => $this->formatAmount($fee->getAmount()),
                'sales_reference' => (string)$fee->getId(),
                'product_reference' => self::PRODUCT_REFERENCE,
                'payment_reference' => [
                    'rule_start_date' => $fee->getRuleStartDate()->format(self::DATE_FORMAT),
                ],
            ];
        }

        $endPoint = '/api/payment/card';
        $scope    = ApiService::SCOPE_CARD;

        $params = [
            // @NOTE CPMS rejects ints as 'missing', so we have to force a string...
            'customer_reference' => (string)$customerReference,
            'scope' => $scope,
            'disable_redirection' => true,
            'redirect_uri' => $redirectUrl,
            'payment_data' => $paymentData,
            'cost_centre' => self::COST_CENTRE,
            'total_amount' => $this->formatAmount($totalAmount),
        ];

        // $this->debug(
        //     'Card payment request',
        //     [
        //         'method' => [
        //             'location' => __METHOD__,
        //             'data' => func_get_args()
        //         ],
        //         'endPoint' => $endPoint,
        //         'scope'    => $scope,
        //         'params'   => $params,
        //     ]
        // );

        $response = $this->cpmsClient->post($endPoint, $scope, $params);

        // $this->debug('Card payment response', ['response' => $response]);
        if (!is_array($response)
            || !isset($response['receipt_reference'])
            || empty($response['receipt_reference'])
        ) {
            throw new Exception\PaymentInvalidResponseException(json_encode($response));
        }

        return $response;
    }

    /**
     * @param array $fees
     * return float
     */
    protected function getTotalAmountFromFees($fees)
    {
        $totalAmount = 0;
        foreach ($fees as $fee) {
            $totalAmount += (float)$fee->getAmount();
        }
        return $totalAmount;
    }

    /**
     * @param mixed $amount
     * @return string amount formatted to two decimal places with no thousands separator
     */
    protected function formatAmount($amount)
    {
        if (!empty($amount) && !is_numeric($amount)) {
            throw new \InvalidArgumentException("'".var_export($amount, true)."' is not a valid amount");
        }
        return sprintf("%1\$.2f", $amount);
    }

    protected function debug($message, $data)
    {
        // @TODO
    }
}
