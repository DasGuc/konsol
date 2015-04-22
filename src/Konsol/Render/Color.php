<?php

namespace Konsol\Render;

class Color
{
    protected $n;
    protected $e;

    protected $pattern = "\033[%sm";
    protected $closing = "\033[0m";

    protected $color = array(
        'default'          => '39',
        'black'            => '30',
        'red'              => '31',
        'green'            => '32',
        'yellow'           => '33',
        'blue'             => '34',
        'magenta'          => '35',
        'cyan'             => '36',
        'light_gray'       => '37',

        'dark_gray'        => '90',
        'light_red'        => '91',
        'light_green'      => '92',
        'light_yellow'     => '93',
        'light_blue'       => '94',
        'light_magenta'    => '95',
        'light_cyan'       => '96',
        'white'            => '97'
    );

    protected $bg = array(
        'default'       => '49',
        'black'         => '40',
        'red'           => '41',
        'green'         => '42',
        'yellow'        => '43',
        'blue'          => '44',
        'magenta'       => '45',
        'cyan'          => '46',
        'light_gray'    => '47',

        'dark_gray'     => '100',
        'light_red'     => '101',
        'light_green'   => '102',
        'light_yellow'  => '103',
        'light_blue'    => '104',
        'light_magenta' => '105',
        'light_cyan'    => '106',
        'white'         => '107'
    );

    protected $styles = array(
        'reset'            => '0',
        'bold'             => '1',
        'dark'             => '2',
        'italic'           => '3',
        'underline'        => '4',
        'blink'            => '5',
        'reverse'          => '7',
        'concealed'        => '8',
    );

    protected function getColor($key)
    {
        $key = strtolower($key);

        if (!empty($this->color[$key])) {
            return $this->color[$key];
        }

        return $this->color['default'];
    }

    protected function getBg($key)
    {
        $key = strtolower($key);

        if (!empty($this->bg[$key])) {
            return $this->bg[$key];
        }

        return $this->bg['bg_default'];
    }

    protected function getStyle($key)
    {
        $key = strtolower($key);

        $split = explode(',', $key);
        if (count($split)>1) {
            $arr = array();
            foreach ($split as $s) {
                $arr[] = $this->getStyle($s);
            }

            $arr = array_unique($arr);
            return implode(';', $arr);
        } else {
            if (!empty($this->styles[$key])) {
                return $this->styles[$key];
            }

            return $this->styles['reset'];
        }
    }

    protected function getDefault($key, $val)
    {
        $text = '';

        if (in_array($key, array_keys($this->color))) {
            if ($val != null && in_array($key, array_keys($this->color))) {
                $text .= sprintf($this->pattern, $this->getBg($key));
                $text .= sprintf($this->pattern, $this->getColor($val));
            } else {
                $text .= sprintf($this->pattern, $this->getColor($key));
            }
        } else {
            switch($key) {
                case 'ko':
                case 'err':
                case 'error':
                    $text .= sprintf($this->pattern, $this->getColor('red'));
                    $text .= sprintf($this->pattern, $this->getStyle('bold'));
                    break;

                case 'warn':
                case 'warning':
                    $text .= sprintf($this->pattern, $this->getColor('yellow'));
                    $text .= sprintf($this->pattern, $this->getStyle('bold'));
                    break;

                case 'inf':
                case 'info':
                case 'infos':
                    $text .= sprintf($this->pattern, $this->getColor('cyan'));
                    $text .= sprintf($this->pattern, $this->getStyle('bold'));
                    break;

                case 'ok':
                case 'success':
                    $text .= sprintf($this->pattern, $this->getColor('green'));
                    break;

                case 'log':
                case 'logs':
                    $text .= sprintf($this->pattern, $this->getColor('cyan'));
                    break;

            }
        }
        return $text;
    }

    protected function parse($style)
    {
        $text = '';

        // PARSE STYLE
        $splitStyle = explode(';', $style);
        foreach ($splitStyle as $splS) {
            $splitSplS = explode(':', $splS);
            $key = $splitSplS[0];
            $val = null;

            if (count($splitSplS)>1) {
                $val = $splitSplS[1];
            }

            switch($key) {
                case 'n':
                case 'br':
                case 'line-break':
                case 'newline':
                    $val = ($val!=null) ? $val : 1;
                    for ($i=0;$i<$val;$i++) {
                        $text .= PHP_EOL;
                    }
                    break;

                case 's':
                case 'space':
                    $val = ($val!=null) ? $val : 1;
                    for ($i=0;$i<$val;$i++) {
                        $text .= ' ';
                    }
                    break;

                case 'bg':
                case 'bgcolor':
                case 'background':
                case 'background-color':
                    if ($val != null) {
                        $text .= sprintf($this->pattern, $this->getBg($val));
                    }
                    break;

                case 'fg':
                case 'color':
                case 'foreground':
                case 'foreground-color':
                    if ($val != null) {
                        $text .= sprintf($this->pattern, $this->getColor($val));
                    }
                    break;

                case 'i':
                case 'italic':
                    $text .= sprintf($this->pattern, $this->getStyle('italic'));
                    break;

                case 'b':
                case 'bold':
                    $text = sprintf($this->pattern, $this->getStyle('bold'));
                    break;

                case 'u':
                case 'underline':
                    $text = sprintf($this->pattern, $this->getStyle('underline'));
                    break;

                case 'd':
                case 'dark':
                    $text .= sprintf($this->pattern, $this->getStyle('dark'));
                    break;

                case 'reset':
                    $text .= sprintf($this->pattern, $this->getStyle('reset'));
                    break;

                case 'r':
                case 'reverse':
                    $text .= sprintf($this->pattern, $this->getStyle('dark'));
                    break;

                case 'bl':
                case 'blink':
                    $text = sprintf($this->pattern, $this->getStyle('blink'));
                    break;

                case 'c':
                case 'concealed':
                    $text .= sprintf($this->pattern, $this->getStyle('concealed'));
                    break;

                default:
                    $text .= $this->getDefault($key, $val);
                    break;
            }
        }

        return $text;
    }

    public function print_c($text)
    {
        echo $this->render($text);
    }

    public function render($text)
    {
        $splitText = explode(CHR(10), $text);

        foreach ($splitText as &$split) {
            if (preg_match_all('/\<(.*)\>/Um', $split, $matches)) {
                foreach ($matches[0] as $k => $m) {
                    $style = $matches[1][$k];

                    if (preg_match('/\//', $style, $s)) {
                        $split = str_replace($m, $this->closing, $split);
                    } else {
                        $split = str_replace($m, $this->parse($style), $split);
                    }
                }
            }

            $split .= $this->closing;
        }

        return implode('', $splitText);
    }
}