<?php

/**
 * This file is part of the Nette Framework (http://nette.texy.info/)
 *
 * Copyright (c) 2005-2007 David Grudl aka -dgx- <dave@dgx.cz>
 *
 * @version  $Revision: 51 $ $Date: 2007-04-11 20:34:53 +0200 (st, 11 IV 2007) $
 * @package  Nette
 */



/**
 * Debug static class
 */
final class NDebug
{
    public static $display = TRUE;
    public static $log = FALSE;
    public static $logDir;


    /**
     * Static class pattern: forbid "new"
     */
    final private function __construct()
    {}


    /**
     * Init debug class
     * @return void
     */
    public static function handleErrors()
    {
        error_reporting(E_ALL | E_STRICT);
        /*
        set_error_handler(array(__CLASS__, 'errorHandler'));
        set_exception_handler(array(__CLASS__, 'exceptionHandler'));
        */
    }



    /**
     * @param Exception
     */
    public static function exceptionHandler($e)
    {
        if (function_exists('debugbreak')) debugbreak();
        // html output
        if (!headers_sent()) NHttpResponse::setCode(NHttpResponse::S500_INTERNAL_SERVER_ERROR);
        while (ob_get_level()) ob_end_clean();

        echo "<h1>Exception</h1>";
        echo "<p>Exception '", get_class($e), "' #", $e->getCode(), " ", htmlSpecialChars($e->getMessage()), "</p>";
        $page = Nette::getPage();

        if (NETTE_MODE === 'DEBUG') {
            echo "<h3>Debug Backtrace</h3>";
            self::printTrace($e->getTrace());
            if ($page) {
                echo "<h3>Page: ", get_class($page), "</h3><xmp>";
                print_r($page->getParams());
                echo "</xmp>";
            }
        } else {
            // log output
            ob_start();
            echo "Exception '", get_class($e), "' #", $e->getCode(), " ", $e->getMessage(), "\n\n",
                NHttpRequest::getMethod(), " ", NHttpRequest::getURI(), "\n\n",
                'Page: ', get_class(Nette::getPage()), "\n\n";
            self::printTrace($e->getTrace(), FALSE);
            if ($page) {
                echo "Page: ", get_class($page), "\n";
                print_r($page->getParams());
            }
            file_put_contents(NETTE_LOG_DIR . '/exception ' . date('Y-m-d H-i-s ') . substr(microtime(FALSE), 2, 6) . '.txt', ob_get_clean());
        }

        // fix IE
        $s = " \t\r\n";
        for ($i = 2e3; $i; $i--) echo $s{rand(0, 3)};
        exit;
    }



    /**
     * @param int    level of the error raised
     * @param string error message
     * @param string filename that the error was raised in
     * @param int    line number the error was raised at
     * @param int    an array of variables that existed in the scope the error was triggered in
     */
    public static function errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        if ($errno === E_USER_ERROR) {
            if (function_exists('debugbreak')) debugbreak();
            if (!headers_sent()) NHttpResponse::setCode(NHttpResponse::S500_INTERNAL_SERVER_ERROR);
            while (ob_get_level()) ob_end_clean();

            echo "<h1>Error</h1>";
            echo "<p>", htmlSpecialChars($errstr), "</p>";
            $page = Nette::getPage();

            if (NETTE_MODE === 'DEBUG') {
                echo "<h3>Debug Backtrace</h3>";
                self::printTrace(debug_backtrace());
                if ($page) {
                    echo "<h3>Page: ", get_class($page), "</h3><xmp>";
                    print_r($page->getParams());
                    echo "</xmp>";
                }
            } else {
                // log output
                ob_start();
                echo "User error: $errstr in $errfile on line $errline\n\n", NHttpRequest::getMethod(), " ", NHttpRequest::getURI(), "\n\n";
                self::printTrace(debug_backtrace(), FALSE);
                if ($page) {
                    echo "Page: ", get_class($page), "\n";
                    print_r($page->getParams());
                }
                file_put_contents(NETTE_LOG_DIR . '/error ' . date('Y-m-d H-i-s ') . substr(microtime(FALSE), 2, 6) . '.txt', ob_get_clean());
            }
            NHttpResponse::fixIE();
            exit;
        }

        if (($errno & error_reporting()) == $errno) {
            // if (function_exists('debugbreak')) debugbreak(); // !!!
            $types = array(
                E_RECOVERABLE_ERROR => 'Recoverable error',  // PHP 5.2
                E_WARNING => 'Warning',
                E_NOTICE => 'Notice',
                E_USER_WARNING => 'User warning',
                E_USER_NOTICE => 'User notice',
                E_STRICT => 'Strict',
            );
            if (NETTE_MODE === 'DEBUG') {
                echo '<b>', $types[$errno], ':</b> ', $errstr, ' in <b>', $errfile, '</b> on line <b>', $errline, '</b><br>';
            } else {
                // log output
                ob_start();
                echo $types[$errno], ": $errstr in $errfile on line $errline\n\n", NHttpRequest::getMethod(), " ", NHttpRequest::getURI(), "\n\n";
                self::printTrace(debug_backtrace(), FALSE);
                file_put_contents(NETTE_LOG_DIR . '/error ' . date('Y-m-d H-i-s ') . substr(microtime(FALSE), 2, 6) . '.txt', ob_get_clean());
            }
        }
    }



    /**
     * Prints debug backtrace in readable form
     * @author David Grudl -dgx-
     * @param  array trace
     * @return void
     */
    public static function printTrace($trace, $html = TRUE)
    {
        if ($html) {
            echo '<pre style="color: black; background: white; font-size: 12px; text-align:left">';
        }

        $index = 0;
        foreach ($trace as $key => $t) {
            $index++;
            printf('#%-2s ', $index);

            // file
            $source = FALSE;
            if (isset($t['file'])) {
                if ($html) {
                    printf("%-46s",
                        htmlSpecialChars(basename(dirname($t['file'])))
                       . '/<b>' . htmlSpecialChars(basename($t['file']))
                       . '</b>(' . $t['line'] . ')');
                } else {
                    printf("%-46s", $t['file'] . '(' . $t['line'] . ')');
                }

                // try to receive source code snippet
                if (is_readable($t['file'])) {
                    $file = file($t['file']);
                    if (isset($file[ $t['line']-1 ])) {
                       $source = trim($file[ $t['line']-1 ]);
                       if ($source > 100) $source = substr(0, 100) . '...';
                    }
                    unset($file);
                }
            } else {
                printf("%-46s", $html ? '&lt;PHP inner-code&gt;' : '<PHP inner-code>');
            }

            // class, method, function
            if (isset($trace[$key+1])) {
                $t2 = $trace[$key+1];

                echo ' in ';

                if (isset($t2['class'])) {
                    echo $t2['class'] . $t2['type'];
                }

                echo $t2['function'];

                // and arguments
                if (isset($t2['args']) && count($t2['args']) > 0) {
                    foreach ($t2['args'] as &$arg) {
                        if (is_null($arg)) $arg = 'NULL';
                        elseif (is_bool($arg)) $arg = $arg ? 'TRUE' : 'FALSE';
                        elseif (is_array($arg)) $arg = 'array('.count($arg).')';
                        elseif (is_object($arg)) $arg = 'object('.get_class($arg).')';
                        else {
                            $arg = preg_replace("#\s#", " ", (string) $arg);
                            if (strlen($arg) > 40) {
                                $arg = substr($arg, 0, 37) . '...';
                            }
                            $arg = $html ? "'" . htmlSpecialChars($arg) . "'" : "'$arg'";
                        }
                    }
                    echo '( ' . implode(', ', $t2['args']) .  ' )';
                } else {
                    echo '()';
                }
            }

            // source code snippet
            if ($source) {
                echo "\n    ";
                echo $html ? "<span style='color:gray'>".htmlSpecialChars($source).'</span>' : $source;
            }

            echo "\n\n";
        }
        if ($html) {
            echo "</pre>";
        }
    }



    public static function dump($var, $detailed = FALSE)
    {
        ob_start();
        if ($detailed) {
            var_dump($var);
        } else {
            print_r($var);
        }
        $dump = ob_get_clean();
        echo '<pre style="color: black; background: white; font-size: 12px; text-align:left">',
             htmlSpecialChars($dump),
             '</pre>';
    }



    public static function timer()
    {
        static $time = 0;
        $now = microtime(TRUE);
        $delta = $now - $time;
        $time = $now;
        return $delta;
    }

}
