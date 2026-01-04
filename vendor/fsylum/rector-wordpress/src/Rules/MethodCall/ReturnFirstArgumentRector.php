<?php

namespace Fsylum\RectorWordPress\Rules\MethodCall;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node;
use PhpParser\Node\VariadicPlaceholder;
use PHPStan\Type\ObjectType;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

final class ReturnFirstArgumentRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var array<string, string>
     */
    private array $configuration;

    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): null|Arg|VariadicPlaceholder
    {
        if (count($node->args) === 0) {
            return null;
        }

        foreach ($this->configuration as $method => $class) {
            if (!$this->isObjectTypeMatch($node, $class)) {
                continue;
            }

            if (!$this->isName($node->name, $method)) {
                continue;
            }

            return $node->args[0];
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
            'Replace method call directly with its first argument',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
                        $someObject = new SomeExampleClass;
                        $foo = $someObject->someMethod('foo');
                        CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
                        $someObject = new SomeExampleClass;
                        $foo = 'foo';
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
