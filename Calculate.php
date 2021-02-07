<?php
namespace MathPaser;

class Calculate
{
    // 运算符优先级
    protected $operations = [
        '!'  => 6,
        '*'  => 5,
        '/'  => 5,
        '+'  => 4,
        '-'  => 4,
        '>'  => 3,
        '>=' => 3,
        '<'  => 3,
        '<=' => 3,
        '==' => 3,
        '!=' => 3,
        '&'  => 2,
        '|'  => 1,
        '?'   => 0
    ];

    protected $operationChars = ['|','&','=','>','<','-','+','*', '/', '!', '?', ':'];

    public function compareOperationPriority($op1, $op2)
    {
        return $this->operations[$op1] > $this->operations[$op2];
    }

    public function getAllowOperations()
    {
        return array_keys($this->operations);
    }

    public function getAllowOperationChars()
    {
        return $this->operationChars;
    }

    public function isAllowOperation($op)
    {
        if (empty($op)) {
            return false;
        }
        return isset($this->operations[$op]);
    }
    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function calc($op, $param1, $param2 = 0, $param3 = 0)
    {
        switch ($op) {
            case '+':
                return $param1 + $param2;
            case '-':
                return $param1 * (-1);
            case '*':
                return $param1 * $param2;
            case '/':
                return $param2 / $param1;
            case '|':
                return $param1 | $param2;
            case '&':
                return $param1 & $param2;
            case '!':
                return !$param1 ? 1 : 0;
            case '>':
                return $param2>$param1 ? 1 : 0;
            case '>=':
                return $param2>=$param1 ? 1 : 0;
            case '<':
                return $param2<$param1 ? 1 : 0;
            case '<=':
                return $param2<=$param1 ? 1 : 0;
            case '==':
                return $param1==$param2 ? 1 : 0;
            case '!=':
                return $param1!=$param2 ? 1 : 0;
            case '?':
                if (is_numeric($param1)) {
                    $param1 = intval($param1)==0 ? false : true;
                }
                return $param1 ? $param2 : $param3;
        }
    }
}
