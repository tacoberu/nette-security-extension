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
 *
 * Na oprávnění se ptáme uživatele.
 */
class User extends Nette\Security\User
{

	const GhostId = 9;

	const GhostName = 'ghost';

	const GhostRole = 'ghost';


	/**
	 * @param ResourceId $resource
	 * @param string Sign:in:any
	 */
	function isAllowed($resource = Nette\Security\IAuthorizator::ALL, $privilege = Nette\Security\IAuthorizator::ALL) : bool
	{
		// Když v šabloně předám Null, tak to nerozliší jako null.
		if ($resource == 'Null') {
			$resource = Null;
		}
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



	function getDisplayName()
	{
		if ( ! $this->loggedIn) {
			return self::GhostName;
		}
		if ( ! isset($this->identity->data['displayName'])) {
			return self::GhostName;
		}
		return $this->identity->data['displayName'];
	}



	function getResourceId() : ?ResourceId
	{
		return new ResourceId('Account', $this->getId(), [
			'id' => $this->getId(),
		]);
	}

}
