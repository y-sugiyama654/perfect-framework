<?php

class Request
{
    /**
     * HTTPメソッドがPOSTか判定
     *
     * @return bool
     */
    public function isPost()
    {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            return true;
        }

        return false;
    }

    /**
     * $_GET変数から値を取得
     *
     * @param $name キー名
     * @param null $default
     * @return mixed|null
     */
    public function getGet($name, $default = NULL)
    {
        if (isset($_GET[$name])) {
            return $_GET[$name];
        }

        return $default;
    }

    /**
     * $_POST変数から値を取得
     *
     * @param $name
     * @param null $default
     * @return mixed|null
     */
    public function getPost($name, $default = NULL)
    {
        if (isset($_POST[$name])) {
            return $_POST[$name];
        }

        return $default;
    }

    /**
     * サーバのホスト名を取得
     *
     * @return mixed
     */
    public function getHost()
    {
        if (!empty($_SERVER['HTTP_HOST'])) {
            return $_SERVER['HTTP_HOST'];
        }

        return $_SERVER['SERVER_NAME'];
    }

    /**
     * HTTPSでのアクセスか判定
     *
     * @return bool
     */
    public function isSsl()
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            return true;
        }

        return false;
    }

    /**
     * リクエストのURL情報を取得
     *
     * @return mixed
     */
    public function getRequestUri()
    {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * ベースURLを取得
     */
    public function getBaseUrl()
    {
        $script_name = $_SERVER['SCRIPT_NAME'];
        $request_uri = $this->getRequestUri();

        if (0 === strpos($request_uri, $script_name)) {
            return $script_name;
        } else if (0 === strpos($request_uri, dirname($script_name))) {
            return rtrim(dirname($script_name), '/');
        }

        return '';
    }

    /**
     * PATH_INFOの取得
     */
    public function getPathInfo()
    {
        $base_url = $this->getBaseUrl();
        $request_uri = $this->getRequestUri();

        if (false !== ($pos = strpos($request_uri, '?'))) {
            $request_uri = substr($request_uri, 0, $pos);
        }
        
        return (string)substr($request_uri, strlen($base_url));
    }
}