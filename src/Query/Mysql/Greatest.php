<?php

namespace NetBull\CoreBundle\Query\Mysql;

use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\AST\Functions;
use Doctrine\ORM\Query\TokenType;

class Greatest extends Functions\FunctionNode
{
    protected $firstExpression, $secondExpression;

    /**
     * @param SqlWalker $sqlWalker
     * @return string
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        return sprintf(
            "GREATEST(%s, %s)",
            $this->firstExpression->dispatch($sqlWalker),
            $this->secondExpression->dispatch($sqlWalker)
        );
    }

    /**
     * @param Parser $parser
     * @return void
     * @throws QueryException
     */
    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER); // (2)
        $parser->match(TokenType::T_OPEN_PARENTHESIS); // (3)
        $this->firstExpression = $parser->ArithmeticPrimary(); // (4)
        $parser->match(TokenType::T_COMMA); // (5)
        $this->secondExpression = $parser->ArithmeticPrimary(); // (6)
        $parser->match(TokenType::T_CLOSE_PARENTHESIS); // (3)
    }
}
