<?php

namespace Solital\Core\Resource\Validation;

use Exception;
use Solital\Core\Resource\Validation\Trait\{ValidatorsTrait, FiltersTrait};

class DataValidator
{
    use ValidatorsTrait, FiltersTrait;

    /**
     * Singleton instance of GUMP.
     *
     * @var self|null
     */
    protected static $instance = null;

    /**
     * Contains readable field names that have been manually set.
     *
     * @var array
     */
    protected static $fields = [];

    /**
     * Custom validators.
     *
     * @var array
     */
    protected static $validation_methods = [];

    /**
     * Custom validators error messages.
     *
     * @var array
     */
    protected static $validation_methods_errors = [];

    /**
     * Customer filters.
     *
     * @var array
     */
    protected static $filter_methods = [];

    // ** ------------------------- Instance Helper ---------------------------- ** //

    /**
     * Function to create and return previously created instance
     *
     * @return self
     */
    public static function get_instance(): self
    {
        if (self::$instance === null) self::$instance = new static();
        return self::$instance;
    }

    // ** ------------------------- Configuration -------------------------------- ** //

    /**
     * Rules delimiter.
     *
     * @var string
     */
    public static $rules_delimiter = '|';

    /**
     * Rules-parameters delimiter.
     *
     * @var string
     */
    public static $rules_parameters_delimiter = ',';

    /**
     * Rules parameters array delimiter.
     *
     * @var string
     */
    public static $rules_parameters_arrays_delimiter = ';';

    /**
     * Characters that will be replaced to spaces during field name conversion (street_name => Street Name).
     *
     * @var array
     */
    public static $field_chars_to_spaces = ['_', '-'];

    // ** ------------------------- Validation Data ------------------------------- ** //

    /**
     * Language for error messages.
     *
     * @var string
     */
    protected $lang;

    /**
     * Custom field-rule messages.
     *
     * @var array
     */
    protected $fields_error_messages = [];

    /**
     * Set of validation rules for execution.
     *
     * @var array
     */
    protected $validation_rules = [];

    /**
     * Set of filters rules for execution.
     *
     * @var array
     */
    protected $filter_rules = [];

    /**
     * Errors.
     *
     * @var array
     */
    protected $errors = [];

    // ** ------------------------- Validation Helpers ---------------------------- ** //

    /**
     * GUMP constructor.
     *
     * @param string $lang
     * @throws ValidatorException when language is not supported
     */
    public function __construct(string $lang = 'en')
    {
        $lang_file_location = __DIR__ . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $lang . '.php';

        if (!EnvHelpers::file_exists($lang_file_location)) {
            throw new ValidatorException(
                sprintf("'%s' language is not supported.", $lang)
            );
        }

        $this->lang = $lang;
    }

    /**
     * Shorthand method for inline validation.
     *
     * @param array $data The data to be validated
     * @param array $validators The GUMP validators
     * @param array $fields_error_messages
     * @return mixed True(boolean) or the array of error messages
     * @throws Exception If validation rule does not exist
     */
    public static function is_valid(array $data, array $validators, array $fields_error_messages = [])
    {
        $gump = self::get_instance();
        $gump->validation_rules($validators);
        $gump->set_fields_error_messages($fields_error_messages);

        return ($gump->run($data) === false) ? $gump->get_readable_errors() : true;
    }

    /**
     * Shorthand method for running only the data filters.
     *
     * @param array $data
     * @param array $filters
     * @return mixed
     * @throws Exception If filter does not exist
     */
    public static function filter_input(array $data, array $filters)
    {
        $gump = self::get_instance();
        return $gump->filter($data, $filters);
    }

    /**
     * Magic method to generate the validation error messages.
     *
     * @return string
     * @throws Exception
     */
    public function __toString()
    {
        return $this->get_readable_errors(true);
    }

    /**
     * An empty value for us is: null, empty string or empty array
     *
     * @param  $value
     * @return bool
     */
    public static function is_empty($value)
    {
        return (is_null($value) || $value === '' || (is_array($value) && count($value) === 0));
    }

    /**
     * Adds a custom validation rule using a callback function.
     *
     * @param string $rule
     * @param callable $callback
     * @param string $error_message
     *
     * @return void
     * @throws Exception when validator with the same name already exists
     */
    public static function add_validator(string $rule, callable $callback, string $error_message)
    {
        if (
            method_exists(
                __CLASS__,
                self::validator_to_method($rule)
            ) ||
            isset(self::$validation_methods[$rule])
        ) {
            throw new Exception(sprintf("'%s' validator is already defined.", $rule));
        }

        self::$validation_methods[$rule] = $callback;
        self::$validation_methods_errors[$rule] = $error_message;
    }

    /**
     * Adds a custom filter using a callback function.
     *
     * @param string $rule
     * @param callable $callback
     *
     * @return void
     * @throws Exception when filter with the same name already exists
     */
    public static function add_filter(string $rule, callable $callback)
    {
        if (method_exists(__CLASS__, self::filter_to_method($rule)) || isset(self::$filter_methods[$rule])) {
            throw new Exception(sprintf("'%s' filter is already defined.", $rule));
        }

        self::$filter_methods[$rule] = $callback;
    }

    /**
     * Checks if a validator exists.
     *
     * @param string $rule
     *
     * @return bool
     */
    public static function has_validator(string $rule)
    {
        return method_exists(__CLASS__, self::validator_to_method($rule)) || isset(self::$validation_methods[$rule]);
    }

    /**
     * Checks if a filter exists.
     *
     * @param string $filter
     *
     * @return bool
     */
    public static function has_filter(string $filter)
    {
        return method_exists(__CLASS__, self::filter_to_method($filter))
            || isset(self::$filter_methods[$filter])
            || function_exists($filter);
    }

    /**
     * Helper method to extract an element from an array safely
     *
     * @param  mixed $key
     * @param  array $array
     * @param  mixed $default
     *
     * @return mixed
     */
    public static function field($key, array $array, $default = null)
    {
        return (isset($array[$key])) ? $array[$key] : $default;
    }

    /**
     * Getter/Setter for the validation rules.
     *
     * @param array $rules
     */
    public function validation_rules(array $rules = [])
    {
        if (empty($rules)) return $this->validation_rules;
        $this->validation_rules = $rules;
    }

    /**
     * Set field-rule specific error messages.
     *
     * @param array $fields_error_messages
     * @return array
     */
    public function set_fields_error_messages(array $fields_error_messages)
    {
        return $this->fields_error_messages = $fields_error_messages;
    }

    /**
     * Getter/Setter for the filter rules.
     *
     * @param array $rules
     */
    public function filter_rules(array $rules = [])
    {
        if (empty($rules)) return $this->filter_rules;
        $this->filter_rules = $rules;
    }

    /**
     * Run the filtering and validation after each other.
     *
     * @param array  $data
     * @param bool   $check_fields
     *
     * @return array|bool
     * @throws Exception
     */
    public function run(array $data, $check_fields = false)
    {
        $data = $this->filter($data, $this->filter_rules());
        $validated = $this->validate($data, $this->validation_rules());

        if ($check_fields === true) $this->check_fields($data);
        return ($validated !== true) ? false : $data;
    }

    /**
     * Ensure that the field counts match the validation rule counts.
     *
     * @param array $data
     */
    private function check_fields(array $data)
    {
        $ruleset = $this->validation_rules();
        $mismatch = array_diff_key($data, $ruleset);
        $fields = array_keys($mismatch);

        foreach ($fields as $field) {
            $this->errors[] = $this->generate_error_array($field, $data[$field], 'mismatch');
        }
    }

    /**
     * Sanitize the input data.
     *
     * @param array $input
     * @param array $fields
     * @param bool $utf8_encode
     *
     * @return array
     */
    public function sanitize(array $input, array $fields = [], bool $utf8_encode = true)
    {
        if (empty($fields)) $fields = array_keys($input);

        $return = [];

        foreach ($fields as $field) {
            if (!isset($input[$field])) continue;

            $value = $input[$field];
            if (is_array($value)) {
                $value = $this->sanitize($value, [], $utf8_encode);
            }
            if (is_string($value)) {
                if (strpos($value, "\r") !== false) $value = trim($value);

                if (
                    function_exists('iconv') &&
                    function_exists('mb_detect_encoding') &&
                    $utf8_encode
                ) {
                    $current_encoding = mb_detect_encoding($value);

                    if ($current_encoding !== 'UTF-8' && $current_encoding !== 'UTF-16')
                        $value = iconv($current_encoding, 'UTF-8', $value);
                }

                $value = static::polyfill_filter_var_string($value);
            }

            $return[$field] = $value;
        }

        return $return;
    }

    /**
     * Return the error array from the last validation run.
     *
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Perform data validation against the provided ruleset.
     *
     * @param array $input Input data.
     * @param array $ruleset Validation rules.
     *
     * @return bool|array Returns bool true when no errors. Returns array when errors.
     * @throws Exception
     */
    public function validate(array $input, array $ruleset)
    {
        $this->errors = [];

        foreach ($ruleset as $field => $rawRules) {
            $input[$field] = ArrayHelpers::data_get($input, $field);

            $rules = $this->parse_rules($rawRules);
            $is_required = $this->field_has_required_rules($rules);

            if (!$is_required && self::is_empty($input[$field])) continue;

            foreach ($rules as $rule) {
                $parsed_rule = $this->parse_rule($rule);
                $result = $this->foreach_call_validator($parsed_rule['rule'], $field, $input, $parsed_rule['param']);

                if (is_array($result)) {
                    $this->errors[] = $result;
                    break; // exit on first error
                }
            }
        }

        return (count($this->errors) > 0) ? $this->errors : true;
    }

    /**
     * Parses filters and validators rules group.
     *
     * @param string|array $rules
     * @return array
     */
    private function parse_rules($rules)
    {
        // v2
        if (is_array($rules)) {
            $rules_names = [];

            foreach ($rules as $key => $value) {
                $rules_names[] = is_numeric($key) ? $value : $key;
            }

            return array_map(function ($value, $key) use ($rules) {
                if ($value === $key) {
                    return [$key];
                }

                return [$key, $value];
            }, $rules, $rules_names);
        }

        return explode(self::$rules_delimiter, $rules);
    }

    /**
     * Parses filters and validators individual rules.
     *
     * @param string|array $rule
     * @return array
     */
    private function parse_rule($rule)
    {
        // v2
        if (is_array($rule)) {
            return [
                'rule' => $rule[0],
                'param' => $this->parse_rule_params($rule[1] ?? [])
            ];
        }

        $result = [
            'rule' => $rule,
            'param' => []
        ];

        if (strpos($rule, self::$rules_parameters_delimiter) !== false) {
            list($rule, $param) = explode(self::$rules_parameters_delimiter, $rule);

            $result['rule'] = $rule;
            $result['param'] = $this->parse_rule_params($param);
        }

        return $result;
    }

    /**
     * Parse rule parameters.
     *
     * @param string|array $param
     * @return array|string|null
     */
    private function parse_rule_params($param)
    {
        if (is_array($param)) return $param;

        return (strpos($param, self::$rules_parameters_arrays_delimiter) !== false) ?
            explode(self::$rules_parameters_arrays_delimiter, $param) : [$param];
    }

    /**
     * Checks if array of rules contains a required type of validator.
     *
     * @param array $rules
     * @return bool
     */
    private function field_has_required_rules(array $rules)
    {
        $require_type_of_rules = ['required', 'required_file'];

        // v2 format (using arrays for definition of rules)
        if (is_array($rules) && is_array($rules[0])) {
            $found = array_filter($rules, function ($item) use ($require_type_of_rules) {
                return in_array($item[0], $require_type_of_rules);
            });

            return count($found) > 0;
        }

        $found = array_values(array_intersect($require_type_of_rules, $rules));
        return count($found) > 0;
    }

    /**
     * Helper to convert validator rule name to validator rule method name.
     *
     * @param string $rule
     * @return string
     */
    private static function validator_to_method(string $rule)
    {
        return sprintf('validate_%s', $rule);
    }

    /**
     * Helper to convert filter rule name to filter rule method name.
     *
     * @param string $rule
     * @return string
     */
    private static function filter_to_method(string $rule)
    {
        return sprintf('filter_%s', $rule);
    }

    /**
     * Calls call_validator.
     *
     * @param string $rule
     * @param string $field
     * @param mixed $input
     * @param array $rule_params
     * @return array|bool
     * @throws Exception
     */
    private function foreach_call_validator(string $rule, string $field, array $input, array $rule_params = [])
    {
        $is_required_kind_of_rule = $this->field_has_required_rules([$rule]);

        // Fixes #315
        if ($is_required_kind_of_rule && is_array($input[$field]) && count($input[$field]) === 0) {
            $result = $this->call_validator(
                $rule,
                $field,
                $input,
                $rule_params,
                $input[$field]
            );

            return is_array($result) ? $result : true;
        }

        $values = is_array($input[$field]) ? $input[$field] : [$input[$field]];

        foreach ($values as $value) {
            $result = $this->call_validator(
                $rule,
                $field,
                $input,
                $rule_params,
                $value
            );

            if (is_array($result)) return $result;
        }

        return true;
    }

    /**
     * Calls a validator.
     *
     * @param string $rule
     * @param string $field
     * @param mixed $input
     * @param array $rule_params
     * @return array|bool
     * @throws Exception
     */
    private function call_validator(string $rule, string $field, array $input, array $rule_params = [], $value = null)
    {
        $method = self::validator_to_method($rule);

        // use native validations
        if (is_callable([$this, $method])) {
            $result = $this->$method($field, $input, $rule_params, $value);

            // is_array check for backward compatibility
            return (is_array($result) || $result === false)
                ? $this->generate_error_array($field, $input[$field], $rule, $rule_params)
                : true;
        }

        // use custom validations
        if (isset(self::$validation_methods[$rule])) {
            $result = call_user_func(self::$validation_methods[$rule], $field, $input, $rule_params, $value);

            return ($result === false)
                ? $this->generate_error_array($field, $input[$field], $rule, $rule_params)
                : true;
        }

        throw new Exception(sprintf("'%s' validator does not exist.", $rule));
    }

    /**
     * Calls a filter.
     *
     * @param string $rule
     * @param mixed $value
     * @param array $rule_params
     * @return mixed
     * @throws Exception
     */
    private function call_filter(string $rule, $value, array $rule_params = [])
    {
        $method = self::filter_to_method($rule);

        // use native filters
        if (is_callable(array($this, $method))) return $this->$method($value, $rule_params);

        // use custom filters
        if (isset(self::$filter_methods[$rule]))
            return call_user_func(self::$filter_methods[$rule], $value, $rule_params);

        // use php functions as filters
        if (function_exists($rule))
            return call_user_func($rule, $value, ...$rule_params);

        throw new Exception(sprintf("'%s' filter does not exist.", $rule));
    }

    /**
     * Generates error array.
     *
     * @param string $field
     * @param mixed $value
     * @param string $rule
     * @param array $rule_params
     * @return array
     */
    private function generate_error_array(string $field, $value, string $rule, array $rule_params = [])
    {
        return [
            'field' => $field,
            'value' => $value,
            'rule' => $rule,
            'params' => $rule_params
        ];
    }

    /**
     * Set a readable name for a specified field names.
     *
     * @param string $field
     * @param string $readable_name
     */
    public static function set_field_name(string $field, string $readable_name)
    {
        self::$fields[$field] = $readable_name;
    }

    /**
     * Set readable name for specified fields in an array.
     *
     * @param array $array
     */
    public static function set_field_names(array $array)
    {
        foreach ($array as $field => $readable_name) {
            self::set_field_name($field, $readable_name);
        }
    }

    /**
     * Set a custom error message for a validation rule.
     *
     * @param string $rule
     * @param string $message
     */
    public static function set_error_message(string $rule, string $message)
    {
        self::$validation_methods_errors[$rule] = $message;
    }

    /**
     * Set custom error messages for validation rules in an array.
     *
     * @param array $array
     */
    public static function set_error_messages(array $array)
    {
        foreach ($array as $rule => $message) {
            self::set_error_message($rule, $message);
        }
    }

    /**
     * Get all error messages.
     *
     * @return array
     */
    protected function get_messages()
    {
        $lang_file = __DIR__ . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $this->lang . '.php';
        $messages = include $lang_file;

        return array_merge($messages, self::$validation_methods_errors);
    }

    /**
     * Get error message.
     *
     * @param array $messages
     * @param string $field
     * @param string $rule
     * @return mixed|null
     * @throws Exception
     */
    private function get_error_message(array $messages, string $field, string $rule)
    {
        $custom_error_message = $this->get_custom_error_message($field, $rule);
        if ($custom_error_message !== null) return $custom_error_message;
        if (isset($messages[$rule])) return $messages[$rule];

        throw new Exception(sprintf("'%s' validator does not have an error message.", $rule));
    }

    /**
     * Get custom error message for field and rule.
     *
     * @param string $field
     * @param string $rule
     * @return string|null
     */
    private function get_custom_error_message(string $field, string $rule)
    {
        $rule_name = str_replace('validate_', '', $rule);
        return $this->fields_error_messages[$field][$rule_name] ?? null;
    }

    /**
     * Process error message string.
     *
     * @param $field
     * @param array $params
     * @param string $message
     * @param callable|null $transformer
     * @return string
     */
    private function process_error_message($field, array $params, string $message, ?callable $transformer = null)
    {
        // if field name is explicitly set, use it
        (array_key_exists($field, self::$fields)) ?
            $field = self::$fields[$field] :
            $field = ucwords(str_replace(self::$field_chars_to_spaces, chr(32), $field));

        // if param is a field (i.e. equalsfield validator)
        if (isset($params[0]) && array_key_exists($params[0], self::$fields))
            $params[0] = self::$fields[$params[0]];

        $replace = [
            '{field}' => $field,
            '{param}' => implode(', ', $params)
        ];

        foreach ($params as $key => $value) {
            $replace[sprintf('{param[%s]}', $key)] = $value;
        }

        // for get_readable_errors() <span>
        if ($transformer) $replace = $transformer($replace);
        return strtr($message, $replace);
    }

    /**
     * Process the validation errors and return human readable error messages.
     *
     * @param bool   $convert_to_string = false
     * @param string $field_class
     * @param string $error_class
     * @return array|string
     * @throws Exception if validator doesn't have an error message to set
     */
    public function get_readable_errors(
        bool $convert_to_string = false,
        string $field_class = 'gump-field',
        string $error_class = 'gump-error-message'
    ) {
        if (empty($this->errors)) return $convert_to_string ? '' : [];

        $messages = $this->get_messages();
        $result = [];

        $transformer = static function ($replace) use ($field_class) {
            $replace['{field}'] = sprintf('<span class="%s">%s</span>', $field_class, $replace['{field}']);
            return $replace;
        };

        foreach ($this->errors as $error) {
            $message = $this->get_error_message($messages, $error['field'], $error['rule']);
            $result[] = $this->process_error_message($error['field'], $error['params'], $message, $transformer);
        }

        if ($convert_to_string) {
            return array_reduce($result, static function ($prev, $next) use ($error_class) {
                return sprintf('%s<span class="%s">%s</span>', $prev, $error_class, $next);
            });
        }

        return $result;
    }

    /**
     * Process the validation errors and return an array of errors with field names as keys.
     *
     * @return array
     * @throws Exception
     */
    public function get_errors_array()
    {
        $messages = $this->get_messages();
        $result = [];

        foreach ($this->errors as $error) {
            $message = $this->get_error_message($messages, $error['field'], $error['rule']);
            $result[$error['field']] = $this->process_error_message($error['field'], $error['params'], $message);
        }

        return $result;
    }

    /**
     * Filter the input data according to the specified filter set.
     *
     * @param mixed  $input
     * @param array  $filterset
     * @return mixed
     * @throws Exception
     */
    public function filter(array $input, array $filterset)
    {
        foreach ($filterset as $field => $filters) {
            if (!array_key_exists($field, $input)) continue;
            $filters = $this->parse_rules($filters);

            foreach ($filters as $filter) {
                $parsed_rule = $this->parse_rule($filter);

                (is_array($input[$field])) ?
                    $input_array = &$input[$field] :
                    $input_array = array(&$input[$field]);

                foreach ($input_array as &$value) {
                    $value = $this->call_filter($parsed_rule['rule'], $value, $parsed_rule['param']);
                }

                unset($input_array, $value);
            }
        }

        return $input;
    }
}