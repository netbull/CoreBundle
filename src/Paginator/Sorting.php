<?php

namespace NetBull\CoreBundle\Paginator;

/**
 * Class Sorting
 * @package NetBull\CoreBundle\Paginator
 */
class Sorting
{
	/**
	 * @var string|null
	 */
	private $field = null;

	/**
	 * @var string
	 */
	private $direction;

	/**
	 * Sorting constructor.
	 * @param string|null $field
	 * @param string|null $direction
	 */
	public function __construct(?string $field = null, ?string $direction = 'asc')
	{
		if ($field) {
			$this->field = $field;
		}
		$this->direction = $direction;
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
     */
	public function setDirection(string $direction = 'asc'): Sorting
	{
		$this->direction = $direction ?? 'asc';
		return $this;
	}
}
