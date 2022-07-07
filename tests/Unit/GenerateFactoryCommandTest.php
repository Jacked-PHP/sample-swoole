<?php

namespace Tests\Unit;

use Nekofar\Slim\Test\Traits\AppTestTrait;
use Tests\TestCase;

class GenerateFactoryCommandTest extends TestCase
{
    use AppTestTrait;

    protected function setUp(): void
    {
        $this->setUpApp($this->getApp());
    }

    public function test_can_create_models_factory()
    {
        global $app;

        $this->mockFilesystem();
        $container = $app->getContainer();
        $filesystem = $container->get('filesystem');
        $model = 'User';
        $factoryLocation = 'src/DB/Factories/' . $model . 'Factory.php';
        $modelLocation = 'src/DB/Models/' . $model . '.php';

        $gotStub = false;

        // here we fake the checks
        $filesystem->shouldReceive('fileExists')
            ->andReturnUsing(function($file) use (&$gotStub, $modelLocation, $factoryLocation) {
                if ($modelLocation === $file) {
                    return true;
                }

                if ($factoryLocation === $file && !$gotStub) {
                    return false;
                } else if ($factoryLocation === $file && $gotStub) {
                    return true;
                }

                $this->fail('Got a call on "fileExists" with a not expected file parameter!');
            });

        // assert that stug is read
        $filesystem->shouldReceive('read')->andReturnUsing(function($stub) use (&$gotStub) {
            if ('src/Commands/stubs/ModelFactory.stub' === $stub) {
                $gotStub = true;
            }
            return 'some-content-{{model}}';
        });

        // assert that we write parsed stub
        $filesystem->shouldReceive('write')->with($factoryLocation, 'some-content-' . $model);

        $result = $this->runCommand('generate:factory', [
            '--model' => $model,
        ]);

        $result->assertCommandIsSuccessful();
        $this->assertTrue($gotStub);
    }

    public function test_fail_if_factory_already_exists()
    {
        global $app;

        $this->mockFilesystem();
        $container = $app->getContainer();
        $filesystem = $container->get('filesystem');
        $factoryLocation = 'src/DB/Factories/UserFactory.php';
        $modelLocation = 'src/DB/Models/User.php';

        // here we fake the checks
        $filesystem->shouldReceive('fileExists')
            ->andReturnUsing(function($file) use (&$gotStub, $modelLocation, $factoryLocation) {
                if ($modelLocation === $file) {
                    return true;
                }

                if ($factoryLocation === $file) {
                    return true;
                }

                $this->fail('Got a call on "fileExists" with a not expected file parameter!');
            });

        $result = $this->runCommand('generate:factory', [
            '--model' => 'User',
        ]);

        $this->assertStringContainsString('ERROR', $result->getDisplay());
    }

    public function test_fail_if_model_doesnt_exists()
    {
        global $app;

        $this->mockFilesystem();
        $container = $app->getContainer();
        $filesystem = $container->get('filesystem');
        $factoryLocation = 'src/DB/Factories/UserFactory.php';
        $modelLocation = 'src/DB/Models/User.php';

        // here we fake the checks
        $filesystem->shouldReceive('fileExists')
            ->andReturnUsing(function($file) use (&$gotStub, $modelLocation) {
                if ($modelLocation === $file) {
                    return false;
                }

                $this->fail('Got a call on "fileExists" with a not expected file parameter!');
            });

        $result = $this->runCommand('generate:factory', [
            '--model' => 'User',
        ]);

        $this->assertStringContainsString('ERROR', $result->getDisplay());
    }
}