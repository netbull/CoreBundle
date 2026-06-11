<?php

namespace NetBull\CoreBundle\ORM\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use NetBull\CoreBundle\ORM\Objects\Range as BaseRange;

class Range extends Type
{
    public const RANGE = 'range';

    public function getName(): string
    {
        return self::RANGE;
    }

    public function getSqlDeclaration(array $column, AbstractPlatform $platform): string
    {
        return '';
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): BaseRange
    {
        list($min, $max) = sscanf($value, '%d-%d');

        return new BaseRange($min, $max);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if ($value instanceof BaseRange) {
            $value = sprintf('%d-%d', $value->getMin(), $value->getMax());
        }

        return $value;
    }
}
