<?php

abstract class Controller
{
    protected $controller_name;
    protected $action_name;
    protected $application;
    protected $request;
    protected $response;
    protected $session;
    protected $db_manager;
    protected $auth_actions;

    /**
     * Controller constructor.
     * @param $application
     */
    public function __construct($application)
    {
        $this->controller_name = strtolower(substr(get_class($this), 0, -10));

        $this->application = $application;
        $this->request = $application->getRequest();
        $this->response = $application->getResponse();
        $this->session = $application->getSession();
        $this->db_manager = $application->getDbManager();
    }

    /**
     * アクションの実行を行う
     *
     * @param string $action
     * @param array $params
     * @return mixed
     */
    public function run(string $action, array $params = [])
    {
        $this->action_name = $action;

        $action_method = $action . 'Action';
        if (!method_exists($this, $action_method)) {
            $this->forward404();
        }

        if ($this->needsAuthentication($action) && !$this->session->isAuthenticated()) {
            throw new UnauthorizedActionException();
        }

        return $this->$action_method($params);
    }

    /**
     * ログインが必要かどうかの判定
     *
     * @param string $action
     * @return bool true:ログイン必要 false:ログイン不要
     */
    protected function needsAuthentication(string $action)
    {
        if ($this->auth_actions === true || (is_array($this->auth_actions && in_array($action, $this->auth_actions)))) {
            return true;
        }
        return false;
    }

    /**
     * ビューファイルの読み込み処理をラッピング
     *
     * @param array $variables
     * @param null $template
     * @param string $layout
     * @return false|mixed|string
     */
    protected function render(array $variables = [], $template = null, string $layout = 'layout')
    {
        $defaults = [
            'request'  => $this->request,
            'base_url' => $this->request->getBaseUrl(),
            'session'  => $this->session,
        ];

        $view = new View($this->application->getViewDir(), $defaults);

        if (is_null($template)) {
            $template = $this->action_name;
        }

        $path = $this->controller_name . '/' . $template;

        return $view->render($path, $variables, $layout);
    }

    /**
     * HttpNotFoundExceptionを通知し、404エラー画面に遷移
     *
     * @throws HttpNotFoundException
     */
    protected function forward404()
    {
        throw new HttpNotFoundException('Forwarded 404 page from ' . $this->controller_name . '/' . $this->action_name);
    }

    /**
     * Responseオブジェクトにリダイレクトするように設定
     *
     * @param string $url
     */
    protected function redirect(string $url)
    {
        if (!preg_match('#https?://#', $url)) {
            $protocol = $this->request->isSsl() ? 'https://' : 'http;//';
            $host = $this->request->getHost();
            $base_url = $this->request->getBaseUrl();

            $url = $protocol . $host . $base_url . $url;
        }

        $this->response->setStatusCode(302, 'Found');
        $this->response->setHttpHeader('Location', $url);
    }

    /**
     * トークンを生成しセッションに格納
     *
     * @param string $form_name
     */
    protected function generateCsrfToken(string $form_name)
    {
        $key = 'csrf_tokens/' . $form_name;
        $tokens = $this->session->get($key, []);
        if (count($tokens) >= 10) {
            array_shift($tokens);
        }

        $token = sha1($form_name . session_id() . microtime());
        $this->session->set($key, $token);
        return $token;
    }

    /**
     * セッションに格納されているトークンからPOSTされたトークンを探す
     *
     * @param string $form_name
     * @param string $token
     */
    protected function checkCsrfToken(string $form_name, string $token)
    {
        $key = 'csrf_tokens/' . $form_name;
        $tokens = $this->session->get($key, []);

        if (false !== ($pos = array_search($token, $tokens, true))) {
            unset($tokens[$pos]);
            $this->session->set($key, $tokens);

            return true;
        }

        return false;
    }
}