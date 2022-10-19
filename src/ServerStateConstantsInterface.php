<?php

namespace Opus\Common;

interface ServerStateConstantsInterface
{
    public const STATE_DELETED     = 'deleted';
    public const STATE_INPROGRESS  = 'inprogress';
    public const STATE_RESTRICTED  = 'restricted';
    public const STATE_UNPUBLISHED = 'unpublished';
    public const STATE_PUBLISHED   = 'published';
    public const STATE_TEMPORARY   = 'temporary';
    public const STATE_AUDITED     = 'audited';
}
