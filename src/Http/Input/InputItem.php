<?php

namespace Solital\Core\Http\Input;

use Solital\Core\Http\Input\InputItemInterface;

class InputItem implements InputItemInterface
{
    /**
     * @var string
     */
    public string $index;

    /**
     * @var string
     */
    public string $name;

    /**
     * @var null|string
     */
    public $value;

    /**
     * @param string $index
     * @param string|null $value
     */
    public function __construct(string $index, ?string $value = null)
    {
        $this->index = $index;
        $this->value = $value;

        // Make the name human friendly, by replace _ with space
        $this->name = ucfirst(str_replace('_', ' ', strtolower($this->index)));
    }

    /**
     * @return string
     */
    public function getIndex(): string
    {
        return $this->index;
    }

    /**
     * @param string $index
     * 
     * @return InputItemInterface
     */
    public function setIndex(string $index): InputItemInterface
    {
        $this->index = $index;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set input name
     * @param string $name
     * @return static
     */
    public function setName(string $name): InputItemInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * Set input value
     * @param string $value
     * @return static
     */
    public function setValue(string $value): InputItemInterface
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->value;
    }
}
