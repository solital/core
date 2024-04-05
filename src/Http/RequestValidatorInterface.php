<?php

namespace Solital\Core\Http;

interface RequestValidatorInterface
{
    /**
     * Set validation rules
     *
     * @return array
     */
    public function rules(): array;

    /**
     * Set filter rules
     *
     * @return array
     */
    public function filters(): array;

    /**
     * Set field-rule specific error messages
     *
     * @return array
     */
    public function messages(): array;
}
