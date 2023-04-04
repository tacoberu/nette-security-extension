<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\NetteSecurity;

use PHPUnit\Framework\TestCase;
use LogicException;


class PredicateBuilderTest extends TestCase
{

	function testSample1()
	{
		$sessionUser = new User;

		//~ $x = PredicateBuilder::from('author = $sessionUser', new ValueBank($sessionUser));
		//~ dump($x);
	}


}
