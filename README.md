# math-parser

> 字符串表达式求值
>
> 分别使用 **逆波兰表达式** 和 **虚拟语法树(todo)** 实现


### 虚拟语法树

> TODO

### 逆波兰表达式

#### 支持如下运算符

| 运算符                   | 优先级 | 实例                   |
| ------------------------ | ------ | ---------------------- |
| ()                       | -1     |                        |
| ?                        | 0      | 4?1:2 = 1      0?1:2=2 |
| \|                       | 1      |                        |
| &                        | 2      |                        |
| 比较运算符(>,<,>=,<=,==) | 3      | 2>1 = 1        2<1 = 0 |
| +,-                      | 4      | 1+2 = 3   3-2=1        |
| *,/                      | 5      | 1*3 = 3   4/2=2        |
| !                        | 6      | !3 = 0    !0 = 1       |

#### 可设置参数

> eg:   T1   T123

以T开头，后加数字。

表达式中的参数如果在parms列表中存在，即认为该参数是1，否则为0。

#### example

```php
// simple express
$expStr = '1 + -2 * -1 + -1';
$params = [];
$obj = new \MathPaser\Expression($expStr, $params);
echo $obj->getVal();   // 2
// params
$expStr = 'T1*1 + (T2*5 - 1)/2 - T3*2';
$params = ['T1', 'T2', 'T3'];
$obj = new \MathPaser\Expression($expStr, $params);
echo $obj->getVal();   // 3
```

