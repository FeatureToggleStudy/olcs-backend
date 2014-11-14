<?php

namespace Olcs\Db\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * SeriousInfringement Entity
 *
 * Auto-Generated
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="serious_infringement",
 *    indexes={
 *        @ORM\Index(name="fk_serious_infringement_cases1_idx", 
 *            columns={"case_id"}),
 *        @ORM\Index(name="fk_serious_infringement_user1_idx", 
 *            columns={"erru_response_user_id"}),
 *        @ORM\Index(name="fk_serious_infringement_country1_idx", 
 *            columns={"member_state_code"}),
 *        @ORM\Index(name="fk_serious_infringement_si_category1_idx", 
 *            columns={"si_category_id"}),
 *        @ORM\Index(name="fk_serious_infringement_si_category_type1_idx", 
 *            columns={"si_category_type_id"}),
 *        @ORM\Index(name="fk_serious_infringement_user2_idx", 
 *            columns={"created_by"}),
 *        @ORM\Index(name="fk_serious_infringement_user3_idx", 
 *            columns={"last_modified_by"})
 *    }
 * )
 */
class SeriousInfringement implements Interfaces\EntityInterface
{

    /**
     * Erru response user
     *
     * @var \Olcs\Db\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Olcs\Db\Entity\User", fetch="LAZY")
     * @ORM\JoinColumn(name="erru_response_user_id", referencedColumnName="id", nullable=true)
     */
    protected $erruResponseUser;

    /**
     * Si category type
     *
     * @var \Olcs\Db\Entity\SiCategoryType
     *
     * @ORM\ManyToOne(targetEntity="Olcs\Db\Entity\SiCategoryType", fetch="LAZY")
     * @ORM\JoinColumn(name="si_category_type_id", referencedColumnName="id", nullable=false)
     */
    protected $siCategoryType;

    /**
     * Member state code
     *
     * @var \Olcs\Db\Entity\Country
     *
     * @ORM\ManyToOne(targetEntity="Olcs\Db\Entity\Country", fetch="LAZY")
     * @ORM\JoinColumn(name="member_state_code", referencedColumnName="id", nullable=true)
     */
    protected $memberStateCode;

    /**
     * Check date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="check_date", nullable=true)
     */
    protected $checkDate;

    /**
     * Erru response sent
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="erru_response_sent", nullable=false)
     */
    protected $erruResponseSent = 0;

    /**
     * Erru response time
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="erru_response_time", nullable=true)
     */
    protected $erruResponseTime;

    /**
     * Infringement date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="infringement_date", nullable=true)
     */
    protected $infringementDate;

    /**
     * Notification number
     *
     * @var string
     *
     * @ORM\Column(type="string", name="notification_number", length=36, nullable=true)
     */
    protected $notificationNumber;

    /**
     * Reason
     *
     * @var string
     *
     * @ORM\Column(type="string", name="reason", length=500, nullable=true)
     */
    protected $reason;

    /**
     * Applied penaltie
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Olcs\Db\Entity\SiPenalty", mappedBy="seriousInfringement")
     */
    protected $appliedPenalties;

    /**
     * Imposed erru
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Olcs\Db\Entity\SiPenaltyErruImposed", mappedBy="seriousInfringement")
     */
    protected $imposedErrus;

    /**
     * Requested erru
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Olcs\Db\Entity\SiPenaltyErruRequested", mappedBy="seriousInfringement")
     */
    protected $requestedErrus;

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
     * Created by
     *
     * @var \Olcs\Db\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Olcs\Db\Entity\User", fetch="LAZY")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=true)
     */
    protected $createdBy;

    /**
     * Last modified by
     *
     * @var \Olcs\Db\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Olcs\Db\Entity\User", fetch="LAZY")
     * @ORM\JoinColumn(name="last_modified_by", referencedColumnName="id", nullable=true)
     */
    protected $lastModifiedBy;

    /**
     * Si category
     *
     * @var \Olcs\Db\Entity\SiCategory
     *
     * @ORM\ManyToOne(targetEntity="Olcs\Db\Entity\SiCategory", fetch="LAZY")
     * @ORM\JoinColumn(name="si_category_id", referencedColumnName="id", nullable=false)
     */
    protected $siCategory;

    /**
     * Case
     *
     * @var \Olcs\Db\Entity\Cases
     *
     * @ORM\ManyToOne(targetEntity="Olcs\Db\Entity\Cases", fetch="LAZY")
     * @ORM\JoinColumn(name="case_id", referencedColumnName="id", nullable=false)
     */
    protected $case;

    /**
     * Deleted date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="deleted_date", nullable=true)
     */
    protected $deletedDate;

    /**
     * Created on
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="created_on", nullable=true)
     */
    protected $createdOn;

    /**
     * Last modified on
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="last_modified_on", nullable=true)
     */
    protected $lastModifiedOn;

    /**
     * Version
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="version", nullable=false)
     * @ORM\Version
     */
    protected $version;

    /**
     * Initialise the collections
     */
    public function __construct()
    {
        $this->appliedPenalties = new ArrayCollection();
        $this->imposedErrus = new ArrayCollection();
        $this->requestedErrus = new ArrayCollection();
    }

    /**
     * Set the erru response user
     *
     * @param \Olcs\Db\Entity\User $erruResponseUser
     * @return SeriousInfringement
     */
    public function setErruResponseUser($erruResponseUser)
    {
        $this->erruResponseUser = $erruResponseUser;

        return $this;
    }

    /**
     * Get the erru response user
     *
     * @return \Olcs\Db\Entity\User
     */
    public function getErruResponseUser()
    {
        return $this->erruResponseUser;
    }

    /**
     * Set the si category type
     *
     * @param \Olcs\Db\Entity\SiCategoryType $siCategoryType
     * @return SeriousInfringement
     */
    public function setSiCategoryType($siCategoryType)
    {
        $this->siCategoryType = $siCategoryType;

        return $this;
    }

    /**
     * Get the si category type
     *
     * @return \Olcs\Db\Entity\SiCategoryType
     */
    public function getSiCategoryType()
    {
        return $this->siCategoryType;
    }

    /**
     * Set the member state code
     *
     * @param \Olcs\Db\Entity\Country $memberStateCode
     * @return SeriousInfringement
     */
    public function setMemberStateCode($memberStateCode)
    {
        $this->memberStateCode = $memberStateCode;

        return $this;
    }

    /**
     * Get the member state code
     *
     * @return \Olcs\Db\Entity\Country
     */
    public function getMemberStateCode()
    {
        return $this->memberStateCode;
    }

    /**
     * Set the check date
     *
     * @param \DateTime $checkDate
     * @return SeriousInfringement
     */
    public function setCheckDate($checkDate)
    {
        $this->checkDate = $checkDate;

        return $this;
    }

    /**
     * Get the check date
     *
     * @return \DateTime
     */
    public function getCheckDate()
    {
        return $this->checkDate;
    }

    /**
     * Set the erru response sent
     *
     * @param string $erruResponseSent
     * @return SeriousInfringement
     */
    public function setErruResponseSent($erruResponseSent)
    {
        $this->erruResponseSent = $erruResponseSent;

        return $this;
    }

    /**
     * Get the erru response sent
     *
     * @return string
     */
    public function getErruResponseSent()
    {
        return $this->erruResponseSent;
    }

    /**
     * Set the erru response time
     *
     * @param \DateTime $erruResponseTime
     * @return SeriousInfringement
     */
    public function setErruResponseTime($erruResponseTime)
    {
        $this->erruResponseTime = $erruResponseTime;

        return $this;
    }

    /**
     * Get the erru response time
     *
     * @return \DateTime
     */
    public function getErruResponseTime()
    {
        return $this->erruResponseTime;
    }

    /**
     * Set the infringement date
     *
     * @param \DateTime $infringementDate
     * @return SeriousInfringement
     */
    public function setInfringementDate($infringementDate)
    {
        $this->infringementDate = $infringementDate;

        return $this;
    }

    /**
     * Get the infringement date
     *
     * @return \DateTime
     */
    public function getInfringementDate()
    {
        return $this->infringementDate;
    }

    /**
     * Set the notification number
     *
     * @param string $notificationNumber
     * @return SeriousInfringement
     */
    public function setNotificationNumber($notificationNumber)
    {
        $this->notificationNumber = $notificationNumber;

        return $this;
    }

    /**
     * Get the notification number
     *
     * @return string
     */
    public function getNotificationNumber()
    {
        return $this->notificationNumber;
    }

    /**
     * Set the reason
     *
     * @param string $reason
     * @return SeriousInfringement
     */
    public function setReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * Get the reason
     *
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Set the applied penaltie
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $appliedPenalties
     * @return SeriousInfringement
     */
    public function setAppliedPenalties($appliedPenalties)
    {
        $this->appliedPenalties = $appliedPenalties;

        return $this;
    }

    /**
     * Get the applied penalties
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getAppliedPenalties()
    {
        return $this->appliedPenalties;
    }

    /**
     * Add a applied penalties
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $appliedPenalties
     * @return SeriousInfringement
     */
    public function addAppliedPenalties($appliedPenalties)
    {
        if ($appliedPenalties instanceof ArrayCollection) {
            $this->appliedPenalties = new ArrayCollection(
                array_merge(
                    $this->appliedPenalties->toArray(),
                    $appliedPenalties->toArray()
                )
            );
        } elseif (!$this->appliedPenalties->contains($appliedPenalties)) {
            $this->appliedPenalties->add($appliedPenalties);
        }

        return $this;
    }

    /**
     * Remove a applied penalties
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $appliedPenalties
     * @return SeriousInfringement
     */
    public function removeAppliedPenalties($appliedPenalties)
    {
        if ($this->appliedPenalties->contains($appliedPenalties)) {
            $this->appliedPenalties->removeElement($appliedPenalties);
        }

        return $this;
    }

    /**
     * Set the imposed erru
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $imposedErrus
     * @return SeriousInfringement
     */
    public function setImposedErrus($imposedErrus)
    {
        $this->imposedErrus = $imposedErrus;

        return $this;
    }

    /**
     * Get the imposed errus
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getImposedErrus()
    {
        return $this->imposedErrus;
    }

    /**
     * Add a imposed errus
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $imposedErrus
     * @return SeriousInfringement
     */
    public function addImposedErrus($imposedErrus)
    {
        if ($imposedErrus instanceof ArrayCollection) {
            $this->imposedErrus = new ArrayCollection(
                array_merge(
                    $this->imposedErrus->toArray(),
                    $imposedErrus->toArray()
                )
            );
        } elseif (!$this->imposedErrus->contains($imposedErrus)) {
            $this->imposedErrus->add($imposedErrus);
        }

        return $this;
    }

    /**
     * Remove a imposed errus
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $imposedErrus
     * @return SeriousInfringement
     */
    public function removeImposedErrus($imposedErrus)
    {
        if ($this->imposedErrus->contains($imposedErrus)) {
            $this->imposedErrus->removeElement($imposedErrus);
        }

        return $this;
    }

    /**
     * Set the requested erru
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $requestedErrus
     * @return SeriousInfringement
     */
    public function setRequestedErrus($requestedErrus)
    {
        $this->requestedErrus = $requestedErrus;

        return $this;
    }

    /**
     * Get the requested errus
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getRequestedErrus()
    {
        return $this->requestedErrus;
    }

    /**
     * Add a requested errus
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $requestedErrus
     * @return SeriousInfringement
     */
    public function addRequestedErrus($requestedErrus)
    {
        if ($requestedErrus instanceof ArrayCollection) {
            $this->requestedErrus = new ArrayCollection(
                array_merge(
                    $this->requestedErrus->toArray(),
                    $requestedErrus->toArray()
                )
            );
        } elseif (!$this->requestedErrus->contains($requestedErrus)) {
            $this->requestedErrus->add($requestedErrus);
        }

        return $this;
    }

    /**
     * Remove a requested errus
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $requestedErrus
     * @return SeriousInfringement
     */
    public function removeRequestedErrus($requestedErrus)
    {
        if ($this->requestedErrus->contains($requestedErrus)) {
            $this->requestedErrus->removeElement($requestedErrus);
        }

        return $this;
    }

    /**
     * Clear properties
     *
     * @param type $properties
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

    /**
     * Set the id
     *
     * @param int $id
     * @return \Olcs\Db\Entity\Interfaces\EntityInterface
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
     * Set the created by
     *
     * @param \Olcs\Db\Entity\User $createdBy
     * @return \Olcs\Db\Entity\Interfaces\EntityInterface
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get the created by
     *
     * @return \Olcs\Db\Entity\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set the last modified by
     *
     * @param \Olcs\Db\Entity\User $lastModifiedBy
     * @return \Olcs\Db\Entity\Interfaces\EntityInterface
     */
    public function setLastModifiedBy($lastModifiedBy)
    {
        $this->lastModifiedBy = $lastModifiedBy;

        return $this;
    }

    /**
     * Get the last modified by
     *
     * @return \Olcs\Db\Entity\User
     */
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }

    /**
     * Set the si category
     *
     * @param \Olcs\Db\Entity\SiCategory $siCategory
     * @return \Olcs\Db\Entity\Interfaces\EntityInterface
     */
    public function setSiCategory($siCategory)
    {
        $this->siCategory = $siCategory;

        return $this;
    }

    /**
     * Get the si category
     *
     * @return \Olcs\Db\Entity\SiCategory
     */
    public function getSiCategory()
    {
        return $this->siCategory;
    }

    /**
     * Set the case
     *
     * @param \Olcs\Db\Entity\Cases $case
     * @return \Olcs\Db\Entity\Interfaces\EntityInterface
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
     * Set the deleted date
     *
     * @param \DateTime $deletedDate
     * @return \Olcs\Db\Entity\Interfaces\EntityInterface
     */
    public function setDeletedDate($deletedDate)
    {
        $this->deletedDate = $deletedDate;

        return $this;
    }

    /**
     * Get the deleted date
     *
     * @return \DateTime
     */
    public function getDeletedDate()
    {
        return $this->deletedDate;
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return !is_null($this->deletedDate);
    }

    /**
     * Set the created on
     *
     * @param \DateTime $createdOn
     * @return \Olcs\Db\Entity\Interfaces\EntityInterface
     */
    public function setCreatedOn($createdOn)
    {
        $this->createdOn = $createdOn;

        return $this;
    }

    /**
     * Get the created on
     *
     * @return \DateTime
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    /**
     * Set the createdOn field on persist
     *
     * @ORM\PrePersist
     */
    public function setCreatedOnBeforePersist()
    {
        $this->setCreatedOn(new \DateTime('NOW'));
    }

    /**
     * Set the last modified on
     *
     * @param \DateTime $lastModifiedOn
     * @return \Olcs\Db\Entity\Interfaces\EntityInterface
     */
    public function setLastModifiedOn($lastModifiedOn)
    {
        $this->lastModifiedOn = $lastModifiedOn;

        return $this;
    }

    /**
     * Get the last modified on
     *
     * @return \DateTime
     */
    public function getLastModifiedOn()
    {
        return $this->lastModifiedOn;
    }

    /**
     * Set the lastModifiedOn field on persist
     *
     * @ORM\PreUpdate
     */
    public function setLastModifiedOnBeforeUpdate()
    {
        $this->setLastModifiedOn(new \DateTime('NOW'));
    }

    /**
     * Set the version
     *
     * @param int $version
     * @return \Olcs\Db\Entity\Interfaces\EntityInterface
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
     * Set the version field on persist
     *
     * @ORM\PrePersist
     */
    public function setVersionBeforePersist()
    {
        $this->setVersion(1);
    }
}
