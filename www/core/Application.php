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
     *
     */
    public function run()
    {
        $params = $this->router->resolve($this->request->getPathInfo());
        if ($params === false) {
            // todo-A
        }

        $controller = $params['controller'];
        $action = $params['action'];

        $this->runAction($controller, $action, $params);
        $this->response->send();
    }


    /**
     * @param string $controller_name
     * @param string $action
     * @param array $params
     */
    public function runAction(string $controller_name, string $action, array $params = [])
    {
        $controller_class = ucfirst($controller_name) . 'Controller';

        $controller = $this->findController($controller_class);
        if ($controller === false) {
            // todo-B
        }

        $content = $controller->run($action, $params);
        $this->response->sentContent($content);
    }

    /**
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

}