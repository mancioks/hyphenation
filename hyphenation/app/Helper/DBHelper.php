<?php

declare(strict_types=1);

namespace Helper;

use PDO;
use PDOException;

class DBHelper
{
    private PDO $conn;
    private string $sql;

    public function __construct()
    {
        try {

            $this->sql = '';

            if(defined('CLI'))
                $this->conn = new PDO("mysql:host=" . SERVERNAME_CLI . ";port=".SERVERPORT_CLI.";dbname=" . DB_NAME, DB_USER, DB_PASS);
            else
                $this->conn = new PDO("mysql:host=" . SERVERNAME . ";dbname=" . DB_NAME, DB_USER, DB_PASS);

            // set the PDO error mode to exception
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            //$this->logger->critical();
            //Logger::log("Connection failed: " . $e->getMessage());

        }
    }

    public function select(string $fields = '*'): self
    {
        $this->sql .= 'SELECT ' . $fields . ' ';

        return $this;
    }

    public function from(string $table): self
    {
        $this->sql .= 'FROM ' . $table . ' ';
        return $this;
    }

    public function truncate(string $table): self
    {
        $this->sql = 'TRUNCATE TABLE '.$table;
        return $this;
    }

    public function clear(): self
    {
        $this->sql = '';
        return $this;
    }

    public function where(string $field, string|int $value, string $operator = '='): self
    {
        $this->sql .= 'WHERE ' . $field . " ". $operator . ' "' . $value . '" ';
        return $this;
    }

    public function andWhere(string $field, string|int $value, string $operator = '='): self
    {
        $this->sql .= 'AND ' . $field . " " . $operator . ' "' . $value . '" ';
        return $this;
    }

    public function orWhere(string $field, string|int $value, string $operator = '='): self
    {
        $this->sql .= 'OR ' . $field . " " . $operator . ' "' . $value . '" ';
        return $this;
    }

    public function get(): array
    {
        $rez = $this->exec();
        return $rez->fetchAll();
    }

    public function delete(): self
    {
        $this->sql .= 'DELETE ';
        return $this;
    }

    public function insert(string $table, array $data):self
    {
        $this->sql .= "INSERT INTO " . $table . " (" . implode(", ", array_keys($data)) . ") VALUES ('" . implode("', '", $data) . "')";

        return $this;
    }

    public function orderBy(string $column, string $order = "DESC"): self
    {
        $this->sql .= " ORDER BY " . $column . " " . $order . " ";

        return $this;
    }

    public function update(string $table, array $data): self
    {
        $update = "";
        foreach ($data as $column => $value) {
            $update .= $column . " = '" . $value . "', ";
        }
        $update = trim($update, ", ");

        $this->sql .= "UPDATE " . $table . " SET " . $update . " ";

        return $this;
    }

    public function exec(): self
    {
//        if(DEBUG_MODE) {
//            Logger::log($this->sql);
//        }
        $this->conn->query($this->sql);
        $this->clear();
        return $this;
    }

    public function getOne(): array|bool
    {
        $rez = $this->exec();
        $data = $rez->fetchAll();

        if (!empty($data)) {
            return $data[0];
        } else {
            return false;
        }
    }

    public function limit(int|string $number): self
    {
        $this->sql .= " LIMIT " . $number;

        return $this;
    }
}