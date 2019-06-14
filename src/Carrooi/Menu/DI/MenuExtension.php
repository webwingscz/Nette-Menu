<?php

declare(strict_types=1);

namespace Carrooi\Menu\DI;

use Carrooi\Menu\LinkGenerator\ILinkGenerator;
use Carrooi\Menu\LinkGenerator\NetteLinkGenerator;
use Carrooi\Menu\Loaders\ArrayMenuLoader;
use Carrooi\Menu\Localization\ReturnTranslator;
use Carrooi\Menu\Menu;
use Carrooi\Menu\MenuContainer;
use Carrooi\Menu\MenuItemFactory;
use Carrooi\Menu\Security\OptimisticAuthorizator;
use Carrooi\Menu\UI\IMenuComponentFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\Http\Request;
use Nette\Localization\ITranslator;
use Nette\Schema\Expect;
use Nette\Schema\Processor;
use Nette\Schema\Schema;
use Nette\Utils\Strings;

/**
 * @author David Kudera <kudera.d@gmail.com>
 */
final class MenuExtension extends CompilerExtension
{
    /**
     * @return Schema
     */
    public function getConfigSchema(): Schema
    {
        return Expect::arrayOf('array');
    }

    /**
     * @return Schema
     */
    public function getMenuConfigSchema(): Schema
    {
        return Expect::structure([
            'authorizator' => Expect::string(OptimisticAuthorizator::class),
            'translator' => Expect::string(ReturnTranslator::class)->assert('class_exists'),
            'loader' => Expect::string(ArrayMenuLoader::class)->assert('class_exists'),
            'linkGenerator' => Expect::string(NetteLinkGenerator::class)->assert('class_exists'),
            'items' => Expect::array()->default([]),
            'templates' => Expect::structure([
                'menu' => Expect::string(__DIR__ . '/../UI/templates/menu.latte')->assert('is_file'),
                'breadcrumbs' => Expect::string(__DIR__ . '/../UI/templates/breadcrumbs.latte')->assert('is_file'),
                'sitemap' => Expect::string(__DIR__ . '/../UI/templates/sitemap.latte')->assert('is_file'),
            ])->castTo('array'),
        ])->castTo('array');
    }

    /**
     * @return Schema
     */
    public function getItemConfigSchema(): Schema
    {
        return Expect::structure([
            'linkGenerator' => Expect::type(ILinkGenerator::class),
            'include' => Expect::arrayOf('string'),
            'title' => Expect::string(null)->nullable(),
            'action' => Expect::string(null)->nullable(),
            'link' => Expect::string(null)->nullable(),
            'data' => Expect::array()->default([]),
            'items' => Expect::array()->default([]),
            'visibility' => Expect::structure([
                'menu' => Expect::bool(true),
                'breadcrumbs' => Expect::bool(true),
                'sitemap' => Expect::bool(true),
            ])->castTo('array'),
        ])->castTo('array');
    }

    public function loadConfiguration(): void
    {
        $builder = $this->getContainerBuilder();

        $container = $builder->addDefinition($this->prefix('container'))
            ->setClass(MenuContainer::class);

        $builder->addFactoryDefinition($this->prefix('component.menu'))
            ->setImplement(IMenuComponentFactory::class);

        foreach ($this->config as $menuName => $menu) {
            $container->addSetup('addMenu', [
                $this->loadMenuConfiguration($builder, $menuName, $menu),
            ]);
        }
    }

    /**
     * @param ContainerBuilder $builder
     * @param string $menuName
     * @param array $config
     * @return ServiceDefinition
     */
    private function loadMenuConfiguration(ContainerBuilder $builder, string $menuName, array $config): ServiceDefinition
    {
        $config = (new Processor())->process($this->getMenuConfigSchema(), $config);

        $translator = $config['translator'];
        $authorizator = $config['authorizator'];
        $loader = $config['loader'];
        $linkGenerator = $config['linkGenerator'];

        if ($config['translator'] === true) {
            $translator = $builder->getDefinitionByType(ITranslator::class);
        } elseif (!Strings::startsWith($config['translator'], '@')) {
            $translator = $builder->addDefinition($this->prefix('menu.' . $menuName . '.translator'))
                ->setClass($config['translator'])
                ->setAutowired(false);
        }

        if (!Strings::startsWith($config['authorizator'], '@')) {
            $authorizator = $builder->addDefinition($this->prefix('menu.' . $menuName . '.authorizator'))
                ->setClass($config['authorizator'])
                ->setAutowired(false);
        }

        if (!Strings::startsWith($config['loader'], '@')) {
            $loader = $builder->addDefinition($this->prefix('menu.' . $menuName . '.loader'))
                ->setClass($config['loader'])
                ->setAutowired(false);
        }

        if (!Strings::startsWith($config['linkGenerator'], '@')) {
            $linkGenerator = $builder->addDefinition($this->prefix('menu.' . $menuName . '.linkGenerator'))
                ->setClass($config['linkGenerator'])
                ->setAutowired(false);
        }

        if ($loader->getClass() === ArrayMenuLoader::class) {
            $loader->setArguments([$this->normalizeMenuItems($config['items'])]);
        }

        $itemFactory = $builder->addDefinition($this->prefix('menu.' . $menuName . '.factory'))
            ->setClass(MenuItemFactory::class);

        return $builder
            ->addDefinition($this->prefix('menu.' . $menuName))
            ->setClass(Menu::class)
            ->setArguments([
                $linkGenerator,
                $translator,
                $authorizator,
                '@' . Request::class,
                $itemFactory,
                $loader,
                $menuName,
                $config['templates']['menu'],
                $config['templates']['breadcrumbs'],
                $config['templates']['sitemap'],
            ])
            ->addSetup('init')
            ->setAutowired(false);
    }

    /**
     * @param array $items
     * @return array
     */
    private function normalizeMenuItems(array $items): array
    {
        array_walk($items, function (array &$item, string $key) {
            $item = (array) (new Processor())->process($this->getItemConfigSchema(), $item);

            if ($item['title'] === null) {
                $item['title'] = $key;
            }

            $item['items'] = $this->normalizeMenuItems($item['items']);
        });

        return $items;
    }
}
