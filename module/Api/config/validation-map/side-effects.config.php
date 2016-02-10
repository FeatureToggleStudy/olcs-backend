<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion as AppCompCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\Queue as QueueCommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\NoValidationRequired;

/**
 * @NOTE This is the home of all commands that are only ever called as side-effects. The calling commands should
 * already have validation, and if the user has the ability to run the calling command, then all side-effects should be
 * runnable
 */
return [
    CommandHandler\Application\GrantGoods::class                                      => NoValidationRequired::class,
    CommandHandler\Application\GrantPsv::class                                        => NoValidationRequired::class,
    CommandHandler\Application\CreateGrantFee::class                                  => NoValidationRequired::class,
    CommandHandler\Application\Grant\CreateDiscRecords::class                         => NoValidationRequired::class,
    CommandHandler\Application\Grant\CopyApplicationDataToLicence::class              => NoValidationRequired::class,
    CommandHandler\Application\Grant\ProcessApplicationOperatingCentres::class        => NoValidationRequired::class,
    CommandHandler\Application\Grant\CommonGrant::class                               => NoValidationRequired::class,
    CommandHandler\Application\Grant\GrantConditionUndertaking::class                 => NoValidationRequired::class,
    CommandHandler\Application\Grant\GrantCommunityLicence::class                     => NoValidationRequired::class,
    CommandHandler\Application\Grant\GrantTransportManager::class                     => NoValidationRequired::class,
    CommandHandler\Application\Grant\GrantPeople::class                               => NoValidationRequired::class,
    CommandHandler\Application\Grant\ValidateApplication::class                       => NoValidationRequired::class,
    CommandHandler\Application\Grant\Schedule41::class                                => NoValidationRequired::class,
    CommandHandler\Application\Grant\ProcessDuplicateVehicles::class                  => NoValidationRequired::class,
    CommandHandler\Application\InForceInterim::class                                  => NoValidationRequired::class,
    CommandHandler\Application\EndInterim::class                                      => NoValidationRequired::class,
    CommandHandler\Application\HandleOcVariationFees::class                           => NoValidationRequired::class,
    CommandHandler\Application\CreateTexTask::class                                   => NoValidationRequired::class,
    CommandHandler\Application\CloseTexTask::class                                    => NoValidationRequired::class,
    CommandHandler\Application\CloseFeeDueTask::class                                 => NoValidationRequired::class,
    CommandHandler\Task\CreateTranslateToWelshTask::class                             => NoValidationRequired::class,
    CommandHandler\Bus\Ebsr\ProcessRequestMap::class                                  => NoValidationRequired::class,
    CommandHandler\Document\DispatchDocument::class                                   => NoValidationRequired::class,
    CommandHandler\Licence\VoidAllCommunityLicences::class                            => NoValidationRequired::class,
    CommandHandler\Licence\ReturnAllCommunityLicences::class                          => NoValidationRequired::class,
    CommandHandler\Licence\ExpireAllCommunityLicences::class                          => NoValidationRequired::class,
    CommandHandler\Licence\TmNominatedTask::class                                     => NoValidationRequired::class,
    CommandHandler\Licence\Withdraw::class                                            => NoValidationRequired::class,
    CommandHandler\Licence\Grant::class                                               => NoValidationRequired::class,
    CommandHandler\Licence\Refuse::class                                              => NoValidationRequired::class,
    CommandHandler\Licence\NotTakenUp::class                                          => NoValidationRequired::class,
    CommandHandler\Licence\UnderConsideration::class                                  => NoValidationRequired::class,
    CommandHandler\Organisation\ChangeBusinessType::class                             => NoValidationRequired::class,
    CommandHandler\Correspondence\ProcessInboxDocuments::class                        => NoValidationRequired::class,
    CommandHandler\Document\CreateDocumentSpecific::class                             => NoValidationRequired::class,
    CommandHandler\Application\CreateApplicationFee::class                            => NoValidationRequired::class,
    CommandHandler\Application\ResetApplication::class                                => NoValidationRequired::class,
    CommandHandler\Application\GenerateLicenceNumber::class                           => NoValidationRequired::class,
    CommandHandler\Application\UpdateVariationCompletion::class                       => NoValidationRequired::class,
    CommandHandler\Application\CreateFee::class                                       => NoValidationRequired::class,
    CommandHandler\Application\CancelAllInterimFees::class                            => NoValidationRequired::class,
    CommandHandler\Application\CancelOutstandingFees::class                           => NoValidationRequired::class,
    CommandHandler\Application\SetDefaultTrafficAreaAndEnforcementArea::class         => NoValidationRequired::class,
    CommandHandler\Application\DeleteApplication::class                               => NoValidationRequired::class,
    CommandHandler\ApplicationOperatingCentre\CreateApplicationOperatingCentre::class => NoValidationRequired::class,
    CommandHandler\ApplicationOperatingCentre\DeleteApplicationOperatingCentre::class => NoValidationRequired::class,
    CommandHandler\LicenceOperatingCentre\AssociateS4::class                          => NoValidationRequired::class,
    CommandHandler\LicenceOperatingCentre\DisassociateS4::class                       => NoValidationRequired::class,
    CommandHandler\OperatingCentre\DeleteApplicationLinks::class                      => NoValidationRequired::class,
    CommandHandler\OperatingCentre\DeleteConditionUndertakings::class                 => NoValidationRequired::class,
    CommandHandler\OperatingCentre\DeleteTmLinks::class                               => NoValidationRequired::class,
    CommandHandler\Cases\ConditionUndertaking\CreateConditionUndertaking::class       => NoValidationRequired::class,
    CommandHandler\Cases\ConditionUndertaking\DeleteConditionUndertakingS4::class     => NoValidationRequired::class,
    CommandHandler\Schedule41\CreateS4::class                                         => NoValidationRequired::class,
    CommandHandler\Schedule41\ApproveS4::class                                        => NoValidationRequired::class,
    CommandHandler\Schedule41\ResetS4::class                                          => NoValidationRequired::class,
    CommandHandler\Schedule41\RefuseS4::class                                         => NoValidationRequired::class,
    CommandHandler\Schedule41\CancelS4::class                                         => NoValidationRequired::class,
    CommandHandler\Bus\CreateBusFee::class                                            => NoValidationRequired::class,
    CommandHandler\Licence\CancelLicenceFees::class                                   => NoValidationRequired::class,
    CommandHandler\Licence\UpdateTotalCommunityLicences::class                        => NoValidationRequired::class,
    CommandHandler\Licence\SaveAddresses::class                                       => NoValidationRequired::class,
    CommandHandler\Publication\PiHearing::class                                       => NoValidationRequired::class,
    CommandHandler\Publication\PiHearing::class                                       => NoValidationRequired::class,
    CommandHandler\Publication\CreateNextPublication::class                           => NoValidationRequired::class,
    CommandHandler\Publication\Licence::class                                         => NoValidationRequired::class,
    CommandHandler\Discs\CeaseGoodsDiscs::class                                       => NoValidationRequired::class,
    CommandHandler\Discs\CeasePsvDiscs::class                                         => NoValidationRequired::class,
    CommandHandler\LicenceVehicle\RemoveLicenceVehicle::class                         => NoValidationRequired::class,
    CommandHandler\Vehicle\ProcessDuplicateVehicleWarning::class                      => NoValidationRequired::class,
    CommandHandler\Vehicle\ProcessDuplicateVehicleWarnings::class                     => NoValidationRequired::class,
    CommandHandler\Tm\DeleteTransportManagerLicence::class                            => NoValidationRequired::class,
    CommandHandler\ContactDetails\SaveAddress::class                                  => NoValidationRequired::class,
    CommandHandler\Organisation\UpdateTradingNames::class                             => NoValidationRequired::class,
    CommandHandler\Fee\CancelFee::class                                               => NoValidationRequired::class,
    CommandHandler\Fee\CancelIrfoGvPermitFees::class                                  => NoValidationRequired::class,
    CommandHandler\Fee\CancelIrfoPsvAuthFees::class                                   => NoValidationRequired::class,
    CommandHandler\Fee\PayFee::class                                                  => NoValidationRequired::class,
    CommandHandler\Transaction\ResolvePayment::class                                  => NoValidationRequired::class,
    AppCompCommandHandler\UpdateTypeOfLicenceStatus::class                            => NoValidationRequired::class,
    AppCompCommandHandler\UpdateAddressesStatus::class                                => NoValidationRequired::class,
    AppCompCommandHandler\UpdateBusinessTypeStatus::class                             => NoValidationRequired::class,
    AppCompCommandHandler\UpdateConvictionsPenaltiesStatus::class                     => NoValidationRequired::class,
    AppCompCommandHandler\UpdateFinancialEvidenceStatus::class                        => NoValidationRequired::class,
    AppCompCommandHandler\UpdateFinancialHistoryStatus::class                         => NoValidationRequired::class,
    AppCompCommandHandler\UpdateLicenceHistoryStatus::class                           => NoValidationRequired::class,
    AppCompCommandHandler\UpdateOperatingCentresStatus::class                         => NoValidationRequired::class,
    AppCompCommandHandler\UpdatePeopleStatus::class                                   => NoValidationRequired::class,
    AppCompCommandHandler\UpdateSafetyStatus::class                                   => NoValidationRequired::class,
    AppCompCommandHandler\UpdateVehiclesStatus::class                                 => NoValidationRequired::class,
    AppCompCommandHandler\UpdateUndertakingsStatus::class                             => NoValidationRequired::class,
    AppCompCommandHandler\UpdateConditionsUndertakingsStatus::class                   => NoValidationRequired::class,
    AppCompCommandHandler\UpdateVehiclesDeclarationsStatus::class                     => NoValidationRequired::class,
    AppCompCommandHandler\UpdateVehiclesPsvStatus::class                              => NoValidationRequired::class,
    AppCompCommandHandler\UpdateTransportManagersStatus::class                        => NoValidationRequired::class,
    AppCompCommandHandler\UpdateTaxiPhvStatus::class                                  => NoValidationRequired::class,
    AppCompCommandHandler\UpdateCommunityLicencesStatus::class                        => NoValidationRequired::class,
    AppCompCommandHandler\UpdateBusinessDetailsStatus::class                          => NoValidationRequired::class,
    AppCompCommandHandler\UpdateDeclarationsInternalStatus::class                     => NoValidationRequired::class,
    CommandHandler\CommunityLic\GenerateBatch::class                                  => NoValidationRequired::class,
    CommandHandler\LicenceStatusRule\ProcessToRevokeCurtailSuspend::class             => NoValidationRequired::class,
    CommandHandler\LicenceStatusRule\ProcessToValid::class                            => NoValidationRequired::class,
    CommandHandler\LicenceStatusRule\RemoveLicenceStatusRulesForLicence::class        => NoValidationRequired::class,
    CommandHandler\Email\CreateCorrespondenceRecord::class                            => NoValidationRequired::class,
    CommandHandler\Email\SendContinuationNotSought::class                             => NoValidationRequired::class,
    CommandHandler\Email\SendTmUserCreated::class                                     => NoValidationRequired::class,
    CommandHandler\Email\SendUserCreated::class                                       => NoValidationRequired::class,
    CommandHandler\Email\SendUserRegistered::class                                    => NoValidationRequired::class,
    CommandHandler\Email\SendUserTemporaryPassword::class                             => NoValidationRequired::class,
    CommandHandler\Email\SendUsernameSingle::class                                    => NoValidationRequired::class,
    CommandHandler\Email\SendUsernameMultiple::class                                  => NoValidationRequired::class,
    CommandHandler\Email\SendEbsrWithdrawn::class                                     => NoValidationRequired::class,
    CommandHandler\Email\SendEbsrRefused::class                                       => NoValidationRequired::class,
    CommandHandler\Email\SendEbsrRegistered::class                                    => NoValidationRequired::class,
    CommandHandler\Email\SendEbsrCancelled::class                                     => NoValidationRequired::class,
    CommandHandler\Email\SendEbsrReceived::class                                      => NoValidationRequired::class,
    CommandHandler\Email\SendEbsrRefreshed::class                                     => NoValidationRequired::class,
    CommandHandler\Person\Create::class                                               => NoValidationRequired::class,
    CommandHandler\Person\UpdateFull::class                                           => NoValidationRequired::class,
    CommandHandler\TransportManagerApplication\Snapshot::class                        => NoValidationRequired::class,
    CommandHandler\PrintScheduler\Enqueue::class                                      => NoValidationRequired::class,
    CommandHandler\Vehicle\CreateGoodsVehicle::class                                  => NoValidationRequired::class,
    CommandHandler\Vehicle\CeaseActiveDiscs::class                                    => NoValidationRequired::class,
    CommandHandler\Vehicle\CreateGoodsDiscs::class                                    => NoValidationRequired::class,
    CommandHandler\InspectionRequest\SendInspectionRequest::class                     => NoValidationRequired::class,
    CommandHandler\ContinuationDetail\Process::class                                  => NoValidationRequired::class,
    CommandHandler\ContinuationDetail\ProcessReminder::class                          => NoValidationRequired::class,
    CommandHandler\CompaniesHouse\EnqueueOrganisations::class                         => NoValidationRequired::class,
    CommandHandler\CompaniesHouse\InitialLoad::class                                  => NoValidationRequired::class,
    CommandHandler\CompaniesHouse\Compare::class                                      => NoValidationRequired::class,
    CommandHandler\CompaniesHouse\CreateAlert::class                                  => NoValidationRequired::class,
    QueueCommandHandler\Complete::class                                               => NoValidationRequired::class,
    QueueCommandHandler\Failed::class                                                 => NoValidationRequired::class,
    QueueCommandHandler\Retry::class                                                  => NoValidationRequired::class,
    QueueCommandHandler\Create::class                                                 => NoValidationRequired::class,
    CommandHandler\Discs\PrintDiscs::class                                            => NoValidationRequired::class,
    CommandHandler\Discs\CreatePsvVehicleListForDiscs::class                          => NoValidationRequired::class,
    CommandHandler\Licence\ProcessContinuationNotSought::class                        => NoValidationRequired::class,
    CommandHandler\Variation\EndInterim::class                                        => NoValidationRequired::class,
    CommandHandler\SystemParameter\Update::class                                      => NoValidationRequired::class,
    QueryHandler\Queue\NextItem::class                                                => NoValidationRequired::class,
    CommandHandler\MyAccount\UpdateMyAccount::class                                   => NoValidationRequired::class,
    CommandHandler\Licence\BatchVehicleListGeneratorForGoodsDiscs::class              => NoValidationRequired::class,
    CommandHandler\Discs\BatchVehicleListGeneratorForPsvDiscs::class                  => NoValidationRequired::class,
];
