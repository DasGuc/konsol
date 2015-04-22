<?php

namespace Konsol\Core;

use Konsol\Cli\Argument;
use Konsol\Cli\Callback;
use Konsol\Cli\Menu;
use Konsol\Cli\Option;
use Konsol\Cli\Press;
use Konsol\Cli\Question;

class Create
{
    public function __construct()
    {
    }

    public function argument($name, $args)
    {
        $o = new \Konsol\Cli\Argument($name, true);

        foreach ($args as $key => $arg) {
            if ($key === 'description') {
                $o->setDescription($arg);
            } elseif ($key === 'be'){
                $o->setBe($arg);
            } elseif ($key === 'default'){
                $o->setDefault($arg);
            } elseif ($key === 'regexp'){
                $o->setRegexp($arg);
            } elseif (in_array($arg, array('string', 'int', 'boolean', 'directory', 'file'))) {
                $o->setType($arg);
            }
        }

        return $o;
    }

    public function option($name, $opts)
    {
        $o = new \Konsol\Cli\Option($name);

        foreach ($opts as $key => $opt) {
            if ($key === 'description') {
                $o->setDescription($opt);
            } elseif($key === 'alias') {
                if (is_array($opt)) {
                    foreach ($opt as $op) {
                        $o->setAlias($op);
                    }
                } else {
                    $o->setAlias($opt);
                }
            } elseif ($key === 'be'){
                $o->setBe($opt);
            } elseif ($key === 'default'){
                $o->setDefault($opt);
            } elseif ($opt === 'require') {
                $o->require();
            } elseif ($key === 'regexp'){
                $o->setRegexp($opt);
            }  elseif (in_array($opt, array('string', 'int', 'boolean', 'directory', 'file'))) {
                $o->setType($opt);
            }
        }

        return $o;
    }

    public function callback($array)
    {
        $c = new \Konsol\Cli\Callback();

        foreach ($array as $key => $val) {
            if ($key === 'file') {
                $c->setFile($val);
            } elseif ($key === 'class') {
                $c->setClass($val);
            } elseif ($key === 'method') {
                $c->setMethod($val);
            } elseif ($key === 'arguments') {
                $c->setArguments($val);
            } elseif ($key === 'loader') {
                $c->setLoader($val);
            } elseif ($val === 'loader') {
                $c->setLoader('spin');
            } elseif ($key === 'thread') {
                $c->setThread($val);
            } elseif ($val === 'thread') {
                $c->setThread(true);
            }
        }

        return $c;
    }

    public function press($array)
    {
        $p = new \Konsol\Cli\Press();

        foreach ($array as $key => $val) {
            if ($key === 'key') {
                $p->setKey($val);
            } elseif ($key === 'text') {
                $p->setText($val);
            } elseif ($key === 'abort') {
                $p->setAbort($val);
            }
        }

        return $p;
    }

    public function menu($array)
    {
        $m = new \Konsol\Cli\Menu();

        foreach ($array as $key => $val) {
            if ($key === 'items') {
                $m->setItems($val);
            } elseif ($key === 'mode') {
                $m->setMode($val);
            } elseif ($key === 'text') {
                $m->setText($val);
            } elseif ($key === 'abort') {
                $m->setAbort($val);
            } elseif ($val === 'abort') {
                $m->setAbort(true);
            } elseif ($key === 'quit') {
                $m->setQuit($val);
            } elseif ($val === 'quit') {
                $m->setQuit(true);
            }
        }

        return $m;
    }

    public function question($name, $array)
    {
        $q = new \Konsol\Cli\Question($name);

        foreach ($array as $key => $val) {
            if ($key === 'text') {
                $q->setText($val);
            } elseif ($key === 'default') {
                $q->setDefault($val);
            } elseif ($key === 'rules') {
                $q->setRules($val);
            } elseif ($key === 'password') {
                $q->setPassword($val);
            } elseif ($key === 'loop') {
                $q->setLoop($val);
            } elseif ($val === 'loop') {
                $q->setLoop('*');
            } elseif ($val === 'password') {
                $q->setPassword(true);
            } elseif ($val === 'require') {
                $q->setRequire(true);
            } elseif ($key === 'autocomplete') {
                $q->setAutocomplete($val);
            } elseif ($key === 'regex') {
                $q->setRegex($val);
            } elseif ($key === 'callback') {
                $c = $this->callback($val);
                $q->setCallback($c);
            } elseif ($key === 'subs') {
                foreach ($val as $subKey => $subVal) {
                    $subQ = $this->question($subKey, $subVal);
                    $q->setSubs($subQ);
                }
            }
        }

        return $q;
    }
}