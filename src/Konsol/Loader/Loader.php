<?php

namespace Konsol\Loader;

class Loader
{
    protected $type;
    protected $object;

    private $available = array(
        'spin' => 'Konsol\Loader\Spin',
        'dot' => 'Konsol\Loader\Dot'
    );

    public function __construct($type, $delay=200)
    {
        if (in_array($type, array_keys($this->available))) {
            $this->type = $this->available[$type];

            if (class_exists($this->type)) {
                $this->object = new $this->type;
                $this->setDelay($delay);
            }
        }
    }

    public function setType($type)
    {
        if (in_array($type, array_keys($this->available))) {
            $this->type = $this->available[$type];
            return $this;
        }

        return false;
    }

    public function setDelay($delay)
    {
        if (is_object($this->object) && is_callable(array($this->object, 'setDelay'))) {
            $this->object->setDelay($delay);
        }
        return $this;
    }

    public function setStep($step)
    {
        if (is_object($this->object) && is_callable(array($this->object, 'setStep'))) {
            $this->object->setStep($step);
        }
        return $this;
    }

    public function setColor($step)
    {
        if (is_object($this->object) && is_callable(array($this->object, 'setColor'))) {
            $this->object->setColor($step);
        }
        return $this;
    }

    public function setN($step)
    {
        if (is_object($this->object) && is_callable(array($this->object, 'setN'))) {
            $this->object->setN($step);
        }
        return $this;
    }

    public function setPercent($percent)
    {
        if (is_object($this->object) && is_callable(array($this->object, 'setPercent'))) {
            $this->object->setPercent($percent);
        }
        return $this;
    }


    /**
     * Loader function
     */
    public function start()
    {
        if (is_object($this->object) && is_callable(array($this->object, 'start'))) {
            $this->object->start();
        }
    }

    public function stop()
    {
        if (is_object($this->object) && is_callable(array($this->object, 'stop'))) {
            $this->object->stop();
        }
    }

    public function finish()
    {
        if (is_object($this->object) && is_callable(array($this->object, 'finish'))) {
            $this->object->finish();
        }
    }

    public function step($step=1)
    {
        if (is_object($this->object) && is_callable(array($this->object, 'step'))) {
            $this->object->step($step);
        }
    }

    public function forward()
    {
        if (is_object($this->object) && is_callable(array($this->object, 'forward'))) {
            $this->object->forward();
        }
    }
}