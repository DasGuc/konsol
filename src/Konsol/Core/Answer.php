<?php

namespace Konsol\Core;

class Answer
{
    protected $id;
    protected $value;
    protected $subs;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = str_replace(CHR(10), '', $value);
        return $this;
    }

    public function getSubs()
    {
        return $this->subs;
    }

    public function setSubs($subs)
    {
        $this->subs[] = $subs;
        return $this;
    }

    public function addSub($answer)
    {
        $this->subs[] = $answer;
    }
}