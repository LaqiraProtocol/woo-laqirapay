<?php

namespace Fsylum\RectorWordPress\Rules\Deprecated\Functions;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class WpKsesJsEntitiesRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isName($node, 'wp_kses_js_entities')) {
            return null;
        }

        if ($node->isFirstClassCallable()) {
            return null;
        }

        $args1 = new Arg(new String_('%&\s*\{[^}]*(\}\s*;?|$)%'));
        $args2 = new Arg(new String_(''));

        $arguments = $this->nodeFactory->createArgs([$args1, $args2, $node->getArgs()[0]]);

        return $this->nodeFactory->createFuncCall('preg_replace', $arguments);
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change `wp_kses_js_entities` to equivalent `preg_replace`.',
            [
                new CodeSample(
                    'wp_kses_js_entities($foo);',
                    'preg_replace("%&\s*\{[^}]*(\}\s*;?|$)%", "", $foo);'
                ),
            ]
        );
    }
}
