<?php

namespace Solital\Core\Http\Input;

interface InputItemInterface
{
    /**
     * @return string
     */
    public function getIndex(): string;

    /**
     * @param string $index
     * 
     * @return self
     */
    public function setIndex(string $index): self;

    /**
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * @param string $name
     * 
     * @return self
     */
    public function setName(string $name): self;

    /**
     * @return string|null
     */
    public function getValue(): ?string;

    /**
     * @param string $value
     * 
     * @return self
     */
    public function setValue(string $value): self;

    public function __toString(): string;

}