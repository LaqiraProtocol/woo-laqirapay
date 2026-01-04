<?php

namespace Fsylum\RectorWordPress\Rules\FuncCall;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

final class ReturnFirstArgumentRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var array<string>
     */
    private array $configuration;

    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (count($node->args) === 0) {
            return null;
        }

        foreach ($this->configuration as $function) {
            if (!$this->isName($node->name, $function)) {
                continue;
            }

            return $node->args[0];
        }

        return null;
    }

    /**
     * @param array<string> $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allString($configuration);

        $this->configuration = $configuration;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace function call directly with its first argument',
            [
                new ConfiguredCodeSample(
                    '$test = my_func($foo);',
                    '$test = $foo;',
                    ['my_func']
                ),
            ]
        );
    }
}
