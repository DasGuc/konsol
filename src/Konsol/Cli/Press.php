<?php

namespace Konsol\Cli;

use Konsol\Render\Color;
use Konsol\Core\Thread;

class Press
{
    protected $key = 'any';
    protected $abort = false;
    protected $text = 'Press %s key to continue:';

    private $currentAbort = 0;
    private $currentContinue = false;

    public function __construct()
    {
    }

    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setAbort($abort)
    {
        $this->abort = $abort;
        return $this;
    }

    public function getAbort()
    {
        return $this->abort;
    }


    public function listen($key) {
        if (file_exists(($c = __DIR__.'/../../C/keypress'))) {
            $out = shell_exec($c);
            $out = CHR($out);

            if ($key == 'any') {
                if (!empty($out)) {
                    return $key;
                }
            } else {
                if ($out == CHR(10)) {
                    $out = 'enter';
                }

                if ($out == $key) {
                    return $key;
                } else {
                    return $this->listen($key);
                }
            }
        }
    }

    public function display()
    {
        $x = null;
        $color = new Color();

        print $color->print_c(sprintf($this->text, $this->key))." ";

        if (!Thread::isAvailable()) {
            $call = array($this, 'listen');

            $thread = new Thread($call);
            $return = $thread->start($this->key);

            while ($thread->isAlive()) {
                if ($this->abort && (($this->currentAbort++) >= $this->abort)) {
                    print "Script aborted" . PHP_EOL;
                    die;
                }
                usleep(1000);
            }
        }

        echo PHP_EOL;
    }
}