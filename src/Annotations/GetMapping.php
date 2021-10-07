<?php

namespace Max\Routing\Annotations;

#[\Attribute(\Attribute::TARGET_METHOD)]
class GetMapping extends RuleMapping
{
    protected array $methods = ['HEAD', 'GET'];
}