<?php

namespace Solital\Core\Console\ProgressBar;

class ProgressBarStyle
{
    /**
     * @param string $name Name of the tracked progress
     * @param string $color Color of the progressbar
     * @param string $datatype Defines what datatype you are iterating. Example MB or Kg
     * @param int $length Length of the progressbar
     */
    public function __construct(
        private string $name,
        private string $color,
        private string $datatype,
        private int $length = 16,
    ) {
        if (!empty(trim($name))) {
            $this->setName($name);
        } else {
            throw new ProgressBarException("Progressbar name cannot be empty!");
        }

        $colors = ["1;37" => "white", "0;31" => "red", "1;33" => "yellow", "0;32" => "green", "0;34" => "blue", "0;35" => "magenta"];

        if (in_array(strtolower($color), $colors)) {
            $this->setColor("\e[" . array_search($color, $colors) . ";40m");
        } else {
            throw new ProgressBarException("Invalid color specified for style object at line " . __LINE__ . ". Valid colors are " . implode(", ", $colors));
        }

        if ($length > 0) {
            $this->setLength($length);
        } else {
            throw new ProgressBarException("Progressbar length must be greater than zero!");
        }

        $this->setDatatype($datatype);
    }

    /**
     * Get the value of name
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the value of name
     * @param string $name
     * 
     * @return ProgrssBarStyle
     */
    public function setName(string $name): ProgressBarStyle
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of color
     * @return string
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * Set the value of color
     * @param string $color
     * 
     * @return ProgressBarStyle
     */
    public function setColor(string $color): ProgressBarStyle
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get the value of datatype
     * @return string
     */
    public function getDatatype(): string
    {
        return $this->datatype;
    }

    /**
     * Set the value of datatype
     * @return string $datatype
     * 
     * @return ProgressBarStyle
     */
    public function setDatatype(string $datatype): ProgressBarStyle
    {
        $this->datatype = $datatype;

        return $this;
    }

    /**
     * Get the value of length
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * Set the value of length
     * @param int $length
     * 
     * @return ProgressBarStyle
     */
    public function setLength(int $length): ProgressBarStyle
    {
        $this->length = $length;

        return $this;
    }
}
