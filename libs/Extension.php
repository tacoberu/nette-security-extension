<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\NetteSecurity;

use Nette;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\Schema\Expect;
use Nette\Security\IAuthenticator;
use Latte;
use LogicException;
use Tracy\Bar;


/**
 * extensions:
 *     secur: Taco\NetteSecurity\Extension
 *
 * @author Martin Takáč <martin@takac.name>
 */
class Extension extends Nette\DI\CompilerExtension
{

	/** @var bool */
	private $debugMode;


	function __construct($debugMode = null)
	{
		$this->debugMode = $debugMode;
	}



	function getConfigSchema(): Nette\Schema\Schema
	{
		return Expect::structure([
			'injectToLatte' => Expect::bool()->default(True),
		]);
	}



	function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		if ($this->debugMode === null) {
			$this->debugMode = $builder->parameters['debugMode'];
		}

		$this->compiler->loadDefinitionsFromConfig(
			$this->loadFromFile(__DIR__ . '/services.neon'),
		);

		$builder->addDefinition($this->prefix('panel'))
			->setClass(AclTracyPanel::class);
	}



	/**
	 * - přidáme do Latte {allowed} a n:allowed
	 */
	function beforeCompile()
	{
		if ($this->config->injectToLatte) {
			$builder = $this->getContainerBuilder();

			$latteFactory = $builder->getDefinitionByType(ILatteFactory::class);
			if (version_compare(Latte\Engine::VERSION, '3', '<')) {
				$latteFactory->getResultDefinition()
					->addSetup('?->onCompile[] = function ($engine) { ' . UI\AclMacroSet::class . '::install($engine->getCompiler()); }'
						, ['@self']);
			}
			else {
				throw new \LogicException('comming soon...');
			}
		}
	}



	function afterCompile($class)
	{
		$builder = $this->getContainerBuilder();
		$initialization = $this->getInitialization();

		if ($this->debugMode) {
			$initialization->addBody(
				// @phpstan-ignore-next-line
				$builder->formatPhp('?->addPanel(?);', [
					$builder->getDefinitionByType(Bar::class),
					new Nette\DI\Definitions\Statement(AclTracyPanel::class),
				])
			);
		}

		self::assertAuthenticator($builder);
	}



	private static function assertAuthenticator($builder)
	{
		$klass = $builder->getDefinition('authenticator')->getClass();
		if ( ! is_subclass_of($klass, IAuthenticator::class)
				|| ! is_subclass_of($klass, PermissionsProvider::class)
				) {
			throw new Nette\Utils\AssertionException('Je třeba mít nastavený authenticator implementující ' . implode(', ', [
					IAuthenticator::class
					, PermissionsProvider::class
					]), E_USER_ERROR);
		}
	}
}
