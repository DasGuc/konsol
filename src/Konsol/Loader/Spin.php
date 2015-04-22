<?php

namespace Konsol\Loader;

use Konsol\Render\Color;

class Spin
{
    protected $printer = " %s %s \r";
    protected $scheme = '-\|/';
    protected $position = 0;
    protected $finish = false;
    protected $stop = false;
    protected $delay;
    protected $percent = false;
    protected $step = 0;
    protected $colors;

    protected $currentPercent = '';
    protected $currentStep = 0;

    private $color;

    public function __construct()
    {
        $this->color = new Color();
    }

    public function setColor($color)
    {
        $this->colors = '<'.$color.'>';
    }

    public function setDelay($delay)
    {
        $this->delay = $delay;
    }

    public function setPercent($percent)
    {
        if ($percent) {
            $this->currentPercent = '- 0%';
        } else {
            $this->currentPercent = '';
        }

        $this->percent = $percent;
    }

    public function setStep($step)
    {
        $this->step = $step;
    }

    public function start()
    {
        $this->display();
    }

    public function finish()
    {
        print str_repeat(' ', 100)."\r";
        $this->finish = true;
    }

    public function step($n=1)
    {
        $this->currentStep += $n;

        if ($this->percent) {
            $this->currentPercent = '- '.round(($this->currentStep*100)/$this->step).'%';
        }
    }

    public function display()
    {
        print $this->color->print_c($this->colors . sprintf($this->printer, $this->scheme{$this->position}, $this->currentPercent) . '</>');
    }

    public function forward()
    {
        if (!$this->finish && !$this->stop) {

            $this->position++;
            if ($this->position > strlen($this->scheme) - 1) {
                $this->position = 0;
            }

            $this->display();
            usleep($this->delay * 1000);
        }
    }
}