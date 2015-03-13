<?php

namespace Olcs\Db\Entity;

use Doctrine\ORM\Mapping as ORM;
use Olcs\Db\Entity\Traits;

/**
 * UserRole Entity
 *
 * Auto-Generated
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="user_role",
 *    indexes={
 *        @ORM\Index(name="ix_user_role_role_id", columns={"role_id"}),
 *        @ORM\Index(name="ix_user_role_user_id", columns={"user_id"}),
 *        @ORM\Index(name="ix_user_role_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_user_role_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class UserRole implements Interfaces\EntityInterface
{
    use Traits\CustomBaseEntity,
        Traits\CreatedByManyToOne,
        Traits\CustomCreatedOnField,
        Traits\IdIdentity,
        Traits\LastModifiedByManyToOne,
        Traits\CustomLastModifiedOnField,
        Traits\UserManyToOneAlt1,
        Traits\CustomVersionField;

    /**
     * Expiry date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="expiry_date", nullable=true)
     */
    protected $expiryDate;

    /**
     * Role
     *
     * @var \Olcs\Db\Entity\Role
     *
     * @ORM\ManyToOne(targetEntity="Olcs\Db\Entity\Role")
     * @ORM\JoinColumn(name="role_id", referencedColumnName="id", nullable=false)
     */
    protected $role;

    /**
     * Valid from
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="valid_from", nullable=true)
     */
    protected $validFrom;

    /**
     * Set the expiry date
     *
     * @param \DateTime $expiryDate
     * @return UserRole
     */
    public function setExpiryDate($expiryDate)
    {
        $this->expiryDate = $expiryDate;

        return $this;
    }

    /**
     * Get the expiry date
     *
     * @return \DateTime
     */
    public function getExpiryDate()
    {
        return $this->expiryDate;
    }

    /**
     * Set the role
     *
     * @param \Olcs\Db\Entity\Role $role
     * @return UserRole
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get the role
     *
     * @return \Olcs\Db\Entity\Role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set the valid from
     *
     * @param \DateTime $validFrom
     * @return UserRole
     */
    public function setValidFrom($validFrom)
    {
        $this->validFrom = $validFrom;

        return $this;
    }

    /**
     * Get the valid from
     *
     * @return \DateTime
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }
}
