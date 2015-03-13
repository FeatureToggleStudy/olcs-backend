<?php

namespace Olcs\Db\Entity;

use Doctrine\ORM\Mapping as ORM;
use Olcs\Db\Entity\Traits;

/**
 * TaskNote Entity
 *
 * Auto-Generated
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="task_note",
 *    indexes={
 *        @ORM\Index(name="ix_task_note_task_id", columns={"task_id"}),
 *        @ORM\Index(name="ix_task_note_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_task_note_created_by", columns={"created_by"})
 *    }
 * )
 */
class TaskNote implements Interfaces\EntityInterface
{
    use Traits\CustomBaseEntity,
        Traits\CreatedByManyToOne,
        Traits\CustomCreatedOnField,
        Traits\IdIdentity,
        Traits\LastModifiedByManyToOne,
        Traits\CustomLastModifiedOnField,
        Traits\CustomVersionField;

    /**
     * Note text
     *
     * @var string
     *
     * @ORM\Column(type="string", name="note_text", length=1800, nullable=true)
     */
    protected $noteText;

    /**
     * Task
     *
     * @var \Olcs\Db\Entity\Task
     *
     * @ORM\ManyToOne(targetEntity="Olcs\Db\Entity\Task")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id", nullable=false)
     */
    protected $task;

    /**
     * Set the note text
     *
     * @param string $noteText
     * @return TaskNote
     */
    public function setNoteText($noteText)
    {
        $this->noteText = $noteText;

        return $this;
    }

    /**
     * Get the note text
     *
     * @return string
     */
    public function getNoteText()
    {
        return $this->noteText;
    }

    /**
     * Set the task
     *
     * @param \Olcs\Db\Entity\Task $task
     * @return TaskNote
     */
    public function setTask($task)
    {
        $this->task = $task;

        return $this;
    }

    /**
     * Get the task
     *
     * @return \Olcs\Db\Entity\Task
     */
    public function getTask()
    {
        return $this->task;
    }
}
