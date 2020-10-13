<?php

namespace KFOSOFT\Cron\Value;

use ArrayIterator;
use Closure;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\ClosureExpressionVisitor;
use Doctrine\Common\Collections\Selectable;
use JsonSerializable;
use RuntimeException;

abstract class AbstractTypedCollection implements Collection, Selectable, JsonSerializable
{
    /**
     * An array containing the entries of this collection.
     *
     * @psalm-var array<TKey,T>
     * @var array
     */
    protected $elements;

    /**
     * Optional field. It uses for queries when you want to get count of all records without using limit and offset(pagination)
     * @var int|null
     */
    protected $queryCount;

    /**
     * Initializes a new ArrayCollection.
     *
     * @param array    $elements
     * @param int|null $queryCount
     *
     * @psalm-param array<TKey,T> $elements
     */
    public function __construct(array $elements = [], ?int $queryCount = null)
    {
        foreach ($elements as $element) {
            $this->validateItem($element);
        }

        $this->elements   = $elements;
        $this->queryCount = $queryCount;
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return $this->elements;
    }

    /**
     * {@inheritDoc}
     */
    public function first()
    {
        return reset($this->elements);
    }

    /**
     * Creates a new instance from the specified elements.
     *
     * This method is provided for derived classes to specify how a new
     * instance should be created when constructor semantics have changed.
     *
     * @param array $elements Elements.
     *
     * @return static
     *
     * @psalm-param array<TKey,T> $elements
     * @psalm-return static<TKey,T>
     */
    protected function createFrom(array $elements)
    {
        return new static($elements);
    }

    /**
     * {@inheritDoc}
     */
    public function last()
    {
        return end($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    public function key()
    {
        return key($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    public function next()
    {
        return next($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    public function current()
    {
        return current($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($key)
    {
        if (! isset($this->elements[$key]) && ! array_key_exists($key, $this->elements)) {
            return null;
        }

        $removed = $this->elements[$key];
        unset($this->elements[$key]);

        return $removed;
    }

    /**
     * {@inheritDoc}
     */
    public function removeElement($element)
    {
        $key = array_search($element, $this->elements, true);

        if ($key === false) {
            return false;
        }

        unset($this->elements[$key]);

        return true;
    }

    /**
     * Required by interface ArrayAccess.
     *
     * {@inheritDoc}
     */
    public function offsetExists($offset)
    {
        return $this->containsKey($offset);
    }

    /**
     * Required by interface ArrayAccess.
     *
     * {@inheritDoc}
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Required by interface ArrayAccess.
     *
     * {@inheritDoc}
     */
    public function offsetSet($offset, $value)
    {
        if (! isset($offset)) {
            $this->add($value);

            return;
        }

        $this->set($offset, $value);
    }

    /**
     * Required by interface ArrayAccess.
     *
     * {@inheritDoc}
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * {@inheritDoc}
     */
    public function containsKey($key)
    {
        return isset($this->elements[$key]) || array_key_exists($key, $this->elements);
    }

    /**
     * {@inheritDoc}
     */
    public function contains($element)
    {
        return in_array($element, $this->elements, true);
    }

    /**
     * {@inheritDoc}
     */
    public function exists(Closure $p)
    {
        foreach ($this->elements as $key => $element) {
            if ($p($key, $element)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function indexOf($element)
    {
        return array_search($element, $this->elements, true);
    }

    /**
     * {@inheritDoc}
     */
    public function get($key)
    {
        return $this->elements[$key] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function getKeys()
    {
        return array_keys($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    public function getValues()
    {
        return array_values($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return count($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    public function set($key, $value)
    {
        $this->validateItem($value);
        $this->elements[$key] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function add($element)
    {
        $this->validateItem($element);

        $this->elements[] = $element;

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function isEmpty()
    {
        return empty($this->elements);
    }

    /**
     * Required by interface IteratorAggregate.
     *
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->elements);
    }

    /**
     * {@inheritDoc}
     *
     * @return static
     *
     * @psalm-template U
     * @psalm-param Closure(T=):U $func
     * @psalm-return static<TKey, U>
     */
    public function map(Closure $func)
    {
        return $this->createFrom(array_map($func, $this->elements));
    }

    /**
     * {@inheritDoc}
     *
     * @return static
     *
     * @psalm-return static<TKey,T>
     */
    public function filter(Closure $p)
    {
        return $this->createFrom(array_filter($this->elements, $p, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * {@inheritDoc}
     */
    public function forAll(Closure $p)
    {
        foreach ($this->elements as $key => $element) {
            if (! $p($key, $element)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function partition(Closure $p)
    {
        $matches = $noMatches = [];

        foreach ($this->elements as $key => $element) {
            if ($p($key, $element)) {
                $matches[$key] = $element;
            } else {
                $noMatches[$key] = $element;
            }
        }

        return [$this->createFrom($matches), $this->createFrom($noMatches)];
    }

    /**
     * Returns a string representation of this object.
     *
     * @return string
     */
    public function __toString()
    {
        return self::class . '@' . spl_object_hash($this);
    }

    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        $this->elements = [];
    }

    /**
     * {@inheritDoc}
     */
    public function slice($offset, $length = null)
    {
        return array_slice($this->elements, $offset, $length, true);
    }

    /**
     * {@inheritDoc}
     */
    public function matching(Criteria $criteria)
    {
        $expr     = $criteria->getWhereExpression();
        $filtered = $this->elements;

        if ($expr) {
            $visitor  = new ClosureExpressionVisitor();
            $filter   = $visitor->dispatch($expr);
            $filtered = array_filter($filtered, $filter);
        }

        $orderings = $criteria->getOrderings();

        if ($orderings) {
            $next = null;
            foreach (array_reverse($orderings) as $field => $ordering) {
                $next = ClosureExpressionVisitor::sortByField($field, $ordering === Criteria::DESC ? -1 : 1, $next);
            }

            uasort($filtered, $next);
        }

        $offset = $criteria->getFirstResult();
        $length = $criteria->getMaxResults();

        if ($offset || $length) {
            $filtered = array_slice($filtered, (int) $offset, $length);
        }

        return $this->createFrom($filtered);
    }

    /**
     * @return string
     */
    abstract protected static function getItemClass(): string;

    /**
     * @param mixed $item
     * @throws RuntimeException
     * @return bool
     */
    protected function validateItem($item): bool
    {
        if (!is_a($item, static::getItemClass())) {
            throw new RuntimeException(sprintf('Unexpected object given "%s" in "%s". You can use only "%s".', get_class($item), static::class, static::getItemClass()));
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize(): array
    {
        return $this->elements;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function indexBy(string $key): self
    {
        $result = [];

        foreach ($this->elements as $element) {

            $methodName = sprintf('get%s', $key);

            if (!method_exists($element, $methodName)) {
                throw new RuntimeException(sprintf('The get method for key "%s" doesn\'t exist! Class: "%s", not existed method %s', $key, get_class($element), $methodName));
            }

            $value = $element->{$methodName}();

            if (null === $value) {
                throw new RuntimeException(sprintf('The value for key "%s" is null!', $key));
            } elseif (is_object($value)) {
                $value = (string) $value;
            } elseif (!is_scalar($value)) {
                throw new RuntimeException(sprintf('The value for key "%s" isn\'t scalar!', $key));
            }

            $result[$value] = $element;
        }

        $this->elements = $result;

        return $this;
    }

    /**
     * Reset $elements keys like the array_values.
     */
    public function resetKeys(): void
    {
        $this->elements = $this->getValues();
    }

    /**
     * It uses for queries when you want to get count of all records without using limit and offset(pagination)
     * @return int|null
     */
    public function getQueryCount(): ?int
    {
        return $this->queryCount;
    }
}
