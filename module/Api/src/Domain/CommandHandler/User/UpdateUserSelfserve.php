<?php

/**
 * Update User Selfserve
 */
namespace Dvsa\Olcs\Api\Domain\CommandHandler\User;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;

/**
 * Update User Selfserve
 */
final class UpdateUserSelfserve extends AbstractCommandHandler implements AuthAwareInterface, TransactionedInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'User';

    protected $extraRepos = ['ContactDetails'];

    public function handleCommand(CommandInterface $command)
    {
        $user = $this->getRepo()->fetchById($command->getId(), Query::HYDRATE_OBJECT, $command->getVersion());

        if (!$this->isGranted(Permission::CAN_MANAGE_USER_SELFSERVE, $user)) {
            throw new ForbiddenException('You do not have permission to manage the record');
        }

        $data = $command->getArrayCopy();

        // populate roles based on the user type and permission
        $data['roles'] = User::getRolesByUserType($user->getUserType(), $data['permission']);

        $user->update(
            $this->getRepo()->populateRefDataReference($data)
        );

        if ($user->getContactDetails() instanceof ContactDetails) {
            // update existing contact details
            $user->getContactDetails()->update(
                $this->getRepo('ContactDetails')->populateRefDataReference(
                    $command->getContactDetails()
                )
            );
        } else {
            // create new contact details
            $user->setContactDetails(
                ContactDetails::create(
                    $this->getRepo()->getRefdataReference(ContactDetails::CONTACT_TYPE_USER),
                    $this->getRepo('ContactDetails')->populateRefDataReference(
                        $command->getContactDetails()
                    )
                )
            );
        }

        $this->getRepo()->save($user);

        $result = new Result();
        $result->addId('user', $user->getId());
        $result->addMessage('User updated successfully');

        return $result;
    }
}
