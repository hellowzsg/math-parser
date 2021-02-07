<?php

require './vendor/autoload.php';
require './PhpMath/NodeVisitor.php';
require './PhpMath/Ast.php';
require './PhpMath/Expression.php';


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
        'exp'   => 'T503 * 1 + T504 * 2 + T505 * 3',
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
        'exp'   => '（！T401*T402 | T403 | T404) * 　(-2.12) + (2==2)*2',
        'params' => ['T401', 'T402', 'T403','T404'],
        'assert' => -0.12
    ],
    9 => [
        'exp'   => '3>2?  （T402?88:22）   :1',
        'params' => ['T401', 'T402', 'T403','T404'],
        'assert' => 88
    ],
    10 => [
        'exp'   => '3<=2?  999   :（T402? (1==1)： 22 ）',
        'params' => ['T401', 'T402', 'T403','T404'],
        'assert' => 1
    ],
];

foreach ($testCases as $id => $case) {

    echo "id: $id\n";
    $exp = $case['exp'];
    $params = $case['params'];
    $assert = $case['assert'];
    $obj = new \PhpMath\Expression($exp, $params);
    $r = $obj->getVal();
//    exit();
    echo "assert: $assert, result: $r\n";
//    exit();
}