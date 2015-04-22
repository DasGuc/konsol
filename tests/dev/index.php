<?php

    require '../autoload.php';
    require '../../vendor/autoload.php';

    if ($cmd = new \Konsol\Konsol()) {
        $name = $cmd->getConsoleName();
        $data = \Spyc::YAMLLoad($name);
        $create = new \Konsol\Core\Create();


        /**
         * BUILD ARGUMENTS
         */
        if (!empty($data['arguments'])) {
            foreach ($data['arguments'] as $name => $arguments) {
                $a = $create->argument($name, $arguments);
                $cmd->add($a);
            }
        }

        /**
         * BUILD OPTIONS
         */
        if (!empty($data['options'])) {
            foreach ($data['options'] as $name => $options) {
                $o = $create->option($name, $options);
                $cmd->add($o);
            }
        }

        /**
         * BUILD QUESTIONS
         */
        if (!empty($data['questions'])) {
            foreach ($data['questions'] as $name => $questions) {
                $q = $create->question($name, $questions);
                $cmd->add($q);
            }
        }

        /**
         * BUILD PRESS
         */
        if (!empty($data['press'])) {
            if (!is_array($data['press'])) {
                $press = array();
                $press['key'] = $data['press'];
            } else {
                $press = $data['press'];
            }

            $p = $create->press($press);
            $cmd->add($p);
        }

        /**
         * BUILD MENU
         */
        if (!empty($data['menu'])) {
            $m = $create->menu($data['menu']);
            $cmd->add($m);
        }

        $cmd->run();
    }

    echo PHP_EOL;


