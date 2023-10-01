<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\NetteSecurity;

use Attribute;


/**
 * Akce, nebo celý presenter je přístupný bez omezení.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class AllowedSkip
{
}
