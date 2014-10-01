<?php

namespace Olcs\Db\Entity;

use Doctrine\ORM\Mapping as ORM;
use Olcs\Db\Entity\Traits;

/**
 * PublicHoliday Entity
 *
 * Auto-Generated
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="public_holiday",
 *    indexes={
 *        @ORM\Index(name="IDX_8744ED8665CF370E", columns={"last_modified_by"}),
 *        @ORM\Index(name="IDX_8744ED86DE12AB56", columns={"created_by"})
 *    }
 * )
 */
class PublicHoliday implements Interfaces\EntityInterface
{
    use Traits\CustomBaseEntity,
        Traits\IdIdentity,
        Traits\LastModifiedByManyToOne,
        Traits\CreatedByManyToOne,
        Traits\CustomCreatedOnField,
        Traits\CustomLastModifiedOnField,
        Traits\CustomVersionField;

    /**
     * Public holiday date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="public_holiday_date", nullable=false)
     */
    protected $publicHolidayDate;

    /**
     * Is england
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="is_england", nullable=true)
     */
    protected $isEngland;

    /**
     * Is wales
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="is_wales", nullable=true)
     */
    protected $isWales;

    /**
     * Is scotland
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="is_scotland", nullable=true)
     */
    protected $isScotland;

    /**
     * Is ni
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="is_ni", nullable=true)
     */
    protected $isNi;

    /**
     * Set the public holiday date
     *
     * @param \DateTime $publicHolidayDate
     * @return PublicHoliday
     */
    public function setPublicHolidayDate($publicHolidayDate)
    {
        $this->publicHolidayDate = $publicHolidayDate;

        return $this;
    }

    /**
     * Get the public holiday date
     *
     * @return \DateTime
     */
    public function getPublicHolidayDate()
    {
        return $this->publicHolidayDate;
    }

    /**
     * Set the is england
     *
     * @param string $isEngland
     * @return PublicHoliday
     */
    public function setIsEngland($isEngland)
    {
        $this->isEngland = $isEngland;

        return $this;
    }

    /**
     * Get the is england
     *
     * @return string
     */
    public function getIsEngland()
    {
        return $this->isEngland;
    }

    /**
     * Set the is wales
     *
     * @param string $isWales
     * @return PublicHoliday
     */
    public function setIsWales($isWales)
    {
        $this->isWales = $isWales;

        return $this;
    }

    /**
     * Get the is wales
     *
     * @return string
     */
    public function getIsWales()
    {
        return $this->isWales;
    }

    /**
     * Set the is scotland
     *
     * @param string $isScotland
     * @return PublicHoliday
     */
    public function setIsScotland($isScotland)
    {
        $this->isScotland = $isScotland;

        return $this;
    }

    /**
     * Get the is scotland
     *
     * @return string
     */
    public function getIsScotland()
    {
        return $this->isScotland;
    }

    /**
     * Set the is ni
     *
     * @param string $isNi
     * @return PublicHoliday
     */
    public function setIsNi($isNi)
    {
        $this->isNi = $isNi;

        return $this;
    }

    /**
     * Get the is ni
     *
     * @return string
     */
    public function getIsNi()
    {
        return $this->isNi;
    }
}
