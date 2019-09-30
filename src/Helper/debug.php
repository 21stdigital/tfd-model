<?php


if (!function_exists('debug')) {
    function debug($log, $headline = '', $error_log = false, $file = '', $line = 0)
    {
        $info = ($file && $line) ? $file . '[' . $line . ']: ' : '';
        if ($error_log) {
            //ob_start();
            if (is_array($log) || is_object($log)) {
                if ($headline) {
                    error_log($info . $headline . ': ' . print_r($log, true));
                } else {
                    error_log($info . print_r($log, true));
                }
            } else {
                if ($headline) {
                    error_log($info . $headline . ': ' . $log);
                } else {
                    error_log($info . $log);
                }
            }
            //ob_end_flush();
        } else {
            echo $info;
            echo '<pre>';
            echo $headline ? $headline . '<br>' : '';
            print_r($log);
            echo '</pre>';
        }
    }
}

if (!function_exists('d_log')) {
    function d_log($log, $headline = '')
    {
        $debug_args = debug_backtrace();
        if (count($debug_args) && $debug_args[0]['function'] == 'd_log') {
            $function_name = $debug_args[0]['function'];
            $line = $debug_args[0]['line'];
            $file = $debug_args[0]['file'];
            debug($log, $headline, true, $file, $line);
        } else {
            debug($log, $headline, true);
        }
    }
}

if (!function_exists('dlog')) {
    function dlog()
    {
        $debug_args = debug_backtrace();
        $info = '';
        if (count($debug_args) && $debug_args[0]['function'] == 'dlog') {
            $function_name = $debug_args[0]['function'];
            $line = $debug_args[0]['line'];
            $file = $debug_args[0]['file'];
            $info = ($file && $line) ? $file . '[' . $line . ']: ' : '';
        }
        $numargs = func_num_args();
        if (true === WP_DEBUG) {
            $arg_list = func_get_args();
            $output = $info;
            for ($i = 0; $i < $numargs; $i++) {
                if (is_array($arg_list[$i]) || is_object($arg_list[$i])) {
                    $output .= print_r($arg_list[$i], true);
                } else {
                    $output .= $arg_list[$i];
                }
                if ($i + 1 < $numargs) {
                    $output .= ', ';
                }
            }
            error_log($output);
        }
    }
}
