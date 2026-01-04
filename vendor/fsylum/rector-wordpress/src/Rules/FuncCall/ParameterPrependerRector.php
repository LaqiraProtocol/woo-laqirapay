<?php

namespace Fsylum\RectorWordPress\Rules\FuncCall;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node;
use PhpParser\Node\Arg;
use Fsylum\RectorWordPress\ValueObject\FunctionParameterPrepender;
use PhpParser\BuilderHelpers;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

final class ParameterPrependerRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var array<FunctionParameterPrepender>
     */
    private array $configuration = [];

    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $hasChanged = false;

        foreach ($this->configuration as $config) {
            if (!$this->isName($node->name, $config->getFunction())) {
                continue;
            }

            $arg = new Arg(BuilderHelpers::normalizeValue($config->getValue()));

            $args = $node->args;

            array_unshift($args, $arg);

            $node->args = $args;
            $hasChanged = true;
        }

        if (!$hasChanged) {
            return null;
        }

        return $node;
    }

    /**
     * @param array<FunctionParameterPrepender> $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allIsAOf($configuration, FunctionParameterPrepender::class);

        $this->configuration = $configuration;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Prepend additional parameter to the start of the function call',
            [
                new ConfiguredCodeSample(
                    'someMethod("bar',
                    'someMethod("foo", "bar);',
                    [new FunctionParameterPrepender('someMethod', 'foo')]
                ),
            ]
        );
    }
}
