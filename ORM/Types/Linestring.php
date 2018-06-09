<?php

namespace NetBull\CoreBundle\ORM\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Class Linestring
 * @package NetBull\CoreBundle\ORM\Types
 */
class Linestring extends Type
{
    const LINESTRING = 'linestring';

    /**
     * {@inheritdoc}
     */
    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'LINESTRING';
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

        if (false === strpos(strtolower($value), 'linestring')) {
            throw new \Exception('This is not a polygon!');
        }

        $poly = \geoPHP::load($value);

        if (!$poly->checkValidity()) {
            throw new \Exception('The shape is not a valid Line ' . $value);
        }

        return $poly;
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

        if (false === strpos(strtolower($value), 'linestring')) {
            throw new \Exception('This is not a Line!');
        }

        $poly = \geoPHP::load($value);

        if (!$poly->checkValidity()) {
            throw new \Exception('The shape is not a valid Line ' . $value);
        }

        return $value;
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
        return self::LINESTRING; // modify to match your constant name
    }
}
