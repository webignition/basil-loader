<?php

namespace webignition\BasilParser\Model;

interface ActionTypesInterface
{
    const CLICK = 'click';
    const SET = 'set';
    const SUBMIT = 'submit';
    const WAIT = 'wait';
    const WAIT_FOR = 'wait-for';
    const BACK = 'back';
    const FORWARD = 'forward';
    const RELOAD = 'reload';
}
