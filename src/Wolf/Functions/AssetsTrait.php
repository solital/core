<?php

namespace Solital\Core\Wolf\Functions;

use Solital\Core\Kernel\Application;

trait AssetsTrait
{
    /**
     * @var array
     */
    protected static array $allow_tags = [];

    /**
     * Return YAML variables
     *
     * @return mixed
     */
    protected static function getConfigYaml(): mixed
    {
        return Application::yamlParse('exceptions.yaml');
    }

    /**
     * @param string $asset
     * 
     * @return string
     */
    public function file(string $asset): string
    {
        return $this->main_url . $asset;
    }

    /**
     * @param string $asset
     * 
     * @return string
     */
    public function css(string $asset): string
    {
        return $this->main_url . 'assets/_css/' . $asset;
    }

    /**
     * @param string $asset
     * 
     * @return string
     */
    public function js(string $asset): string
    {
        return $this->main_url . 'assets/_js/' . $asset;
    }

    /**
     * @param string $asset
     * 
     * @return string
     */
    public function img(string $asset): string
    {
        return $this->main_url . 'assets/_img/' . $asset;
    }

    /**
     * @param array $tags
     * 
     * @return void
     */
    public function setAllowIndex(array $tags): void
    {
        foreach ($tags as $value) {
            self::$allow_tags[$value] = $value;
        }
    }

    /**
     * @return array
     */
    protected function getAllowIndex(): array
    {
        return self::$allow_tags;
    }
}
