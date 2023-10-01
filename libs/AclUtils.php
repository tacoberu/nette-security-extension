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
	 * - @forbiddenRedirect(Post:remove:any)
	 */
	function checkRequirements($element) : void
	{
		switch (True) {
			case $element instanceof UI\ComponentReflection:
				// @TODO
				return;

			case $element instanceof UI\MethodReflection:
				// Tuto akci nevalidovat
				if (self::hasAllowedSkip($element)) {
					return;
				}
				// Když používáme action i render zároveň, tak nebudeme dávat allowd nad obě metody, protože přeci musí být stejný.
				if (substr($element->name, 0, 6) === 'render' && method_exists($element->class, 'action' . substr($element->name, 6))) {
					return;
				}

				if ($this->allowPermissions($this->resolvePermissions($element))) {
					return;
				}

				// Určíme, že při neoprávněném přístupu se to má přesměrovat
				//if ($url = self::parseAnnotation(new \ReflectionClass($element->class), 'forbiddenRedirect')) {
				//	$url = reset($url);
				//	$this->redirect($url);
				//}

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



	private function resolvePermissions($element): array
	{
		$permissions = self::parseAnnotation($element, 'allowed');

		if (method_exists($element, 'getAttributes')) {
			foreach ($element->getAttributes(\Taco\NetteSecurity\Allowed::class) as $attr) {
				$permissions[] = (string) $attr->newInstance();
			}
		}

		$permissions = array_unique($permissions);
		if (count($permissions)) {
			return $permissions;
		}
		if ($permissions = $this->buildGeneratedPermissionsByAction()) {
			return $permissions;
		}
		return [];
	}



	private function allowPermissions(array $permissions): bool
	{
		foreach ($permissions as $perm) {
			$resourceType = self::getResourceType($perm);
			if ($this->user->isAllowed($this->getResourceOfType($resourceType), $perm)) {
				return True;
			}
		}
		return False;
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
	 * @param  \ReflectionClass|\ReflectionMethod  $ref
	 */
	private static function hasAllowedSkip(\Reflector $ref): bool
	{
		// Pomocí php8 anotace
		if (method_exists($ref, 'getAttributes')) {
			foreach ($ref->getAttributes(\Taco\NetteSecurity\AllowedSkip::class) as $attr) {
				return True;
			}
		}
		// Pomocí old style
		return count(self::parseAnnotation($ref, 'allowed-skip'));
	}



	/**
	 * Returns an annotation value.
	 * @param  \ReflectionClass|\ReflectionMethod  $ref
	 * @return array<string>|Null List of permission, aka: ["AclPermissions:show:any"]
	 */
	private static function parseAnnotation(\Reflector $ref, string $name): array
	{
		$res = [];
		if (preg_match_all('#[\s*]@' . preg_quote($name, '#') . '(?:\(\s*(.*)\s*\)|\s|$)#', (string) $ref->getDocComment(), $m)) {
			foreach ($m[1] as $s) {
				$res[] = $s;
			}
		}
		return $res;
	}



	private static function getResourceType(string $src) : string
	{
		list($x, ) = explode(':', $src, 2);
		return $x;
	}
}
