<?php

namespace Olcs\Db\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * Document many to one alt1 trait
 *
 * Auto-Generated (Shared between 2 entities)
 */
trait DocumentManyToOneAlt1
{
    /**
     * Document
     *
     * @var \Olcs\Db\Entity\Document
     *
     * @ORM\ManyToOne(targetEntity="Olcs\Db\Entity\Document")
     * @ORM\JoinColumn(name="document_id", referencedColumnName="id", nullable=true)
     */
    protected $document;

    /**
     * Set the document
     *
     * @param \Olcs\Db\Entity\Document $document
     * @return \Olcs\Db\Entity\Interfaces\EntityInterface
     */
    public function setDocument($document)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get the document
     *
     * @return \Olcs\Db\Entity\Document
     */
    public function getDocument()
    {
        return $this->document;
    }
}
