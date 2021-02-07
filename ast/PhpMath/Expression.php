<?php
namespace PhpMath;

use \Exception;
use \SplStack;
use PhpMath\Ast;

class Expression
{
    // 参数集合
    protected $paramsSet = [];
    // 清洗后的表达式字符串
    protected $exp = '';
    // 参数标识
    protected $paramFlag = 'T';
    protected $calculate = null;

    /**
     * 表达式计算
     * @param string $expression
     * @param array $params
     * @param string $paramFlag
     * @throws Exception
     */
    public function __construct($expression = '', $params = [], $paramFlag = 'T')
    {
//        $this->calculate = new Calculate();
        $this->paramFlag = $paramFlag;

        !empty($params) && $this->setParams($params);
        strlen($expression) > 0 && $this->setExpression($expression);
    }

    /**
     * 设置参数
     * @param array $params
     */
    protected function setParams(array $params)
    {
        // 覆盖之前的参数
        $this->paramsSet && $this->paramsSet = [];
        foreach ($params as $item) {
            $this->paramsSet[trim($item)] = true;
        }
    }

    /**
     * 设置表达式
     * @param $exp
     * @throws Exception
     */
    public function setExpression($exp)
    {
        $this->exp = $this->purgeExpressionStr($exp);
    }


    public function getVal($params = [])
    {
        // 设置参数
        !empty($params) && $this->setParams($params);

        if (!$this->exp) {
            throw new Exception("运算表达式未设置");
        }
        // 转中缀表达式, 并替换参数
        $expArray = $this->parseExp($this->exp);
        // 转抽象语法树
        if (!$expArray) {
            throw new Exception("表达式错误");
        }

        $ast = new Ast(implode('', $expArray));

        return $ast->getResult();
    }

    /**
     * 表达式清洗
     * @param string $exp
     * @return string
     * @throws Exception
     */
    public function purgeExpressionStr($exp)
    {
        $search = [" ", "　", "\n", "\r", "\t", "（", "）", '！', '？', '：'];
        $replace = ['', '', '', '', '', '(', ')', '!', '?', ':'];
        $exp = str_replace($search, $replace, $exp);

        // 存在未知字符
        $allowChars = array_merge(['(',')', $this->paramFlag], $this->getAllowOperationChars());
        $allowChars = implode('\\', $allowChars);
        $pattern = '/[^(0-9\.\\'.$allowChars.')]/';
        if (preg_match($pattern, $exp) > 0) {
            throw new Exception('运算表达式有误, 存在非法字符');
        }

        // 括号是否匹配
        $stack = new SplStack();
        $len = strlen($exp);
        for ($i=0; $i<$len; $i++) {
            if ($exp[$i] == ')') {
                if ($stack->isEmpty() || $stack->pop()!='(') {
                    throw new Exception('运算表达式有误, 括号未闭合');
                }
            } elseif ($exp[$i] == '(') {
                $stack->push('(');
            }
        }
        if (!$stack->isEmpty()) {
            throw new Exception('运算表达式有误, 括号未闭合');
        }

        return $exp;
    }

    /**
     * 加载字符串为中缀表达式, 并替换参数为0|1
     * @param string $expStr
     * @return array
     */
    protected function parseExp($expStr)
    {
        $len = strlen($expStr);
        $expArr = [];
        for ($i=0; $i<$len; $i++) {
            if ($expStr[$i] == $this->paramFlag) {
                // 参数
                $item = $this->paramFlag;
                while (++$i<$len && is_numeric($expStr[$i])) {
                    $item .= $expStr[$i];
                }
                $i--;
                $expArr[] = $this->getParamReal($item);
            } elseif (is_numeric($expStr[$i])) {
                // 数字
                $numStr = '';
                while ($i<$len && (is_numeric($expStr[$i]) || $expStr[$i]=='.')) {
                    $numStr .= $expStr[$i] == '.' ? '.' : $expStr[$i];
                    $i++;
                }
                $i--;
                $expArr[] = floatval($numStr);
            } else {
                // 操作符
                if (in_array($expStr[$i], ['>','<','!','=']) && $i<$len-1 && $expStr[$i+1]=='=') {
                    $expArr[] = $expStr[$i] . $expStr[++$i];
                } else {
                    $expArr[] = $expStr[$i];
                }
            }
        }
        return $expArr;
    }


    protected function getParamReal($param)
    {
        return isset($this->paramsSet[$param]) ? 1 : 0;
    }

    protected function getAllowOperationChars()
    {
        return ['+', '-', '*', '/', '!', '|', '&', '%', '>', '<', '=', '?', ':'];
    }
}
