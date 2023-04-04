<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\NetteSecurity\UI;

use Latte;
use Nette\Utils\Strings;
use LogicException;


class AclMacroSet extends Latte\Macros\MacroSet
{

	static function install(Latte\Compiler $compiler): void {
		$me = new self($compiler);
		$me->addMacro('allowed', [$me, 'macroAllowed'], '}');
	}



	/**
	 * n:allowed="Page:create:any"
	 * n:allowed="Page:edit:any | Page($entity->resourceId):edit:(author = $sessionUser)"
	 * n:allowed="Page($entity->resourceId):edit:(author = $sessionUser and published = Null)"
	 */
	function macroAllowed(Latte\MacroNode $node, Latte\PhpWriter $writer): string
	{
		//~ $expression = Strings::replace(strtr($node->args, ['|' => '||', '&' => '&&', ]), '~([\w]+[\w\-\:]*[\w])(\(\$[\w\-\>]+\))?~', function(array $xs) : string {
		//~ $expression = Strings::replace(strtr($node->args, ['|' => '||', '&' => '&&', ]), '~([\w]+)(\(\s[\w\-\>]+\))?\:([^:]+)\:(.+)~', function(array $xs) : string {
		$expression = Strings::replace(strtr($node->args, ['|' => '||', '&' => '&&', ]), '~([\w]+)(\(\$[\w\-\>]+\))?\:([^:]+\:[^)|]+\)?)~', function(array $xs) : string {
			$xs[3] = trim($xs[3]);
			if ($xs[2]) {
				$resource = trim($xs[2], '()');
				return "\$user->isAllowed($resource, '{$xs[1]}:{$xs[3]}') ";
			}
			else {
				return "\$user->isAllowed(Null, '{$xs[1]}:{$xs[3]}') ";
			}
		});
		return $writer->write("if ({$expression}) %node.line  {");
	}

}
