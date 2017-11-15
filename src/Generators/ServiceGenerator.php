<?php
namespace LaravelRocket\Generator\Generators;

class ServiceGenerator extends Generator
{
    public function generate($name, $overwrite = false, $baseDirectory = null, $additionalData = [])
    {
        $modelName = $this->getModelName($name);
        $this->generateService($modelName);
        $this->generateServiceInterface($modelName);
        $this->generateServiceUnitTest($modelName);
        $this->bindInterface($modelName);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function getModelName($name)
    {
        $className = $this->getClassName($name);
        $modelName = str_replace('Service', '', $className);

        return $modelName;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function getModelClass($name)
    {
        $modelName = $this->getModelName($name);

        return '\\App\\Models\\'.$modelName;
    }

    protected function getServiceClass($name)
    {
        $modelName = $this->getModelName($name);

        return '\\App\\Services\\Production\\'.$modelName.'Service';
    }

    /**
     * @param string $modelName
     *
     * @return bool
     */
    protected function generateService($modelName)
    {
        $className  = $this->getServiceClass($modelName);
        $classPath  = $this->convertClassToPath($className);
        $modelClass = $this->getModelClass($modelName);

        $stubFilePath = $this->getStubPath('/service/service.stub');

        $classExists = class_exists($modelClass);
        if ($classExists) {
            $instance = new $modelClass();
            if ($instance instanceof \LaravelRocket\Foundation\Models\AuthenticatableBase) {
                $stubFilePath = $this->getStubPath('/service/service_with_auth.stub');
            } else {
                $stubFilePath = $this->getStubPath('/service/service_with_model.stub');
            }
        }

        return $this->generateFile($modelName, $classPath, $stubFilePath);
    }

    /**
     * @param string $modelName
     *
     * @return bool
     */
    protected function generateServiceInterface($modelName)
    {
        $className  = '\\App\\Services\\'.$modelName.'ServiceInterface';
        $classPath  = $this->convertClassToPath($className);
        $modelClass = $this->getModelClass($modelName);

        $stubFilePath = $this->getStubPath('/service/service_interface.stub');
        $classExists  = class_exists($modelClass);
        if ($classExists) {
            $instance = new $modelClass();
            if ($instance instanceof \LaravelRocket\Foundation\Models\AuthenticatableBase) {
                $stubFilePath = $this->getStubPath('/service/service_interface_with_auth.stub');
            }
        }

        return $this->generateFile($modelName, $classPath, $stubFilePath);
    }

    /**
     * @param string $modelName
     *
     * @return bool
     */
    protected function generateServiceUnitTest($modelName)
    {
        $classPath  = base_path('/tests/Services/'.$modelName.'ServiceTest.php');
        $modelClass = $this->getModelClass($modelName);

        $stubFilePath = $this->getStubPath('/service/service_unittest.stub');
        $classExists  = class_exists($modelClass);
        if ($classExists) {
            $instance = new $modelClass();
            if ($instance instanceof \LaravelRocket\Foundation\Models\AuthenticatableBase) {
                $stubFilePath = $this->getStubPath('/service/service_unittest.stub');
            }
        }

        return $this->generateFile($modelName, $classPath, $stubFilePath);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    protected function bindInterface($name)
    {
        $bindingPath = base_path('/app/Providers/ServiceServiceProvider.php');

        $key  = '/* NEW BINDING */';
        $bind = '$this->app->singleton('.PHP_EOL.'            \\App\\Services\\'.$name.'ServiceInterface::class,'.PHP_EOL.'            \\App\\Services\\Production\\'.$name.'Service::class'.PHP_EOL.'        );'.PHP_EOL.PHP_EOL.'        ';
        $this->replaceFile([
            $key => $bind,
        ], $bindingPath);

        return true;
    }
}
