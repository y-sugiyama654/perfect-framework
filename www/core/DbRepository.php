<?php

abstract class DbRepository
{

    /**
     * PDOインスタンスを保持
     */
    protected $con;

    /**
     * DbRepository constructor.
     *
     * @param $con
     */
    public function __construct($con)
    {
        $this->setConnection($con);
    }

    /**
     * PDOクラスのインスタンスを受け取りプロパティに保持
     *
     * @param $con
     */
    public function setConnection($con)
    {
        $this->con = $con;
    }

    /**
     * PDOStatementクラスのインスタンスを取得
     *
     * @param $sql
     * @param array $params
     * @return mixed
     */
    public function execute($sql, $params = [])
    {
        $stmt = $this->con->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }

    /**
     * PDOStatementクラスのインスタンスを1行取得
     *
     * @param $sql
     * @param array $params
     * @return mixed
     */
    public function fetch($sql, $params = [])
    {
        return $this->execute($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * PDOStatementクラスのインスタンスを全ての行取得
     *
     * @param $sql
     * @param array $params
     * @return mixed
     */
    public function fetchAll($sql, $params = [])
    {
        return $this->execute($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }
}