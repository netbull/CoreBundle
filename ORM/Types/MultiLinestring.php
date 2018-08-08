<?php

namespace NetBull\CoreBundle\ORM\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Class MultiLinestring
 * @package NetBull\CoreBundle\ORM\Types
 */
class MultiLinestring extends Type
{
    const MULTILINESTRING = 'multilinestring';

    /**
     * {@inheritdoc}
     */
    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'MULTILINESTRING';
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
        if (false === strpos(strtolower($value), 'multilinestring')) {
            throw new \Exception('This is not a MultiLine!');
        }

        $poly = \geoPHP::load($value);

        if (!$poly->checkValidity()) {
            throw new \Exception('The shape is not a valid MultiLine ' . $value);
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

        if (false === strpos(strtolower($value), 'multilinestring')) {
            throw new \Exception("This is not a polygon!");
        }

        $poly = \geoPHP::load($value);

        if (!$poly->checkValidity()) {
            throw new \Exception('The shape is not a valid MultiLine ' . $value);
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
        return sprintf('ST_GeomFromText(%s)', $sqlExpr);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValueSQL($sqlExpr, $platform)
    {
        return sprintf('ST_AsText(%s)', $sqlExpr);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::MULTILINESTRING; // modify to match your constant name
    }
}
