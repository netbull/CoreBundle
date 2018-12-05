<?php

namespace NetBull\CoreBundle\ORM\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

use NetBull\CoreBundle\ORM\Objects\Range as BaseRange;

/**
 * Class Range
 * @package NetBull\CoreBundle\ORM\Types
 */
class Range extends Type
{
    const RANGE = 'range';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::RANGE;
    }

    /**
     * {@inheritdoc}
     */
    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        list($min, $max) = sscanf($value, '%d-%d');

        return new BaseRange($min, $max);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof BaseRange) {
            $value = sprintf('%d-%d', $value->getMin(), $value->getMax());
        }

        return $value;
    }
}
