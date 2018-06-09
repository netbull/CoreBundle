<?php

namespace NetBull\CoreBundle\ORM\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Class Geometry
 * @package NetBull\CoreBundle\ORM\Types
 */
class Geometry extends Type
{
    const GEOMETRY = 'geometry';

    /**
     * {@inheritdoc}
     */
    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'GEOMETRY';
    }

    /**
     * @param mixed $value
     * @param AbstractPlatform $platform
     * @return mixed|string
     * @throws \Exception
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (is_null($value)) {
            return '';
        }

        if (false === strpos(strtolower($value), 'multipolygon') && false === strpos(strtolower($value), 'polygon')) {
            throw new \Exception('This is not a Geometry!');
        }

        $geometry = \geoPHP::load($value);

        if (!$geometry->checkValidity() && !is_null($geometry->checkValidity())) {
            throw new \Exception('The shape is not a valid Geometry ' . $value);
        }

        return $value;
    }

    /**
     * @param mixed $value
     * @param AbstractPlatform $platform
     * @return mixed|string
     * @throws \Exception
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (is_null($value)) {
            return '';
        }

        $geometry = \geoPHP::load($value);

        if (!$geometry->checkValidity() && !is_null($geometry->checkValidity())) {
            throw new \Exception('The shape is not a valid Geometry ' . $value);
        }

        return $geometry;
    }

    /**
     * {@inheritdoc}
     */
    public function canRequireSQLConversion()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValueSQL($sqlExpr, AbstractPlatform $platform)
    {
        return sprintf('GeomFromText(%s)', $sqlExpr);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValueSQL($sqlExpr, $platform)
    {
        return sprintf('AsText(%s)', $sqlExpr);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::GEOMETRY;
    }
}
