<?php
/**
 * Singleton logger
 */

namespace PHPAP21;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Log {

    protected static $instance;

    protected static $debugStdout = true;
    //protected static $defaultLevel = Logger::DEBUG;
    protected static $defaultLevel = Logger::INFO;
    protected static $mkLogDir = true;
    protected static $rotateLog = true;

    protected static $logLevels = [
        Logger::EMERGENCY,
        Logger::ALERT,
        Logger::CRITICAL,
        Logger::ERROR,
        Logger::WARNING,
        Logger::NOTICE,
        Logger::INFO,
        Logger::DEBUG
    ];

    /**
     * getLogLevel
     *
     * @return string
     */
    public static function getLogLevel() {
        return self::$defaultLevel;
    }

    /**
     * Get the Monolog instance
     *
     * @return \Monolog\Logger
     */
    static public function getLogger()
    {
        if (!self::$instance) {
            self::configureInstance($logLevel = self::$defaultLevel);
        }
        return self::$instance;
    }

    /**
     * Configure the Monolog instance
     *
     * @return \Monolog\Logger
     */
    protected static function configureInstance($logLevel = false)
    {
        if ($logLevel && in_array($logLevel, self::$logLevels)) {
            self::$defaultLevel = $logLevel;
        }
        $logDir = sprintf("%s%s%s", dirname(__DIR__), DIRECTORY_SEPARATOR, 'log');
        if (!file_exists($logDir)) {
            if (self::$mkLogDir) {
                mkdir($logDir, 0777, true) or die(sprintf("cannot mkdir %s!", $logDir));
            }
            else {
                throw new Exception(sprintf("log directory %s doen't exist!", $logDir));
            }
        }
        $logger = new Logger('ap21sdk');
        $logFilename = sprintf("%s%s%s", $logDir, DIRECTORY_SEPARATOR, 'ap21sdk.log');

        if (self::$debugStdout) {
            $handler = new StreamHandler(
                'php://stdout',
                self::getLogLevel()
            );
        }
        else if (self::$rotateLog) {
            $handler = new RotatingFileHandler(
                $logFilename,
                $maxFiles = 5,
                self::getLogLevel()
            );
        }
        else {
            $handler = new StreamHandler(
                $logFilename,
                self::getLogLevel()
            );
        }
        $logger->pushHandler($handler);
        self::$instance = $logger;
    }

    public static function debug($message, array $context = []) {
        self::getLogger()->addDebug($message, $context);
    }

    public static function info($message, array $context = []) {
        self::getLogger()->addInfo($message, $context);
    }

    public static function notice($message, array $context = []) {
        self::getLogger()->addNotice($message, $context);
    }

    public static function warning($message, array $context = []) {
        self::getLogger()->addWarning($message, $context);
    }

    public static function error($message, array $context = []) {
        self::getLogger()->addError($message, $context);
    }

    public static function critical($message, array $context = []) {
        self::getLogger()->addCritical($message, $context);
    }

    public static function alert($message, array $context = []) {
        self::getLogger()->addAlert($message, $context);
    }

    public static function emergency($message, array $context = []) {
        self::getLogger()->addEmergency($message, $context);
    }

}