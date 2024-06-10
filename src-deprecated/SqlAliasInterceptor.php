<?php

declare(strict_types=1);

namespace Ray\Query;

use BEAR\Resource\ResourceObject;
use InvalidArgumentException;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Aop\ReflectionMethod;
use Ray\Di\InjectorInterface;
use Ray\Query\Annotation\AliasQuery;

/**
 * @deprecated use MapQueryInterceptor instead
 */
class SqlAliasInterceptor implements MethodInterceptor
{
    /**
     * @var InjectorInterface
     */
    private $injector;

    public function __construct(InjectorInterface $injector)
    {
        $this->injector = $injector;
    }

    public function invoke(MethodInvocation $invocation)
    {
        /** @var ReflectionMethod $method */
        $method = $invocation->getMethod();
        /** @var AliasQuery $aliasQuery */
        $aliasQuery = $method->getAnnotation(AliasQuery::class);
        $namedArguments = (array) $invocation->getNamedArguments();
        [$queryId, $params] = $aliasQuery->templated ? $this->templated($aliasQuery, $namedArguments) : [$aliasQuery->id, $namedArguments];
        $interface = $aliasQuery->type === 'row' ? RowInterface::class : RowListInterface::class;
        $query = $this->injector->getInstance($interface, $queryId);
        if ($query instanceof QueryInterface) {
            return $this->getQueryResult($invocation, $query, $params);
        }

        return $invocation->proceed();
    }

    private function getQueryResult(MethodInvocation $invocation, QueryInterface $query, array $param)
    {
        $result = $query($param);
        $object = $invocation->getThis();
        if ($object instanceof ResourceObject) {
            return $this->returnRo($object, $invocation, $result);
        }

        return $result;
    }

    private function returnRo(ResourceObject $ro, MethodInvocation $invocation, $result) : ResourceObject
    {
        if (! $result) {
            return $this->return404($ro);
        }
        $ro->body = $result;

        return $invocation->proceed();
    }

    private function return404(ResourceObject $ro) : ResourceObject
    {
        $ro->code = 404;
        $ro->body = [];

        return $ro;
    }

    private function templated(AliasQuery $aliasQuery, array $namedArguments) : array
    {
        $url = parse_url(uri_template($aliasQuery->id, $namedArguments));
        if (! $url) {
            throw new InvalidArgumentException($aliasQuery->id);
        }
        $queryId = $url['path'];
        isset($url['query']) ? parse_str($url['query'], $params) : $params = $namedArguments;

        return [$queryId, $params + $namedArguments];
    }
}
