<?php

namespace Solital\Core\Resource\Validation;

use Solital\Core\Resource\Validation\DataValidator;

class Validator
{
    /**
     * @var DataValidator
     */
    private DataValidator $validator;

    /**
     * @var array
     */
    private array $data;

    public function __construct(string $language = "en")
    {
        $this->validator = new DataValidator($language);
    }

    /**
     * Validate data values in $_POST and $_FILES
     *
     * @param array $rules
     * 
     * @return self
     */
    public function verifyRequestInput(array $rules, ?array $input = null): self
    {
        $this->validator->validation_rules($rules);

        if (!is_null($input)) {
            // Código para pegar valores usando o Input Handler
            // Ex: $this->data = input()->all()
        } else {
            $this->data = array_merge($_POST, $_FILES);
        }

        return $this;
    }

    /**
     * Validate data values
     *
     * @param array $data
     * @param array $rules
     * 
     * @return self
     */
    public function verify(array $data, array $rules): self
    {
        $this->validator->validation_rules($rules);
        $this->data = $data;
        return $this;
    }

    /**
     * Set message errors when validation fail
     *
     * @param array $messages
     * 
     * @return self
     */
    public function setFieldsErrorMessages(array $messages): self
    {
        $this->validator->set_fields_error_messages($messages);
        return $this;
    }

    /**
     * Filter values
     *
     * @param array $rules
     * 
     * @return self
     */
    public function filter(array $rules): self
    {
        $this->validator->filter_rules($rules);
        return $this;
    }

    /**
     * Get result
     *
     * @return array
     */
    public function getResult(): array
    {
        $validated = $this->validator->run($this->data);

        return ($this->validator->errors()) ? 
            ['validation_errors' => $this->validator->get_errors_array()] : 
            $validated;
    }
}
