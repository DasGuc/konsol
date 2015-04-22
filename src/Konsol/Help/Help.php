<?php

namespace Konsol\Help;

use Colors\Color;
use Packaged\Figlet\Figlet;

class Help
{
    private $show = array();
    private $c;
    private $version = '1.0';

    static $FIGLET = 'speed';
    static $FIGLET_ACTIVE = false;

    static $TITLE_BG = 'green';
    static $TITLE_FG = 'white';

    static $TEXT_BG = 'default';
    static $TEXT_FG = 'white';

    static $H1_BG = 'default';
    static $H1_FG = 'yellow';

    static $H2_BG = 'default';
    static $H2_FG = 'white';

    static $H3_BG = 'default';
    static $H3_FG = 'white';

    static $USAGE_BG = 'default';
    static $USAGE_FG = 'white';

    static $VARIABLE_BG = 'default';
    static $VARIABLE_FG = 'green';

    static $ERROR_BG = 'default';
    static $ERROR_FG = 'red';

    static $QUESTION_BG = 'default';
    static $QUESTION_FG = 'yellow';

    public function title($value)
    {
        $c = new Color();

        $show = array();

        $show[] = PHP_EOL;

        $show[] = ' ';

        $show[] = '' . $c(str_pad('', strlen($value) + 4, ' '))
                ->bg(self::$TITLE_BG);

        $show[] = PHP_EOL;
        $show[] = ' ';

        $show[] = '' . $c('  ' . $value . '  ')
                ->fg(self::$TITLE_FG)
                ->bg(self::$TITLE_BG);

        $show[] = PHP_EOL;
        $show[] = ' ';

        $show[] = '' . $c(str_pad('', strlen($value) + 4, ' '))
                ->bg(self::$TITLE_BG);

        $show[] = PHP_EOL . PHP_EOL;

        if (self::$FIGLET_ACTIVE) {
            $text = new Figlet();

            if (file_exists($text->getFontsDirectory().self::$FIGLET.'.flf')) {
                $text->loadFont(self::$FIGLET);

                if ($text->isFontLoaded()) {
                    $show = array();
                    $show[] = $c($text->render($value))
                        ->fg(self::$TITLE_FG);

                    if (in_array(self::$FIGLET, array('small', 'slant'))) {
                        $show[] = PHP_EOL;
                    }
                }
            }
        }

        return implode('', $show);
    }

    public function text($value)
    {
        $c = new Color();

        return ''.$c($value)
            ->fg(self::$TEXT_FG)
            ->bg(self::$TEXT_BG);
    }

    public function variable($value)
    {
        $c = new Color();

        return ''.$c($value)
            ->fg(self::$VARIABLE_FG)
            ->bg(self::$VARIABLE_BG);
    }

    public function usage($value)
    {
        $c = new Color();

        return ''.$c($value)
            ->fg(self::$USAGE_FG)
            ->bg(self::$USAGE_BG);
    }

    public function h1($value)
    {
        $c = new Color();

        return ''.$c($value)
            ->fg(self::$H1_FG)
            ->bg(self::$H1_BG);
    }

    public function h2($value)
    {
        $c = new Color();

        return ''.$c($value)
            ->fg(self::$H2_FG)
            ->bg(self::$H2_BG);
    }

    public function h3($value)
    {
        $c = new Color();

        return ''.$c($value)
            ->fg(self::$H3_FG)
            ->bg(self::$H3_BG);
    }

    public function error($value)
    {
        $c = new Color();

        return ''.$c($value)
            ->fg(self::$ERROR_FG)
            ->bg(self::$ERROR_BG);
    }

    public function question($value)
    {
        $c = new Color();

        return ''.$c($value)
            ->fg(self::$QUESTION_FG)
            ->bg(self::$QUESTION_BG);
    }

    public function setColors(Array $colors)
    {
        foreach ($colors as $keyc => $datac) {
            if (is_array($datac)) {
                foreach ($datac as $kc => $dc) {
                    switch($keyc.'.'.$kc){
                        case 'title.background':
                            self::$TITLE_BG = $dc;
                            break;

                        case 'title.color':
                            self::$TITLE_FG = $dc;
                            break;

                        case 'text.background':
                            self::$TEXT_BG = $dc;
                            break;

                        case 'text.color':
                            self::$TEXT_FG = $dc;
                            break;

                        case 'h1.background':
                            self::$H1_BG = $dc;
                            break;

                        case 'h1.color':
                            self::$H1_FG = $dc;
                            break;

                        case 'h2.background':
                            self::$H2_BG = $dc;
                            break;

                        case 'h2.color':
                            self::$H2_FG = $dc;
                            break;

                        case 'h3.background':
                            self::$H3_BG = $dc;
                            break;

                        case 'h3.color':
                            self::$H3_FG = $dc;
                            break;

                        case 'usage.background':
                            self::$USAGE_BG = $dc;
                            break;

                        case 'usage.color':
                            self::$USAGE_FG = $dc;
                            break;

                        case 'variable.background':
                            self::$VARIABLE_BG = $dc;
                            break;

                        case 'variable.color':
                            self::$VARIABLE_FG = $dc;
                            break;
                    }
                }
            }

        }
    }

    public function showError($h = false, $error, $usage = true)
    {
        $this->show = array();
        system('clear');

        if (is_string($h)) {
            if (file_exists($h)) {
                $data = \Spyc::YAMLLoad($h);

                if (!empty($data['colors']) && is_array($data['colors'])) {
                    $this->setColors($data['colors']);
                }

                self::$TITLE_BG = 'red';
                self::$TITLE_FG = 'white';

                $title = (!empty($data['title'])) ? $data['title'] : $h;
                $this->show[] = $this->title($title);

                $this->show[] = ' ';
                $this->show[] = $this->error('Error:').' '.$this->error($error);

                if ($usage) {
                    $this->show[] = PHP_EOL . PHP_EOL;

                    $arguments = array();
                    if (!empty($data['arguments'])) {
                        foreach ($data['arguments'] as $k => $d) {
                            if (is_array($d)) {
                                $u = $k;
                            } else {
                                $u = $d;
                            }

                            $arguments[] = $u;
                        }
                    }

                    $options = array();
                    if (!empty($data['options'])) {
                        foreach ($data['options'] as $k => $d) {
                            if (is_array($d)) {
                                $u = $k;
                            } else {
                                $u = $d;
                            }

                            if (in_array('require', $d)) {
                                $options[] = '[--' . $u . ']';
                            } else {
                                $options[] = '[--' . $u . ']';
                            }
                        }
                    }

                    $this->show[] = ' ';
                    $this->show[] = $this->h1('Usage:') . PHP_EOL;
                    $this->show[] = '   ';
                    $this->show[] = $this->usage(
                            'konsol ' .
                            $h .
                            ((count($arguments) > 0) ? ' ' . implode(' ', $arguments) : '') .
                            ((count($options) > 0) ? ' ' . implode(' ', $options) : '')
                        ) . PHP_EOL;

                    $this->show[] = PHP_EOL;
                    $this->show[] = ' ';
                    $this->show[] = $this->h1('Help:') . PHP_EOL;
                    $this->show[] = '   ';
                    $this->show[] = $this->text('Type "konsol ' . $h . ' --help" to display this console help') . PHP_EOL;
                }

                $this->show();
            } else {
                self::$TITLE_BG = 'red';
                self::$FIGLET_ACTIVE = false;

                $title = 'Konsol / '.$h;
                $this->show[] = $this->title($title);

                $this->show[] = ' ';
                $this->show[] = $this->error('Error:').' '.$this->error($error) . PHP_EOL . PHP_EOL;

                $this->show[] = ' ';
                $this->show[] = $this->h1('Usage:') . PHP_EOL;
                $this->show[] = '   ';

                switch ($h) {
                    case 'create':
                        break;

                    case 'update':
                        break;
                }

                $this->show[] = PHP_EOL;
                $this->show[] = ' ';
                $this->show[] = $this->h1('Help:') . PHP_EOL;
                $this->show[] = '   ';
                $this->show[] = $this->text('Type "konsol ' . $h . ' --help" to display this console help');

                $this->show();
            }
        }
    }

    public function showHelp($h = false)
    {
        $c = new Color();
        $this->show = array();

        if (is_string($h)) {
            if (file_exists($h)) {
                $data = \Spyc::YAMLLoad($h);

                if (!empty($data['colors']) && is_array($data['colors'])) {
                    $this->setColors($data['colors']);
                }

                if (!empty($data['figlet'])) {
                    self::$FIGLET_ACTIVE = true;
                    self::$FIGLET = $data['figlet'];
                }

                $title = (!empty($data['title'])) ? $data['title'] : $h;
                $this->show[] = $this->title($title);

                if (!empty($data['description'])) {
                    $this->show[] = ' ';
                    $this->show[] = $this->text($data['description']) . PHP_EOL . PHP_EOL;
                }

                $arguments = array();
                if (!empty($data['arguments'])) {
                    foreach ($data['arguments'] as $k => $d) {
                        if (is_array($d)) {
                            $u = $k;
                        } else {
                            $u = $d;
                        }

                        $arguments[] = $u;
                    }
                }

                $options = array();
                if (!empty($data['options'])) {
                    foreach ($data['options'] as $k => $d) {
                        if (is_array($d)) {
                            $u = $k;
                        } else {
                            $u = $d;
                        }

                        if (in_array('require', $d)) {
                            $options[] = '[--' . $u . ']';
                        } else {
                            $options[] = '[--' . $u . ']';
                        }
                    }
                }

                if (count($arguments) > 0 || count($options) > 0) {
                    $this->show[] = ' ';
                    $this->show[] = $this->h1('Usage:') . PHP_EOL;
                    $this->show[] = '   ';
                    $this->show[] = $this->usage(
                            'konsol ' .
                            $h .
                            ((count($arguments) > 0) ? ' ' . implode(' ', $arguments) : '') .
                            ((count($options) > 0) ? ' ' . implode(' ', $options) : '')
                        ) . PHP_EOL;

                    $calcLength = 0;
                    $showArgs = array();
                    if (count($arguments) > 0) {
                        $showArgs[0][] = PHP_EOL;
                        $showArgs[0][] = ' ';
                        $showArgs[0][] = $this->h1('Arguments:') . PHP_EOL;

                        foreach ($arguments as $arg) {
                            if (strlen($arg) > $calcLength) {
                                $calcLength = strlen($arg);
                            }

                            $showArgs[$arg][] = '   ';
                            $showArgs[$arg][] = $this->variable($arg);
                            $showArgs[$arg][] = '§';

                            if (!empty($data['arguments'][$arg]['description'])) {
                                $showArgs[$arg][] = $this->text($data['arguments'][$arg]['description']);
                            } else {
                                $showArgs[$arg][] = $this->text('Description not found');
                            }

                            $showArgs[$arg][] = PHP_EOL;
                        }
                    }

                    $showOpts = array();

                    $showOpts[0][] = PHP_EOL;
                    $showOpts[0][] = ' ';
                    $showOpts[0][] = $this->h1('Options:') . PHP_EOL;

                    if (count($options) > 0) {
                        foreach ($options as $opt) {
                            $opt = str_replace(array('[', ']'), '', $opt);
                            $optKey = str_replace('--', '', $opt);

                            $optName = $opt;
                            $alias = array();
                            if (!empty($data['options'][$optKey]['alias'])) {
                                if (is_array($data['options'][$optKey]['alias'])) {
                                    foreach ($data['options'][$optKey]['alias'] as $al) {
                                        if (strlen($al) > 1) {
                                            $a = '--' . $al;
                                        } else {
                                            $a = '-' . $al;
                                        }

                                        $alias[] = $a;
                                    }
                                } else {
                                    if (strlen($data['options'][$optKey]['alias']) > 1) {
                                        $a = '--' . $data['options'][$optKey]['alias'];
                                    } else {
                                        $a = '-' . $data['options'][$optKey]['alias'];
                                    }

                                    $alias[] = $a;
                                }

                                if (count($alias) > 0) {
                                    $optName .= ' (' . implode('/', $alias) . ')';
                                }
                            }

                            if (strlen($optName) > $calcLength) {
                                $calcLength = strlen($optName);
                            }

                            $showOpts[$optName][] = '   ';
                            $showOpts[$optName][] = $this->variable($opt);
                            if (count($alias) > 0) {
                                $showOpts[$optName][] = ' ' . $this->text('(' . implode('/', $alias) . ')');
                            }

                            $showOpts[$optName][] = '§';

                            if (!empty($data['options'][$optKey]['description'])) {
                                $showOpts[$optName][] = $this->text($data['options'][$optKey]['description']);
                            } else {
                                $showOpts[$optName][] = $this->text('Description not found');
                            }

                            $showOpts[$optName][] = PHP_EOL;
                        }
                    }

                    // help & version options
                    $optName = '--help (-h)';
                    $showOpts[$optName][] = '   ';
                    $showOpts[$optName][] = $this->variable('--help');
                    $showOpts[$optName][] = ' ' . $this->text('(-h)');
                    $showOpts[$optName][] = '§';
                    $showOpts[$optName][] = $this->text('Display help for this console');
                    $showOpts[$optName][] = PHP_EOL;

                    $optName = '--version';
                    $showOpts[$optName][] = '   ';
                    $showOpts[$optName][] = $this->variable('--version');
                    $showOpts[$optName][] = '§';
                    $showOpts[$optName][] = $this->text('Display version for this console');
                    $showOpts[$optName][] = PHP_EOL;


                    foreach ($showArgs as $arg => $sargs) {
                        if ($arg == '0') {
                            $this->show[] = implode('', $sargs);
                        } else {
                            if (strlen($arg) < $calcLength) {
                                $diffLen = $calcLength - strlen($arg);
                                $this->show[] = str_replace('§', str_pad('', $diffLen + 4, ' '), implode('', $sargs));
                            }
                        }
                    }

                    foreach ($showOpts as $arg => $sargs) {
                        if ($arg == '0') {
                            $this->show[] = implode('', $sargs);
                        } else {
                            if (strlen($arg) < $calcLength) {
                                $diffLen = $calcLength - strlen($arg);
                                $this->show[] = str_replace('§', str_pad('', $diffLen + 4, ' '), implode('', $sargs));
                            } else {
                                $this->show[] = str_replace('§', str_pad('', 4, ' '), implode('', $sargs));
                            }
                        }
                    }

                    if (!empty($data['help'])) {
                        $this->show[] = PHP_EOL;
                        $this->show[] = ' ';
                        $this->show[] = $this->h1('Help:') . PHP_EOL;
                        $this->show[] = '   ';
                        $this->show[] = $this->text($data['help']);
                    }
                }
            } else {
                self::$TITLE_BG = 'red';

                $title = 'Konsol';
                $this->show[] = $this->title($title);
                $this->show[] = ' ' . $this->error('Error: This console "'.$h.'" does not exist');
            }
        } else {
            // General help
            self::$FIGLET_ACTIVE = true;

            $title = 'Konsol';
            $this->show[] = $this->title($title);
            $this->show[] = ' ' . $c('Usage:')->yellow() . PHP_EOL;
            $this->show[] = '  ' . $c('konsol directory/console [options]')->white() . PHP_EOL;
            $this->show[] = PHP_EOL;
            $this->show[] = ' ' . $c('Options:')->yellow() . PHP_EOL;
            $this->show[] = '  ' . $c('--help')->green().' '.$c('(-h)')->white();
            $this->show[] = '  ' . $c('Display this help message')->white() . PHP_EOL;
            $this->show[] = '  ' . $c('--version')->green().' ';
            $this->show[] = '   ' . $c('Display konsol version')->white() . PHP_EOL;
            $this->show[] = PHP_EOL;
            $this->show[] = ' ' . $c('Help:')->yellow() . PHP_EOL;
            $this->show[] = '  ' . $c('The')->white().' '.$c('help')->green()->dark().' '.$c('command display help for a given console')->white() . PHP_EOL .PHP_EOL;
            $this->show[] = '    ' . $c('konsol --help directory/console')->green();
        }

        $this->show();
    }

    public function showVersion($h = false)
    {
        $c = new Color();
        $this->show = array();

        if (is_string($h)) {
            if (file_exists($h)) {
                $data = \Spyc::YAMLLoad($h);

                if (!empty($data['colors']) && is_array($data['colors'])) {
                    $this->setColors($data['colors']);
                }

                if (!empty($data['figlet'])) {
                    self::$FIGLET_ACTIVE = true;
                    self::$FIGLET = $data['figlet'];
                }

                $v = 'unknown version';
                if (!empty($data['version'])) {
                    $v = $data['version'];
                }

                $title = (!empty($data['title'])) ? $data['title'] : $h;
                $this->show[] = $this->title($title);
                $this->show[] = ' ' . $c('Version: '.$v)->white();
            } else {
                self::$TITLE_BG = 'red';

                $title = 'Konsol';
                $this->show[] = $this->title($title);
                $this->show[] = ' ' . $this->error('Error: This console "'.$h.'" does not exist') . PHP_EOL;
            }
        } else {
            self::$FIGLET_ACTIVE = true;

            $title = 'Konsol';
            $this->show[] = $this->title($title);
            $this->show[] = ' ' . $c('Version: '.$this->version)->white();
        }

        $this->show();
    }

    public function showTitle($h = false)
    {
        $c = new Color();
        $this->show = array();

        if (is_string($h)) {
            if (file_exists($h)) {
                $data = \Spyc::YAMLLoad($h);

                if (!empty($data['colors']) && is_array($data['colors'])) {
                    $this->setColors($data['colors']);
                }

                if (!empty($data['figlet'])) {
                    self::$FIGLET_ACTIVE = true;
                    self::$FIGLET = $data['figlet'];
                }

                $title = (!empty($data['title'])) ? $data['title'] : $h;
                $this->show[] = $this->title($title);
            }
        } else {
            self::$FIGLET_ACTIVE = true;

            $title = 'Konsol';
            $this->show[] = $this->title($title);
        }

        $this->show();
    }

    public function showUsage()
    {
        $this->show = array();
        $this->show();
    }

    public function show($text = false)
    {
        if ($text) {
            $colors = new Colors();
            if (($text = $colors->colorize($text)) != false) {

            }
        } else {
            echo implode('', $this->show) . PHP_EOL;
        }
    }
}