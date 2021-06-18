<?php

namespace NetBull\CoreBundle\Paginator;

use InvalidArgumentException;

/**
 * Class Sorting
 * @package NetBull\CoreBundle\Paginator
 */
class Sorting
{
    const DIRECTION_ASC = 'asc';
    const DIRECTION_DESC = 'desc';

    /**
     * @var string|null
     */
    private $field = null;

    /**
     * @var string
     */
    private $direction;

    /**
     * @var string[]
     */
    private $allowedDirections = [self::DIRECTION_ASC, self::DIRECTION_DESC];

    /**
     * Sorting constructor.
     * @param string|null $field
     * @param string|null $direction
     */
    public function __construct(?string $field = null, ?string $direction = self::DIRECTION_ASC)
    {
        if ($field) {
            $this->field = $field;
        }
        $this->direction = in_array($direction, $this->allowedDirections) ? $direction : self::DIRECTION_ASC;
    }

    /**
     * @return string|null
     */
    public function getField(): ?string
    {
        return $this->field;
    }

    /**
     * @param string|null $field
     * @return Sorting
     */
    public function setField(?string $field): Sorting
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @return string
     */
    public function getDirection(): string
    {
        return $this->direction;
    }

    /**
     * @param string $direction
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setDirection(string $direction = self::DIRECTION_ASC): Sorting
    {
        if (!in_array($direction, $this->allowedDirections)) {
            throw new InvalidArgumentException("Direction \"$direction\" is not valid.");
        }

        $this->direction = $direction;
        return $this;
    }

    /**
     * @return array
     */
    public function __toArray(): array
    {
        return [$this->getField(), $this->getDirection()];
    }
}
