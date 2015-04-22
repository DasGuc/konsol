<?php

namespace Konsol\Cli;

use Konsol\Core\Thread;
use Konsol\Konsol;
use Konsol\Loader\Loader;
use Konsol\Render\Color;

class Callback
{
    protected $file = false;
    protected $namespace = false;
    protected $class = false;
    protected $autoload = false;
    protected $method = false;
    protected $arguments = false;
    protected $thread = false;
    protected $loader = false;

    private $modules = array(
        'progress'  => 'Konsol\Render\Progress',
        'figlet'    => 'Konsol\Render\Figlet',
        'html'      => 'Konsol\Render\Html',
        'markedown' => 'Konsol\Render\Markedown',
        'color'     => 'Konsol\Render\Color',
        'dot'       => 'Konsol\Loader\Dot',
        'spint'     => 'Konsol\Loader\Spin',
        'thread'    => 'Konsol\Core\Thread'
    );

    const ERROR_FILE = '<error>File not found</error>';
    const ERROR_THREAD = '<error>Threads not supported</error>';
    const ERROR_LOADER = '<error>Loader not found</error>';
    const ERROR_CLASS = '<error>Class not exist</error>';
    const ERROR_METHOD = '<error>Method not exist</error>';

    public function __construct()
    {
    }

    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    public function getFile(){
        return $this->file;
    }

    public function setThread($thread)
    {
        $this->thread = $thread;
        return $this;
    }

    public function getThread()
    {
        return $this->thread;
    }

    public function setLoader($loader)
    {
        $this->loader = $loader;
        return $this;
    }

    public function getLoader()
    {
        return $this->loader;
    }

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }

    public function getNamespace(){
        return $this->namespace;
    }

    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    public function getClass(){
        return $this->class;
    }

    public function setAutoload($autoload)
    {
        $this->autoload = $autoload;
        return $this;
    }

    public function getAutoload(){
        return $this->autoload;
    }

    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    public function getMethod(){
        return $this->method;
    }

    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
        return $this;
    }

    public function getArguments(){
        return $this->arguments;
    }

    public function call($question)
    {
        $color = new Color();
        $threaded = false;
        $arguments = false;
        $return = false;

        try {
            if ($this->autoload && file_exists($this->autoload)) {
                require_once $this->autoload;
            }

            if ($this->file && file_exists($this->file)) {
                require_once $this->file;
            }

            $class = "\\" . $this->class;

            if ($this->namespace) {
                $class = '\\' . $this->namespace . $class;
            }

            $addLoaderArgument = false;

            if (class_exists($class)) {
                if ($this->arguments) {
                    $arguments = array();

                   foreach ($this->arguments as $args) {
                       if (preg_match('/^%([a-zA-Z]+).(.*)%$/', $args, $m)) {
                           list($scheme, $obj, $var) = $m;
                           $var = 'get'.ucfirst(strtolower($var));

                           if ($obj!='this') {
                               if (!empty($$obj)) {
                                   if (is_callable(array($$obj, $var))) {
                                       $arguments[] = $$obj->{$var}();
                                   }
                               }
                           } else {
                               if (is_callable(array($this, $var))) {
                                   $arguments[] = $this->{$var}();
                               }
                           }
                       } elseif (preg_match('/^@(.*)@$/', $args, $m)) {
                           list($scheme, $obj) = $m;

                           if (in_array($obj, array_keys($this->modules))) {
                               $arguments[] = new $this->modules[$obj];
                           } elseif ($obj === 'loader') {
                               $addLoaderArgument = true;
                           }
                       } else {
                           $arguments[] = $args;
                       }
                   }
                }

                $call = new $class;

                if (is_callable(array($call, $this->method))) {
                    $object = array($call, $this->method);
                    if ($this->thread) {
                        if (!Thread::isAvailable()) {

                            $thread = new Thread($object);

                            if ($this->loader &&
                                !Konsol::$VERBOSE &&
                                !Konsol::$VERY_VERBOSE &&
                                !Konsol::$DEBUG ) {

                                if (is_array($this->loader)) {
                                    if (!empty($this->loader['type'])) {
                                        $type = $this->loader['type'];
                                    } else {
                                        $type = 'spin';
                                    }

                                    $loader = new Loader($type);
                                    foreach ($this->loader as $key => $load) {
                                        if ($key === 'delay') {
                                            $loader->setDelay($load);
                                        } elseif ($key === 'step') {
                                            $loader->setStep($load);
                                        } elseif ($key === 'n') {
                                            $loader->setN($load);
                                        } elseif ($key === 'color') {
                                            $loader->setColor($load);
                                        } elseif ($load === 'percent') {
                                            $loader->setPercent(true);
                                        }  elseif ($key === 'percent') {
                                            $loader->setPercent($load);
                                        }
                                    }
                                } else {
                                    $loader = new Loader($this->loader, 100);
                                }

                                $loader->start();
                            }

                            if ($addLoaderArgument) {
                                $arguments[] = $loader;
                            }

                            if (!empty($arguments)) {
                                $threaded = true;
                                $return = call_user_func_array(array($thread, 'start'), $arguments);
                            } else {
                                $threaded = true;
                                $return = $thread->start();
                            }

                            while($thread->isAlive()) {
                                if (!empty($loader)) {
                                    $loader->forward();
                                    $loader->step();
                                } else {
                                    sleep(1);
                                }
                            }

                            if (!empty($loader)) {
                                $loader->finish();
                                unset($loader);
                            }
                        }
                    }

                    if (!$threaded) {
                        if (!empty($arguments)) {
                            $return = call_user_func_array($object, $arguments);
                        } else {
                            $return = call_user_func($object);
                        }
                    }
                } else {
                    throw new \ErrorException(Callback::ERROR_METHOD);
                }
            } else {
                throw new \ErrorException(Callback::ERROR_CLASS);
            }
        } catch(\ErrorException $e) {
            print $color->print_c($e->getMessage());
        }

        echo PHP_EOL;
        die;
    }
}