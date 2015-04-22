<?php

namespace Konsol\Core;

class Update extends Colors
{
    protected $version = 'latest';
    protected $verbose;
    protected $copyDir;
    protected $pharFile;
    protected $zipFile;

    public function __construct($v, $verbose = false)
    {
        $this->version = 'v'.$v;
        $this->verbose = $verbose;

        $this->pharFile = $_SERVER['argv'][0];
        $this->zipFile = '/var/www/konsol/tests/zip/konsol.'.$this->version.'.zip';
        $this->copyDir = $_SERVER['PWD'].'/konsolUpdateTmp/';
    }

    public function getPharEquivalence($dir)
    {
        return str_replace($this->copyDir.'konsol', 'phar://'.$this->pharFile, $dir);
    }

    public function updRecursive($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir.$object)){
                        $p = $this->getPharEquivalence($dir.$object);
                        if (!is_dir($p)) {
                            mkdir($p);
                        }

                        $this->updRecursive($dir.$object.'/');
                    } else {
                        file_put_contents($this->getPharEquivalence($dir).$object, file_get_contents($dir.$object));
                    }
                }
            }
        }
    }

    public function rmRecursive($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir."/".$object)){
                        $this->rmRecursive($dir."/".$object);
                    }else{
                        unlink($dir."/".$object);
                    }
                }
            }

            reset($objects);

            if (is_dir($dir)) {
                rmdir($dir);
            }
        }
    }

    public function run()
    {
        echo $this->colorize('<pre><code class="language-sh">$ node app</code></pre>');

        if (file_exists($this->pharFile)) {
            $zip = new \ZipArchive;
            if ($zip->open($this->zipFile) === true) {
                if (!is_dir($this->copyDir)) {
                    mkdir($this->copyDir);
                }

                $zip->extractTo($this->copyDir);
                $zip->close();

                if (is_dir($this->copyDir.'konsol/')) {
                    $this->rmRecursive('phar://'.$this->pharFile.'/src');
                    $this->rmRecursive('phar://'.$this->pharFile.'/vendor');
                    $this->updRecursive($this->copyDir.'konsol/');
                    $this->rmRecursive($this->copyDir);

                    echo $this->colorize("<success>Update completed</success>");
                }
            }
        }
    }
}