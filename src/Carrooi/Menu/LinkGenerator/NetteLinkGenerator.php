<?php

declare(strict_types=1);

namespace Carrooi\Menu\LinkGenerator;

use Carrooi\Menu\IMenuItem;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\InvalidLinkException;

/**
 * @author David Kudera <kudera.d@gmail.com>
 */
final class NetteLinkGenerator implements ILinkGenerator
{
    /** @var LinkGenerator */
    private $linkGenerator;

    /**
     * NetteLinkGenerator constructor.
     * @param LinkGenerator $linkGenerator
     */
    public function __construct(LinkGenerator $linkGenerator)
    {
        $this->linkGenerator = $linkGenerator;
    }

    /**
     * @param IMenuItem $item
     * @return string
     * @throws InvalidLinkException
     */
    public function link(IMenuItem $item): string
    {
        if (($action = $item->getAction()) !== null) {
            return $this->linkGenerator->link($action, $item->getActionParameters());
        } elseif (($link = $item->getLink()) !== null) {
            return $link;
        }

        return '#';
    }
}
