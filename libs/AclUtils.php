<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\NetteSecurity\UI;

use Taco\NetteSecurity\PermissionAuthorizator;
use Latte;
use Nette;
use Nette\Application\UI;


trait AclUtils
{

	/**
	 * Checks authorization.
	 *
	 * Support annotations:
	 * - @allowed-skip
	 * - @allowed(Post:remove:any)
	 * - @allowed(Post:remove:(author = $sessionUser and editor = Null))
	 */
	function checkRequirements($element) : void
	{
		switch (True) {
			case $element instanceof UI\ComponentReflection:
				// @TODO
				return;

			case $element instanceof UI\MethodReflection:
				if (self::parseAnnotation($element, 'allowed-skip')) {
					return;
				}
				if ($permissions = self::parseAnnotation($element, 'allowed')) {
					$this->assertPermissions($permissions);
					return;
				}
				if ($permissions = $this->buildGeneratedPermissionsByAction()) {
					$this->assertPermissions($permissions);
					return;
				}
				throw new Nette\Application\ForbiddenRequestException;

			default:
				throw new LogicException("oops.");
		}
	}



	/**
	 * Ptáme se na práva ke konkrétní instanci typy. Například pro `Post:edit:(author = $sessionUser)` potřebujeme vědět
	 * zda a jakého má Post autora.
	 * Dále ale můžeme v rámci jednoho presenteru poskytovat různé instance různých typů (jedna instance na typ).
	 */
	function getResourceOfType(string $resourceType) : ?ResourceId
	{
		return Null;
	}



	private function assertPermissions(array $permissions)
	{
		foreach ($permissions as $perm) {
			$resourceType = self::getResourceType($perm);
			if ($this->user->isAllowed($this->getResourceOfType($resourceType), $perm)) {
				return;
			}
		}
		throw new Nette\Application\ForbiddenRequestException;
	}



	/**
	 * @return array<string> Like 'Dashboard:default:any'.
	 */
	private function buildGeneratedPermissionsByAction() : array
	{
		return [
			ucfirst($this->name) . ':' . $this->action . ':any',
		];
	}



	/**
	 * Returns an annotation value.
	 * @param  \ReflectionClass|\ReflectionMethod  $ref
	 */
	private static function parseAnnotation(\Reflector $ref, string $name): ?array
	{
		if (!preg_match_all('#[\s*]@' . preg_quote($name, '#') . '(?:\(\s*(.*)\s*\)|\s|$)#', (string) $ref->getDocComment(), $m)) {
			return null;
		}
		$res = [];
		foreach ($m[1] as $s) {
			$res[] = $s;
		}
		return $res;
	}



	private static function getResourceType(string $src) : string
	{
		list($x, ) = explode(':', $src, 2);
		return $x;
	}
}
