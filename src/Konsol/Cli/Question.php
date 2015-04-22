<?php

namespace Konsol\Cli;

use Konsol\Render\Color;

class Question
{
    protected $id;
    protected $text;
    protected $loop = 1;
    protected $default = false;
    protected $rules = false;
    protected $regex = false;
    protected $password = false;
    protected $require = false;
    protected $callback = false;
    protected $subs = array();
    protected $autocomplete = array();

    protected $answer = false;

    private $currentLoop = 0;
    private $instantLoop = true;

    const ERROR_REQUIRE = '<error>Answer required</error>';
    const ERROR_REGEX = '<error>Answer bad format</error>';

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

    public function getText()
    {
        return $this->text;
    }

    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    public function getCallback()
    {
        return $this->callback;
    }

    public function setCallback($callback)
    {
        $this->callback = $callback;
        return $this;
    }

    public function getRegex()
    {
        return $this->regex;
    }

    public function setRegex($exp)
    {
        $this->regex = $exp;
        return $this;
    }

    public function isLoop()
    {
        return $this->instantLoop;
    }

    public function resetLoop()
    {
        $this->instantLoop = true;
    }

    public function getLoop()
    {
        return $this->loop;
    }

    public function setLoop($loop)
    {
        $this->loop = $loop;
        return $this;
    }

    public function hasDefault()
    {
        return (!empty($this->default));
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function setDefault($default)
    {
        $this->default = $default;
        return $this;
    }

    public function getRules()
    {
        return $this->rules;
    }

    public function setRules($rules)
    {
        $this->rules = $rules;
        return $this;
    }

    public function isPassword()
    {
        return (!empty($this->password));
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    public function isRequire()
    {
        return $this->require;
    }

    public function getRequire()
    {
        return $this->require;
    }

    public function setRequire($require = true)
    {
        $this->require = $require;
        return $this;
    }

    public function haveSubs()
    {
        return (count($this->subs)>0);
    }

    public function getSubs()
    {
        return $this->subs;
    }

    public function setSubs($subs)
    {
        if (is_array($subs)) {
            foreach($subs as $s) {
                $this->subs[] = $s;
            }
        } else {
            $this->subs[] = $subs;
        }

        return $this;
    }

    public function getAnswer()
    {
        return $this->answer;
    }

    public function haveAutocomplete()
    {
        return (count($this->autocomplete)>0);
    }

    public function getAutocomplete()
    {
        return $this->autocomplete;
    }

    public function setAutocomplete($autocomplete)
    {
        if (is_array($autocomplete)) {
            foreach($autocomplete as $auto) {
                $this->autocomplete[] = $auto;
            }
        } else {
            $this->autocomplete[] = $autocomplete;
        }
        return $this;
    }

    public function reAsk()
    {
        $this->currentLoop = 0;
        $this->ask();
    }

    public function ask()
    {

        if ( $this->loop == '*' ||
             $this->loop>0 && ($this->currentLoop++) < $this->loop ) {

            $color = new Color();

            $d = '';

            if ($this->hasDefault() && preg_match('/%([a-zA-Z]+).(.*)%/', $this->default, $m)) {
                if (in_array($m[1], array('options','option','opts','opt','o'))) {
                    if (!empty($m[2]) && !empty(\Konsol\Konsol::$OPTIONS[$m[2]])) {
                        $this->setDefault(\Konsol\Konsol::$OPTIONS[$m[2]]);
                    } else {
                        $this->setDefault(false);
                    }
                } elseif (in_array($m[1], array('arguments','argument','args','arg','a'))) {
                    if (!empty($m[2]) && !empty(\Konsol\Konsol::$ARGUMENTS[$m[2]])) {
                        $this->setDefault(\Konsol\Konsol::$ARGUMENTS[$m[2]]);
                    } else {
                        $this->setDefault(false);
                    }
                }
            }

            if ($this->hasDefault()) {
                if (preg_match_all('/^\<(.*)\>(.*)(\<\/(.*){0,}\>){0,}$/Um', $this->default, $m)) {
                    $d = str_replace($m[2][0], ' ['.$m[2][0].']', $this->default);
                } else {
                    $d = ' [' . $this->default . ']';
                }
            }

            print sprintf('%s%s: ', $color->print_c($this->text), $color->print_c($d));

            if ($this->isPassword()) {
                $style = shell_exec('stty -g');
                shell_exec('stty -echo');
                $testLine = $line = rtrim(fgets(STDIN), "\n");
                shell_exec('stty ' . $style);

                switch ($this->password) {
                    case 'sha1':
                        $line = sha1($line);
                        break;

                    case 'md5':
                    default:
                        $line = md5($line);
                        break;
                }
            } else {
                $handle = fopen("php://stdin", "r");
                $testLine = $line = fgets($handle);
            }

            if ($this->default && ($testLine == CHR(10) || empty($testLine)))  {
                $line = $testLine = $this->default;
            }

            if ($testLine == CHR(10) || empty($testLine)) {
                if ($this->loop == '*' || $this->loop>1) {
                    $this->instantLoop = false;
                    $this->currentLoop = 0;
                } else {
                    if ($this->require) {
                        if ($this->isPassword()) {
                            echo PHP_EOL;
                        }

                        print $color->print_c(Question::ERROR_REQUIRE).PHP_EOL;
                        return $this->reAsk();
                    }
                }
            } else {
                if ($this->regex) {
                    if (!preg_match($this->regex, $line)) {
                        print $color->print_c(Question::ERROR_REGEX).PHP_EOL;
                        return $this->reAsk();
                    }
                }

                $this->answer = $line;

                if ($this->callback) {
                    $this->callback->call($this);
                    die;
                }

                return $line;
            }
        } else {
            $this->instantLoop = false;
            $this->currentLoop = 0;
        }

        return false;
    }
}