<?php

namespace NetBull\CoreBundle\Paginator;

use InvalidArgumentException;

class Sorting
{
    public const DIRECTION_ASC = 'asc';

    public const DIRECTION_DESC = 'desc';

    private ?string $field = null;

    private ?string $direction;

    /**
     * @var string[]
     */
    private array $allowedDirections = [self::DIRECTION_ASC, self::DIRECTION_DESC];

    public function __construct(?string $field = null, ?string $direction = self::DIRECTION_ASC)
    {
        if ($field) {
            $this->field = $field;
        }
        $this->direction = in_array($direction, $this->allowedDirections) ? $direction : self::DIRECTION_ASC;
    }

    public function getField(): ?string
    {
        return $this->field;
    }

    public function setField(?string $field): Sorting
    {
        $this->field = $field;

        return $this;
    }

    public function getDirection(): string
    {
        return $this->direction;
    }

    /**
     * @throws InvalidArgumentException
     *
     * @return $this
     */
    public function setDirection(string $direction = self::DIRECTION_ASC): Sorting
    {
        if (!in_array($direction, $this->allowedDirections)) {
            throw new InvalidArgumentException("Direction \"$direction\" is not valid.");
        }

        $this->direction = $direction;

        return $this;
    }

    public function __toArray(): array
    {
        return [$this->getField(), $this->getDirection()];
    }
}
