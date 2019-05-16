<?php

namespace webignition\BasilParser\Model\Identifier;

interface IdentifierTypesInterface
{
    const CSS_SELECTOR = 'css-selector';
    const XPATH_EXPRESSION = 'xpath-expression';
    const PAGE_MODEL_ELEMENT_REFERENCE = 'page-model-element-reference';
    const ELEMENT_PARAMETER = 'element-parameter';
}
