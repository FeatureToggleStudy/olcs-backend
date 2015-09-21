<?php

/**
 * Create User Selfserve
 */
namespace Dvsa\Olcs\Api\Domain\CommandHandler\User;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Create User Selfserve
 */
final class CreateUserSelfserve extends AbstractCommandHandler implements AuthAwareInterface, TransactionedInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'User';

    protected $extraRepos = ['ContactDetails'];

    public function handleCommand(CommandInterface $command)
    {
        // TODO - OLCS-10516 - User management restrictions

        $data = $command->getArrayCopy();

        // copy user type from the current loggedin user
        switch ($this->getCurrentUser()->getUserType()) {
            case User::USER_TYPE_PARTNER:
                $data['userType'] = User::USER_TYPE_PARTNER;
                $data['partnerContactDetails'] = $this->getCurrentUser()->getPartnerContactDetails()->getId();
                break;
            case User::USER_TYPE_LOCAL_AUTHORITY:
                $data['userType'] = User::USER_TYPE_LOCAL_AUTHORITY;
                $data['localAuthority'] = $this->getCurrentUser()->getLocalAuthority()->getId();
                break;
            case User::USER_TYPE_SELF_SERVICE:
                $data['userType'] = User::USER_TYPE_SELF_SERVICE;
                $data['organisations'] = array_map(
                    function ($item) {
                        return $item->getOrganisation();
                    },
                    $this->getCurrentUser()->getOrganisationUsers()->toArray()
                );
                break;
            default:
                // only available to specific user types
                throw new BadRequestException('User type must be provided');
        }

        $user = User::create(
            $data['userType'],
            $this->getRepo()->populateRefDataReference($data)
        );

        // create new contact details
        $user->setContactDetails(
            ContactDetails::create(
                $this->getRepo()->getRefdataReference(ContactDetails::CONTACT_TYPE_USER),
                $this->getRepo('ContactDetails')->populateRefDataReference(
                    $command->getContactDetails()
                )
            )
        );

        $this->getRepo()->save($user);

        $result = new Result();
        $result->addId('user', $user->getId());
        $result->addMessage('User created successfully');

        return $result;
    }
}
