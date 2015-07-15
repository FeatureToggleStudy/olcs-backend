<?php

/**
 * Send Inspection Request Email
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Dvsa\Olcs\Api\Domain\CommandHandler\InspectionRequest;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\EmailAwareInterface;
use Dvsa\Olcs\Api\Domain\EmailAwareTrait;
use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;

/**
 * Send Inspection Request Email
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class SendInspectionRequest extends AbstractCommandHandler implements EmailAwareInterface, AuthAwareInterface
{
    use EmailAwareTrait,
        AuthAwareTrait;

    const SUBJECT_LINE = "[ Maintenance Inspection ] REQUEST=%s,STATUS=";

    protected $repoServiceName = 'InspectionRequest';

    /*
     * we don't have translation service on backend so for now I just placed translations in array
     */
    protected $licenceTypes = [
        'en_GB' => [
            'ltyp_cbp' => 'Community',
            'ltyp_dbp' => 'Designated Body/Local Authority',
            'ltyp_lbp' => 'Large',
            'ltyp_r'   => 'Restricted',
            'ltyp_sbp' => 'Small',
            'ltyp_si'  => 'Standard International',
            'ltyp_sn'  => 'Standard National',
            'ltyp_sr'  => 'Special Restricted'
        ],
        'cy_GB' => [
            'ltyp_cbp' => 'W Community',
            'ltyp_dbp' => 'W Designated Body/Local Authority',
            'ltyp_lbp' => 'W Large',
            'ltyp_r'   => 'W Restricted',
            'ltyp_sbp' => 'W Small',
            'ltyp_si'  => 'W Standard International',
            'ltyp_sn'  => 'W Standard National',
            'ltyp_sr'  => 'W Special Restricted',
        ]
    ];

    /**
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /* @var array - just to save the time during migration process,
         * InspectionRequest view model used an array, not an object
         */
        $inspectionRequest = $this->getRepo()->fetchForInspectionRequest($command->getId());

        $message = new Message(
            $inspectionRequest['licence']['enforcementArea']['emailAddress'],
            sprintf(self::SUBJECT_LINE, $inspectionRequest['id'])
        );
        $translateToWelsh = 'N';
        if (isset($inspectionRequest['application']['licence']['translateToWelsh'])) {
            $translateToWelsh = $inspectionRequest['application']['licence']['translateToWelsh'];
        } elseif (isset($inspectionRequest['licence']['translateToWelsh'])) {
            $translateToWelsh = $inspectionRequest['licence']['translateToWelsh'];
        }
        $message->setTranslateToWelsh($translateToWelsh);
        $message->setHtml(false);

        $variables = $this->populateInspectionRequestVariables($inspectionRequest, $message->getLocale());
        $this->sendEmailTemplate(
            $message,
            'inspection-request',
            $variables,
            false
        );

        $result = new Result();
        $result->addMessage('Inspection request email sent');
        return $result;
    }

    protected function populateInspectionRequestVariables($inspectionRequest, $locale)
    {
        $workshop = isset($inspectionRequest['licence']['workshops'][0]) ?
            $inspectionRequest['licence']['workshops'][0] : null;
        $user = $this->getCurrentUser();

        $requestDate = '';
        if (isset($inspectionRequest['requestDate'])) {
            $requestDate = (new \DateTime($inspectionRequest['requestDate']))->format('d/m/Y H:i:s');
        }
        $dueDate = '';
        if (isset($inspectionRequest['dueDate'])) {
            $dueDate = (new \DateTime($inspectionRequest['dueDate']))->format('d/m/Y H:i:s');
        }
        $expiryDate = '';
        if (isset($inspectionRequest['licence']['expiryDate'])) {
            $expiryDate = (new \DateTime($inspectionRequest['licence']['expiryDate']))->format('d/m/Y');
        }

        $data = [
            'inspectionRequestId' => $inspectionRequest['id'],
            'currentUserName' => $user->getLoginId(),
            'currentUserEmail' => $user->getContactDetails()->getEmailAddress(),
            'inspectionRequestDateRequested' => $requestDate,
            'inspectionRequestNotes' => $inspectionRequest['requestorNotes'],
            'inspectionRequestDueDate' => $dueDate,
            'ocAddress' => $inspectionRequest['operatingCentre']['address'],
            'inspectionRequestType' => $inspectionRequest['requestType']['description'],
            'licenceNumber' => $inspectionRequest['licence']['licNo'],
            'licenceType' => $this->getLicenceType($inspectionRequest, $locale),
            'totAuthVehicles' => $this->getTotAuthVehicles($inspectionRequest),
            'totAuthTrailers' => $this->getTotAuthTrailers($inspectionRequest),
            'numberOfOperatingCentres' => count($inspectionRequest['licence']['operatingCentres']),
            'expiryDate' => $expiryDate,
            'operatorId' => $inspectionRequest['licence']['organisation']['id'],
            'operatorName' => $inspectionRequest['licence']['organisation']['name'],
            'operatorEmail' => $inspectionRequest['licence']['correspondenceCd']['emailAddress'],
            'operatorAddress' => $inspectionRequest['licence']['correspondenceCd']['address'],
            'contactPhoneNumbers' => $inspectionRequest['licence']['correspondenceCd']['phoneContacts'],
            'transportManagers' => $this->getTransportManagers($inspectionRequest),
            'tradingNames' => $this->getTradingNames($inspectionRequest),
            'workshopIsExternal' => (isset($workshop['isExternal']) && $workshop['isExternal'] === 'Y'),
            'safetyInspectionVehicles' => $inspectionRequest['licence']['safetyInsVehicles'],
            'safetyInspectionTrailers' => $inspectionRequest['licence']['safetyInsTrailers'],
            'inspectionProvider' => $workshop['contactDetails'],
            'people' => $this->getPeopleFromPeopleData(
                $inspectionRequest['licence']['organisation']['organisationPersons']
            ),
            'otherLicences' => $this->getOtherLicences($inspectionRequest),
            'applicationOperatingCentres' => $this->getApplicationOperatingCentres($inspectionRequest),
        ];
        return $data;
    }

    protected function getTotAuthVehicles($inspectionRequest)
    {
        $totAuthVehicles = '';
        if (!empty($inspectionRequest['application'])) {
            $totAuthVehicles = $inspectionRequest['application']['totAuthVehicles'];
        } elseif (isset($inspectionRequest['licence']['totAuthVehicles'])) {
            $totAuthVehicles = $inspectionRequest['licence']['totAuthVehicles'];
        }
        return $totAuthVehicles;
    }

    protected function getTotAuthTrailers($inspectionRequest)
    {
        $totAuthTrailers = '';
        if (!empty($inspectionRequest['application'])) {
            $totAuthTrailers = $inspectionRequest['application']['totAuthTrailers'];
        } elseif (isset($inspectionRequest['licence']['totAuthTrailers'])) {
            $totAuthTrailers = $inspectionRequest['licence']['totAuthTrailers'];
        }
        return $totAuthTrailers;
    }

    protected function getLicenceType($inspectionRequest, $locale)
    {
        $licenceType = '';
        if (!empty($inspectionRequest['application']) &&
            isset($inspectionRequest['application']['licenceType']['id'])) {
            $licenceType =  $this->licenceTypes[$locale][$inspectionRequest['application']['licenceType']['id']];
        } elseif (isset($inspectionRequest['licence']['licenceType']['id'])) {
            $licenceType =  $this->licenceTypes[$locale][$inspectionRequest['licence']['licenceType']['id']];
        }
        return $licenceType;
    }

    protected function getOtherLicences($inspectionRequest)
    {
        $licenceNos = array_map(
            function ($licence) {
                return $licence['licNo'];
            },
            $inspectionRequest['licence']['organisation']['licences']
        );

        $currentLicNo = $inspectionRequest['licence']['licNo'];

        $filtered = array_filter(
            $licenceNos,
            function ($licNo) use ($currentLicNo) {
                return ($licNo !== $currentLicNo) && !empty($licNo);
            }
        );

        return array_values($filtered); // ignore keys;
    }

    protected function getApplicationOperatingCentres($inspectionRequest)
    {
        if (!is_array($inspectionRequest['application']['operatingCentres'])) {
            return [];
        }
        return array_map(
            function ($aoc) {
                switch ($aoc['action']) {
                    case ApplicationOperatingCentre::ACTION_ADD:
                        $aoc['action'] = 'Added';
                        break;
                    case ApplicationOperatingCentre::ACTION_UPDATE:
                        $aoc['action'] = 'Updated';
                        break;
                    case ApplicationOperatingCentre::ACTION_DELETE:
                        $aoc['action'] = 'Deleted';
                        break;
                }
                return $aoc;
            },
            $inspectionRequest['application']['operatingCentres']
        );
    }

    protected function getTransportManagers($inspectionRequest)
    {
        return array_map(
            function ($tmLicence) {
                $person = $tmLicence['transportManager']['homeCd']['person'];
                return $person['forename'].' '.$person['familyName'];
            },
            $inspectionRequest['licence']['tmLicences']
        );
    }

    protected function getTradingNames($inspectionRequest)
    {
        return array_map(
            function ($tradingName) {
                return $tradingName['name'];
            },
            $inspectionRequest['licence']['organisation']['tradingNames']
        );
    }

    protected function getPeopleFromPeopleData($peopleData)
    {
        return array_map(
            function ($peopleResult) {
                return $peopleResult['person'];
            },
            $peopleData
        );
    }
}
