<?php

namespace Helper;

use PDO;
use PDOException;

class Database
{
    private PDO $conn;
    private $sql;

    public function __construct()
    {
        $serverName = SERVERNAME;
        $serverPort = SERVER_PORT;
        $dbName = DB_NAME;
        $dbUser = DB_USER;
        $dbPass = DB_PASS;

        if(defined('CLI') && CLI === true) {
            $serverPort = SERVERPORT_CLI;
            $serverName = SERVERNAME_CLI;
        }

        try {
            $this->conn = new PDO("mysql:host=" . $serverName . ";port=".$serverPort.";dbname=" . $dbName, $dbUser, $dbPass);
            // set the PDO error mode to exception
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $this->sql = '';
        } catch(PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }

        return $this;
    }

    public function query($query)
    {
        $this->sql = $query;

        return $this;
    }

    public function getAll()
    {
        $sth = $this->conn->prepare($this->sql);
        $sth->execute();

        return $sth->fetchAll();
    }

    public function get()
    {
        $sth = $this->conn->prepare($this->sql);
        $sth->execute();

        return $sth->fetch();
    }

    public function begin()
    {
        $this->conn->beginTransaction();

        return $this;
    }

    public function exec($params = null)
    {
        $sth = $this->conn->prepare($this->sql);
        $sth->execute($params);

        return $this;
    }

    public function commit()
    {
        $this->conn->commit();

        return $this;
    }

    public function rollBack()
    {
        $this->conn->rollBack();

        return $this;
    }

    public function lastId()
    {
        return $this->conn->lastInsertId();
    }
}