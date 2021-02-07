<?php

namespace PhpMath;
use PhpParser\Error;
use PhpParser\NodeDumper;
use PhpParser\ParserFactory;
use PhpParser\{Node, NodeTraverser, NodeVisitorAbstract};

class NodeVisitor extends NodeVisitorAbstract
{
    public function __construct()
    {
    }
    protected function isOpNumber($node)
    {
        return $node instanceof Node\Scalar\DNumber || $node instanceof Node\Scalar\LNumber;
    }
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Expr\BinaryOp) {
            // 二元运算符
//            $this->rpnList[] = $node->getOperatorSigil();
            if ($this->isOpNumber($node->left) && $this->isOpNumber($node->right)) {
                switch ($node->getType()) {
                    case 'Expr_BinaryOp_Div':
                        return new \PhpParser\Node\Scalar\DNumber($node->left->value/$node->right->value);
                    case 'Expr_BinaryOp_Mul':
                        return new \PhpParser\Node\Scalar\DNumber($node->left->value*$node->right->value);
                    case 'Expr_BinaryOp_Plus':
                        return new \PhpParser\Node\Scalar\DNumber($node->left->value+$node->right->value);
                    case 'Expr_BinaryOp_Minus':
                        return new \PhpParser\Node\Scalar\DNumber($node->left->value-$node->right->value);
                    case 'Expr_BinaryOp_BitwiseAnd':
                        $value = (intval($node->left->value)!==0 && intval($node->right->value)!==0) ? 1 : 0;
                        return new \PhpParser\Node\Scalar\DNumber($value);
                    case 'Expr_BinaryOp_BitwiseOr':
                        $value = (intval($node->left->value)!==0 || intval($node->right->value)!==0) ? 1 : 0;
                        return new \PhpParser\Node\Scalar\DNumber($value);
                    case 'Expr_BinaryOp_Greater':
                        $value = $node->left->value > $node->right->value ? 1 : 0;
                        return new \PhpParser\Node\Scalar\DNumber($value);
                    case 'Expr_BinaryOp_GreaterOrEqual':
                        $value = $node->left->value >= $node->right->value ? 1 : 0;
                        return new \PhpParser\Node\Scalar\DNumber($value);
                    case 'Expr_BinaryOp_Smaller':
                        $value = $node->left->value < $node->right->value ? 1 : 0;
                        return new \PhpParser\Node\Scalar\DNumber($value);
                    case 'Expr_BinaryOp_SmallerOrEqual':
                        $value = $node->left->value <= $node->right->value ? 1 : 0;
                        return new \PhpParser\Node\Scalar\DNumber($value);
                    case 'Expr_BinaryOp_Equal':
                        $value = $node->left->value == $node->right->value ? 1 : 0;
                        return new \PhpParser\Node\Scalar\DNumber($value);
                    case 'Expr_BinaryOp_NotEqual':
                        $value = $node->left->value != $node->right->value ? 1 : 0;
                        return new \PhpParser\Node\Scalar\DNumber($value);
                    default:
//                        var_dump($node);
                }
            }
        }
        if ($node instanceof Node\Expr\Ternary) {
            // 三目运算
//            $this->rpnList[] = $node->getType();
            if ($this->isOpNumber($node->cond) && $this->isOpNumber($node->if) && $this->isOpNumber($node->else)) {
                return intval($node->cond->value)!=0 ? $node->if : $node->else;
            }
        }
        if ($node instanceof Node\Expr\UnaryMinus && $this->isOpNumber($node->expr)) {
            // 负数
            return new \PhpParser\Node\Scalar\DNumber(-1 * $node->expr->value);
        }
        if ($node instanceof Node\Expr\BooleanNot && $this->isOpNumber($node->expr)) {
            // !运算
            $val = !$node->expr ? 1 : 0;
            return new \PhpParser\Node\Scalar\LNumber($val);
        }
    }
}