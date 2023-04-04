<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\NetteSecurity;

use Nette;
use Nette\Utils\Validators;


/**
 * Architektura, kdy neřešíme role, ale jen oprávnění. Každý uživatel má sumu oprávnění na každý zdroj. Pokud nemá
 * tak nemá přístup. U každého oprávnění evidujeme Zdroj, Operaci, a Podmínku, která musí být splněna.
 */
class User extends Nette\Security\User
{

	const GhostId = 0;


	/**
	 * @param ResourceId $resource
	 * @param string Sign:in:any
	 */
	function isAllowed($resource = Nette\Security\IAuthorizator::ALL, $privilege = Nette\Security\IAuthorizator::ALL) : bool
	{
		$this->getAuthorizator()->injectUser($this);
		return $this->getAuthorizator()->isAllowed(Null, $resource, $privilege);
	}



	function getId()
	{
		if ( ! $this->loggedIn) {
			return self::GhostId;
		}
		return parent::getId();
	}

}
