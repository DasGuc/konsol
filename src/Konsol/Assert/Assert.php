<?php

namespace Konsol\Assert;

class Assert
{
    protected $keys;
    protected $args;

    public function __construct($keys, $args)
    {
        $this->keys = $keys;
        $this->args = $args;
    }

    public function needVersion($keys=false, $args=false)
    {
        $arg  = false;
        $keys = (!$keys) ? $this->keys : $keys;
        $args = (!$args) ? $this->args : $args;

        foreach ($keys as $k) {
            if ($k!='0') {
                if ($k == '--version') {
                    $arg = $args['--version'];
                } elseif ($k == '-V') {
                    $arg = $args['-V'];
                }
            }
        }

        if (!empty($arg) && is_bool($arg)) {
            foreach($args as $a) {
                if (preg_match('/^([a-z]+)\/([a-z]+)$/i', $a, $m)) {
                    $arg = $a;
                    break;
                }
            }
        }

        return $arg;
    }

    public function needHelp($keys=false, $args=false)
    {
        $arg  = false;
        $keys = (!$keys) ? $this->keys : $keys;
        $args = (!$args) ? $this->args : $args;

        foreach ($keys as $k) {
            if ($k!='0') {
                if ($k == '--help') {
                    $arg = $args['--help'];
                } elseif ($k == '-h') {
                    $arg = $args['-h'];
                }
            }
        }

        if (!empty($arg) && is_bool($arg)) {
            foreach($args as $a) {
                if (preg_match('/^([a-z]+)\/([a-z]+)$/i', $a, $m)) {
                    $arg = $a;
                    break;
                }
            }
        }

        return $arg;
    }

    public function needDebug($keys=false, $args=false)
    {
        $arg  = false;
        $keys = (!$keys) ? $this->keys : $keys;
        $args = (!$args) ? $this->args : $args;

        foreach ($keys as $k) {
            if ($k!='0') {
                if ($k == '--debug') {
                    $arg = $args['--debug'];
                } elseif ($k == '-vvv') {
                    $arg = $args['-vvv'];
                }
            }
        }

        if (!empty($arg) && is_bool($arg)) {
            foreach($args as $a) {
                if (preg_match('/^([a-z]+)\/([a-z]+)$/i', $a, $m)) {
                    $arg = $a;
                    break;
                }
            }
        }

        return $arg;
    }

    public function needVeryVerbose($keys=false, $args=false)
    {
        $arg  = false;
        $keys = (!$keys) ? $this->keys : $keys;
        $args = (!$args) ? $this->args : $args;

        foreach ($keys as $k) {
            if ($k!='0') {
                if ($k == '--very-verbose') {
                    $arg = $args['--very-verbose'];
                } elseif ($k == '-vv') {
                    $arg = $args['-vv'];
                }
            }
        }

        if (!empty($arg) && is_bool($arg)) {
            foreach($args as $a) {
                if (preg_match('/^([a-z]+)\/([a-z]+)$/i', $a, $m)) {
                    $arg = $a;
                    break;
                }
            }
        }

        return $arg;
    }

    public function needVerbose($keys=false, $args=false)
    {
        $arg  = false;
        $keys = (!$keys) ? $this->keys : $keys;
        $args = (!$args) ? $this->args : $args;

        foreach ($keys as $k) {
            if ($k!='0') {
                if ($k == '--verbose') {
                    $arg = $args['--verbose'];
                } elseif ($k == '-v') {
                    $arg = $args['-v'];
                }
            }
        }

        if (!empty($arg) && is_bool($arg)) {
            foreach($args as $a) {
                if (preg_match('/^([a-z]+)\/([a-z]+)$/i', $a, $m)) {
                    $arg = $a;
                    break;
                }
            }
        }

        return $arg;
    }

    public function isInt($v)
    {
        if (preg_match('/^([0-9]+)$/i', $v, $m)) {
            return true;
        }

        return false;
    }

    public function isString($v)
    {
        if (preg_match('/([a-z.-_]+)/i', $v, $m)) {
            return true;
        }

        return false;
    }

    public function isBoolean($v)
    {
        if ($v === true || $v === false) {
            return true;
        }

        return false;
    }

    public function isDirectory($v)
    {
        return is_dir($v);
    }

    public function isFile($v)
    {
        return file_exists($v);
    }

    public function isRegexp($v, $r)
    {
        if (preg_match('/'.$r.'/i', $v, $m)) {
            return true;
        }

        return false;
    }

    public function isEmpty($v)
    {
        if (empty($v)) {
            return true;
        }

        return false;
    }

    public function isNotEmpty()
    {

    }
}