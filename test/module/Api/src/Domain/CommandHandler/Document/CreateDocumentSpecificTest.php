<?php

/**
 * Create Document Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository\Document;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Transfer\Command\Document\UpdateDocumentLinks;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Document\CreateDocumentSpecific;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\CommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Document\CreateDocumentSpecific as Cmd;
use Dvsa\Olcs\Api\Entity\Doc\Document as Entity;
use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\CreateSubmission as CreateEbsrSubmissionCmd;

/**
 * Create Document Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreateDocumentSpecificTest extends CommandHandlerTestCase
{
    public function setUp()
    {
        $this->sut = new CreateDocumentSpecific();
        $this->mockRepo('Document', Document::class);

        parent::setUp();
    }

    protected function initReferences()
    {
        $this->refData = [];

        $this->references = [];

        $this->categoryReferences = [
            1 => m::mock(Category::class),
        ];

        $this->subCategoryReferences = [
            2 => m::mock(SubCategory::class),
        ];

        parent::initReferences();
    }

    public function testHandleCommand()
    {
        $data = [
            'identifier' => 'ABCDEF',
            'filename' => 'foo.pdf',
            'size' => 1024,
            'category' => 1,
            'subCategory' => 2,
            'application' => 123,
            'issuedDate' => '2015-01-01',
            'metadata' => 'foo',
            'isEbsrPack' => 0
        ];

        $command = Cmd::create($data);

        $this->repoMap['Document']->shouldReceive('save')
            ->with(m::type(Entity::class))
            ->andReturnUsing(
                function (Entity $document) {
                    $document->setId(111);

                    $this->assertEquals('ABCDEF', $document->getIdentifier());
                    $this->assertNull($document->getIsExternal());
                    $this->assertEquals('foo.pdf', $document->getFilename());
                    $this->assertEquals(1024, $document->getSize());
                    $this->assertSame($this->categoryReferences[1], $document->getCategory());
                    $this->assertSame($this->subCategoryReferences[2], $document->getSubCategory());
                    $this->assertNull($document->getLicence());
                    $this->assertInstanceOf('\DateTime', $document->getIssuedDate());
                    $this->assertEquals('2015-01-01', $document->getIssuedDate()->format('Y-m-d'));
                    $this->assertEquals('foo', $document->getMetadata());
                }
            );

        $result = new Result();
        $this->expectedSideEffect(UpdateDocumentLinks::class, ['id' => 111, 'application' => 123], $result);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'document' => 111
            ],
            'messages' => [
                'Document created'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * Tests the extra side effect is thrown when document is EBSR pack
     */
    public function testHandleCommandEbsr()
    {
        $data = [
            'identifier' => 'ABCDEF',
            'filename' => 'foo.pdf',
            'size' => 1024,
            'category' => 1,
            'subCategory' => 2,
            'application' => 123,
            'issuedDate' => '2015-01-01',
            'metadata' => 'foo',
            'isEbsrPack' => true
        ];

        $command = Cmd::create($data);

        $this->repoMap['Document']->shouldReceive('save')
            ->with(m::type(Entity::class))
            ->andReturnUsing(
                function (Entity $document) {
                    $document->setId(111);

                    $this->assertEquals('ABCDEF', $document->getIdentifier());
                    $this->assertNull($document->getIsExternal());
                    $this->assertEquals('foo.pdf', $document->getFilename());
                    $this->assertEquals(1024, $document->getSize());
                    $this->assertSame($this->categoryReferences[1], $document->getCategory());
                    $this->assertSame($this->subCategoryReferences[2], $document->getSubCategory());
                    $this->assertNull($document->getLicence());
                    $this->assertInstanceOf('\DateTime', $document->getIssuedDate());
                    $this->assertEquals('2015-01-01', $document->getIssuedDate()->format('Y-m-d'));
                    $this->assertEquals('foo', $document->getMetadata());
                }
            );

        $result = new Result();
        $this->expectedSideEffect(UpdateDocumentLinks::class, ['id' => 111, 'application' => 123], $result);
        $this->expectedSideEffect(CreateEbsrSubmissionCmd::class, ['document' => 111], $result);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'document' => 111
            ],
            'messages' => [
                'Document created'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandInternal()
    {
        $data = [
            'identifier' => 'ABCDEF',
            'filename' => 'foo.pdf',
            'size' => 1024,
            'category' => 1,
            'subCategory' => 2,
            'application' => 123,
            'isEbsrPack' => 0
        ];

        $command = Cmd::create($data);

        $this->repoMap['Document']->shouldReceive('save')
            ->with(m::type(Entity::class))
            ->andReturnUsing(
                function (Entity $document) {
                    $document->setId(111);

                    $this->assertEquals('ABCDEF', $document->getIdentifier());
                    $this->assertNull($document->getIsExternal());
                    $this->assertEquals('foo.pdf', $document->getFilename());
                    $this->assertEquals(1024, $document->getSize());
                    $this->assertSame($this->categoryReferences[1], $document->getCategory());
                    $this->assertSame($this->subCategoryReferences[2], $document->getSubCategory());
                    $this->assertNull($document->getLicence());
                }
            );

        $result = new Result();
        $this->expectedSideEffect(UpdateDocumentLinks::class, ['id' => 111, 'application' => 123], $result);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'document' => 111
            ],
            'messages' => [
                'Document created'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandIsExternalSet()
    {
        $data = [
            'identifier' => 'ABCDEF',
            'filename' => 'foo.pdf',
            'size' => 1024,
            'category' => 1,
            'subCategory' => 2,
            'application' => 123,
            'isExternal' => true,
            'isEbsrPack' => 0
        ];

        $command = Cmd::create($data);

        $this->repoMap['Document']->shouldReceive('save')
            ->with(m::type(Entity::class))
            ->andReturnUsing(
                function (Entity $document) {
                    $document->setId(111);

                    $this->assertEquals('ABCDEF', $document->getIdentifier());
                    $this->assertTrue($document->getIsExternal());
                    $this->assertEquals('foo.pdf', $document->getFilename());
                    $this->assertEquals(1024, $document->getSize());
                    $this->assertSame($this->categoryReferences[1], $document->getCategory());
                    $this->assertSame($this->subCategoryReferences[2], $document->getSubCategory());
                    $this->assertNull($document->getLicence());
                }
            );

        $result = new Result();
        $this->expectedSideEffect(UpdateDocumentLinks::class, ['id' => 111, 'application' => 123], $result);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'document' => 111
            ],
            'messages' => [
                'Document created'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
