<?php

declare(strict_types=1);

namespace Carrooi\Menu;

/**
 * @author David Kudera <kudera.d@gmail.com>
 */
final class MenuContainer
{
    /** @var \Carrooi\Menu\IMenu[] */
    private $menus = [];

    /**
     * @param string $name
     * @return IMenu
     */
    public function getMenu(string $name): IMenu
    {
        return $this->menus[$name];
    }

    /**
     * @param IMenu $menu
     */
    public function addMenu(IMenu $menu): void
    {
        $this->menus[$menu->getName()] = $menu;
    }
}
