<?php

abstract class Application
{
    protected $debug = false;
    protected $request;
    protected $response;
    protected $session;
    protected $db_manager;
    protected $router;

    /**
     * Application constructor.
     * @param bool $debug
     */
    public function __construct(bool $debug = false)
    {
        $this->setDebugMode($debug);
        $this->initialize();
        $this->configure();
    }

    /**
     * デバックモードに応じてエラー表示処理を変更
     *
     * @param bool $debug
     */
    protected function setDebugMode(bool $debug)
    {
        if ($debug) {
            $this->debug = true;
            ini_set('display_errors', 1);
            error_reporting(-1);
        } else {
            $this->debug = false;
            ini_set('display_errors', 0);
        }
    }

    /**
     * クラスの初期化
     */
    protected function initialize()
    {
        $this->request = new Request();
        $this->response = new Response();
        $this->session = new Session();
        $this->db_manager = new DbManager();
        $this->router = new Router($this->registerRoutes());
    }

    /**
     * 個別のアプリケーションで設定
     */
    protected function configure()
    {
    }

    /**
     * ルートディレクトリを返却
     *
     * @return mixed
     */
    abstract public function getRootDir();

    /**
     * ルーティング定義配列を返却
     *
     * @return mixed
     */
    abstract protected function registerRoutes();

    /**
     * デバックモードを返却
     *
     * @return bool
     */
    public function isDebugMode()
    {
        return $this->debug;
    }

    /**
     * requestを返却
     *
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * responseを返却
     *
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * sessionを返却
     *
     * @return mixed
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * do_managerを返却
     *
     * @return mixed
     */
    public function getDbManager()
    {
        return $this->db_manager;
    }

    /**
     * controllerのディレクトリを返却
     *
     * @return string
     */
    public function getControllerDir()
    {
        return $this->getRootDir() . '/controllers';
    }

    /**
     * viewのディレクトリを返却
     *
     * @return string
     */
    public function getViewDir()
    {
        return $this->getRootDir() . '/views';
    }

    /**
     * modelのディレクトリを返却
     *
     * @return string
     */
    public function getModelDir()
    {
        return $this->getRootDir() . '/models';
    }

    /**
     * webのディレクトリを返却
     *
     * @return string
     */
    public function getWebDir()
    {
        return $this->getRootDir() . '/web';
    }

    /**
     * コントローラ名とアクション名を取得しrunAction()メソッドを呼び出す
     *
     * @throws HttpNotFoundException
     */
    public function run()
    {
        try {
            $params = $this->router->resolve($this->request->getPathInfo());
            if ($params === false) {
                throw new HttpNotFoundException('No router found for ' . $this->request->getPathInfo());
            }

            $controller = $params['controller'];
            $action = $params['action'];

            $this->runAction($controller, $action, $params);
        } catch (HttpNotFoundException $e) {
            $this->render404Page($e);
        }

        $this->response->send();
    }


    /**
     * アクションの実行
     *
     * @param string $controller_name
     * @param string $action
     * @param array $params
     * @throws HttpNotFoundException
     */
    public function runAction(string $controller_name, string $action, array $params = [])
    {
        $controller_class = ucfirst($controller_name) . 'Controller';

        $controller = $this->findController($controller_class);
        if ($controller === false) {
            throw new HttpNotFoundException($controller_class, ' controller is not found.');
        }

        $content = $controller->run($action, $params);
        $this->response->sentContent($content);
    }

    /**
     * コントローラクラスが読み込まれていない場合に、クラスファイルの読み込みを行う
     *
     * @param string $controller_class
     * @return bool|mixed
     */
    public function findController(string $controller_class)
    {
        if(!class_exists($controller_class)) {
            $controller_file = $this->getControllerDir() . '/' .$controller_class . '.php';

            if (!is_readable($controller_file)) {
                return false;
            } else {
                require_once $controller_file;

                if (!class_exists($controller_class)) {
                    return false;
                }
            }
        }

        return new $controller_class($this);
    }

    protected function render404Page($e)
    {
        $this->response->setStatusCode(404, 'Not Found');
        $message = $this->isDebugMode() ? $e->getMessage() : 'Page not found.';
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

        $this->response->setContent(<<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html"; charset="utf-8" />
    <title>404</title>
</head>
<body>
    {$message}
</body>
</html>
EOF
);
    }
}