<?php

namespace Solital\Core\Resource;

use GUMP;

class Validation
{
    /**
     * @var GUMP
     */
    private GUMP $gump;

    /**
     * @var array
     */
    private array $data;

    public function __construct()
    {
        $this->gump = new GUMP();
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
        $this->gump->validation_rules($rules);

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
        $this->gump->validation_rules($rules);
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
        $this->gump->set_fields_error_messages($messages);
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
        $this->gump->filter_rules($rules);
        return $this;
    }

    /**
     * Get result
     *
     * @return array
     */
    public function getResult(): array
    {
        $validated = $this->gump->run($this->data);

        if ($this->gump->errors()) {
            return ['validation_errors' => $this->gump->get_errors_array()];
        } else {
            return $validated;
        }
    }
}
