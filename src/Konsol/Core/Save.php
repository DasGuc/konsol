<?php

namespace Konsol\Core;

class Save
{
    protected $arguments = array();
    protected $options = array();
    protected $questions = array();

    public function setArgument($k, $v)
    {
        $this->arguments[$k] = $v;
    }

    public function getArguments()
    {
        if (!empty($this->arguments)) {
            return $this->arguments;
        }

        return false;
    }

    public function getArgument($k)
    {
        if (!empty($this->arguments[$k])) {
            return $this->arguments[$k];
        }

        return false;
    }

    public function argument($k)
    {
        if (!empty($this->arguments[$k])) {
            return $this->arguments[$k];
        }

        return false;
    }

    public function setOption($k, $v)
    {
        $this->options[$k] = $v;
    }

    public function option($k)
    {
        if (!empty($this->options[$k])) {
            return $this->options[$k];
        }

        return false;
    }

    public function getOptions()
    {
        if (!empty($this->options)) {
            return $this->options;
        }

        return false;
    }

    public function getOption($k)
    {
        if (!empty($this->options[$k])) {
            return $this->options[$k];
        }

        return false;
    }

    public function setQuestion($q, $v)
    {
        $this->questions[$q] = $v;
    }

    public function question($k)
    {
        if (!empty($this->questions[$k])) {
            return $this->questions[$k];
        }

        return false;
    }

    public function getQuestion($k)
    {
        if (!empty($this->questions[$k])) {
            return $this->questions[$k];
        }

        return false;
    }
}