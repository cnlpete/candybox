<?php
namespace Composer\Installers;

class CandyboxInstaller extends BaseInstaller
{
    protected $locations = array(
        'plugin'    => 'plugins/{$name}/',
        'module'    => 'modules/{name}/',
    );
}
