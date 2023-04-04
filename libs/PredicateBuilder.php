<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\NetteSecurity;

use Taco\BNF\Parser;
use Taco\BNF\Token;
use Taco\BNF\Combinators\Whitechars;
use Taco\BNF\Combinators\Pattern;
use Taco\BNF\Combinators\Match;
use Taco\BNF\Combinators\Sequence;
use Taco\BNF\Combinators\Variants;
use Taco\BNF\Combinators\OneOf;


class PredicateBuilder
{

	static function from(string $src, ValueBank $values) : Predicate
	{
		$parser = new Parser(self::getSchema());
		return self::buildPredicate($parser->parse($src), $values);
	}



	private static function getSchema()
	{
		$sep = new Whitechars(Null, False);
		$bool = new Pattern('AND/OR', ['~\s+AND\s+~i', '~\s+OR\s+~i']);
		//~ $not = new Pattern('NOT', ['~\s*NOT\s+~i']);
		$op = new OneOf('operator (=, !=, IS, LIKE, IN, etc)', [
				new Pattern(Null, [
					'~NOT\s+LIKE~i',
					'~NOT\s+IN~i',
				]),
				new Match(Null, [
					//~ 'LIKE',
					//~ 'IN',
					'!=',
					'>=',
					'<=',
					'=',
					'<',
					'>',
				]),
			]);

		$expr = new Sequence('expr (col = :param)', [
			//~ $not->setOptional(),
			new Pattern('resource.column', ['~[a-z][a-zA-Z0-9]*~']),
			$sep,
			$op,
			$sep,
			new Match('value', [
				'Null',
				'True',
				'False',
				'$sessionUser',
			]),
			//~ new Pattern('param (:name, :arg1, etc)', ['~\:[a-z][a-z0-9]*~']),
		]);

		// A series of expressions separated by an AND / OR conjunction.
		return new Variants('chain-of-expressions', [
			$expr,
			$bool,
		], [$expr], [$expr]);
	}



	private static function buildPredicate(Token $ast, $values) : Predicate
	{
		switch ($ast->getName()) {
			case 'chain-of-expressions':
				$chain = clone $ast;
				if (count($chain->content) < 2) {
					return self::buildPredicate($chain->content[0], $values);
				}
				$left = self::buildPredicate(array_shift($chain->content), $values);
				$cond = array_shift($chain->content);
				if (count($chain->content) <= 1) {
					$chain = $chain->content[0];
				}
				$right = self::buildPredicate($chain, $values);
				switch (strtolower(trim($cond->content))) {
					case 'and':
						return new PredicateAnd($left, $right);

					default:
						throw new LogicException("oops.");
				}

			case 'expr (col = :param)':
				return new PredicateOperation($ast->content[0], $ast->content[1], $values->get($ast->content[2]));

			default:
				throw new LogicException("Illegal token '{$ast->getName()}'.");
		}
	}

}



/**
 * expr AND expr
 */
class PredicateAnd implements Predicate
{
	private $first;
	private $next;

	function __construct($first, $next)
	{
		$this->first = $first;
		$this->next = $next;
	}


	function resultFor($resource) : bool
	{
		if ( ! $this->first->resultFor($resource)) {
			return False;
		}
		return $this->next->resultFor($resource);
	}
}



/**
 * column != 5
 */
class PredicateOperation implements Predicate
{
	private $column;
	private $operation;
	private $value;

	function __construct(string $column, string $operation, $value)
	{
		$this->column = $column;
		$this->operation = $operation;
		$this->value = $value;
	}



	function resultFor($resource) : bool
	{
		switch ($this->operation) {
			case '=':
				return $resource->getResourceAttr($this->column) === $this->value;
			case '!=':
				return $resource->getResourceAttr($this->column) !== $this->value;
			case '>=':
				return $resource->getResourceAttr($this->column) >= $this->value;
			case '<=':
				return $resource->getResourceAttr($this->column) <= $this->value;
			case '>':
				return $resource->getResourceAttr($this->column) > $this->value;
			case '<':
				return $resource->getResourceAttr($this->column) < $this->value;
			default:
				throw new \LogicException("Unsupported operation: '{$this->operation}'.");
		}
	}
}
