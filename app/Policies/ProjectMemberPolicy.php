<?php

namespace App\Policies;

use App\Contracts\Repositories\ProjectMemberRepository;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;

class ProjectMemberPolicy extends BasePolicy
{
    use Helpers\ProjectMemberChecks;

    public function __construct(ProjectMemberRepository $projectMemberRepository)
    {
        $this->projectMemberRepository = $projectMemberRepository;
    }

    public function before(User $user, $ability): ?bool
    {
        if (in_array($ability, ['update', 'delete'])) {
            return null;
        }

        if ($this->hasAdministrativeRights($user)) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user, Project $project): bool
    {
        return $this->canViewProject($user, $project);
    }

    public function create(User $user, Project $project): bool
    {
        return $this->isLeadInProject($user, $project);
    }

    public function update(User $user, ProjectMember $projectMember, Project $project): bool
    {
        // You can't change your role in the project.
        if ($projectMember->user->id === $user->id) {
            return false;
        }

        return $this->isLeadInProject($user, $project) || $this->hasAdministrativeRights($user);
    }

    public function delete(User $user, ProjectMember $projectMember, Project $project): bool
    {
        // You can't remove yourself from the project.
        if ($projectMember->user->id === $user->id) {
            return false;
        }

        return $this->isLeadInProject($user, $project) || $this->hasAdministrativeRights($user);
    }
}