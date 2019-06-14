<?php

declare(strict_types=1);

namespace Carrooi\Menu\Loaders;

use Carrooi\Menu\IMenu;
use Carrooi\Menu\IMenuItem;
use Carrooi\Menu\IMenuItemsContainer;
use Nette\Utils\Validators;

/**
 * @author David Kudera <kudera.d@gmail.com>
 */
final class ArrayMenuLoader implements IMenuLoader
{
    /** @var array */
    private $items;

    /**
     * ArrayMenuLoader constructor.
     * @param array $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * @param IMenu $menu
     */
    public function load(IMenu $menu): void
    {
        $this->processItems($menu, $this->items);
    }

    /**
     * @param IMenuItemsContainer $parent
     * @param array $items
     */
    private function processItems(IMenuItemsContainer $parent, array $items): void
    {
        foreach ($items as $name => $item) {
            $this->processItem($parent, $name, $item);
        }
    }

    /**
     * @param IMenuItemsContainer $parent
     * @param string $name
     * @param array $config
     */
    private function processItem(IMenuItemsContainer $parent, string $name, array $config): void
    {
        $parent->addItem($name, $config['title'], function (IMenuItem $item) use ($config) {
            $item->setData($config['data']);

            $item->setMenuVisibility($config['visibility']['menu']);
            $item->setBreadcrumbsVisibility($config['visibility']['breadcrumbs']);
            $item->setSitemapVisibility($config['visibility']['sitemap']);

            if ($config['linkGenerator'] !== null) {
                $item->setLinkGenerator($config['linkGenerator']);
            }

            if ($config['action'] !== null) {
                if (is_array($config['action'])) {
                    $item->setAction($config['action']['target'], $config['action']['parameters']);
                } else {
                    $item->setAction($config['action']);
                }
            }

            if ($config['link'] !== null) {
                $item->setLink($config['link']);
            }

            if ($config['include'] !== null) {
                $include = array_map(function ($include) {
                    Validators::assert($include, 'string');

                    return $include;
                }, is_array($config['include']) ? $config['include'] : [$config['include']]);
                $item->setInclude($include);
            }

            $this->processItems($item, $config['items']);
        });
    }
}
