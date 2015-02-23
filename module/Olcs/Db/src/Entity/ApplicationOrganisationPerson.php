<?php

namespace Olcs\Db\Entity;

use Doctrine\ORM\Mapping as ORM;
use Olcs\Db\Entity\Traits;

/**
 * ApplicationOrganisationPerson Entity
 *
 * Auto-Generated
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="application_organisation_person",
 *    indexes={
 *        @ORM\Index(name="ix_application_organisation_person_person_id", columns={"person_id"}),
 *        @ORM\Index(name="ix_application_organisation_person_original_person_id", columns={"original_person_id"}),
 *        @ORM\Index(name="ix_application_organisation_person_organisation_id", columns={"organisation_id"}),
 *        @ORM\Index(name="ix_application_organisation_person_application_id", columns={"application_id"}),
 *        @ORM\Index(name="ix_application_organisation_person_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_application_organisation_person_created_by", columns={"created_by"})
 *    }
 * )
 */
class ApplicationOrganisationPerson implements Interfaces\EntityInterface
{
    use Traits\CustomBaseEntity,
        Traits\ApplicationManyToOne,
        Traits\CreatedByManyToOne,
        Traits\CustomCreatedOnField,
        Traits\IdIdentity,
        Traits\LastModifiedByManyToOne,
        Traits\CustomLastModifiedOnField,
        Traits\OrganisationManyToOne,
        Traits\PersonManyToOne,
        Traits\Position45Field;

    /**
     * Action
     *
     * @var string
     *
     * @ORM\Column(type="string", name="action", length=1, nullable=false)
     */
    protected $action;

    /**
     * Original person
     *
     * @var \Olcs\Db\Entity\Person
     *
     * @ORM\ManyToOne(targetEntity="Olcs\Db\Entity\Person")
     * @ORM\JoinColumn(name="original_person_id", referencedColumnName="id", nullable=true)
     */
    protected $originalPerson;

    /**
     * Version
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="version", nullable=false)
     */
    protected $version = 1;

    /**
     * Set the action
     *
     * @param string $action
     * @return ApplicationOrganisationPerson
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get the action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set the original person
     *
     * @param \Olcs\Db\Entity\Person $originalPerson
     * @return ApplicationOrganisationPerson
     */
    public function setOriginalPerson($originalPerson)
    {
        $this->originalPerson = $originalPerson;

        return $this;
    }

    /**
     * Get the original person
     *
     * @return \Olcs\Db\Entity\Person
     */
    public function getOriginalPerson()
    {
        return $this->originalPerson;
    }

    /**
     * Set the version
     *
     * @param int $version
     * @return ApplicationOrganisationPerson
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get the version
     *
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }
}
