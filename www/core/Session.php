<?php

/**
 * Class Session
 */
class Session
{
    protected static $sessionStarted = false;
    protected static $sessionIdRegenerated = false;

    /**
     * Session constructor.
     */
    public function __construct()
    {
        if (!self::$sessionStarted) {
            session_start();
            self::$sessionStarted = true;
        }
    }

    /**
     * セッションに値を設定
     *
     * @param string $name
     * @param string $value
     */
    public function set(string $name, string $value)
    {
        $_SESSION[$name] = $value;
    }

    /**
     * セッションの値を取得
     *
     * @param string $name
     * @param null $default
     * @return mixed|null
     */
    public function get(string $name, $default = null)
    {
        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }

        return $default;
    }

    /**
     * セッションの値を除去
     *
     * @param string $name
     */
    public function remove(string $name)
    {
        unset($_SESSION[$name]);
    }

    /**
     * セッションのクリア
     */
    public function clear()
    {
        $_SESSION = [];
    }

    /**
     * セッションIDを再発行
     *
     * @param bool $destroy
     */
    public function regenerate($destroy = true)
    {
        if(!self::$sessionIdRegenerated) {
            session_regenerate_id($destroy);
            self::$sessionIdRegenerated = true;
        }
    }

    /**
     * ログイン状態の制御
     *
     * @param bool $bool
     */
    public function setAuthenticate(bool $bool)
    {
        $this->set('_authenticated', (bool)$bool);
        $this->regenerate();
    }

    /**
     * ログイン状態の確認
     *
     * @return mixed|null
     */
    public function isAuthenticated()
    {
        return $this->get('_authenticated', false);
    }

}