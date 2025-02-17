<?php

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\RefData as Repo;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Class RefDataTest
 * @package OlcsTest\Db\Entity\Repository
 */
class RefDataTest extends RepositoryTestCase
{
    public function testApplyListFilters()
    {
        $sut = m::mock(Repo::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $mockDqb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $mockDqb->shouldReceive('expr->eq')->with('m.refDataCategoryId', ':category')->once()
            ->andReturn('EXPR');
        $mockDqb->shouldReceive('andWhere')->with('EXPR')->once()->andReturnSelf();
        $mockDqb->shouldReceive('setParameter')->with('category', 'cat')->once()->andReturnSelf();
        $mockDqb->shouldReceive('orderBy')->with('m.displayOrder')->once()->andReturnSelf();
        $mockDqb->shouldReceive('addOrderBy')->with('m.description')->once()->andReturnSelf();

        $mockDqb->shouldReceive('getQuery')
            ->andReturn(
                m::mock()
                ->shouldReceive('setHint')
                ->with(
                    \Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER,
                    'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
                )
                ->once()
                ->shouldReceive('setHint')
                ->with(\Gedmo\Translatable\TranslatableListener::HINT_FALLBACK, 1)
                ->once()
                ->shouldReceive('setHint')
                ->with(\Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE, 'en')
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $params = [
            'refDataCategory' => 'cat',
            'language' => 'en'
        ];
        $query = \Dvsa\Olcs\Transfer\Query\RefData\RefDataList::create($params);
        $sut->applyListFilters($mockDqb, $query);
    }

    public function testApplyListJoins()
    {
        $sut = m::mock(Repo::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $mockQb = m::mock(QueryBuilder::class);

        $mockQb->shouldReceive('modifyQuery')->andReturnSelf();
        $mockQb->shouldReceive('with')->with('parent', 'p')->once()->andReturnSelf();
        $sut->shouldReceive('getQueryBuilder')->with()->andReturn($mockQb);

        $sut->applyListJoins($mockQb);
    }
}
