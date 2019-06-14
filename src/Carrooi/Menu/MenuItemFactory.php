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
final class MenuItemFactory implements IMenuItemFactory
{
    /**
     * @param IMenu $menu
     * @param ILinkGenerator $linkGenerator
     * @param ITranslator $translator
     * @param IAuthorizator $authorizator
     * @param Request $httpRequest
     * @param IMenuItemFactory $menuItemFactory
     * @param string $title
     * @return IMenuItem
     */
    public function create(IMenu $menu, ILinkGenerator $linkGenerator, ITranslator $translator, IAuthorizator $authorizator, Request $httpRequest, IMenuItemFactory $menuItemFactory, string $title): IMenuItem
    {
        return new MenuItem($menu, $linkGenerator, $translator, $authorizator, $httpRequest, $menuItemFactory, $title);
    }
}
