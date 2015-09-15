<?php

namespace Dvsa\Olcs\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Domain\QueryHandler\QueryHandlerInterface;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Person\Person;

/**
 * Class AbstractSection
 * @package Dvsa\Olcs\Api\Service\Submission\Section
 */
abstract class AbstractSection implements SectionGeneratorInterface
{
    /**
     * @var QueryHandlerInterface
     */
    private $queryHandler;

    public function __construct(QueryHandlerInterface $queryHandler)
    {
        $this->queryHandler = $queryHandler;
    }

    protected function handleQuery($query)
    {
        return $this->queryHandler->handleQuery($query);
    }

    /**
     * Extract personData if exists, used by child classes
     * @param $contactDetails
     * @return array
     */
    protected function extractPerson($contactDetails = null)
    {
        $personData = [
            'title' => '',
            'forename' => '',
            'familyName' => '',
            'birthDate' => '',
            'birthPlace' => ''
        ];

        if ($contactDetails instanceof ContactDetails && ($contactDetails->getPerson() instanceof Person)) {
            $person = $contactDetails->getPerson();

            $personData = [
                'title' => !empty($person->getTitle()) ? $person->getTitle()->getDescription() : '',
                'forename' => $person->getForename(),
                'familyName' => $person->getFamilyName(),
                'birthDate' => $this->formatDate($person->getBirthDate()),
                'birthPlace' => $person->getBirthPlace()
            ];
        }
        return $personData;
    }

    protected function formatDate($datetime = null)
    {
        if (!empty($datetime)) {
            if (is_string($datetime)) {
                return $datetime;
            } elseif ($datetime instanceof \DateTime) {
                return $datetime->format('d/m/Y');
            }
        }

        return '';
    }
}
