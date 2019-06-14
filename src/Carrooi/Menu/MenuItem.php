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
final class MenuItem extends AbstractMenuItemsContainer implements IMenuItem
{
    /** @var string */
    private $title;

    /** @var array */
    private $action = [
        'target' => null,
        'parameters' => [],
    ];

    /** @var string|null */
    private $link;

    /** @var array */
    private $data = [];

    /** @var bool[] */
    private $visibility = [
        'menu' => true,
        'breadcrumbs' => true,
        'sitemap' => true,
    ];

    /** @var bool */
    private $active;

    /** @var string[] */
    private $include = [];

    /**
     * MenuItem constructor.
     * @param IMenu $menu
     * @param ILinkGenerator $linkGenerator
     * @param ITranslator $translator
     * @param IAuthorizator $authorizator
     * @param Request $httpRequest
     * @param IMenuItemFactory $menuItemFactory
     * @param string $title
     */
    public function __construct(IMenu $menu, ILinkGenerator $linkGenerator, ITranslator $translator, IAuthorizator $authorizator, Request $httpRequest, IMenuItemFactory $menuItemFactory, string $title)
    {
        parent::__construct($menu, $linkGenerator, $translator, $authorizator, $httpRequest, $menuItemFactory);
        $this->title = $title;
    }

    /**
     * @return bool
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    public function isActive(): bool
    {
        if ($this->active !== null) {
            return $this->active;
        }

        if (!$this->isAllowed()) {
            return $this->active = false;
        }

        if ($this->getAction() && ($presenter = $this->menu->getActivePresenter())) {
            if ($presenter->link('//this') === $this->linkGenerator->link($this)) {
                return $this->active = true;
            }

            if (!empty($this->include)) {
                $actionName = sprintf('%s:%s', $presenter->getName(), $presenter->getAction());
                foreach ($this->include as $include) {
                    if (preg_match(sprintf('~%s~', $include), $actionName)) {
                        return $this->active = true;
                    }
                }
            }
        }

        foreach ($this->getItems() as $item) {
            if ($item->isAllowed() && $item->isActive()) {
                return $this->active = true;
            }
        }

        return $this->active = false;
    }

    /**
     * @return bool
     */
    public function isAllowed(): bool
    {
        return $this->authorizator->isMenuItemAllowed($this);
    }

    /**
     * @return string|null
     */
    public function getAction(): ?string
    {
        return $this->action['target'];
    }

    /**
     * @return array
     */
    public function getActionParameters(): array
    {
        return $this->action['parameters'];
    }

    /**
     * @param string $target
     * @param array $parameters
     */
    public function setAction(string $target, array $parameters = []): void
    {
        $this->action['target'] = $target;
        $this->action['parameters'] = $parameters;
    }

    /**
     * @return string|null
     */
    public function getLink(): ?string
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink(string $link): void
    {
        $this->link = $link;
    }

    /**
     * @return string
     */
    public function getRealTitle(): string
    {
        return $this->translator->translate($this->title);
    }

    /**
     * @return string
     */
    public function getRealLink(): string
    {
        return $this->linkGenerator->link($this);
    }

    /**
     * @return string
     */
    public function getRealAbsoluteLink(): string
    {
        $url = $this->httpRequest->getUrl();
        $prefix = $url->getScheme() . '://' . $url->getHost();

        if ($url->getPort() !== 80) {
            $prefix .= ':' . $url->getPort();
        }

        return $prefix . $this->getRealLink();
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasData(string $name): bool
    {
        return array_key_exists($name, $this->data);
    }

    /**
     * @param string|null $name
     * @param null $default
     * @return array|mixed|null
     */
    public function getData(string $name = null, $default = null)
    {
        if ($name === null) {
            return $this->data;
        }

        if (!$this->hasData($name)) {
            return $default;
        }

        return $this->data[$name];
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @param string $name
     * @param $value
     */
    public function addData(string $name, $value): void
    {
        $this->data[$name] = $value;
    }

    /**
     * @param array $include
     */
    public function setInclude(array $include): void
    {
        $this->include = $include;
    }

    /**
     * @return bool
     */
    public function isVisibleOnMenu(): bool
    {
        return $this->visibility['menu'];
    }

    /**
     * @param bool $visibility
     */
    public function setMenuVisibility(bool $visibility): void
    {
        $this->visibility['menu'] = $visibility;
    }

    /**
     * @return bool
     */
    public function isVisibleOnBreadcrumbs(): bool
    {
        return $this->visibility['breadcrumbs'];
    }

    /**
     * @param bool $visibility
     */
    public function setBreadcrumbsVisibility(bool $visibility): void
    {
        $this->visibility['breadcrumbs'] = $visibility;
    }

    /**
     * @return bool
     */
    public function isVisibleOnSitemap(): bool
    {
        return $this->visibility['sitemap'];
    }

    /**
     * @param bool $visibility
     */
    public function setSitemapVisibility(bool $visibility): void
    {
        $this->visibility['sitemap'] = $visibility;
    }
}
