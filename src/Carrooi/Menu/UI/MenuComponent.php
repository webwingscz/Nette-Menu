<?php

declare(strict_types=1);

namespace Carrooi\Menu\UI;

use Carrooi\Menu\IMenu;
use Carrooi\Menu\MenuContainer;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;

/**
 * @author David Kudera <kudera.d@gmail.com>
 */
final class MenuComponent extends Control
{
    /** @var MenuContainer */
    private $container;

    /** @var string */
    private $menuName;

    /**
     * MenuComponent constructor.
     * @param MenuContainer $container
     * @param string $name
     */
    public function __construct(MenuContainer $container, string $name)
    {
        $this->container = $container;
        $this->menuName = $name;

        $this->monitor(Presenter::class, [$this, 'onPresenter']);
    }

    public function render(): void
    {
        $menu = $this->container->getMenu($this->menuName);
        $this->renderType($menu, $menu->getMenuTemplate());
    }

    public function renderBreadcrumbs(): void
    {
        $menu = $this->container->getMenu($this->menuName);
        $this->renderType($menu, $menu->getBreadcrumbsTemplate());
    }

    public function renderSitemap(): void
    {
        $menu = $this->container->getMenu($this->menuName);
        $this->renderType($menu, $menu->getSitemapTemplate());
    }

    /**
     * @param IMenu $menu
     * @param string $menuTemplate
     */
    public function renderType(IMenu $menu, string $menuTemplate): void
    {
        $this->template->setFile($menuTemplate);
        $this->template->setTranslator($menu->getTranslator());
        $this->template->menu = $menu;

        $this->template->render();
    }

    /**
     * @param Presenter $presenter
     * @return void
     */
    public function onPresenter(Presenter $presenter): void
    {
        $menu = $this->container->getMenu($this->menuName);
        $menu->setActivePresenter($presenter);
    }
}
