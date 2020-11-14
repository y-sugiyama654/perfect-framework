<?php

class DbManager
{
    /**
     * PDOクラスのインスタンスを保持
     */
    protected $connections = [];

    /**
     * repositoryと接続名のマッピングを保持
     */
    protected $repository_connection_map = [];

    /**
     * リポジトリ情報を保持
     */
    protected $repositories = [];

    /**
     * DBとの接続を開放
     */
    public function __destruct()
    {
        foreach ($this->repositories as $repository) {
            unset($repository);
        }

        foreach ($this->connections as $con) {
            unset($con);
        }
    }

    /**
     * DBの接続
     *
     * @param string $name 接続コネクション名
     * @param array $params 接続情報
     */
    public function connect(string $name, array $params)
    {
        $params = array_merge([
            'dsn' => null,
            'user' => '',
            'password' => '',
            'options' => [],
        ], $params);

        $con = new PDO(
            $params['dsn'],
            $params['user'],
            $params['password'],
            $params['options']
        );

        // エラー時に例外を発生
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->connections[$name] = $con;
    }

    /**
     * コネクションを取得
     *
     * @param null $name 接続名
     * @return mixed 接続コネクション名
     */
    public function getConnection($name = NULL)
    {
        if (is_null($name)) {
            return current($this->connections);
        }

        return $this->connections[$name];
    }

    /**
     * repositoryと接続名のマッピング
     *
     * @param string $repository_name
     * @param string $name
     */
    public function setRepositoryConnectionMap(string $repository_name, string $name)
    {
        $this->repository_connection_map[$repository_name] = $name;
    }

    /**
     * repositoryクラスにに対応する接続を取得
     *
     * @param string $repository_name リポジトリ名
     * @return mixed 接続情報
     */
    public function getConnectionForRepository(string $repository_name)
    {
        if (isset($this->repository_connection_map[$repository_name])) {
            $name = $this->repository_connection_map[$repository_name];
            $con = $this->getConnection($name);
        } else {
            $con = $this->getConnection();
        }

        return $con;
    }

    /**
     * 各リポジトリ事にインスタンスの生成
     *
     * @param string $repository_name リポジトリ名
     * @return mixed
     */
    public function get(string $repository_name)
    {
        if (!isset($this->repositories[$repository_name])) {
            $repository_class = $repository_name . 'Repository';
            $con = $this->getConnectionForRepository($repository_name);

            $repository = new $repository_class($con);

            $this->repositories[$repository_name] = $repository;
        }

        return $this->repositories[$repository_name];
    }

}