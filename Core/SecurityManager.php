<?php
class SecurityManager extends BaseManager {

	/**
	 * checks if a user is the public user
	 * @param User $user
	 */
	public static function checkPublicUser($user) {
		if ($user->userId == 1) {
			throw new UnauthorizedException("You are not authorized because I don't know you");
		}
	}
	
	/**
	 * promotes a user to an admin user
	 * @param User $user
	 */
	public function promoteUserToAdmin($user) {
		$aci = $this->repository->getAccessControlItem(0, $user->userId);
		if ($aci != null) {
			return;
		}
		$acl = new AccessControlItem();
		$acl->userId = $user->userId;
		$acl->roleId = RoleEnum::Admin;
		$acl->projectId = 0;
		$acl = $this->repository->createAccessControlItem($acl);
	}

	/**
	 * promotes a user to project lead
	 * @param User $user
	 * @param Project $project
	 */
	public function promoteUserToProjectLead($user, $project) {
		$aci = $this->repository->getAccessControlItem($project->projectId, $user->userId);
		if ($aci == null) {
			$acl = new AccessControlItem();
			$acl->userId = $user->userId;
			$acl->roleId = RoleEnum::Lead;
			$acl->projectId = $project->projectId;
			$acl = $this->repository->createAccessControlItem($acl);
			return;	
		}
		if ($aci->roleId == RoleEnum::Member) {
			$this->repository->deleteAccessControlItem($aci->aclId);
			$acl = new AccessControlItem();
			$acl->userId = $user->userId;
			$acl->roleId = RoleEnum::Lead;
			$acl->projectId = $project->projectId;
			$acl = $this->repository->createAccessControlItem($acl);
			return;				
		}
	}

	/**
	 * promotes a user to project member
	 * @param User $user
	 * @param Project $project
	 */
	public function promoteUserToProjectMember($user, $project) {
		$aci = $this->repository->getAccessControlItem($project->projectId, $user->userId);
		if ($aci == null) {
			$acl = new AccessControlItem();
			$acl->userId = $user->userId;
			$acl->roleId = RoleEnum::Member;
			$acl->projectId = $project->projectId;
			$acl = $this->repository->createAccessControlItem($acl);
			return;
		}
	}

	/**
	 * checks if user is admin in the system
	 * @param User $user
	 * @throws SecurityException
	 */
	public function checkAdminAuthorization($user) {
		$this->checkUserIsConfirmed($user);
		if (!$this->repository->checkAccessControl($user->userId, RoleEnum::Admin, 0)) {
			throw new UnauthorizedException("You are not authorized because you are not an administrator");
		}
	}

	/**
	 * checks if user is lead of the project
	 * @param User $user
	 * @param Project $project
	 * @throws SecurityException
	 */
	public function checkLeadAuthorization($user, $project) {
		$this->checkUserIsConfirmed($user);
		if (!$this->repository->checkAccessControl($user->userId, RoleEnum::Lead, $project->projectId)) {
			throw new UnauthorizedException("You are not authorized because you are not a project lead of this project");
		}
	}

	/**
	 * checks if User is lead or member of the project
	 * @param User $user
	 * @param Project $project
	 * @throws SecurityException
	 */
	public function checkLeadOrMemberAuthorization($user, $project) {
		if ($project == null) {
			throw new ParameterException("project does not exist");
		}
		$this->checkUserIsConfirmed($user);
		if (
				(!$this->repository->checkAccessControl($user->userId, RoleEnum::Lead, $project->projectId))
				&& (!$this->repository->checkAccessControl($user->userId, RoleEnum::Member, $project->projectId))
		) {
			throw new UnauthorizedException("You are not authorized because you are neither a project lead nor a member of this project");
		}
	}
	
	/**
	 * checks if a user is project lead or member in a project
	 * @param User $user
	 * @param Project $project
	 */
	public function isProjectLeadOrMember($user, $project) {
		if ($user == null) {
			throw new ParameterException("user is null");
		}
		if ($project == null) {
			throw new ParameterException("project is null");
		}
		$is = $user->isConfirmed && (
				$this->repository->checkAccessControl($user->userId, RoleEnum::Lead, $project->projectId)
				||
				$this->repository->checkAccessControl($user->userId, RoleEnum::Member, $project->projectId)
				);
		return $is;
	}

	/**
	 * checks if user is member of the project
	 * @param User $user
	 * @param Project $project
	 * @throws SecurityException
	 */
	public function checkMemberAuthorization($user, $project) {
		$this->checkUserIsConfirmed($user);
		if (!$this->repository->checkAccessControl($user->userId, RoleEnum::Member, $project->projectId)) {
			throw new UnauthorizedException("You are not authorized because you are not a member of this project");
		}
	}

	private function checkUserIsConfirmed($user) {
		if (!$user->isConfirmed) {
			throw new UnauthorizedException("user is not confirmed yet");
		}
	}

	/**
	 * confirms a user with its confirmationKey
	 * @param User $user
	 * @param string $confirmationKey
	 * @return User
	 */
	public function confirmUser($user, $confirmationKey) {
		if ($user->confirmationKey != $confirmationKey) {
			throw new ParameterException("The confirmation key is not correct");
		}
		$this->repository->confirmUser($user);
		$user->isConfirmed = 1;
		return $user;
	}

	/**
	 * gets user by name
	 * @param string $name
	 * @return User
	 */
	public function getUserByName($name) {
		if ($name == "") {
			throw new ParameterException("name is empty");
		}
		$model = $this->repository->getUserByName($name);
		if ($model == null) {
			throw new NotFoundException($name);
		}
		return $model;
	}

}

?>