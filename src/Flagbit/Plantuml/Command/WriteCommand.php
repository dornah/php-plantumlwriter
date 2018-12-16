<?php

namespace Flagbit\Plantuml\Command;

use Flagbit\Plantuml\TokenReflection\ClassWriter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class WriteCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('write')
            ->setDescription('Generates PlantUML diagram from php source')
            ->addArgument(
                'files',
                InputArgument::IS_ARRAY
            )
            ->addOption('without-constants', null, null, 'Disables rendering of constants')
            ->addOption('without-methods', null, null, 'Disables rendering of methods')
            ->addOption('without-properties', null, null, 'Disables rendering of properties')
            ->addOption('without-doc-content', null, null, 'Disables parsing doc block for methods or properties');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $classWriter = new \Flagbit\Plantuml\TokenReflection\ClassWriter();
        if (!$input->getOption('without-constants')) {
            $classWriter->setConstantWriter(new \Flagbit\Plantuml\TokenReflection\ConstantWriter());
        }
        if (!$input->getOption('without-properties')) {
            $classWriter->setPropertyWriter(new \Flagbit\Plantuml\TokenReflection\PropertyWriter());
        }
        if (!$input->getOption('without-methods')) {
            $classWriter->setMethodWriter(new \Flagbit\Plantuml\TokenReflection\MethodWriter());
        }
        if (!$input->getOption('without-doc-content')) {
            $classWriter->setDocContentWriter(new \Flagbit\Plantuml\TokenReflection\DocContentWriter());
        }

        $output->write('@startuml', "\n");
        foreach ($input->getArgument('files') as $path) {
            $output->write($this->writePath($path, $classWriter));
        }
        $output->write('@enduml', "\n");
    }


    protected function writePath(string $path, ClassWriter $classWriter)
    {
        if (is_dir($path)) {
            return $this->writeDirectory($path, $classWriter);
        } elseif (is_file($path)) {
            return $this->writeFile($path, $classWriter);
        } else {
            throw new \InvalidArgumentException('The given directory/file does not exist.');
        }
    }

    protected function writeDirectory(string $path, ClassWriter $classWriter)
    {
        $output = "";

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $entry) {
            if ($entry->isFile()) {
                $output .= $this->writeFile($entry, $classWriter);
            }
        }
        return $output;
    }

    protected function writeFile(string $path, ClassWriter $classWriter)
    {
        $output = "";
        $parsedFile = new \Go\ParserReflection\ReflectionFile($path);
        $fileNameSpaces = $parsedFile->getFileNamespaces();
        foreach ($fileNameSpaces as $namespace) {
            foreach ($namespace->getClasses() as $class) {
                $output .= $classWriter->writeElement($class);
            }
        }
        return $output;
    }
}
