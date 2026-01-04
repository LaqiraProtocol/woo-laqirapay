<?php

namespace Fsylum\RectorWordPress\Rules\MethodCall;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node;
use PhpParser\Node\Arg;
use Fsylum\RectorWordPress\ValueObject\MethodParameterAdder;
use PhpParser\BuilderHelpers;
use PHPStan\Type\ObjectType;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

final class ParameterAdderRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var array<MethodParameterAdder>
     */
    private array $configuration = [];
    private bool $hasChanged     = false;

    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class];
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->configuration as $config) {
            if (!$this->isObjectTypeMatch($node, $config->getObjectType())) {
                continue;
            }

            if (!$this->isName($node->name, $config->getMethod())) {
                continue;
            }

            $position = $config->getPosition();
            $arg      = new Arg(BuilderHelpers::normalizeValue($config->getValue()));

            if ($node instanceof StaticCall) {
                $this->processStaticCall($node, $position, $arg);
            }

            if ($node instanceof MethodCall) {
                $this->processMethodCall($node, $position, $arg);
            }
        }

        if ($this->hasChanged) {
            return $node;
        }

        return null;
    }

    private function processStaticCall(StaticCall $staticCall, int $position, Arg $arg): void
    {
        if (isset($staticCall->args[$position])) {
            return;
        }

        $staticCall->args[$position] = $arg;
        $this->hasChanged            = true;
    }

    private function processMethodCall(MethodCall $methodCall, int $position, Arg $arg): void
    {
        if (isset($methodCall->args[$position])) {
            return;
        }

        $methodCall->args[$position] = $arg;
        $this->hasChanged            = true;
    }

    /**
     * @param array<MethodParameterAdder> $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allIsAOf($configuration, MethodParameterAdder::class);

        $this->configuration = $configuration;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Add additional parameter to the method call',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
                        $someObject = new SomeExampleClass;
                        $someObject->someMethod('foo');
                        CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
                        $someObject = new SomeExampleClass;
                        $someObject->someMethod('foo', 'bar');
                        CODE_SAMPLE,
                    [new MethodParameterAdder('SomeExampleClass', 'someMethod', 1, 'bar')]
                ),
            ]
        );
    }

    private function isObjectTypeMatch(MethodCall|StaticCall $call, ObjectType $objectType): bool
    {
        if ($call instanceof MethodCall) {
            return $this->isObjectType($call->var, $objectType);
        }

        return $this->isObjectType($call->class, $objectType);
    }
}
