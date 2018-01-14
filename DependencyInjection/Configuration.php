<?php

namespace BM\BackupManagerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('bm_backup_manager');

        $rootNode
            ->children()
                ->arrayNode('storage')->useAttributeAsKey('name')
                    ->prototype('array')
                    ->validate()
                        ->always()
                        ->then(function ($config) {
                            switch ($config['type']) {
                                case 'Local':
                                    $this->validateAuthenticationType(['root'], $config, 'Local');
                                    break;
                                case 'AwsS3':
                                    $this->validateAuthenticationType(['key', 'secret', 'region', 'version', 'bucket', 'root'], $config, 'AwsS3');
                                    break;
                                case 'Rackspace':
                                    $this->validateAuthenticationType(['username', 'password', 'container'], $config, 'Rackspace');
                                    break;
                                case 'Dropbox':
                                    $this->validateAuthenticationType(['token', 'key', 'secret', 'app', 'root'], $config, 'Dropbox');
                                    break;
                                case 'Ftp':
                                    $this->validateAuthenticationType(['host', 'username', 'password', 'root', 'port', 'passive', 'ssl', 'timeout'], $config, 'Ftp');
                                    break;
                                case 'Sftp':
                                    $this->validateAuthenticationType(['host', 'username', 'password', 'root', 'port', 'timeout', 'privateKey'], $config, 'Sftp');
                                    break;
                            }
                            return $config;
                        })
                    ->end()
                    ->children()
                        ->enumNode('type')
                            ->values(['Local', 'AwsS3', 'Rackspace', 'Dropbox', 'Ftp', 'Sftp'])
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('root')->end()
                        ->scalarNode('key')->end()
                        ->scalarNode('secret')->end()
                        ->scalarNode('region')->end()
                        ->scalarNode('version')->end()
                        ->scalarNode('bucket')->end()
                        ->scalarNode('root')->end()
                        ->scalarNode('username')->end()
                        ->scalarNode('password')->end()
                        ->scalarNode('container')->end()
                        ->scalarNode('token')->end()
                        ->scalarNode('app')->end()
                        ->scalarNode('host')->end()
                        ->scalarNode('port')->end()
                        ->scalarNode('passive')->end()
                        ->scalarNode('ssl')->end()
                        ->scalarNode('timeout')->end()
                        ->scalarNode('privateKey')->end()
                        ->end()
                    ->end()
                ->end() // End storage

                ->arrayNode('database')->isRequired()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('type')->end()
                            ->scalarNode('host')->end()
                            ->scalarNode('port')->end()
                            ->scalarNode('user')->end()
                            ->scalarNode('pass')->end()
                            ->scalarNode('database')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * Validate that the configuration fragment has the specified keys and none other.
     *
     * @param array  $expected Fields that must exist
     * @param array  $actual   Actual configuration hashmap
     * @param string $authName Name of authentication method for error messages
     *
     * @throws InvalidConfigurationException If $actual does not have exactly the keys specified in $expected (plus 'type')
     */
    private function validateAuthenticationType(array $expected, array $actual, $authName)
    {
        unset($actual['type']);
        $actual = array_keys($actual);
        sort($actual);
        sort($expected);
        if ($expected === $actual) {
            return;
        }
        throw new InvalidConfigurationException(sprintf(
            'Storage type "%s" requires keys "%s" but got "%s"',
            $authName,
            implode(', ', $expected),
            implode(', ', $actual)
        ));
    }
}
