<?php
namespace NetBull\CoreBundle\ORM\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Class Multipolygon
 * @package NetBull\CoreBundle\ORM\Types
 */
class Multipolygon extends Type
{
    const MULTIPOLYGON = 'multipolygon';

    /**
     * {@inheritdoc}
     */
    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'MULTIPOLYGON';
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
            throw new \Exception('This is not a MultiPolygon!');
        }

        $poly = \geoPHP::load($value);

        if (!$poly->checkValidity() && !is_null($poly->checkValidity())) {
            throw new \Exception('The shape is not a valid MultiPolygon ' . $value);
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

        if (false === strpos(strtolower($value), 'multipolygon') && false === strpos(strtolower($value), 'polygon')) {
            throw new \Exception('This is not a MultiPolygon!');
        }

        $poly = \geoPHP::load($value);

        if (!$poly->checkValidity() && !is_null($poly->checkValidity())) {
            throw new \Exception('The shape is not a valid MultiPolygon' . $value);
        }

        return $poly;
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
        return self::MULTIPOLYGON;
    }
}
