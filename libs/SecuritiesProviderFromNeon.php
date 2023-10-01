<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\NetteSecurity;

use Nette\Utils\Validators;
use Nette\Security\IAuthenticator;
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
		$this->table = AuthenticationsFromNeon::fromFile($fileName);
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
					return new SimpleIdentity($x->id, null, [
						'login' => $x->login,
						'displayName' => $x->login
						]);
				}
				else {
					throw new AuthenticationException('Invalid password.', IAuthenticator::INVALID_CREDENTIAL);
				}
			}
		}
		throw new AuthenticationException("User '$login' not found.", IAuthenticator::IDENTITY_NOT_FOUND);
	}



	private function verifyPassword(string $password, string $passOrHash): bool
	{
		return $password === $passOrHash;
	}

}
