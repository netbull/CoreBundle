<?php

namespace NetBull\CoreBundle\ORM\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use NetBull\CoreBundle\ORM\Objects\Range as BaseRange;

class Range extends Type
{
    const RANGE = 'range';

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::RANGE;
    }

    /**
     * @param array $column
     * @param AbstractPlatform $platform
     * @return string
     */
    public function getSqlDeclaration(array $column, AbstractPlatform $platform): string
    {
        return '';
    }

    /**
     * @param mixed $value
     * @param AbstractPlatform $platform
     * @return BaseRange
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): BaseRange
    {
        list($min, $max) = sscanf($value, '%d-%d');

        return new BaseRange($min, $max);
    }

    /**
     * @param mixed $value
     * @param AbstractPlatform $platform
     * @return mixed
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if ($value instanceof BaseRange) {
            $value = sprintf('%d-%d', $value->getMin(), $value->getMax());
        }

        return $value;
    }
}
