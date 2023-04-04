<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\NetteSecurity;

use Nette\Neon;
use Nette\Security\Authenticator;
use Nette\Security\AuthenticationException;
use Nette\Security\IIdentity;
use Nette\Security\SimpleIdentity;
use RuntimeException;


/**
 * Přihlašovací údaje, uživatelské účty i práva získává z Neon souboru.
 */
class SecuritiesProviderFromNeon implements PermissionsProvider, IAuthenticator
{

	private $table = [];


	function __construct(string $fileName)
	{
		self::requireFileExists($fileName);
		$this->table = self::build((new Neon\Decoder)->decode(file_get_contents($fileName)));
	}



	function getAllPermissions(User $user) : array
	{
		if (!isset($this->table[$user->getId()])) {
			throw new AuthenticationException("Account with id({$user->getId()}) is not found.");
		}
		return $this->table[$user->getId()]->permissions;
	}



	/**
	 * Performs an authentication against e.g. database.
	 * and returns IIdentity on success or throws AuthenticationException
	 * @throws AuthenticationException
	 */
	function authenticate(array $credentials)
	{
		list($login, $password) = $credentials;

		Validators::assert($login, 'string:1..64');
		Validators::assert($password, 'string:1..64');

		foreach ($this->table as $x) {
			if (strcasecmp($x->login, $login) === 0) {
				if ($this->verifyPassword($password, $x->password)) {
					return new SimpleIdentity($x->id, null, ['realname' => $x->login]);
				}
				else {
					throw new AuthenticationException('Invalid password.', Authenticator::INVALID_CREDENTIAL);
				}
			}
		}
		throw new AuthenticationException("User '$login' not found.", Authenticator::IDENTITY_NOT_FOUND);
	}



	private function verifyPassword(string $password, string $passOrHash): bool
	{
		return $password === $passOrHash;
	}



	private static function build(array $src) : array
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
			'login' => $src['login'],
			'password' => $src['password'],
			'permissions' => self::buildPermissions($src['permissions']),
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
