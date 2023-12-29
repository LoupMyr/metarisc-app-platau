<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

class LoggerService implements LoggerInterface
{
    final public const LOG_PATH = '/var/log/metarisc-platau.log';

    public function __construct()
    {
    }

    public function emergency(string|\Stringable $message, array $context = []) : void
    {
        $file = fopen(self::LOG_PATH, 'a', true);
        fwrite($file, 'Emergency: '.$message."\n");
        fclose($file);
    }

    public function alert(string|\Stringable $message, array $context = []) : void
    {
        $file = fopen(self::LOG_PATH, 'a', true);
        fwrite($file, 'Alert: '.$message."\n");
        fclose($file);
    }

    public function critical(string|\Stringable $message, array $context = []) : void
    {
        $file = fopen(self::LOG_PATH, 'a', true);
        fwrite($file, 'Critical: '.$message."\n");
        fclose($file);
    }

    public function error(string|\Stringable $message, array $context = []) : void
    {
        $file = fopen(self::LOG_PATH, 'a', true);
        fwrite($file, 'Error: '.$message."\n");
        fclose($file);
    }

    public function warning(string|\Stringable $message, array $context = []) : void
    {
        $file = fopen(self::LOG_PATH, 'a', true);
        fwrite($file, 'Warning: '.$message."\n");
        fclose($file);
    }

    public function notice(string|\Stringable $message, array $context = []) : void
    {
        $file = fopen(self::LOG_PATH, 'a', true);
        fwrite($file, 'Notice: '.$message."\n");
        fclose($file);
    }

    public function info(string|\Stringable $message, array $context = []) : void
    {
        $file = fopen(self::LOG_PATH, 'a', true);
        fwrite($file, 'Info: '.$message."\n");
        fclose($file);
    }

    public function debug(string|\Stringable $message, array $context = []) : void
    {
        $file = fopen(self::LOG_PATH, 'a', true);
        fwrite($file, 'Debug: '.$message."\n");
        fclose($file);
    }

    public function log($level, string|\Stringable $message, array $context = []) : void
    {
        $file = fopen(self::LOG_PATH, 'a', true);
        fwrite($file, 'Log: '.$message."\n");
        fclose($file);
    }
}
