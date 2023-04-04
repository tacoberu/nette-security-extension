<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\NetteSecurity;

use Nette;
use Nette\Security\User as SecurityUser;


/**
 * Záznam oprávnění obsahující kromě klíče hlavně description. Klíč může být složitější konstrukce.
 */
class Permission
{
	private string $resource;
	private string $condition;


	static function create(string $code)
	{
		list($resource, $operation, $condition) = explode(':', $code, 3);
		return new self($resource, $condition);
	}



	function __construct(string $resource, string $condition)
	{
		$this->resource = $resource;
		$this->condition = $condition;
	}



	function match(?ResourceId $resource, SecurityUser $user) : bool
	{
		if (empty($resource) && $this->condition === 'any') {
			return True;
		}
		if ($resource && $resource->getResourceId() !== $this->resource) {
			throw new \LogicException("Unconsistenci resource: {$resource->getResourceId()} !== {$this->resource}.");
		}
		if ($this->condition === 'any') {
			return True;
		}

		return PredicateBuilder::from(trim($this->condition, '()'), new ValueBank($user))
			->resultFor($resource);
	}

}



/**
 * For example: `new ResourceId('Post', ['author' => 5])`
 */
class ResourceId
{
	private string $id;
	private array $attrs = [];

	function __construct($id, array $attrs = [])
	{
		$this->id = $id;
		$this->attrs = $attrs;
	}



	function getResourceId()
	{
		return $this->id;
	}



	function getResourceAttr(string $name)
	{
		return $this->attrs[$name] ?? Null;
	}
}



class ValueBank
{
	private $sessionUser;

	function __construct(User $user)
	{
		$this->sessionUser = $user;
	}



	function get(string $name)
	{
		switch ($name) {
			case '$sessionUser':
				return $this->sessionUser->getId();
			case 'Null':
				return Null;
			case 'True':
				return True;
			case 'False':
				return False;
			default:
				return $name;
		}
	}
}
