<?php

namespace Solital\Core\Http\Input;

use Solital\Core\Http\{Request, RequestValidatorInterface};
use Solital\Core\Resource\JSON;
use Solital\Core\Resource\Validation\Validator;

class InputHandler
{
    /**
     * @var array
     */
    protected array $get = [];

    /**
     * @var array
     */
    protected array $post = [];

    /**
     * @var array
     */
    protected array $file = [];

    /**
     * @var Request
     */
    protected $request;

    /**
     * Input constructor.
     * 
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->parseInputs();
    }

    /**
     * Parse input values
     * 
     * @return void
     *
     */
    public function parseInputs(): void
    {
        $getVars = filter_input_array(INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (empty($getVars) || $getVars == null) {
            $getVars = filter_var_array($_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }

        /* Parse get requests */
        if (is_array($getVars)) {
            if (\count($getVars) !== 0) {
                $this->get = $this->parseInputItem($getVars);
            }
        }

        /* Parse post requests */
        $postVars = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (empty($postVars) || $postVars == null) {
            $postVars = filter_var_array($_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }

        if (\in_array($this->request->getMethod(), ['put', 'patch', 'delete'], false) === true) {
            parse_str(file_get_contents('php://input'), $postVars);
        }

        if (is_array($postVars)) {
            if (\count($postVars) !== 0) {
                $this->post = $this->parseInputItem($postVars);
            }
        }

        /* Parse get requests */
        if (\count($_FILES) !== 0) {
            $this->file = $this->parseFiles();
        }

        #var_dump($this->post);exit;
    }

    /**
     * @return array
     */
    public function parseFiles(): array
    {
        $list = [];

        foreach ((array)$_FILES as $key => $value) {

            // Handle array input
            if (\is_array($value['name']) === false) {
                $values['index'] = $key;
                try {
                    $list[$key] = InputFile::createFromArray($values + $value);
                } catch (\InvalidArgumentException $e) {
                    throw new \InvalidArgumentException($e->getMessage());
                }
                continue;
            }

            $keys = [$key];
            $files = $this->rearrangeFile($value['name'], $keys, $value);

            if (isset($list[$key]) === true) {
                $list[$key][] = $files;
            } else {
                $list[$key] = $files;
            }
        }

        return $list;
    }

    /**
     * Rearrange multi-dimensional file object created by PHP.
     *
     * @param array $values
     * @param array $index
     * @param array|null $original
     * @return array
     */
    protected function rearrangeFile(array $values, &$index, $original): array
    {
        $originalIndex = $index[0];
        array_shift($index);

        $output = [];

        foreach ($values as $key => $value) {

            if (\is_array($original['name'][$key]) === false) {

                try {

                    $file = InputFile::createFromArray([
                        'index'    => (empty($key) === true && empty($originalIndex) === false) ? $originalIndex : $key,
                        'name'     => $original['name'][$key],
                        'error'    => $original['error'][$key],
                        'tmp_name' => $original['tmp_name'][$key],
                        'type'     => $original['type'][$key],
                        'size'     => $original['size'][$key],
                    ]);

                    if (isset($output[$key]) === true) {
                        $output[$key][] = $file;
                        continue;
                    }

                    $output[$key] = $file;
                    continue;
                } catch (\InvalidArgumentException $e) {
                    throw new \InvalidArgumentException($e->getMessage());
                }
            }

            $index[] = $key;

            $files = $this->rearrangeFile($value, $index, $original);

            if (isset($output[$key]) === true) {
                $output[$key][] = $files;
            } else {
                $output[$key] = $files;
            }
        }

        return $output;
    }

    /**
     * Parse input item from array
     *
     * @param array $array
     * @return array
     */
    protected function parseInputItem(array $array): array
    {
        $list = [];

        foreach ($array as $key => $value) {
            // Handle array input
            if (\is_array($value) === false) {
                $list[$key] = new InputItem($key, $value);
                continue;
            }

            $output = $this->parseInputItem($value);
            $list[$key] = $output;
        }

        return $list;
    }

    /**
     * Find input object
     *
     * @param string $index
     * @param array ...$methods
     * 
     * @return InputItemInterface|array|null
     */
    public function find(string $index, ...$methods): mixed
    {
        $element = null;

        if (\count($methods) === 0 || \in_array('get', $methods, true) === true) {
            $element = $this->get($index);
        }

        if (($element === null && \count($methods) === 0) || (\count($methods) !== 0 && \in_array('post', $methods, true) === true)) {
            $element = $this->post($index);
        }

        if (($element === null && \count($methods) === 0) || (\count($methods) !== 0 && \in_array('file', $methods, true) === true)) {
            $element = $this->file($index);
        }

        return $element;
    }

    /**
     * Get input element value matching index
     *
     * @param string $index
     * @param string|null $defaultValue
     * @param array ...$methods
     * 
     * @return string|array
     */
    public function value(string $index, ?string $defaultValue = null, ...$methods): mixed
    {
        $input = $this->find($index, ...$methods);

        $output = [];

        /* Handle collection */
        if (\is_array($input) === true) {
            /* @var $item InputItem */
            foreach ($input as $item) {
                $output[] = $item->getValue();
            }

            return (\count($output) === 0) ? $defaultValue : $output;
        }

        return ($input === null || ($input !== null && trim($input->getValue()) === '')) ? $defaultValue : $input->getValue();
    }

    /**
     * Check if a input-item exist
     *
     * @param string $index
     * @param array ...$methods
     * 
     * @return bool
     */
    public function exists(string $index, ...$methods): bool
    {
        return $this->value($index, null, ...$methods) !== null;
    }

    /**
     * Find post-value by index or return default value.
     *
     * @param string $index
     * @param string|null $defaultValue
     * 
     * @return InputItem|array|string|null
     */
    public function post(string $index, ?string $defaultValue = null): mixed
    {
        return $this->post[$index] ?? $defaultValue;
    }

    /**
     * Find file by index or return default value.
     *
     * @param string $index
     * @param string|null $defaultValue
     * 
     * @return InputFile|array|string|null
     */
    public function file(string $index, ?string $defaultValue = null): mixed
    {
        return $this->file[$index] ?? $defaultValue;
    }

    /**
     * Find parameter/query-string by index or return default value.
     *
     * @param string $index
     * @param string|null $defaultValue
     * 
     * @return InputItem|array|string|null
     */
    public function get(string $index, ?string $defaultValue = null): mixed
    {
        return $this->get[$index] ?? $defaultValue;
    }

    /**
     * Get all get/post items
     * @param array $filter Only take items in filter
     * 
     * @return array
     */
    public function all(array $filter = []): array
    {
        $output = $_GET;

        if ($this->request->getMethod() === 'post' || $this->request->getMethod() === 'put') {
            if (filter_input_array(INPUT_POST) != null) {
                // Append POST data
                $output += filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $contents = file_get_contents('php://input');

                // Append any PHP-input json
                if (strpos(trim($contents), '{') === 0) {
                    $post = json_decode($contents, true);
                    if ($post !== false) {
                        $output += $post;
                    }
                }
            } elseif ($_POST != null || !empty(file_get_contents('php://input'))) {
                // Append POST data
                $output += filter_var_array($_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $contents = file_get_contents('php://input');

                // Append any PHP-input json
                if (strpos(trim($contents), '{') === 0) {
                    $post = json_decode($contents, true);
                    if ($post !== false) {
                        $output += $post;
                    }
                }
            }
        }

        return (\count($filter) > 0) ? array_intersect_key($output, array_flip($filter)) : $output;
    }

    /**
     * Validate input values
     *
     * @param array|string $validate
     * @param array|null $data_filter
     * 
     * @return array
     */
    public function validate(array|string $validate, ?array $data_filter = null): array
    {
        if (is_string($validate)) {
            if (!class_exists($validate)) {
                throw new \Exception("Class '" . $validate . "' not exists");
            }

            $reflection = new \ReflectionClass($validate);

            if (!$reflection->implementsInterface(RequestValidatorInterface::class)) {
                throw new \Exception($reflection->getName() . " not implements 'RequestValidationInterface' interface");
            }

            $request_validation = $reflection->newInstanceWithoutConstructor();
            $validate = $request_validation->rules();
            $data_messages = $request_validation->messages();
            $data_filter = $request_validation->filters();
        }

        $valid = new Validator();
        $valid->verifyRequestInput($validate);

        if (!empty($data_messages)) {
            $valid->setFieldsErrorMessages($data_messages);
        }

        if (!is_null($data_filter)) {
            $valid->filter($data_filter);
        }

        $result = $valid->getResult();
        return $result;
    }

    /**
     * @return string
     */
    public function getAllJson(): string
    {
        return (new JSON())->encode($_GET);
    }

    /**
     * @param array $filter
     * 
     * @return object
     */
    public function getAllObject(array $filter = []): object
    {
        $object = $this->all($filter);

        return (object)$object;
    }

    /**
     * Add GET parameter
     *
     * @param string $key
     * 
     * @param InputItem $item
     */
    public function addGet(string $key, InputItem $item): void
    {
        $this->get[$key] = $item;
    }

    /**
     * Add POST parameter
     *
     * @param string $key
     * 
     * @param InputItem $item
     */
    public function addPost(string $key, InputItem $item): void
    {
        $this->post[$key] = $item;
    }

    /**
     * Add FILE parameter
     *
     * @param string $key
     * 
     * @param InputFile $item
     */
    public function addFile(string $key, InputFile $item): void
    {
        $this->file[$key] = $item;
    }
}
