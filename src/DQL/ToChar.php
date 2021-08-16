<?php

namespace App\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\SqlWalker;

class ToChar extends FunctionNode
{
	public $timestamp = null;

	public $pattern = null;

	/**
	 * This tells Doctrine's Lexer how to parse the expression:
	 *
	 * @param Parser $parser
	 * @throws QueryException
	 */
	public function parse(Parser $parser)
	{
		$parser->match(Lexer::T_IDENTIFIER);
		$parser->match(Lexer::T_OPEN_PARENTHESIS);
		$this->timestamp = $parser->ArithmeticPrimary();
		$parser->match(Lexer::T_COMMA);
		$this->pattern = $parser->ArithmeticPrimary(); // I'm not sure about `ArithmeticPrimary()` but it works. Post a comment, if you know more details!
		$parser->match(Lexer::T_CLOSE_PARENTHESIS);
	}

	/**
	 * This tells Doctrine how to create SQL from the expression - namely by (basically) keeping it as is:
	 *
	 * @param SqlWalker $sqlWalker
	 * @return string
	 */
	public function getSql(SqlWalker $sqlWalker)
	{
		return 'to_char('.$this->timestamp->dispatch($sqlWalker) . ', ' . $this->pattern->dispatch($sqlWalker) . ')';
	}
}