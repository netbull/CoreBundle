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
     * @param array $fieldDeclaration
     * @param AbstractPlatform $platform
     * @return string
     */
    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return '';
    }

    /**
     * @param $value
     * @param AbstractPlatform $platform
     * @return BaseRange
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): BaseRange
    {
        list($min, $max) = sscanf($value, '%d-%d');

        return new BaseRange($min, $max);
    }

    /**
     * @param $value
     * @param AbstractPlatform $platform
     * @return mixed|string
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof BaseRange) {
            $value = sprintf('%d-%d', $value->getMin(), $value->getMax());
        }

        return $value;
    }
}
