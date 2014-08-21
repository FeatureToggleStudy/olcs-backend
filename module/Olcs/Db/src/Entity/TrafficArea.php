<?php

namespace Olcs\Db\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Olcs\Db\Entity\Traits;

/**
 * TrafficArea Entity
 *
 * Auto-Generated
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="traffic_area",
 *    indexes={
 *        @ORM\Index(name="fk_traffic_area_user1_idx", columns={"created_by"}),
 *        @ORM\Index(name="fk_traffic_area_user2_idx", columns={"last_modified_by"}),
 *        @ORM\Index(name="fk_traffic_area_contact_details1_idx", columns={"contact_details_id"})
 *    }
 * )
 */
class TrafficArea implements Interfaces\EntityInterface
{
    use Traits\CustomBaseEntity,
        Traits\ContactDetailsManyToOne,
        Traits\LastModifiedByManyToOne,
        Traits\CreatedByManyToOne,
        Traits\Name70Field,
        Traits\CustomCreatedOnField,
        Traits\CustomLastModifiedOnField,
        Traits\CustomVersionField;

    /**
     * Identifier - Id
     *
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string", name="id", length=1)
     */
    protected $id;

    /**
     * Recipient
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Olcs\Db\Entity\Recipient", mappedBy="trafficAreas", fetch="LAZY")
     */
    protected $recipients;

    /**
     * Txc name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="txc_name", length=70, nullable=true)
     */
    protected $txcName;

    /**
     * Scottish rules
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="scottish_rules", nullable=false)
     */
    protected $scottishRules = 0;

    /**
     * Document
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Olcs\Db\Entity\Document", mappedBy="trafficArea")
     */
    protected $documents;

    /**
     * Initialise the collections
     */
    public function __construct()
    {
        $this->recipients = new ArrayCollection();
        $this->documents = new ArrayCollection();
    }

    /**
     * Set the id
     *
     * @param string $id
     * @return TrafficArea
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the recipient
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $recipients
     * @return TrafficArea
     */
    public function setRecipients($recipients)
    {
        $this->recipients = $recipients;

        return $this;
    }

    /**
     * Get the recipients
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * Set the txc name
     *
     * @param string $txcName
     * @return TrafficArea
     */
    public function setTxcName($txcName)
    {
        $this->txcName = $txcName;

        return $this;
    }

    /**
     * Get the txc name
     *
     * @return string
     */
    public function getTxcName()
    {
        return $this->txcName;
    }

    /**
     * Set the scottish rules
     *
     * @param boolean $scottishRules
     * @return TrafficArea
     */
    public function setScottishRules($scottishRules)
    {
        $this->scottishRules = $scottishRules;

        return $this;
    }

    /**
     * Get the scottish rules
     *
     * @return boolean
     */
    public function getScottishRules()
    {
        return $this->scottishRules;
    }

    /**
     * Set the document
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $documents
     * @return TrafficArea
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
}
