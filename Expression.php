<?php
namespace MathPaser;

use \SplStack;

class Expression
{
    // 参数集合
    protected $paramsSet = [];
    // 逆波兰表达式
    protected $prnExp = [];
    // 清洗后的表达式字符串
    protected $exp = '';
    // 参数标识
    protected $paramFlag = 'T';
    protected $calculate = null;

    /**
     * 表达式计算
     * examples:
     * expression='T1101 * 4 + T1102 * 6 + T1103 * (-2)', params=['T1101', 'T1102']  getVal return 8
     * expression='(!T401*T402 | T403 | T404) * (-6)', params=['T401', 'T402', 'T403', 'T404', 'T405']  getVal return -6
     * expression='3<=2?  999   :（T402? (1==1)： 22 ）', params=['T401', 'T402']  getVal return 1
     * @param string $expression
     * @param array $params
     * @param string $paramFlag
     * @throws Exception
     */
    public function __construct($expression = '', $params = [], $paramFlag = 'T')
    {
        $this->calculate = new Calculate();
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
        // 转后缀表达式
        $rpnExp = $this->expToRPN($expArray);
        // 计算表达式结果
        try {
            return $this->getValByRpn($rpnExp);
        } catch (RuntimeException $e) {
            throw new Exception('程序运行异常:'.$e->getMessage().', in File:'.$e->getFile().', on Line:'.$e->getLine());
        } catch (Exception $e) {
            throw new Exception('计算异常:'.$e->getMessage().', in File:'.$e->getFile().', on Line:'.$e->getLine());
        }
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
        $allowChars = array_merge(['(',')', $this->paramFlag], $this->calculate->getAllowOperationChars());
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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
                } elseif ($expStr[$i] == ':') {
                    continue;
                } else {//$expression = 'T5157*-0.5+T5158*-50';
                    // $expArr[] = $expStr[$i];
                    if ($expStr[$i] == '-' && ($i == 0 || in_array($expStr[$i-1], $this->calculate->getAllowOperationChars()))) {
                        $i++;
                        if ($expStr[$i] == $this->paramFlag) {
                            $item = $this->paramFlag;
                            while (++$i<$len && is_numeric($expStr[$i])) {
                                $item .= $expStr[$i];
                            }
                            $i--;
                            $expArr[] = (-1)*$this->getParamReal($item);
                        } else {
                            $numStr = '';
                            while ($i<$len && (is_numeric($expStr[$i]) || $expStr[$i]=='.')) {
                                $numStr .= $expStr[$i++];
                            }
                            $i--;
                            $expArr[] = (-1)*floatval($numStr);
                        }
                    } else {
                        $expArr[] = $expStr[$i];
                    }
                }
            }
        }
        return $expArr;
    }

    /**
     * 计算逆波兰表达式的值
     * @param array $rpnExp
     * @return mixed
     */
    public function getValByRpn(array $rpnExp)
    {
        $stack = new SplStack();
        $len = count($rpnExp);
        for ($i=0; $i<$len; $i++) {
            if (is_numeric($rpnExp[$i])) {
                $num = $rpnExp[$i];
                $stack->push($num);
            } elseif ($this->calculate->isAllowOperation($rpnExp[$i])) {
                if ($rpnExp[$i] == '!') {
                    $num = $this->calculate->calc('!', $stack->pop());
                    $stack->push($num);
                } elseif ($rpnExp[$i] == '-') {
                    $num = $this->calculate->calc('-', $stack->pop());
                    $stack->push($num);
                } elseif ($rpnExp[$i] == '?') {
                    $item3 = $stack->pop();
                    $item2 = $stack->pop();
                    $item1 = $stack->pop();
                    $num = $this->calculate->calc($rpnExp[$i], $item1, $item2, $item3);
                    $stack->push($num);
                } else {
                    $num = $this->calculate->calc($rpnExp[$i], $stack->pop(), $stack->pop());
                    $stack->push($num);
                }
            }
        }
        $ans = 0;
        while(!$stack->isEmpty()) {
            $ans += $stack->pop();
        }
        return $ans;
    }

    /**
     * 将中缀表达式转换为后缀表达式(逆波兰表达式)
     * @param array $exp
     * @return array
     */
    protected function expToRPN($exp)
    {
        $stack = new SplStack();
        $list = [];
        $len = count($exp);
        for ($i=0; $i<$len; $i++) {
            if (is_numeric($exp[$i])) {
                $list[] = $exp[$i];
            } elseif ($this->calculate->isAllowOperation($exp[$i])) {
                // exp[i] 为运算符
                if ($stack->isEmpty()) {
                    $stack->push($exp[$i]);
                    continue;
                }
                while (!$stack->isEmpty() && 
                ($stack->top()!='(') && 
                !$this->calculate->compareOperationPriority($exp[$i], $stack->top())) {
                    $list[] = $stack->pop();
                }
                $stack->push($exp[$i]);
            } elseif ($exp[$i] == '(') {
                $stack->push($exp[$i]);
            } elseif ($exp[$i] == ')') {
                while ($stack->top()!='(') {
                    $list[] = $stack->pop();
                }
                //此时这栈顶元素必为"("
                $stack->pop();
            }
        }

        // 将栈中剩余运算符加入到队列中
        while (!$stack->isEmpty()) {
            $list[] = $stack->pop();
        }

        return $list;
    }

    protected function getParamReal($param)
    {
        return isset($this->paramsSet[$param]) ? 1 : 0;
    }
}
