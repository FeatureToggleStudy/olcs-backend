<?php

/**
 * Create Document
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Domain\Command\Document\CreateDocumentSpecific as Cmd;
use Dvsa\Olcs\Transfer\Command\Document\UpdateDocumentLinks;

/**
 * Create Document
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CreateDocumentSpecific extends AbstractCommandHandler
{
    protected $repoServiceName = 'Document';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $document = $this->createDocumentEntity($command);

        $this->getRepo()->save($document);

        $data = $command->getArrayCopy();
        $data['id'] = $document->getId();

        $result->merge($this->handleSideEffect(UpdateDocumentLinks::create($data)));

        $result->addId('document', $document->getId());
        $result->addMessage('Document created');

        return $result;
    }

    /**
     * @param Cmd $command
     * @return Document
     */
    private function createDocumentEntity(Cmd $command)
    {
        $document = new Document($command->getIdentifier());

        $this->categoriseDocument($document, $command);
        $this->setDocumentDetails($document, $command);
        $this->setDocumentFlags($document, $command);

        return $document;
    }

    private function setDocumentFlags(Document $document, Cmd $command)
    {
        $document->setIsExternal($command->getIsExternal());
        $document->setIsReadOnly($command->getIsReadOnly());
        $document->setIsScan($command->getIsScan());
    }

    private function setDocumentDetails(Document $document, Cmd $command)
    {
        $document->setFilename($command->getFilename());
        $document->setSize($command->getSize());
        $document->setDescription($command->getDescription());

        if ($command->getIssuedDate() !== null) {
            $document->setIssuedDate(new \DateTime($command->getIssuedDate()));
        }

        if ($command->getMetadata() !== null) {
            $document->setMetadata($command->getMetadata());
        }
    }

    private function categoriseDocument(Document $document, Cmd $command)
    {
        if ($command->getCategory() != null) {
            $document->setCategory($this->getRepo()->getCategoryReference($command->getCategory()));
        }

        if ($command->getSubCategory() != null) {
            $document->setSubCategory($this->getRepo()->getCategoryReference($command->getSubCategory()));
        }
    }
}
