<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section;

use Dvsa\Olcs\Api\Entity\Surrender;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Licence\Licence;

class DocumentationReviewService extends AbstractReviewService
{
    /**
     * @param Surrender $surrender
     *
     * @return array|mixed
     */
    public function getConfigFromData(Surrender $surrender)
    {

        $items = [];

        $items[] =
            [
                'label' => 'surrender-review-documentation-operator-licence',
                'value' => $surrender->getLicenceDocumentStatus()->getDescription()
            ];

        if ($surrender->getLicenceDocumentStatus()->getId() !== RefData::SURRENDER_DOC_STATUS_DESTROYED) {
            $items[] = [
                'label' => 'surrender-review-additional-information',
                'value' => $surrender->getLicenceDocumentInfo()
            ];
        }

        if ($surrender->getLicence()->getLicenceType()->getId() === Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL) {
            $items[] =
                [
                    'label' => 'surrender-review-documentation-community-licence',
                    'value' => $surrender->getCommunityLicenceDocumentStatus()->getDescription()
                ];

            if ($surrender->getCommunityLicenceDocumentStatus()->getId() !== RefData::SURRENDER_DOC_STATUS_DESTROYED) {
                $items[] =
                    [
                        'label' => 'surrender-review-additional-information',
                        'value' => $surrender->getCommunityLicenceDocumentInfo()
                    ];
            }
        }

        return [
            'multiItems' => [
                $items
            ]
        ];
    }
}
