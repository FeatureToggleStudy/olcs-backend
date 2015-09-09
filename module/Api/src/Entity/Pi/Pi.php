<?php

namespace Dvsa\Olcs\Api\Entity\Pi;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Dvsa\Olcs\Api\Entity\Pi\PresidingTc as PresidingTcEntity;
use Dvsa\Olcs\Api\Entity\Pi\PiHearing as PiHearingEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\System\Sla as SlaEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\CloseableInterface;
use Dvsa\Olcs\Api\Entity\ReopenableInterface;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;

/**
 * Pi Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="pi",
 *    indexes={
 *        @ORM\Index(name="ix_pi_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_pi_pi_status", columns={"pi_status"}),
 *        @ORM\Index(name="ix_pi_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_pi_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_pi_assigned_to", columns={"assigned_to"}),
 *        @ORM\Index(name="ix_pi_agreed_by_tc_id", columns={"agreed_by_tc_id"}),
 *        @ORM\Index(name="ix_pi_decided_by_tc_id", columns={"decided_by_tc_id"}),
 *        @ORM\Index(name="ix_pi_agreed_by_tc_role", columns={"agreed_by_tc_role"}),
 *        @ORM\Index(name="ix_pi_decided_by_tc_role", columns={"decided_by_tc_role"}),
 *        @ORM\Index(name="ix_pi_written_outcome", columns={"written_outcome"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_pi_olbs_key_olbs_type", columns={"olbs_key","olbs_type"})
 *    }
 * )
 */
class Pi extends AbstractPi implements CloseableInterface, ReopenableInterface
{
    const STATUS_REGISTERED = 'pi_s_reg';
    const MSG_UPDATE_CLOSED = 'Can\'t update a closed Pi';

    /**
     * @param CasesEntity $case
     * @param PresidingTc $agreedByTc
     * @param RefData $agreedByTcRole
     * @param ArrayCollection $piTypes
     * @param ArrayCollection $reasons
     * @param \DateTime $agreedDate
     * @param RefData $piStatus
     * @param String $comment
     */
    public function __construct(
        CasesEntity $case,
        PresidingTcEntity $agreedByTc,
        RefData $agreedByTcRole,
        ArrayCollection $piTypes,
        ArrayCollection $reasons,
        \DateTime $agreedDate,
        RefData $piStatus,
        $comment
    ) {
        parent::__construct();

        $this->create($case, $agreedByTc, $agreedByTcRole, $piTypes, $reasons, $agreedDate, $piStatus, $comment);
    }

    /**
     * @param CasesEntity $case
     * @param PresidingTc $agreedByTc
     * @param RefData $agreedByTcRole
     * @param ArrayCollection $piTypes
     * @param ArrayCollection $reasons
     * @param \DateTime $agreedDate
     * @param RefData $piStatus
     * @param String $comment
     * @throws ForbiddenException
     */
    private function create(
        CasesEntity $case,
        PresidingTcEntity $agreedByTc,
        RefData $agreedByTcRole,
        ArrayCollection $piTypes,
        ArrayCollection $reasons,
        \DateTime $agreedDate,
        RefData $piStatus,
        $comment
    ) {
        if ($case->isClosed()) {
            throw new ForbiddenException('Can\'t create a Pi for a closed case');
        }

        $this->case = $case;
        $this->agreedByTc = $agreedByTc;
        $this->agreedByTcRole = $agreedByTcRole;
        $this->piTypes = $piTypes;
        $this->reasons = $reasons;
        $this->agreedDate = $agreedDate;
        $this->piStatus = $piStatus;
        $this->comment = $comment;
    }

    /**
     * @param PresidingTc $agreedByTc
     * @param RefData $agreedByTcRole
     * @param ArrayCollection $piTypes
     * @param ArrayCollection $reasons
     * @param \DateTime $agreedDate
     * @param String $comment
     * @throws ForbiddenException
     */
    public function updateAgreedAndLegislation(
        PresidingTcEntity $agreedByTc,
        RefData $agreedByTcRole,
        ArrayCollection $piTypes,
        ArrayCollection $reasons,
        \DateTime $agreedDate,
        $comment
    ) {
        if ($this->isClosed()) {
            throw new ForbiddenException(self::MSG_UPDATE_CLOSED);
        }

        $this->agreedByTc = $agreedByTc;
        $this->agreedByTcRole = $agreedByTcRole;
        $this->piTypes = $piTypes;
        $this->reasons = $reasons;
        $this->agreedDate = $agreedDate;
        $this->comment = $comment;
    }

    /**
     * @param PresidingTcEntity $decidedByTc
     * @param RefData $decidedByTcRole
     * @param ArrayCollection $decisions
     * @param $licenceRevokedAtPi
     * @param $licenceSuspendedAtPi
     * @param $licenceCurtailedAtPi
     * @param $witnesses
     * @param $decisionDate
     * @param $notificationDate
     * @param $decisionNotes
     * @throws ForbiddenException
     */
    public function updatePiWithDecision(
        $decidedByTc,
        RefData $decidedByTcRole,
        ArrayCollection $decisions,
        $licenceRevokedAtPi,
        $licenceSuspendedAtPi,
        $licenceCurtailedAtPi,
        $witnesses,
        $decisionDate,
        $notificationDate,
        $decisionNotes
    ) {
        if ($this->isClosed()) {
            throw new ForbiddenException(self::MSG_UPDATE_CLOSED);
        }

        $this->setDecidedByTc($decidedByTc);
        $this->decidedByTcRole = $decidedByTcRole;
        $this->decisions = $decisions;
        $this->licenceRevokedAtPi = $licenceRevokedAtPi;
        $this->licenceSuspendedAtPi = $licenceSuspendedAtPi;
        $this->licenceCurtailedAtPi = $licenceCurtailedAtPi;
        $this->witnesses = $witnesses;
        $this->decisionNotes = $decisionNotes;
        $this->decisionDate = $this->processDate($decisionDate);
        $this->notificationDate = $this->processDate($notificationDate);
    }

    /**
     * @param RefData|null $writtenOutcome
     * @param string $callUpLetterDate
     * @param string $briefToTcDate
     * @throws ForbiddenException
     */
    public function updateWrittenOutcomeNone($writtenOutcome, $callUpLetterDate, $briefToTcDate)
    {
        if ($this->isClosed()) {
            throw new ForbiddenException(self::MSG_UPDATE_CLOSED);
        }

        $this->updateSla(
            $writtenOutcome,
            $this->processDate($callUpLetterDate),
            $this->processDate($briefToTcDate),
            null,
            null,
            null,
            null
        );
    }

    /**
     * @param RefData $writtenOutcome
     * @param string $callUpLetterDate
     * @param string $briefToTcDate
     * @param string $tcWrittenDecisionDate
     * @param string $decisionLetterSentDate
     * @throws ForbiddenException
     */
    public function updateWrittenOutcomeDecision(
        RefData $writtenOutcome,
        $callUpLetterDate,
        $briefToTcDate,
        $tcWrittenDecisionDate,
        $decisionLetterSentDate
    ) {
        if ($this->isClosed()) {
            throw new ForbiddenException(self::MSG_UPDATE_CLOSED);
        }

        $this->updateSla(
            $writtenOutcome,
            $this->processDate($callUpLetterDate),
            $this->processDate($briefToTcDate),
            null,
            null,
            $this->processDate($tcWrittenDecisionDate),
            $this->processDate($decisionLetterSentDate)
        );
    }

    /**
     * @param RefData $writtenOutcome
     * @param string $callUpLetterDate
     * @param string $briefToTcDate
     * @param string $tcWrittenReasonDate
     * @param string $writtenReasonLetterDate
     * @throws ForbiddenException
     */
    public function updateWrittenOutcomeReason(
        RefData $writtenOutcome,
        $callUpLetterDate,
        $briefToTcDate,
        $tcWrittenReasonDate,
        $writtenReasonLetterDate
    ) {
        if ($this->isClosed()) {
            throw new ForbiddenException(self::MSG_UPDATE_CLOSED);
        }

        $this->updateSla(
            $writtenOutcome,
            $this->processDate($callUpLetterDate),
            $this->processDate($briefToTcDate),
            $this->processDate($tcWrittenReasonDate),
            $this->processDate($writtenReasonLetterDate),
            null,
            null
        );
    }

    /**
     * @param RefData|null $writtenOutcome
     * @param \DateTime|null $callUpLetterDate
     * @param \DateTime|null $briefToTcDate
     * @param \DateTime|null $tcWrittenReasonDate
     * @param \DateTime|null $writtenReasonLetterDate
     * @param \DateTime|null $tcWrittenDecisionDate
     * @param \DateTime|null $decisionLetterSentDate
     */
    private function updateSla(
        $writtenOutcome,
        $callUpLetterDate,
        $briefToTcDate,
        $tcWrittenReasonDate,
        $writtenReasonLetterDate,
        $tcWrittenDecisionDate,
        $decisionLetterSentDate
    ) {
        $this->writtenOutcome = $writtenOutcome;
        $this->callUpLetterDate = $callUpLetterDate;
        $this->briefToTcDate = $briefToTcDate;
        $this->tcWrittenReasonDate = $tcWrittenReasonDate;
        $this->writtenReasonLetterDate = $writtenReasonLetterDate;
        $this->tcWrittenDecisionDate = $tcWrittenDecisionDate;
        $this->decisionLetterSentDate = $decisionLetterSentDate;
    }

    /**
     * Close the Pi
     */
    public function close()
    {
        if (!$this->canClose()) {
            throw new ForbiddenException('Pi is not allowed to be closed');
        }

        $this->closedDate = new \DateTime();
    }

    /**
     * Reopen the Pi
     */
    public function reopen()
    {
        if (!$this->canReopen()) {
            throw new ForbiddenException('Pi is not allowed to be reopened');
        }

        $this->closedDate = null;
    }

    /**
     * Can the Pi be closed?
     *
     * @return bool
     */
    public function canClose()
    {
        //if latest pi hearing is cancelled
        if ($this->piHearings->count() > 0) {
            if ($this->piHearings->last()->getIsCancelled() === 'Y') {
                return !$this->isClosed();
            }
        }

        //sla fields not specific to the decision
        if ($this->callUpLetterDate === null || $this->briefToTcDate === null) {
            return false;
        }

        //sla fields specific to the decision
        if ($this->writtenOutcome !== null) {
            $writtenOutcomeId = $this->writtenOutcome->getId();

            switch($writtenOutcomeId) {
                case SlaEntity::WRITTEN_OUTCOME_NONE:
                    return !$this->isClosed();
                case SlaEntity::WRITTEN_OUTCOME_REASON:
                    if ($this->tcWrittenReasonDate === null || $this->writtenReasonLetterDate === null) {
                        return false;
                    }
                    return !$this->isClosed();
                case SlaEntity::WRITTEN_OUTCOME_DECISION:
                    if ($this->tcWrittenDecisionDate === null || $this->decisionLetterSentDate === null) {
                        return false;
                    }
                    return !$this->isClosed();
            }
        }

        return false;
    }

    /**
     * @param string $date
     * @param string $format
     * @param bool $zeroTime
     * @return \DateTime|null
     */
    public function processDate($date, $format = 'Y-m-d', $zeroTime = true)
    {
        $dateTime = \DateTime::createFromFormat($format, $date);

        if (!$dateTime instanceof \DateTime) {
            return null;
        }

        if ($zeroTime) {
            $dateTime->setTime(0, 0, 0);
        }

        return $dateTime;
    }

    /**
     * Is this a Transport Manager Pi?
     */
    public function isTm()
    {
        return $this->case->isTm();
    }

    /**
     * Is the Pi closed?
     *
     * return bool
     */
    public function isClosed()
    {
        return $this->canReopen();
    }

    /**
     * Can the Pi be reopened?
     *
     * @return bool
     */
    public function canReopen()
    {
        return (bool) $this->closedDate != null;
    }

    /**
     * Gets the upcoming hearing date
     */
    public function getHearingDate()
    {
        if ($this->piHearings->count() > 0) {
            /** @var PiHearingEntity $hearing */
            $hearing = $this->piHearings->last();

            if ($hearing->getIsAdjourned() !== 'Y' && $hearing->getIsCancelled() !== 'Y') {
                return $hearing->getHearingDate();
            }
        }

        return null;
    }

    /**
     * Calculated values to be added to a bundle
     *
     * @return array
     */
    public function getCalculatedBundleValues()
    {
        return [
            'isClosed' => $this->isClosed(),
            'canReopen' => $this->canReopen(),
            'canClose' => $this->canClose(),
            'hearingDate' => $this->getHearingDate(),
            'isTm' => $this->isTm()
        ];
    }
}
