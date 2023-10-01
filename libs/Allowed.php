<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\NetteSecurity;

use Attribute;


/**
 * Uživatel musí mít toto právo. Je-li u metody více atributů musí mít alespoň jedno.
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class Allowed
{
	private $perm;

	/**
	 * For example:
	 *  #[Allowed('Post:show:any')]
	 *  #[Allowed('Post:show:(author = $sessionUser and editor = Null)')]
	 */
	function __construct(string $perm)
	{
		$this->perm = $perm;
	}



	function __toString()
	{
		return $this->perm;
	}

}
