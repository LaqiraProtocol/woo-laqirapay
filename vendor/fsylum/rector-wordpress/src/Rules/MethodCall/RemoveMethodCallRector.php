<?php

namespace Fsylum\RectorWordPress\Rules\MethodCall;

use PhpParser\Node\Stmt\Expression;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\NodeTraverser;
use PHPStan\Type\ObjectType;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

final class RemoveMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var array<string, string>
     */
    private array $configuration = [];

    public function getNodeTypes(): array
    {
        return [Expression::class];
    }

    /**
     * @param Expression $node
     */
    public function refactor(Node $node): ?int
    {
        $expr = $node->expr;

        if (!$expr instanceof MethodCall) {
            return null;
        }

        foreach ($this->configuration as $method => $class) {
            if (!$this->isObjectTypeMatch($expr, $class)) {
                continue;
            }

            if (!$this->isName($expr->name, $method)) {
                continue;
            }

            return NodeTraverser::REMOVE_NODE;
        }

        return null;
    }

    /**
     * @param array<string, string> $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allString(array_values($configuration));
        Assert::allString($configuration);

        $this->configuration = $configuration;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove method call from the specified class',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
                        $someObject = new SomeExampleClass;
                        $someObject->someMethod('foo');
                        CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
                        $someObject = new SomeExampleClass;
                        CODE_SAMPLE,
                    ['someMethod' => 'SomeExampleClass']
                ),
            ]
        );
    }

    private function isObjectTypeMatch(MethodCall $methodCall, string $class): bool
    {
        $objectType = new ObjectType($class);

        return $this->isObjectType($methodCall->var, $objectType);
    }
}
