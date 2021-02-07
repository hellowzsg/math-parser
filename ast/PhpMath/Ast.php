<?php


namespace PhpMath;
use \Exception;
use PhpParser\Error;
use PhpParser\NodeDumper;
use PhpMath\NodeVisitor;
use PhpParser\ParserFactory;
use PhpParser\{Node, NodeTraverser, NodeVisitorAbstract};

class Ast
{
    protected $code = '';
    protected $ast = [];
    public function __construct(string $expression)
    {
//        echo $expression . "\n";
        $this->assignExpression($expression);
//        echo $this->code . "\n";
        $this->ast = $this->phpcodeToAst();
    }

    protected function assignExpression($exp)
    {
        $this->code = <<<CODE
<?php
$exp;
CODE;
    }

    protected function phpcodeToAst()
    {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        try {
            $ast = $parser->parse($this->code);
//            $this->dumpAst($ast);
//            echo "========\n";
//            exit();
        } catch (Error $error) {
            throw new Exception("Parse To Ast error: {$error->getMessage()}");
        }
        $traverser = new NodeTraverser();
        $vistor = new NodeVisitor();

        $traverser->addVisitor($vistor);
        $modifiedStmts = $traverser->traverse($ast);
//        $this->dumpAst($ast);
        return $modifiedStmts;
    }

    public function getResult()
    {
        if (isset($this->ast[0]) && property_exists($this->ast[0], 'expr')) {
            if (property_exists($this->ast[0]->expr, 'value')) {
                return $this->ast[0]->expr->value;
            }
        }
    }

    protected function dumpAst($stms)
    {
        $dumper = new NodeDumper;
        echo $dumper->dump($stms) . "\n";
    }
}