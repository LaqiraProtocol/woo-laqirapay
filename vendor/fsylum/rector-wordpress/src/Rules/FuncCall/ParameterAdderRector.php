<?php

namespace Fsylum\RectorWordPress\Rules\FuncCall;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node;
use PhpParser\Node\Arg;
use Fsylum\RectorWordPress\ValueObject\FunctionParameterAdder;
use PhpParser\BuilderHelpers;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

final class ParameterAdderRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var array<FunctionParameterAdder>
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

            $position = $config->getPosition();
            $arg      = new Arg(BuilderHelpers::normalizeValue($config->getValue()));

            if (isset($node->args[$position])) {
                continue;
            }

            $node->args[$position] = $arg;
            $hasChanged            = true;
        }

        if (!$hasChanged) {
            return null;
        }

        return $node;
    }

    /**
     * @param array<FunctionParameterAdder> $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allIsAOf($configuration, FunctionParameterAdder::class);

        $this->configuration = $configuration;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Add additional parameter to the function call',
            [
                new ConfiguredCodeSample(
                    'someMethod("foo");',
                    'someMethod("foo", "bar);',
                    [new FunctionParameterAdder('someMethod', 1, 'bar')]
                ),
            ]
        );
    }
}
