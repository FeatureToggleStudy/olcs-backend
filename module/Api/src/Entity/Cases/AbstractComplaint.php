<?php

namespace Dvsa\Olcs\Api\Entity\Cases;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Complaint Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="complaint",
 *    indexes={
 *        @ORM\Index(name="ix_complaint_complainant_contact_details_id",
     *     columns={"complainant_contact_details_id"}),
 *        @ORM\Index(name="ix_complaint_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_complaint_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_complaint_status", columns={"status"}),
 *        @ORM\Index(name="ix_complaint_complaint_type", columns={"complaint_type"}),
 *        @ORM\Index(name="ix_complaint_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_complaint_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
abstract class AbstractComplaint implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;

    /**
     * Case
     *
     * @var \Dvsa\Olcs\Api\Entity\Cases\Cases
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Cases\Cases",
     *     fetch="LAZY",
     *     inversedBy="complaints"
     * )
     * @ORM\JoinColumn(name="case_id", referencedColumnName="id", nullable=false)
     */
    protected $case;

    /**
     * Closed date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="closed_date", nullable=true)
     */
    protected $closedDate;

    /**
     * Complainant contact details
     *
     * @var \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails",
     *     fetch="LAZY",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="complainant_contact_details_id", referencedColumnName="id", nullable=true)
     */
    protected $complainantContactDetails;

    /**
     * Complaint date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="complaint_date", nullable=true)
     */
    protected $complaintDate;

    /**
     * Complaint type
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="complaint_type", referencedColumnName="id", nullable=true)
     */
    protected $complaintType;

    /**
     * Created by
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=true)
     * @Gedmo\Blameable(on="create")
     */
    protected $createdBy;

    /**
     * Created on
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="created_on", nullable=true)
     */
    protected $createdOn;

    /**
     * Deleted date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="deleted_date", nullable=true)
     */
    protected $deletedDate;

    /**
     * Description
     *
     * @var string
     *
     * @ORM\Column(type="string", name="description", length=4000, nullable=true)
     */
    protected $description;

    /**
     * Driver family name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="driver_family_name", length=40, nullable=true)
     */
    protected $driverFamilyName;

    /**
     * Driver forename
     *
     * @var string
     *
     * @ORM\Column(type="string", name="driver_forename", length=40, nullable=true)
     */
    protected $driverForename;

    /**
     * Identifier - Id
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * Is compliance
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_compliance", nullable=false)
     */
    protected $isCompliance;

    /**
     * Last modified by
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="last_modified_by", referencedColumnName="id", nullable=true)
     * @Gedmo\Blameable(on="update")
     */
    protected $lastModifiedBy;

    /**
     * Last modified on
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="last_modified_on", nullable=true)
     */
    protected $lastModifiedOn;

    /**
     * Olbs key
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="olbs_key", nullable=true)
     */
    protected $olbsKey;

    /**
     * Operating centre
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre",
     *     inversedBy="complaints",
     *     fetch="LAZY"
     * )
     * @ORM\JoinTable(name="oc_complaint",
     *     joinColumns={
     *         @ORM\JoinColumn(name="complaint_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="operating_centre_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $operatingCentres;

    /**
     * Status
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="status", referencedColumnName="id", nullable=true)
     */
    protected $status;

    /**
     * Version
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="version", nullable=false, options={"default": 1})
     * @ORM\Version
     */
    protected $version = 1;

    /**
     * Vrm
     *
     * @var string
     *
     * @ORM\Column(type="string", name="vrm", length=20, nullable=true)
     */
    protected $vrm;

    /**
     * Initialise the collections
     *
     * @return void
     */
    public function __construct()
    {
        $this->initCollections();
    }

    /**
     * Initialise the collections
     *
     * @return void
     */
    public function initCollections()
    {
        $this->operatingCentres = new ArrayCollection();
    }

    /**
     * Set the case
     *
     * @param \Dvsa\Olcs\Api\Entity\Cases\Cases $case entity being set as the value
     *
     * @return Complaint
     */
    public function setCase($case)
    {
        $this->case = $case;

        return $this;
    }

    /**
     * Get the case
     *
     * @return \Dvsa\Olcs\Api\Entity\Cases\Cases
     */
    public function getCase()
    {
        return $this->case;
    }

    /**
     * Set the closed date
     *
     * @param \DateTime $closedDate new value being set
     *
     * @return Complaint
     */
    public function setClosedDate($closedDate)
    {
        $this->closedDate = $closedDate;

        return $this;
    }

    /**
     * Get the closed date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime
     */
    public function getClosedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->closedDate);
        }

        return $this->closedDate;
    }

    /**
     * Set the complainant contact details
     *
     * @param \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails $complainantContactDetails entity being set as the value
     *
     * @return Complaint
     */
    public function setComplainantContactDetails($complainantContactDetails)
    {
        $this->complainantContactDetails = $complainantContactDetails;

        return $this;
    }

    /**
     * Get the complainant contact details
     *
     * @return \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails
     */
    public function getComplainantContactDetails()
    {
        return $this->complainantContactDetails;
    }

    /**
     * Set the complaint date
     *
     * @param \DateTime $complaintDate new value being set
     *
     * @return Complaint
     */
    public function setComplaintDate($complaintDate)
    {
        $this->complaintDate = $complaintDate;

        return $this;
    }

    /**
     * Get the complaint date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime
     */
    public function getComplaintDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->complaintDate);
        }

        return $this->complaintDate;
    }

    /**
     * Set the complaint type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $complaintType entity being set as the value
     *
     * @return Complaint
     */
    public function setComplaintType($complaintType)
    {
        $this->complaintType = $complaintType;

        return $this;
    }

    /**
     * Get the complaint type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getComplaintType()
    {
        return $this->complaintType;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return Complaint
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get the created by
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set the created on
     *
     * @param \DateTime $createdOn new value being set
     *
     * @return Complaint
     */
    public function setCreatedOn($createdOn)
    {
        $this->createdOn = $createdOn;

        return $this;
    }

    /**
     * Get the created on
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime
     */
    public function getCreatedOn($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->createdOn);
        }

        return $this->createdOn;
    }

    /**
     * Set the deleted date
     *
     * @param \DateTime $deletedDate new value being set
     *
     * @return Complaint
     */
    public function setDeletedDate($deletedDate)
    {
        $this->deletedDate = $deletedDate;

        return $this;
    }

    /**
     * Get the deleted date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime
     */
    public function getDeletedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->deletedDate);
        }

        return $this->deletedDate;
    }

    /**
     * Set the description
     *
     * @param string $description new value being set
     *
     * @return Complaint
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the driver family name
     *
     * @param string $driverFamilyName new value being set
     *
     * @return Complaint
     */
    public function setDriverFamilyName($driverFamilyName)
    {
        $this->driverFamilyName = $driverFamilyName;

        return $this;
    }

    /**
     * Get the driver family name
     *
     * @return string
     */
    public function getDriverFamilyName()
    {
        return $this->driverFamilyName;
    }

    /**
     * Set the driver forename
     *
     * @param string $driverForename new value being set
     *
     * @return Complaint
     */
    public function setDriverForename($driverForename)
    {
        $this->driverForename = $driverForename;

        return $this;
    }

    /**
     * Get the driver forename
     *
     * @return string
     */
    public function getDriverForename()
    {
        return $this->driverForename;
    }

    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return Complaint
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the is compliance
     *
     * @param boolean $isCompliance new value being set
     *
     * @return Complaint
     */
    public function setIsCompliance($isCompliance)
    {
        $this->isCompliance = $isCompliance;

        return $this;
    }

    /**
     * Get the is compliance
     *
     * @return boolean
     */
    public function getIsCompliance()
    {
        return $this->isCompliance;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy entity being set as the value
     *
     * @return Complaint
     */
    public function setLastModifiedBy($lastModifiedBy)
    {
        $this->lastModifiedBy = $lastModifiedBy;

        return $this;
    }

    /**
     * Get the last modified by
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User
     */
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }

    /**
     * Set the last modified on
     *
     * @param \DateTime $lastModifiedOn new value being set
     *
     * @return Complaint
     */
    public function setLastModifiedOn($lastModifiedOn)
    {
        $this->lastModifiedOn = $lastModifiedOn;

        return $this;
    }

    /**
     * Get the last modified on
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime
     */
    public function getLastModifiedOn($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->lastModifiedOn);
        }

        return $this->lastModifiedOn;
    }

    /**
     * Set the olbs key
     *
     * @param int $olbsKey new value being set
     *
     * @return Complaint
     */
    public function setOlbsKey($olbsKey)
    {
        $this->olbsKey = $olbsKey;

        return $this;
    }

    /**
     * Get the olbs key
     *
     * @return int
     */
    public function getOlbsKey()
    {
        return $this->olbsKey;
    }

    /**
     * Set the operating centre
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $operatingCentres collection being set as the value
     *
     * @return Complaint
     */
    public function setOperatingCentres($operatingCentres)
    {
        $this->operatingCentres = $operatingCentres;

        return $this;
    }

    /**
     * Get the operating centres
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getOperatingCentres()
    {
        return $this->operatingCentres;
    }

    /**
     * Add a operating centres
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $operatingCentres collection being added
     *
     * @return Complaint
     */
    public function addOperatingCentres($operatingCentres)
    {
        if ($operatingCentres instanceof ArrayCollection) {
            $this->operatingCentres = new ArrayCollection(
                array_merge(
                    $this->operatingCentres->toArray(),
                    $operatingCentres->toArray()
                )
            );
        } elseif (!$this->operatingCentres->contains($operatingCentres)) {
            $this->operatingCentres->add($operatingCentres);
        }

        return $this;
    }

    /**
     * Remove a operating centres
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $operatingCentres collection being removed
     *
     * @return Complaint
     */
    public function removeOperatingCentres($operatingCentres)
    {
        if ($this->operatingCentres->contains($operatingCentres)) {
            $this->operatingCentres->removeElement($operatingCentres);
        }

        return $this;
    }

    /**
     * Set the status
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $status entity being set as the value
     *
     * @return Complaint
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the status
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return Complaint
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

    /**
     * Set the vrm
     *
     * @param string $vrm new value being set
     *
     * @return Complaint
     */
    public function setVrm($vrm)
    {
        $this->vrm = $vrm;

        return $this;
    }

    /**
     * Get the vrm
     *
     * @return string
     */
    public function getVrm()
    {
        return $this->vrm;
    }

    /**
     * Set the createdOn field on persist
     *
     * @ORM\PrePersist
     *
     * @return void
     */
    public function setCreatedOnBeforePersist()
    {
        $this->createdOn = new \DateTime();
    }

    /**
     * Set the lastModifiedOn field on persist
     *
     * @ORM\PreUpdate
     *
     * @return void
     */
    public function setLastModifiedOnBeforeUpdate()
    {
        $this->lastModifiedOn = new \DateTime();
    }

    /**
     * Clear properties
     *
     * @param array $properties array of properties
     *
     * @return void
     */
    public function clearProperties($properties = array())
    {
        foreach ($properties as $property) {
            if (property_exists($this, $property)) {
                if ($this->$property instanceof Collection) {
                    $this->$property = new ArrayCollection(array());
                } else {
                    $this->$property = null;
                }
            }
        }
    }
}
