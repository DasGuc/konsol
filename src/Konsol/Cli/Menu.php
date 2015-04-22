<?php

namespace Konsol\Cli;

use Konsol\Render\Color;

class Menu
{

    protected $active = '> %s';
    protected $inactive = '  %s';
    protected $scheme = ' [%s] %s';

    protected $items = array();
    protected $abort = false;
    protected $quit = false;
    protected $text;
    protected $mode = 'type';

    private $color;
    private $currentKey = 0;
    private $exitAdded = false;
    private $currentQuit = 'quit';

    public function __construct()
    {
        $this->color = new Color();
    }

    public function setItems($items)
    {
        $this->items = $items;
        return $this;
    }

    public function addItem($items)
    {
        if (is_array($items)) {
            foreach ($items as $itm) {
                $this->addItems($itm);
            }
        } else {
            $this->items[] = $items;
        }
    }

    public function getList()
    {
        return $this->list;
    }

    public function setQuit($quit)
    {
        $this->quit = $quit;
        return $this;
    }

    public function getQuit()
    {
        return $this->quit;
    }

    public function setAbort($abort)
    {
        $this->abort = $abort;
    }

    public function getAbort()
    {
        return $this->abort;
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

    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    public function getMode()
    {
        return $this->mode;
    }

    public function display($error = false)
    {
        $selectedItem = false;
        $typeItems = array();

        system('clear');
        print PHP_EOL;

        if ($this->quit && !$this->exitAdded) {
            $this->items[] = $this->currentQuit;
            $this->exitAdded = true;
        }

        foreach ($this->items as $k => $item) {
            if ($this->mode == 'press') {
                if ($this->currentKey == $k) {
                    print sprintf($this->active, $item) . PHP_EOL;
                } else {
                    print sprintf($this->inactive, $item) . PHP_EOL;
                }
            } else {
                if ($item == $this->currentQuit) {
                    print sprintf($this->scheme, 'q', $item) . PHP_EOL;
                    $typeItems['q'] = $item;
                } else {
                    print sprintf($this->scheme, ($k+1), $item) . PHP_EOL;
                    $typeItems[($k+1)] = $item;
                }
            }
        }

        print PHP_EOL;

        if ($this->mode == 'press') {
            print $this->color->print_c($this->text);
            if (file_exists(($c = __DIR__.'/../../C/keypress'))) {
                $out = shell_exec($c);

                if ($out == 27) {
                    $this->currentKey++;

                    if ($this->currentKey == count($this->items)) {
                        $this->currentKey = 0;
                    }

                    $this->display();
                } else {
                    $selectedItem = $this->items[$this->currentKey];
                }
            }
        } else {
            if ($error) {
                print $this->color->print_c($error).PHP_EOL;
            }

            print $this->color->print_c($this->text.': ');
            $handle = fopen("php://stdin", "r");
            $k = str_replace(CHR(10), '', fgets($handle));

            if (!empty($typeItems[$k])) {
                $selectedItem = $typeItems[$k];
            } else {
                $this->display('<error>Undefined item</error>');
            }
        }

        if ($selectedItem == $this->currentQuit) {
            die;
        }

        return $selectedItem;
    }
}