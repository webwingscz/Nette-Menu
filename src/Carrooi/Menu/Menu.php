<?php

declare(strict_types=1);

namespace Carrooi\Menu;

use Carrooi\Menu\LinkGenerator\ILinkGenerator;
use Carrooi\Menu\Loaders\IMenuLoader;
use Carrooi\Menu\Security\IAuthorizator;
use Nette\Application\UI\Presenter;
use Nette\Http\Request;
use Nette\Localization\ITranslator;

/**
 * @author David Kudera <kudera.d@gmail.com>
 */
final class Menu extends AbstractMenuItemsContainer implements IMenu
{
    /** @var \Carrooi\Menu\Loaders\IMenuLoader */
    private $loader;

    /** @var string */
    private $name;

    /** @var string[] */
    private $templates = [
        'menu' => null,
        'breadcrumbs' => null,
        'sitemap' => null,
    ];

    /** @var Presenter */
    private $activePresenter;

    /**
     * Menu constructor.
     * @param ILinkGenerator $linkGenerator
     * @param ITranslator $translator
     * @param IAuthorizator $authorizator
     * @param Request $httpRequest
     * @param IMenuItemFactory $menuItemFactory
     * @param IMenuLoader $loader
     * @param string $name
     * @param string $menuTemplate
     * @param string $breadcrumbsTemplate
     * @param string $sitemapTemplate
     */
    public function __construct(ILinkGenerator $linkGenerator, ITranslator $translator, IAuthorizator $authorizator, Request $httpRequest, IMenuItemFactory $menuItemFactory, IMenuLoader $loader, string $name, string $menuTemplate, string $breadcrumbsTemplate, string $sitemapTemplate)
    {
        parent::__construct($this, $linkGenerator, $translator, $authorizator, $httpRequest, $menuItemFactory);

        $this->loader = $loader;
        $this->name = $name;
        $this->templates['menu'] = $menuTemplate;
        $this->templates['breadcrumbs'] = $breadcrumbsTemplate;
        $this->templates['sitemap'] = $sitemapTemplate;
    }

    public function init(): void
    {
        $this->loader->load($this);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getMenuTemplate(): string
    {
        return $this->templates['menu'];
    }

    /**
     * @return string
     */
    public function getBreadcrumbsTemplate(): string
    {
        return $this->templates['breadcrumbs'];
    }

    /**
     * @return string
     */
    public function getSitemapTemplate(): string
    {
        return $this->templates['sitemap'];
    }

    /**
     * @return array
     */
    public function getPath(): array
    {
        $path = [];
        $parent = $this;

        while ($parent) {
            $item = $parent->findActiveItem();

            if (!$item) {
                break;
            }

            $parent = $path[] = $item;
        }

        return $path;
    }

    /**
     * @return Presenter|null
     */
    public function getActivePresenter(): ?Presenter
    {
        return $this->activePresenter;
    }

    /**
     * @param Presenter|null $presenter
     */
    public function setActivePresenter(?Presenter $presenter): void
    {
        $this->activePresenter = $presenter;
    }
}
