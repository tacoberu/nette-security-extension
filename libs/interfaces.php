<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\NetteSecurity;


/**
 * Poskytuje seznam všech oprávnění pro konkrétního uživatele.
 */
interface PermissionsProvider
{

	/**
	 * Seznam oprávnění ve formátu
	 * klíč: Sign:in:any
	 * value: Přihlásitelý účet.
	 *
	 * @return array<string, string>
	 */
	function getAllPermissions(User $user) : array;

}



/**
 * Zpracuje podmínku a vrátí zda ano/ne.
 */
interface Predicate
{
	function resultFor($resource) : bool;
}
