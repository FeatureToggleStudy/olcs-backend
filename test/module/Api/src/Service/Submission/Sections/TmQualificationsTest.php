<?php

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class TmQualificationsTest
 * @author Shaun Lizzio <shaun@valtech.co.uk>
 */
class TmQualificationsTest extends SubmissionSectionTest
{
    protected $submissionSection = '\Dvsa\Olcs\Api\Service\Submission\Sections\TmQualifications';

    /**
     * Filter provider
     *
     * @return array
     */
    public function sectionTestProvider()
    {
        $case = $this->getCase();

        $expectedResult = [
            'data' => [
                'tables' => [
                    'tm-qualifications' => [
                        0 => [
                            'id' => 1,
                            'version' => 5,
                            'qualificationType' => 'tm-qual-desc',
                            'serialNo' => '12344321',
                            'country' => 'GB-desc',
                            'issuedDate' => '04/12/2008'
                        ]
                    ]
                ]
            ]
        ];

        return [
            [$case, $expectedResult],
        ];
    }
}
