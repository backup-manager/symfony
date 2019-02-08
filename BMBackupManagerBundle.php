<?php

namespace BM\BackupManagerBundle;

use BM\BackupManagerBundle\DependencyInjection\Compiler\FlysystemFilesystemPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * BMBackupManagerBundle.
 *
 * @author Luiz Henrique Gomes Palácio <lhpalacio@outlook.com>
 */
class BMBackupManagerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new FlysystemFilesystemPass());
    }
}
