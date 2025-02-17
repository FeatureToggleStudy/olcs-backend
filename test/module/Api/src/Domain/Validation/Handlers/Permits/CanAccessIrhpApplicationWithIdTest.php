<?php

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Permits;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Permits\CanAccessIrhpApplicationWithId;

/**
 * @covers Dvsa\Olcs\Api\Domain\Validation\Handlers\Permits\CanAccessIrhpApplicationWithId
 */
class CanAccessIrhpApplicationWithIdTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessIrhpApplicationWithId
     */
    protected $sut;

    public function setUp()
    {
        $this->sut = new CanAccessIrhpApplicationWithId();

        parent::setUp();
    }

    /**
     * @dataProvider dpTestIsValid
     */
    public function testIsValid($canAccess, $expected)
    {
        /** @var CommandInterface $dto */
        $id = 111;
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn($id);

        $this->setIsValid('CanAccessIrhpApplicationWithId', [$id], $canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public function dpTestIsValid()
    {
        return [
            [true, true],
            [false, false],
        ];
    }
}
