<?php

namespace Solital\Core\Wolf\Functions;

trait AssetsTrait
{
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
}
