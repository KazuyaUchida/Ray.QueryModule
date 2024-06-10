<?php

declare(strict_types=1);

namespace Ray\Query\Annotation;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Ray\Query\Exception\QueryTypeException;

/**
 * Annotates your class methods into which the Injector should inject values
 *
 * @Annotation
 * @Target("METHOD")
 * @psalm-suppress MissingConstructor
 * @NamedArgumentConstructor
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class Query
{
    /**
     * Query ID
     *
     * @var string
     */
    public $id;

    /**
     * Is ID templated ?
     *
     * @var bool
     */
    public $templated;

    /**
     * @Enum({"row", "row_list"})
     * @var 'row'|'row_list'
     */
    public $type = 'row_list';

    /** @SuppressWarnings(PHPMD.BooleanArgumentFlag) */
    public function __construct(string $id, string $type = 'row_list', bool $templated = false)
    {
        $this->id = $id;
        $this->templated = $templated;
        if (! ($type === 'row') && ! ($type === 'row_list')) {
            throw new QueryTypeException($type);
        }

        $this->type = $type;
    }
}
