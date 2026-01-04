<?php

namespace Fsylum\RectorWordPress\Rules\ConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name\FullyQualified;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

final class ConstToFuncCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var array<string, string>
     */
    private array $configuration = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes constants use to function calls', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
            class SomeClass
            {
                public function run()
                {
                    $value = PHP_SAPI;
                }
            }
            CODE_SAMPLE
            , <<<'CODE_SAMPLE'
                class SomeClass
                {
                    public function run()
                    {
                        $value = php_sapi_name();
                    }
                }
                CODE_SAMPLE
            , ['PHP_SAPI' => 'php_sapi_name'])]);
    }

    public function getNodeTypes(): array
    {
        return [ConstFetch::class];
    }

    public function refactor(Node $node): ?Node
    {
        $const = $this->getName($node);

        if (!is_string($const)) {
            return null;
        }

        if (!array_key_exists($const, $this->configuration)) {
            return null;
        }

        return new FuncCall(new FullyQualified($this->configuration[$const]));
    }

    /**
     * @param array<string, string> $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allString($configuration);
        Assert::allString(\array_keys($configuration));

        $this->configuration = $configuration;
    }
}
