<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\NetteSecurity;

use Nette;
use Nette\Security\User as SecurityUser;
use LogicException;


/**
 * Záznam oprávnění obsahující kromě klíče hlavně description. Klíč může být složitější konstrukce.
 */
class Permission
{
	const ConditionAny = 'any';

	private string $resource;
	private string $condition;


	/**
	 * <entity>:<operation>:<condition>
	 * Page:show:any
	 * Post:edit:(author = $sessionUser)
	 * Post:remove:(author = $sessionUser and published = Null)
	 */
	static function create(string $code)
	{
		list($resource, $_operation, $condition) = explode(':', $code, 3);
		return new self($resource, $condition);
	}



	function __construct(string $resource, string $condition)
	{
		$this->resource = $resource;
		$this->condition = $condition;
	}



	function match(?ResourceId $resource, SecurityUser $user) : bool
	{
		if (empty($resource) && $this->condition === self::ConditionAny) {
			return True;
		}
		if ($resource && $resource->getResourceType() !== $this->resource) {
			throw new LogicException("Unconsistenci resource: {$resource->getResourceType()} !== {$this->resource}.");
		}
		if ($this->condition === self::ConditionAny) {
			return True;
		}

		return PredicateBuilder::from(trim($this->condition, '()'), new ValueBank($user))
			->resultFor($resource);
	}

}



/**
 * For example: `new ResourceId('Post', '42', ['author' => 5])`
 */
class ResourceId implements Nette\Security\IResource
{
	private string $type;
	private string $id;
	private array $attrs = [];

	function __construct(string $type, string $id, array $attrs = [])
	{
		$this->type = $type;
		$this->id = $id;
		$this->attrs = $attrs;
	}



	function getResourceId() : string
	{
		return $this->id;
	}



	function getResourceType() : string
	{
		return $this->type;
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
