<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\NetteSecurity;

use Nette;
use Nette\Security\IAuthorizator;
use Nette\Security\User as SecurityUser;
use LogicException;


/**
 * Architektura, kdy neřešíme role, ale jen oprávnění. Každý uživatel má sumu oprávnění na každý zdroj. Pokud nemá
 * tak nemá přístup. U každého oprávnění evidujeme Zdroj, Operaci, a Podmínku, která musí být splněna.
 * @property-read String $name
 * @property-read String $password
 */
class Authorizator implements IAuthorizator
{

	use Nette\SmartObject;


	private PermissionsProvider $model;

	private SecurityUser $user;


	function __construct(PermissionsProvider $model)
	{
		$this->model = $model;
	}



	function injectUser(SecurityUser $user)
	{
		$this->user = $user;
	}



	function isAllowed($_role, $resource, $privilege) : bool
	{
		if ( ! $this->hasPermission($privilege)) {
			return False;
		}
		if ($resource) {
			return $this->getPermission($privilege)
				->match($resource, $this->user);
		}
		if (self::isAnyPrivilege($privilege)) {
			return True;
		}
		return False;
	}



	private function getAllPermissions() : array
	{
		return $this->model->getAllPermissions($this->user);
	}



	/**
	 * Má přihlášený uživatel povolené toto právo? Například právem: Permission:delete:any smí přihlášený uživatel komukoliv smazat právo.
	 */
	private function hasPermission(string $perm): bool
	{
		return isset($this->getAllPermissions()[$perm]);
	}



	private function getPermission(string $perm) : ?Permission
	{
		return isset($this->getAllPermissions()[$perm])
			? Permission::create($perm, $this->getAllPermissions()[$perm])
			: Null;
	}



	private static function isAnyPrivilege(string $priv) : bool
	{
		list(,, $cond) = explode(':', $priv, 3);
		return $cond === Permission::ConditionAny;
	}

}
