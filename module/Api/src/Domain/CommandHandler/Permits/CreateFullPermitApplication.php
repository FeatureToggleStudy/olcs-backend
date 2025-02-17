<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Permits;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Domain\ToggleRequiredInterface;
use Dvsa\Olcs\Api\Entity\Permits\EcmtPermitApplication;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Permits\Sectors;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Transfer\Command\Permits\CreateFullPermitApplication as CreateFullPermitApplicationCmd;
use Dvsa\Olcs\Api\Domain\Command\Permits\CreateIrhpPermitApplication;
use Dvsa\Olcs\Api\Domain\Command\Permits\UpdatePermitFee;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Create an ECMT Permit application
 *
 * @author Andy Newton
 */
final class CreateFullPermitApplication extends AbstractCommandHandler implements ToggleRequiredInterface
{
    use ToggleAwareTrait;

    protected $toggleConfig = [FeatureToggle::BACKEND_ECMT];

    const LICENCE_INVALID_MSG = 'Licence ID %s with number %s is unable to make an ECMT application';

    /**
     * @var string
     */
    protected $repoServiceName = 'EcmtPermitApplication';

    protected $extraRepos = ['Country', 'IrhpPermitWindow', 'IrhpPermitStock', 'Licence'];

    /**
     * Handle command
     *
     * @param CommandInterface $command command
     *
     * @return Result
     * @throws ForbiddenException
     */
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var CreateFullPermitApplicationCmd $command
         * @var LicenceRepo                    $licenceRepo
         * @var LicenceEntity                  $licence
         */
        $licenceRepo = $this->getRepo('Licence');
        $licence = $licenceRepo->fetchById($command->getLicence());

        if (!$licence->canMakeEcmtApplication()) {
            $message = sprintf(self::LICENCE_INVALID_MSG, $licence->getId(), $licence->getLicNo());
            throw new ForbiddenException($message);
        }

        $ecmtPermitApplication = $this->createPermitApplicationObject($command, $licence);
        $this->getRepo()->save($ecmtPermitApplication);

        $result = new Result();

        if ($command->getPermitsRequired() > 0 && $ecmtPermitApplication->getId()) {
            $this->result->merge($this->handleSideEffect(
                UpdatePermitFee::create(
                    [
                        'ecmtPermitApplicationId' => $ecmtPermitApplication->getId(),
                        'licenceId' => $command->getLicence(),
                        'permitsRequired' => $command->getPermitsRequired(),
                        'permitType' =>  $ecmtPermitApplication::PERMIT_TYPE,
                        'receivedDate' =>  $ecmtPermitApplication->getDateReceived()
                    ]
                )
            ));
        }

        $result->addId('ecmtPermitApplication', $ecmtPermitApplication->getId());
        $result->addMessage('ECMT Permit Application created successfully');

        $stock = $this->getRepo('IrhpPermitStock')->getNextIrhpPermitStockByPermitType(
            EcmtPermitApplication::PERMIT_TYPE,
            new DateTime()
        );

        $window = $this->getRepo('IrhpPermitWindow')->fetchLastOpenWindowByStockId($stock->getId());

        $this->result->merge(
            $this->handleSideEffect(
                CreateIrhpPermitApplication::create(
                    [
                        'window' => $window->getId(),
                        'ecmtPermitApplication' => $ecmtPermitApplication->getId(),
                    ]
                )
            )
        );

        return $result;
    }

    /**
     * Create EcmtPermitApplication object
     *
     * @param CreateFullPermitApplicationCmd $command Command
     *
     * @return EcmtPermitApplication
     */
    private function createPermitApplicationObject(
        CreateFullPermitApplicationCmd $command,
        LicenceEntity $licence
    ): EcmtPermitApplication {
        $countrys = new ArrayCollection();
        foreach ($command->getCountryIds() as $countryId) {
            $countrys->add($this->getRepo('Country')->getReference(Country::class, $countryId));
        }

        return EcmtPermitApplication::createNewInternal(
            $this->getRepo()->getRefdataReference(EcmtPermitApplication::SOURCE_INTERNAL),
            $this->getRepo()->getRefdataReference(EcmtPermitApplication::STATUS_NOT_YET_SUBMITTED),
            $this->getRepo()->getRefdataReference(EcmtPermitApplication::PERMIT_TYPE),
            $licence,
            $command->getDateReceived(),
            $this->getRepo()->getReference(Sectors::class, $command->getSectors()),
            $countrys,
            $command->getCabotage(),
            $command->getDeclaration(),
            $command->getEmissions(),
            $command->getPermitsRequired(),
            $command->getTrips(),
            $this->getRepo()->getRefdataReference($command->getInternationalJourneys())
        );
    }
}
