<?php

namespace Logger;

use Psr\Log\LoggerInterface;

class Logger implements LoggerInterface
{
    protected $path;
    protected $prefix;

    public function __construct()
    {
        $this->path = PROJECT_ROOT_DIR."/var/log/application.log";
        $this->prefix = "[".date("Y-m-d H:i:s")."] ";
    }
    public function emergency(\Stringable|string $message, array $context = []): void
    {
        $message = "[emergency] ".$this->prefix.$message;
        error_log($message."\n", 3, $this->path);
    }

    public function alert(\Stringable|string $message, array $context = []): void
    {
        $message = "[alert] ".$this->prefix.$message;
        echo $message;
    }

    public function critical(\Stringable|string $message, array $context = []): void
    {
        $message = "[critical] ".$this->prefix.$message;
        error_log($message."\n", 3, $this->path);
    }

    public function error(\Stringable|string $message, array $context = []): void
    {
        $message = "[error] ".$this->prefix.$message;
        error_log($message."\n", 3, $this->path);
    }

    public function warning(\Stringable|string $message, array $context = []): void
    {
        $message = "[warning] ".$this->prefix.$message;
        error_log($message."\n", 3, $this->path);
    }

    public function notice(\Stringable|string $message, array $context = []): void
    {
        $message = "[notice] ".$this->prefix.$message;
        error_log($message."\n", 3, $this->path);
    }

    public function info(\Stringable|string $message, array $context = []): void
    {
        $message = "[info] ".$this->prefix.$message;
        error_log($message."\n", 3, $this->path);
    }

    public function debug(\Stringable|string $message, array $context = []): void
    {
        $message = "[debug] ".$this->prefix.$message;
        error_log($message."\n", 3, $this->path);
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $message = "[log] ".$this->prefix.$message;
        error_log($message."\n", 3, $this->path);
    }
}