<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\NetteSecurity;

use Nette\Neon;
use Nette\Utils\Validators;
use RuntimeException;


class AuthenticationsFromNeon
{

	static function fromFile(string $fileName)
	{
		self::requireFileExists($fileName);
		return self::build((new Neon\Decoder)->decode(file_get_contents($fileName)));
	}



	static function build(array $src) : array
	{
		$ret = [];
		foreach ($src as $x) {
			$x = self::buildAccount($x);
			$ret[$x->id] = $x;
		}
		return $ret;
	}



	private static function buildAccount(array $src)
	{
		return (object) [
			'id' => isset($src['id']) ? $src['id'] : $src['login'],
			'displayName' => isset($src['displayName']) ? $src['displayName'] : $src['login'],
			'login' => $src['login'],
			'password' => $src['password'],
			'permissions' => self::buildPermissions($src['permissions'] ?? []),
		];
	}



	private static function buildPermissions($src) : array
	{
		$ret = [];
		foreach ($src as $code => $desc) {
			$ret[$code] = self::buildPermissionOne($code, $desc);
		}
		return $ret;
	}



	private static function buildPermissionOne($code, $descr)
	{
		return (string) $descr;
	}



	private static function requireFileExists(string $fn)
	{
		if ( ! file_exists($fn)) {
			throw new RuntimeException("File '{$fn}' is not found.");
		}
		if ( ! is_readable($fn)) {
			throw new RuntimeException("File '{$fn}' is not readable.");
		}
	}
}
