<?php

namespace Konsol\Cli;

class Argument
{
    protected $mode = 'argument';
    protected $name;
    protected $type = 'string';
    protected $be = array();
    protected $description;
    protected $default = false;
    protected $require = true;
    protected $regexp = false;

    public function __construct($name, $arg = false)
    {
        if($arg) {
            $this->name = $name;
        } else {
            if (strlen($name) > 1) {
                $this->name = '--' . $name;
            } else {
                $this->name = '-' . $name;
            }
        }
    }

    public function setDefault($default)
    {
        $this->default = $default;
        return $this;
    }

    public function setDescription($desc)
    {
        $this->description = $desc;
        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setRegexp($reg)
    {
        $this->regexp = $reg;
        return $this;
    }

    public function setBe($be)
    {
        if (is_array($be)) {
            $this->be = $be;
        } else {
            $this->be[] = $be;
        }

        return $this;
    }

    public function __call($a, $b)
    {
        if (in_array($a, array('int','boolean','string'))) {
            $this->type = $a;
        }elseif ($a == 'default') {
            $this->default = $b;
        } elseif ($a == 'regexp') {
            $this->regexp = $b;
        }  elseif ($a == 'be' && !empty($b)) {
            if (is_array($b)) {
                foreach ($b as $be) {
                    $this->be[] = $be;
                }
            } else {
                $this->be[] = $b;
            }
        } elseif ($a == 'description' && !empty($b)) {
            $this->description = $b[0];
        }

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getMode()
    {
        return $this->mode;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getType()
    {
        return $this->type;
    }

    public function isRequire()
    {
        return $this->require;
    }

    public function isBe($v)
    {
        return in_array($v, $this->be);
    }

    public function getBe()
    {
        if (count($this->be)>0) {
            return $this->be;
        }

        return false;
    }

    public function getRegexp()
    {
        return $this->regexp;
    }

    public function getDefault()
    {
        return $this->default;
    }
}