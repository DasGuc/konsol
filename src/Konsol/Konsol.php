<?php

    namespace Konsol;

    Use Konsol\Assert\Assert;
    use Konsol\Core\Answer;
    Use Konsol\Core\Save;
    Use Konsol\Cli\Option;
    Use Konsol\Cli\Argument;
    Use Konsol\Core\Update;
    Use Konsol\Help\Help;
    Use Konsol\Render\Color;
    Use Konsol\Question\Question;

    class Konsol
    {
        static $VERBOSE = false;
        static $VERY_VERBOSE = false;
        static $DEBUG = false;

        static $ARGUMENTS = array();
        static $OPTIONS = array();

        protected $arguments = array();
        protected $keys = array();
        protected $options = array();
        protected $questions = array();
        protected $press = array();
        protected $menu = array();
        protected $save;

        protected $counter = array(
            'options' => 0,
            'arguments' => 0,
            'questions' => 0,
            'press' => 0,
            'menu' => 0
        );

        protected $konsol = array(
            'update',
            'generate'
        );

        public function __construct()
        {
            system('clear');

            $this->save = new Save();
            $this->parse($_SERVER['argv']);
        }

        public function add()
        {
            $opts = func_get_args();
            foreach ($opts as $opt) {
                $type = gettype($opt);

                if ($type == 'object') {
                    if (get_class($opt) == 'Konsol\\Cli\\Option') {
                        $this->options[] = $opt;
                        $this->counter['options']++;
                    } elseif (get_class($opt) == 'Konsol\\Cli\\Argument') {
                        $this->options[] = $opt;
                        $this->counter['arguments']++;
                    } elseif (get_class($opt) == 'Konsol\\Cli\\Question') {
                        $this->questions[] = $opt;
                        $this->counter['questions']++;
                    } elseif (get_class($opt) == 'Konsol\\Cli\\Press') {
                        $this->press[] = $opt;
                        $this->counter['press']++;
                    } elseif (get_class($opt) == 'Konsol\\Cli\\Menu') {
                        $this->menu[] = $opt;
                        $this->counter['menu']++;
                    }
                } elseif ($type == 'array') {
                    foreach ($opt as $o) {
                        $this->add($o);
                    }
                }
            }
        }

        public function count($c)
        {
            if (!empty($this->counter[$c])) {
                return $this->counter[$c];
            }

            return 0;
        }

        public function countOptions()
        {
            return $this->counter['options'];
        }

        public function countArguments()
        {
            return $this->counter['arguments'];
        }

        public function getKeys()
        {
            return $this->keys;
        }

        public function getArguments()
        {
            return $this->arguments;
        }

        public function getConsoleName()
        {
            if (!empty($this->arguments[0])) {
                return $this->arguments[0];
            } elseif (!empty($this->arguments['--help']) || !empty($this->arguments['-h'])) {
                if (count($this->arguments)==1) {
                    if (!empty($this->arguments['--help'])) {
                        return $this->arguments['--help'];
                    } elseif (!empty($this->arguments['-h'])) {
                        return $this->arguments['-h'];
                    }
                }
            }
        }

        public function run()
        {
            $help = new Help();
            $colors = new Color();
            $assert = new Assert($this->keys, $this->arguments);

            if (($h = $assert->needHelp()) != false) {
                $help->showHelp($h);
            } elseif (($h = $assert->needVersion()) != false) {
                $help->showVersion($h);
            } else {
                if(count($this->arguments)==0) {
                    $help->showHelp();
                } else {
                    if (preg_match('/^(.*)\/(.*)$/i', $this->getConsoleName(), $m)) {

                        if ($assert->needVerbose()) {
                            self::$VERBOSE = true;
                        } elseif ($assert->needVeryVerbose()) {
                            self::$VERY_VERBOSE = true;
                        } elseif ($assert->needDebug()) {
                            self::$DEBUG = true;
                        }

                        try {
                            if (file_exists($this->getConsoleName())) {
                                $data = \Spyc::YAMLLoad($this->getConsoleName());

                                $this->compare();

                                if ($this->count('press')>0) {
                                    $this->press();
                                }

                                if ($this->count('menu')>0) {
                                    $this->menu();
                                }

                                if ($this->count('question')>0) {
                                    $this->ask();
                                }
                            } else {
                                $help->showHelp($this->getConsoleName());
                            }
                        } catch (\ErrorException $e) {
                            $usage = true;

                            if ($e->getCode() == '101') {
                                $usage = false;
                            }

                            $help->showError($this->arguments[0], $e->getMessage(), $usage);
                        }
                    } else {
                        if (in_array($this->getConsoleName(), $this->konsol)) {
                            switch($this->getConsoleName()) {
                                case 'generate':
                                    // Add Arguments & Options

                                    break;

                                case 'update':
                                    // Add option
                                    try {
                                        $o = new Option('number');
                                        $o->setAlias('n');
                                        $o->setDefault('latest');
                                        $this->add($o);

                                        $o = new Option('verbose');
                                        $o->setAlias('-v');
                                        $o->setDefault(true);
                                        $o->boolean();
                                        $this->add($o);

                                        if ($this->compare()) {
                                            $upd = new Update(
                                                $this->save->option('number'),
                                                $this->save->option('verbose')
                                            );

                                            $upd->run();
                                        }
                                    } catch (\ErrorException $e) {
                                        $help->showError($this->arguments[0], $e->getMessage());
                                    }
                                    break;
                            }
                        }
                    }
                }
            }
        }

        /**
         * Parse argv
         * @param $args
         */
        protected function parse($args)
        {
            unset($args[0]);
            $args = array_values($args);

            $k = 0;
            $keyMatch = false;
            foreach ($args as $arg) {
                if (preg_match('/\-/i', $arg, $matches)) {
                    if ($keyMatch) {
                        $this->arguments[$keyMatch] = true;
                    }

                    $keyMatch = $arg;
                } else {
                    if ($keyMatch) {
                        $this->arguments[$keyMatch] = $arg;
                        $keyMatch = false;
                    } else {
                        $this->arguments[$k++] = $arg;
                    }
                }
            }

            if ($keyMatch) {
                $this->arguments[$keyMatch] = true;
            }

            $this->keys = array_keys($this->arguments);
        }

        /**
         * Compare arguments between options
         */
        protected function compare()
        {
            $arguments = $this->arguments;
            unset($arguments[0]);

            $args = $opts = array();
            foreach ($arguments as $k => $a) {
                if (preg_match('/^([0-9]+)$/', $k, $m)) {
                    $args[] = $a;
                } else {
                    $opts[$k] = $a;
                }
            }

            $a = $o = 0;
            foreach ($this->options as $key => $options) {
                $value = $be = $default = $regexp = false;

                $type = $options->getType();
                $be = $options->getBe();
                $default = $options->getDefault();
                $regexp = $options->getRegexp();

                if ($options->getMode() == 'argument') {
                    $name = $options->getName();

                    if ($options->isRequire() && empty($args[$a])) {
                        if ($default) {
                            $value = $default;
                        } else {
                            throw new \ErrorException('Argument "' . $name . '" missing');
                        }
                    }

                    if (!empty($args[$a])) {
                        $value = $args[$a];
                    }

                    $a++;
                } elseif ($options->getMode() == 'option') {
                    $alias = $options->getAlias();
                    $alias[] = $options->getName();
                    $name = str_replace('--', '', $options->getName());

                    $find = false;
                    foreach ($alias as $al) {
                        if (in_array($al, array_keys($opts))) {
                            $find = $al;
                        }
                    }

                    if ($options->isRequire() && !$find) {
                        if ($default) {
                            $value = $default;
                        } else {
                            throw new \ErrorException('Option "' . str_replace('--', '', $name) . '" missing');
                        }
                    }

                    if ($find && !empty($opts[$find])) {
                        $value = $opts[$find];
                    }
                }

                if ($value) {
                    $assert = new Assert($this->keys, $this->arguments);

                    if ($type == 'string') {
                        if (!$assert->isString($value)) {
                            throw new \ErrorException(ucfirst($options->getMode()).' format for "' . $options->getName() . '" not matching (string needed)');
                        }
                    } elseif ($type == 'int') {
                        if (!$assert->isInt($value)) {
                            throw new \ErrorException(ucfirst($options->getMode()).' format for "' . $options->getName() . '" not matching (int needed)');
                        }
                    } elseif ($type == 'boolean') {
                        if (!$assert->isBoolean($value)) {
                            throw new \ErrorException(ucfirst($options->getMode()).' format for "' . $options->getName() . '" not matching (boolean needed)');
                        }
                    } elseif ($type == 'directory') {
                        if (!$assert->isDirectory($value)) {
                            throw new \ErrorException('Directory "' . $value . '" does not exist');
                        }
                    } elseif ($type == 'file') {
                        if (!$assert->isFile($value)) {
                            throw new \ErrorException('File "' . $value . '" does not exist');
                        }
                    }

                    if ($regexp) {
                        if (!$assert->isRegexp($value, $regexp)) {
                            throw new \ErrorException(ucfirst($options->getMode()).' "' . $value . '" value not matching with regular expression');
                        }
                    }
                }

                if ($value && $be) {
                    if (!$options->isBe($value)) {
                        $end = end($be);
                        unset($be[count($be)-1]);
                        throw new \ErrorException('incorrect "'.$name.'" value. Value must be '.implode(', ', $be).' or '.$end);
                    }
                }

                if (!$value && $default) {
                    $value = $default;
                }

                if ($options->getMode() == 'argument') {
                    $this->save->setArgument($name, $value);
                } elseif ($options->getMode() == 'option') {
                    $this->save->setOption($name, $value);
                }
            }

            self::$ARGUMENTS = $this->save->getArguments();
            self::$OPTIONS = $this->save->getOptions();

            return true;
        }

        /**
         * Press
         */
        protected function press()
        {
            foreach ($this->press as $press) {
                $press->display();
            }
        }

        /**
         * Menu
         */
        protected function menu()
        {
            foreach ($this->menu as $menu) {
                $menu->display();
            }
        }

        /**
         * Ask question
         */
        protected function ask()
        {
            $answer = array();
            foreach ($this->questions as $questions) {

                $answerQuestion = new Answer($questions->getId());

                do {
                    if (($a = $questions->ask()) != false) {
                        $answerQuestion->setValue($a);
                    }

                    if ($questions->haveSubs() && $questions->isLoop()) {
                        foreach ($questions->getSubs() as $subQuestion) {
                            $answerSubQuestion = new Answer($subQuestion->getId());

                            do {
                                if (($a = $subQuestion->ask()) != false) {
                                    $answerSubQuestion->setValue($a);
                                }
                            } while ($subQuestion->isLoop());

                            $answerQuestion->setSubs($answerSubQuestion);
                            $subQuestion->resetLoop();
                        }
                    }
                } while($questions->isLoop());

                $answer[] = $answerQuestion;
                $questions->resetLoop();
            }
        }
    }