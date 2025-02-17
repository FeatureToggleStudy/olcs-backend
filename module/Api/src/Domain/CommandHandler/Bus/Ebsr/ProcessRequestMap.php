<?php

/**
 * Request new Ebsr map
 */
namespace Dvsa\Olcs\Api\Domain\CommandHandler\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactioningCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission as EbsrSubmissionEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Task\Task as TaskEntity;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\ProcessRequestMap as RequestMapCmd;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask as CreateTaskCmd;
use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\UpdateTxcInboxPdf as UpdateTxcInboxPdfCmd;
use Dvsa\Olcs\Transfer\Command\Document\Upload as UploadCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\UploaderAwareInterface;
use Dvsa\Olcs\Api\Domain\UploaderAwareTrait;
use Dvsa\Olcs\Api\Domain\TransExchangeAwareInterface;
use Dvsa\Olcs\Api\Domain\TransExchangeAwareTrait;
use Dvsa\Olcs\Api\Domain\ConfigAwareInterface;
use Dvsa\Olcs\Api\Domain\ConfigAwareTrait;
use Dvsa\Olcs\Api\Domain\FileProcessorAwareInterface;
use Dvsa\Olcs\Api\Domain\FileProcessorAwareTrait;
use Dvsa\Olcs\Api\Domain\QueueAwareTrait;
use Olcs\XmlTools\Xml\TemplateBuilder;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Domain\Exception\TransxchangeException;
use Dvsa\Olcs\Api\Service\Ebsr\TransExchangeClient;
use Olcs\Logging\Log\Logger;
use Dvsa\Olcs\Api\Service\Ebsr\FileProcessor;

/**
 * Request new Ebsr map
 */
final class ProcessRequestMap extends AbstractCommandHandler implements
    TransactionedInterface,
    UploaderAwareInterface,
    TransExchangeAwareInterface,
    ConfigAwareInterface,
    FileProcessorAwareInterface
{
    use UploaderAwareTrait;
    use TransExchangeAwareTrait;
    use ConfigAwareTrait;
    use FileProcessorAwareTrait;
    use QueueAwareTrait;

    const MISSING_TMP_DIR_ERROR = 'No tmp directory specified in config';
    const MISSING_PACK_FILE_ERROR = 'Could not fetch EBSR pack file';
    const MISSING_TEMPLATE_ERROR = 'Missing template: %s';

    const TASK_DESC = '%s created: %s';
    const SCALE_DESC = ' (%s Scale)';
    const PDF_GENERATED = "The following PDFs %s: %s";

    const TXC_INBOX_TYPE_ROUTE = 'Route';
    const TXC_INBOX_TYPE_PDF = 'Pdf';

    protected $repoServiceName = 'Bus';

    protected $templatePaths;

    protected $documentDescriptions = [
        TransExchangeClient::REQUEST_MAP_TEMPLATE => "Route Track Map PDF",
        TransExchangeClient::TIMETABLE_TEMPLATE => "Timetable PDF",
        TransExchangeClient::DVSA_RECORD_TEMPLATE => "DVSA Record PDF",
    ];

    /**
     * @var TemplateBuilder
     */
    protected $templateBuilder;

    /**
     * Creates the service (injects template builder)
     *
     * @param ServiceLocatorInterface $serviceLocator service locator
     *
     * @return TransactioningCommandHandler
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $mainServiceLocator = $serviceLocator->getServiceLocator();

        $this->templateBuilder = $mainServiceLocator->get(TemplateBuilder::class);

        return parent::createService($serviceLocator);
    }

    /**
     * Transxchange map request
     *
     * @param CommandInterface|RequestMapCmd $command the command
     *
     * @throws TransxchangeException
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var BusRegEntity $busReg
         * @var EbsrSubmissionEntity $submission
         */
        $config = $this->getConfig();

        if (!isset($config['ebsr']['tmp_extra_path'])) {
            Logger::info('TransXchange error', ['data' => self::MISSING_TMP_DIR_ERROR]);
            throw new TransxchangeException(self::MISSING_TMP_DIR_ERROR);
        }

        $sideEffects = [];
        $processedMaps = [];
        $failedMaps = [];
        $result = new Result();

        $busReg = $this->getRepo()->fetchUsingId($command);
        $ebsrSubmissions = $busReg->getEbsrSubmissions();
        $submission = $ebsrSubmissions->first();

        /** @var FileProcessor $fileProcessor */
        $fileProcessor = $this->getFileProcessor();
        $fileProcessor->setSubDirPath($config['ebsr']['tmp_extra_path']);

        try {
            $xmlFilename = $fileProcessor->fetchXmlFileNameFromDocumentStore(
                $submission->getDocument()->getIdentifier(),
                true
            );
        } catch (\Exception $e) {
            Logger::info('TransXchange error', ['data' => self::MISSING_PACK_FILE_ERROR]);
            throw new TransxchangeException(self::MISSING_PACK_FILE_ERROR);
        }

        //decide which template files we need
        $templates = [TransExchangeClient::DVSA_RECORD_TEMPLATE => TransExchangeClient::DVSA_RECORD_TEMPLATE];

        //we only create the dvsa record pdf for cancellations, otherwise create all three
        if (!$busReg->isCancellation()) {
            $templates[TransExchangeClient::TIMETABLE_TEMPLATE] = TransExchangeClient::TIMETABLE_TEMPLATE;
            $templates[TransExchangeClient::REQUEST_MAP_TEMPLATE] = TransExchangeClient::REQUEST_MAP_TEMPLATE;
        }

        $scale = $command->getScale();
        $busRegId = $busReg->getId();

        foreach ($templates as $templateFile) {
            try {
                $documentDesc = $this->getDocumentDescription($templateFile, $scale);
                $template = $this->createRequestMapTemplate($templateFile, $xmlFilename, $scale);
                $documents = $this->getTransExchange()->makeRequest($template);
            } catch (\Exception $e) {
                Logger::info('TransXchange error', ['data' => $e->getMessage()]);
                $failedMaps[$documentDesc] = $documentDesc;
                continue;
            }

            if (!isset($documents['files'])) {
                $failedMaps[$documentDesc] = $documentDesc;
                continue;
            }

            foreach ($documents['files'] as $document) {
                $uploadDocCmd = $this->generateDocumentCmd($document, $busReg, $command->getUser(), $documentDesc);
                $uploadedDoc = $this->handleSideEffect($uploadDocCmd);
                $documentId = $uploadedDoc->getId('document');
                $result->addId('document', $documentId, true);

                //add txc inbox pdf for all except timetables
                if ($templateFile !== TransExchangeClient::TIMETABLE_TEMPLATE) {
                    $sideEffects[] = $this->createUpdateTxcInboxPdfCmd($busRegId, $documentId, $templateFile);
                }

                $processedMaps[] = $documentDesc;
            }
        }

        $sideEffects[] = $this->createTaskCmd($busReg, $processedMaps, $failedMaps, $command->getFromNewEbsr());
        $result->merge($this->handleSideEffects($sideEffects));

        return $result;
    }

    /**
     * Creates a transxchange xml file to request a map
     *
     * @param string $template    xml template
     * @param string $xmlFilename xml file name and path
     * @param string $scale       scale of route map
     *
     * @return string
     * @throws TransxchangeException
     */
    private function createRequestMapTemplate($template, $xmlFilename, $scale)
    {
        $config = $this->getConfig();

        if (!isset($config['ebsr']['transexchange_publisher']['templates'][$template])) {
            throw new TransxchangeException(sprintf(self::MISSING_TEMPLATE_ERROR, $template));
        }

        $templatePath = $config['ebsr']['transexchange_publisher']['templates'][$template];
        $dir = dirname($xmlFilename);

        $substitutions = [
            'DocumentPath' => $dir,
            'DocumentName' => basename($xmlFilename),
            'OutputPath' => $dir,
            'RouteScale' => $scale
        ];

        return $this->templateBuilder->buildTemplate($templatePath, $substitutions);
    }

    /**
     * Creates a command to upload the transxchange map
     *
     * @param string       $document     document content
     * @param BusRegEntity $busReg       bus reg entity
     * @param int          $user         user id
     * @param string       $documentDesc document description
     *
     * @return UploadCmd
     */
    private function generateDocumentCmd($document, BusRegEntity $busReg, $user, $documentDesc)
    {
        $data = [
            'content' => base64_encode(file_get_contents($document)),
            'busReg' => $busReg->getId(),
            'licence' => $busReg->getLicence()->getId(),
            'category' => CategoryEntity::CATEGORY_BUS_REGISTRATION,
            'subCategory' => CategoryEntity::BUS_SUB_CATEGORY_TRANSXCHANGE_PDF,
            'filename' => basename($document),
            'description' => $documentDesc,
            'user' => $user
        ];

        return UploadCmd::create($data);
    }

    /**
     * Returns a command to create a task
     *
     * @param BusRegEntity $busReg        bus reg entity
     * @param array        $processedMaps processed maps, comma separated
     * @param array        $failedMaps    failed maps, comma separated
     * @param bool         $fromNewEbsr   whether this map request is a result of a new ebsr submission
     *
     * @return CreateTaskCmd
     */
    private function createTaskCmd(BusRegEntity $busReg, array $processedMaps, array $failedMaps, $fromNewEbsr)
    {
        $message = [];
        $state = 'pdf files'; //default, if this isn't as a result of a new EBSR pack being processed

        if ($fromNewEbsr) {
            if ($busReg->isEbsrRefresh()) {
                $state = 'data refresh';
            } else {
                $status = $busReg->getStatus()->getId();

                switch ($status) {
                    case BusRegEntity::STATUS_CANCEL:
                        $state = 'cancellation';
                        break;
                    case BusRegEntity::STATUS_VAR:
                        $state = 'variation';
                        break;
                    default:
                        $state = 'application';
                }
            }
        }

        $message[] = sprintf(self::TASK_DESC, 'New ' . $state, $busReg->getRegNo());

        if (!empty($processedMaps)) {
            $message[] = sprintf(self::PDF_GENERATED, 'were generated', implode(', ', $processedMaps));
        }

        if (!empty($failedMaps)) {
            $message[] = sprintf(self::PDF_GENERATED, 'failed to generate', implode(', ', $failedMaps));
        }

        $data = [
            'category' => TaskEntity::CATEGORY_BUS,
            'subCategory' => TaskEntity::SUBCATEGORY_EBSR,
            'description' => implode("\n", $message),
            'actionDate' => date('Y-m-d'),
            'busReg' => $busReg->getId(),
            'licence' => $busReg->getLicence()->getId(),
        ];

        return CreateTaskCmd::create($data);
    }

    /**
     * Creates a command to update TxcInbox records with the new document id
     * We don't do this for timetable pdfs
     *
     * @param int $busRegId     bus reg id
     * @param int $documentId   document id
     * @param int $templateFile the template file
     *
     * @return UpdateTxcInboxPdfCmd
     */
    private function createUpdateTxcInboxPdfCmd($busRegId, $documentId, $templateFile)
    {
        $pdfType = $templateFile === TransExchangeClient::REQUEST_MAP_TEMPLATE
                    ? self::TXC_INBOX_TYPE_ROUTE
                    : self::TXC_INBOX_TYPE_PDF;

        $data = [
            'id' => $busRegId,
            'document' => $documentId,
            'pdfType' => $pdfType
        ];

        return UpdateTxcInboxPdfCmd::create($data);
    }

    /**
     * Works out the document description (route maps also have a scale)
     *
     * @param string $templateFile template file
     * @param string $scale        scale of the route map
     *
     * @return string
     */
    public function getDocumentDescription($templateFile, $scale)
    {
        $scaleString = '';

        if ($templateFile === TransExchangeClient::REQUEST_MAP_TEMPLATE) {
            $scaleString = sprintf(self::SCALE_DESC, ucwords(strtolower($scale)));
        }

        return $this->documentDescriptions[$templateFile] . $scaleString;
    }

    /**
     * Returns the array of document descriptions
     *
     * @return array
     */
    public function getDocumentDescriptions()
    {
        return $this->documentDescriptions;
    }
}
