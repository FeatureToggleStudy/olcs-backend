<?php

namespace Dvsa\OlcsTest\Api\Service\Lva;

use Dvsa\Olcs\Api\Service\Lva\RestrictionService;

/**
 * @covers Dvsa\Olcs\Api\Service\Lva\RestrictionService
 */
class RestrictionServiceTest extends \PHPUnit\Framework\TestCase
{
    /** @var  RestrictionService */
    protected $helper;

    /**
     * Setup the helper
     */
    public function setUp()
    {
        $this->helper = new RestrictionService();
    }

    /**
     * Test isRestrictionSatisfied
     *
     * @dataProvider dpTestIsRestrictionSatisfied
     *
     * @group helper_service
     * @group restriction_helper_service
     */
    public function testIsRestrictionSatisfied($restrictions, $accessKeys, $expected, $ref = null)
    {
        $this->assertEquals($expected, $this->helper->isRestrictionSatisfied($restrictions, $accessKeys, $ref));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function dpTestIsRestrictionSatisfied()
    {
        return array(
            //  check callable
            [
                'restrictions' => function ($arg) {
                    static::assertEquals($arg, 'unit_Ref');

                    return 'EXPECTED';
                },
                'accessKeys' => [],
                'expected' => 'EXPECTED',
                'ref' => 'unit_Ref',
            ],
            // Really simple restrictions
            array(
                // We just need to match the string in the array
                'foo',
                array('foo'),
                true
            ),
            array(
                'foo',
                array('foo', 'bar'),
                true
            ),
            array(
                'foo',
                array(),
                false
            ),
            array(
                'foo',
                array('bar'),
                false
            ),
            // Simple restrictions
            array(
                // We can match ANY of the items
                array('foo', 'bar'),
                array('foo'),
                true
            ),
            array(
                array('foo', 'bar'),
                array('bar'),
                true
            ),
            array(
                array('foo', 'bar'),
                array('foo', 'bar'),
                true
            ),
            array(
                array('foo', 'bar'),
                array('foo', 'bar', 'cake'),
                true
            ),
            array(
                array('foo', 'bar'),
                array('cake', 'fudge'),
                false
            ),
            array(
                array('foo'),
                array('bar'),
                false
            ),
            array(
                array('foo'),
                array(),
                false
            ),
            // Strict restrictions
            array(
                array(
                    // We need to match ALL items in the sub array
                    array('foo', 'bar')
                ),
                array('foo', 'bar'),
                true
            ),
            array(
                array(
                    array('foo', 'bar', 'cake')
                ),
                array('foo', 'bar'),
                false
            ),
            array(
                array(
                    array('foo', 'bar', 'cake')
                ),
                array(),
                false
            ),
            // Combination of Strict and Not Strict
            array(
                array(
                    // We need to match ALL items in the sub array
                    array('foo', 'bar'),
                    // Or just this one
                    'cake'
                ),
                array('foo', 'bar'),
                true
            ),
            array(
                array(
                    array('foo', 'bar'),
                    'cake'
                ),
                array('foo', 'bar', 'cake'),
                true
            ),
            array(
                array(
                    array('foo', 'bar'),
                    'cake'
                ),
                array('foo', 'cake'),
                true
            ),
            array(
                array(
                    array('foo', 'bar'),
                    'cake'
                ),
                array('cake'),
                true
            ),
            array(
                array(
                    array('foo', 'bar'),
                    'cake'
                ),
                array('fudge'),
                false
            ),
            array(
                array(
                    array('foo', 'bar'),
                    'cake'
                ),
                array(),
                false
            ),
            // Complex rules
            array(
                array(
                    // Must match ALL of these
                    array(
                        'foo',
                        // This can be satisfied by anything in here
                        array('fudge', 'bar')
                    ),
                    // Or this one
                    'cake'
                ),
                array('foo', 'fudge'),
                true
            ),
            array(
                array(
                    array(
                        'foo',
                        array('fudge', 'bar')
                    ),
                    'cake'
                ),
                array('foo', 'bar'),
                true
            ),
            array(
                array(
                    array(
                        'foo',
                        array('fudge', 'bar')
                    ),
                    'cake'
                ),
                array('cake'),
                true
            ),
            array(
                array(
                    array(
                        'foo',
                        array('fudge', 'bar')
                    ),
                    'cake'
                ),
                array('fudge'),
                false
            ),
            array(
                array(
                    array(
                        'foo',
                        array('fudge', 'bar')
                    ),
                    'cake'
                ),
                array('fudge', 'bar'),
                false
            ),
            array(
                array(
                    array(
                        'foo',
                        array('fudge', 'bar')
                    ),
                    'cake'
                ),
                array('foo'),
                false
            ),
            array(
                array(
                    array(
                        'foo',
                        array('fudge', 'bar')
                    ),
                    'cake'
                ),
                array(),
                false
            ),
            array(
                array(
                    array(
                        array('foo', 'whip'),
                        array('fudge', 'bar')
                    ),
                    'cake'
                ),
                array('foo', 'bar'),
                true
            ),
            array(
                array(
                    array(
                        array('foo', 'whip'),
                        array('fudge', 'bar')
                    ),
                    'cake'
                ),
                array('foo'),
                false
            ),
            // Edge cases
            array(
                null,
                array('foo'),
                false
            ),
            array(
                null,
                array(),
                false
            ),
            array(
                null,
                array(null),
                false
            ),
        );
    }
}
