<?php

namespace App\Security\Voter;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskVoter extends Voter
{
    public const CREATE = 'TASK_CREATE';
    public const VIEW = 'TASK_VIEW';
    public const EDIT = 'TASK_EDIT';
    public const DELETE = 'TASK_DELETE';

    public function __construct( private Security $security, private EntityManagerInterface $em ) {}

    protected function supports( $attribute, $subject ): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::CREATE, self::EDIT, self::VIEW, self::DELETE]);
            //&& $subject instanceof \App\Entity\Task;
    }


    protected function voteOnAttribute( $attribute, $task, TokenInterface $token ): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }
        /*
        if ( is_array($task) ) {

            $task = $this->em->getRepository(Task::class)
                ->find($task['id']);

        }

        if ( !$task instanceof Task or is_null( $task ) ) {
            return false;
        }
        */
        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::CREATE:

                return $this->canCreate();

                break;
            case self::EDIT:
                
                return $this->canEdit($task,$user);

                break;
            case self::DELETE:
            
                return $this->canDelete($task,$user);

                break;
            case self::VIEW:
                
                return $this->canView($task,$user);

                break;
        }

        return false;
    }

    private function canCreate ()
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }

    private function canView ($task, User $user)
    {
        $more = false;
        
        if ( $task instanceof Task ) {

            $more = $task->getAssignedTo() == $user;

        } 
        
        return $this->check_role ($task, $user) or $more;
    
    }

    private function canEdit ($task, User $user)
    {
        return $this->check_role ($task, $user);
    }

    private function canDelete ($task, User $user)
    {
        return $this->check_role ($task, $user);
    }

    private function check_role ($task, $user)
    {
        if ( $task instanceof Task ) {

            return ( $this->security->isGranted('ROLE_ADMIN') and $task->getCreatedBy() == $user );
        
        }

        return false;

    }
}
