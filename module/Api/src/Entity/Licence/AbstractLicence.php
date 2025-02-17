<?php

namespace Dvsa\Olcs\Api\Entity\Licence;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Licence Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="licence",
 *    indexes={
 *        @ORM\Index(name="ix_licence_enforcement_area_id", columns={"enforcement_area_id"}),
 *        @ORM\Index(name="ix_licence_traffic_area_id", columns={"traffic_area_id"}),
 *        @ORM\Index(name="ix_licence_organisation_id", columns={"organisation_id"}),
 *        @ORM\Index(name="ix_licence_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_licence_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_licence_goods_or_psv", columns={"goods_or_psv"}),
 *        @ORM\Index(name="ix_licence_licence_type", columns={"licence_type"}),
 *        @ORM\Index(name="ix_licence_status", columns={"status"}),
 *        @ORM\Index(name="ix_licence_tachograph_ins", columns={"tachograph_ins"}),
 *        @ORM\Index(name="ix_licence_correspondence_cd_id", columns={"correspondence_cd_id"}),
 *        @ORM\Index(name="ix_licence_establishment_cd_id", columns={"establishment_cd_id"}),
 *        @ORM\Index(name="ix_licence_transport_consultant_cd_id",
     *     columns={"transport_consultant_cd_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_licence_lic_no", columns={"lic_no"}),
 *        @ORM\UniqueConstraint(name="uk_licence_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
abstract class AbstractLicence implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;

    /**
     * Cns date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="cns_date", nullable=true)
     */
    protected $cnsDate;

    /**
     * Correspondence cd
     *
     * @var \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails",
     *     fetch="LAZY",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="correspondence_cd_id", referencedColumnName="id", nullable=true)
     */
    protected $correspondenceCd;

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
     * Curtailed date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="curtailed_date", nullable=true)
     */
    protected $curtailedDate;

    /**
     * Decision
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Pi\Decision",
     *     inversedBy="licences",
     *     fetch="LAZY"
     * )
     * @ORM\JoinTable(name="licence_status_decision",
     *     joinColumns={
     *         @ORM\JoinColumn(name="licence_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="decision_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $decisions;

    /**
     * Deleted date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="deleted_date", nullable=true)
     */
    protected $deletedDate;

    /**
     * Enforcement area
     *
     * @var \Dvsa\Olcs\Api\Entity\EnforcementArea\EnforcementArea
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\EnforcementArea\EnforcementArea",
     *     fetch="LAZY"
     * )
     * @ORM\JoinColumn(name="enforcement_area_id", referencedColumnName="id", nullable=true)
     */
    protected $enforcementArea;

    /**
     * Establishment cd
     *
     * @var \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails", fetch="LAZY")
     * @ORM\JoinColumn(name="establishment_cd_id", referencedColumnName="id", nullable=true)
     */
    protected $establishmentCd;

    /**
     * Expiry date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="expiry_date", nullable=true)
     */
    protected $expiryDate;

    /**
     * Fabs reference
     *
     * @var string
     *
     * @ORM\Column(type="string", name="fabs_reference", length=10, nullable=true)
     */
    protected $fabsReference;

    /**
     * Fee date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="fee_date", nullable=true)
     */
    protected $feeDate;

    /**
     * Goods or psv
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="goods_or_psv", referencedColumnName="id", nullable=true)
     */
    protected $goodsOrPsv;

    /**
     * Granted date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="granted_date", nullable=true)
     */
    protected $grantedDate;

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
     * In force date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="in_force_date", nullable=true)
     */
    protected $inForceDate;

    /**
     * Is maintenance suitable
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="is_maintenance_suitable", nullable=true)
     */
    protected $isMaintenanceSuitable;

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
     * Lic no
     *
     * @var string
     *
     * @ORM\Column(type="string", name="lic_no", length=18, nullable=true)
     */
    protected $licNo;

    /**
     * Licence type
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="licence_type", referencedColumnName="id", nullable=true)
     */
    protected $licenceType;

    /**
     * Olbs key
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="olbs_key", nullable=true)
     */
    protected $olbsKey;

    /**
     * Opt out tm letter
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="opt_out_tm_letter", nullable=false, options={"default": 0})
     */
    protected $optOutTmLetter = 0;

    /**
     * Organisation
     *
     * @var \Dvsa\Olcs\Api\Entity\Organisation\Organisation
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Organisation\Organisation",
     *     fetch="LAZY",
     *     cascade={"persist"},
     *     inversedBy="licences"
     * )
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="id", nullable=false)
     */
    protected $organisation;

    /**
     * Psv discs to be printed no
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="psv_discs_to_be_printed_no", nullable=true)
     */
    protected $psvDiscsToBePrintedNo;

    /**
     * Review date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="review_date", nullable=true)
     */
    protected $reviewDate;

    /**
     * Revoked date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="revoked_date", nullable=true)
     */
    protected $revokedDate;

    /**
     * Safety ins
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="safety_ins", nullable=false, options={"default": 0})
     */
    protected $safetyIns = 0;

    /**
     * Safety ins trailers
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="safety_ins_trailers", nullable=true)
     */
    protected $safetyInsTrailers;

    /**
     * Safety ins varies
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="safety_ins_varies", nullable=true)
     */
    protected $safetyInsVaries;

    /**
     * Safety ins vehicles
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="safety_ins_vehicles", nullable=true)
     */
    protected $safetyInsVehicles;

    /**
     * Status
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="status", referencedColumnName="id", nullable=false)
     */
    protected $status;

    /**
     * Surrendered date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="surrendered_date", nullable=true)
     */
    protected $surrenderedDate;

    /**
     * Suspended date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="suspended_date", nullable=true)
     */
    protected $suspendedDate;

    /**
     * Tachograph ins
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="tachograph_ins", referencedColumnName="id", nullable=true)
     */
    protected $tachographIns;

    /**
     * Tachograph ins name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="tachograph_ins_name", length=90, nullable=true)
     */
    protected $tachographInsName;

    /**
     * Tot auth trailers
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="tot_auth_trailers", nullable=true)
     */
    protected $totAuthTrailers;

    /**
     * Tot auth vehicles
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="tot_auth_vehicles", nullable=true)
     */
    protected $totAuthVehicles;

    /**
     * Tot community licences
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="tot_community_licences", nullable=true)
     */
    protected $totCommunityLicences;

    /**
     * Traffic area
     *
     * @var \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea", fetch="LAZY")
     * @ORM\JoinColumn(name="traffic_area_id", referencedColumnName="id", nullable=true)
     */
    protected $trafficArea;

    /**
     * Trailers in possession
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="trailers_in_possession", nullable=true)
     */
    protected $trailersInPossession;

    /**
     * Translate to welsh
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="translate_to_welsh", nullable=false, options={"default": 0})
     */
    protected $translateToWelsh = 0;

    /**
     * Transport consultant cd
     *
     * @var \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails", fetch="LAZY")
     * @ORM\JoinColumn(name="transport_consultant_cd_id", referencedColumnName="id", nullable=true)
     */
    protected $transportConsultantCd;

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
     * Vi action
     *
     * @var string
     *
     * @ORM\Column(type="string", name="vi_action", length=1, nullable=true)
     */
    protected $viAction;

    /**
     * Application
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Application\Application", mappedBy="licence")
     */
    protected $applications;

    /**
     * Bus reg
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Bus\BusReg",
     *     mappedBy="licence",
     *     cascade={"persist"}
     * )
     */
    protected $busRegs;

    /**
     * Case
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Cases\Cases", mappedBy="licence")
     */
    protected $cases;

    /**
     * Change of entity
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Organisation\ChangeOfEntity",
     *     mappedBy="licence"
     * )
     */
    protected $changeOfEntitys;

    /**
     * Community lic
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic",
     *     mappedBy="licence",
     *     fetch="EXTRA_LAZY"
     * )
     */
    protected $communityLics;

    /**
     * Company subsidiarie
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Organisation\CompanySubsidiary",
     *     mappedBy="licence"
     * )
     */
    protected $companySubsidiaries;

    /**
     * Condition undertaking
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking",
     *     mappedBy="licence"
     * )
     */
    protected $conditionUndertakings;

    /**
     * Continuation detail
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail",
     *     mappedBy="licence"
     * )
     */
    protected $continuationDetails;

    /**
     * Document
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Doc\Document", mappedBy="licence")
     */
    protected $documents;

    /**
     * Ecmt application
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Permits\EcmtPermitApplication",
     *     mappedBy="licence"
     * )
     */
    protected $ecmtApplications;

    /**
     * Fee
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Fee\Fee", mappedBy="licence")
     */
    protected $fees;

    /**
     * Grace period
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Licence\GracePeriod", mappedBy="licence")
     */
    protected $gracePeriods;

    /**
     * Irhp application
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Permits\IrhpApplication", mappedBy="licence")
     */
    protected $irhpApplications;

    /**
     * Operating centre
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre",
     *     mappedBy="licence"
     * )
     */
    protected $operatingCentres;

    /**
     * Read audit
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Licence\LicenceReadAudit", mappedBy="licence")
     */
    protected $readAudits;

    /**
     * Licence status rule
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Licence\LicenceStatusRule",
     *     mappedBy="licence"
     * )
     */
    protected $licenceStatusRules;

    /**
     * Licence vehicle
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle", mappedBy="licence")
     */
    protected $licenceVehicles;

    /**
     * Private hire licence
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Licence\PrivateHireLicence",
     *     mappedBy="licence"
     * )
     */
    protected $privateHireLicences;

    /**
     * Psv disc
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Licence\PsvDisc", mappedBy="licence")
     * @ORM\OrderBy({"discNo" = "ASC"})
     */
    protected $psvDiscs;

    /**
     * Publication link
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Publication\PublicationLink",
     *     mappedBy="licence"
     * )
     */
    protected $publicationLinks;

    /**
     * Trading name
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Organisation\TradingName", mappedBy="licence")
     */
    protected $tradingNames;

    /**
     * Tm licence
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence",
     *     mappedBy="licence"
     * )
     */
    protected $tmLicences;

    /**
     * Workshop
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Licence\Workshop", mappedBy="licence")
     */
    protected $workshops;

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
        $this->decisions = new ArrayCollection();
        $this->applications = new ArrayCollection();
        $this->busRegs = new ArrayCollection();
        $this->cases = new ArrayCollection();
        $this->changeOfEntitys = new ArrayCollection();
        $this->communityLics = new ArrayCollection();
        $this->companySubsidiaries = new ArrayCollection();
        $this->conditionUndertakings = new ArrayCollection();
        $this->continuationDetails = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->ecmtApplications = new ArrayCollection();
        $this->fees = new ArrayCollection();
        $this->gracePeriods = new ArrayCollection();
        $this->irhpApplications = new ArrayCollection();
        $this->operatingCentres = new ArrayCollection();
        $this->readAudits = new ArrayCollection();
        $this->licenceStatusRules = new ArrayCollection();
        $this->licenceVehicles = new ArrayCollection();
        $this->privateHireLicences = new ArrayCollection();
        $this->psvDiscs = new ArrayCollection();
        $this->publicationLinks = new ArrayCollection();
        $this->tradingNames = new ArrayCollection();
        $this->tmLicences = new ArrayCollection();
        $this->workshops = new ArrayCollection();
    }

    /**
     * Set the cns date
     *
     * @param \DateTime $cnsDate new value being set
     *
     * @return Licence
     */
    public function setCnsDate($cnsDate)
    {
        $this->cnsDate = $cnsDate;

        return $this;
    }

    /**
     * Get the cns date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime
     */
    public function getCnsDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->cnsDate);
        }

        return $this->cnsDate;
    }

    /**
     * Set the correspondence cd
     *
     * @param \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails $correspondenceCd entity being set as the value
     *
     * @return Licence
     */
    public function setCorrespondenceCd($correspondenceCd)
    {
        $this->correspondenceCd = $correspondenceCd;

        return $this;
    }

    /**
     * Get the correspondence cd
     *
     * @return \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails
     */
    public function getCorrespondenceCd()
    {
        return $this->correspondenceCd;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return Licence
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
     * @return Licence
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
     * Set the curtailed date
     *
     * @param \DateTime $curtailedDate new value being set
     *
     * @return Licence
     */
    public function setCurtailedDate($curtailedDate)
    {
        $this->curtailedDate = $curtailedDate;

        return $this;
    }

    /**
     * Get the curtailed date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime
     */
    public function getCurtailedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->curtailedDate);
        }

        return $this->curtailedDate;
    }

    /**
     * Set the decision
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $decisions collection being set as the value
     *
     * @return Licence
     */
    public function setDecisions($decisions)
    {
        $this->decisions = $decisions;

        return $this;
    }

    /**
     * Get the decisions
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getDecisions()
    {
        return $this->decisions;
    }

    /**
     * Add a decisions
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $decisions collection being added
     *
     * @return Licence
     */
    public function addDecisions($decisions)
    {
        if ($decisions instanceof ArrayCollection) {
            $this->decisions = new ArrayCollection(
                array_merge(
                    $this->decisions->toArray(),
                    $decisions->toArray()
                )
            );
        } elseif (!$this->decisions->contains($decisions)) {
            $this->decisions->add($decisions);
        }

        return $this;
    }

    /**
     * Remove a decisions
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $decisions collection being removed
     *
     * @return Licence
     */
    public function removeDecisions($decisions)
    {
        if ($this->decisions->contains($decisions)) {
            $this->decisions->removeElement($decisions);
        }

        return $this;
    }

    /**
     * Set the deleted date
     *
     * @param \DateTime $deletedDate new value being set
     *
     * @return Licence
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
     * Set the enforcement area
     *
     * @param \Dvsa\Olcs\Api\Entity\EnforcementArea\EnforcementArea $enforcementArea entity being set as the value
     *
     * @return Licence
     */
    public function setEnforcementArea($enforcementArea)
    {
        $this->enforcementArea = $enforcementArea;

        return $this;
    }

    /**
     * Get the enforcement area
     *
     * @return \Dvsa\Olcs\Api\Entity\EnforcementArea\EnforcementArea
     */
    public function getEnforcementArea()
    {
        return $this->enforcementArea;
    }

    /**
     * Set the establishment cd
     *
     * @param \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails $establishmentCd entity being set as the value
     *
     * @return Licence
     */
    public function setEstablishmentCd($establishmentCd)
    {
        $this->establishmentCd = $establishmentCd;

        return $this;
    }

    /**
     * Get the establishment cd
     *
     * @return \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails
     */
    public function getEstablishmentCd()
    {
        return $this->establishmentCd;
    }

    /**
     * Set the expiry date
     *
     * @param \DateTime $expiryDate new value being set
     *
     * @return Licence
     */
    public function setExpiryDate($expiryDate)
    {
        $this->expiryDate = $expiryDate;

        return $this;
    }

    /**
     * Get the expiry date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime
     */
    public function getExpiryDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->expiryDate);
        }

        return $this->expiryDate;
    }

    /**
     * Set the fabs reference
     *
     * @param string $fabsReference new value being set
     *
     * @return Licence
     */
    public function setFabsReference($fabsReference)
    {
        $this->fabsReference = $fabsReference;

        return $this;
    }

    /**
     * Get the fabs reference
     *
     * @return string
     */
    public function getFabsReference()
    {
        return $this->fabsReference;
    }

    /**
     * Set the fee date
     *
     * @param \DateTime $feeDate new value being set
     *
     * @return Licence
     */
    public function setFeeDate($feeDate)
    {
        $this->feeDate = $feeDate;

        return $this;
    }

    /**
     * Get the fee date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime
     */
    public function getFeeDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->feeDate);
        }

        return $this->feeDate;
    }

    /**
     * Set the goods or psv
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $goodsOrPsv entity being set as the value
     *
     * @return Licence
     */
    public function setGoodsOrPsv($goodsOrPsv)
    {
        $this->goodsOrPsv = $goodsOrPsv;

        return $this;
    }

    /**
     * Get the goods or psv
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getGoodsOrPsv()
    {
        return $this->goodsOrPsv;
    }

    /**
     * Set the granted date
     *
     * @param \DateTime $grantedDate new value being set
     *
     * @return Licence
     */
    public function setGrantedDate($grantedDate)
    {
        $this->grantedDate = $grantedDate;

        return $this;
    }

    /**
     * Get the granted date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime
     */
    public function getGrantedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->grantedDate);
        }

        return $this->grantedDate;
    }

    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return Licence
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
     * Set the in force date
     *
     * @param \DateTime $inForceDate new value being set
     *
     * @return Licence
     */
    public function setInForceDate($inForceDate)
    {
        $this->inForceDate = $inForceDate;

        return $this;
    }

    /**
     * Get the in force date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime
     */
    public function getInForceDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->inForceDate);
        }

        return $this->inForceDate;
    }

    /**
     * Set the is maintenance suitable
     *
     * @param string $isMaintenanceSuitable new value being set
     *
     * @return Licence
     */
    public function setIsMaintenanceSuitable($isMaintenanceSuitable)
    {
        $this->isMaintenanceSuitable = $isMaintenanceSuitable;

        return $this;
    }

    /**
     * Get the is maintenance suitable
     *
     * @return string
     */
    public function getIsMaintenanceSuitable()
    {
        return $this->isMaintenanceSuitable;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy entity being set as the value
     *
     * @return Licence
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
     * @return Licence
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
     * Set the lic no
     *
     * @param string $licNo new value being set
     *
     * @return Licence
     */
    public function setLicNo($licNo)
    {
        $this->licNo = $licNo;

        return $this;
    }

    /**
     * Get the lic no
     *
     * @return string
     */
    public function getLicNo()
    {
        return $this->licNo;
    }

    /**
     * Set the licence type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $licenceType entity being set as the value
     *
     * @return Licence
     */
    public function setLicenceType($licenceType)
    {
        $this->licenceType = $licenceType;

        return $this;
    }

    /**
     * Get the licence type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getLicenceType()
    {
        return $this->licenceType;
    }

    /**
     * Set the olbs key
     *
     * @param int $olbsKey new value being set
     *
     * @return Licence
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
     * Set the opt out tm letter
     *
     * @param boolean $optOutTmLetter new value being set
     *
     * @return Licence
     */
    public function setOptOutTmLetter($optOutTmLetter)
    {
        $this->optOutTmLetter = $optOutTmLetter;

        return $this;
    }

    /**
     * Get the opt out tm letter
     *
     * @return boolean
     */
    public function getOptOutTmLetter()
    {
        return $this->optOutTmLetter;
    }

    /**
     * Set the organisation
     *
     * @param \Dvsa\Olcs\Api\Entity\Organisation\Organisation $organisation entity being set as the value
     *
     * @return Licence
     */
    public function setOrganisation($organisation)
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * Get the organisation
     *
     * @return \Dvsa\Olcs\Api\Entity\Organisation\Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * Set the psv discs to be printed no
     *
     * @param int $psvDiscsToBePrintedNo new value being set
     *
     * @return Licence
     */
    public function setPsvDiscsToBePrintedNo($psvDiscsToBePrintedNo)
    {
        $this->psvDiscsToBePrintedNo = $psvDiscsToBePrintedNo;

        return $this;
    }

    /**
     * Get the psv discs to be printed no
     *
     * @return int
     */
    public function getPsvDiscsToBePrintedNo()
    {
        return $this->psvDiscsToBePrintedNo;
    }

    /**
     * Set the review date
     *
     * @param \DateTime $reviewDate new value being set
     *
     * @return Licence
     */
    public function setReviewDate($reviewDate)
    {
        $this->reviewDate = $reviewDate;

        return $this;
    }

    /**
     * Get the review date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime
     */
    public function getReviewDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->reviewDate);
        }

        return $this->reviewDate;
    }

    /**
     * Set the revoked date
     *
     * @param \DateTime $revokedDate new value being set
     *
     * @return Licence
     */
    public function setRevokedDate($revokedDate)
    {
        $this->revokedDate = $revokedDate;

        return $this;
    }

    /**
     * Get the revoked date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime
     */
    public function getRevokedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->revokedDate);
        }

        return $this->revokedDate;
    }

    /**
     * Set the safety ins
     *
     * @param string $safetyIns new value being set
     *
     * @return Licence
     */
    public function setSafetyIns($safetyIns)
    {
        $this->safetyIns = $safetyIns;

        return $this;
    }

    /**
     * Get the safety ins
     *
     * @return string
     */
    public function getSafetyIns()
    {
        return $this->safetyIns;
    }

    /**
     * Set the safety ins trailers
     *
     * @param int $safetyInsTrailers new value being set
     *
     * @return Licence
     */
    public function setSafetyInsTrailers($safetyInsTrailers)
    {
        $this->safetyInsTrailers = $safetyInsTrailers;

        return $this;
    }

    /**
     * Get the safety ins trailers
     *
     * @return int
     */
    public function getSafetyInsTrailers()
    {
        return $this->safetyInsTrailers;
    }

    /**
     * Set the safety ins varies
     *
     * @param string $safetyInsVaries new value being set
     *
     * @return Licence
     */
    public function setSafetyInsVaries($safetyInsVaries)
    {
        $this->safetyInsVaries = $safetyInsVaries;

        return $this;
    }

    /**
     * Get the safety ins varies
     *
     * @return string
     */
    public function getSafetyInsVaries()
    {
        return $this->safetyInsVaries;
    }

    /**
     * Set the safety ins vehicles
     *
     * @param int $safetyInsVehicles new value being set
     *
     * @return Licence
     */
    public function setSafetyInsVehicles($safetyInsVehicles)
    {
        $this->safetyInsVehicles = $safetyInsVehicles;

        return $this;
    }

    /**
     * Get the safety ins vehicles
     *
     * @return int
     */
    public function getSafetyInsVehicles()
    {
        return $this->safetyInsVehicles;
    }

    /**
     * Set the status
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $status entity being set as the value
     *
     * @return Licence
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
     * Set the surrendered date
     *
     * @param \DateTime $surrenderedDate new value being set
     *
     * @return Licence
     */
    public function setSurrenderedDate($surrenderedDate)
    {
        $this->surrenderedDate = $surrenderedDate;

        return $this;
    }

    /**
     * Get the surrendered date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime
     */
    public function getSurrenderedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->surrenderedDate);
        }

        return $this->surrenderedDate;
    }

    /**
     * Set the suspended date
     *
     * @param \DateTime $suspendedDate new value being set
     *
     * @return Licence
     */
    public function setSuspendedDate($suspendedDate)
    {
        $this->suspendedDate = $suspendedDate;

        return $this;
    }

    /**
     * Get the suspended date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime
     */
    public function getSuspendedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->suspendedDate);
        }

        return $this->suspendedDate;
    }

    /**
     * Set the tachograph ins
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $tachographIns entity being set as the value
     *
     * @return Licence
     */
    public function setTachographIns($tachographIns)
    {
        $this->tachographIns = $tachographIns;

        return $this;
    }

    /**
     * Get the tachograph ins
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getTachographIns()
    {
        return $this->tachographIns;
    }

    /**
     * Set the tachograph ins name
     *
     * @param string $tachographInsName new value being set
     *
     * @return Licence
     */
    public function setTachographInsName($tachographInsName)
    {
        $this->tachographInsName = $tachographInsName;

        return $this;
    }

    /**
     * Get the tachograph ins name
     *
     * @return string
     */
    public function getTachographInsName()
    {
        return $this->tachographInsName;
    }

    /**
     * Set the tot auth trailers
     *
     * @param int $totAuthTrailers new value being set
     *
     * @return Licence
     */
    public function setTotAuthTrailers($totAuthTrailers)
    {
        $this->totAuthTrailers = $totAuthTrailers;

        return $this;
    }

    /**
     * Get the tot auth trailers
     *
     * @return int
     */
    public function getTotAuthTrailers()
    {
        return $this->totAuthTrailers;
    }

    /**
     * Set the tot auth vehicles
     *
     * @param int $totAuthVehicles new value being set
     *
     * @return Licence
     */
    public function setTotAuthVehicles($totAuthVehicles)
    {
        $this->totAuthVehicles = $totAuthVehicles;

        return $this;
    }

    /**
     * Get the tot auth vehicles
     *
     * @return int
     */
    public function getTotAuthVehicles()
    {
        return $this->totAuthVehicles;
    }

    /**
     * Set the tot community licences
     *
     * @param int $totCommunityLicences new value being set
     *
     * @return Licence
     */
    public function setTotCommunityLicences($totCommunityLicences)
    {
        $this->totCommunityLicences = $totCommunityLicences;

        return $this;
    }

    /**
     * Get the tot community licences
     *
     * @return int
     */
    public function getTotCommunityLicences()
    {
        return $this->totCommunityLicences;
    }

    /**
     * Set the traffic area
     *
     * @param \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea $trafficArea entity being set as the value
     *
     * @return Licence
     */
    public function setTrafficArea($trafficArea)
    {
        $this->trafficArea = $trafficArea;

        return $this;
    }

    /**
     * Get the traffic area
     *
     * @return \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea
     */
    public function getTrafficArea()
    {
        return $this->trafficArea;
    }

    /**
     * Set the trailers in possession
     *
     * @param int $trailersInPossession new value being set
     *
     * @return Licence
     */
    public function setTrailersInPossession($trailersInPossession)
    {
        $this->trailersInPossession = $trailersInPossession;

        return $this;
    }

    /**
     * Get the trailers in possession
     *
     * @return int
     */
    public function getTrailersInPossession()
    {
        return $this->trailersInPossession;
    }

    /**
     * Set the translate to welsh
     *
     * @param string $translateToWelsh new value being set
     *
     * @return Licence
     */
    public function setTranslateToWelsh($translateToWelsh)
    {
        $this->translateToWelsh = $translateToWelsh;

        return $this;
    }

    /**
     * Get the translate to welsh
     *
     * @return string
     */
    public function getTranslateToWelsh()
    {
        return $this->translateToWelsh;
    }

    /**
     * Set the transport consultant cd
     *
     * @param \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails $transportConsultantCd entity being set as the value
     *
     * @return Licence
     */
    public function setTransportConsultantCd($transportConsultantCd)
    {
        $this->transportConsultantCd = $transportConsultantCd;

        return $this;
    }

    /**
     * Get the transport consultant cd
     *
     * @return \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails
     */
    public function getTransportConsultantCd()
    {
        return $this->transportConsultantCd;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return Licence
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
     * Set the vi action
     *
     * @param string $viAction new value being set
     *
     * @return Licence
     */
    public function setViAction($viAction)
    {
        $this->viAction = $viAction;

        return $this;
    }

    /**
     * Get the vi action
     *
     * @return string
     */
    public function getViAction()
    {
        return $this->viAction;
    }

    /**
     * Set the application
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $applications collection being set as the value
     *
     * @return Licence
     */
    public function setApplications($applications)
    {
        $this->applications = $applications;

        return $this;
    }

    /**
     * Get the applications
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getApplications()
    {
        return $this->applications;
    }

    /**
     * Add a applications
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $applications collection being added
     *
     * @return Licence
     */
    public function addApplications($applications)
    {
        if ($applications instanceof ArrayCollection) {
            $this->applications = new ArrayCollection(
                array_merge(
                    $this->applications->toArray(),
                    $applications->toArray()
                )
            );
        } elseif (!$this->applications->contains($applications)) {
            $this->applications->add($applications);
        }

        return $this;
    }

    /**
     * Remove a applications
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $applications collection being removed
     *
     * @return Licence
     */
    public function removeApplications($applications)
    {
        if ($this->applications->contains($applications)) {
            $this->applications->removeElement($applications);
        }

        return $this;
    }

    /**
     * Set the bus reg
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $busRegs collection being set as the value
     *
     * @return Licence
     */
    public function setBusRegs($busRegs)
    {
        $this->busRegs = $busRegs;

        return $this;
    }

    /**
     * Get the bus regs
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getBusRegs()
    {
        return $this->busRegs;
    }

    /**
     * Add a bus regs
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $busRegs collection being added
     *
     * @return Licence
     */
    public function addBusRegs($busRegs)
    {
        if ($busRegs instanceof ArrayCollection) {
            $this->busRegs = new ArrayCollection(
                array_merge(
                    $this->busRegs->toArray(),
                    $busRegs->toArray()
                )
            );
        } elseif (!$this->busRegs->contains($busRegs)) {
            $this->busRegs->add($busRegs);
        }

        return $this;
    }

    /**
     * Remove a bus regs
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $busRegs collection being removed
     *
     * @return Licence
     */
    public function removeBusRegs($busRegs)
    {
        if ($this->busRegs->contains($busRegs)) {
            $this->busRegs->removeElement($busRegs);
        }

        return $this;
    }

    /**
     * Set the case
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $cases collection being set as the value
     *
     * @return Licence
     */
    public function setCases($cases)
    {
        $this->cases = $cases;

        return $this;
    }

    /**
     * Get the cases
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCases()
    {
        return $this->cases;
    }

    /**
     * Add a cases
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $cases collection being added
     *
     * @return Licence
     */
    public function addCases($cases)
    {
        if ($cases instanceof ArrayCollection) {
            $this->cases = new ArrayCollection(
                array_merge(
                    $this->cases->toArray(),
                    $cases->toArray()
                )
            );
        } elseif (!$this->cases->contains($cases)) {
            $this->cases->add($cases);
        }

        return $this;
    }

    /**
     * Remove a cases
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $cases collection being removed
     *
     * @return Licence
     */
    public function removeCases($cases)
    {
        if ($this->cases->contains($cases)) {
            $this->cases->removeElement($cases);
        }

        return $this;
    }

    /**
     * Set the change of entity
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $changeOfEntitys collection being set as the value
     *
     * @return Licence
     */
    public function setChangeOfEntitys($changeOfEntitys)
    {
        $this->changeOfEntitys = $changeOfEntitys;

        return $this;
    }

    /**
     * Get the change of entitys
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getChangeOfEntitys()
    {
        return $this->changeOfEntitys;
    }

    /**
     * Add a change of entitys
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $changeOfEntitys collection being added
     *
     * @return Licence
     */
    public function addChangeOfEntitys($changeOfEntitys)
    {
        if ($changeOfEntitys instanceof ArrayCollection) {
            $this->changeOfEntitys = new ArrayCollection(
                array_merge(
                    $this->changeOfEntitys->toArray(),
                    $changeOfEntitys->toArray()
                )
            );
        } elseif (!$this->changeOfEntitys->contains($changeOfEntitys)) {
            $this->changeOfEntitys->add($changeOfEntitys);
        }

        return $this;
    }

    /**
     * Remove a change of entitys
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $changeOfEntitys collection being removed
     *
     * @return Licence
     */
    public function removeChangeOfEntitys($changeOfEntitys)
    {
        if ($this->changeOfEntitys->contains($changeOfEntitys)) {
            $this->changeOfEntitys->removeElement($changeOfEntitys);
        }

        return $this;
    }

    /**
     * Set the community lic
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $communityLics collection being set as the value
     *
     * @return Licence
     */
    public function setCommunityLics($communityLics)
    {
        $this->communityLics = $communityLics;

        return $this;
    }

    /**
     * Get the community lics
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCommunityLics()
    {
        return $this->communityLics;
    }

    /**
     * Add a community lics
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $communityLics collection being added
     *
     * @return Licence
     */
    public function addCommunityLics($communityLics)
    {
        if ($communityLics instanceof ArrayCollection) {
            $this->communityLics = new ArrayCollection(
                array_merge(
                    $this->communityLics->toArray(),
                    $communityLics->toArray()
                )
            );
        } elseif (!$this->communityLics->contains($communityLics)) {
            $this->communityLics->add($communityLics);
        }

        return $this;
    }

    /**
     * Remove a community lics
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $communityLics collection being removed
     *
     * @return Licence
     */
    public function removeCommunityLics($communityLics)
    {
        if ($this->communityLics->contains($communityLics)) {
            $this->communityLics->removeElement($communityLics);
        }

        return $this;
    }

    /**
     * Set the company subsidiarie
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $companySubsidiaries collection being set as the value
     *
     * @return Licence
     */
    public function setCompanySubsidiaries($companySubsidiaries)
    {
        $this->companySubsidiaries = $companySubsidiaries;

        return $this;
    }

    /**
     * Get the company subsidiaries
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCompanySubsidiaries()
    {
        return $this->companySubsidiaries;
    }

    /**
     * Add a company subsidiaries
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $companySubsidiaries collection being added
     *
     * @return Licence
     */
    public function addCompanySubsidiaries($companySubsidiaries)
    {
        if ($companySubsidiaries instanceof ArrayCollection) {
            $this->companySubsidiaries = new ArrayCollection(
                array_merge(
                    $this->companySubsidiaries->toArray(),
                    $companySubsidiaries->toArray()
                )
            );
        } elseif (!$this->companySubsidiaries->contains($companySubsidiaries)) {
            $this->companySubsidiaries->add($companySubsidiaries);
        }

        return $this;
    }

    /**
     * Remove a company subsidiaries
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $companySubsidiaries collection being removed
     *
     * @return Licence
     */
    public function removeCompanySubsidiaries($companySubsidiaries)
    {
        if ($this->companySubsidiaries->contains($companySubsidiaries)) {
            $this->companySubsidiaries->removeElement($companySubsidiaries);
        }

        return $this;
    }

    /**
     * Set the condition undertaking
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $conditionUndertakings collection being set as the value
     *
     * @return Licence
     */
    public function setConditionUndertakings($conditionUndertakings)
    {
        $this->conditionUndertakings = $conditionUndertakings;

        return $this;
    }

    /**
     * Get the condition undertakings
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getConditionUndertakings()
    {
        return $this->conditionUndertakings;
    }

    /**
     * Add a condition undertakings
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $conditionUndertakings collection being added
     *
     * @return Licence
     */
    public function addConditionUndertakings($conditionUndertakings)
    {
        if ($conditionUndertakings instanceof ArrayCollection) {
            $this->conditionUndertakings = new ArrayCollection(
                array_merge(
                    $this->conditionUndertakings->toArray(),
                    $conditionUndertakings->toArray()
                )
            );
        } elseif (!$this->conditionUndertakings->contains($conditionUndertakings)) {
            $this->conditionUndertakings->add($conditionUndertakings);
        }

        return $this;
    }

    /**
     * Remove a condition undertakings
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $conditionUndertakings collection being removed
     *
     * @return Licence
     */
    public function removeConditionUndertakings($conditionUndertakings)
    {
        if ($this->conditionUndertakings->contains($conditionUndertakings)) {
            $this->conditionUndertakings->removeElement($conditionUndertakings);
        }

        return $this;
    }

    /**
     * Set the continuation detail
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $continuationDetails collection being set as the value
     *
     * @return Licence
     */
    public function setContinuationDetails($continuationDetails)
    {
        $this->continuationDetails = $continuationDetails;

        return $this;
    }

    /**
     * Get the continuation details
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getContinuationDetails()
    {
        return $this->continuationDetails;
    }

    /**
     * Add a continuation details
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $continuationDetails collection being added
     *
     * @return Licence
     */
    public function addContinuationDetails($continuationDetails)
    {
        if ($continuationDetails instanceof ArrayCollection) {
            $this->continuationDetails = new ArrayCollection(
                array_merge(
                    $this->continuationDetails->toArray(),
                    $continuationDetails->toArray()
                )
            );
        } elseif (!$this->continuationDetails->contains($continuationDetails)) {
            $this->continuationDetails->add($continuationDetails);
        }

        return $this;
    }

    /**
     * Remove a continuation details
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $continuationDetails collection being removed
     *
     * @return Licence
     */
    public function removeContinuationDetails($continuationDetails)
    {
        if ($this->continuationDetails->contains($continuationDetails)) {
            $this->continuationDetails->removeElement($continuationDetails);
        }

        return $this;
    }

    /**
     * Set the document
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $documents collection being set as the value
     *
     * @return Licence
     */
    public function setDocuments($documents)
    {
        $this->documents = $documents;

        return $this;
    }

    /**
     * Get the documents
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Add a documents
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $documents collection being added
     *
     * @return Licence
     */
    public function addDocuments($documents)
    {
        if ($documents instanceof ArrayCollection) {
            $this->documents = new ArrayCollection(
                array_merge(
                    $this->documents->toArray(),
                    $documents->toArray()
                )
            );
        } elseif (!$this->documents->contains($documents)) {
            $this->documents->add($documents);
        }

        return $this;
    }

    /**
     * Remove a documents
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $documents collection being removed
     *
     * @return Licence
     */
    public function removeDocuments($documents)
    {
        if ($this->documents->contains($documents)) {
            $this->documents->removeElement($documents);
        }

        return $this;
    }

    /**
     * Set the ecmt application
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $ecmtApplications collection being set as the value
     *
     * @return Licence
     */
    public function setEcmtApplications($ecmtApplications)
    {
        $this->ecmtApplications = $ecmtApplications;

        return $this;
    }

    /**
     * Get the ecmt applications
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getEcmtApplications()
    {
        return $this->ecmtApplications;
    }

    /**
     * Add a ecmt applications
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $ecmtApplications collection being added
     *
     * @return Licence
     */
    public function addEcmtApplications($ecmtApplications)
    {
        if ($ecmtApplications instanceof ArrayCollection) {
            $this->ecmtApplications = new ArrayCollection(
                array_merge(
                    $this->ecmtApplications->toArray(),
                    $ecmtApplications->toArray()
                )
            );
        } elseif (!$this->ecmtApplications->contains($ecmtApplications)) {
            $this->ecmtApplications->add($ecmtApplications);
        }

        return $this;
    }

    /**
     * Remove a ecmt applications
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $ecmtApplications collection being removed
     *
     * @return Licence
     */
    public function removeEcmtApplications($ecmtApplications)
    {
        if ($this->ecmtApplications->contains($ecmtApplications)) {
            $this->ecmtApplications->removeElement($ecmtApplications);
        }

        return $this;
    }

    /**
     * Set the fee
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $fees collection being set as the value
     *
     * @return Licence
     */
    public function setFees($fees)
    {
        $this->fees = $fees;

        return $this;
    }

    /**
     * Get the fees
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getFees()
    {
        return $this->fees;
    }

    /**
     * Add a fees
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $fees collection being added
     *
     * @return Licence
     */
    public function addFees($fees)
    {
        if ($fees instanceof ArrayCollection) {
            $this->fees = new ArrayCollection(
                array_merge(
                    $this->fees->toArray(),
                    $fees->toArray()
                )
            );
        } elseif (!$this->fees->contains($fees)) {
            $this->fees->add($fees);
        }

        return $this;
    }

    /**
     * Remove a fees
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $fees collection being removed
     *
     * @return Licence
     */
    public function removeFees($fees)
    {
        if ($this->fees->contains($fees)) {
            $this->fees->removeElement($fees);
        }

        return $this;
    }

    /**
     * Set the grace period
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $gracePeriods collection being set as the value
     *
     * @return Licence
     */
    public function setGracePeriods($gracePeriods)
    {
        $this->gracePeriods = $gracePeriods;

        return $this;
    }

    /**
     * Get the grace periods
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getGracePeriods()
    {
        return $this->gracePeriods;
    }

    /**
     * Add a grace periods
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $gracePeriods collection being added
     *
     * @return Licence
     */
    public function addGracePeriods($gracePeriods)
    {
        if ($gracePeriods instanceof ArrayCollection) {
            $this->gracePeriods = new ArrayCollection(
                array_merge(
                    $this->gracePeriods->toArray(),
                    $gracePeriods->toArray()
                )
            );
        } elseif (!$this->gracePeriods->contains($gracePeriods)) {
            $this->gracePeriods->add($gracePeriods);
        }

        return $this;
    }

    /**
     * Remove a grace periods
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $gracePeriods collection being removed
     *
     * @return Licence
     */
    public function removeGracePeriods($gracePeriods)
    {
        if ($this->gracePeriods->contains($gracePeriods)) {
            $this->gracePeriods->removeElement($gracePeriods);
        }

        return $this;
    }

    /**
     * Set the irhp application
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irhpApplications collection being set as the value
     *
     * @return Licence
     */
    public function setIrhpApplications($irhpApplications)
    {
        $this->irhpApplications = $irhpApplications;

        return $this;
    }

    /**
     * Get the irhp applications
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getIrhpApplications()
    {
        return $this->irhpApplications;
    }

    /**
     * Add a irhp applications
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irhpApplications collection being added
     *
     * @return Licence
     */
    public function addIrhpApplications($irhpApplications)
    {
        if ($irhpApplications instanceof ArrayCollection) {
            $this->irhpApplications = new ArrayCollection(
                array_merge(
                    $this->irhpApplications->toArray(),
                    $irhpApplications->toArray()
                )
            );
        } elseif (!$this->irhpApplications->contains($irhpApplications)) {
            $this->irhpApplications->add($irhpApplications);
        }

        return $this;
    }

    /**
     * Remove a irhp applications
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irhpApplications collection being removed
     *
     * @return Licence
     */
    public function removeIrhpApplications($irhpApplications)
    {
        if ($this->irhpApplications->contains($irhpApplications)) {
            $this->irhpApplications->removeElement($irhpApplications);
        }

        return $this;
    }

    /**
     * Set the operating centre
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $operatingCentres collection being set as the value
     *
     * @return Licence
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
     * @return Licence
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
     * @return Licence
     */
    public function removeOperatingCentres($operatingCentres)
    {
        if ($this->operatingCentres->contains($operatingCentres)) {
            $this->operatingCentres->removeElement($operatingCentres);
        }

        return $this;
    }

    /**
     * Set the read audit
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $readAudits collection being set as the value
     *
     * @return Licence
     */
    public function setReadAudits($readAudits)
    {
        $this->readAudits = $readAudits;

        return $this;
    }

    /**
     * Get the read audits
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getReadAudits()
    {
        return $this->readAudits;
    }

    /**
     * Add a read audits
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $readAudits collection being added
     *
     * @return Licence
     */
    public function addReadAudits($readAudits)
    {
        if ($readAudits instanceof ArrayCollection) {
            $this->readAudits = new ArrayCollection(
                array_merge(
                    $this->readAudits->toArray(),
                    $readAudits->toArray()
                )
            );
        } elseif (!$this->readAudits->contains($readAudits)) {
            $this->readAudits->add($readAudits);
        }

        return $this;
    }

    /**
     * Remove a read audits
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $readAudits collection being removed
     *
     * @return Licence
     */
    public function removeReadAudits($readAudits)
    {
        if ($this->readAudits->contains($readAudits)) {
            $this->readAudits->removeElement($readAudits);
        }

        return $this;
    }

    /**
     * Set the licence status rule
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $licenceStatusRules collection being set as the value
     *
     * @return Licence
     */
    public function setLicenceStatusRules($licenceStatusRules)
    {
        $this->licenceStatusRules = $licenceStatusRules;

        return $this;
    }

    /**
     * Get the licence status rules
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getLicenceStatusRules()
    {
        return $this->licenceStatusRules;
    }

    /**
     * Add a licence status rules
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $licenceStatusRules collection being added
     *
     * @return Licence
     */
    public function addLicenceStatusRules($licenceStatusRules)
    {
        if ($licenceStatusRules instanceof ArrayCollection) {
            $this->licenceStatusRules = new ArrayCollection(
                array_merge(
                    $this->licenceStatusRules->toArray(),
                    $licenceStatusRules->toArray()
                )
            );
        } elseif (!$this->licenceStatusRules->contains($licenceStatusRules)) {
            $this->licenceStatusRules->add($licenceStatusRules);
        }

        return $this;
    }

    /**
     * Remove a licence status rules
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $licenceStatusRules collection being removed
     *
     * @return Licence
     */
    public function removeLicenceStatusRules($licenceStatusRules)
    {
        if ($this->licenceStatusRules->contains($licenceStatusRules)) {
            $this->licenceStatusRules->removeElement($licenceStatusRules);
        }

        return $this;
    }

    /**
     * Set the licence vehicle
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $licenceVehicles collection being set as the value
     *
     * @return Licence
     */
    public function setLicenceVehicles($licenceVehicles)
    {
        $this->licenceVehicles = $licenceVehicles;

        return $this;
    }

    /**
     * Get the licence vehicles
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getLicenceVehicles()
    {
        return $this->licenceVehicles;
    }

    /**
     * Add a licence vehicles
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $licenceVehicles collection being added
     *
     * @return Licence
     */
    public function addLicenceVehicles($licenceVehicles)
    {
        if ($licenceVehicles instanceof ArrayCollection) {
            $this->licenceVehicles = new ArrayCollection(
                array_merge(
                    $this->licenceVehicles->toArray(),
                    $licenceVehicles->toArray()
                )
            );
        } elseif (!$this->licenceVehicles->contains($licenceVehicles)) {
            $this->licenceVehicles->add($licenceVehicles);
        }

        return $this;
    }

    /**
     * Remove a licence vehicles
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $licenceVehicles collection being removed
     *
     * @return Licence
     */
    public function removeLicenceVehicles($licenceVehicles)
    {
        if ($this->licenceVehicles->contains($licenceVehicles)) {
            $this->licenceVehicles->removeElement($licenceVehicles);
        }

        return $this;
    }

    /**
     * Set the private hire licence
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $privateHireLicences collection being set as the value
     *
     * @return Licence
     */
    public function setPrivateHireLicences($privateHireLicences)
    {
        $this->privateHireLicences = $privateHireLicences;

        return $this;
    }

    /**
     * Get the private hire licences
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getPrivateHireLicences()
    {
        return $this->privateHireLicences;
    }

    /**
     * Add a private hire licences
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $privateHireLicences collection being added
     *
     * @return Licence
     */
    public function addPrivateHireLicences($privateHireLicences)
    {
        if ($privateHireLicences instanceof ArrayCollection) {
            $this->privateHireLicences = new ArrayCollection(
                array_merge(
                    $this->privateHireLicences->toArray(),
                    $privateHireLicences->toArray()
                )
            );
        } elseif (!$this->privateHireLicences->contains($privateHireLicences)) {
            $this->privateHireLicences->add($privateHireLicences);
        }

        return $this;
    }

    /**
     * Remove a private hire licences
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $privateHireLicences collection being removed
     *
     * @return Licence
     */
    public function removePrivateHireLicences($privateHireLicences)
    {
        if ($this->privateHireLicences->contains($privateHireLicences)) {
            $this->privateHireLicences->removeElement($privateHireLicences);
        }

        return $this;
    }

    /**
     * Set the psv disc
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $psvDiscs collection being set as the value
     *
     * @return Licence
     */
    public function setPsvDiscs($psvDiscs)
    {
        $this->psvDiscs = $psvDiscs;

        return $this;
    }

    /**
     * Get the psv discs
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getPsvDiscs()
    {
        return $this->psvDiscs;
    }

    /**
     * Add a psv discs
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $psvDiscs collection being added
     *
     * @return Licence
     */
    public function addPsvDiscs($psvDiscs)
    {
        if ($psvDiscs instanceof ArrayCollection) {
            $this->psvDiscs = new ArrayCollection(
                array_merge(
                    $this->psvDiscs->toArray(),
                    $psvDiscs->toArray()
                )
            );
        } elseif (!$this->psvDiscs->contains($psvDiscs)) {
            $this->psvDiscs->add($psvDiscs);
        }

        return $this;
    }

    /**
     * Remove a psv discs
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $psvDiscs collection being removed
     *
     * @return Licence
     */
    public function removePsvDiscs($psvDiscs)
    {
        if ($this->psvDiscs->contains($psvDiscs)) {
            $this->psvDiscs->removeElement($psvDiscs);
        }

        return $this;
    }

    /**
     * Set the publication link
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $publicationLinks collection being set as the value
     *
     * @return Licence
     */
    public function setPublicationLinks($publicationLinks)
    {
        $this->publicationLinks = $publicationLinks;

        return $this;
    }

    /**
     * Get the publication links
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getPublicationLinks()
    {
        return $this->publicationLinks;
    }

    /**
     * Add a publication links
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $publicationLinks collection being added
     *
     * @return Licence
     */
    public function addPublicationLinks($publicationLinks)
    {
        if ($publicationLinks instanceof ArrayCollection) {
            $this->publicationLinks = new ArrayCollection(
                array_merge(
                    $this->publicationLinks->toArray(),
                    $publicationLinks->toArray()
                )
            );
        } elseif (!$this->publicationLinks->contains($publicationLinks)) {
            $this->publicationLinks->add($publicationLinks);
        }

        return $this;
    }

    /**
     * Remove a publication links
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $publicationLinks collection being removed
     *
     * @return Licence
     */
    public function removePublicationLinks($publicationLinks)
    {
        if ($this->publicationLinks->contains($publicationLinks)) {
            $this->publicationLinks->removeElement($publicationLinks);
        }

        return $this;
    }

    /**
     * Set the trading name
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $tradingNames collection being set as the value
     *
     * @return Licence
     */
    public function setTradingNames($tradingNames)
    {
        $this->tradingNames = $tradingNames;

        return $this;
    }

    /**
     * Get the trading names
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTradingNames()
    {
        return $this->tradingNames;
    }

    /**
     * Add a trading names
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $tradingNames collection being added
     *
     * @return Licence
     */
    public function addTradingNames($tradingNames)
    {
        if ($tradingNames instanceof ArrayCollection) {
            $this->tradingNames = new ArrayCollection(
                array_merge(
                    $this->tradingNames->toArray(),
                    $tradingNames->toArray()
                )
            );
        } elseif (!$this->tradingNames->contains($tradingNames)) {
            $this->tradingNames->add($tradingNames);
        }

        return $this;
    }

    /**
     * Remove a trading names
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $tradingNames collection being removed
     *
     * @return Licence
     */
    public function removeTradingNames($tradingNames)
    {
        if ($this->tradingNames->contains($tradingNames)) {
            $this->tradingNames->removeElement($tradingNames);
        }

        return $this;
    }

    /**
     * Set the tm licence
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $tmLicences collection being set as the value
     *
     * @return Licence
     */
    public function setTmLicences($tmLicences)
    {
        $this->tmLicences = $tmLicences;

        return $this;
    }

    /**
     * Get the tm licences
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTmLicences()
    {
        return $this->tmLicences;
    }

    /**
     * Add a tm licences
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $tmLicences collection being added
     *
     * @return Licence
     */
    public function addTmLicences($tmLicences)
    {
        if ($tmLicences instanceof ArrayCollection) {
            $this->tmLicences = new ArrayCollection(
                array_merge(
                    $this->tmLicences->toArray(),
                    $tmLicences->toArray()
                )
            );
        } elseif (!$this->tmLicences->contains($tmLicences)) {
            $this->tmLicences->add($tmLicences);
        }

        return $this;
    }

    /**
     * Remove a tm licences
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $tmLicences collection being removed
     *
     * @return Licence
     */
    public function removeTmLicences($tmLicences)
    {
        if ($this->tmLicences->contains($tmLicences)) {
            $this->tmLicences->removeElement($tmLicences);
        }

        return $this;
    }

    /**
     * Set the workshop
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $workshops collection being set as the value
     *
     * @return Licence
     */
    public function setWorkshops($workshops)
    {
        $this->workshops = $workshops;

        return $this;
    }

    /**
     * Get the workshops
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getWorkshops()
    {
        return $this->workshops;
    }

    /**
     * Add a workshops
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $workshops collection being added
     *
     * @return Licence
     */
    public function addWorkshops($workshops)
    {
        if ($workshops instanceof ArrayCollection) {
            $this->workshops = new ArrayCollection(
                array_merge(
                    $this->workshops->toArray(),
                    $workshops->toArray()
                )
            );
        } elseif (!$this->workshops->contains($workshops)) {
            $this->workshops->add($workshops);
        }

        return $this;
    }

    /**
     * Remove a workshops
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $workshops collection being removed
     *
     * @return Licence
     */
    public function removeWorkshops($workshops)
    {
        if ($this->workshops->contains($workshops)) {
            $this->workshops->removeElement($workshops);
        }

        return $this;
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
