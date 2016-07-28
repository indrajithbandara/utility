<?php namespace Common\Utility\Dev;

use Common\Logger\Logger;

class Inspector
{
    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        if (!(error_reporting() & $errno)) {
            // This error code is not included in error_reporting
            return null;
        }
        $msg = "<xmp>\n%s: %s in %s:%s\nStack trace:\n%s\n</xmp>";
        $trace = debug_backtrace();
        array_shift($trace); //shift callstack of calling this
        $msg = sprintf(
            $msg,
            self::friendlyErrorType($errno),
            $errstr,
            $errfile,
            $errline,
            implode("\n", self::formatTrace($trace))
        );

        echo $msg;

        exit(255);
        /* Don't execute PHP internal error handler */
//        return true;
    }


    public static function exceptionHandler(\Exception $exception)
    {
        Logger::instance()->error(__METHOD__, [
            'exception' => $exception,
        ]);

        $msg = <<<HTML
<h2>PHP Fatal error</h2>
<xmp>Uncaught exception '%s' with message '%s' in %s:%s
    Stack trace:
    %s
    thrown in %s on line %s
</xmp>
<pre>
HTML;

        $trace = $exception->getTrace();

        $result = self::formatTrace($trace);

        $msg = sprintf(
            $msg,
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            implode("\n", $result),
            $exception->getFile(),
            $exception->getLine()
        );
        if( php_sapi_name() !== 'cli' )  header('Content-Type: text/html; charset=utf-8');
        
        echo $msg;
        exit(255);
    }

    public static function formatTrace($trace)
    {
        $result = array();
        $traceline = '#%s %4$s(%5$s) @ %2$s:%3$s';
        $key = 0;
        foreach ($trace as $key => $stackPoint) {

            if (isset($stackPoint['args'])) {
                foreach ($stackPoint['args'] as $k => $arg) {
                    unset($stackPoint['args'][$k]); //args下可能有引用，先unset防止串改
                    $stackPoint['args'][$k] = is_scalar($arg) ? var_export($arg, true) : (is_object($arg) ? get_class(
                        $arg
                    ) : gettype($arg));
                }
            } else {
                $stackPoint['args'] = array();
            }
            unset($arg);
            $fn = isset($stackPoint['class'])
                ? "{$stackPoint['class']}{$stackPoint['type']}{$stackPoint['function']}"
                : $stackPoint['function'];

            $result[] = sprintf(
                $traceline,
                $key,
                @$stackPoint['file'],
                @$stackPoint['line'],
                $fn,
                implode(', ', $stackPoint['args'])
            );
        }

        $result[] = '#' . ++$key . ' {main}';
        return $result;
    }

    protected static function friendlyErrorType($type)
    {
        switch ($type) {
            case E_ERROR: // 1 //
                return 'E_ERROR';
            case E_WARNING: // 2 //
                return 'E_WARNING';
            case E_PARSE: // 4 //
                return 'E_PARSE';
            case E_NOTICE: // 8 //
                return 'E_NOTICE';
            case E_CORE_ERROR: // 16 //
                return 'E_CORE_ERROR';
            case E_CORE_WARNING: // 32 //
                return 'E_CORE_WARNING';
            case E_COMPILE_ERROR: // 64 //
                return 'E_COMPILE_ERROR';
            case E_COMPILE_WARNING: // 128 //
                return 'E_COMPILE_WARNING';
            case E_USER_ERROR: // 256 //
                return 'E_USER_ERROR';
            case E_USER_WARNING: // 512 //
                return 'E_USER_WARNING';
            case E_USER_NOTICE: // 1024 //
                return 'E_USER_NOTICE';
            case E_STRICT: // 2048 //
                return 'E_STRICT';
            case E_RECOVERABLE_ERROR: // 4096 //
                return 'E_RECOVERABLE_ERROR';
            case E_DEPRECATED: // 8192 //
                return 'E_DEPRECATED';
            case E_USER_DEPRECATED: // 16384 //
                return 'E_USER_DEPRECATED';
        }
        return "";
    }
}
