<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\EcmtPermits;
use Dvsa\Olcs\Api\Entity\EcmtPermitCountryLink;

use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Create an ECMT Permit application
 *
 * @author Tonci Vidovic <tonci.vidovic@capgemini.com>
 */
final class CreateEcmtPermits extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'EcmtPermits';
    protected $extraRepos = ['EcmtPermitCountryLink','ApplicationStatus','EcmtPermitApplication','PaymentStatus'];

    public function handleCommand(CommandInterface $command)
    {

        $ecmtPermit = new EcmtPermits();


        $applicationStatus = $this->getRepo('ApplicationStatus')->fetchById($command->getApplicationStatus());
        $ecmtPermitsApplication = $this->getRepo('EcmtPermitApplication')->fetchById($command->getEcmtPermitsApplication());
        $paymentStatus = $this->getRepo('PaymentStatus')->fetchById($command->getPaymentStatus());


        $ecmtPermit->setApplicationStatus($applicationStatus);
        $ecmtPermit->setEcmtPermitsApplication($ecmtPermitsApplication);
        $ecmtPermit->setIntensity($command->getIntensity());
        $ecmtPermit->setPaymentStatus($paymentStatus);
        $this->getRepo()->save($ecmtPermit);

        $result = new Result();
        $result->addId('ecmtPermit', $ecmtPermit->getId());
        $result->addMessage("ECMT permit application ID {$ecmtPermit->getId()} created");

        /*foreach($command->getCountries() as $country)
        {
            $EcmtPermitCountryLink = new EcmtPermitCountryLink();
            $EcmtPermitCountryLink->setEcmtPermitId($ecmtPermit->getId());
            $EcmtPermitCountryLink->setCountryId($country);
            $this->getRepo('EcmtPermitCountryLink')->save($EcmtPermitCountryLink);
            $resultCountry = new Result();
            $resultCountry->addId('ecmtPermitCountryLink', $ecmtPermit->getId());
            $resultCountry->addMessage("ECMT permit country link {$EcmtPermitCountryLink->getId()} created");
        }*/

        return $result;

    }
}
