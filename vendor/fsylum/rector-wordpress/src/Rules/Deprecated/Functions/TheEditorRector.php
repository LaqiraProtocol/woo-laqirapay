<?php

namespace Fsylum\RectorWordPress\Rules\Deprecated\Functions;

use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class TheEditorRector extends AbstractRector
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
        if (!$this->isName($node, 'the_editor')) {
            return null;
        }

        if ($node->isFirstClassCallable()) {
            return null;
        }

        $arrayItem = new ArrayItem($node->getArgs()[3]->value, new String_('media_buttons'));
        $arguments = $this->nodeFactory->createArgs([
            $node->getArgs()[0],
            $node->getArgs()[1],
            new Array_([$arrayItem]),
        ]);

        return $this->nodeFactory->createFuncCall('wp_editor', $arguments);
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change `the_editor` to equivalent `wp_editor`.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
                        the_editor($content, 'content', 'title', true);
                        CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
                        wp_editor($content, 'content', ['media_buttons' => true]);
                        CODE_SAMPLE
                ),
            ]
        );
    }
}
