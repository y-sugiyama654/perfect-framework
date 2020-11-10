<?php

class ClassLoader
{
    protected $dirs;

    /**
     * オートローダー登録処理
     */
    public function register()
    {
        mysql_autoload_register(array($this, 'loadClass'));
    }

    /**
     * ディレクトリ登録処理
     *
     * @param string $dir ディレクトリ名
     */
    public function registerDir(string $dir)
    {
        $this->dirs[] = $dir;
    }

    /**
     * クラスファイルの読み込み処理
     *
     * @param string $class クラス名
     */
    public function loadClass(string $class)
    {
        foreach ($this->dirs as $dir) {
            $file = $dir . '/' . $class . '.php';
            if (is_readable($file)) {
                require $file;
                return;
            }
        }
    }
}