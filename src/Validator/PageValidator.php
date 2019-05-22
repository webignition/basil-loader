<?php

namespace webignition\BasilParser\Validator;

use webignition\BasilParser\Model\Page\PageInterface;

class PageValidator
{
    public function validate(PageInterface $page): bool
    {
        return '' !== (string) $page->getUri();
    }
}
