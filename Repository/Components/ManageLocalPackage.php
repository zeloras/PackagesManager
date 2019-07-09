<?php

namespace GeekCms\PackagesManager\Repository\Components;
use Nwidart\Modules\Process\Installer;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;


class ManageLocalPackage extends Installer
{
    public function __construct() {}

    public function install(array $module_info = [], $install_dir = null)
    {
        $status = false;

        if (!empty($module_info) && isset($module_info['composer_info'])) {
            $this->type = (!$module_info['is_official']) ? false : 'download';
            $this->name = $module_info['composer_info']['name'];
            $process = $this->getProcess();
            $process->setTimeout($this->timeout);

            if ($this->type !== 'download') {
                $status = $process->run();
            } else {
                $status = $this->download($module_info, $install_dir);
            }
        }

        return $status;
    }

    public function download(array $module_info = [], $install_dir = null)
    {
        $file = $install_dir . DIRECTORY_SEPARATOR . $module_info['vendor'] . '_' . $module_info['name'] . '.zip';
        //$file = $module_info['vendor'] . '_' . $module_info['name'] . '.zip';
        $download_file = self::copy($module_info['url'] . '/archive/master.zip', $file);
        //$download_file = \Storage::putFileAs($install_dir, new \File($module_info['release']['download']), $file);
        dd($download_file);
        die();
    }

    /**
     * Install the module via composer.
     *
     * @return \Symfony\Component\Process\Process
     */
    public function installViaComposer()
    {
        $dir = base_path();
        $pack = $this->getPackageName(); //--no-suggest
        $flags = '--prefer-dist --no-scripts --working-dir="' . $dir . '"';
        $export = 'export HOME="' . $dir .'" && cd "$HOME" && ';
        $composer_require = 'composer ' . $flags . ' --no-update require "'. $pack .'" && ';
        $composer_install = $composer_require . 'composer ' . $flags . ' update "'. $pack .'"';
        return new Process($export . $composer_install);
    }

    public static function copy($remote, $desDir)
    {
        $adapter = new Local($desDir);

        $filesystem = new Filesystem($adapter);

        $pathInfo = pathinfo($remote);

        $stream = fopen($remote, 'r');

        if ($filesystem->putStream($pathInfo['basename'], $stream)) {

            fclose($stream);

            return trim($desDir) . DIRECTORY_SEPARATOR . $pathInfo['basename'];
        }

        return null;
    }
}
