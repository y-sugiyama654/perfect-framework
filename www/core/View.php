<?php

class View
{
    protected $base_dir;
    protected $defaults;
    protected $layout_variable = [];

    /**
     * View constructor.
     * @param string $base_dir
     * @param array $defaults
     */
    public function __construct(string $base_dir, array $defaults = [])
    {
        $this->base_dir = $base_dir;
        $this->defaults = $defaults;
    }

    /**
     * レイアウトに渡す値を設定
     *
     * @param $name
     * @param $value
     */
    public function setLayoutVar(string $name, string $value)
    {
        $this->layout_variable[$name] = $value;
    }

    /**
     * ビューファイルの読み込み
     *
     * @param string $_path
     * @param array $_variables
     * @param bool $_layout
     * @return false|mixed|string
     */
    public function render(string $_path, array $_variables = [], bool $_layout = false)
    {
        $_file = $this->base_dir . '/' . $_path . '.php';

        // 配列からシンボルテーブルに変数をインポート
        extract(array_merge($this->defaults, $_variables));

        // アウトプットバッファリング
        ob_start();
        // 自動フラッシュを無効に設定
        ob_implicit_flush(0);

        require $_file;

        // バッファに格納された文字列を変数に格納
        $content = ob_get_clean();

        if ($_layout) {
            $content = $this->render($_layout, array_merge($this->layout_variable, ['_content' => $content]));
        }
        return $content;
    }

    /**
     * HTML特殊文字をエスケープ
     *
     * @param string $string
     * @return string
     */
    public function escape(string $string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}