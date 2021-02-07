<?php
require './Calculate.php';
require './Expression.php';

$testCases = [
        0 => [
            'exp'   => 'T1101 * 4 + T1102 * 6 + T1103 * (-2)',
            'params' => ['T1101', 'T1102', 'T1103'],
            'assert' => 8
        ],
        1 => [
            'exp'   => 'T1101 * 4 + T1102 * 6 + T1103 * (-2)',
            'params' => ['T1101', 'T1102'],
            'assert' => 10
        ],
        2 => [
            'exp'   => '(T503 * 1 + T504 * 2 + T505 * 3) ',
            'params' => ['T501', 'T502', 'T503', 'T504', 'T505'],
            'assert' => 6
        ],
        3 => [
            'exp'   => '(!T501 & !T502) * (T503 * 1 + T504 * 2 + T505 * 3) ',
            'params' => ['T501', 'T502', 'T503', 'T504', 'T505'],
            'assert' => 0
        ],
        4 => [
            'exp'   => '(!T401*T402 | T403 | T404) * (-6)',
            'params' => ['T401', 'T402', 'T403', 'T404', 'T405'],
            'assert' => -6
        ],
        5 => [
            'exp'   => '((!T501 & !T502) *T503 * 1 + T504 *(-2) + T505 * 3) * ((!T501 & !T502) *T503 * 1 + T504 * (-2) + T505 * 3)',
            'params' => ['T501', 'T502', 'T503', 'T504', 'T505'],
            'assert' => 1
        ],
        6 => [
            'exp'   => '((!T501 & !T502) *T503 * 1 + T504 * (-2) + T505 * 3) * ((!T501 & !T502) *T503 * 1 + T504 * (-2) + T505 * 3)',
            'params' => ['T501', 'T502', 'T503', 'T504'],
            'assert' => 4
        ],
        7 => [
            'exp'   => '（！T401*T402 | T403 | T404) * 　(-2.12) / 　5/3',
            'params' => ['T401', 'T402', 'T403','T404'],
            'assert' => -0.1413333333
        ],
        8 => [
            'exp'   => 'T5157*-0.5+T5158*-50',
            'params' => ['T5158'],
            'assert' => -50
        ],
        9 => [
            'exp'   => 'T5157*-0.5+T5158*-50',
            'params' => ['T5157', 'T5158'],
            'assert' => -50.5
        ],
        10 => [
            'exp'   => 'T5157*-0.5*-T5158',
            'params' => ['T5157', 'T5158'],
            'assert' => 0.5
        ],
        11 => [
            'exp'   => '(T1101 + T1102 + T1103) < 3 ? 0 : (T1101 + T1102 + T1103)',
            'params' => ['T1101', 'T1102', 'T1103'],
            'assert' => 3
        ],
        12 => [
            'exp'   => '3<=2?  999   :（T402? (1==1)： 22 ）',
            'params'=> ['T401', 'T402'],
            'assert'=> 1
        ],
        13 => [
            'exp'   => '1 - -2',
            'params'=> [],
            'assert'=> 3
        ],
        14 => [
            'exp'   => '1 + -2 * -1 + -1',
            'params'=> [],
            'assert'=> 2
        ],
    ];
try {
    foreach ($testCases as $key => $item) {
        $expStr = $item['exp'];
        $params = $item['params'];
        $assert = $item['assert'];
        $obj = new \MathPaser\Expression($expStr, $params);
        $r = $obj->getVal();
        echo $r." ===== ".$assert.PHP_EOL;
    }
} catch (Exception $e) {
    exit($e->getMessage());
}