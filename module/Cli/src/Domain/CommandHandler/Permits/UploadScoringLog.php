<?php

namespace Dvsa\Olcs\Cli\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Domain\ToggleRequiredInterface;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Document\Upload as UploadCmd;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;


/**
 * Upload the log output for the permit scoring
 * batch process
 *
 */
final class UploadScoringLog extends AbstractCommandHandler implements ToggleRequiredInterface
{
    use ToggleAwareTrait;

    protected $toggleConfig = [FeatureToggle::BACKEND_ECMT];
    protected $repoServiceName = 'IrhpCandidatePermit';

    /**
    * Handle command
    *
    * @param CommandInterface $command command
    *
    * @return Result
    */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $content = $command->getLogContent();

        $data = [
            'content' => base64_encode($content),
            'category' => Category::CATEGORY_PERMITS,
            'subCategory' => SubCategory::PERMITS_SUB_CATEGORY,
            'filename' => 'international-goods-list.log',
            'description' => 'Scoring Log File ' . date('d/m/Y'),
            'user' => \Dvsa\Olcs\Api\Rbac\PidIdentityProvider::SYSTEM_USER,
        ];

        $document = $this->handleSideEffect(
            UploadCmd::create($data)
        );

        $result->addMessage('Log file successfully uploaded.');

        return $result;
    }
}
