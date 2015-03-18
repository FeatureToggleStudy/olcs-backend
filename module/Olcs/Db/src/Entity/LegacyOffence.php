<?php

namespace Olcs\Db\Entity;

use Doctrine\ORM\Mapping as ORM;
use Olcs\Db\Entity\Traits;

/**
 * LegacyOffence Entity
 *
 * Auto-Generated
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="legacy_offence",
 *    indexes={
 *        @ORM\Index(name="ix_legacy_offence_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_legacy_offence_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_legacy_offence_case_id", columns={"case_id"})
 *    }
 * )
 */
class LegacyOffence implements Interfaces\EntityInterface
{
    use Traits\CustomBaseEntity,
        Traits\CreatedByManyToOne,
        Traits\CustomCreatedOnField,
        Traits\IdIdentity,
        Traits\LastModifiedByManyToOne,
        Traits\CustomLastModifiedOnField,
        Traits\Notes4000Field,
        Traits\CustomVersionField,
        Traits\Vrm20Field;

    /**
     * Case
     *
     * @var \Olcs\Db\Entity\Cases
     *
     * @ORM\ManyToOne(targetEntity="Olcs\Db\Entity\Cases", inversedBy="legacyOffences")
     * @ORM\JoinColumn(name="case_id", referencedColumnName="id", nullable=false)
     */
    protected $case;

    /**
     * Definition
     *
     * @var string
     *
     * @ORM\Column(type="string", name="definition", length=1000, nullable=true)
     */
    protected $definition;

    /**
     * Is trailer
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="is_trailer", nullable=true)
     */
    protected $isTrailer;

    /**
     * Num of offences
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="num_of_offences", nullable=true)
     */
    protected $numOfOffences;

    /**
     * Offence authority
     *
     * @var string
     *
     * @ORM\Column(type="string", name="offence_authority", length=100, nullable=true)
     */
    protected $offenceAuthority;

    /**
     * Offence date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="offence_date", nullable=true)
     */
    protected $offenceDate;

    /**
     * Offence to date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="offence_to_date", nullable=true)
     */
    protected $offenceToDate;

    /**
     * Offence type
     *
     * @var string
     *
     * @ORM\Column(type="string", name="offence_type", length=100, nullable=true)
     */
    protected $offenceType;

    /**
     * Offender name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="offender_name", length=100, nullable=true)
     */
    protected $offenderName;

    /**
     * Points
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="points", nullable=true)
     */
    protected $points;

    /**
     * Position
     *
     * @var string
     *
     * @ORM\Column(type="string", name="position", length=100, nullable=true)
     */
    protected $position;

    /**
     * Set the case
     *
     * @param \Olcs\Db\Entity\Cases $case
     * @return LegacyOffence
     */
    public function setCase($case)
    {
        $this->case = $case;

        return $this;
    }

    /**
     * Get the case
     *
     * @return \Olcs\Db\Entity\Cases
     */
    public function getCase()
    {
        return $this->case;
    }

    /**
     * Set the definition
     *
     * @param string $definition
     * @return LegacyOffence
     */
    public function setDefinition($definition)
    {
        $this->definition = $definition;

        return $this;
    }

    /**
     * Get the definition
     *
     * @return string
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Set the is trailer
     *
     * @param string $isTrailer
     * @return LegacyOffence
     */
    public function setIsTrailer($isTrailer)
    {
        $this->isTrailer = $isTrailer;

        return $this;
    }

    /**
     * Get the is trailer
     *
     * @return string
     */
    public function getIsTrailer()
    {
        return $this->isTrailer;
    }

    /**
     * Set the num of offences
     *
     * @param int $numOfOffences
     * @return LegacyOffence
     */
    public function setNumOfOffences($numOfOffences)
    {
        $this->numOfOffences = $numOfOffences;

        return $this;
    }

    /**
     * Get the num of offences
     *
     * @return int
     */
    public function getNumOfOffences()
    {
        return $this->numOfOffences;
    }

    /**
     * Set the offence authority
     *
     * @param string $offenceAuthority
     * @return LegacyOffence
     */
    public function setOffenceAuthority($offenceAuthority)
    {
        $this->offenceAuthority = $offenceAuthority;

        return $this;
    }

    /**
     * Get the offence authority
     *
     * @return string
     */
    public function getOffenceAuthority()
    {
        return $this->offenceAuthority;
    }

    /**
     * Set the offence date
     *
     * @param \DateTime $offenceDate
     * @return LegacyOffence
     */
    public function setOffenceDate($offenceDate)
    {
        $this->offenceDate = $offenceDate;

        return $this;
    }

    /**
     * Get the offence date
     *
     * @return \DateTime
     */
    public function getOffenceDate()
    {
        return $this->offenceDate;
    }

    /**
     * Set the offence to date
     *
     * @param \DateTime $offenceToDate
     * @return LegacyOffence
     */
    public function setOffenceToDate($offenceToDate)
    {
        $this->offenceToDate = $offenceToDate;

        return $this;
    }

    /**
     * Get the offence to date
     *
     * @return \DateTime
     */
    public function getOffenceToDate()
    {
        return $this->offenceToDate;
    }

    /**
     * Set the offence type
     *
     * @param string $offenceType
     * @return LegacyOffence
     */
    public function setOffenceType($offenceType)
    {
        $this->offenceType = $offenceType;

        return $this;
    }

    /**
     * Get the offence type
     *
     * @return string
     */
    public function getOffenceType()
    {
        return $this->offenceType;
    }

    /**
     * Set the offender name
     *
     * @param string $offenderName
     * @return LegacyOffence
     */
    public function setOffenderName($offenderName)
    {
        $this->offenderName = $offenderName;

        return $this;
    }

    /**
     * Get the offender name
     *
     * @return string
     */
    public function getOffenderName()
    {
        return $this->offenderName;
    }

    /**
     * Set the points
     *
     * @param int $points
     * @return LegacyOffence
     */
    public function setPoints($points)
    {
        $this->points = $points;

        return $this;
    }

    /**
     * Get the points
     *
     * @return int
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Set the position
     *
     * @param string $position
     * @return LegacyOffence
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get the position
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }
}
