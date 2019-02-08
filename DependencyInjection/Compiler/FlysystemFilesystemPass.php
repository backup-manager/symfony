<?php

namespace BM\BackupManagerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FlysystemFilesystemPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('backup_manager.filesystems.flysystem_filesystem');

        if (!empty($definition->getArguments())) {
            return;
        }

        $filesystems = [];
        foreach ($container->findTaggedServiceIds('oneup_flysystem.filesystem') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['key'])) {
                    continue;
                }

                $name = $attribute['key'];
                $filesystems[$name] = new Reference($id);
            }
        }

        $arguments = [$filesystems];
        if ($container->hasDefinition('oneup_flysystem.mount_manager')) {
            $arguments[] = new Reference('oneup_flysystem.mount_manager');
        }

        $definition->setArguments($arguments);
    }
}
