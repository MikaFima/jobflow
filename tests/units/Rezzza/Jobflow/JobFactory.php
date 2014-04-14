<?php

namespace Rezzza\Jobflow\Tests\Units;

use mageekguy\atoum as Units;

use Rezzza\Jobflow\JobFactory as TestedClass;

class JobFactory extends Units\Test
{
    protected $registry;

    protected $factory;

    public function beforeTestMethod($method)
    {
        $this->mockGenerator->orphanize('__construct');
        $this->registry = new \mock\Rezzza\Jobflow\JobRegistry;
        $this->factory = new TestedClass($this->registry);
    }

    public function test_it_creates_simple_builder()
    {
        $initOptions = array('a' => '1', 'b' => '2');
        $execOptions = array('c' => '3');
        $resolved = $this->getMockResolvedJob();

        $resolved->getMockController()->createBuilder = 'jean-marc';
        $this->registry->getMockController()->getType = $resolved;

        $this
            ->if($builder = $this->factory->createNamedBuilder('name', 'type', $initOptions, $execOptions))
                ->mock($this->registry)
                    ->call('getType')
                        ->withArguments('type')
                        ->once()

                ->mock($resolved)
                    ->call('createBuilder')
                        ->withArguments('name', $this->factory, $initOptions, $execOptions)
                        ->once()

                ->variable($builder)
                    ->isEqualTo('jean-marc')
        ;
    }

    public function test_it_creates_builder_with_type()
    {
        $initOptions = array('a' => '1', 'b' => '2');
        $execOptions = array('c' => '3');
        $resolved = $this->getMockResolvedJob();
        $type = new \mock\Rezzza\Jobflow\Extension\Core\Type\JobType();
        $factory = $this->getMockFactory();

        $resolved->getMockController()->createBuilder = 'jean-marc';
        $factory->getMockController()->createResolvedType = $resolved;

        $this
            ->if($builder = $factory->createNamedBuilder('name', $type, $initOptions, $execOptions))
                ->mock($factory)
                    ->call('createResolvedType')
                        ->withArguments($type)
                        ->once()

                ->mock($resolved)
                    ->call('createBuilder')
                        ->withArguments('name', $factory, $initOptions, $execOptions)
                        ->once()

                ->variable($builder)
                    ->isEqualTo('jean-marc')
        ;
    }

    public function test_it_creates_builder_with_type_with_parent()
    {
        $initOptions = array('a' => '1', 'b' => '2');
        $execOptions = array('c' => '3');
        $factory = $this->getMockFactory();
        $type = new \mock\Rezzza\Jobflow\Extension\Core\Type\JobType();
        $parentType = new \mock\Rezzza\Jobflow\Extension\Core\Type\JobType();
        $resolved = $this->getMockResolvedJob();

        $type->getMockController()->getParent = 'flex';
        $this->registry->getMockController()->getType = $parentType;
        $factory->getMockController()->createResolvedType = $resolved;
        $resolved->getMockController()->createBuilder = 'jean-marc';

        $this
            ->if($builder = $factory->createNamedBuilder('name', $type, $initOptions, $execOptions))

                ->mock($this->registry)
                    ->call('getType')
                        ->withArguments('flex')
                        ->once()

                ->mock($factory)
                    ->call('createResolvedType')
                        ->withArguments($type, $parentType)
                        ->once()

                ->mock($resolved)
                    ->call('createBuilder')
                        ->withArguments('name', $factory, $initOptions, $execOptions)
                        ->once()
        ;
    }

    public function test_it_creates_builder_with_type_with_parent_job()
    {
        $initOptions = array('a' => '1', 'b' => '2');
        $execOptions = array('c' => '3');
        $factory = $this->getMockFactory();
        $type = new \mock\Rezzza\Jobflow\Extension\Core\Type\JobType();
        $parentType = new \mock\Rezzza\Jobflow\Extension\Core\Type\JobType();
        $resolved = $this->getMockResolvedJob();
        $parentResolved = $this->getMockResolvedJob();

        $resolved->getMockController()->createBuilder = 'jean-marc';
        $type->getMockController()->getParent = $parentType;
        $factory->getMockController()->createResolvedType = $resolved;

        $this
            ->if($builder = $factory->createNamedBuilder('name', $type, $initOptions, $execOptions))

                ->mock($factory)
                    ->call('createResolvedType')
                        ->withArguments($parentType, null)
                        ->once()

                    ->call('createResolvedType')
                        ->withArguments($type, $resolved)
                        ->once()

                ->mock($resolved)
                    ->call('createBuilder')
                        ->withArguments('name', $factory, $initOptions, $execOptions)
                        ->once()

                ->variable($builder)
                    ->isEqualTo('jean-marc')
        ;
    }

    public function test_it_creates_builder_with_resolved_type()
    {
        $initOptions = array('a' => '1', 'b' => '2');
        $execOptions = array('c' => '3');
        $resolved = $this->getMockResolvedJob();

        $resolved->getMockController()->createBuilder = 'jean-marc';

        $this
            ->if($builder = $this->factory->createNamedBuilder('name', $resolved, $initOptions, $execOptions))
                ->mock($resolved)
                    ->call('createBuilder')
                        ->withArguments('name', $this->factory, $initOptions, $execOptions)
                        ->once()

                ->variable($builder)
                    ->isEqualTo('jean-marc')
        ;
    }

    public function test_it_creates_builder_and_fills_io()
    {
        $initOptions = array('a' => '1', 'b' => '2');
        $execOptions = array('c' => '3');
        $resolved = $this->getMockResolvedJob();

        $resolved->getMockController()->createBuilder = 'jean-marc';
        $this->registry->getMockController()->getType = $resolved;

        $this
            ->if($builder = $this->factory->createNamedBuilder('name', 'type', $initOptions, $execOptions))
                ->mock($resolved)
                    ->call('createBuilder')
                        ->withArguments('name', $this->factory, $initOptions, $execOptions)
                        ->once()

                ->mock($this->registry)
                    ->call('getType')
                        ->withArguments('type')
                        ->once()

                ->variable($builder)
                    ->isEqualTo('jean-marc')
        ;
    }

    public function test_it_creates_builder_and_keeps_io()
    {
        $initOptions = array('a' => '1', 'b' => '2');
        $execOptions = array('c' => '3');
        $resolved = $this->getMockResolvedJob();

        $resolved->getMockController()->createBuilder = 'jean-marc';
        $this->registry->getMockController()->getType = $resolved;

        $this
            ->if($builder = $this->factory->createNamedBuilder('name', 'type', $initOptions, $execOptions))
                ->mock($resolved)
                    ->call('createBuilder')
                        ->withArguments('name', $this->factory, $initOptions, $execOptions)
                        ->once()

                ->mock($this->registry)
                    ->call('getType')
                        ->withArguments('type')
                        ->once()

                ->variable($builder)
                    ->isEqualTo('jean-marc')
        ;
    }

    public function test_create_builder_accepts_only_specified_class()
    {
        $factory = $this->factory;

        $this
            ->exception(function() use ($factory) {
                $factory->createNamedBuilder('name', new \stdClass());
            })
                ->hasMessage('Type "stdClass" should be a string, JobTypeInterface or ResolvedJob')
        ;
    }

    public function test_it_creates_a_job_with_string_type()
    {
        $initOptions = array('a' => '1', 'b' => '2');
        $execOptions = array('c' => '3');
        $resolved = $this->getMockResolvedJob();
        $builder = $this->getMockBuilder();

        $this->registry->getMockController()->getType = $resolved;
        $resolved->getMockController()->createBuilder = $builder;
        $builder->getMockController()->getJob = 'JOB';

        $this
            ->if($job = $this->factory->create('TYPE', $initOptions, $execOptions))

                ->mock($this->registry)
                    ->call('getType')
                        ->withArguments('TYPE')
                        ->once()

                ->mock($resolved)
                    ->call('createBuilder')
                        ->withArguments('TYPE', $this->factory, $initOptions, $execOptions)
                        ->once()

                ->mock($builder)
                    ->call('getJob')
                        ->once()

                ->variable($job)
                    ->isEqualTo('JOB')
        ;
    }

    public function test_it_creates_resolved_job()
    {
        $type = new \mock\Rezzza\Jobflow\Extension\Core\Type\JobType();
        $parent = $this->getMockResolvedJob();

        $this
            ->if($resolved = $this->factory->createResolvedType($type, $parent))

                ->object($resolved)
                    ->isInstanceOf('Rezzza\Jobflow\ResolvedJob')
        ;
    }

    private function getMockResolvedJob()
    {
        $this->mockGenerator->orphanize('__construct');

        return new \mock\Rezzza\Jobflow\ResolvedJob;
    }

    private function getMockFactory()
    {
        return new \mock\Rezzza\Jobflow\JobFactory($this->registry);
    }

    private function getMockBuilder()
    {
        $this->mockGenerator->orphanize('__construct');

        return new \mock\Rezzza\Jobflow\JobBuilder;
    }
}
