<?php

declare(strict_types=1);

namespace Carrooi\Menu;

use Carrooi\Menu\LinkGenerator\ILinkGenerator;
use Carrooi\Menu\Security\IAuthorizator;
use Nette\Http\Request;
use Nette\Localization\ITranslator;

/**
 * @author David Kudera <kudera.d@gmail.com>
 */
abstract class AbstractMenuItemsContainer implements IMenuItemsContainer
{
    /** @var IMenu */
    protected $menu;

    /** @var ILinkGenerator */
    protected $linkGenerator;

    /** @var ITranslator */
    protected $translator;

    /** @var IAuthorizator */
    protected $authorizator;

    /** @var Request */
    protected $httpRequest;

    /** @var IMenuItemFactory */
    protected $menuItemFactory;

    /** @var IMenuItem[] */
    private $items = [];

    /**
     * AbstractMenuItemsContainer constructor.
     * @param IMenu $menu
     * @param ILinkGenerator $linkGenerator
     * @param ITranslator $translator
     * @param IAuthorizator $authorizator
     * @param Request $httpRequest
     * @param IMenuItemFactory $menuItemFactory
     */
    public function __construct(IMenu $menu, ILinkGenerator $linkGenerator, ITranslator $translator, IAuthorizator $authorizator, Request $httpRequest, IMenuItemFactory $menuItemFactory)
    {
        $this->menu = $menu;
        $this->linkGenerator = $linkGenerator;
        $this->translator = $translator;
        $this->authorizator = $authorizator;
        $this->httpRequest = $httpRequest;
        $this->menuItemFactory = $menuItemFactory;
    }

    /**
     * @param ILinkGenerator $linkGenerator
     */
    public function setLinkGenerator(ILinkGenerator $linkGenerator): void
    {
        $this->linkGenerator = $linkGenerator;
    }

    /**
     * @return IMenuItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param string $name
     * @return IMenuItem
     */
    public function getItem(string $name): IMenuItem
    {
        $path = explode('-', $name);

        if (count($path) === 1) {
            return $this->getItems()[$name];
        }

        $current = $this->getItem(array_shift($path));

        while (count($path) > 0) {
            $current = $current->getItem(array_shift($path));
        }

        return $current;
    }

    /**
     * @param string $name
     * @param string $title
     * @param callable|null $fn
     */
    public function addItem(string $name, string $title, callable $fn = null): void
    {
        $this->items[$name] = $item = $this->menuItemFactory->create($this->menu, $this->linkGenerator, $this->translator, $this->authorizator, $this->httpRequest, $this->menuItemFactory, $title);

        if ($fn) {
            $fn($item);
        }
    }

    /**
     * @return IMenuItem|null
     */
    public function findActiveItem(): ?IMenuItem
    {
        foreach ($this->getItems() as $item) {
            if ($item->isAllowed() && $item->isActive()) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    public function hasVisibleItemsOnMenu(): bool
    {
        return $this->hasVisibleItemsOn('menu');
    }

    /**
     * @return array
     */
    public function getVisibleItemsOnMenu(): array
    {
        return $this->getVisibleItemsOn('menu');
    }

    /**
     * @return bool
     */
    public function hasVisibleItemsOnBreadcrumbs(): bool
    {
        return $this->hasVisibleItemsOn('breadcrumbs');
    }

    /**
     * @return array
     */
    public function getVisibleItemsOnBreadcrumbs(): array
    {
        return $this->getVisibleItemsOn('breadcrumbs');
    }

    /**
     * @return bool
     */
    public function hasVisibleItemsOnSitemap(): bool
    {
        return $this->hasVisibleItemsOn('sitemap');
    }

    /**
     * @return array
     */
    public function getVisibleItemsOnSitemap(): array
    {
        return $this->getVisibleItemsOn('sitemap');
    }

    /**
     * @param string $type
     * @return bool
     */
    private function hasVisibleItemsOn(string $type): bool
    {
        return count($this->getVisibleItemsOn($type)) > 0;
    }

    /**
     * @param string $type
     * @return AbstractMenuItemsContainer[]
     */
    private function getVisibleItemsOn(string $type): array
    {
        return array_filter($this->getItems(), function (IMenuItem $item) use ($type) {
            switch ($type) {
                case 'menu': return $item->isVisibleOnMenu();
                case 'breadcrumbs': return $item->isVisibleOnBreadcrumbs();
                case 'sitemap': return $item->isVisibleOnSitemap();
                default: return false;
            }
        });
    }
}
