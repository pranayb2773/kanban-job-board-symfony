<?php

namespace App\Security\Voter;

use App\Entity\JobBoard;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class JobBoardVoter extends Voter
{
    public const string VIEW = 'view';
    public const string EDIT = 'edit';
    public const string DELETE = 'delete';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof JobBoard;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, Vote|null $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var JobBoard $jobBoard */
        $jobBoard = $subject;

        return match($attribute) {
            self::VIEW, self::EDIT, self::DELETE => $this->canAccess($jobBoard, $user),
            default => false
        };
    }

    private function canAccess(JobBoard $jobBoard, User $user): bool
    {
        return $jobBoard->getUser() === $user;
    }
}
