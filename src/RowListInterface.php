<?php

declare(strict_types=1);

namespace Ray\Query;

interface RowListInterface extends InvokeInterface
{
    /**
     * @param array<string, mixed> ...$query
     *
     * @return iterable<mixed>
     */
    public function __invoke(array ...$query): iterable;
}
