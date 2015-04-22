<?php

namespace Konsol\Question;

Use Konsol\Help\Help;

class Question
{
    protected $questions;
    protected $helper;
    protected $q;
    protected $lastAnswer;
    protected $saveAnswer = array();
    protected $s = 0;
    protected $loop = 0;
    protected $current_loop = 0;
    protected $sloop = 0;
    protected $current_sloop = 0;

    public function __construct($q)
    {
        $this->questions = $q;
        $this->helper = new Help();
    }

    public function run()
    {
        if (count($this->questions)>0) {
            $i = 0; $c = count($this->questions);
            foreach ($this->questions as $q => $question) {
                $this->ask($q);

                $this->s++;
                $i++;
            }

            echo PHP_EOL;
        }
    }

    public function getLastAnswer($id)
    {
        $sid = false;
        $exp = explode('.', $id);

        if (count($exp)>1) {
            $id = $exp[0];
            $sid = 'q'.$exp[1];
        }

        $last = count($this->saveAnswer)-1;

        if (!empty($this->saveAnswer[$last])) {
            $ans = $this->saveAnswer[$last];

            if($ans->getId() == $id) {
                if ($sid) {
                    $sub = $ans->getSubs();
                    $lst = count($sub)-1;

                    if (!empty($sub[$lst])) {
                        $ans_sub = $sub[$lst];
                        if($ans_sub->getId() == $sid) {
                            return $ans_sub->getValue();
                        }
                    }
                } else {
                    return $ans->getValue();
                }
            }
        }
    }

    public function getAnswer()
    {
        $answer = array();
        foreach ($this->saveAnswer as $q => $ans) {
            if (count($ans)>1) {
                $answer[$q] = $ans;
            } else {
                if (!empty($ans)) {
                    $answer[$q] = $ans;
                } else {
                    $answer[$q] = null;
                }
            }
        }

        return $answer;
    }

    public function ask($q, $qq = false)
    {
        $res = true;

        if ($qq) {
            $default = (!empty($this->questions[$q]['subs'][$qq]['default'])) ? $this->questions[$q]['subs'][$qq]['default'] : false;
            $rules = (!empty($this->questions[$q]['subs'][$qq]['rules'])) ? $this->questions[$q]['subs'][$qq]['rules'] : false;
        } else {
            $default = (!empty($this->questions[$q]['default'])) ? $this->questions[$q]['default'] : false;
            $rules = (!empty($this->questions[$q]['rules'])) ? $this->questions[$q]['rules'] : false;
        }

        if ($rules) {
            $reg = false;
            $exp1 = explode('==', $rules);
            $exp2 = explode('!=', $rules);
            $exp3 = explode('>=', $rules);
            $exp4 = explode('<=', $rules);
            $exp5 = explode('>', $rules);
            $exp6 = explode('<', $rules);

            if (count($exp1)>0) {
                list($ex1, $ex2) = $exp1;
                $reg = '==';
            } elseif (count($exp2)>0) {
                list($ex1, $ex2) = $exp2;
                $reg = '!=';
            } elseif (count($exp3)>0) {
                list($ex1, $ex2) = $exp3;
                $reg = '>=';
            } elseif (count($exp4)>0) {
                list($ex1, $ex2) = $exp4;
                $reg = '<=';
            } elseif (count($exp5)>0) {
                list($ex1, $ex2) = $exp5;
                $reg = '>';
            } elseif (count($exp6)>0) {
                list($ex1, $ex2) = $exp6;
                $reg = '<';
            }

            if ($reg) {
                $res = false;
                $ex1 = trim($ex1);
                $ex2 = trim($ex2);

                if (preg_match('/\[|\(|\)|\]|\.|\*|\?|\+|\-/i', $ex2, $m)) {
                    print_r($m);
                } else {
                    $v = $this->getLastAnswer($ex1);
                    eval("\$res = (\"$v\" $reg \"$ex2\");");
                }
            }
        }

        if ($res) {
            // Get keyboard
            $password = false;

            if ($qq) {
                foreach (array_keys($this->questions[$q]['subs'][$qq]) as $data) {
                    if ($data != '0') {
                        if ($data == 'password') {
                            $password = $this->questions[$q]['subs'][$qq]['password'];
                        }
                    }
                }

                if (!$password) {
                    foreach ($this->questions[$q]['subs'][$qq] as $data) {
                        if ($data != '0') {
                            if ($data == 'password') {
                                $password = 'default';
                            }
                        }
                    }
                }
            } else {
                foreach (array_keys($this->questions[$q]) as $data) {
                    if ($data != '0') {
                        if ($data == 'password') {
                            $password = $this->questions[$q]['password'];
                        }
                    }
                }

                if (!$password) {
                    foreach ($this->questions[$q] as $data) {
                        if ($data != '0') {
                            if ($data == 'password') {
                                $password = 'default';
                            }
                        }
                    }
                }
            }

            if ($password) {
                if ($qq) {
                    echo ' ' . $this->helper->question($this->questions[$q]['subs'][$qq]['text'] . (($default) ? ' [' . $default . ']' : '') . ": ");
                } else {
                    echo ' ' . $this->helper->question($this->questions[$q]['text'] . (($default) ? ' [' . $default . ']' : '') . ": ");
                }

                $style = shell_exec('stty -g');
                shell_exec('stty -echo');
                $line = rtrim(fgets(STDIN), "\n");
                shell_exec('stty ' . $style);

                switch ($password) {
                    case 'md5':
                        $line = md5($line);
                        break;
                    case 'sha1':
                        $line = sha1($line);
                        break;
                }
            } else {
                if (is_callable('readline')) {
                    $this->q = $q;
                    $this->qq = $qq;
                    readline_completion_function(function ($input, $index) {
                        if ($this->qq) {
                            return (!empty($this->questions[$this->q]['subs'][$this->qq]['autocomplete'])) ? $this->questions[$this->q]['subs'][$this->qq]['autocomplete'] : null;
                        } else {
                            return (!empty($this->questions[$this->q]['autocomplete'])) ? $this->questions[$this->q]['autocomplete'] : null;
                        }
                    });

                    if ($qq) {
                        $line = readline(' ' . $this->helper->question($this->questions[$q]['subs'][$qq]['text'] . (($default) ? ' [' . $default . ']' : '') . ": "));
                    } else {
                        $line = readline(' ' . $this->helper->question($this->questions[$q]['text'] . (($default) ? ' [' . $default . ']' : '') . ": "));
                    }
                } else {
                    if ($qq) {
                        echo ' ' . $this->helper->question($this->questions[$q]['subs'][$qq]['text'] . (($default) ? ' [' . $default . ']' : '') . ": ");
                    } else {
                        echo ' ' . $this->helper->question($this->questions[$q]['text'] . (($default) ? ' [' . $default . ']' : '') . ": ");
                    }

                    $handle = fopen("php://stdin", "r");
                    $line = fgets($handle);
                }
            }


            $this->answer($q, $qq, $line);
        }
    }

    public function answer($q, $qq = false, $value)
    {
        $skip = $require = $regexp = false;
        if ($value == CHR(10) || empty($value)) {
            if ($qq) {
                if ((empty($this->questions[$q]['subs'][$qq]['skip'])) || (!empty($this->questions[$q]['subs'][$qq]['skip']) && $this->questions[$q]['subs'][$qq]['skip'] == 'enter')) {
                    $skip = true;
                }
            } else {
                if ((empty($this->questions[$q]['skip'])) || (!empty($this->questions[$q]['skip']) && $this->questions[$q]['skip'] == 'enter')) {
                    $skip = true;
                }
            }

            if ($qq) {
                if (!empty($this->questions[$q]['subs'][$qq]['default'])) {
                    // SAVE SUB ANSWER
                    $answer = new Answer($qq);
                    $answer->setValue($this->questions[$q]['subs'][$qq]['default']);
                    $this->saveAnswer[$this->s]->addSub($answer);
                } else {
                    foreach (array_keys($this->questions[$q]['subs'][$qq]) as $data) {
                        if ($data != '0') {
                            if ($data == 'require') {
                                $require = true;
                            }
                        }
                    }

                    if (!$require) {
                        foreach ($this->questions[$q]['subs'][$qq] as $data) {
                            if ($data != '0') {
                                if ($data == 'require') {
                                    $require = true;
                                }
                            }
                        }
                    }
                }
            } else {
                if (!empty($this->questions[$q]['default'])) {
                    // SAVE ANSWER
                    $answer = new Answer($q);
                    $answer->setValue($this->questions[$q]['default']);
                    $this->saveAnswer[$this->s] = $answer;
                } else {
                    foreach (array_keys($this->questions[$q]) as $data) {
                        if ($data != '0') {
                            if ($data == 'require') {
                                $require = true;
                            }
                        }
                    }

                    if (!$require) {
                        foreach ($this->questions[$q] as $data) {
                            if ($data != '0') {
                                if ($data == 'require') {
                                    $require = true;
                                }
                            }
                        }
                    }
                }
            }
        } else {
            if ($qq) {
                foreach (array_keys($this->questions[$q]['subs'][$qq]) as $data) {
                    if ($data != '0') {
                        if ($data == 'regexp') {
                            $regexp = $this->questions[$q]['subs'][$qq][$data];
                        }
                    }
                }
            } else {
                foreach (array_keys($this->questions[$q]) as $data) {
                    if ($data != '0') {
                        if ($data == 'regexp') {
                            $regexp = $this->questions[$q][$data];
                        }
                    }
                }
            }

            if ($regexp) {
                if (preg_match('/'.$regexp.'/i', $value, $m)) {
                    if ($qq) {
                        $answer = new Answer($qq);
                        $answer->setValue($value);
                        $this->saveAnswer[$this->s]->addSub($answer);
                    } else {
                        // SAVE ANSWER
                        $answer = new Answer($q);
                        $answer->setValue($value);
                        $this->saveAnswer[$this->s] = $answer;
                    }
                } else {
                    $require = true;
                }
            } else {
                if ($qq) {
                    // SAVE SUB ANSWER
                    $answer = new Answer($qq);
                    $answer->setValue($value);
                    $this->saveAnswer[$this->s]->addSub($answer);
                } else {
                    // SAVE ANSWER
                    $answer = new Answer($q);
                    $answer->setValue($value);
                    $this->saveAnswer[$this->s] = $answer;
                }
            }
        }

        if ($require) {
            echo PHP_EOL;
            $this->ask($q, $qq);
        } else {
            $this->next($q, $qq, $skip, $value);
        }
    }

    public function next($q, $qq = false, $skip = false, $value = null)
    {
        if ($skip) {
            if ($qq) {
                $this->current_sloop = 0;
            } else {
                $this->current_loop = 0;
            }
        }

        if ($qq) {
            if (!$skip) {

                $loop = false;
                foreach ($this->questions[$q]['subs'][$qq] as $k => $l) {
                    if ($k == 'loop') {
                        $loop = $l;
                    }
                }

                if (in_array('loop', $this->questions[$q]['subs'][$qq]) || $loop) {
                    if ($loop) {
                        $this->sloop = ($loop=='*') ? $loop : $loop-1;
                    } else {
                        $this->sloop = '*';
                    }

                    if ($this->sloop == '*' || $this->current_sloop<$this->sloop) {
                        $this->current_sloop++;
                        $this->ask($q, $qq);
                    } else {
                        if ($this->current_sloop>=$this->sloop) {
                            $this->current_sloop = 0;
                        }
                    }
                }
            }
        } else {
            if (!$skip) {
                $loop = false;
                foreach ($this->questions[$q] as $k => $l) {
                    if ($k == 'loop') {
                        $loop = $l;
                    }
                }

                if (in_array('loop', $this->questions[$q]) || $loop) {
                    if ($loop) {
                        $this->loop = ($loop=='*') ? $loop : $loop-1;
                    } else {
                        $this->loop = '*';
                    }

                    if (!$skip && !empty($this->questions[$q]['subs'])) {
                        foreach ($this->questions[$q]['subs'] as $qq => $data) {
                            $this->ask($q, $qq);
                        }
                    }

                    if ($this->loop == '*' || $this->current_loop<$this->loop) {
                        $this->current_loop++;
                        $this->s++;
                        $this->ask($q);
                    } else {
                        if ($this->current_loop>=$this->loop) {
                            $this->current_loop = 0;
                        }
                    }
                }
            } else {
                if (!$skip) {
                    if (!$skip && !empty($this->questions[$q]['subs'])) {
                        foreach ($this->questions[$q]['subs'] as $qq => $data) {
                            $this->ask($q, $qq);
                        }
                    }
                }
            }
        }
    }
}