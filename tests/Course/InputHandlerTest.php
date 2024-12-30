<?php

require_once 'Dummy/DummyMiddleware.php';
require_once 'Dummy/DummyController.php';
require_once 'Dummy/Handler/ExceptionHandler.php';
require_once dirname(__DIR__) . '/bootstrap.php';

use PHPUnit\Framework\TestCase;
use Solital\Core\Http\Input\InputItem;
use Solital\Test\TestRouter;
use Solital\Core\Http\RequestValidatorInterface;

class InputHandlerTest extends TestCase
{
    public function testPost()
    {
        global $_POST;

        $names = [
            'Lester',
            'Michael',
            'Franklin',
            'Trevor',
        ];

        $day = 'monday';

        $_POST = [
            'names' => $names,
            'day' => $day,
        ];

        $router = TestRouter::router();
        $router->reset();
        $router->getRequest()->setMethod('post');

        $handler = TestRouter::request()->getInputHandler();

        $this->assertEquals($names, $handler->value('names'));
        $this->assertEquals($names, $handler->all(['names'])['names']);
        $this->assertEquals($day, $handler->value('day'));
        $this->assertTrue($handler->exists('day'));
        $this->assertInstanceOf(InputItem::class, $handler->find('day'));
        $this->assertInstanceOf(InputItem::class, $handler->post('day'));

        // Check non-existing and wrong request-type
        $this->assertEmpty($handler->all(['non-existing']));
        $this->assertFalse($handler->exists('non-existing'));
        $this->assertNull($handler->value('non-existing'));
        $this->assertNull($handler->find('non-existing'));
        $this->assertNull($handler->value('names', null, 'get'));
        $this->assertNull($handler->find('names', 'get'));

        $objects = $handler->find('names');

        $this->assertCount(4, $objects);

        /* @var $object InputItem */
        foreach ($objects as $i => $object) {
            $this->assertInstanceOf(InputItem::class, $object);
            $this->assertEquals($names[$i], $object->getValue());
        }

        $_POST = [];
    }

    public function testGet()
    {
        global $_GET;

        $names = [
            'Lester',
            'Michael',
            'Franklin',
            'Trevor',
        ];

        $day = 'monday';

        $_GET = [
            'names' => $names,
            'day' => $day,
        ];

        $router = TestRouter::router();
        $router->reset();
        $router->getRequest()->setMethod('get');

        $handler = TestRouter::request()->getInputHandler();

        $this->assertEquals($names, $handler->value('names'));
        $this->assertEquals($names, $handler->all(['names'])['names']);
        $this->assertEquals($day, $handler->value('day'));
        $this->assertTrue($handler->exists('day'));
        $this->assertInstanceOf(InputItem::class, $handler->find('day'));
        $this->assertInstanceOf(InputItem::class, $handler->get('day'));

        // Check non-existing and wrong request-type
        $this->assertEmpty($handler->all(['non-existing']));
        $this->assertFalse($handler->exists('non-existing'));
        $this->assertNull($handler->value('non-existing'));
        $this->assertNull($handler->find('non-existing'));
        $this->assertNull($handler->value('names', null, 'post'));
        $this->assertNull($handler->find('names', 'post'));

        $objects = $handler->find('names');

        $this->assertCount(4, $objects);

        /* @var $object InputItem */
        foreach ($objects as $i => $object) {
            $this->assertInstanceOf(InputItem::class, $object);
            $this->assertEquals($names[$i], $object->getValue());
        }

        $_GET = [];
    }

    public function testValidation()
    {
        global $_POST;

        $_POST = [
            'username' => 'Solital123',
            'password' => 'password2024'
        ];

        $data_rules = [
            'username' => 'required|alpha_numeric|max_len,100|min_len,6',
            'password' => 'required|max_len,100|min_len,6',
            'email'    => 'required|valid_email'
        ];

        $router = TestRouter::router();
        $router->reset();
        $router->getRequest()->setMethod('post');

        $handler = TestRouter::request()->getInputHandler();
        
        $result = $handler->validate($data_rules);
        $this->assertArrayHasKey('validation_errors', $result);

        $_POST = [
            'username' => 'Solital123',
            'password' => ' password2024 ',
            'email' => 'solital@email.com',
        ];

        $data_filter = [
            'username' => 'upper_case|sanitize_string',
            'password' => 'trim',
            'email'    => 'trim|sanitize_email'
        ];

        $result = $handler->validate($data_rules, $data_filter);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys([
            'username' => 'SOLITAL123',
            'password' => 'password2024',
            'email' => 'solital@email.com',
        ], $result, ['username', 'password', 'email']);


        $validator_class = new class implements RequestValidatorInterface
        {
            public function rules(): array
            {
                return [
                    'username' => 'required|alpha_numeric|max_len,100|min_len,6',
                    'password' => 'required|max_len,100|min_len,6',
                    'email'    => 'required|valid_email'
                ];
            }

            public function filters(): array
            {
                return [
                    'username' => 'upper_case|sanitize_string',
                    'password' => 'trim',
                    'email'    => 'trim|sanitize_email'
                ];
            }

            public function messages(): array
            {
                return [
                    'username' => ['required' => 'Fill the Username field please, its required.'],
                    'password' => ['required' => 'Please enter a password. This field is empty.'],
                    'email'    => ['valid_email' => 'Please enter a valid e-mail.']
                ];
            }
        };

        $result = $handler->validate($validator_class::class, $data_filter);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys([
            'username' => 'SOLITAL123',
            'password' => 'password2024',
            'email' => 'solital@email.com',
        ], $result, ['username', 'password', 'email']);

        $_POST = [];
    }

    public function testFile()
    {
        $this->assertEquals(true, true);
    }

    public function testFiles()
    {
        $this->assertEquals(true, true);
    }

    public function testAll()
    {
        $this->assertEquals(true, true);
    }
}
