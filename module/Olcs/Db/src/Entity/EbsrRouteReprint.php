<?php

namespace Olcs\Db\Entity;

use Doctrine\ORM\Mapping as ORM;
use Olcs\Db\Entity\Traits;

/**
 * EbsrRouteReprint Entity
 *
 * Auto-Generated
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="ebsr_route_reprint",
 *    indexes={
 *        @ORM\Index(name="ix_ebsr_route_reprint_bus_reg_id", columns={"bus_reg_id"}),
 *        @ORM\Index(name="ix_ebsr_route_reprint_requested_user_id", columns={"requested_user_id"}),
 *        @ORM\Index(name="ix_ebsr_route_reprint_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class EbsrRouteReprint implements Interfaces\EntityInterface
{
    use Traits\CustomBaseEntity,
        Traits\BusRegManyToOne,
        Traits\IdIdentity,
        Traits\OlbsKeyField;

    /**
     * Exception name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="exception_name", length=45, nullable=true)
     */
    protected $exceptionName;

    /**
     * Published timestamp
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="published_timestamp", nullable=true)
     */
    protected $publishedTimestamp;

    /**
     * Requested timestamp
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="requested_timestamp", nullable=false)
     */
    protected $requestedTimestamp;

    /**
     * Requested user
     *
     * @var \Olcs\Db\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Olcs\Db\Entity\User")
     * @ORM\JoinColumn(name="requested_user_id", referencedColumnName="id", nullable=false)
     */
    protected $requestedUser;

    /**
     * Scale
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="scale", nullable=false, options={"default": 0})
     */
    protected $scale = 0;

    /**
     * Set the exception name
     *
     * @param string $exceptionName
     * @return EbsrRouteReprint
     */
    public function setExceptionName($exceptionName)
    {
        $this->exceptionName = $exceptionName;

        return $this;
    }

    /**
     * Get the exception name
     *
     * @return string
     */
    public function getExceptionName()
    {
        return $this->exceptionName;
    }

    /**
     * Set the published timestamp
     *
     * @param \DateTime $publishedTimestamp
     * @return EbsrRouteReprint
     */
    public function setPublishedTimestamp($publishedTimestamp)
    {
        $this->publishedTimestamp = $publishedTimestamp;

        return $this;
    }

    /**
     * Get the published timestamp
     *
     * @return \DateTime
     */
    public function getPublishedTimestamp()
    {
        return $this->publishedTimestamp;
    }

    /**
     * Set the requested timestamp
     *
     * @param \DateTime $requestedTimestamp
     * @return EbsrRouteReprint
     */
    public function setRequestedTimestamp($requestedTimestamp)
    {
        $this->requestedTimestamp = $requestedTimestamp;

        return $this;
    }

    /**
     * Get the requested timestamp
     *
     * @return \DateTime
     */
    public function getRequestedTimestamp()
    {
        return $this->requestedTimestamp;
    }

    /**
     * Set the requested user
     *
     * @param \Olcs\Db\Entity\User $requestedUser
     * @return EbsrRouteReprint
     */
    public function setRequestedUser($requestedUser)
    {
        $this->requestedUser = $requestedUser;

        return $this;
    }

    /**
     * Get the requested user
     *
     * @return \Olcs\Db\Entity\User
     */
    public function getRequestedUser()
    {
        return $this->requestedUser;
    }

    /**
     * Set the scale
     *
     * @param boolean $scale
     * @return EbsrRouteReprint
     */
    public function setScale($scale)
    {
        $this->scale = $scale;

        return $this;
    }

    /**
     * Get the scale
     *
     * @return boolean
     */
    public function getScale()
    {
        return $this->scale;
    }
}
