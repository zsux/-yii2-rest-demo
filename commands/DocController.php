<?php

namespace app\commands;

use Yii;
use app\components\base\Form;
use phpDocumentor\Reflection\DocBlockFactory;
use yii\base\ErrorException;
use yii\base\Model;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;

/**
 * 文档生成器
 */
class DocController extends Controller
{
    /**
     * @var string 控制器名称, 例如; -c='app\controllers\MemberController'
     */
    public $controllerName;

    /**
     * @var string 模块名称, 例如: -m=member
     */
    public $moduleName;

    /**
     * @inheritdoc
     */
    public function options($actionId)
    {
        return ['controllerName', 'moduleName'];
    }

    /**
     * @inheritdoc
     */
    public function optionAliases()
    {
        return [
            'm' => 'moduleName',
            'c' => 'controllerName'
        ];
    }

    /**
     * 生成文档
     */
    public function actionIndex()
    {
        if ($this->controllerName) {
            if (!class_exists($this->controllerName)) {
                echo sprintf('控制器 %s 不存在。' . PHP_EOL, $this->controllerName);
            } else {
                self::generateControllerDocument($this->controllerName);
            }
        }

        $controllerNamespace = '\app\controllers';
        $controllerPath = Yii::getAlias('@app/controllers');

        if ($this->moduleName) {
            if (!Yii::$app->hasModule($this->moduleName)) {
                echo sprintf('模块 %s 不存在。' . PHP_EOL, $this->moduleName);

                Yii::$app->end();
            }

            $controllerPath = Yii::$app->getModule($this->moduleName)->controllerPath;
            $controllerNamespace = Yii::$app->getModule($this->moduleName)->controllerNamespace;
        }

        foreach (glob($controllerPath . '/*Controller.php') as $filename) {
            $class = $controllerNamespace . '\\' . str_replace([$controllerPath . '/', '.php'], '', $filename);

            if (!strpos($class, 'SiteController')) {
                self::generateControllerDocument($class);
            }
        }
    }

    /**
     * 生成一个控制器的文档
     *
     * @param $controller
     * @throws ErrorException
     */
    public static function generateControllerDocument($controller)
    {
        if (!class_exists($controller)) {
            throw new ErrorException(sprintf('控制器 %s 不存在。' . PHP_EOL, $controller));
        } else {
            self::create($controller);
        }
    }

    /**
     * 创建文档
     *
     * @param $class
     */
    protected static function create($class)
    {
        $controller = new \ReflectionClass($class);
        $moduleId = current(explode('\\', $controller->getNamespaceName()));
        $controllerId = Inflector::camel2id(str_replace('Controller', '', $controller->getShortName()));

        $classDocBlock = self::parseDocBlock($controller->getDocComment());

        $methods = self::parseActionMethod($controller);

        $verbs = self::parseVerbs($controller);

        echo '# ' . $classDocBlock->getSummary() . "\n\n";

        echo $classDocBlock->getDescription();

        foreach ($methods as $method) {
            $methodDocBlock = self::parseDocBlock($method->getDocComment());

            echo "\n\n" . '## ' . $methodDocBlock->getSummary() . "\n\n";

            echo $methodDocBlock->getDescription() . PHP_EOL . PHP_EOL;

            $actionId = Inflector::camel2id(str_replace('action', '', $method->name));

            echo '### 请求方式' . "\n\n";
            echo '`' . strtoupper(implode('`, `', ArrayHelper::getValue($verbs, $actionId, ['GET', 'POST']))) . "`\n\n";

            echo '### 请求地址' . "\n\n";
            echo '`/' . $moduleId . '/' . $controllerId . '/' . $actionId . "`\n\n";

            $parameters = self::parseActionParameters($method);

            echo '### 请求参数' . "\n\n";

            if ($parameters) {
                echo <<<EOF
| 名称 | 类型 | 是否必须 | 描述 |
| :-: | :-: | :-: | :-: |

EOF;
                foreach ($parameters as $parameter) {
                    echo sprintf(
                        "| %s | %s | %s | %s |\n",
                        Inflector::underscore($parameter[0]),
                        $parameter[1],
                        $parameter[2],
                        $parameter[3]
                    );
                }
            }
        }
    }

    /**
     * 解析参数
     *
     * @param \ReflectionMethod $method
     * @return array
     */
    protected static function parseActionParameters(\ReflectionMethod $method)
    {
        $form = self::parseForm($method);
        $formClass = self::parseFormClass($method->getDeclaringClass(), $form[0]);

        $formModel = self::createObject($formClass);
        $scenario = self::parseScenario(new \ReflectionMethod($formModel, $form[1]));
        $scenarios = $formModel->scenarios();
        $parameters = isset($scenarios[$scenario]) ? $scenarios[$scenario] : [];
        $rules = $formModel->rules();

        $parameters = array_map(function ($name) use ($formModel, $rules, $scenario) {
            $parameter = [$name, '', '', ''];
            $property = new \ReflectionProperty($formModel, $name);
            $comment = $property->getDocComment();
            $varTag = self::parseVarTag($comment);
            if ($varTag) {
                $parameter[1] = $varTag[0];
                $parameter[3] = $varTag[1];
            }
            $parameter[2] = self::isRequired($name, $rules, $scenario) ? '是' : '否';

            return $parameter;
        }, $parameters);

        return $parameters;
    }

    /**
     * 解析场景和规则, 判断参数是否必须
     *
     * @param $name
     * @param $rules
     * @param $scenario
     * @return bool
     */
    public static function isRequired($name, $rules, $scenario)
    {
        $required = false;

        foreach ($rules as $rule) {
            if ($rule[1] != 'required') {
                continue;
            }
            if (is_array($rule[0])) {
                if (!in_array($name, $rule[0])) {
                    continue;
                }
            } elseif ($name != $rule[0]) {
                continue;
            }
            if (isset($rule['on'])) {
                if (in_array($scenario, $rule['on'])) {
                    $required = true;
                }
            } else {
                $required = true;
            }
            if ($required) {
                break;
            }
        }

        return $required;
    }

    /**
     * 解析 verbs
     *
     * @param \ReflectionClass $class
     * @return array|mixed
     */
    protected static function parseVerbs(\ReflectionClass $class)
    {
        if ($class->hasMethod('verbs')) {
            $method = $class->getMethod('verbs');

            $body = self::parseMethodBody($method);

            if (preg_match('/\{(.*)\}/is', $body, $match)) {
                return eval($match[1]);
            }
        }

        return [];
    }

    /**
     * 从控制器类中解析出完整的表单类
     *
     * @param \ReflectionClass $class
     * @param $name
     * @return mixed
     * @throws \ErrorException
     */
    protected static function parseFormClass(\ReflectionClass $class, $name)
    {
        $body = self::parseClassBody($class);

        if (preg_match('/^use\s+(.*' . preg_quote('\\' . $name) . ');/mU', $body, $match)) {
            return $match[1];
        }

        throw new \ErrorException(sprintf('无法解析表单 %s', $name));
    }

    /**
     * 从 comment 字符串解析 @var
     *
     * @param $comment
     * @return array|bool
     */
    public static function parseVarTag($comment)
    {
        if (preg_match('/@var\s+(.+)\s+(.*)$/isUm', $comment, $match)) {
            return array_splice($match, 1);
        };

        return false;
    }

    /**
     * 解析场景
     *
     * @param \ReflectionMethod $method
     * @return string
     */
    protected static function parseScenario(\ReflectionMethod $method)
    {
        $body = self::parseMethodBody($method);

        $scenario = Model::SCENARIO_DEFAULT;

        if (preg_match('/\$this->validateScenario\(.*,\s[\'"](.*)[\'""]\)/', $body, $match)) {
            $scenario = $match[1];
        }

        return $scenario;
    }

    /**
     * 从类名称创建实例对象
     *
     * @param $name
     * @return Form
     */
    protected static function createObject($name)
    {
        return new $name();
    }

    /**
     * 从 action method 中解析出使用的 Form 类名
     *
     * 例如, (new UserForm)->login($_POST), 返回结果就是 ['UserForm', 'login']
     *
     * @param \ReflectionMethod $method
     * @return array|null
     */
    protected static function parseForm(\ReflectionMethod $method)
    {
        $body = self::parseMethodBody($method);

        $form = null;

        if (preg_match('/\(new\s+(.*Form)(?:\(\))?\)->(.*)\(/m', $body, $match)) {
            $form = [$match[1], $match[2]];
        } else {
            if (preg_match('/(\$.+)\s+=\s+new\s+(.+Form)/m', $body, $m1)) {
                if (preg_match('/' . preg_quote($m1[1]) . '->([^\(]+)/m', $body, $m2)) {
                    $form = [$m1[2], $m2[1]];
                }
            }
        }

        return $form;
    }

    /**
     * 获得方法的 body
     *
     * @param \ReflectionMethod $method
     * @return string
     */
    protected static function parseMethodBody(\ReflectionMethod $method)
    {
        $startLine = $method->getStartLine() - 1;
        $endLine = $method->getEndLine();
        $content = file($method->getFileName());
        $length = $endLine - $startLine;

        return implode('', array_slice($content, $startLine, $length));
    }

    /**
     * 获得类的 body
     *
     * @param \ReflectionClass $class
     * @return string
     */
    protected static function parseClassBody(\ReflectionClass $class)
    {
        $content = file($class->getFileName());

        return implode('', $content);
    }

    /**
     * 解析 action 的 method
     *
     * @param \ReflectionClass $class
     * @return \ReflectionMethod[]
     */
    protected static function parseActionMethod(\ReflectionClass $class)
    {
        $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);

        return array_filter($methods, function (\ReflectionMethod $method) {
            return strpos($method->name, 'action') === 0 && $method->name != 'actions';
        });
    }

    /**
     * 解析注释
     *
     * @param $comment
     * @return array|\phpDocumentor\Reflection\DocBlock
     */
    protected static function parseDocBlock($comment)
    {
        $factory = DocBlockFactory::createInstance();
        $docblock = $factory->create($comment);

        return $docblock;
    }
}
